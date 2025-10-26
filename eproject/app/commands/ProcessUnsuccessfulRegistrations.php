<?php

use Indatus\Dispatcher\Scheduling\ScheduledCommand;
use Indatus\Dispatcher\Scheduling\Schedulable;
use Indatus\Dispatcher\Drivers\Cron\Scheduler;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use PCK\ModuleParameters\VendorManagement\VendorRegistrationAndPrequalificationModuleParameter;
use PCK\Base\Helpers;
use Carbon\Carbon;
use PCK\Users\UserLogin;
use PCK\SystemModules\SystemModuleConfiguration;
use PCK\VendorRegistration\VendorRegistration;
use PCK\Helpers\DBTransaction;

class ProcessUnsuccessfulRegistrations extends ScheduledCommand {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'system:process-unsucessful-registrations';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Marks and deletes unsuccessful registrations.';

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

	        $this->markUnsuccessfulRegistrations();
	        $this->processUnsuccessfulRegistrations();
		}
	}

	protected function markUnsuccessfulRegistrations()
	{
		$transaction = new DBTransaction();

		\Log::info("Marking vendor registration records as unsuccessful.");

		try {
			$transaction->begin();

			$now = \Carbon\Carbon::now();

			$registrations = VendorRegistration::has('processor')
				->where('status', '=', VendorRegistration::STATUS_DRAFT)
				->where('revision', '=', 0)
				->orderBy('company_id', 'ASC')
				->get();

			$validSubmissionDays = VendorRegistrationAndPrequalificationModuleParameter::getValue('valid_submission_days');
			$markedCount = 0;

			foreach($registrations as $registration)
			{
				if(Carbon::parse($registration->updated_at)->addDays($validSubmissionDays)->isPast())
				{
					$registration->unsuccessful_at = $now;
					$registration->save();

					++$markedCount;
				}
			}

			$transaction->commit();

			\Log::info($markedCount . " records marked as unsuccessful.");
		}
		catch(\Exception $e)
        {
            $transaction->rollback();

            \Log::info("Unable to mark as unsuccessful for record [{$registration->id}] -> {$e->getMessage()}");
            \Log::info($e->getTraceAsString());
        }
	}

	protected function processUnsuccessfulRegistrations()
	{
		$transaction = new DBTransaction(array( 'buildspace' ));

		\Log::info("Processing unsuccessful registrations.");

		try{
			$transaction->begin();

			$registrations = VendorRegistration::whereNotNull('unsuccessful_at')->get();

			$now = \Carbon\Carbon::now();

			$rows = [];

			switch(VendorRegistrationAndPrequalificationModuleParameter::getValue('period_retain_unsuccessful_reg_and_preq_submission_unit'))
			{
				case VendorRegistrationAndPrequalificationModuleParameter::DAY:
					$validityPeriodUnit = 'days';
					break;
				case VendorRegistrationAndPrequalificationModuleParameter::WEEK:
					$validityPeriodUnit = 'weeks';
					break;
				case VendorRegistrationAndPrequalificationModuleParameter::MONTH:
					$validityPeriodUnit = 'months';
					break;
				default:
					throw new \Exception("Invalid time unit");
			}

			foreach($registrations as $registration)
			{
				// $startTime = Carbon::parse($registration->unsuccessful_at);

				switch(VendorRegistrationAndPrequalificationModuleParameter::getValue('start_period_retain_unsuccessful_reg_and_preq_submission_value'))
				{
					case VendorRegistrationAndPrequalificationModuleParameter::REQUEST_RESUBMISSION_IS_SENT:
						$startTime = Carbon::parse($registration->submitted_at);

						break;
					default:
						// VendorRegistrationAndPrequalificationModuleParameter::VENDOR_LAST_LOGIN_DAY_IN_RESUBMISSION_STAGE

						$user = $registration->company->users->first();

						$latestLogin = UserLogin::where('user_id', '=', $user->id)
							->where('created_at', '>', $registration->submitted_at)
							->where('created_at', '<', $registration->unsuccessful_at)
							->orderBy('created_at', 'desc')
							->first();

						$startTime = $now;

						if($latestLogin) $startTime = Carbon::parse($latestLogin->created_at);
				}

				$purgeDate  = Helpers::getTimeFrom($startTime, VendorRegistrationAndPrequalificationModuleParameter::getValue('period_retain_unsuccessful_reg_and_preq_submission_value'), $validityPeriodUnit);

				if(!$purgeDate->isPast()) continue;

				$rows[] = [
					'name' 		       => $registration->company->name,
					'reference_no'     => $registration->company->reference_no,
					'email' 	       => $registration->company->email,
					'telephone_number' => $registration->company->telephone_number,
					'purged_at' 	   => $now,
				];

				\DB::table('purged_vendors')->insert($rows);

				$registration->company->flushRelatedVendorRegistrationData();

				foreach($registration->company->users as $user)
				{
					$user->delete();
				}

				$registration->company->delete();
				\Log::info("Deleted company: " . $registration->company->id);
			}

			$transaction->commit();

			\Log::info("Done processing unsuccessful registrations.");
		}
		catch(\Exception $e)
        {
            $transaction->rollback();

            \Log::info("Encountered error: -> {$e->getMessage()}");
            \Log::info($e->getTraceAsString());
        }
	}
}
