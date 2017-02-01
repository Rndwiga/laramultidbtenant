<?php

namespace rndwiga\MultiTenant\Commands;

use rndwiga\MultiTenant\LaraMultiDbTenant;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Kernel;

class BaseModelsCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'tenant:basemodels';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the tenant, tenant_user, tenant_database models';

    /**
     * @type \rndwiga\MultiTenant\LaraMultiDbTenant
     */
    private $multiDbTenant;
    /**
     * @type \Illuminate\Contracts\Console\Kernel
     */
    private $kernel;

    /**
     * Constructs the BaseModelsCommand
     *
     * @param \rndwiga\MultiTenant\LaraMultiDbTenant $multiDbTenant
     * @param \Illuminate\Contracts\Console\Kernel           $kernel
     */
    public function __construct(LaraMultiDbTenant $multiDbTenant, Kernel $kernel)
    {
        parent::__construct();

        $this->multiDbTenant = $multiDbTenant;
        $this->kernel = $kernel;
    }

    /**
     * Execute the console command
     *
     * @return void
     */
    public function handle()
    {
        // FIXME: make:model replaced with make:migration
        $this->kernel->call('make:migration', ['name' => 'Tenant']);
        $this->kernel->call('make:migration', ['name' => 'TenantUser']);
        $this->kernel->call('make:migration', ['name' => 'TenantDatabase']);

        $this->info('Tenant, TenantUser, TenantDatabase models created successfully.');
        $this->info('Remember to set the relationships between Tenant, TenantUser, TenantDatabase as well as the default user model!');
    }
}
