<?php namespace PCK\Helpers;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class Zip {

    public static function zip(array $filesToZipByFileName, $zipFilePath = null)
    {
        $zip = new \ZipArchive();

        $zipFilePath = $zipFilePath ?? Files::getTmpFileUri();

        if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
            die ("An error occurred creating your ZIP file.");
        }

        foreach($filesToZipByFileName as $outPutFileName => $filePath)
        {
            $zip->addFile($filePath, $outPutFileName);
        }

        $zip->close();

        return $zipFilePath;
    }

    public static function createEmptyZip($zipFilePath)
    {
        $zip = new \ZipArchive;

        $success = false;

        if ($zip->open($zipFilePath, \ZipArchive::CREATE))
        {
           //Add an empty folder
              $zip->addEmptyDir("Forms");

           // All files are added, so close the zip file.
            $zip->close();

            $success = true;
        }

        return $success;
    }

    public static function unzip(UploadedFile $file, $unzippedFileLocation = null)
    {
        return Files::unzip($file, $unzippedFileLocation);
    }

}