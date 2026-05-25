<?php

namespace App\Console\Commands;

use App\Models\ScheduleGroup;
use App\Services\IrkatScheduleDomParser;
use Illuminate\Console\Command;

class ScheduleParseGroups extends Command
{
    protected $signature = 'schedule:parse-groups {--force : Overwrite remote ids for same name}';
    protected $description = 'Parse schedule.irkat.ru DOM to build group name -> id mapping (no API token)';

    public function handle(IrkatScheduleDomParser $parser): int
    {
        try {
            $map = $parser->parseGroupMap();
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        $force = (bool) $this->option('force');
        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($map as $name => $remoteId) {
            $existingByRemote = ScheduleGroup::where('remote_id', $remoteId)->first();
            if ($existingByRemote && $existingByRemote->name !== $name) {
                $this->warn("Skip: remote_id {$remoteId} already mapped to {$existingByRemote->name}");
                $skipped++;
                continue;
            }

            $existingByName = ScheduleGroup::where('name', $name)->first();
            if ($existingByName) {
                if ($existingByName->remote_id !== $remoteId) {
                    if (!$force) {
                        $this->warn("Skip: {$name} already mapped to {$existingByName->remote_id} (parsed {$remoteId}). Use --force to overwrite.");
                        $skipped++;
                        continue;
                    }
                    $existingByName->remote_id = $remoteId;
                }
                $existingByName->save();
                $updated++;
                continue;
            }

            ScheduleGroup::create([
                'name' => $name,
                'remote_id' => $remoteId,
                'course' => null,
            ]);
            $created++;
        }

        $this->info("Parsed groups: " . count($map) . ". Created: {$created}. Updated: {$updated}. Skipped: {$skipped}.");
        return self::SUCCESS;
    }
}

