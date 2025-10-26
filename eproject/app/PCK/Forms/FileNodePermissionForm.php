<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;
use PCK\Folder\FileNode;
use PCK\Folder\FileNodePermission;
use PCK\Users\User;

class FileNodePermissionForm extends CustomFormValidator {

    protected $throwException = false;

    protected function preParentValidation($formData)
    {
        $errors = $this->getNewMessageBag();

        if($formData['grant'])
        {
            $user = User::find($formData['user_id']);

            if($formData['set_editor'] && $user->isCompanyTypeExternal())
            {
                $errors->add('form', trans('folders.errors:invalidEditor'));
            }
        }
        else
        {
            $node = FileNode::find($formData['file_node_id']);

            $viewerIds = FileNodePermission::getViewerIds($node->parent_id);
            $editorIds = FileNodePermission::getEditorIds($node->parent_id);

            if($formData['set_editor'])
            {
                if(in_array($formData['user_id'], $editorIds))
                {
                    $errors->add('form', trans('folders.errors:cannotRevoke'));
                }
            }
            else
            {
                if(in_array($formData['user_id'], $viewerIds))
                {
                    $errors->add('form', trans('folders.errors:cannotRevoke'));
                }
            }
        }

        return $errors;
    }

}
