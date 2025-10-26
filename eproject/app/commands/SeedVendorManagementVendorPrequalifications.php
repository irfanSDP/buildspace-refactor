<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use PCK\Helpers\DBTransaction;
use PCK\VendorRegistration\VendorRegistration;
use PCK\VendorPreQualification\VendorPreQualification;
use PCK\Users\User;

/**
 * This script will create PreQ forms for vendors and put them into vendor lists accordingly
 */

class SeedVendorManagementVendorPrequalifications extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'vendor-management:seed-vendor-prequalifications';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Seeds vendor prequalifications for vendors that are migrated.';

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
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$transaction = new DBTransaction();
        $transaction->begin();

        try
        {
			$user = User::where('is_super_admin', true)->orderBy('id', 'ASC')->first();

			Auth::login($user);

			$query = DB::select(DB::raw("SELECT c.id AS company_id, c.name AS company_name, vr.id AS vendor_registration_id, vr.revision
											FROM companies c
											JOIN vendor_registrations vr ON vr.company_id = c.id
											JOIN (
												SELECT vr2.company_id, max(vr2.revision) AS revision
												FROM vendor_registrations vr2
												WHERE vr2.deleted_at IS NULL
												GROUP BY vr2.company_id
												ORDER BY vr2.company_id
											) vr2 ON vr2.company_id = vr.company_id AND vr2.revision = vr.revision
											WHERE vr.deleted_at IS NULL
											ORDER BY c.id ASC;"));

			$vendorRegistrationIds = array_column($query, 'vendor_registration_id');

			$vendorRegistrations = VendorRegistration::whereIn('id', $vendorRegistrationIds)->get();

			foreach($vendorRegistrations as $vendorRegistration)
			{
				VendorPreQualification::syncLatestForms($vendorRegistration);

				if($vendorRegistration->isCompleted())
				{
					// add to AVL, and do nothing else
					$vendorRegistration->processResults(false);
				}
			}

			Auth::logout();

            $transaction->commit();

			$this->info("Done seeding vendor PreQ and pushing into vendor lists.");
        }
        catch(\Exception $e)
        {
            $transaction->rollback();

			Auth::logout();

            $this->error($e->getMessage());
        }
	}
}
