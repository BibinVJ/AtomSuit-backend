<?php

namespace App\Console\Commands;

use App\Models\Utility;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TruncateTenants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'truncate:tenants 
                            {--allTenants : Run the seeder for all tenants}
                            {--tenantIds=* : The IDs of the tenants to seed (comma-separated)}
                            {--tenantEmails=* : The Emails of the tenants to seed (comma-separated)} 
                            {--tables=* : Specify one or more table names (comma-separated)} 
                            {--allTransactions : Truncate pre-defined transactions tables}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Truncate specified tables';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $tenantIds = $this->getTenantIds();
        if (empty($tenantIds)) {
            $this->error('No tenants found.');
            return Command::FAILURE;
        }

        $tables = $this->getTablesToTruncate();
        if (empty($tables)) {
            $this->error('No tables specified. Use --tables option to specify one or more table names, or use --allTransactions to truncate pre-defined transactions tables.');
            return Command::FAILURE;
        }

        foreach ($tenantIds as $tenantId) {
            tenancy()->find($tenantId)->run(function () use ($tables,$tenantId) {
                $this->output->writeln("<fg=blue>Tenant: $tenantId</>");

                DB::statement('SET FOREIGN_KEY_CHECKS=0');
                try {
                    foreach ($tables as $table) {
                        $this->info("    Truncating table: {$table}");
                        DB::table($table)->truncate();
                    }
                    $this->output->writeln("<fg=white>Tables truncated successfully!</>");
                    $this->line('');
                } finally {
                    DB::statement('SET FOREIGN_KEY_CHECKS=1');
                }
            });
        }
    }

    /**
     * Get the tenant IDs based on the command options.
     *
     * @return array
     */
    private function getTenantIds(): array
    {
        if ($this->option('allTenants')) {
            return DB::table('tenants')->orderBy('id')->pluck('id')->toArray();
        }

        if ($this->option('tenantIds')) {
            $tenantIdsSpecified = $this->option('tenantIds');

            if (is_array($tenantIdsSpecified)) {
                $tenantIdsSpecified = implode(',', $tenantIdsSpecified);
            }
            return array_map('trim', explode(',', $tenantIdsSpecified));
        }

        if ($this->option('tenantEmails')) {
            $tenantEmailsSpecified = $this->option('tenantEmails');

            if (is_array($tenantEmailsSpecified)) {
                $tenantEmailsSpecified = implode(',', $tenantEmailsSpecified);
            }
            $tenantEmails = array_map('trim', explode(',', $tenantEmailsSpecified));
            return DB::table('tenants')->whereIn('email', $tenantEmails)->orderBy('id')->pluck('id')->toArray();
        }

        return [];
    }

    /**
     * Get the tables to truncate based on the command options.
     *
     * @return array
     */
    private function getTablesToTruncate(): array
    {
        $tables = [];

        if ($this->option('allTransactions')) {
            $tables = array_merge($tables, Utility::allTransactionTables());
        }

        if ($this->option('tables')) {
            $specifiedTables = $this->option('tables');

            if (is_array($specifiedTables)) {
                $specifiedTables = implode(',', $specifiedTables);
            }

            $specifiedTables = array_map('trim', explode(',', $specifiedTables));
            $tables = array_merge($tables, $specifiedTables);
        }

        return array_unique($tables);
    }
}
