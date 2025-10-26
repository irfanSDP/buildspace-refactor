<?php
class sfBuildspaceFileInfoXML extends sfBuildspaceXMLGenerator
{
    public $xml;
    public $files;

    const TAG_FILEINFO = "FILEINFO";
    const TAG_FILES = "FILES";
    const TAG_FILE = "FILE";

    function __construct( $filename = null, $uploadPath = null, $extension = null, $deleteFile = null ) 
    {
        parent::__construct( $filename, $uploadPath, $extension, $deleteFile );
    }

    public function process( $extractDir = false, $extractedFiles = null, $write = true )
    {
        parent::create( self::TAG_FILEINFO, array('extractDir' => $extractDir));

        if ( $extractedFiles && count($extractedFiles) > 0)
        {
            $this->createFilesTag();

            foreach($extractedFiles as $k => $file)
            {
                $this->addChildTag($this->files, self::TAG_FILE, $file);
            }
        }

        if($write)
            parent::write();
    }


    public function createFilesTag() 
    {
        $this->files = parent::createTag( self::TAG_FILES );
    }
}
