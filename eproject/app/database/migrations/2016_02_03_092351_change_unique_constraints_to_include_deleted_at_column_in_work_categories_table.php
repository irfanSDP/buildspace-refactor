<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeUniqueConstraintsToIncludeDeletedAtColumnInWorkCategoriesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('work_categories', function (Blueprint $table)
        {
            // Check if constraint exists before dropping unique constraint.
            // Unique constraint is not set on rollback because the identifier column may already have non-unique values.
            $result = DB::select("
                select constraint_name
                from information_schema.constraint_column_usage
                where constraint_name = 'work_categories_identifier_unique'
                ");

            if( ! empty( $result ) )
            {
                $table->dropUnique('work_categories_identifier_unique');
            }

            // Unique constraint is not set on rollback because the name column may already have non-unique values.
            $result = DB::select("
                select constraint_name
                from information_schema.constraint_column_usage
                where constraint_name = 'work_categories_name_unique'
                ");

            if( ! empty( $result ) )
            {
                $table->dropUnique('work_categories_name_unique');
            }

            $table->unique(array( 'identifier', 'deleted_at' ));
            $table->unique(array( 'name', 'deleted_at' ));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('work_categories', function (Blueprint $table)
        {
            $table->dropUnique('work_categories_name_deleted_at_unique');
            $table->dropUnique('work_categories_identifier_deleted_at_unique');
        });
    }

}
