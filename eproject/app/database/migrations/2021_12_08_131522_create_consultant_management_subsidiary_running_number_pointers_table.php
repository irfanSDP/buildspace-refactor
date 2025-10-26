<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use PCK\ConsultantManagement\LetterOfAward;
use PCK\Subsidiaries\Subsidiary;

class CreateConsultantManagementSubsidiaryRunningNumberPointersTable extends Migration
{
    public function up()
    {
        Schema::create('consultant_management_loa_subsidiary_running_numbers', function(Blueprint $table)
        {
            $table->unsignedInteger('subsidiary_id')->index('cm_srnp_subsidiary_id_idx')->primary();
            $table->integer('next_running_number')->index('cm_srnp_next_running_number_idx');
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('subsidiary_id', 'cm_srnp_subsidiary_id_fk')->references('id')->on('subsidiaries');
            $table->foreign('created_by', 'cm_srnp_created_by_fk')->references('id')->on('users');
            $table->foreign('updated_by', 'cm_srnp_updated_by_fk')->references('id')->on('users');
        });

        Schema::table('consultant_management_letter_of_awards', function(Blueprint $table)
        {
            $table->string('reference_number')->index('consultant_management_loa_reference_number_idx')->nullable();
            $table->integer('running_number')->index('consultant_management_loa_running_number_idx')->nullable();
        });

        $this->seedDefault();

        \DB::statement('ALTER TABLE consultant_management_letter_of_awards ALTER COLUMN reference_number SET NOT NULL;');
        \DB::statement('ALTER TABLE consultant_management_letter_of_awards ALTER COLUMN running_number SET NOT NULL;');
        \DB::statement('ALTER TABLE consultant_management_letter_of_awards ADD CONSTRAINT consultant_management_loa_ref_no_unique UNIQUE (reference_number);');
    }

    protected function seedDefault()
    {
        $letterOfAwards = LetterOfAward::all();

        $records = [];
        foreach($letterOfAwards as $letterOfAward)
        {
            $subsidiaryId = $letterOfAward->consultantManagementVendorCategoryRfp->consultantManagementContract->subsidiary_id;
            if(!array_key_exists($subsidiaryId, $records))
            {
                $records[$subsidiaryId] = [];
            }

            $records[$subsidiaryId][] = $letterOfAward;
        }

        unset($letterOfAwards);

        foreach($records as $subsidiaryId => $letterOfAwards)
        {
            $runningNumber = 0;
            $subsidiaryIdentifier = Subsidiary::find($subsidiaryId)->identifier ?? null;

            foreach($letterOfAwards as $letterOfAward)
            {
                $runningNumber++;

                $runningNumberFormat = str_pad(($runningNumber), 5, '0', STR_PAD_LEFT);
                
                $vendorCategoryCode = $letterOfAward->consultantManagementVendorCategoryRfp->vendorCategory->code;

                $runningNumberPrefix = date('Y', strtotime($letterOfAward->created_at));

                $referenceNo = "{$subsidiaryIdentifier}/{$vendorCategoryCode}/{$runningNumberPrefix}/{$runningNumberFormat}";

                $letterOfAward->reference_number = $referenceNo;
                $letterOfAward->running_number = $runningNumber;

                $letterOfAward->save();
            }

            \DB::insert('INSERT INTO consultant_management_loa_subsidiary_running_numbers (subsidiary_id, next_running_number, created_at, updated_at) values (?, ?, ?, ?)', [$subsidiaryId, $runningNumber+1, date('Y-m-d H:i:s'),  date('Y-m-d H:i:s')]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('consultant_management_loa_subsidiary_running_numbers');

        if (Schema::hasColumn('consultant_management_letter_of_awards', 'reference_number'))
        {
            Schema::table('consultant_management_letter_of_awards', function (Blueprint $table)
            {
                $table->dropColumn('reference_number');
            });
        }

        if (Schema::hasColumn('consultant_management_letter_of_awards', 'running_number'))
        {
            Schema::table('consultant_management_letter_of_awards', function (Blueprint $table)
            {
                $table->dropColumn('running_number');
            });
        }
    }
}
