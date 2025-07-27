<?php 
/**
 * Rejestruje funkcję autoload, która automatycznie dołącza klasy z katalogów Controllers, Core, Models i Services.
 * 
 * @version 1.0.0
 * @author Tobiasz Szerszeń
 * 
 * @return void
 */
spl_autoload_register(function ($class): void{
    $paths = [
        __DIR__ . '/../Controllers/',
        __DIR__ . '/', // Core
        __DIR__ . '/../Models/',
        __DIR__ . '/../Services/'
    ];

    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if(file_exists($file)) {
            require_once $file;
            return;
        }
    }
});