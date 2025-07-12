<?php
/**
 * Klasa reprezentująca użytkownika w systemie.
 * 
 * Przechowuje podstawowe informacje o użytkowniku pobrane z bazy danych,
 * takie jak imię, nazwisko, e-mail, status weryfikacji oraz rola użytkownika.
 */
class User
{
    /**
     * Identyfikator użytkownika w bazie danych.
     * 
     * @var int
     */
    private int $id;

    /**
     * Imię użytkownika.
     * 
     * @var string
     */
    private string $firstName;

    /**
     * Nazwisko użytkownika.
     * 
     * @var string
     */
    private string $lastName;

    /**
     * Adres e-mail użytkownika.
     * 
     * @var string
     */
    private string $email;

    /**
     * Status weryfikacji konta użytkownika.
     * 
     * @var bool
     */
    private bool $isVerified;

    /**
     * Rola użytkownika (np. admin, user).
     * 
     * @var string
     */
    private string $role;

    /**
     * Konstruktor klasy User.
     * 
     * Inicjalizuje właściwości obiektu na podstawie tablicy danych.
     * 
     * @param array $data Tablica danych z bazy danych z kluczami: id, first_name, last_name, email, is_verified, role.
     */
    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->firstName = $data['first_name'];
        $this->lastName = $data['last_name'];
        $this->email = $data['email'];
        $this->isVerified = (bool)$data['is_verified'];
        $this->role = $data['role'];
    }

    /**
     * Zwraca id użytkownika
     * 
     * @return int Id użytkownika z bazy danych
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Zwraca pełne imię i nazwisko użytkownika.
     * 
     * @return string Imię i nazwisko połączone spacją.
     */
    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    /**
     * Zwraca adres e-mail użytkownika.
     * 
     * @return string E-mail użytkownika.
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Zwraca rolę użytkownika.
     * 
     * @return string Rola użytkownika.
     */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * Sprawdza, czy konto użytkownika jest zweryfikowane.
     * 
     * @return bool True, jeśli konto jest zweryfikowane, w przeciwnym razie false.
     */
    public function isVerified(): bool
    {
        return $this->isVerified;
    }
}
?>