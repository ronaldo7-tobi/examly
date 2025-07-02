let countdown = 60;
const button = document.getElementById('resendButton');
const countdownSpan = document.getElementById('countdown');

const timer = setInterval(() => {
    countdown--;
    countdownSpan.textContent = countdown;

    if (countdown <= 0) {
        clearInterval(timer);
        button.disabled = false;
        button.textContent = 'Wyślij ponownie e-mail';
    }
}, 1000);

button.addEventListener('click', () => {
    button.disabled = true;
    button.textContent = 'Wysyłanie...';

    // Przekierowanie lub AJAX (przykład z przekierowaniem):
    window.location.href = 'verify_email';
});