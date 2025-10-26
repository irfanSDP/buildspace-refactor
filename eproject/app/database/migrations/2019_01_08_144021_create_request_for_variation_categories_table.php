<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequestForVariationCategoriesTable extends Migration {
    /**
    * Run the migrations.
    *
    * @return void
    */
    public function up()
    {
        $pdo=\DB::getPdo();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_name = 'request_for_variations' AND column_name = 'category';");

        $stmt->execute();
        $count = $stmt->fetch(\PDO::FETCH_COLUMN, 0);

        $stmt = $pdo->prepare("TRUNCATE TABLE request_for_variations;");
        $stmt->execute();

        $stmt = $pdo->prepare("TRUNCATE TABLE request_for_variation_action_logs;");
        $stmt->execute();

        $stmt = $pdo->prepare("TRUNCATE TABLE request_for_variation_files;");
        $stmt->execute();

        $stmt = $pdo->prepare("TRUNCATE TABLE request_for_variation_contract_and_contingency_sum;");
        $stmt->execute();

        $stmt = $pdo->prepare("TRUNCATE TABLE request_for_variation_user_permissions;");
        $stmt->execute();

        if($count)
        {
            $stmt = $pdo->prepare("ALTER TABLE request_for_variations RENAME COLUMN category TO request_for_variation_category_id;");

            $stmt->execute();
        }

        if (!Schema::hasTable('request_for_variation_categories'))
        {
            Schema::create('request_for_variation_categories', function (Blueprint $table)
            {
                $table->increments('id')->unique();
                $table->string('name')->unique();
                $table->text('description')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index('deleted_at');
            });
        }

        Schema::table('request_for_variations', function(Blueprint $table)
		{
            $table->index('project_id');
            $table->foreign('project_id')
                ->references('id')
                ->on('projects')
                ->onDelete('cascade');

            $table->index('request_for_variation_category_id');
            $table->foreign('request_for_variation_category_id')
                ->references('id')
                ->on('request_for_variation_categories')
                ->onDelete('cascade');

            $table->index('initiated_by');
            $table->foreign('initiated_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->index('status');
            $table->index('permission_module_in_charge');

            $table->unique(['rfv_number', 'project_id']);
		});

        Schema::table('request_for_variation_action_logs', function(Blueprint $table)
		{
            $table->index('request_for_variation_id');
            $table->foreign('request_for_variation_id')
                ->references('id')
                ->on('request_for_variations')
                ->onDelete('cascade');

            $table->index('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->index('permission_module_id');
            $table->index('action_type');

		});

        Schema::table('request_for_variation_contract_and_contingency_sum', function(Blueprint $table)
		{
            $table->index('project_id');
            $table->foreign('project_id')
                ->references('id')
                ->on('projects')
                ->onDelete('cascade');

            $table->index('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
		});

        Schema::table('request_for_variation_files', function(Blueprint $table)
		{
            $table->index('request_for_variation_id');
            $table->foreign('request_for_variation_id')
                ->references('id')
                ->on('request_for_variations')
                ->onDelete('cascade');

            $table->index('cabinet_file_id');
            $table->foreign('cabinet_file_id')
                ->references('id')
                ->on('uploads')
                ->onDelete('cascade');
		});

        Schema::table('request_for_variation_user_permissions', function(Blueprint $table)
		{
            $table->index('project_id');
            $table->foreign('project_id')
                ->references('id')
                ->on('projects')
                ->onDelete('cascade');

            $table->index('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->index('added_by');
            $table->foreign('added_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->index('module_id');
            $table->index('is_editor');

            $table->unique(['project_id', 'user_id', 'module_id']);
		});
    }

    /**
    * Reverse the migrations.
    *
    * @return void
    */
    public function down()
    {
        Schema::table('request_for_variations', function(Blueprint $table)
        {
            $table->dropForeign(['project_id']);
            $table->dropForeign(['request_for_variation_category_id']);
            $table->dropForeign(['initiated_by']);
            $table->dropIndex(['project_id']);
            $table->dropIndex(['request_for_variation_category_id']);
            $table->dropIndex(['initiated_by']);
            $table->dropIndex(['status']);
            $table->dropIndex(['permission_module_in_charge']);
        });

        Schema::table('request_for_variation_action_logs', function(Blueprint $table)
        {
            $table->dropForeign(['request_for_variation_id']);
            $table->dropForeign(['user_id']);
            $table->dropIndex(['request_for_variation_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['permission_module_id']);
            $table->dropIndex(['action_type']);
        });

        Schema::table('request_for_variation_contract_and_contingency_sum', function(Blueprint $table)
		{
            $table->dropForeign(['project_id']);
            $table->dropForeign(['user_id']);
            $table->dropIndex(['project_id']);
            $table->dropIndex(['user_id']);
		});

        Schema::table('request_for_variation_files', function(Blueprint $table)
		{
            $table->dropForeign(['request_for_variation_id']);
            $table->dropForeign(['cabinet_file_id']);
            $table->dropIndex(['request_for_variation_id']);
            $table->dropIndex(['cabinet_file_id']);
		});

        Schema::table('request_for_variation_user_permissions', function(Blueprint $table)
		{
            $table->dropForeign(['project_id']);
            $table->dropForeign(['user_id']);
            $table->dropForeign(['added_by']);

            $table->dropIndex(['project_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['added_by']);
            $table->dropIndex(['module_id']);
            $table->dropIndex(['is_editor']);

            $table->dropUnique(['project_id', 'user_id', 'module_id']);
		});

        Schema::dropIfExists('request_for_variation_categories');
    }
}
