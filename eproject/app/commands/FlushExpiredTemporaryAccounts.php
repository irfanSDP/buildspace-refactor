<?php

use Indatus\Dispatcher\Scheduling\ScheduledCommand;
use Indatus\Dispatcher\Scheduling\Schedulable;
use Indatus\Dispatcher\Drivers\Cron\Scheduler;
use PCK\Users\UserCompanyLog;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use PCK\FormBuilder\DynamicForm;
use PCK\FormBuilder\FormObjectMapping;
use PCK\SystemModules\SystemModuleConfiguration;
use PCK\VendorRegistration\VendorRegistration;

class FlushExpiredTemporaryAccounts extends ScheduledCommand {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'system:flush-expired-temporary-accounts';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Removes temporary users and companies if their expiry date is past.';

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

            $this->flushExpiredAccounts();
        }
	}

	protected function flushExpiredAccounts()
    {
        $transaction = new \PCK\Helpers\DBTransaction(array('buildspace'));

        \Log::info("Flushing expired accounts.");

        try
        {
            $transaction->begin();

            $expiredCompanies = \PCK\Companies\Company::whereNotNull('purge_date')->where('purge_date', '<', 'now()')->get();

            foreach($expiredCompanies as $company)
            {
                \Log::info("Flushing expired company [{$company->id}:{$company->name}]. Expired at: {$company->purge_date}.");

				$this->flushExpiredCompanyRegistrationInfo($company);

                foreach($company->users as $user)
                {
                    if ($user->isTemporaryAccount())
                    {
                        UserCompanyLog::flushRecords($user);
                        $user->delete();
                    }
                }

                $company->delete();
            }

            $transaction->commit();

            \Log::info("All expired accounts have been flushed.");
        }
        catch(\Exception $e)
        {
            $transaction->rollback();

            \Log::error("Unable to delete expired company [{$company->id}:{$company->name}] -> {$e->getMessage()}");
            \Log::error($e->getTraceAsString());
        }
    }

    protected function flushExpiredCompanyRegistrationInfo($company)
    {
        $vendorRegistrations = VendorRegistration::where('company_id', '=', $company->id)->get();

        foreach($vendorRegistrations as $vendorRegistration)
        {
            $formObjectMapping = FormObjectMapping::findRecord($vendorRegistration, DynamicForm::VENDOR_REGISTRATION_IDENTIFIER);

            if($formObjectMapping && $formObjectMapping->dynamicForm)
            {
                $formObjectMapping->dynamicForm->delete();
            }

            \PCK\TrackRecordProject\TrackRecordProject::where('vendor_registration_id', '=', $vendorRegistration->id)->delete();

            \PCK\Users\UserCompanyLog::where('company_id', '=', $company->id)->delete();

            \PCK\VendorPreQualification\VendorPreQualification::where('vendor_registration_id', '=', $vendorRegistration->id)->delete();
        }
    }

}
