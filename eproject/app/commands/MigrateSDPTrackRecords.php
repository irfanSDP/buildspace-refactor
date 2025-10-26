<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

use PCK\VendorCategory\VendorCategory;
use PCK\VendorWorkCategory\VendorWorkCategory;
use PCK\Companies\Company;
use PCK\VendorRegistration\VendorRegistration;
use PCK\TrackRecordProject\TrackRecordProject;

class MigrateSDPTrackRecords extends Command {

    protected $name = 'sdp:migrate-track-records';
    protected $description = 'To migrate SDP track records';

    protected $progressBar;

    public function __construct()
    {
        //$output = new ConsoleOutput();
        
        //ProgressBar::setFormatDefinition('custom', "%status%\n%current%/%max% [%bar%] %percent:3s%%\n  %estimated:-6s%  %memory:6s%");

        //$this->progressBar = new ProgressBar($output, 9);
        //$this->progressBar->setFormat('custom');

        parent::__construct();
    }

    public function fire()
    {
        ini_set('memory_limit','2048M');

        $this->migrate();
    }

    protected function migrate()
    {
        $vendorRecords = Company::select("companies.id AS company_id", "companies.third_party_vendor_id AS vendor_id", "vendor_registrations.id AS vendor_registration_id",
        "vendor_categories.id AS vendor_category_id", "vendor_work_categories.id AS vendor_work_category_id")
        ->join('vendor_registrations', 'vendor_registrations.company_id', '=', 'companies.id')
        ->join(\DB::raw("(SELECT max(revision) AS revision, company_id
            FROM vendor_registrations
            WHERE status = ".VendorRegistration::STATUS_COMPLETED."
            AND deleted_at IS NULL
            GROUP BY company_id) vr"), function($join){
            $join->on('vr.company_id', '=', 'vendor_registrations.company_id');
            $join->on('vr.revision', '=', 'vendor_registrations.revision');
        })
        ->join('contract_group_categories', 'contract_group_categories.id', '=', 'companies.contract_group_category_id')
        ->join('vendors', 'vendors.company_id', '=', 'companies.id')
        ->join('vendor_work_categories', function($join){
            $join->on('vendor_work_categories.id', '=', 'vendors.vendor_work_category_id');
            $join->on('vendor_work_categories.hidden', \DB::raw('IS NOT'), \DB::raw('TRUE'));
        })
        ->join('vendor_categories', 'vendor_categories.contract_group_category_id', '=', 'contract_group_categories.id')
        ->join('vendor_category_vendor_work_category', function($join){
            $join->on('vendor_category_vendor_work_category.vendor_category_id', '=', 'vendor_categories.id');
            $join->on('vendor_category_vendor_work_category.vendor_work_category_id', '=', 'vendor_work_categories.id');
        })
        ->where('companies.confirmed', '=', true)
        ->where('contract_group_categories.hidden', '=', false)
        ->where('vendor_categories.hidden', '=', false)
        ->where('vendor_work_categories.hidden', '=', false)
        ->whereRaw("EXISTS (
            SELECT trackrecordandworkexperiences.vendor_id
            FROM trackrecordandworkexperiences
            WHERE trackrecordandworkexperiences.vendor_id = companies.third_party_vendor_id
            GROUP BY trackrecordandworkexperiences.vendor_id
        )")
        ->orderBy(\DB::raw('companies.id, vendors.type'), 'DESC')
        ->get()
        ->toArray();

        $vendors = [];
        foreach($vendorRecords as $vendor)
        {
            if(!array_key_exists($vendor['vendor_id'], $vendors))
            {
                $vendors[$vendor['vendor_id']] = $vendor;
            }
        }

        $trackRecords = Company::select("companies.id AS company_id", "trackrecordandworkexperiences.vendor_id AS vendor_id",
        "vendor_registrations.id AS vendor_registration_id", "trackrecordandworkexperiences.client_name",
        "trackrecordandworkexperiences.value AS amount", "trackrecordandworkexperiences.currency_code", "trackrecordandworkexperiences.project_status",
        "trackrecordandworkexperiences.project_description", "trackrecordandworkexperiences.city_state", "trackrecordandworkexperiences.scope_of_work",
        "trackrecordandworkexperiences.year")
        ->join('contract_group_categories', 'contract_group_categories.id', '=', 'companies.contract_group_category_id')
        ->join('vendor_registrations', 'vendor_registrations.company_id', '=', 'companies.id')
        ->join(\DB::raw("(SELECT max(revision) AS revision, company_id
            FROM vendor_registrations
            WHERE status = ".VendorRegistration::STATUS_COMPLETED."
            AND deleted_at IS NULL
            GROUP BY company_id) vr"), function($join){
            $join->on('vr.company_id', '=', 'vendor_registrations.company_id');
            $join->on('vr.revision', '=', 'vendor_registrations.revision');
        })
        ->join('trackrecordandworkexperiences', 'trackrecordandworkexperiences.vendor_id', '=', 'companies.third_party_vendor_id')
        ->whereNotNull('trackrecordandworkexperiences.client_name')
        ->where('companies.confirmed', '=', true)
        ->where('contract_group_categories.hidden', '=', false)
        ->orderBy('companies.id', 'DESC')
        ->get()
        ->toArray();
        
        self::arrayBatch($trackRecords, 500, function($batch) use($vendors) {

            $insertRecords = [];
            $questionMarks = [];

            foreach($batch as $trackRecord)
            {
                $startYear = null;
                $endYear = null;

                $dates = explode('-', $trackRecord['year']);

                if(count($dates) == 3)
                {
                    $startYear = date('Y', strtotime($dates[2].'-'.$dates[1].'-'.$dates[0]));

                    continue;
                }

                if(isset($dates[0]) && strlen($dates[0]))
                {
                    $dates[0] = trim($dates[0]);

                    if(strlen($dates[0]) == 4 && is_numeric($dates[0]))
                    {
                        $startYear = $dates[0];
                    }
                    else
                    {
                        $dates[0] = str_ireplace("(1 tahun)", "", $dates[0]);
                        $dates[0] = str_ireplace("(2 tahun)", "", $dates[0]);
                        $dates[0] = str_ireplace("mac", "MARCH", $dates[0]);
                        $dates[0] = str_ireplace("mei", "MAY", $dates[0]);
                        $dates[0] = str_ireplace("ogos", "AUG", $dates[0]);
                        $dates[0] = str_ireplace("okt", "OCT", $dates[0]);
                        $dates[0] = str_ireplace("dis", "DEC", $dates[0]);
                        $dates[0] = str_ireplace("julai", "JULY", $dates[0]);

                        $dates[0] = trim($dates[0]);

                        $startYear = date('Y', strtotime($dates[0]));

                        if((int)$startYear < 1980)
                        {
                            $dmy = DateTime::createFromFormat('d/m/Y', $dates[0]);
                            
                            if( $dmy !== false)
                            {
                                $startYear = $dmy->format('Y');
                            }
                        }
                    }
                }

                if(isset($dates[1]) && strlen($dates[1]) && empty($endYear))
                {
                    $dates[1] = trim($dates[1]);

                    $dates[1] = str_ireplace("(1 tahun)", "", $dates[1]);
                    $dates[1] = str_ireplace("(2 tahun)", "", $dates[1]);
                    $dates[1] = str_ireplace("completed on", "", $dates[1]);
                    $dates[1] = str_ireplace("mac", "MARCH", $dates[1]);
                    $dates[1] = str_ireplace("mei", "MAY", $dates[1]);
                    $dates[1] = str_ireplace("ogos", "AUG", $dates[1]);
                    $dates[1] = str_ireplace("okt", "OCT", $dates[1]);
                    $dates[1] = str_ireplace("dis", "DEC", $dates[1]);
                    $dates[1] = str_ireplace("julai", "JULY", $dates[1]);
                    $dates[1] = str_ireplace("augus", "AUG", $dates[1]);
                    $dates[1] = str_ireplace("augt", "AUG", $dates[1]);
                    $dates[1] = str_ireplace("febuary", "FEBRUARY", $dates[1]);
                    $dates[1] = str_ireplace("(cpc)", "", $dates[1]);
                    $dates[1] = str_ireplace("end", "", $dates[1]);
                    $dates[1] = str_ireplace("(Defect Liability Period)", "", $dates[1]);
                    $dates[1] = str_ireplace("(42%)", "", $dates[1]);
                    $dates[1] = str_ireplace("(83%)", "", $dates[1]);
                    $dates[1] = str_ireplace("(20%)", "", $dates[1]);
                    $dates[1] = str_ireplace("(92%)", "", $dates[1]);
                    $dates[1] = str_ireplace("(63%)", "", $dates[1]);
                    $dates[1] = str_ireplace("(54%)", "", $dates[1]);
                    $dates[1] = str_ireplace("(12%)", "", $dates[1]);
                    $dates[1] = str_ireplace("(95%)", "", $dates[1]);
                    $dates[1] = str_ireplace("(Estimates only)", "", $dates[1]);

                    $dates[1] = str_ireplace("present", $startYear.'-01-01', $dates[1]);

                    $dates[1] = trim($dates[1]);

                    if(strlen($dates[1]) == 4 && is_numeric($dates[1]))
                    {
                        $endYear = $dates[1];
                    }
                    else
                    {
                        $endYear = date('Y', strtotime($dates[1]));

                        if((int)$endYear < 1980)
                        {
                            $dmy = DateTime::createFromFormat('d/m/Y', $dates[1]);
                            
                            if( $dmy !== false)
                            {
                                $endYear = $dmy->format('Y');
                            }
                        }
                    }
                }

                $startYear = ((int)$startYear < 1980) ? 1970 : $startYear;
                $endYear = ((int)$endYear < 1980) ? 1970 : $endYear;

                $now = date('Y-m-d H:i:s');

                if(array_key_exists($trackRecord['vendor_id'], $vendors))
                {
                    $type = (strtolower(trim($trackRecord['project_status'])) == 'completed') ? TrackRecordProject::TYPE_COMPLETED : TrackRecordProject::TYPE_CURRENT;

                    if(strlen(trim($trackRecord['currency_code'])))
                    {
                        $amount = mb_strtoupper(trim($trackRecord['currency_code'])).' '.number_format($trackRecord['amount'], 2, '.', ',');
                    }
                    else
                    {
                        $amount = number_format($trackRecord['amount'], 2, '.', ',');
                    }

                    $record = [
                        mb_strtoupper(trim($trackRecord['project_description']))." ".mb_strtoupper(trim($trackRecord['city_state'])),
                        mb_strtoupper(trim($trackRecord['client_name'])),
                        $vendors[$trackRecord['vendor_id']]['vendor_work_category_id'],
                        $now,
                        $now,
                        $type,
                        $amount,
                        date('Y-m-d H:i:s', strtotime($startYear.'-01-01')),
                        date('Y-m-d H:i:s', strtotime($endYear.'-01-01')),
                        $vendors[$trackRecord['vendor_id']]['vendor_registration_id'],
                        $vendors[$trackRecord['vendor_id']]['vendor_category_id'],
                    ];

                    $insertRecords = array_merge($insertRecords, $record);
                    $questionMarks[] = '('.implode(',', array_fill(0, count($record), '?')).')';
                }
            }

            if($insertRecords)
            {
                $conn=\DB::getPdo();

                try
                {
                    $this->output->write("Inserting records\n");

                    $conn->beginTransaction();

                    $sth = $conn->prepare("INSERT INTO track_record_projects
                    (title, property_developer_text, vendor_work_category_id, created_at, updated_at, type, project_amount, year_of_site_possession, year_of_completion, vendor_registration_id, vendor_category_id)
                    VALUES " . implode(',', $questionMarks));
                    
                    $sth->execute($insertRecords);
                    
                    $conn->commit();

                    $this->output->write("Sucessfully inserted records\n");
                }
                catch(Exception $e)
                {
                    $conn->rollBack();

                    return $this->output->write("Error >> ".$e->getMessage()."\n");
                }

                unset($insertRecords);
            }
        });
    }

    protected static function arrayBatch($arr, $batchSize, $closure)
    {
        $batch = [];
        foreach($arr as $i)
        {
            $batch[] = $i;
            // See if we have the right amount in the batch
            if(count($batch) === $batchSize)
            {
                // Pass the batch into the Closure
                $closure($batch);
                // Reset the batch
                $batch = [];
            }
        }
        // See if we have any leftover ids to process
        if(count($batch)) $closure($batch);
    }
}
