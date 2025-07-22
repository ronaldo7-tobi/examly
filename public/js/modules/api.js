// Plik: public/js/modules/api.js
export const IMAGE_BASE_PATH = '/examly/public/images/questions/';
const API_BASE_URL = '/examly/public/api';

/**
 * Pobiera pytanie z serwera na podstawie wybranych tematów.
 * Używa teraz metody GET, zgodnie z najlepszymi praktykami.
 * * @param {string[]} subjects - Tablica z wybranymi tematami.
 * @returns {Promise<object>} - Obietnica z danymi pytania.
 */
export async function fetchQuestion(subjects) {
    // 1. Tworzymy parametry, które dodamy do adresu URL
    const params = new URLSearchParams();
    subjects.forEach(subject => params.append('subject[]', subject));

    // 2. Budujemy pełny adres URL razem z naszymi parametrami
    const url = `${API_BASE_URL}/get-question?${params.toString()}`;

    // 3. Wysyłamy żądanie metodą GET
    const response = await fetch(url, {
        method: 'GET', // Zmienione z 'POST' na 'GET'
        headers: {
            'Accept': 'application/json',
            // Nie potrzebujemy już 'Content-Type', bo nie wysyłamy body
        },
        // Nie ma już obiektu 'body'
    });

    if (!response.ok) {
        throw new Error(`Błąd serwera: ${response.status}`);
    }
    return response.json();
}

/**
 * Wysyła odpowiedź użytkownika do sprawdzenia.
 * Ta funkcja pozostaje bez zmian (używa POST, co jest poprawne).
 * * @param {number|string} questionId - ID pytania.
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
        throw new Error(`Błąd serwera: ${response.status}`);
    }
    return response.json();
}