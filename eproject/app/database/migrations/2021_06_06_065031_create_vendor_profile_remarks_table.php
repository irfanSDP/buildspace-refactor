<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use PCK\VendorRegistration\VendorProfile;
use PCK\VendorRegistration\VendorProfileRemark;

class CreateVendorProfileRemarksTable extends Migration {

    public function up()
    {
        Schema::create('vendor_profile_remarks', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('vendor_profile_id');
            $table->text('content')->nullable();
            $table->unsignedInteger('created_by')->index();
            $table->unsignedInteger('updated_by')->index();
            $table->timestamps();

            $table->index('vendor_profile_id');

            $table->foreign('vendor_profile_id')->references('id')->on('vendor_profiles')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });

        foreach(VendorProfile::all() as $vendorProfile)
        {
            if(strlen($vendorProfile->remarks) > 0 )
            {
                $remark = new VendorProfileRemark;
                $remark->vendor_profile_id = $vendorProfile->id;
                $remark->content = trim($vendorProfile->remarks);
                
                $remark->save();
            }
        }
    }

    public function down()
    {
        Schema::drop('vendor_profile_remarks');
    }
}
