<?php

declare(strict_types=1);

namespace App\Web\Controllers;

use App\Models\User;
use Core\Controller;
use Core\Request;

final class UserController extends Controller
{
    public function index(Request $request): void
    {
        $users = User::table()
            ->select('id', 'username', 'email', 'created_at', 'updated_at')
            ->orderBy('created_at', 'DESC')
            ->get();

        $this->view('users/index.twig', ['users' => $users]);
    }
}
