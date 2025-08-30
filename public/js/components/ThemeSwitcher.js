/**
 * Zarządza zmianą motywu (jasny/ciemny) i zapisuje wybór użytkownika.
 */
class ThemeSwitcher {
  constructor(checkboxId) {
    this.checkbox = document.getElementById(checkboxId);
    
    if (!this.checkbox) {
      console.error(`ThemeSwitcher: Nie znaleziono elementu o ID "${checkboxId}".`);
      return;
    }
    this.init();
  }

  init() {
    // 1. Ustaw stan suwaka zgodnie z tym, co ustawił skrypt w <head>
    this.checkbox.checked = document.documentElement.classList.contains('dark');

    // 2. Nasłuchuj na kliknięcie w przełącznik
    this.checkbox.addEventListener('change', () => {
      const newTheme = this.checkbox.checked ? 'dark' : 'light';
      this.setTheme(newTheme);
    });
  }

  /**
   * Ustawia motyw, aktualizuje klasę na <html> i zapisuje wybór w localStorage.
   * @param {'light' | 'dark'} theme - Nazwa motywu do ustawienia.
   */
  setTheme(theme) {
    // Zastosuj zmianę wizualną
    document.documentElement.classList.toggle('dark', theme === 'dark');
    
    // Zapisz wybór użytkownika w pamięci przeglądarki, aby przetrwał odświeżenie
    localStorage.setItem('theme', theme);
  }
}

// Uruchom skrypt po załadowaniu strony
document.addEventListener('DOMContentLoaded', () => {
  new ThemeSwitcher('theme-toggle-checkbox');
});