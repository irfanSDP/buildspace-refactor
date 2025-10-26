<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use PCK\ModulePermission\ModulePermission;
use PCK\Helpers\DBTransaction;
use PCK\Users\User;

class MigrateFinanceUserSubsidiariesTableDataIntoModulePermissionSubsidiariesTable extends Migration
{
    private function getFinanceUserSubsidiariesRecordsToMigrate()
    {
        $query = "
            WITH migrate_data_cte AS (
                SELECT user_id, subsidiary_id
                FROM finance_user_subsidiaries fus
                EXCEPT
                SELECT mp.user_id, mps.subsidiary_id
                FROM module_permission_subsidiaries mps 
                INNER JOIN module_permissions mp ON mp.id = mps.module_permission_id 
                WHERE mp.module_identifier = " . ModulePermission::MODULE_ID_FINANCE . "
            )
            SELECT user_id, ARRAY_TO_JSON(ARRAY_AGG(subsidiary_id ORDER BY subsidiary_id ASC)) AS subsidiary_ids 
            FROM migrate_data_cte
            GROUP BY user_id
            ORDER BY user_id ASC
        ";

        $dataToMigrate = [];

        foreach(DB::select(DB::raw($query)) as $record)
        {
            $dataToMigrate[$record->user_id] = json_decode($record->subsidiary_ids);
        }

        return $dataToMigrate;
    }

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        $dataToMigrate = $this->getFinanceUserSubsidiariesRecordsToMigrate();

        if (count($dataToMigrate) <= 0)
        {
            return;
        }

        $transaction = new DBTransaction();

        try
        {
           
            $transaction->begin();

            foreach($dataToMigrate as $userId => $subsidiaryIds)
            {
                $user = User::find($userId);

                ModulePermission::grant($user, ModulePermission::MODULE_ID_FINANCE);

                $modulePermission = $user->modulePermission(ModulePermission::MODULE_ID_FINANCE)->first();

                $modulePermission->subsidiaries()->sync($subsidiaryIds);
            }

            $transaction->commit();
        }
        catch(\Exception $e)
        {
            $transaction->rollback();
        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		
	}
}
