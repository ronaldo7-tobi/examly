// Plik: public/js/topic-form-enhancer.js

/**
 * Klasa TopicFormEnhancer zarządza zaawansowaną logiką
 * formularza wyboru tematów.
 */
class TopicFormEnhancer {
    constructor(formId) {
        this.form = document.getElementById(formId);
        if (!this.form) return;

        // Pobranie kluczowych elementów formularza
        this.selectAllCheckbox = this.form.querySelector('#select-all-inf03');
        this.topicCheckboxes = this.form.querySelectorAll('.topic-checkbox');
        this.premiumCheckboxes = this.form.querySelectorAll('.premium-checkbox');
        
        this.isUserLoggedIn = window.examlyAppState?.isUserLoggedIn || false;

        this.bindEvents();
        this.updateFormState(); // Zmieniono nazwę dla większej jasności
    }

    /**
     * Wiąże wszystkie potrzebne zdarzenia z elementami formularza.
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
     * Obsługuje kliknięcie w checkbox "Cały materiał INF.03".
     * Zaznacza lub odznacza wszystkie podkategorie, ale ich nie blokuje.
     */
    handleSelectAll() {
        const isChecked = this.selectAllCheckbox.checked;
        this.topicCheckboxes.forEach(cb => {
            cb.checked = isChecked;
            // Usunęliśmy blokowanie checkboxów!
        });
        this.updatePremiumOptionsState();
    }

    /**
     * Aktualizuje stan checkboxa "Cały materiał" w zależności od stanu podkategorii.
     */
    handleSingleTopicChange() {
        // Sprawdza, czy wszystkie podkategorie są zaznaczone
        const allChecked = [...this.topicCheckboxes].every(cb => cb.checked);
        this.selectAllCheckbox.checked = allChecked;
        
        this.updatePremiumOptionsState();
    }
    
    /**
     * Zapewnia, że tylko jedna opcja premium może być aktywna w danym momencie.
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
     * Aktualizuje stan całego formularza, w tym opcji premium.
     */
    updateFormState() {
        this.handleSingleTopicChange(); // Aktualizuje "Zaznacz wszystko" na starcie
        this.updatePremiumOptionsState(); // Aktualizuje opcje premium na starcie
    }

    /**
     * Zarządza stanem opcji premium na podstawie logowania i wyboru tematów.
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

// Inicjalizujemy klasę po załadowaniu drzewa DOM
document.addEventListener('DOMContentLoaded', () => {
    new TopicFormEnhancer('topic-form');
});