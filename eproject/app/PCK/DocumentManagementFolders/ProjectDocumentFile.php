<?php namespace PCK\DocumentManagementFolders;

use Illuminate\Database\Eloquent\Model;
use PCK\Base\TimestampFormatterTrait;
use PCK\Helpers\ModelOperations;

class ProjectDocumentFile extends Model {

    use TimestampFormatterTrait;

    protected static function boot()
    {
        parent::boot();

        static::created(function(ProjectDocumentFile $file)
        {
            if( $file->revision == 0 && is_null($file->parent_id) )
            {
                $file->parent_id = $file->id;

                $file->save();
            }
        });

        static::deleting(function(ProjectDocumentFile $file)
        {
            $file->deleteRelatedModels();
        });
    }

    public function fileProperties()
    {
        return $this->hasOne('PCK\Base\Upload', 'id', 'cabinet_file_id');
    }

    public function revisionFiles()
    {
        return $this->hasMany('PCK\DocumentManagementFolders\ProjectDocumentFile', 'parent_id', 'id')->where('parent_id', '=', $this->parent_id)->where('revision', '>', $this->revision)->orderBy('revision', 'asc');
    }

    public function folder()
    {
        return $this->belongsTo('PCK\DocumentManagementFolders\DocumentManagementFolder', 'project_document_folder_id')->orderBy('id', 'desc');
    }

    public function setAsNewRevisionToFile(ProjectDocumentFile $revisedFile)
    {
        if( $this->revision == 0 and ( ! $this->revisionFiles->isEmpty() ) )
        {
            throw new \Exception('This file ' . $this->id . ' cannot be set as revision to file ' . $revisedFile->id);
        }

        $revisionCount = ProjectDocumentFile::where('parent_id', '=', $revisedFile->parent_id)
            ->where('project_document_folder_id', '=', $this->project_document_folder_id)
            ->where('id', '<>', $this->id)
            ->whereRaw('id <> parent_id')
            ->count();

        $this->parent_id = $revisedFile->parent_id;
        $this->revision  = $revisionCount + 1;

        $this->save();
    }

    public function detachFromCurrentRevision()
    {
        $this->parent_id = $this->id;
        $this->revision  = 0;

        $this->save();
    }

    /**
     * Delete related records.
     *
     * @throws \Exception
     */
    public function deleteRelatedModels()
    {
        ModelOperations::deleteWithTrigger(array(
            $this->revisionFiles,
            $this->fileProperties,
        ));
    }

    /**
     * Returns true if the file exists on the disk.
     *
     * @return bool
     */
    public function fileExists()
    {
        // Have to manually load the related model, because it is not automatically loaded when passed to empty().
        $this->fileProperties;
        if( ! empty( $this->fileProperties ) )
        {
            return $this->fileProperties->fileExists();
        }

        return false;
    }

}