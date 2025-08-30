class CountdownButton {
  constructor(buttonId) {
    this.button = document.getElementById(buttonId);
    if (!this.button) return;

    this.remaining = parseInt(this.button.dataset.remaining || '0', 10);
    this.countdownSpan = this.button.querySelector('#countdown');
    this.originalText = this.button.dataset.text || this.button.textContent.trim();

    if (this.remaining > 0) {
      this.startCountdown();
    }
  }

  startCountdown() {
    this.button.disabled = true;

    const timer = setInterval(() => {
      this.remaining--;
      if (this.countdownSpan) {
        this.countdownSpan.textContent = this.remaining;
      }

      if (this.remaining <= 0) {
        clearInterval(timer);
        this.button.disabled = false;
        this.button.innerHTML = this.originalText;
      }
    }, 1000);
  }
}

export default CountdownButton;