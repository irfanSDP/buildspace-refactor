<?php namespace PCK\Base;

use Andrew13\Cabinet\CabinetUpload;
use Carbon\Carbon;
use PCK\ModuleUploadedFiles\ModuleUploadedFile;
use PCK\DocumentManagementFolders\ProjectDocumentFile;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Upload extends CabinetUpload {

    use TimestampFormatterTrait;

    private $presetUserId;

    protected $appends = array( 'download_url' );

    protected $supportedImage = [ 'gif', 'jpg', 'jpeg', 'png' ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $upload)
        {
            if(is_null($upload->original_file_name)) $upload->original_file_name = $upload->filename;
        });

        static::deleted(function (self $upload)
        {
            $upload->deletePhysicalFile();
        });
    }

    public function setPresetUserId($userId)
    {
        $this->presetUserId = $userId;
    }

    /**
     * Override this function to allow all type of files
     *
     * @param UploadedFile $file
     *
     * @return bool|void
     */
    public function verifyUploadType(UploadedFile $file)
    {
        return true;
    }

    // return a path without starting with /public
    //   the path begins and ends in a / (slash)
    public function publicPath()
    {
        return str_replace('/public/', '/', asset($this->path) . '/');
    }

    public function physicalPath()
    {
        return base_path($this->path);
    }

    public function delete()
    {
        //delete record in project document file if any
        ProjectDocumentFile::where('cabinet_file_id', '=', $this->id)->delete();

        // delete record in Module Uploaded File if any
        ModuleUploadedFile::where('upload_id', '=', $this->id)->delete();

        //delete all thumbnails if any
        Upload::where('parent_id', '=', $this->id)->delete();

        parent::delete();
    }

    public function createdBy()
    {
        return $this->belongsTo('PCK\Users\User', 'user_id');
    }

    public function generateDeleteURL($id = null)
    {
        return route('moduleUploads.delete', array( $id, $this->id ));
    }

    /**
     * Generates the delete URL for files that are not yet associated with anything (e.g. Projects or Companies)
     *
     * @return string
     */
    public function generateGeneralDeleteURL()
    {
        return route('generalUploads.delete', array( $this->id ));
    }

    public function generateThumbnailURL()
    {
        $fileParts = pathinfo($this->filename);
        $filename  = $fileParts['filename'];
        $ext       = (array_key_exists('extension', $fileParts) && !empty($fileParts['extension'])) ? $fileParts['extension'] : null;

        $thumbnail = $ext && in_array(strtolower($ext), $this->supportedImage) ? $this->publicPath() . $filename . '_84x64.' . $ext : 'img/default-file.png';

        return asset($thumbnail);
    }

    public function getDownloadUrlAttribute()
    {
        $fileParts = pathinfo($this->filename);
        $filename  = $fileParts['filename'];
        $ext       = (array_key_exists('extension', $fileParts) && !empty($fileParts['extension'])) ? ".".$fileParts['extension'] : null;

        return asset($this->publicPath() . $filename . $ext );
    }

    public function process(UploadedFile $file, $fromMobileSync= false)
    {
        // File extension
        $this->extension = $file->getClientOriginalExtension();

        // Mimetype for the file
        $this->mimetype = $file->getMimeType();

        // Current user or 0
        $this->user_id = ( \Auth::user() ? \Auth::user()->id : 0 );

        // overwrite user id that has been posted through BuildSpace
        if( $this->presetUserId )
        {
            $this->user_id = $this->presetUserId;
        }

        $this->size = $file->getSize();

        $this->original_file_name = $file->getClientOriginalName();

        list( $this->path, $this->filename ) = $this->upload($file);

        if($fromMobileSync)
        {
            $withoutExt = preg_replace('/\\.[^.\\s]{2,4}$/', '', $this->filename);

            $this->mobile_sync_uuid = $withoutExt;
        }

        $this->save();

        // Check to see if image thumbnail generation is enabled
        if( static::$app['config']->get('cabinet::image_manipulation') )
        {
            $thumbnails = $this->generateThumbnails($this->path, $this->filename);
            $uploads = array();

            foreach($thumbnails as $thumbnail)
            {
                $upload = new $this;

                $upload->filename = $thumbnail->fileSystemName;

                $upload->path = static::$app['config']->get('cabinet::upload_folder_public_path') . $this->dateFolderPath . $thumbnail->fileSystemName;

                // File extension
                $upload->extension = $thumbnail->getClientOriginalExtension();

                // Mimetype for the file
                $upload->mimetype = $thumbnail->getMimeType();

                // Current user or 0
                $upload->user_id = $this->user_id;

                $upload->size = $thumbnail->getSize();

                $upload->parent_id = $this->id;

                $upload->save();

                $uploads[] = $upload;
            }

            $this->children = $uploads;
        }

        if($fromMobileSync)
        {

        }
    }

    /**
     * Deletes the physical file from the disk.
     *
     * @throws \Exception
     */
    private function deletePhysicalFile()
    {
        $fileDirectory = $this->getFullFilePath();
        if( ! file_exists($fileDirectory) )
        {
            // File cannot be found. Assume deletion is successful.
            return true;
        }
        if( ! unlink($fileDirectory) )
        {
            throw new \Exception("The physical file cannot be deleted.");
        }
    }

    /**
     * Returns the full path for the file.
     *
     * @return string
     */
    public function getFullFilePath()
    {
        return $this->physicalPath() . $this->filename;
    }

    /**
     * Returns true if the uploaded file exists on the disk.
     *
     * @return bool
     */
    public function fileExists()
    {
        return file_exists($this->getFullFilePath());
    }

    public function getCopy()
    {
        $originalPathInfo = pathinfo($this->getFullFilePath());

        $clonedFilePath = tempnam($this->physicalPath(), "{$originalPathInfo['filename']} ");

        $ext = (array_key_exists('extension', $originalPathInfo) && !empty($originalPathInfo['extension'])) ? $originalPathInfo['extension'] : null;

        // extract unique identifier
        $length           = strlen(basename($clonedFilePath));
        $lastSpaceIndex   = strripos(basename($clonedFilePath), ' ');
        $uniqueIdentifier = substr(basename($clonedFilePath), ($lastSpaceIndex + 1), ($length - $lastSpaceIndex));

        $originalFileNameWithoutExtension = str_replace(('.' . $this->extension), '', $this->original_file_name);

        $filename = "{$originalFileNameWithoutExtension}_{$uniqueIdentifier}";

        if( ! is_null($ext) )
        {
            $filename .= ".{$ext}";
        }

        $clonedFilePathInfo = pathinfo($clonedFilePath);

        // copy from original file with new name
        copy($this->getFullFilePath(), $clonedFilePathInfo['dirname'] . DIRECTORY_SEPARATOR . $filename);

        // delete temporary file
        unlink($clonedFilePath);

        \Log::info("[Upload@getCopy] File copied and renamed successfully : " . $clonedFilePathInfo['dirname'] . DIRECTORY_SEPARATOR . $filename);

        $clonedUpload = $this->replicate();
        $clonedUpload->filename = $filename;
        $clonedUpload->save();

        return $clonedUpload;
    }
}
