<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\TeamJoinRequest;
use App\Models\TeamMember;
use Illuminate\Http\Request;

class TeamJoinRequestController extends Controller
{
    public function store(Request $request, Team $team)
    {
        $user = $request->user();

        if (! $user || $user->role !== 'student') {
            abort(403);
        }

        $request->validate([
            'message' => 'nullable|string|max:1000',
            'return_from' => 'nullable|in:profile',
        ]);

        $teamShowRoute = $request->input('return_from') === 'profile'
            ? ['team' => $team, 'from' => 'profile']
            : $team;

        $isAlreadyMember = TeamMember::query()
            ->where('team_id', $team->id)
            ->where('user_id', $user->id)
            ->whereNull('out')
            ->exists();

        if ($isAlreadyMember) {
            return redirect()->route('teams.show', $teamShowRoute)->with('error', 'Вы уже состоите в этой команде.');
        }

        $alreadyPending = TeamJoinRequest::query()
            ->where('team_id', $team->id)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        if ($alreadyPending) {
            return redirect()->route('teams.show', $teamShowRoute)->with('error', 'Заявка уже отправлена и ожидает рассмотрения.');
        }

        TeamJoinRequest::create([
            'team_id' => $team->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'message' => $request->input('message') ?: null,
        ]);

        return redirect()->route('teams.show', $teamShowRoute)->with('success', 'Заявка отправлена.');
    }

    public function index(Request $request, Team $team)
    {
        $this->authorizeTeacherForTeam($request, $team);

        $requests = TeamJoinRequest::query()
            ->with(['user'])
            ->where('team_id', $team->id)
            ->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'approved' THEN 1 WHEN 'rejected' THEN 2 WHEN 'cancelled' THEN 3 ELSE 9 END")
            ->latest('created_at')
            ->paginate(20);

        return view('teams.teacher.join-requests', compact('team', 'requests'));
    }

    public function approve(Request $request, Team $team, TeamJoinRequest $joinRequest)
    {
        $this->authorizeTeacherForTeam($request, $team);

        if ($joinRequest->team_id !== $team->id) {
            abort(404);
        }

        if ($joinRequest->status !== 'pending') {
            return back()->with('error', 'Эту заявку уже рассмотрели.');
        }

        $user = $joinRequest->user;
        if (! $user) {
            return back()->with('error', 'Пользователь заявки не найден.');
        }

        $alreadyMember = TeamMember::query()
            ->where('team_id', $team->id)
            ->where('user_id', $user->id)
            ->whereNull('out')
            ->exists();

        if (! $alreadyMember) {
            TeamMember::create([
                'team_id' => $team->id,
                'user_id' => $user->id,
                'id_adding' => $request->user()->id,
                'type_user' => 'member',
                'joined_at' => now(),
            ]);
        }

        $joinRequest->update([
            'status' => 'approved',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'review_note' => $request->input('review_note') ?: null,
        ]);

        return back()->with('success', 'Заявка принята, участник добавлен в команду.');
    }

    public function reject(Request $request, Team $team, TeamJoinRequest $joinRequest)
    {
        $this->authorizeTeacherForTeam($request, $team);

        if ($joinRequest->team_id !== $team->id) {
            abort(404);
        }

        if ($joinRequest->status !== 'pending') {
            return back()->with('error', 'Эту заявку уже рассмотрели.');
        }

        $request->validate([
            'review_note' => 'nullable|string|max:1000',
        ]);

        $joinRequest->update([
            'status' => 'rejected',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'review_note' => $request->input('review_note') ?: null,
        ]);

        return back()->with('success', 'Заявка отклонена.');
    }

    private function authorizeTeacherForTeam(Request $request, Team $team): void
    {
        $user = $request->user();
        if (! $user || $user->role !== 'teacher') {
            abort(403);
        }
    }
}

