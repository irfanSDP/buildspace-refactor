<?php namespace PCK\Helpers;

use Illuminate\Database\Eloquent\Model;
use PCK\ModuleUploadedFiles\ModuleUploadedFile;

class ModuleAttachment {

    public static function copyAttachments(Model $sourceModel, Model $targetModel)
    {
        $uploadedFiles = ModuleUploadedFile::where('uploadable_id', '=', $sourceModel->id)
            ->where('uploadable_type', '=', get_class($sourceModel))
            ->get();

        foreach($uploadedFiles as $file)
        {
            $file->copyTo($targetModel);
        }
    }

    public static function saveAttachments(Model $model, array $inputs, $type = null)
    {
        // will delete previous attachment first before proceeding to enter the new one
        ModuleUploadedFile::deletePreviousAttachments($model, $type);

        $data = array();

        if( isset( $inputs['uploaded_files'] ) )
        {
            $data = $inputs['uploaded_files'];
        }

        foreach($data as $uploadId)
        {
            \PCK\ModuleUploadedFiles\ModuleUploadedFile::create(array(
                'upload_id' => $uploadId,
                'uploadable_id' => $model->id,
                'uploadable_type' => get_class($model),
                'type' => $type,
            ));
        }

        return $model;
    }
}

