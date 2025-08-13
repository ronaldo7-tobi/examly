/**
 * @file Reużywalny komponent do wyświetlania powiadomień typu "toast".
 * @module Toast
 */
class Toast {
    constructor() {
        this.container = document.getElementById('toast-container');
    }

    /**
     * Wyświetla nowe powiadomienie.
     * @param {string} message - Treść wiadomości.
     * @param {string} [type='info'] - Typ powiadomienia ('info', 'success', 'error').
     * @param {number} [duration=5000] - Czas wyświetlania w milisekundach.
     */
    show(message, type = 'info', duration = 5000) {
        if (!this.container) return;

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
            toast.remove();
        }, duration);
    }
}

// Eksportujemy jedną, gotową do użycia instancję
export default new Toast();