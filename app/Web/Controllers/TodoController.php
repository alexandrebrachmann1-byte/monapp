<?php

declare(strict_types=1);

namespace App\Web\Controllers;

use App\DTO\TodoDTO;
use App\DTO\TodoInputDTO;
use App\Models\Todo;
use Core\Controller;
use Core\Request;
use Core\ValidationException;

final class TodoController extends Controller
{
    public function index(Request $request): void
    {
        $todos = Todo::all();
        $this->view('todos/index.twig', ['todos' => $todos]);
    }

    public function create(Request $request): void
    {
        $this->view('todos/form.twig', [
            'todo' => null,
            'action' => $this->view->url('/todos'),
        ]);
    }

    public function store(Request $request): void
    {
        try {
            $input = TodoInputDTO::fromArray($request->all());
        } catch (ValidationException $e) {
            $this->session->flash('errors', $e->errors);
            $this->session->flash('old', $request->all());
            $this->redirect('/todos/create');
        }

        Todo::insert([
            'title' => $input->title,
            'is_done' => $input->is_done ? 1 : 0,
        ]);

        $this->session->flash('success', 'Tâche créée.');
        $this->redirect('/todos');
    }

    public function show(Request $request): void
    {
        $todo = Todo::find((int) $request->param('id'));
        if ($todo === null) {
            $this->session->flash('error', 'Tâche introuvable.');
            $this->redirect('/todos');
        }

        $this->view('todos/show.twig', ['todo' => TodoDTO::fromArray($todo)]);
    }

    public function edit(Request $request): void
    {
        $todo = Todo::find((int) $request->param('id'));
        if ($todo === null) {
            $this->session->flash('error', 'Tâche introuvable.');
            $this->redirect('/todos');
        }

        $output = TodoDTO::fromArray($todo);
        $this->view('todos/form.twig', [
            'todo' => $output,
            'action' => $this->view->url('/todos/' . $output->id),
        ]);
    }

    public function update(Request $request): void
    {
        $id = (int) $request->param('id');
        if (Todo::find($id) === null) {
            $this->session->flash('error', 'Tâche introuvable.');
            $this->redirect('/todos');
        }

        $data = $request->all();
        $data['is_done'] = isset($data['is_done']);

        try {
            $input = TodoInputDTO::fromArray($data);
        } catch (ValidationException $e) {
            $this->session->flash('errors', $e->errors);
            $this->session->flash('old', $request->all());
            $this->redirect('/todos/' . $id . '/edit');
        }

        Todo::update($id, [
            'title' => $input->title,
            'is_done' => $input->is_done ? 1 : 0,
        ]);

        $this->session->flash('success', 'Tâche mise à jour.');
        $this->redirect('/todos/' . $id);
    }

    public function destroy(Request $request): void
    {
        $id = (int) $request->param('id');
        if (Todo::find($id) === null) {
            $this->session->flash('error', 'Tâche introuvable.');
            $this->redirect('/todos');
        }

        Todo::delete($id);
        $this->session->flash('success', 'Tâche supprimée.');
        $this->redirect('/todos');
    }
}
