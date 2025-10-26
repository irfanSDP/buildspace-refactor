<?php

class sfZipGenerator {

    public $extension = "ebq";
    public $filename = "archive";
    public $path;
    public $pathToFile;
    public $overwrite;
    public $deleteFileAfterAdd = false;
    public $extractDir;
    public $extractedFiles = array();
    public $fileInfo;
    public $noOfFiles = 0;
    public $noOfFolders = 0;

    protected $zipArchive;
    protected $addedFiles = array();

    function __construct($filename = null, $path = null, $extension = null, $overwrite = false, $deleteFileAfterAdd = false)
    {
        $this->path = ( $path ) ? $path : sfConfig::get('sf_data_dir') . DIRECTORY_SEPARATOR . 'exportedZip' . DIRECTORY_SEPARATOR;

        $this->extension = ( $extension ) ? $extension : $this->extension;

        $this->filename = ( $filename ) ? $filename : $this->filename;

        $this->filename = Utilities::massageText($this->filename);

        $this->overwrite = $overwrite;

        $this->deleteFileAfterAdd = ( $deleteFileAfterAdd ) ? $deleteFileAfterAdd : $this->deleteFileAfterAdd;

        $this->pathToFile = $this->path . $this->filename . '.' . $this->extension;
    }

    public function getFileInfo()
    {
        return $this->fileInfo = array(
            'filename'   => $this->filename,
            'extension'  => $this->extension,
            'path'       => $this->path,
            'pathToFile' => $this->pathToFile
        );
    }

    public function createZip(Array $filesToZip)
    {
        if ( file_exists($this->pathToFile) && !$this->overwrite )
        {
            return false;
        }

        $this->zipArchive = new ZipArchive();

        if ( $this->zipArchive->open($this->pathToFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true )
        {
            die ( "An error occurred creating your ZIP file." );
        }

        //cycle through each file
        foreach ( $filesToZip as $file )
        {
            $this->addFile($file['path'], $file['filename'], $file['extension'], $file['localname'] ?? null);
        }

        return $this->write();
    }


    function unzip($deleteFileAfterExtract = true, $destDir = false, $createZipNameDir = true)
    {
        if ( $zip = zip_open($this->pathToFile) )
        {
            if ( is_resource($zip) )
            {
                $splitter = ( $createZipNameDir === true ) ? "." : "/";

                if ( $destDir === false )
                {
                    $this->extractDir = $destDir = substr($this->pathToFile, 0, strrpos($this->pathToFile, $splitter)) . "/";
                }

                // Create the directories to the destination dir if dont exist
                $this->createDirectories($destDir);

                // For every file in the zip-packet
                while ($zipEntry = zip_read($zip))
                {
                    $posLastSlash = strrpos(zip_entry_name($zipEntry), "/");

                    if ( $posLastSlash !== false )
                    {
                        // Create the directory where the zip-entry should be saved (with a "/" at the end)
                        $this->createDirectories($destDir . substr(zip_entry_name($zipEntry), 0, $posLastSlash + 1));
                        $this->noOfFolders += 1;
                    }

                    // Open the entry
                    if ( zip_entry_open($zip, $zipEntry, "r") )
                    {
                        // The name of the file to save on the disk
                        $pathToFile = $destDir . zip_entry_name($zipEntry);

                        $pathParts = pathinfo($pathToFile);

                        // Check if the files should be overwritten or not
                        if ( $this->overwrite === true || $this->overwrite === false && !is_file($pathToFile) )
                        {
                            // Get the content of the zip entry
                            $fstream = zip_entry_read($zipEntry, zip_entry_filesize($zipEntry));

                            file_put_contents($pathToFile, $fstream);
                            // Set the rights
                            chmod($pathToFile, 0777);

                            if ( is_file($pathToFile) )
                            {
                                array_push($this->extractedFiles, $pathParts);
                                $this->noOfFiles += 1;
                            }
                        }

                        // Close the entry
                        zip_entry_close($zipEntry);
                    }
                }

                // Close the zip-file
                zip_close($zip);

                if ( $deleteFileAfterExtract && file_exists($this->pathToFile))
                {
                    unlink($this->pathToFile);
                }

                return $this->extractedFiles;
            }
        }

        return false;
    }

    protected function createDirectories($path)
    {
        if ( !is_dir($path) )
        {
            $directory_path = "";
            $directories    = explode("/", $path);
            array_pop($directories);

            foreach ( $directories as $directory )
            {
                $directory_path .= $directory . "/";
                if ( !is_dir($directory_path) )
                {
                    mkdir($directory_path);
                    chmod($directory_path, 0777);
                }
            }
        }
    }

    public function checkValidity($pathToFile)
    {
        return file_exists($pathToFile);
    }

    private function addFile($path, $filename, $extension, $localname = null)
    {
        if($this->zipArchive)
        {
            $pathToFile = $this->generatePathToFile($path, $filename, $extension);

            if ( $this->checkValidity($pathToFile) )
            {
                if( ! $localname ) $localname = $filename;

                $this->zipArchive->addFile($pathToFile, $localname . '.' . $extension);

                array_push($this->addedFiles, $pathToFile);
            }
        }
    }

    public function deleteSourceFile()
    {
        foreach ( $this->addedFiles as $pathToFile )
        {
            if(file_exists($pathToFile))
                unlink($pathToFile);
        }
    }

    protected function generatePathToFile($path, $filename, $extension)
    {
        return $path . $filename . '.' . $extension;
    }

    private function write()
    {
        if($this->zipArchive)
            $this->zipArchive->close();

        if ( $this->deleteFileAfterAdd )
        {
            $this->deleteSourceFile();
        }

        return file_exists($this->pathToFile);
    }

}