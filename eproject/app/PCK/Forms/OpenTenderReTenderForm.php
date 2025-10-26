<?php namespace PCK\Forms;

use PCK\Forms\CustomFormValidator;
use Illuminate\Support\MessageBag;

class OpenTenderReTenderForm extends CustomFormValidator {

	protected $tender;

	protected $rules = [
		'verifiers' => 'array',
	];

	public function setTender($tender)
	{
		$this->tender = $tender;
	}

	protected function preParentValidation($formData)
	{
		$errors = new MessageBag();

		if($this->tender->id != $this->tender->project->latestTender->id || $this->tender->retender_status)
		{
			$errors->add('tender', trans('tenders.tenderAlreadyResubmitted'));
		}

		return $errors;
	}

}