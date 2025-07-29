/**
 * @module api
 * @description Moduł do komunikacji z API, wykorzystujący scentralizowaną funkcję `apiClient`.
 * Wersja 1.1.0 wprowadza dynamiczne endpointy, które przyjmują kod egzaminu
 * bezpośrednio w adresie URL, co zwiększa elastyczność i skalowalność.
 * @version 1.1.0
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
 * @param {string} endpoint - Ścieżka endpointu API (np. '/question/INF.03').
 * @param {object} [options={}] - Opcjonalny obiekt konfiguracji dla `fetch`.
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
 * Przygotowuje i wysyła zapytanie o nowe pytanie do API dla konkretnego egzaminu.
 *
 * @param {string} examCode - Kod egzaminu (np. 'INF.03'), który będzie częścią URL.
 * @param {string[]} subjects - Tablica z numerycznymi ID wybranych tematów.
 * @param {string|null} premiumOption - Wybrana opcja premium lub null.
 * @returns {Promise<{success: boolean, data?: object, error?: string}>} Obietnica z wynikiem zapytania.
 */
export function fetchQuestion(examCode, subjects, premiumOption) {
    const params = new URLSearchParams();
    subjects.forEach(subjectId => params.append('subject[]', subjectId));
    if (premiumOption) {
        params.append('premium_option', premiumOption);
    }
    
    // Budujemy nowy, dynamiczny URL, np. /api/question/INF.03?subject[]=1
    const endpoint = `/question/${examCode}?${params.toString()}`;
    return apiClient(endpoint);
}

/**
 * Przygotowuje i wysyła zapytanie o pełny test dla konkretnego egzaminu.
 *
 * @param {string} examCode - Kod egzaminu (np. 'INF.03').
 * @returns {Promise<{success: boolean, data?: object, error?: string}>} Obietnica z wynikiem zapytania.
 */
export function fetchFullTest(examCode) {
    const endpoint = `/test/full/${examCode}`;
    return apiClient(endpoint);
}

/**
 * Zapisuje wynik ukończonego testu w bazie danych.
 *
 * @param {object} resultData - Obiekt z wynikami testu.
 * @returns {Promise<{success: boolean, data?: object, error?: string}>}
 */
export function saveTestResult(resultData) {
    const options = {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(resultData)
    };
    
    return apiClient('/save-test-result', options);
}

/**
 * Przygotowuje i wysyła odpowiedź użytkownika do sprawdzenia.
 * Ten endpoint jest uniwersalny i nie wymaga kodu egzaminu.
 *
 * @param {number|string} questionId - ID pytania.
 * @param {number|string} answerId - ID odpowiedzi użytkownika.
 * @returns {Promise<{success: boolean, data?: object, error?: string}>} Obietnica z wynikiem zapytania.
 */
export function checkAnswer(questionId, answerId) {
    const options = {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            'question_id': questionId,
            'answer_id': answerId
        })
    };
    
    return apiClient('/check-answer', options);
}