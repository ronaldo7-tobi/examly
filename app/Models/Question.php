<?php

/**
 * Model Pytania (Question).
 *
 * Centralna klasa odpowiedzialna za całą logikę pobierania pytań z bazy
 * danych. Zawiera zarówno proste zapytania, jak i zaawansowane, spersonalizowane
 * kwerendy dla zalogowanych użytkowników, wykorzystujące złączenia (`JOIN`)
 * z tabelą postępów (`user_progress`).
 *
 * @version 1.2.0
 * @author Tobiasz Szerszeń
 */
class Question
{
  /**
   * Instancja centralnego obiektu do obsługi bazy danych (Singleton).
   * @var Database
   */
  private Database $db;

  /**
   * Konstruktor modelu Question.
   *
   * Pobiera instancję połączenia z bazą danych, która będzie używana
   * we wszystkich metodach tego modelu.
   */
  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  /**
   * Pobiera losowe pytania z określonych kategorii i typu egzaminu.
   *
   * Podstawowa metoda do pobierania pytań, używana gdy użytkownik nie jest
   * zalogowany lub nie korzysta z filtrów premium.
   *
   * @param array<int> $subjectIds Tablica z numerycznymi ID kategorii.
   * @param int $limit Maksymalna liczba pytań do pobrania.
   * @param int $examTypeId Numeryczne ID typu egzaminu.
   *
   * @return array Tablica z danymi pytań.
   */
  public function getQuestions(array $subjectIds, int $limit, int $examTypeId): array
  {
    $placeholders = $this->buildInClausePlaceholders($subjectIds);
    if (!$placeholders) {
      return [];
    }

    $sql = "SELECT * FROM questions 
                WHERE topic_id IN ($placeholders) AND exam_type_id = ? 
                ORDER BY RAND() LIMIT " . (int) $limit;

    return $this->db->fetchAll($sql, array_merge($subjectIds, [$examTypeId]));
  }

  /**
   * Pobiera pytania, na które użytkownik jeszcze nie odpowiadał ("Nieodkryte").
   *
   * Wykorzystuje zaawansowaną technikę `LEFT JOIN ... IS NULL`, która jest
   * bardzo wydajnym sposobem na znalezienie wierszy w jednej tabeli (pytania),
   * które nie mają żadnego powiązania w drugiej tabeli (postępy użytkownika).
   *
   * @param int $userId ID zalogowanego użytkownika.
   * @param array<int> $subjectIds Tablica z numerycznymi ID kategorii.
   * @param int $limit Limit pytań do pobrania.
   * @param int $examTypeId Numeryczne ID typu egzaminu.
   *
   * @return array Tablica z "nieodkrytymi" pytaniami.
   */
  public function getUndiscoveredQuestions(int $userId, array $subjectIds, int $limit, int $examTypeId): array
  {
    $placeholders = $this->buildInClausePlaceholders($subjectIds);
    if (!$placeholders) {
      return [];
    }

    $sql = "SELECT q.* FROM questions q
                LEFT JOIN user_progress up ON q.id = up.question_id AND up.user_id = ? 
                WHERE q.topic_id IN ($placeholders) AND q.exam_type_id = ? AND up.question_id IS NULL
                ORDER BY RAND() LIMIT " . (int) $limit;

    return $this->db->fetchAll($sql, array_merge([$userId], $subjectIds, [$examTypeId]));
  }

  /**
   * Pobiera pytania, z którymi użytkownik radzi sobie najsłabiej (skuteczność <= 60%).
   *
   * Zapytanie w locie oblicza skuteczność (`accuracy`) dla każdego pytania,
   * a następnie używa klauzuli `HAVING` do odfiltrowania tylko tych, które
   * spełniają kryterium niskiej skuteczności. Wyniki są sortowane rosnąco
   * wg skuteczności, aby w pierwszej kolejności zwracać najtrudniejsze pytania.
   *
   * @param int $userId ID zalogowanego użytkownika.
   * @param array<int> $subjectIds Tablica z numerycznymi ID kategorii.
   * @param int $limit Limit pytań do pobrania.
   * @param int $examTypeId Numeryczne ID typu egzaminu.
   *
   * @return array Tablica z pytaniami o najniższej skuteczności.
   */
  public function getLowerAccuracyQuestions(int $userId, array $subjectIds, int $limit, int $examTypeId): array
  {
    $placeholders = $this->buildInClausePlaceholders($subjectIds);
    if (!$placeholders) {
      return [];
    }

    $sql = "SELECT q.*, (up.correct_attempts * 100.0 / (up.correct_attempts + up.wrong_attempts)) as accuracy
              FROM questions q
              INNER JOIN user_progress up ON q.id = up.question_id AND up.user_id = ?
              WHERE q.topic_id IN ($placeholders) AND q.exam_type_id = ?
              AND (up.correct_attempts + up.wrong_attempts) > 0
              HAVING accuracy <= 60
              ORDER BY accuracy, RAND() LIMIT " . (int) $limit;

    return $this->db->fetchAll($sql, array_merge([$userId], $subjectIds, [$examTypeId]));
  }

  /**
   * Pobiera pytania, które były rozwiązywane przez użytkownika najdawniej.
   *
   * Idealne do mechanizmu "powtórek". Sortuje pytania po dacie ostatniej
   * odpowiedzi (`last_attempt ASC`), aby na początku znalazły się te,
   * które użytkownik widział najdawniej i mógł już zapomnieć.
   *
   * @param int $userId ID zalogowanego użytkownika.
   * @param array<int> $subjectIds Tablica z numerycznymi ID kategorii.
   * @param int $limit Limit pytań do pobrania.
   * @param int $examTypeId Numeryczne ID typu egzaminu.
   *
   * @return array Tablica z najdawniej powtarzanymi pytaniami.
   */
  public function getQuestionsRepeatedAtTheLatest(int $userId, array $subjectIds, int $limit, int $examTypeId): array
  {
    $placeholders = $this->buildInClausePlaceholders($subjectIds);
    if (!$placeholders) {
      return [];
    }

    $sql = "SELECT q.* FROM questions q
              INNER JOIN user_progress up ON q.id = up.question_id AND up.user_id = ?
              WHERE q.topic_id IN ($placeholders) AND q.exam_type_id = ?
              ORDER BY up.last_attempt ASC LIMIT " . (int) $limit;

    return $this->db->fetchAll($sql, array_merge([$userId], $subjectIds, [$examTypeId]));
  }

  /**
   * Pobiera pytania, na które użytkownik ostatnio odpowiedział błędnie.
   *
   * Prosty, ale skuteczny filtr `WHERE up.last_result = 0` pozwala szybko
   * wyselekcjonować pytania, z którymi użytkownik miał problem podczas
   * ostatniego kontaktu, umożliwiając natychmiastową korektę.
   *
   * @param int $userId ID zalogowanego użytkownika.
   * @param array<int> $subjectIds Tablica z numerycznymi ID kategorii.
   * @param int $limit Limit pytań do pobrania.
   * @param int $examTypeId Numeryczne ID typu egzaminu.
   *
   * @return array Tablica z ostatnimi błędnie rozwiązanymi pytaniami.
   */
  public function getLastMistakes(int $userId, array $subjectIds, int $limit, int $examTypeId): array
  {
    $placeholders = $this->buildInClausePlaceholders($subjectIds);
    if (!$placeholders) {
      return [];
    }

    $sql = "SELECT q.* FROM questions q
              INNER JOIN user_progress up ON q.id = up.question_id AND up.user_id = ?
              WHERE q.topic_id IN ($placeholders) AND q.exam_type_id = ? AND up.last_result = 0
              ORDER BY RAND() LIMIT " . (int) $limit;

    return $this->db->fetchAll($sql, array_merge([$userId], $subjectIds, [$examTypeId]));
  }

  /**
   * Pobiera jedno, konkretne pytanie na podstawie jego ID.
   *
   * @param int $id ID szukanego pytania.
   *
   * @return array|false Tablica z danymi pytania lub `false`, jeśli nie istnieje.
   */
  public function getQuestionById(int $id): array|false
  {
    $sql = 'SELECT * FROM questions WHERE id = ?';
    return $this->db->fetch($sql, [$id]);
  }

  /**
   * Pobiera numeryczne ID typu egzaminu na podstawie jego kodu tekstowego.
   *
   * Tłumaczy przyjazny dla użytkownika kod (np. "INF.03") na klucz obcy
   * używany w relacjach bazy danych.
   *
   * @param string $code Kod egzaminu (np. 'INF.03').
   *
   * @return int|null Zwraca numeryczne ID egzaminu lub `null`, jeśli nie znaleziono.
   */
  public function getExamTypeIdByCode(string $code): ?int
  {
    $sql = 'SELECT id FROM exam_types WHERE code = ? LIMIT 1';
    $result = $this->db->fetch($sql, [$code]);

    // Operator ?? (null coalescing) to elegancki sposób na zwrócenie
    // wartości z tablicy lub `null`, jeśli klucz nie istnieje lub $result to `false`.
    return $result['id'] ?? null;
  }

  /**
   * Pobiera unikalne ID wszystkich tematów powiązanych z danym typem egzaminu.
   *
   * Używane przy generowaniu pełnego arkusza egzaminacyjnego, aby mieć pewność,
   * że losujemy pytania z całej dostępnej puli dla danego egzaminu.
   *
   * @param int $examTypeId Numeryczne ID typu egzaminu.
   *
   * @return array<int> Płaska tablica z unikalnymi ID tematów, np. `[1, 5, 12]`.
   */
  public function getTopicIdsByExamType(int $examTypeId): array
  {
    // Użycie `DISTINCT` zapobiega powtórzeniom ID tematów.
    $sql = 'SELECT DISTINCT topic_id FROM questions WHERE exam_type_id = ?';
    $results = $this->db->fetchAll($sql, [$examTypeId]);

    // Funkcja `array_column` to wydajny sposób na "spłaszczenie" tablicy
    // wyników z `[['topic_id'=>1], ['topic_id'=>5]]` do `[1, 5]`.
    return array_column($results, 'topic_id');
  }

  // ========================================================================
  // METODY POMOCNICZE (PRIVATE)
  // ========================================================================

  /**
   * Buduje ciąg placeholderów `(?, ?, ...)` dla klauzuli SQL `IN`.
   *
   * Zabezpiecza przed pustą tablicą ID i dynamicznie tworzy odpowiednią
   * liczbę znaków zapytania na potrzeby przygotowanych zapytań (prepared statements).
   *
   * @param array<int> $ids Tablica numerycznych ID.
   *
   * @return string|null Ciąg znaków `?,?,?` lub `null`, jeśli tablica jest pusta.
   */
  private function buildInClausePlaceholders(array $ids): ?string
  {
    if (empty($ids)) {
      return null;
    }
    return implode(',', array_fill(0, count($ids), '?'));
  }
}
