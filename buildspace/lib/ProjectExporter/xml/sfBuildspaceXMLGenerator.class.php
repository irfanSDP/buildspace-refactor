<?php

class sfBuildspaceXMLGenerator
{
    public $extension = 'xml';

    public $filename;

    public $uploadPath;

    public $pathToFile;

    public $xml;

    public $currentParentTag;

    public $previousParentTag;

    public $deleteFile = false;

    function __construct($filename = null, $uploadPath = null, $extension = null, $deleteFile = false)
    {
        $this->filename   = ( $filename ) ? Utilities::massageText($filename) : Utilities::massageText($this->filename);
        $this->uploadPath = ( $uploadPath ) ? $uploadPath : sfConfig::get('sf_upload_dir') . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR;
        $this->extension  = ( $extension ) ? $extension : $this->extension;
        $this->pathToFile = $this->uploadPath . $this->filename . '.' . $this->extension;
        $this->deleteFile = ( $deleteFile ) ? $deleteFile : $this->deleteFile;
    }

    public function getFileInformation()
    {
        return array(
            'filename'  => $this->filename,
            'extension' => $this->extension,
            'path'      => $this->uploadPath
        );
    }

    /*
        Create XML Header and create Wrapper Child and
        attributes if parameter exist
    */
    public function create($wrapperChild = null, $wrapperAttributes = null)
    {
        if ( !$wrapperChild ) //start Tag is mandatory
        {
            return;
        }

        $startTag = $this->createStartTag($wrapperChild);

        $this->xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?>' . $startTag);

        if ( count($wrapperAttributes) > 0 )
        {
            $this->createWrapperAttributes($wrapperAttributes);
        }
    }

    public function createStartTag($tag = null)
    {
        if ( !$tag )
        {
            return;
        }

        return '<' . $tag . '></' . $tag . '>';
    }

    //Create a single Attribute to current wrapper Child
    public function createWrapperAttribute($attribute = 'undefineAttribute', $value = '')
    {
        $this->xml->addAttribute($attribute, $value);
    }

    //create a multiple attributes to current wrapper child
    //parameter: array of attribute 'attribute' => value
    public function createWrapperAttributes($attributes = array())
    {
        foreach ( $attributes as $attribute => $value )
        {
            $this->xml->addAttribute($attribute, $value);
        }
    }

    public function createTag($tag = null, $attributes = array(), $children = array())
    {
        if ( !$tag )
        {
            return null;
        }

        if ( $this->currentParentTag )
        {
            $this->previousParentTag = $this->currentParentTag;
        }

        $this->currentParentTag = $this->xml->addChild($tag);

        if ( count($children) > 0 )
        {
            $this->addChildren($this->currentParentTag, $children);
        }

        if ( count($attributes) > 0 )
        {
            $this->addMultipleAttributes($this->currentParentTag, $attributes);
        }

        return $this->currentParentTag;
    }

    public function addChildTag($tag = null, $child = null, $fieldAndValues = array())
    {
        $childTag = $tag->addChild($child);

        if ( count($fieldAndValues) > 0 )
        {
            $this->addChildren($childTag, $fieldAndValues);
        }

        return $childTag;
    }

    public function addChildren($tag = null, $fieldAndValues)
    {
        foreach ( $fieldAndValues as $field => $value )
        {
            if ( !is_array($value) )
            {
                $tag->addChild($field, htmlspecialchars($value));
            }
        }
    }

    public function addMultipleAttributes($tag = null, $attributes)
    {
        if ( !$tag )
        {
            return;
        }

        foreach ( $attributes as $attribute => $value )
        {
            $this->addSingleAttribute($tag, $attribute, $value);
        }
    }

    public function addSingleAttribute($tag = null, $attribute = "undefine", $value = '')
    {
        if ( !$tag )
        {
            return;
        }

        $tag->addAttribute($attribute, $value);
    }

    public function addCurrentTagChildren($fieldAndValues)
    {
        if ( !$this->currentParentTag )
        {
            return;
        }

        $this->addChildren($this->currentParentTag, $fieldAndValues);
    }

    public function addPreviousTagChildren($fieldAndValues)
    {
        if ( !$this->previousParentTag )
        {
            return;
        }

        $this->addChildren($this->previousParentTag, $fieldAndValues);
    }

    public function write($format = true)
    {
        if ( $format )
        {
            /*
                By Default SimpleXML create unformatted xml structure
                This function read the document and re-structured it
                to a readable XML structure (prettifying)
            */
            $dom                     = new DOMDocument('1.0');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput       = true;
            $dom->loadXML(Utilities::utf8_for_xml($this->xml->asXML()));
            $dom->save($this->pathToFile);
        }
        else
        {
            $this->xml->asXML($this->pathToFile);
        }

        $this->endReader();
    }

    public function endReader()
    {
        if ( $this->deleteFile && file_exists($this->pathToFile))
        {
            unlink($this->pathToFile);
        }
    }

}