/**
 * @file Reużywalny komponent do wyświetlania powiadomień typu "toast".
 * @module Toast
 * @version 1.2.0
 */
class Toast {
    constructor() {
        this.container = null;
        // Ustawiamy maksymalną liczbę widocznych powiadomień
        this.maxToasts = 3; 
    }

    /**
     * Wyświetla nowe powiadomienie z limitem.
     * @param {string} message - Treść wiadomości.
     * @param {string} [type='info'] - Typ powiadomienia ('info', 'success', 'error').
     * @param {number} [duration=5000] - Czas wyświetlania w milisekundach.
     */
    show(message, type = 'info', duration = 5000) {
        this.container = document.getElementById('toast-container');
        
        if (!this.container) {
            console.error('Błąd krytyczny: Nie znaleziono elementu #toast-container w DOM.');
            return;
        }

        // --- NOWA LOGIKA OGRANICZAJĄCA ---
        // Pętla while upewnia się, że usuniemy wystarczająco dużo starych powiadomień,
        // jeśli jakimś cudem pojawi się ich więcej niż limit.
        while (this.container.children.length >= this.maxToasts) {
            // Usuwamy najstarsze powiadomienie, czyli pierwsze dziecko kontenera.
            this.container.removeChild(this.container.firstChild);
        }
        // --- KONIEC NOWEJ LOGIKI ---

        const toast = document.createElement('div');
        toast.className = `toast toast--${type}`;
        
        const icons = {
            info: 'fas fa-info-circle',
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-circle'
        };

        toast.innerHTML = `
            <i class="toast__icon ${icons[type]}"></i>
            <p class="toast__message">${message}</p>
            <button type="button" class="toast__close">&times;</button>
        `;

        this.container.appendChild(toast);

        const closeButton = toast.querySelector('.toast__close');
        closeButton.addEventListener('click', () => toast.remove());

        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, duration);
    }
}

// Eksportujemy jedną, gotową do użycia instancję
export default new Toast();