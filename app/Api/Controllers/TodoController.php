<?php

declare(strict_types=1);

namespace App\Api\Controllers;

use App\DTO\TodoDTO;
use App\DTO\TodoInputDTO;
use App\Models\Todo;
use Core\ApiController;
use Core\Request;
use Core\ValidationException;

final class TodoController extends ApiController
{
    public function index(Request $request): void
    {
        $data = [];
        foreach (Todo::all() as $row) {
            $data[] = TodoDTO::fromArray($row);
        }
        $this->json(['data' => $data]);
    }

    public function show(Request $request): void
    {
        $todo = Todo::find((int) $request->param('id'));
        if ($todo === null) {
            $this->error('Tâche introuvable', 404);

            return;
        }

        $this->json(['data' => TodoDTO::fromArray($todo)]);
    }

    public function store(Request $request): void
    {
        try {
            $input = TodoInputDTO::fromArray($request->all());
        } catch (ValidationException $e) {
            $this->json(['error' => 'Validation échouée', 'status' => 422, 'errors' => $e->errors], 422);

            return;
        }

        $id = Todo::insert([
            'title' => $input->title,
            'is_done' => $input->is_done ? 1 : 0,
        ]);

        $this->json(['data' => TodoDTO::fromArray(Todo::find($id))], 201);
    }

    public function update(Request $request): void
    {
        $id = (int) $request->param('id');
        if (Todo::find($id) === null) {
            $this->error('Tâche introuvable', 404);

            return;
        }

        try {
            $input = TodoInputDTO::fromArray($request->all());
        } catch (ValidationException $e) {
            $this->json(['error' => 'Validation échouée', 'status' => 422, 'errors' => $e->errors], 422);

            return;
        }

        Todo::update($id, [
            'title' => $input->title,
            'is_done' => $input->is_done ? 1 : 0,
        ]);

        $this->json(['data' => TodoDTO::fromArray(Todo::find($id))]);
    }

    public function destroy(Request $request): void
    {
        $id = (int) $request->param('id');
        if (Todo::find($id) === null) {
            $this->error('Tâche introuvable', 404);

            return;
        }

        Todo::delete($id);
        $this->json(['deleted' => true]);
    }
}
