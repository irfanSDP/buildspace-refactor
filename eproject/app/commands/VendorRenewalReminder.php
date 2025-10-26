<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Indatus\Dispatcher\Scheduling\ScheduledCommand;
use Indatus\Dispatcher\Scheduling\Schedulable;
use Indatus\Dispatcher\Drivers\Cron\Scheduler;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use PCK\Base\Helpers;
use PCK\Companies\Company;
use PCK\VendorRegistration\VendorRegistration;
use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\ModuleParameters\VendorManagement\VendorRegistrationAndPrequalificationModuleParameter;
use PCK\Notifications\EmailNotifier;

class VendorRenewalReminder extends ScheduledCommand {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'vendor-management:vendor-renewal-reminder';

    private $emailNotifier;

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Sends renewal reminders to vendors.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct(EmailNotifier $emailNotifier)
	{
		parent::__construct();

        $this->emailNotifier = $emailNotifier;
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
        \Log::info("Firing scheduled command", [
            'class'   => get_class($this),
            'command' => $this->name,
        ]);

        $record = VendorRegistrationAndPrequalificationModuleParameter::first();

        // only sends reminders to companies that have no on-going renewals
        $query = "WITH latest_vendor_registrations AS (
                      SELECT ROW_NUMBER() OVER (PARTITION BY company_id ORDER BY revision DESC) AS RANK, * FROM vendor_registrations WHERE deleted_at IS NULL
                  )
                  SELECT c.id, c.name, c.expiry_date
                  FROM companies c
                  INNER JOIN contract_group_categories cgc ON cgc.id = c.contract_group_category_id
                  INNER JOIN latest_vendor_registrations vr on vr.company_id = c.id
                  WHERE c.expiry_date IS NOT NULL
                  AND cgc.hidden IS FALSE
                  AND cgc.type = " . ContractGroupCategory::TYPE_EXTERNAL . "
                  AND vr.rank = 1
                  AND vr.status = " . VendorRegistration::STATUS_COMPLETED . "
                  ORDER BY c.id ASC";

        $results = DB::select(DB::raw($query));

        if(count($results) < 1)
        {
            \Log::info('VendorRenewalReminder@fire : no records found.');

            return false;
        }

        $companyIds = [];

        foreach($results as $result)
        {
            $dateTimeFrowNow = Helpers::getTimeBefore(Carbon::parse($result->expiry_date), $record->notify_vendors_for_renewal_value, VendorRegistrationAndPrequalificationModuleParameter::getHelperClassUnit($record->notify_vendors_for_renewal_unit));
            
            if($dateTimeFrowNow->isPast())
            {
                $companyIds[] = $result->id;
            }
        }

        $this->emailNotifier->sendVendorRenewalReminderEmail($companyIds);

        \Log::info("Done firing scheduled command", [
            'class'   => get_class($this),
            'command' => $this->name,
        ]);
	}
}
