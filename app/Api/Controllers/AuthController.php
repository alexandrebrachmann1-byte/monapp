<?php

declare(strict_types=1);

namespace App\Api\Controllers;

use App\DTO\LoginDTO;
use App\DTO\RegisterDTO;
use App\DTO\UserDTO;
use App\Models\User;
use Core\ApiController;
use Core\Request;
use Core\ValidationException;

final class AuthController extends ApiController
{
    public function register(Request $request): void
    {
        try {
            $input = RegisterDTO::fromArray($request->all());
        } catch (ValidationException $e) {
            $this->json(['error' => 'Validation échouée', 'status' => 422, 'errors' => $e->errors], 422);

            return;
        }

        $emailExists = User::table()->where('email', '=', $input->email)->first();
        if ($emailExists !== null) {
            $this->error('Cet email est déjà utilisé.', 422);

            return;
        }

        $usernameExists = User::table()->where('username', '=', $input->username)->first();
        if ($usernameExists !== null) {
            $this->error("Ce nom d'utilisateur est déjà utilisé.", 422);

            return;
        }

        $id = User::insert([
            'username' => $input->username,
            'email' => $input->email,
            'password_hash' => password_hash($input->password, PASSWORD_DEFAULT),
        ]);
        $user = User::find($id);
        $token = $this->auth->issueToken($id);

        $this->json([
            'user' => UserDTO::fromArray($user),
            'token' => $token,
        ], 201);
    }

    public function login(Request $request): void
    {
        try {
            $input = LoginDTO::fromArray($request->all());
        } catch (ValidationException $e) {
            $this->json(['error' => 'Validation échouée', 'status' => 422, 'errors' => $e->errors], 422);

            return;
        }

        $row = User::table()->where('email', '=', $input->email)->first();
        if ($row === null || !password_verify($input->password, (string) $row['password_hash'])) {
            $this->error('Email ou mot de passe incorrect.', 401);

            return;
        }

        $token = $this->auth->issueToken((int) $row['id']);

        $this->json([
            'user' => UserDTO::fromArray($row),
            'token' => $token,
        ]);
    }
}
