<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use PCK\Tag\Tag;
use PCK\Tag\ObjectTag;
use PCK\VendorManagement\VendorManagementUserPermission;
use PCK\Users\User;

class CreateVendorManagementUserPermissionsTable extends Migration {

    const GROUP_PROCUREMENT = 1;
    const GROUP_PROCESSOR   = 2;
    const GROUP_OTHERS      = 3;

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vendor_management_user_permissions', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('user_id');
			$table->unsignedInteger('type');
			$table->unsignedInteger('created_by');
			$table->unsignedInteger('updated_by');
			$table->timestamps();
			$table->softDeletes();

			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');

			$table->index('user_id');
			$table->index('type');
		});

		$this->migrateData();
	}

	private function migrateData()
	{
		Auth::login(User::where('is_super_admin', '=', true)->orderBy('id')->get()->first());

		$this->createProcurementUserRecords();
		$this->createProcessorUserRecords();
		$this->createViewerUserRecords();
	}

	private function createProcurementUserRecords()
	{
		$userIds = \DB::table('vendor_management_users')->where('group_identifier', '=', self::GROUP_PROCUREMENT)->lists('user_id');

		$permissionTypes = [
			VendorManagementUserPermission::TYPE_DASHBOARD,
			VendorManagementUserPermission::TYPE_VENDOR_PROFILE_EDIT,
			VendorManagementUserPermission::TYPE_FORM_TEMPLATES,
			VendorManagementUserPermission::TYPE_PERFORMANCE_EVALUATION,
			VendorManagementUserPermission::TYPE_PAYMENT,
			VendorManagementUserPermission::TYPE_SETTINGS_AND_MAINTENANCE,
			VendorManagementUserPermission::TYPE_VENDOR_PROFILE_VIEW,
		];

		VendorManagementUserPermission::updatePermissions($userIds, $permissionTypes, true);

		$users = User::whereIn('id', $userIds)->get();

		foreach($users as $user)
		{
			ObjectTag::addTags($user, Tag::CATEGORY_VENDOR_MANAGEMENT_USERS, ["Procurement"]);
		}
	}

	private function createProcessorUserRecords()
	{
		$userIds = \DB::table('vendor_management_users')->where('group_identifier', '=', self::GROUP_PROCESSOR)->lists('user_id');

		$permissionTypes = [
			VendorManagementUserPermission::TYPE_DASHBOARD,
			VendorManagementUserPermission::TYPE_VENDOR_PROFILE_EDIT,
			VendorManagementUserPermission::TYPE_APPROVAL_REGISTRATION,
			VendorManagementUserPermission::TYPE_ACTIVE_VENDOR_LIST,
			VendorManagementUserPermission::TYPE_PAYMENT,
			VendorManagementUserPermission::TYPE_VENDOR_PROFILE_VIEW,
		];

		VendorManagementUserPermission::updatePermissions($userIds, $permissionTypes, true);

		$users = User::whereIn('id', $userIds)->get();

		foreach($users as $user)
		{
			ObjectTag::addTags($user, Tag::CATEGORY_VENDOR_MANAGEMENT_USERS, ["Processor"]);
		}
	}

	private function createViewerUserRecords()
	{
		$userIds = \DB::table('vendor_management_users')->where('group_identifier', '=', self::GROUP_OTHERS)->lists('user_id');

		$permissionTypes = [
			VendorManagementUserPermission::TYPE_VENDOR_PROFILE_EDIT,
			VendorManagementUserPermission::TYPE_ACTIVE_VENDOR_LIST,
			VendorManagementUserPermission::TYPE_VENDOR_PROFILE_VIEW,
		];

		VendorManagementUserPermission::updatePermissions($userIds, $permissionTypes, true);

		$users = User::whereIn('id', $userIds)->get();

		foreach($users as $user)
		{
			ObjectTag::addTags($user, Tag::CATEGORY_VENDOR_MANAGEMENT_USERS, ["Viewer"]);
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('vendor_management_user_permissions');
	}

}
