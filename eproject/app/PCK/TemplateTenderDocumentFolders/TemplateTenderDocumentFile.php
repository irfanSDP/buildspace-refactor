<?php namespace PCK\TemplateTenderDocumentFolders;

use Illuminate\Database\Eloquent\Model;
use PCK\Base\TimestampFormatterTrait;
use PCK\Helpers\ModelOperations;
use PCK\WorkCategories\WorkCategory;

class TemplateTenderDocumentFile extends Model {

    use TimestampFormatterTrait;

    protected static function boot()
    {
        parent::boot();

        static::created(function (TemplateTenderDocumentFile $file)
        {
            if( $file->revision == 0 && is_null($file->parent_id) )
            {
                $file->parent_id = $file->id;

                $file->save();
            }
        });

        static::deleting(function (self $file)
        {
            \DB::table('template_tender_document_files_roles_readonly')
                ->where('template_tender_document_file_id', '=', $file->id)
                ->delete();

            $file->deleteRelatedModels();
        });
    }

    public function fileProperties()
    {
        return $this->hasOne('PCK\Base\Upload', 'id', 'cabinet_file_id');
    }

    public function folder()
    {
        return $this->belongsTo('PCK\TemplateTenderDocumentFolders\TemplateTenderDocumentFolder');
    }

    public function workCategory()
    {
        return $this->belongsTo('PCK\WorkCategories\WorkCategory', 'work_category_id');
    }

    public function readOnlyContractGroups()
    {
        return $this->belongsToMany('PCK\ContractGroups\ContractGroup', 'template_tender_document_files_roles_readonly', 'template_tender_document_file_id', 'contract_group_id');
    }

    /**
     * Delete related records.
     *
     * @throws \Exception
     */
    public function deleteRelatedModels()
    {
        ModelOperations::deleteWithTrigger(array(
            $this->fileProperties
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