<?php namespace PCK\Helpers;

use Illuminate\Database\Schema\Blueprint;

class CustomBlueprint extends Blueprint {

	public function signAbleColumns()
	{
		$this->unsignedInteger('created_by')->index();

		$this->unsignedInteger('updated_by')->index();

		$this->foreign('created_by')->references('id')->on('users');

		$this->foreign('updated_by')->references('id')->on('users');
	}

}