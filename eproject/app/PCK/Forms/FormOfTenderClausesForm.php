<?php namespace PCK\Forms;

class FormOfTenderClausesForm extends CustomFormValidator {

	protected $rules = [
		'tenderAlternativesMarkerPositions' => 'required|array|max:1|min:1',
	];

	protected $messages = [
		'tenderAlternativesMarkerPositions.required' => 'Tender alternatives need to be included.',
		'tenderAlternativesMarkerPositions.max'      => 'There can only be one set of tender alternatives. Please remove the rest.',
	];
}