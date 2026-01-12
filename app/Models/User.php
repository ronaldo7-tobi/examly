<?php

namespace App\Models;

class User
{
  private int $id;
  private ?string $googleId;
  private string $authProvider;
  private string $firstName;
  private string $lastName;
  private string $email;
  private bool $isVerified;
  private bool $isActive;
  private string $role;

  public function __construct(array $data)
  {
    $this->id = (int)$data['id'];
    $this->googleId = $data['google_id'] ?? null;
    $this->authProvider = $data['auth_provider'] ?? 'local';
    $this->firstName = $data['first_name'];
    $this->lastName = $data['last_name'];
    $this->email = $data['email'];
    $this->isVerified = (bool)$data['is_verified'];
    $this->isActive = (bool)($data['is_active'] ?? true);
    $this->role = $data['role'];
  }

  public function getId(): int { return $this->id; }
  public function getGoogleId(): ?string { return $this->googleId; }
  public function getAuthProvider(): string { return $this->authProvider; }
  public function getFirstName(): string { return $this->firstName; }
  public function getLastName(): string { return $this->lastName; }
  public function getFullName(): string { return "{$this->firstName} {$this->lastName}"; }
  public function getEmail(): string { return $this->email; }
  public function getRole(): string { return $this->role; }
  public function isVerified(): bool { return $this->isVerified; }
  public function isActive(): bool { return $this->isActive; }
}