<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class DocumentManagementFolderForm extends FormValidator {

	/**
	 * Validation rules for creating Company
	 *
	 * @var array
	 */
	protected $rules = [
		'name'        => 'required|max:200',
		'project_id'  => 'required',
		'parent_id'   => 'required',
		'folder_type' => 'required'
	];

}