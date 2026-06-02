<?php

namespace App\Console\Commands;

use App\Services\LdapStudentImporter;
use Illuminate\Console\Command;

class ImportLdapStudents extends Command
{
    protected $signature = 'ldap:import-students';

    protected $description = 'Импорт всех студентов из Active Directory в таблицу users';

    public function handle(LdapStudentImporter $importer): int
    {
        try {
            $count = $importer->importAll(fn (string $message) => $this->line($message));
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        if ($count === 0) {
            $this->error('Ни один студент не импортирован. Проверьте LDAP_STUDENTS_BASE_DN и доступ к AD.');

            return self::FAILURE;
        }

        $this->info("Готово: {$count} студентов.");

        return self::SUCCESS;
    }
}
