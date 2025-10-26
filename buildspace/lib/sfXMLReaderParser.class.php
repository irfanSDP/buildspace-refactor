<?php

class sfXMLReaderParser
{
	public $filename;
	public $fullFileName;
	public $uploadPath;
	public $extension;
	public $deleteFile;
	public $filePath;
	public $reader;
	
	function __construct($filename, $uploadPath = false, $extension = 'xml', $deleteFile = false) 
    {
        $this->filename		= $filename;
        $this->uploadPath	= ( $uploadPath ) ? $uploadPath : sfConfig::get( 'sf_upload_dir' ).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
        $this->extension 	= $extension;
		$this->fullFileName = $filename.'.'.$extension;
        $this->filePath		= $this->uploadPath.DIRECTORY_SEPARATOR.$this->filename.'.'.$this->extension;
        $this->deleteFile 	= $deleteFile;
		
		$this->reader 		= new XMLReader();
    }
	
	public function getBySingleTag($tag)
	{
		$this->reader->open($this->filePath, 'UTF-8');
		
		$doc = new DOMDocument;
		
		$node = false;
		
		while ($this->reader->read() && $this->reader->name != $tag);
		
		while ($this->reader->name == $tag)
		{
			$node = simplexml_import_dom($doc->importNode($this->reader->expand(), true));

			break;
		}

		$this->reader->close();
		
		return ($node) ? json_decode(json_encode($node), true) : false;
	}
}
	