<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToTrackRecordProjectsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('track_record_projects', function(Blueprint $table)
		{
			if (!Schema::hasColumn('track_record_projects', 'type'))
			{
				$table->unsignedInteger('type')->default(\PCK\TrackRecordProject\TrackRecordProject::TYPE_CURRENT);
			}

			if (!Schema::hasColumn('track_record_projects', 'project_amount'))
			{
				$table->string('project_amount')->default(0);
			}
			
			if (!Schema::hasColumn('track_record_projects', 'year_of_site_possession'))
			{
				$table->timestamp('year_of_site_possession')->nullable();
			}

			if (!Schema::hasColumn('track_record_projects', 'year_of_completion'))
			{
				$table->timestamp('year_of_completion')->nullable();
			}

			if (!Schema::hasColumn('track_record_projects', 'has_qlassic_or_conquas_score'))
			{
				$table->boolean('has_qlassic_or_conquas_score')->default(false);
			}

			if (!Schema::hasColumn('track_record_projects', 'qlassic_score'))
			{
				$table->string('qlassic_score')->nullable();
			}

			if (!Schema::hasColumn('track_record_projects', 'qlassic_year_of_achievement'))
			{
				$table->timestamp('qlassic_year_of_achievement')->nullable();
			}

			if (!Schema::hasColumn('track_record_projects', 'conquas_score'))
			{
				$table->string('conquas_score')->nullable();
			}

			if (!Schema::hasColumn('track_record_projects', 'conquas_year_of_achievement'))
			{
				$table->timestamp('conquas_year_of_achievement')->nullable();
			}

			if (!Schema::hasColumn('track_record_projects', 'has_recognition_awards'))
			{
				$table->boolean('has_recognition_awards')->default(false);
			}

			if (!Schema::hasColumn('track_record_projects', 'year_of_recognition_awards'))
			{
				$table->timestamp('year_of_recognition_awards')->nullable();
			}
		});

		if (Schema::hasColumn('track_record_projects', 'project_awarded_at'))
		{
			Schema::table('track_record_projects', function (Blueprint $table)
			{
				$table->dropColumn('project_awarded_at');
			});
		}

		if (Schema::hasColumn('track_record_projects', 'recognition_awards'))
		{
			Schema::table('track_record_projects', function (Blueprint $table)
			{
				$table->dropColumn('recognition_awards');
			});
		}

		if (Schema::hasColumn('track_record_projects', 'recognition_awards_date'))
		{
			Schema::table('track_record_projects', function (Blueprint $table)
			{
				$table->dropColumn('recognition_awards_date');
			});
		}

		\PCK\TrackRecordProject\TrackRecordProject::where('id', '!=', 0)->update(array(
			'year_of_site_possession' => \Carbon\Carbon::now(),
			'year_of_completion' => \Carbon\Carbon::now(),
		));

		\DB::statement('ALTER TABLE track_record_projects ALTER COLUMN year_of_site_possession SET NOT NULL');
		\DB::statement('ALTER TABLE track_record_projects ALTER COLUMN year_of_completion SET NOT NULL');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('track_record_projects', function(Blueprint $table)
		{
			$table->timestamp('project_awarded_at')->default(\Carbon\Carbon::now());
			$table->timestamp('recognition_awards')->nullable();
			$table->timestamp('recognition_awards_date')->nullable();

			$table->dropColumn('type');
			$table->dropColumn('project_amount');
			$table->dropColumn('year_of_site_possession');
			$table->dropColumn('year_of_completion');
			$table->dropColumn('has_qlassic_or_conquas_score');
			$table->dropColumn('qlassic_score');
			$table->dropColumn('qlassic_year_of_achievement');
			$table->dropColumn('conquas_score');
			$table->dropColumn('conquas_year_of_achievement');
			$table->dropColumn('has_recognition_awards');
			$table->dropColumn('year_of_recognition_awards');
		});
	}

}
