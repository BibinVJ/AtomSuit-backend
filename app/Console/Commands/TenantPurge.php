<?php

namespace App\Console\Commands;

use App\Services\TenantSelector;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TenantPurge extends Command
{
    protected $signature = 'tenant:purge
                            {--ids= : Comma-separated tenant IDs}
                            {--emails= : Comma-separated tenant emails}
                            {--tables= : Comma-separated list of tables to purge (all tables if not given)}';

    protected $description = 'Safely delete tenant data (respects foreign key constraints)';

    public function handle(): int
    {
        $tenantIds = TenantSelector::resolve($this->option('ids'), $this->option('emails'));

        foreach ($tenantIds as $tenantId) {
            tenancy()->initialize($tenantId);

            $connection = DB::connection('tenant');

            $tables = $this->option('tables')
                ? array_map('trim', explode(',', $this->option('tables')))
                : $this->getAllTables($connection);

            foreach ($tables as $table) {
                if ($connection->getSchemaBuilder()->hasTable($table)) {
                    $this->info("Purging table: {$table} for tenant {$tenantId}");
                    $connection->table($table)->delete(); // cascades handled by DB
                }
            }

            tenancy()->end();
        }

        return Command::SUCCESS;
    }

    private function getAllTables($connection): array
    {
        $dbName = $connection->getDatabaseName();
        $tables = $connection->select("SHOW TABLES");
        $column = "Tables_in_{$dbName}";

        return array_map(fn($row) => $row->$column, $tables);
    }
}
