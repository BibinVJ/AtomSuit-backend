<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetupMultiTenantCommand extends Command
{
    protected $signature = 'setup:multitenant {--fresh : Drop all tables and start fresh}';

    protected $description = 'Setup multi-tenant environment';

    public function handle(): int
    {
        $this->info('🚀 Setting up Multi-Tenant Environment...');

        if ($this->option('fresh')) {
            $this->warn('⚠️  Fresh setup will drop all existing data!');
            if (!$this->confirm('Are you sure you want to continue?')) {
                $this->info('Setup cancelled.');
                return 1;
            }
        }

        // Step 1: Setup Central Database
        $this->info('📁 Setting up Central Database...');
        if ($this->option('fresh')) {
            $this->call('migrate:fresh', ['--database' => 'central']);
        } else {
            $this->call('migrate', ['--database' => 'central']);
        }

        // Step 2: Generate Passport Keys
        $this->info('🔐 Setting up Passport...');
        $this->call('passport:keys', ['--force' => true]);
        $this->call('passport:client', ['--personal' => true, '--name' => 'Central Personal Access Client']);

        // Step 3: Seed Central Database
        $this->info('🌱 Seeding Central Database...');
        $this->call('db:seed', [
            '--class' => 'Database\\Seeders\\CentralDatabaseSeeder',
            '--database' => 'central'
        ]);

        // Step 4: Create Default Tenant if it doesn't exist
        $this->info('🏢 Creating default tenant...');
        $this->call('tenant:create', [
            'name' => 'Default Company',
            'domain' => 'default.localhost',
            'email' => 'admin@default.com'
        ]);

        // Step 5: Migrate and Seed Tenant Database
        $this->info('🏬 Setting up Tenant Databases...');
        $this->call('tenants:migrate', ['--force' => true]);
        $this->call('tenants:seed', ['--force' => true]);

        $this->info('✅ Multi-Tenant setup completed successfully!');
        $this->newLine();
        $this->info('🔑 Central Admin Login:');
        $this->info('   Email: admin@admin.com');
        $this->info('   Password: password');
        $this->info('   URL: http://localhost/api/admin/auth/login');
        $this->newLine();
        $this->info('🏢 Default Tenant Login:');
        $this->info('   Email: admin@default.com');
        $this->info('   Password: password');
        $this->info('   URL: http://default.localhost/api/login');

        return 0;
    }
}