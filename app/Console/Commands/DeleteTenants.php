<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeleteTenants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:tenants
                            {--allTenants : Delete the database for all tenants}
                            {--tenantIds=* : The IDs of the tenants to delete (comma-separated)}
                            {--tenantEmails=* : The Emails of the tenants to delete (comma-separated)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete the entire database and storage for specified tenants';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tenantIds = $this->getTenantIds();
        if (empty($tenantIds)) {
            $this->error('No tenants found.');
            return Command::FAILURE;
        }

        foreach ($tenantIds as $tenantId) {
            $this->output->writeln("<fg=red>Deleting database for Tenant: $tenantId</>");
            $tenant = DB::table('tenants')->where('id', $tenantId)->first();

            if (!$tenant) {
                $this->error("Tenant with ID {$tenantId} not found.");
                return;
            }

            $tenantData = json_decode($tenant->data, true); // Decode as associative array
            if (isset($tenantData['tenancy_db_name'])) {
                $databaseName = $tenantData['tenancy_db_name'];
                DB::statement("DROP DATABASE IF EXISTS `$databaseName`");
                $this->info("Database for tenant {$tenantId} has been deleted.");
            } else {
                $this->error("Database not configured for tenant ID: $tenantId");
                return Command::FAILURE;
            }

            $this->removeTenantDataInCentralDatabase($tenant);
            $this->removeTenantStorageData($tenantId);

            $this->line('');
        }

        return Command::SUCCESS;
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
     * Remove the tenant related data in the central database.
     *
     * @return void
     */
    private function removeTenantDataInCentralDatabase(object $tenant): void
    {
        DB::table('domains')->where('tenant_id', $tenant->id)->delete();
        DB::table('users')->where('email', $tenant->email)->delete();
        DB::table('user_license__purchase_log')->where('email', $tenant->email)->delete();
        DB::table('tenants')->where('id', $tenant->id)->delete();

        $this->info("Tenant data for ID {$tenant->id} has been removed from the central database.");
    }

    /**
     * Remove the tenant-related stored data in the storage folder.
     *
     * @param int $tenantId
     * @return void
     */
    private function removeTenantStorageData(int $tenantId): void
    {
        $folderName = config('tenancy.filesystem.suffix_base') . $tenantId;

        if (Storage::exists($folderName) && Storage::deleteDirectory($folderName)) {
            $this->info("Storage folder for tenant ID {$tenantId} has been deleted.");
        } else {
            $this->error("Failed to delete storage folder or it does not exist for tenant ID {$tenantId}.");
        }
    }

}
