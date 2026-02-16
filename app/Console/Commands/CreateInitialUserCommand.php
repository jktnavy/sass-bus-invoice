<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateInitialUserCommand extends Command
{
    protected $signature = 'app:create-initial-user 
                            {tenant_code : Tenant code} 
                            {name : User name} 
                            {email : User email} 
                            {password : User password} 
                            {role=admin : Role (admin|sales|finance|superadmin)}';

    protected $description = 'Create the first user for a tenant';

    public function handle(): int
    {
        $tenant = Tenant::query()->where('code', $this->argument('tenant_code'))->first();

        if (! $tenant) {
            $this->error('Tenant not found by code.');

            return self::FAILURE;
        }

        $role = $this->argument('role');

        if (! in_array($role, ['admin', 'sales', 'finance', 'superadmin'], true)) {
            $this->error('Invalid role.');

            return self::FAILURE;
        }

        $existing = User::query()->where('tenant_id', $tenant->id)->where('email', $this->argument('email'))->exists();

        if ($existing) {
            $this->error('User email already exists in this tenant.');

            return self::FAILURE;
        }

        $user = User::query()->create([
            'tenant_id' => $tenant->id,
            'name' => $this->argument('name'),
            'email' => $this->argument('email'),
            'password_hash' => Hash::make($this->argument('password')),
            'role' => $role,
            'is_active' => true,
        ]);

        $this->info('User created: '.$user->email);

        return self::SUCCESS;
    }
}
