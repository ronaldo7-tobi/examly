/**
 * @module api
 * @description Moduł do komunikacji z API, wykorzystujący scentralizowaną funkcję `apiClient`
 * do obsługi wszystkich zapytań. Zapewnia to spójność i łatwość w utrzymaniu.
 */

/**
 * Bazowy URL dla wszystkich zapytań do API.
 * @type {string}
 * @private
 * @constant
 */
const API_BASE_URL = '/examly/public/api';

/**
 * Scentralizowana funkcja do wysyłania zapytań do API.
 * Obsługuje cały cykl życia żądania: wysłanie, parsowanie odpowiedzi oraz
 * ujednoliconą obsługę błędów sieciowych i serwerowych.
 *
 * @private
 * @param {string} endpoint - Ścieżka endpointu API (np. '/get-question').
 * @param {object} [options={}] - Opcjonalny obiekt konfiguracji dla `fetch` (np. method, headers, body).
 * @returns {Promise<{success: boolean, data?: object, error?: string}>} Obiekt z wynikiem operacji.
 */
async function apiClient(endpoint, options = {}) {
    try {
        const response = await fetch(API_BASE_URL + endpoint, options);
        
        // Zawsze próbujemy parsować JSON, aby uzyskać ewentualne komunikaty o błędach.
        const data = await response.json(); 

        if (!response.ok) {
            const errorMessage = data.message || `Błąd serwera: ${response.status}`;
            return { success: false, error: errorMessage };
        }

        return { success: true, data: data };

    } catch (error) {
        console.error(`Błąd sieci dla endpointu ${endpoint}:`, error);
        return { success: false, error: 'Błąd połączenia. Sprawdź swoje połączenie z internetem.' };
    }
}

/**
 * Przygotowuje i wysyła zapytanie o nowe pytanie do API.
 * Wykorzystuje `apiClient` do faktycznego wykonania żądania.
 *
 * @param {string[]} subjects - Tablica z nazwami wybranych tematów.
 * @param {string|null} premiumOption - Wybrana opcja premium lub null.
 * @returns {Promise<{success: boolean, data?: object, error?: string}>} Obietnica, która rozwiązuje się do obiektu zwróconego przez `apiClient`.
 */
export function fetchQuestion(subjects, premiumOption) {
    const params = new URLSearchParams();
    subjects.forEach(subject => params.append('subject[]', subject));
    if (premiumOption) {
        params.append('premium_option', premiumOption);
    }
    
    // Delegujemy wykonanie żądania do scentralizowanej funkcji.
    return apiClient(`/get-question?${params.toString()}`);
}

/**
 * Przygotowuje i wysyła odpowiedź użytkownika do sprawdzenia.
 * Wykorzystuje `apiClient` do faktycznego wykonania żądania.
 *
 * @param {number|string} questionId - ID pytania.
 * @param {number|string} answerId - ID odpowiedzi użytkownika.
 * @returns {Promise<{success: boolean, data?: object, error?: string}>} Obietnica, która rozwiązuje się do obiektu zwróconego przez `apiClient`.
 */
export function checkAnswer(questionId, answerId) {
    const options = {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `question_id=${questionId}&answer_id=${answerId}`
    };
    
    // Delegujemy wykonanie żądania do scentralizowanej funkcji.
    return apiClient('/check-answer', options);
}