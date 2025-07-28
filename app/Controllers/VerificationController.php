<?php

/**
 * Kontroler Weryfikacji Adresu E-mail.
 *
 * Zarządza logiką walidacji tokenu wysłanego na adres e-mail użytkownika
 * w celu aktywacji konta. Odpowiada za cały proces od odebrania tokenu
 * aż po finalizację weryfikacji lub obsługę błędu.
 *
 * @version 1.1.0
 * @author Tobiasz Szerszeń
 */
class VerificationController extends BaseController
{
    /**
     * Instancja modelu użytkownika do interakcji z tabelą użytkowników.
     * @var UserModel
     */
    private UserModel $userModel;

    /**
     * Instancja serwisu do obsługi logiki tokenów.
     * @var TokenService
     */
    private TokenService $tokenService;

    /**
     * Konstruktor kontrolera.
     *
     * Inicjalizuje nadrzędny BaseController oraz wstrzykuje niezbędne
     * zależności, takie jak UserModel i TokenService.
     */
    public function __construct()
    {
        parent::__construct();
        $this->userModel = new UserModel();
        $this->tokenService = new TokenService();
    }

    /**
     * Główna metoda obsługująca proces weryfikacji.
     *
     * Pobiera token z adresu URL, waliduje go, a następnie albo aktywuje
     * konto użytkownika i przekierowuje go na stronę logowania z komunikatem
     * o sukcesie, albo wyświetla stronę z informacją o błędzie.
     *
     * @return void
     */
    public function handle(): void
    {
        // Jeśli użytkownik jest już zalogowany, nie ma potrzeby weryfikacji.
        // Przekieruj go na stronę główną.
        if ($this->isUserLoggedIn) {
            header('Location: /');
            exit;
        }

        // Pobierz token z parametrów GET. Użyj operatora ?? dla bezpieczeństwa.
        $token = $_GET['token'] ?? null;

        // Jeśli token nie został przekazany w URL, obsłuż błąd.
        if (!$token) {
            $this->renderErrorView('Brak tokenu weryfikacyjnego.');
            return; // Zakończ dalsze wykonywanie skryptu.
        }
        
        // Pobierz rekord tokenu z bazy danych.
        $tokenRecord = $this->tokenService->getTokenRecord($token);

        // Sprawdź, czy token istnieje lub czy nie wygasł.
        if (!$tokenRecord || strtotime($tokenRecord['expires_at']) < time()) {
            $this->renderErrorView('Nieprawidłowy lub wygasły token. Spróbuj wysłać link ponownie.');
            return;
        }
        
        // Jeśli token jest prawidłowy, spróbuj zweryfikować użytkownika.
        if ($this->userModel->verifyUser($tokenRecord['user_id'])) {
            // Po udanej weryfikacji usuń użyty token (i inne tego typu), aby zachować porządek.
            $this->tokenService->deleteTokensForUserByType($tokenRecord['user_id'], 'email_verify');

            // Ustaw jednorazowy komunikat (flash message) dla strony logowania.
            $_SESSION['flash_message'] = [
                'type' => 'success', // 'success' lub 'info' dla stylizacji komunikatu
                'text' => 'Adres e-mail został pomyślnie zweryfikowany! Możesz się teraz zalogować.'
            ];

            // Przekieruj użytkownika na stronę logowania, aby mógł sfinalizować proces.
            header('Location: /login');
            exit;
        } else {
            // Obsłuż rzadki przypadek, gdyby aktualizacja w bazie danych się nie powiodła.
            $this->renderErrorView('Wystąpił nieoczekiwany błąd podczas aktywacji konta.');
        }
    }

    /**
     * Pomocnicza metoda do renderowania widoku błędu.
     *
     * Centralizuje logikę wyświetlania strony błędu, aby uniknąć powtarzania kodu.
     *
     * @param string $message Komunikat błędu do wyświetlenia.
     * @return void
     */
    private function renderErrorView(string $message): void
    {
        $this->renderView('verify', [
            'status' => 'error',
            'message' => $message
        ]);
    }
}