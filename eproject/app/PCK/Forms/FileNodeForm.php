<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;
use PCK\Folder\FileNodePermission;

class FileNodeForm extends CustomFormValidator {

    protected $throwException = false;

    protected $rules = [
        'name' => 'required|max:250',
    ];

    protected function preParentValidation($formData)
    {
        $user = \Confide::user();

        $errors = $this->getNewMessageBag();

        $editorIds = FileNodePermission::getEditorIds($formData['file_node_id']);

        if(($formData['file_node_id'] > 0) && !in_array($user->id, $editorIds))
        {
            $errors->add('form', trans('folders.errors:cannotUpdate'));
        }

        return $errors;
    }
}
