<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use PCK\Companies\Company;
use Carbon\Carbon;
use PCK\VendorRegistration\VendorRegistrationRepository;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class SetVendorExpiryDate extends Command {

    protected $revertVendorStatusToRegisteringCommand;
    protected $deactivateVendorsCommand;
    protected $vendorRegistrationRepository;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'vendor-management:set-vendor-expiry-date';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets/updates the expiry dates of the specified vendors.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(RevertVendorStatusToRegistering $revertVendorStatusToRegisteringCommand, DeactivateVendors $deactivateVendorsCommand, VendorRegistrationRepository $vendorRegistrationRepository)
    {
        $this->revertVendorStatusToRegisteringCommand = $revertVendorStatusToRegisteringCommand;
        $this->vendorRegistrationRepository           = $vendorRegistrationRepository;
        $this->deactivateVendorsCommand               = $deactivateVendorsCommand;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $transaction = new \PCK\Helpers\DBTransaction();
        $transaction->begin();

        try
        {
            $this->readData();
            $this->process();

            $this->deactivateVendorsCommand->fire();

            $transaction->commit();
        }
        catch(\Exception $e)
        {
            $transaction->rollback();

            $this->error($e->getMessage());
            $this->error($e->getTraceAsString());
        }
    }

    protected $statusesToRevertToDraft = [
        'Draft',
    ];

    protected $statusesToRevertToProcessing = [
        'Processing',
    ];

    protected $placeholdersForInvalidDates = [
        '1900-01-01 00:00:00+06:46:46',
    ];

    const COLUMN_VENDOR_ID = 0;
    const COLUMN_VENDOR_NAME = 1;
    const COLUMN_EXPIRY_DATE = 2;
    const COLUMN_STATUS = 4;

    protected $expiryDates = [];
    protected $vendorsToRevertToDraft = [];
    protected $vendorsToRevertToSubmitted = [];

    public function readData()
    {
        $filePath = $this->argument('file');

        if(!file_exists($filePath))
        {
            $this->info("File not found.");

            return false;
        }

        $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($filePath);
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);

        $reader->setReadDataOnly(true);

        $spreadsheet = $reader->load($filePath);

        if(!$spreadsheet)
        {
            $this->info('Unable to load spreadsheet');

            return false;
        }

        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();

        $highestRow = $sheet->getHighestRow(); 
        $highestColumn = $sheet->getHighestColumn();

        for ($row = 4; $row <= $highestRow; $row++)
        {
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
            $rowData = $rowData[0];

            if(in_array($rowData[self::COLUMN_EXPIRY_DATE], $this->placeholdersForInvalidDates))
            {
                $this->expiryDates[$rowData[self::COLUMN_VENDOR_ID]] = null;
            }
            else
            {
                $newDate = Carbon::createFromFormat('Y-m-d H:i:s+', $rowData[self::COLUMN_EXPIRY_DATE]);

                if(!array_key_exists((int)$rowData[self::COLUMN_VENDOR_ID], $this->expiryDates) || is_null($this->expiryDates[$rowData[self::COLUMN_VENDOR_ID]]) || $newDate->gt($this->expiryDates[$rowData[self::COLUMN_VENDOR_ID]]))
                {
                    $this->expiryDates[$rowData[self::COLUMN_VENDOR_ID]] = $newDate;
                }
            }

            if(in_array($rowData[self::COLUMN_EXPIRY_DATE], $this->placeholdersForInvalidDates))
            {
                if(in_array($rowData[self::COLUMN_STATUS], $this->statusesToRevertToDraft))
                {
                    $this->vendorsToRevertToDraft[$rowData[self::COLUMN_VENDOR_ID]] = $rowData[self::COLUMN_VENDOR_ID];
                }
                elseif(in_array($rowData[self::COLUMN_STATUS], $this->statusesToRevertToProcessing))
                {
                    $this->vendorsToRevertToSubmitted[$rowData[self::COLUMN_VENDOR_ID]] = $rowData[self::COLUMN_VENDOR_ID];
                }
            }
        }
    }

    public function process()
    {
        foreach($this->expiryDates as $thirdPartyVendorId => $expiryDate)
        {
            $dateArgument = is_null($expiryDate) ? null : $expiryDate->toDateTimeString();

            \DB::statement('UPDATE companies set expiry_date = ?, deactivation_date = NULL, deactivated_at = NULL WHERE third_party_vendor_id = ? AND third_party_app_identifier LIKE \'SDPRP%\'', [$dateArgument, $thirdPartyVendorId]);

            if(array_key_exists($thirdPartyVendorId, $this->vendorsToRevertToDraft))
            {
                $this->revertToDraft($thirdPartyVendorId);
            }
            elseif(array_key_exists($thirdPartyVendorId, $this->vendorsToRevertToSubmitted))
            {
                $this->revertToSubmitted($thirdPartyVendorId);
            }
        }
    }

    protected function revertToDraft($thirdPartyVendorId)
    {
        $company = Company::where('third_party_vendor_id', '=', $thirdPartyVendorId)
            ->where('third_party_app_identifier', 'LIKE', 'SDPRP%')
            ->first();

        if(!$company) return;

        $this->revertVendorStatusToRegisteringCommand->revert($company);
    }

    protected function revertToSubmitted($thirdPartyVendorId)
    {
        $this->revertToDraft($thirdPartyVendorId);

        $company = Company::where('third_party_vendor_id', '=', $thirdPartyVendorId)
            ->where('third_party_app_identifier', 'LIKE', 'SDPRP%')
            ->first();

        if(!$company) return;

        $this->vendorRegistrationRepository->submitVendorRegistration($company->vendorRegistration);
    }

    protected function getArguments()
    {
        return array(
                array('file', InputArgument::REQUIRED, 'Path of the file to read.'),
        );
    }
}