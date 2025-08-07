/**
 * @module topicFormEnhancer
 * @description Skrypt, który wzbogaca formularz wyboru tematów o dodatkową interaktywność,
 * taką jak "zaznacz wszystko" oraz dynamiczne zarządzanie opcjami premium.
 */

/**
 * @class TopicFormEnhancer
 * @classdesc Zarządza zaawansowaną logiką interfejsu użytkownika
 * dla formularza wyboru tematów quizu.
 */
class TopicFormEnhancer {
    /**
     * @param {string} formId - ID elementu formularza, który ma zostać wzbogacony.
     */
    constructor(formId) {
        /**
         * Element DOM formularza.
         * @type {HTMLFormElement|null}
         * @private
         */
        this.form = document.getElementById(formId);
        if (!this.form) return;

        /**
         * Checkbox "Zaznacz wszystko".
         * @type {HTMLInputElement}
         * @private
         */
        this.selectAllCheckbox = this.form.querySelector('#select-all-inf03');
        
        /**
         * Lista wszystkich checkboxów z kategoriami tematycznymi.
         * @type {NodeListOf<HTMLInputElement>}
         * @private
         */
        this.topicCheckboxes = this.form.querySelectorAll('.topic-checkbox');

        /**
         * Lista wszystkich checkboxów z opcjami premium.
         * @type {NodeListOf<HTMLInputElement>}
         * @private
         */
        this.premiumCheckboxes = this.form.querySelectorAll('.premium-checkbox');
        
        /**
         * Stan zalogowania użytkownika, pobierany z globalnego obiektu.
         * @type {boolean}
         * @private
         */
        this.isUserLoggedIn = window.examlyAppState?.isUserLoggedIn || false;

        this.bindEvents();
        this.updateFormState();
    }

    /**
     * Wiąże wszystkie potrzebne zdarzenia z elementami formularza.
     * @private
     * @returns {void}
     */
    bindEvents() {
        this.selectAllCheckbox.addEventListener('change', () => this.handleSelectAll());
        
        this.topicCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => this.handleSingleTopicChange());
        });

        this.premiumCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', (e) => this.handlePremiumChange(e.target));
        });
    }

    /**
     * Obsługuje zmianę stanu checkboxa "Zaznacz wszystko".
     * Synchronizuje stan wszystkich podrzędnych checkboxów tematycznych.
     * @private
     * @returns {void}
     */
    handleSelectAll() {
        const isChecked = this.selectAllCheckbox.checked;
        this.topicCheckboxes.forEach(cb => {
            cb.checked = isChecked;
        });
        this.updatePremiumOptionsState();
    }

    /**
     * Obsługuje zmianę stanu pojedynczego checkboxa tematycznego.
     * Aktualizuje stan głównego checkboxa "Zaznacz wszystko".
     * @private
     * @returns {void}
     */
    handleSingleTopicChange() {
        const allChecked = [...this.topicCheckboxes].every(cb => cb.checked);
        this.selectAllCheckbox.checked = allChecked;
        this.updatePremiumOptionsState();
    }
    
    /**
     * Zapewnia, że tylko jedna opcja premium może być aktywna w danym momencie.
     * @private
     * @param {HTMLInputElement} changedCheckbox - Checkbox, który właśnie został zmieniony.
     * @returns {void}
     */
    handlePremiumChange(changedCheckbox) {
        if (changedCheckbox.checked) {
            this.premiumCheckboxes.forEach(cb => {
                if (cb !== changedCheckbox) {
                    cb.checked = false;
                }
            });
        }
    }

    /**
     * Ustawia początkowy, poprawny stan całego formularza przy ładowaniu strony.
     * @private
     * @returns {void}
     */
    updateFormState() {
        this.handleSingleTopicChange();
        this.updatePremiumOptionsState();
    }

    /**
     * Zarządza stanem (włączony/wyłączony) opcji premium.
     * Opcje te są aktywne tylko dla zalogowanych użytkowników, którzy wybrali co najmniej jeden temat.
     * @private
     * @returns {void}
     */
    updatePremiumOptionsState() {
        const anyTopicSelected = [...this.topicCheckboxes].some(cb => cb.checked);

        this.premiumCheckboxes.forEach(checkbox => {
            const label = checkbox.closest('.topic-selector__label');
            const isEnabled = this.isUserLoggedIn && anyTopicSelected;

            checkbox.disabled = !isEnabled;
            
            if (label) {
                if (this.isUserLoggedIn) {
                    label.title = isEnabled ? 'Wybierz, aby filtrować pytania' : 'Najpierw wybierz kategorię tematyczną.';
                } else {
                    label.title = 'Ta opcja jest dostępna tylko dla zalogowanych użytkowników.';
                }
                label.style.cursor = isEnabled ? 'pointer' : 'not-allowed';
                label.style.opacity = isEnabled ? '1' : '0.6';
            }
            
            if (!isEnabled) {
                checkbox.checked = false;
            }
        });
    }
}

/**
 * Punkt wejściowy skryptu.
 * Po załadowaniu struktury DOM, tworzy instancję klasy `TopicFormEnhancer`,
 * aby aktywować zaawansowaną logikę formularza.
 */
document.addEventListener('DOMContentLoaded', () => {
    new TopicFormEnhancer('topic-form');
});