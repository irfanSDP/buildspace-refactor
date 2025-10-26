<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;
use PCK\Folder\FileNode;

class FileNodeRepositionForm extends CustomFormValidator {

    protected $throwException = false;

    protected function preParentValidation($formData)
    {
        $errors = $this->getNewMessageBag();

        $node = FileNode::find($formData['node_id']);

        if(!empty($formData['new_previous_node_id']))
        {
            $newPreviousNode = FileNode::find($formData['new_previous_node_id']);

            if($node->parent_id !== intval($formData['parent_id']) || $newPreviousNode->parent_id !== intval($formData['parent_id']))
            {
                $errors->add('form', trans('folders.errors:cannotMove'));
            }
        }

        if($node->parent_id !== intval($formData['parent_id']))
        {
            $errors->add('form', trans('folders.errors:cannotMove'));
        }

        return $errors;
    }

}
