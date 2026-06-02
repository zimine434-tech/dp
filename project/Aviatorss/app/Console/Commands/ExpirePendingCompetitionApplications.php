<?php

namespace App\Console\Commands;

use App\Models\ApplicationCompetition;
use Illuminate\Console\Command;

class ExpirePendingCompetitionApplications extends Command
{
    protected $signature = 'applications:expire-stale';

    protected $description = 'Переводит pending-заявки в expired для соревнований, которые уже не предстоящие';

    public function handle(): int
    {
        $count = ApplicationCompetition::expireAllStalePending();

        $this->info("Обновлено заявок: {$count}");

        return self::SUCCESS;
    }
}
