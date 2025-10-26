<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;
use Illuminate\Support\MessageBag;

class ProjectImportForm extends CustomFormValidator {

    protected $rules = [
        'contract_id'    => 'required|integer|exists:contracts,id',
        'reference'      => 'required|unique:projects,reference,NULL,id,deleted_at,NULL',
        'running_number' => 'required|integer|min:1',
        'subsidiary_id'  => 'required|integer',
        'description'    => 'required',
    ];

    protected $messages = [
        'reference.unique'       => 'There is already a project with this Contract No. Please enter another one, or refresh the page to generate one automatically.',
        'running_number.integer' => 'The contract number must be an integer.',
        'running_number.min'     => 'The contract number must be greater than 0.',
        'subsidiary_id.required' => 'Please select a subsidiary.',
    ];

    protected function preParentValidation($formData)
    {
        $errors = new MessageBag();

        $file = $formData['ebqFile'];

        if( ! $file )
        {
            \Flash::error(trans('files.noFileUploaded'));

            $errors->add('ebqFile', trans('files.noFileUploaded'));

            return $errors;
        }

        if( ! \PCK\Helpers\Files::hasExtension(\PCK\Helpers\Files::EXTENSION_EBQ, $file) )
        {
            \Flash::error(trans('files.extensionMismatchEbq'));

            $errors->add('ebqFile', trans('files.extensionMismatchEbq'));

            return $errors;
        }

        try
        {
            $folderLocation = \PCK\Helpers\Zip::unzip($file);

            $contents = \PCK\Helpers\Files::getFolderContents($folderLocation);

            $projectRootId = (int)$contents[0]->ROOT->id;

            $buildspaceId = (string)$contents[0]->attributes()->buildspaceId;

            \PCK\Helpers\Files::deleteDirectory($folderLocation);
        }
        catch(\Exception $e)
        {
            $errors->add('ebqFile', trans('files.unzipFailed'));

            return $errors;
        }

        if( \Config::get('buildspace.BUILDSPACE_ID') != $buildspaceId )
        {
            $errors->add('ebqFile', trans('projects.misMatchingOriginSystem'));

            return $errors;
        }

        $buildspaceProject = \PCK\Buildspace\Project::find($projectRootId);

        if( $formData['parent_project_id'] != $buildspaceProject->mainInformation->eproject_origin_id )
        {
            $errors->add('ebqFile', trans('projects.misMatchingOriginProject'));

            return $errors;
        }

        return $errors;
    }
}