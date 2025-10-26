<?php

use Indatus\Dispatcher\Scheduling\ScheduledCommand;
use Indatus\Dispatcher\Scheduling\Schedulable;
use Indatus\Dispatcher\Drivers\Cron\Scheduler;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use PCK\ModuleParameters\VendorManagement\VendorProfileModuleParameter;
use PCK\Base\Helpers;
use PCK\Companies\Company;
use Carbon\Carbon;
use PCK\SystemModules\SystemModuleConfiguration;

class DeactivateVendors extends ScheduledCommand {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'system:deactivate-vendors';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Marks vendors as deactivated.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * When a command should run
     *
     * @param Scheduler $scheduler
     * @return \Indatus\Dispatcher\Scheduling\Schedulable
     */
    public function schedule(Schedulable $scheduler)
    {
        return $scheduler->daily();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        if(SystemModuleConfiguration::isEnabled(SystemModuleConfiguration::MODULE_ID_VENDOR_MANAGEMENT))
        {
            \Log::info("Firing scheduled command", [
                'class'   => get_class($this),
                'command' => $this->name,
            ]);

            $this->setDeactivationDate();
            $this->markDeactivatedVendors();
        }
    }

    protected function setDeactivationDate()
    {
        $now = \Carbon\Carbon::now();

        $companies = Company::whereNotNull('activation_date')
            ->whereNull('deactivation_date')
            ->get();

        foreach($companies as $company)
        {
            $deactivationDate = $company->calculateDeactivationDate();

            if(!$deactivationDate || $deactivationDate->isFuture()) continue;

            \Log::info("Setting deactivation date for company:{$company->id}. Deactivation date: {$deactivationDate}.");

            $company->deactivation_date = $deactivationDate;
            $company->save();

            $company->updateVendorStatus();
        }
    }

    protected function markDeactivatedVendors()
    {
        $now = \Carbon\Carbon::now();

        $companies = Company::whereNotNull('deactivation_date')
            ->whereNull('deactivated_at')
            ->get();

        foreach($companies as $company)
        {
            if(Carbon::parse($company->deactivation_date)->isFuture()) continue;

            \Log::info("Setting company:{$company->id} as deactivated.");

            $company->deactivated_at = $now;
            $company->save();

            $company->updateVendorStatus();
        }
    }
}
