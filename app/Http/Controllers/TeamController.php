<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function index(Request $request): View
    {
        $teams = $request->user()->teams()
            ->orderBy('name')
            ->get();

        return view('teams.index', ['teams' => $teams]);
    }

    public function create(): View
    {
        return view('teams.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
        ]);

        $team = Team::create([
            'name' => $data['name'],
            'owner_id' => $request->user()->id,
        ]);

        $team->users()->attach($request->user()->id, ['role' => Team::ROLE_ADMIN]);

        return redirect()->route('teams.show', $team)->with('status', 'チームを作成しました。');
    }

    public function show(Request $request, Team $team): View
    {
        $this->ensureTeamMember($request, $team);

        $team->load(['users' => fn ($query) => $query->orderBy('name')]);

        $todos = $team->todos()
            ->orderBy('is_done')
            ->orderByRaw('due_date is null')
            ->orderBy('due_date')
            ->latest()
            ->get();

        return view('teams.show', [
            'team' => $team,
            'todos' => $todos,
            'role' => $this->roleFor($request, $team),
        ]);
    }

    public function invite(Request $request, Team $team): RedirectResponse
    {
        $this->ensureTeamAdmin($request, $team);

        $data = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'role' => ['required', 'in:admin,member,viewer'],
        ]);

        $user = User::where('email', $data['email'])->firstOrFail();

        $team->users()->syncWithoutDetaching([
            $user->id => ['role' => $data['role']],
        ]);

        return redirect()->route('teams.show', $team)->with('status', 'メンバーを追加しました。');
    }

    private function ensureTeamMember(Request $request, Team $team): void
    {
        abort_unless($this->roleFor($request, $team) !== null, 403);
    }

    private function ensureTeamAdmin(Request $request, Team $team): void
    {
        abort_unless($this->roleFor($request, $team) === Team::ROLE_ADMIN, 403);
    }

    private function roleFor(Request $request, Team $team): ?string
    {
        $membership = $request->user()->teams()
            ->where('teams.id', $team->id)
            ->first();

        return $membership?->pivot->role;
    }
}
