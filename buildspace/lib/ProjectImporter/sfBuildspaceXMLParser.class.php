<?php

class sfBuildspaceXMLParser
{

    public $extension = 'xml';
    public $filename;
    public $uploadPath;
    public $pathToFile;
    public $xml;
    public $deleteFile = false;

    protected $excludedFields = array(
        'deleted_at',
        'id',
        sfBuildspaceExportBillXML::TAG_RATES,
        sfBuildspaceExportBillXML::TAG_ITEM_LS_PERCENT,
        sfBuildspaceExportBillXML::TAG_ITEM_PC_RATE,
        sfBuildspaceExportBillXML::TAG_TYPEREFERENCES,
        sfBuildspaceExportBillXML::TAG_QTY,
        sfBuildspaceExportBillXML::TAG_HEADSETTING,
        sfBuildspaceExportBillXML::TAG_PHRASE,
        sfBuildspaceExportBillXML::TAG_BILLPAGES,
        sfBuildspaceExportBillXML::TAG_BILLPAGE,
        sfBuildspaceExportBillXML::TAG_COLLECTIONPAGES,
        sfBuildspaceExportProjectXML::TAG_REGION,
        sfBuildspaceExportProjectXML::TAG_SUBREGION,
        sfBuildspaceExportProjectXML::TAG_WORKCAT,
        sfBuildspaceExportProjectXML::TAG_CURRENCY,
        sfBuildspaceExportProjectXML::TAG_PROJECT_SUMMARY_STYLE,
        sfBuildspaceExportProjectXML::TAG_PROJECT_SUMMARY_FOOTER,
        sfBuildspaceExportProjectXML::TAG_PROJECT_SUMMARY_GENERAL_SETTING,
        sfBuildspaceExportBillRatesXML::TAG_ITEM_PRIME_COST
    );

    function __construct($filename = null, $uploadPath = null, $extension = null, $deleteFile = null)
    {
        $this->filename   = ( $filename ) ? $filename : $this->filename;
        $this->uploadPath = ( $uploadPath ) ? $uploadPath : sfConfig::get('sf_upload_dir') . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR;
        $this->extension  = ( $extension ) ? $extension : $this->extension;
        $this->pathToFile = $this->uploadPath . $this->filename . '.' . $this->extension;
        $this->deleteFile = ( $deleteFile ) ? $deleteFile : $this->deleteFile;
    }

    public function setPathToFile($path)
    {
        $this->pathToFile = $path;
    }

    public function read()
    {
        $this->xml = simplexml_load_file($this->pathToFile);
    }

    public function getProcessedData()
    {
        return $this->xml;
    }

    public function endReader()
    {
        unset( $this->xml );

        if ($this->deleteFile && file_exists($this->pathToFile))
        {
            unlink($this->pathToFile);
        }
    }

    public function generateArrayOfSingleData($xmlObject, $generateStructure = false)
    {
        $structure = array();

        $data = array();

        foreach ($xmlObject as $field => $value) {
            if (!in_array($field, $this->excludedFields)) {
                $fieldValue = (string) $xmlObject->{$field};

                if ($fieldValue != '' && $fieldValue != null) {
                    array_push($structure, $field);

                    array_push($data, $fieldValue);
                }


            }
        }

        return array(
            'structure' => $structure,
            'data'      => $data
        );
    }

    public function getExcludedFieldsList()
    {
        return $this->excludedFields;
    }

}