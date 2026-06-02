<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;

class User extends Authenticatable implements LdapAuthenticatable
{
    use HasFactory, Notifiable, AuthenticatesWithLdap;

    protected $fillable = [
        'login',
        'firstname',
        'lastname',
        'patronymic',
        'role',
        'status_fizorg',
        'group_name',
        'active',
        'email',
        'avatar_path',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'status_fizorg' => 'boolean',
        'active' => 'boolean',
    ];

    // Relationships
    public function teamMembers()
    {
        return $this->hasMany(TeamMember::class);
    }

    /**
     * Одна запись на команду для профиля: при повторном вступлении показывается активное участие,
     * а не устаревшая строка с датой выхода.
     *
     * @return Collection<int, TeamMember>
     */
    public function teamMembersForProfileParticipationListing(): Collection
    {
        $rows = $this->teamMembers()
            ->with('team')
            ->get();

        return $rows
            ->groupBy('team_id')
            ->map(function (Collection $group): TeamMember {
                $active = $group->filter(fn (TeamMember $m) => $m->out === null)
                    ->sortByDesc(fn (TeamMember $m) => $this->teamMemberSortKey($m))
                    ->first();
                if ($active) {
                    return $active;
                }

                return $group->sortByDesc(fn (TeamMember $m) => $this->teamMemberSortKey($m))->first();
            })
            ->values();
    }

    /**
     * Число команд в блоке «Команды» и на странице истории (без дублей при выходе и повторном входе).
     */
    public function participantTeamParticipationDisplayCount(): int
    {
        return $this->teamMembersForProfileParticipationListing()->count();
    }

    private function teamMemberSortKey(TeamMember $m): int
    {
        $ts = $m->joined_at?->getTimestamp() ?? 0;

        return (int) (($ts * 1000) + $m->id);
    }

    public function competitionParticipants()
    {
        return $this->hasMany(CompetitionParticipant::class);
    }

    public function trainingRegistrations()
    {
        return $this->hasMany(TrainingRegistration::class);
    }

    /**
     * Число записей на завершённые тренировки (совпадает с разделом истории в профиле).
     */
    public function participantFinishedTrainingRegistrationCount(): int
    {
        return $this->trainingRegistrations()->whereHas('training', function (Builder $query) {
            $query->where('status', '!=', 'cancelled')
                ->where(function (Builder $q) {
                    $q->where('status', 'completed')
                        ->orWhere('end_time', '<', now());
                });
        })->count();
    }

    /**
     * Число завершённых соревнований, где пользователь указан участником (совпадает со списком в профиле).
     */
    public function participantFinishedCompetitionParticipationCount(): int
    {
        return $this->competitionParticipants()->whereHas('competition', function (Builder $query) {
            $query->where('status', 'finished');
        })->count();
    }

    public function createdNews()
    {
        return $this->hasMany(News::class, 'created_by');
    }

    public function createdSports()
    {
        return $this->hasMany(Sport::class, 'created_by');
    }

    public function createdCompetitions()
    {
        return $this->hasMany(Competition::class, 'created_by');
    }

    public function submittedCompetitionApplications()
    {
        return $this->hasMany(ApplicationCompetition::class, 'user_id');
    }

    public function acceptedCompetitionApplications()
    {
        return $this->hasMany(ApplicationCompetition::class, 'accepted_by_user_id');
    }

    public function teamJoinRequests()
    {
        return $this->hasMany(TeamJoinRequest::class);
    }

    public function reviewedTeamJoinRequests()
    {
        return $this->hasMany(TeamJoinRequest::class, 'reviewed_by');
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if (! filled($this->avatar_path)) {
            return null;
        }

        return asset('storage/'.ltrim(str_replace('\\', '/', $this->avatar_path), '/'));
    }
}