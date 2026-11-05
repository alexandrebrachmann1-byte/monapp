<?php

declare(strict_types=1);

namespace App\Web\Controllers;
use App\DTO\TeamDTO;
use App\DTO\TeamInputDTO;
use App\Models\Team;
use Core\Controller;
use Core\Request;
use Core\ValidationException;

final class TeamController extends Controller
{
    public function index(Request $request): void
    {
        $teams = Team::query(
            '
                SELECT teams.id, teams.name, COUNT(userteam.id) AS members_count
                FROM teams
                LEFT JOIN userteam ON userteam.team_id = teams.id
                GROUP BY teams.id, teams.name
                ORDER BY teams.name ASC
            '
        );

        $this->view('teams/index.twig', ['teams' => $teams]);
    }

    public function create(Request $request): void
    {
        $this->view('teams/add.twig', [
            'action' => $this->view->url('/teams'),
        ]);
    }

    public function store(Request $request): void
    {
        try {
            $input = TeamInputDTO::fromArray($request->all());
        } catch (ValidationException $e) {
            $this->session->flash('errors', $e->errors);
            $this->session->flash('old', $request->all());
            $this->redirect('/teams/create');
        }
        
        $name = trim($input->name);
        if ($name === '') {
            $this->session->flash('error', "Le nom d'équipe est obligatoire.");
            $this->session->flash('old', $request->all());
            $this->redirect('/teams/create');
        }

        $now = date('Y-m-d H:i:s');
        $id = Team::insert([
            'name' => $name,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->session->flash('success', 'Equipe créée.');
        $this->redirect('/teams/' . $id);

    }

    public function show(Request $request): void
    {
        $team = Team::find((int) $request->param('id'));
        if ($team === null) {
            $this->session->flash('error', 'Equipe introuvable');
            $this->redirect('/teams');
        }

        $teamId = (int) $team['id'];

        $this->view('teams/show.twig', [
            'team' => $team,
            'members' => $this->membersForTeam($teamId),
            'available_users' => $this->availableUsersForTeam($teamId),
        ]);
    }
}