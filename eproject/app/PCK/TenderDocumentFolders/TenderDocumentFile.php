<?php namespace PCK\TenderDocumentFolders;

use Illuminate\Database\Eloquent\Model;
use PCK\Base\TemplateProcessorExt;
use PCK\Base\TimestampFormatterTrait;
use PCK\Helpers\ModelOperations;

class TenderDocumentFile extends Model {

    const TEMPLLATE_KEYWORD_FILE_FORMATS = array('docx');
    const TEMPLATE_KEYWORD_PROJECT_TITLE = 'EPROJECT_PROJECT_TITLE';
    const TEMPLATE_KEYWORD_SITE_ADDRESS  = 'EPROJECT_SITE_ADDRESS';

    use TimestampFormatterTrait;

    protected static function boot()
    {
        parent::boot();

        static::created(function (TenderDocumentFile $file)
        {
            if( $file->revision == 0 && is_null($file->parent_id) )
            {
                $file->parent_id = $file->id;

                $file->save();
            }
        });

        static::deleting(function (self $file)
        {
            \DB::table('tender_document_files_roles_readonly')
                ->where('tender_document_file_id', '=', $file->id)
                ->delete();

            $file->deleteRelatedModels();
        });
    }

    public function fileProperties()
    {
        return $this->hasOne('PCK\Base\Upload', 'id', 'cabinet_file_id');
    }

    public function revisionFiles()
    {
        return $this->hasMany('PCK\TenderDocumentFolders\TenderDocumentFile', 'parent_id', 'id')->where('parent_id', '=', $this->parent_id)->where('revision', '>', $this->revision)->orderBy('revision', 'asc');
    }

    public function getLatestRevisionFile()
    {
        return $this->revisionFiles->isEmpty() ? $this: $this->revisionFiles->last();
    }

    public function folder()
    {
        return $this->belongsTo('PCK\TenderDocumentFolders\TenderDocumentFolder', 'tender_document_folder_id');
    }

    public function readOnlyContractGroups()
    {
        return $this->belongsToMany('PCK\ContractGroups\ContractGroup', 'tender_document_files_roles_readonly', 'tender_document_file_id', 'contract_group_id');
    }

    public function setAsNewRevisionToFile(TenderDocumentFile $revisedFile)
    {
        if( $this->revision == 0 and ( ! $this->revisionFiles->isEmpty() ) )
        {
            throw new \Exception('This file ' . $this->id . ' cannot be set as revision to file ' . $revisedFile->id);
        }

        $revisionCount = TenderDocumentFile::where('parent_id', '=', $revisedFile->parent_id)
            ->where('tender_document_folder_id', '=', $this->tender_document_folder_id)
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

    public function overwriteTenderDocumentKeyWords()
    {
        $templateProcessor = new TemplateProcessorExt($this->fileProperties->physicalPath() . $this->fileProperties->filename);

        $templateProcessor->setValue(self::TEMPLATE_KEYWORD_PROJECT_TITLE, $this->folder->project->title, true);

        $templateProcessor->setValue(self::TEMPLATE_KEYWORD_SITE_ADDRESS, $this->folder->project->address.' '.$this->folder->project->state->name.' '.$this->folder->project->country->country, true);

        $templateProcessor->saveAs(sys_get_temp_dir().DIRECTORY_SEPARATOR.$this->filename.'-edited.'.$this->fileProperties->extension);

        return array(sys_get_temp_dir().DIRECTORY_SEPARATOR, $this->filename.'-edited.'.$this->fileProperties->extension);
    }

    public static function convertToReadOnlyFormat($inputFilename, $outputFilename, $inputFileExt, $path=null)
    {
        $path = !empty($path) ? $path : sys_get_temp_dir().DIRECTORY_SEPARATOR;

        switch(strtolower($inputFileExt))
        {
            case 'docx':
            case 'doc':
                $unoconv = \Unoconv\Unoconv::create(array(
                    'unoconv.binaries' => getenv('UNOCONV_PATH'),
                ));
                $unoconv->transcode($path.$inputFilename, 'pdf', $path.$outputFilename.'.pdf');

                return array($path, $outputFilename.'.pdf', 'pdf');

            default:
                return array($path, $inputFilename, $inputFileExt);
        }
    }
}