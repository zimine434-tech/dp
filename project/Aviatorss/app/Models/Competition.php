<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class Competition extends Model
{
    use HasFactory;

    public function statusLabel(): string
    {
        return match ((string) $this->status) {
            'upcoming' => 'Предстоящее',
            'ongoing' => 'Идёт',
            'finished' => 'Завершено',
            'cancelled' => 'Отменено',
            default => '—',
        };
    }

    protected $fillable = [
        'sport_id',
        'team_id',
        'name',
        'description',
        'form_regulation_text',
        'start_date',
        'end_date',
        'status',
        'created_by',
        'location_id',
        'competition_category_id',
        'result_type',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Boot метод для автоматического обновления статуса при загрузке модели
     */
    protected static function boot()
    {
        parent::boot();

        // Автоматически обновляем статус при загрузке модели
        static::retrieved(function ($competition) {
            // Проверяем, не обновляется ли уже статус (защита от рекурсии)
            if (!isset($competition->_updatingStatus)) {
                $competition->updateStatusIfNeeded();
            }
        });
    }

    /**
     * Автоматически обновляет статус соревнования на основе текущей даты
     */
    public function updateStatusIfNeeded(): void
    {
        // Не обновляем отмененные соревнования
        if ($this->status === 'cancelled') {
            return;
        }

        // Защита от рекурсии
        if (isset($this->_updatingStatus) && $this->_updatingStatus) {
            return;
        }
        $this->_updatingStatus = true;

        try {
            // Получаем текущую дату
            $today = Carbon::today();

            // Получаем даты начала и окончания
            $startDate = Carbon::parse($this->start_date);
            $endDate = Carbon::parse($this->end_date);

            // Определяем новый статус на основе текущей даты
            $newStatus = null;

            if ($endDate->lt($today)) {
                // Дата окончания прошла - соревнование завершено
                $newStatus = 'finished';
            } elseif ($startDate->lte($today) && $endDate->gte($today)) {
                // Текущая дата между началом и окончанием - соревнование идет
                $newStatus = 'ongoing';
            } elseif ($startDate->gt($today)) {
                // Дата начала еще не наступила - соревнование предстоящее
                $newStatus = 'upcoming';
            }

            // Обновляем статус только если он изменился
            if ($newStatus && $this->status !== $newStatus) {
                // Используем прямое обновление через DB чтобы избежать событий и рекурсии
                static::where('id', $this->id)->update(['status' => $newStatus]);
                // Обновляем локальный атрибут
                $this->status = $newStatus;
            }

            if ($this->status !== 'upcoming') {
                ApplicationCompetition::expirePendingForCompetition((int) $this->id, (string) $this->status);
            }
        } finally {
            // Снимаем флаг защиты от рекурсии
            unset($this->_updatingStatus);
        }
    }

    // Relationships
    public function sport()
    {
        return $this->belongsTo(Sport::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function category()
    {
        return $this->belongsTo(CompetitionCategory::class, 'competition_category_id');
    }

    public function participants()
    {
        return $this->hasMany(CompetitionParticipant::class);
    }

    public function teacher()
    {
        return $this->hasOne(CompetitionTeacher::class);
    }

    public function results()
    {
        return $this->hasMany(CompetitionResult::class, 'competitions_id');
    }

    public function images()
    {
        return $this->hasMany(CompetitionImage::class);
    }

    public function competitionApplications()
    {
        return $this->hasMany(ApplicationCompetition::class);
    }

    public function forms()
    {
        return $this->hasMany(CompetitionForm::class, 'competition_id');
    }

    public function isPersonalCompetition(): bool
    {
        return ($this->result_type ?? 'team') === 'personal';
    }

    /** Полное редактирование формы (выдача, вид, номер, сдача). */
    public function formsAreEditable(): bool
    {
        return ! in_array($this->status, ['finished', 'cancelled'], true);
    }

    /** После завершения соревнования можно менять только «Сдал» / «Не сдал». */
    public function formsReturnStatusEditable(): bool
    {
        return $this->status === 'finished';
    }

    /** Отметки допуска студентов — только предстоящие соревнования. */
    public function medicalAdmissionStatusEditable(): bool
    {
        return $this->status === 'upcoming';
    }

    /** Загрузка подписанного документа допуска — только предстоящие соревнования. */
    public function medicalAdmissionDocumentEditable(): bool
    {
        return $this->status === 'upcoming';
    }

    /** Формат соревнования: личное или командное (result_type). */
    public function resultFormatLabel(): string
    {
        return $this->isPersonalCompetition() ? 'Личное' : 'Командное';
    }

    /**
     * Фильтр по виду спорта: командное — sport_id; личное — sport_id команды участника.
     */
    public function scopeWhereListingSport(Builder $query, int $sportId): void
    {
        $query->where(function (Builder $q) use ($sportId) {
            $q->where('sport_id', $sportId)
                ->orWhere(function (Builder $personal) use ($sportId) {
                    $personal->where('result_type', 'personal')
                        ->whereHas('participants.team', fn (Builder $t) => $t->where('sport_id', $sportId));
                });
        });
    }

    public function matchesListingSportFilter(int $sportId): bool
    {
        if ((int) $this->sport_id === $sportId) {
            return true;
        }

        if (! $this->isPersonalCompetition()) {
            return false;
        }

        if (! $this->relationLoaded('participants')) {
            return false;
        }

        foreach ($this->participants as $participant) {
            if ((int) ($participant->team?->sport_id) === $sportId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Уникальные названия видов спорта для личного соревнования (из команд участников).
     *
     * @return Collection<int, string>
     */
    public function personalSportNames(): Collection
    {
        if (! $this->isPersonalCompetition()) {
            return collect();
        }

        $bySportId = collect();

        if ($this->relationLoaded('sport') && $this->sport_id && $this->sport?->name) {
            $bySportId[(int) $this->sport_id] = $this->sport->name;
        }

        if ($this->relationLoaded('participants')) {
            foreach ($this->participants as $participant) {
                $sport = $participant->team?->sport;
                if ($sport && filled($sport->name)) {
                    $bySportId[(int) $sport->id] = $sport->name;
                }
            }
        }

        return $bySportId->values();
    }

    /**
     * Вид спорта для командного соревнования или список видов для личного.
     *
     * @return Collection<int, string>
     */
    public function sportNamesForListing(): Collection
    {
        if ($this->isPersonalCompetition()) {
            return $this->personalSportNames();
        }

        $name = $this->sport?->name;

        return filled($name) ? collect([$name]) : collect();
    }

    public static function sportCountLabelRu(int $count): string
    {
        $mod100 = $count % 100;
        $mod10 = $count % 10;

        if ($mod100 >= 11 && $mod100 <= 14) {
            return 'видов спорта';
        }

        return match ($mod10) {
            1 => 'вид спорта',
            2, 3, 4 => 'вида спорта',
            default => 'видов спорта',
        };
    }

    /**
     * Краткий текст вида спорта для списков соревнований.
     */
    public function sportListingText(): ?string
    {
        $names = $this->sportNamesForListing();
        $count = $names->count();

        if ($this->isPersonalCompetition()) {
            if ($count === 0) {
                return null;
            }

            if ($count === 1) {
                return $names->first();
            }

            return $count.' '.self::sportCountLabelRu($count);
        }

        return $count > 0 ? $names->first() : null;
    }

    public function showParticipantsListLink(): bool
    {
        return $this->isPersonalCompetition() && $this->sportNamesForListing()->count() > 1;
    }

    /**
     * @param  array<string, mixed>  $routeParams
     */
    public function participantsListUrl(array $routeParams = []): string
    {
        $params = array_merge(['competition' => $this], $routeParams);
        $url = route('competitions.show', $params);

        $user = auth()->user();
        if ($user && $user->role === 'teacher') {
            $separator = str_contains($url, '?') ? '&' : '?';

            return $url.$separator.'tab=participants';
        }

        return $url.'#competition-participants';
    }

    /**
     * Текст описания соревнования без HTML (для проверки, что поле не пустое).
     */
    public function visibleDescriptionPlain(): string
    {
        return \App\Support\RichTextPlain::fromHtml($this->description);
    }

    public function hasVisibleDescription(): bool
    {
        return $this->visibleDescriptionPlain() !== '';
    }

    /**
     * Краткий текст под заголовком: описание соревнования или, если его нет, описание категории.
     */
    public function headerSubtitleText(): ?string
    {
        if ($this->hasVisibleDescription()) {
            return null;
        }

        $categoryDescription = trim((string) ($this->category?->description ?? ''));

        return $categoryDescription !== '' ? $categoryDescription : null;
    }
}