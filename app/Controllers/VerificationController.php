<?php

/**
 * Kontroler Weryfikacji.
 *
 * Odpowiada za logikę wyświetlania widoku weryfikacyjnego tokena.
 * 
 * @version 1.0.0
 * @author Tobiasz Szerszeń
 */
class VerificationController extends BaseController
{
    /**
     * Wyświetla stronę weryfikacji i wykonuje logikę zawartą w pliku widoku.
     * @return void
     */
    public function handle(): void
    {
        require_once __DIR__ . '/../../views/verify.php';
    }
}