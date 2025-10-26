<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConsultantManagementLoaNewColumns extends Migration
{
    public function up()
    {
        Schema::table('consultant_management_letter_of_award_templates', function(Blueprint $table)
        {
            $table->text('letterhead')->nullable();
            $table->text('signatory')->nullable();
        });

        if (Schema::hasColumn('consultant_management_letter_of_award_templates', 'content'))
        {
            Schema::table('consultant_management_letter_of_award_templates', function (Blueprint $table)
            {
                $table->dropColumn('content');
            });
        }

        Schema::create('consultant_management_letter_of_award_template_clauses', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('template_id');
            $table->text('content')->nullable();
            $table->boolean('display_numbering')->default(true);
            $table->unsignedInteger('sequence_number');
            $table->unsignedInteger('parent_id')->nullable();
            $table->timestamps();

            $table->index(['template_id', 'parent_id']);

            $table->foreign('template_id')->references('id')->on('consultant_management_letter_of_award_templates')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('consultant_management_letter_of_award_template_clauses')->onDelete('cascade');
        });

        Schema::table('consultant_management_letter_of_awards', function(Blueprint $table)
        {
            $table->text('letterhead')->nullable();
            $table->text('signatory')->nullable();
        });

        if (Schema::hasColumn('consultant_management_letter_of_awards', 'content'))
        {
            Schema::table('consultant_management_letter_of_awards', function (Blueprint $table)
            {
                $table->dropColumn('content');
            });
        }

        Schema::create('consultant_management_letter_of_award_clauses', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('template_id');
            $table->text('content')->nullable();
            $table->boolean('display_numbering')->default(true);
            $table->unsignedInteger('sequence_number');
            $table->unsignedInteger('parent_id')->nullable();
            $table->timestamps();

            $table->index(['template_id', 'parent_id']);

            $table->foreign('template_id')->references('id')->on('consultant_management_letter_of_awards')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('consultant_management_letter_of_award_clauses')->onDelete('cascade');
        });
    }

    public function down()
    {
        if (!Schema::hasColumn('consultant_management_letter_of_award_templates', 'content'))
        {
            Schema::table('consultant_management_letter_of_award_templates', function (Blueprint $table)
            {
                $table->text('content')->nullable();
            });
        }

        if (Schema::hasColumn('consultant_management_letter_of_award_templates', 'letterhead'))
        {
            Schema::table('consultant_management_letter_of_award_templates', function (Blueprint $table)
            {
                $table->dropColumn('letterhead');
            });
        }

        if (Schema::hasColumn('consultant_management_letter_of_award_templates', 'signatory'))
        {
            Schema::table('consultant_management_letter_of_award_templates', function (Blueprint $table)
            {
                $table->dropColumn('signatory');
            });
        }

        Schema::dropIfExists('consultant_management_letter_of_award_template_clauses');

        if (!Schema::hasColumn('consultant_management_letter_of_awards', 'content'))
        {
            Schema::table('consultant_management_letter_of_awards', function (Blueprint $table)
            {
                $table->text('content')->nullable();
            });
        }

        if (Schema::hasColumn('consultant_management_letter_of_awards', 'letterhead'))
        {
            Schema::table('consultant_management_letter_of_awards', function (Blueprint $table)
            {
                $table->dropColumn('letterhead');
            });
        }

        if (Schema::hasColumn('consultant_management_letter_of_awards', 'signatory'))
        {
            Schema::table('consultant_management_letter_of_awards', function (Blueprint $table)
            {
                $table->dropColumn('signatory');
            });
        }

        Schema::dropIfExists('consultant_management_letter_of_award_clauses');
    }
}
