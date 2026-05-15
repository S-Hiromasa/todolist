<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $teamIds = $request->user()->teams()->pluck('teams.id');

        $projects = Project::query()
            ->with('team')
            ->where(function ($query) use ($request, $teamIds): void {
                $query->where(function ($personal) use ($request): void {
                    $personal->whereNull('team_id')
                        ->where('owner_id', $request->user()->id);
                })->orWhereIn('team_id', $teamIds);
            })
            ->orderByRaw('deadline is null')
            ->orderBy('deadline')
            ->latest()
            ->get();

        return view('projects.index', ['projects' => $projects]);
    }

    public function create(Request $request): View
    {
        return view('projects.create', [
            'teams' => $this->writableTeams($request),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:active,completed,archived'],
            'deadline' => ['nullable', 'date'],
            'team_id' => ['nullable', 'integer', 'exists:teams,id'],
        ]);

        $teamId = $data['team_id'] ?? null;

        if ($teamId) {
            $team = Team::findOrFail($teamId);
            $this->ensureCanWriteTeamProject($request, $team);
        }

        $project = Project::create([
            ...$data,
            'team_id' => $teamId ?: null,
            'owner_id' => $request->user()->id,
        ]);

        return redirect()->route('projects.show', $project)->with('status', 'プロジェクトを作成しました。');
    }

    public function show(Request $request, Project $project): View
    {
        $this->ensureCanViewProject($request, $project);

        $project->load(['team.users' => fn ($query) => $query->orderBy('name')]);

        $todos = $project->todos()
            ->with('assignee')
            ->orderBy('status')
            ->orderByRaw('due_date is null')
            ->orderBy('due_date')
            ->latest()
            ->get();

        return view('projects.show', [
            'project' => $project,
            'todos' => $todos,
            'progressRate' => $project->progressRate(),
            'canWrite' => $this->canWriteProject($request, $project),
        ]);
    }

    private function writableTeams(Request $request)
    {
        return $request->user()->teams()
            ->wherePivotIn('role', [Team::ROLE_ADMIN, Team::ROLE_MEMBER])
            ->orderBy('name')
            ->get();
    }

    private function ensureCanViewProject(Request $request, Project $project): void
    {
        if ($project->team_id) {
            abort_unless($this->teamRole($request, $project->team) !== null, 403);

            return;
        }

        abort_unless($project->owner_id === $request->user()->id, 403);
    }

    private function ensureCanWriteTeamProject(Request $request, Team $team): void
    {
        abort_unless(in_array($this->teamRole($request, $team), [Team::ROLE_ADMIN, Team::ROLE_MEMBER], true), 403);
    }

    private function canWriteProject(Request $request, Project $project): bool
    {
        if ($project->team_id) {
            return in_array($this->teamRole($request, $project->team), [Team::ROLE_ADMIN, Team::ROLE_MEMBER], true);
        }

        return $project->owner_id === $request->user()->id;
    }

    private function teamRole(Request $request, Team $team): ?string
    {
        $membership = $request->user()->teams()
            ->where('teams.id', $team->id)
            ->first();

        return $membership?->pivot->role;
    }
}
