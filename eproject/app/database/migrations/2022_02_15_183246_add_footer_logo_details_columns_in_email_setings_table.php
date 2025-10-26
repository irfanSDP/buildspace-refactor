<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFooterLogoDetailsColumnsInEmailSetingsTable extends Migration
{
    public function up()
    {
        Schema::table('email_settings', function(Blueprint $table)
        {
            $table->string('footer_logo_image')->nullable();
            $table->boolean('resize_footer_image')->default(false);
            $table->integer('footer_logo_width')->default(0);
            $table->integer('footer_logo_height')->default(0);
        });
    }

    public function down()
    {
        if (Schema::hasColumn('email_settings', 'footer_logo_image'))
        {
            Schema::table('email_settings', function (Blueprint $table)
            {
                $table->dropColumn('footer_logo_image');
            });
        }

        if (Schema::hasColumn('email_settings', 'resize_footer_image'))
        {
            Schema::table('email_settings', function (Blueprint $table)
            {
                $table->dropColumn('resize_footer_image');
            });
        }

        if (Schema::hasColumn('email_settings', 'footer_logo_width'))
        {
            Schema::table('email_settings', function (Blueprint $table)
            {
                $table->dropColumn('footer_logo_width');
            });
        }
        
        if (Schema::hasColumn('email_settings', 'footer_logo_height'))
        {
            Schema::table('email_settings', function (Blueprint $table)
            {
                $table->dropColumn('footer_logo_height');
            });
        }
    }
}
