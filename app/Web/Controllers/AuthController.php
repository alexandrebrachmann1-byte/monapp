<?php

declare(strict_types=1);

namespace App\Web\Controllers;

use App\DTO\LoginDTO;
use App\DTO\RegisterDTO;
use App\Models\User;
use Core\Controller;
use Core\Request;
use Core\ValidationException;

final class AuthController extends Controller
{
    public function showLogin(Request $request): void
    {
        if ($this->auth->check()) {
            $this->redirect('/users');
        }
        $this->view('auth/login.twig');
    }

    public function login(Request $request): void
    {
        try {
            $input = LoginDTO::fromArray($request->all());
        } catch (ValidationException $e) {
            $this->session->flash('errors', $e->errors);
            $this->session->flash('old', $request->all());
            $this->redirect('/login');
        }

        if (!$this->auth->attempt($input->email, $input->password)) {
            $this->session->flash('error', 'Email ou mot de passe incorrect.');
            $this->session->flash('old', ['email' => $input->email]);
            $this->redirect('/login');
        }

        $this->session->flash('success', 'Vous êtes connecté.');
        $this->redirect('/users');
    }

    public function showRegister(Request $request): void
    {
        if ($this->auth->check()) {
            $this->redirect('/users');
        }
        $this->view('auth/register.twig');
    }

    public function register(Request $request): void
    {
        try {
            $input = RegisterDTO::fromArray($request->all());
        } catch (ValidationException $e) {
            $this->session->flash('errors', $e->errors);
            $this->session->flash('old', $request->all());
            $this->redirect('/register');
        }

        $emailExists = User::table()->where('email', '=', $input->email)->first();
        if ($emailExists !== null) {
            $this->session->flash('error', 'Cet email est déjà utilisé.');
            $this->session->flash('old', $request->all());
            $this->redirect('/register');
        }

        $usernameExists = User::table()->where('username', '=', $input->username)->first();
        if ($usernameExists !== null) {
            $this->session->flash('error', "Ce nom d'utilisateur est déjà utilisé.");
            $this->session->flash('old', $request->all());
            $this->redirect('/register');
        }

        $id = User::insert([
            'username' => $input->username,
            'email' => $input->email,
            'password_hash' => password_hash($input->password, PASSWORD_DEFAULT),
        ]);

        $user = User::find($id);
        if ($user !== null) {
            $this->auth->login($user);
        }

        $this->session->flash('success', 'Compte créé, vous êtes connecté.');
        $this->redirect('/users');
    }

    public function logout(Request $request): void
    {
        $this->auth->logout();
        $this->session->flash('success', 'Vous êtes déconnecté.');
        $this->redirect('/login');
    }
}
