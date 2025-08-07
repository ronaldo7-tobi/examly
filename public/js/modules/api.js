/**
 * @module api
 * @version 1.4.0
 * @description Moduł tworzy i eksportuje jedną, gotową do użycia, singletonową
 * instancję klienta API jako domyślny eksport. Zapewnia to spójny
 * punkt dostępu do API w całej aplikacji.
 */

/**
 * @class ApiClient
 * @classdesc Zarządza wszystkimi zapytaniami do backendu aplikacji, hermetyzując
 * logikę `fetch`, obsługę błędów i standaryzację odpowiedzi.
 * @property {string} baseUrl - Bazowy URL, do którego dołączane są wszystkie endpointy.
 */
class ApiClient {
    /**
     * @constructs ApiClient
     * @param {string} baseUrl - Podstawowy URL dla wszystkich zapytań API.
     */
    constructor(baseUrl) {
        this.baseUrl = baseUrl;
    }

    /**
     * Prywatna, scentralizowana metoda do wysyłania zapytań `fetch`.
     * Standaryzuje format odpowiedzi oraz obsługę błędów sieciowych i serwerowych.
     * @param {string} endpoint - Ścieżka endpointu API (np. '/question/INF.03').
     * @param {object} [options={}] - Opcjonalny obiekt konfiguracji dla `fetch`.
     * @returns {Promise<{success: boolean, data?: object, error?: string}>} Obiekt z wynikiem operacji.
     * @private
     */
    async _request(endpoint, options = {}) {
        try {
            const response = await fetch(this.baseUrl + endpoint, options);
            const data = await response.json();

            if (!response.ok) {
                const errorMessage = data.message || `Błąd serwera: ${response.status}`;
                return { success: false, error: errorMessage };
            }

            return { success: true, data };
        } catch (error) {
            console.error(`Błąd sieci dla endpointu ${endpoint}:`, error);
            return { success: false, error: 'Błąd połączenia. Sprawdź swoje połączenie z internetem.' };
        }
    }

    /**
     * Pobiera pojedyncze pytanie dla określonego egzaminu i kryteriów.
     * @param {string} examCode - Kod egzaminu (np. 'INF.03').
     * @param {string[]} subjects - Tablica ID wybranych tematów.
     * @param {string|null} premiumOption - Wybrana opcja premium lub null.
     * @returns {Promise<{success: boolean, data?: object, error?: string}>}
     */
    fetchQuestion(examCode, subjects, premiumOption) {
        const params = new URLSearchParams();
        subjects.forEach(subjectId => params.append('subject[]', subjectId));
        if (premiumOption) {
            params.append('premium_option', premiumOption);
        }
        const endpoint = `/question/${examCode}?${params.toString()}`;
        return this._request(endpoint);
    }

    /**
     * Pobiera pełny test egzaminacyjny (40 pytań).
     * @param {string} examCode - Kod egzaminu (np. 'INF.03').
     * @returns {Promise<{success: boolean, data?: object, error?: string}>}
     */
    fetchFullTest(examCode) {
        const endpoint = `/test/full/${examCode}`;
        return this._request(endpoint);
    }

    /**
     * Pobiera spersonalizowany zestaw pytań testowych.
     * @param {string} examCode - Kod egzaminu.
     * @param {string[]} subjects - Tablica ID tematów.
     * @param {string|null} premiumOption - Opcja premium.
     * @param {number} questionCount - Liczba pytań.
     * @returns {Promise<{success: boolean, data?: object, error?: string}>}
     */
    fetchPersonalizedTest(examCode, subjects, premiumOption, questionCount) {
        const params = new URLSearchParams();
        subjects.forEach(subjectId => params.append('subject[]', subjectId));
        if (premiumOption) {
            params.append('premium_option', premiumOption);
        }
        params.append('question_count', questionCount);
        const endpoint = `/test/personalized/${examCode}?${params.toString()}`;
        return this._request(endpoint);
    }

    /**
     * Zapisuje wynik ukończonego testu w bazie danych.
     * @param {object} resultData - Obiekt z wynikami testu.
     * @returns {Promise<{success: boolean, data?: object, error?: string}>}
     */
    saveTestResult(resultData) {
        const options = {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(resultData)
        };
        return this._request('/save-test-result', options);
    }

    /**
     * Wysyła szczegółowe wyniki (każdą odpowiedź) w celu zapisania postępu użytkownika.
     * @param {Array<object>} progressData - Tablica obiektów z odpowiedziami (np. [{questionId: 1, isCorrect: true}]).
     * @returns {Promise<{success: boolean, data?: object, error?: string}>}
     */
    saveBulkProgress(progressData) {
        const options = {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(progressData)
        };
        return this._request('/save-progress-bulk', options);
    }

    /**
     * Sprawdza poprawność wybranej odpowiedzi.
     * @param {(number|string)} questionId - ID pytania.
     * @param {(number|string)} answerId - ID odpowiedzi użytkownika.
     * @returns {Promise<{success: boolean, data?: object, error?: string}>}
     */
    checkAnswer(questionId, answerId) {
        const options = {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                'question_id': questionId,
                'answer_id': answerId
            })
        };
        return this._request('/check-answer', options);
    }
}

/**
 * Singletonowa instancja klienta API.
 * Tworzymy i eksportujemy jedną, gotową do użycia instancję,
 * aby cała aplikacja korzystała z tego samego, skonfigurowanego obiektu.
 */
const api = new ApiClient('/examly/public/api');

// Eksportujemy całą instancję jako domyślny eksport.
export default api;