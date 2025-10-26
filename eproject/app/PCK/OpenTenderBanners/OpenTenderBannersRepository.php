<?php namespace PCK\OpenTenderBanners;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use PCK\Helpers\Files;

class OpenTenderBannersRepository {

    const BASE_PATH = '/upload/banner/';

    public function find($id)
    {
        $record = OpenTenderBanners::findOrFail($id);

        return $record;
    }

    public function upload($uploadedFile, $id , $logoFieldName)
    {
        if (! Files::isImage($uploadedFile))
        {
            return false;
        }

        $banner = $this->find($id);

        // Get the original extension
        $extension = strtolower($uploadedFile->getClientOriginalExtension());

        // Define directory and file paths
        $logoDir = public_path(self::BASE_PATH.$id);

        // Get existing file name (if saved)
        $existingLogoFileName = $banner->image;

        if (! empty($existingLogoFileName)) {
            $existingLogoFile = $logoDir . '/' . $existingLogoFileName;

            // Delete old image file if exist
            if (file_exists($existingLogoFile)) {
                Files::deleteFile($existingLogoFile);
            }
        }

        // Move uploaded file
        Files::mkdirIfDoesNotExist($logoDir);   // Create the folder if it does not exist
        $uploadedFile->move($logoDir, $logoFieldName);

        return true;
    }

}