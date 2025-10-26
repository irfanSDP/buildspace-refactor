<?php namespace PCK\ScheduledMaintenances;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use PCK\Helpers\Files;

class ScheduledMaintenanceRepository {

    const BASE_PATH = '/upload/maintenance/';

    public function find($id)
    {
        $record = ScheduledMaintenance::findOrFail($id);

        return $record;
    }

    public function upload($uploadedFile, $id , $logoFieldName)
    {
        if (! Files::isImage($uploadedFile))
        {
            return false;
        }

        $scheduled = $this->find($id);

        // Get the original extension
        $extension = strtolower($uploadedFile->getClientOriginalExtension());

        // Define directory and file paths
        $logoDir = public_path(self::BASE_PATH.$id);

        // Get existing file name (if saved)
        $existingLogoFileName = $scheduled->image;

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