<?php namespace PCK\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Files {

    const EXTENSION_RATES = 'tr';
    const EXTENSION_EBQ   = 'ebq';
    const EXTENSION_PDF   = 'pdf';
    const EXTENSION_EXCEL = 'xlsx';
    const EXTENSION_ZIP   = 'zip';
    const EXTENSION_CLAIM = 'ebqclaim';
    const EXTENSION_LOG   = 'log';

    const FILENAME_FALLBACK_CHARACTER = '_';

    const DEFAULT_COPY_LABEL = 'Copy';

    /**
     * Deletes a file, throws exception on failure.
     *
     * @param $fullFilePath
     *
     * @throws \Exception
     */
    public static function deleteFile($fullFilePath)
    {
        if( ! file_exists($fullFilePath) ) return;

        if( ! unlink($fullFilePath) )
        {
            throw new \Exception("The file cannot be deleted.");
        }
    }

    /**
     * Deletes the directory along with all folders.
     *
     * @param $path
     *
     * @return bool
     * @throws \Exception
     */
    public static function deleteDirectory($path)
    {
        if( is_dir($path) )
        {
            $items = self::getDirectoryItems($path);
            foreach($items as $item)
            {
                self::deleteDirectory($path . '/' . $item);
            }
            rmdir($path);
        }

        if( is_file($path) )
        {
            self::deleteFile($path);
        }

        return true;
    }

    /**
     * Returns all items in a directory.
     *
     * @param $path
     *
     * @return array
     */
    public static function getDirectoryItems($path)
    {
        if( ! is_dir($path) )
        {
            throw new FileNotFoundException();
        }

        return array_values(array_diff(scandir($path), array( '..', '.' )));
    }

    /**
     * Creates a directory if it does not yet exist.
     *
     * @param $directoryPath
     *
     * @return bool
     */
    public static function mkdirIfDoesNotExist($directoryPath)
    {
        if (! file_exists($directoryPath)) {
            //return mkdir($directoryPath, 0777, true);
            return File::makeDirectory($directoryPath, 0777, true);
        }

        return true;
    }

    /**
     * Determines if the file is an image.
     *
     * @param $file
     *
     * @return bool
     */
    public static function isImage($file)
    {
        try
        {
            // Returns false if file is not an image.
            $imageSize = getimagesize($file);
        }
        catch(\Exception $e)
        {
            $imageSize = false;
        }

        if( ! $imageSize )
        {
            return false;
        }

        return true;
    }

    /**
     * As a substitute for \Symfony\Component\HttpFoundation\BinaryFileResponse::download.
     * Using it will throw InvalidArgumentException: The filename fallback must only contain ASCII characters
     * if there are invalid (non-ascii) characters.
     *
     * Reference:
     * http://stackoverflow.com/questions/20094286/laravel-4-the-filename-fallback-must-only-contain-ascii-characters
     *
     * @param        $file
     * @param null   $name
     * @param array  $headers
     * @param string $disposition
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public static function download($file, $name = null, array $headers = array(), $disposition = 'attachment')
    {
        $response = new \Symfony\Component\HttpFoundation\BinaryFileResponse($file, 200, $headers, true);

        if( is_null($name) )
        {
            $name = basename($file);
        }

        $name = self::sanitizeFileName($name);

        return $response->setContentDisposition($disposition, $name, Str::ascii($name));
    }

    /**
     * Replaces invalid characters with a valid one.
     *
     * @param $filename
     *
     * @return mixed
     */
    public static function sanitizeFileName($filename)
    {
        $special_chars = array( "?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}", "%", chr(0) );
        $filename      = preg_replace("#\x{00a0}#siu", ' ', $filename);
        $filename      = str_replace($special_chars, '', $filename);
        $filename      = str_replace(array( '%20', '+' ), '-', $filename);
        $filename      = preg_replace('/[\r\n\t -]+/', '-', $filename);

        return trim($filename, '.-_');
    }

    public static function getTmpFileUri()
    {
        $tmpHandle = tmpfile();
        $metaDatas = stream_get_meta_data($tmpHandle);

        return $metaDatas['uri'];
    }

    /**
     * Checks if a file's extension is correct.
     *
     * @param $extension
     * @param $file
     *
     * @return bool
     */
    public static function hasExtension($extension, UploadedFile $file)
    {
        return ( $extension == $file->getClientOriginalExtension() );
    }

    /**
     * Unzips the file.
     * Returns the location of the unzipped file,
     * or false on failure.
     *
     * @param UploadedFile $file
     * @param null         $folderPath
     * @param true         $uniqueFolderPath Set to true if the folder path has to be unique, i.e. not currently in use.
     *
     * @return bool|string
     */
    public static function unzip(UploadedFile $file, $folderPath = null, $uniqueFolderPath = true)
    {
        if( is_null($folderPath) )
        {
            $folderPath = self::getTmpFolder();
        }
        else
        {
            if( $uniqueFolderPath && file_exists($folderPath) )
            {
                throw new \Exception("Folder ({$folderPath}) already exists.");
            }

            if( ! $uniqueFolderPath && ! self::mkdirIfDoesNotExist($folderPath) )
            {
                throw new AccessDeniedException($folderPath);
            }

            if( ! self::pathIsValid($folderPath) )
            {
                throw new FileNotFoundException();
            }
        }

        //extract
        $zip = new \ZipArchive;

        if( ! $zip->open($file) )
        {
            return false;
        }

        $zip->extractTo($folderPath);
        $zip->close();

        return $folderPath;
    }

    /**
     * Checks if a path is valid.
     *
     * @param $path
     *
     * @return bool
     */
    public static function pathIsValid($path)
    {
        return file_exists($path);
    }

    /**
     * Copies a file.
     *
     * @param $source
     * @param $destination
     *
     * @throws \Exception
     */
    public static function copy($source, $destination)
    {
        if( self::pathIsValid($destination) )
        {
            throw new \Exception('There is already a file with that name!');
        }

        copy($source, $destination);
    }

    /**
     * Generates a unique name for the copied file or folder.
     *
     * @param      $folderPath
     * @param      $originalName
     * @param null $extension
     *
     * @return string
     */
    public static function generateCopyName($folderPath, $originalName, $extension = null)
    {
        $index   = 0;
        $rawName = $originalName;

        if( $extension )
        {
            if( ! strrpos('.', $originalName) )
            {
                $extension = '.' . $extension;
            }

            $rawName = substr($originalName, 0, strlen($originalName) - strlen($extension));
        }

        do
        {
            $index++;

            $newName = $rawName . ' ' . self::DEFAULT_COPY_LABEL . ' ' . $index . $extension;
        }
        while( self::pathIsValid($folderPath . '/' . $newName) );

        return $newName;
    }

    public static function extractFiles($folder)
    {
        return array_values(array_diff(scandir($folder), array( '..', '.' )));
    }

    public static function getFolderContents($folder)
    {
        $extractedFiles = self::extractFiles($folder);
        $files          = array();

        foreach($extractedFiles as $file)
        {
            $files[] = new \SimpleXMLElement(file_get_contents($folder . DIRECTORY_SEPARATOR . $file));
        }

        return $files;
    }

    public static function getTmpFolder($maxAttempts = 10)
    {
        $attempts = 0;

        do
        {
            if( ++$attempts > $maxAttempts ) throw new \Exception("Unable to generate temporary folder. Too many attempts.");

            $timestamp = \Carbon\Carbon::now()->format('Y-m-d_H:i:s');

            $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $timestamp;

        }
        while( file_exists($path) );

        if( ! self::mkdirIfDoesNotExist($path) )
        {
            throw new AccessDeniedException($path);
        }

        return $path;
    }

    public static function getSplFileObjectLastLineNumber($filepath)
    {
        $file = new \SplFileObject($filepath, 'r');
        $file->setFlags(\SplFileObject::SKIP_EMPTY);
        $file->seek(PHP_INT_MAX);

        return $file->key();
    }

    public static function getSplFileObjectLastLine($filepath)
    {
        $file = new \SplFileObject($filepath, 'r');
        $file->seek(self::getSplFileObjectLastLineNumber($filepath)-1);

        return $file->current();
    }
}