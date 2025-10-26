<?php namespace PCK\Tenders\Services;

use PCK\Base\Helpers;
use PCK\Tenders\Tender;
use PCK\Projects\Project;
use PCK\Companies\Company;
use PCK\Tenders\SubmitTenderRate;

class GetTenderAmountFromImportedZip {

    private $project;

    private $tender;

    private $company;

    private $parsedXML;

    private $parsedBillXML = [];

    private $extractToFolderLocation;

    public function __construct(Project $project, Tender $tender, Company $company)
    {
        $this->project = $project;
        $this->tender = $tender;
        $this->company = $company;
    }

    public function parseFile()
    {
        $destinationPath = SubmitTenderRate::getContractorRatesUploadPath($this->project, $this->tender, $this->company);
        $this->extractToFolderLocation = "{$destinationPath}/unzipped/";

        $this->extractToFolder($this->extractToFolderLocation);

        $this->parsedXML = self::getParsedFile($this->extractToFolderLocation);
    }

    public function parseBillFiles() {
        $destinationPath = SubmitTenderRate::getContractorRatesUploadPath($this->project, $this->tender, $this->company);
        $this->extractToFolderLocation = "{$destinationPath}/unzipped/";

        $this->extractToFolder($this->extractToFolderLocation);

        $this->getParsedBillFile($this->extractToFolderLocation);
    }

    public function getParsedBillFile($folder) {
        $firstFile = true;
        $extractedFiles = array_values(array_diff(scandir($folder), ['..', '.']));

        foreach($extractedFiles as $file) {
            if($firstFile) {
                $firstFile = false;
                continue;
            }

            array_push($this->parsedBillXML, [
                'name'      => $file,
                'contents'  => new \SimpleXMLElement(file_get_contents($folder . DIRECTORY_SEPARATOR . $file)),
            ]);
            
        }
    }

    public function getParsedBillFileContents() {
        return $this->parsedBillXML;
    }

    /**
     * Returns the XML Element for Project Main Information.
     *
     * @param $folder
     *
     * @return \SimpleXMLElement
     */
    public static function getParsedFile($folder)
    {
        // read the first file from folder (Project Main Information)
        $extractedFiles = array_values(array_diff(scandir($folder), array( '..', '.' )));

        // read the first xml file
        return new \SimpleXMLElement(file_get_contents($folder . DIRECTORY_SEPARATOR . $extractedFiles[0]));
    }

    public function getTenderAmount()
    {
        if( ! $this->parsedXML->ROOT->tender_amount )
        {
            throw new \Exception('No Tender Amount detected in uploaded rates file!');
        }

        return (string)$this->parsedXML->ROOT->tender_amount;
    }

    public function getSupplyOfMaterialAmount()
    {
        if( ! $this->parsedXML->ROOT->tender_som_amount )
        {
            throw new \Exception('No Tender Supply of Material Amount detected in uploaded rates file!');
        }

        return (string)$this->parsedXML->ROOT->tender_som_amount;
    }

    public function getTenderAmountWithoutPrimeCostAndProvisional()
    {
        if( ! $this->parsedXML->ROOT->tender_amount_except_prime_cost_provisional )
        {
            throw new \Exception('No Tender Amount Except Prime Cost & Provisional detected in uploaded rates file!');
        }

        return (string)$this->parsedXML->ROOT->tender_amount_except_prime_cost_provisional;
    }

    public function getRevisionVersion()
    {
        if( ! isset( $this->parsedXML->REVISIONS->VERSION->version ) )
        {
            // Old rates files do not have version data.
            throw new \Exception(trans('files.outdatedRates'));
        }

        return (int)$this->parsedXML->REVISIONS->VERSION->version;
    }

    public function getTenderAlternativesDetails()
    {
        $records = [];

        if($this->parsedXML->TENDER_ALTERNATIVES)
        {
            foreach($this->parsedXML->TENDER_ALTERNATIVES->TENDER_ALTERNATIVE as $tenderAlternative)
            {
                if(empty((string)$tenderAlternative->project_revision_deleted_at))
                {
                    $records[] = [
                        'id' => (int)$tenderAlternative->id,
                        'tender_amount' => isset($tenderAlternative->tender_amount) ? (float)$tenderAlternative->tender_amount : 0,
                        'tender_amount_except_prime_cost_provisional' => isset($tenderAlternative->tender_amount_except_prime_cost_provisional) ? (float)$tenderAlternative->tender_amount_except_prime_cost_provisional : 0,
                        'tender_som_amount' => isset($tenderAlternative->tender_som_amount) ? (float)$tenderAlternative->tender_som_amount : 0
                    ];
                }
            }
        }
        
        return $records;
    }

    public function getUniqueId()
    {
        if( ! $this->parsedXML->attributes()->uniqueId )
        {
            throw new \Exception('No Unique Id detected in uploaded rates file!');
        }

        return (string)$this->parsedXML->attributes()->uniqueId;
    }

    private function extractToFolder($directory)
    {
        $fileName = SubmitTenderRate::ratesFileName;

        // will need to open and extract the uploaded rates file to get the tender amount
        $zip = new \ZipArchive;

        if( ! $zip->open(SubmitTenderRate::getContractorRatesUploadPath($this->project, $this->tender, $this->company) . "/{$fileName}") )
        {
            throw new \Exception('Cannot open uploaded rates file !');
        }

        $zip->extractTo($directory);

        $zip->close();
    }

    private function deleteFolder($directory)
    {
        // after get the rates, will proceed the folder extracted out
        Helpers::deleteDir($directory);
    }

    public function __destruct()
    {
        $this->deleteFolder($this->extractToFolderLocation);
    }

}