// Plik: public/js/modules/api.js

const API_BASE_PATH = '/examly/public/api'; // Centralne miejsce na ścieżkę do API

/**
 * Pobiera nowe pytanie z serwera na podstawie wybranych tematów.
 * @param {string[]} subjects - Tablica z nazwami tematów.
 * @returns {Promise<Object>} - Obietnica, która zwróci dane pytania.
 */
export async function fetchQuestion(subjects) {
    const formData = new FormData();
    subjects.forEach(subject => formData.append('subjects[]', subject));

    const response = await fetch(`${API_BASE_PATH}/get-question`, {
        method: 'POST',
        body: formData
    });

    if (!response.ok) {
        throw new Error('Błąd sieci podczas pobierania pytania.');
    }
    return response.json();
}

/**
 * Wysyła odpowiedź użytkownika do sprawdzenia.
 * @param {number|string} questionId - ID pytania.
 * @param {number|string} answerId - ID wybranej odpowiedzi.
 * @returns {Promise<Object>} - Obietnica, która zwróci wynik sprawdzenia.
 */
export async function checkAnswer(questionId, answerId) {
    const formData = new FormData();
    formData.append('question_id', questionId);
    formData.append('answer_id', answerId);

    const response = await fetch(`${API_BASE_PATH}/check-answer`, {
        method: 'POST',
        body: formData
    });

    if (!response.ok) {
        throw new Error('Błąd sieci podczas sprawdzania odpowiedzi.');
    }
    return response.json();
}