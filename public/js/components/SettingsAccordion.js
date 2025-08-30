/**
 * Zarządza działaniem akordeonu na stronie ustawień.
 */
class SettingsAccordion {
  constructor(accordionContainerId) {
    this.container = document.getElementById(accordionContainerId);
    if (!this.container) return;

    this.cards = this.container.querySelectorAll('.settings-card');
    this.init();
  }

  init() {
    this.cards.forEach(card => {
      const header = card.querySelector('.settings-card__header');
      header.addEventListener('click', () => this.toggleCard(card));
    });
  }

  toggleCard(clickedCard) {
    const isOpen = clickedCard.classList.contains('is-open');

    // Najpierw zamknij wszystkie otwarte karty
    this.cards.forEach(card => card.classList.remove('is-open'));

    // Jeśli kliknięta karta nie była już otwarta, otwórz ją
    if (!isOpen) {
      clickedCard.classList.add('is-open');
    }
  }
}

document.addEventListener('DOMContentLoaded', () => {
  new SettingsAccordion('settings-accordion');
});