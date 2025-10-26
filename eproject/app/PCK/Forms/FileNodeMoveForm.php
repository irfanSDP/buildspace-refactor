<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;
use PCK\Folder\FileNode;
use PCK\Folder\FileNodePermission;

class FileNodeMoveForm extends CustomFormValidator {

    protected $throwException = false;

    protected function preParentValidation($formData)
    {
        $user = \Confide::user();

        $errors = $this->getNewMessageBag();

        $node = FileNode::find($formData['moving_node_id']);

        $targetNodeEditorIds       = FileNodePermission::getEditorIds($formData['target_node_id']);
        $movingNodeParentEditorIds = FileNodePermission::getEditorIds($node->parent_id);

        $canMoveNode     = in_array($user->id, $movingNodeParentEditorIds) || $node->isRoot();
        $canMoveToTarget = in_array($user->id, $targetNodeEditorIds) || ($formData['target_node_id'] == 0);

        if(!$canMoveNode || !$canMoveToTarget)
        {
            $errors->add('form', trans('folders.errors:cannotMove'));
        }

        return $errors;
    }

}
