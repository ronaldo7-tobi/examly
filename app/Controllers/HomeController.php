<?php 

/**
 * Kontroler Strony Głównej.
 *
 * Odpowiada za logikę i wyświetlanie głównej strony aplikacji.
 *
 * @version 1.0.0
 * @author Tobiasz Szerszeń
 */
class HomeController extends BaseController
{
    /**
     * Wyświetla widok strony głównej.
     * @return void
     */
    public function show(): void
    {
        $this->renderView('home');
    }
}