<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->is_admin, 403);

        $users = User::query()
            ->orderByDesc('is_admin')
            ->orderBy('name')
            ->get();

        return view('admin.users.index', ['users' => $users]);
    }
}
