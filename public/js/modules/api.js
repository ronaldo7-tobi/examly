// Plik: public/js/modules/api.js
export const IMAGE_BASE_PATH = '/examly/public/images/questions/';
const API_BASE_URL = '/examly/public/api';

/**
 * Pobiera pytanie z serwera na podstawie wybranych tematów i opcji premium.
 *
 * @param {string[]} subjects - Tablica z wybranymi tematami.
 * @param {string|null} premiumOption - Wybrana opcja premium lub null.
 * @returns {Promise<object>} - Obietnica z danymi pytania.
 */
export async function fetchQuestion(subjects, premiumOption) {
    // 1. Tworzymy parametry, które dodamy do adresu URL
    const params = new URLSearchParams();
    
    // Dodajemy wszystkie wybrane tematy
    subjects.forEach(subject => params.append('subject[]', subject));

    // --- POPRAWKA ---
    // Jeśli opcja premium została wybrana, dodajemy ją do parametrów
    if (premiumOption) {
        params.append('premium_option', premiumOption);
    }
    // --- KONIEC POPRAWKI ---

    // 2. Budujemy pełny adres URL z naszymi parametrami
    const url = `${API_BASE_URL}/get-question?${params.toString()}`;

    // 3. Wysyłamy żądanie metodą GET
    const response = await fetch(url, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
        },
    });

    if (!response.ok) {
        // Spróbujemy odczytać treść błędu z odpowiedzi JSON
        const errorData = await response.json().catch(() => null);
        const errorMessage = errorData?.message || `Błąd serwera: ${response.status}`;
        throw new Error(errorMessage);
    }
    return response.json();
}

/**
 * Wysyła odpowiedź użytkownika do sprawdzenia.
 *
 * @param {number|string} questionId - ID pytania.
 * @param {number|string} answerId - ID odpowiedzi wybranej przez użytkownika.
 * @returns {Promise<object>} - Obietnica z wynikiem sprawdzenia.
 */
export async function checkAnswer(questionId, answerId) {
    const response = await fetch(`${API_BASE_URL}/check-answer`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `question_id=${questionId}&answer_id=${answerId}`
    });

    if (!response.ok) {
        const errorData = await response.json().catch(() => null);
        const errorMessage = errorData?.message || `Błąd serwera: ${response.status}`;
        throw new Error(errorMessage);
    }
    return response.json();
}