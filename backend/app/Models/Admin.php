<?php

namespace App\Models;

/**
 * Administrateur étend Utilisateur (même table users, role = admin).
 */
class Admin extends User
{
    protected $table = 'users';

    public function creerUtilisateur(array $data): User
    {
        return User::query()->create($data);
    }

    public function gererProduit(): void {}

    public function gererCategorie(): void {}

    public function gererCommande(): void {}
}
