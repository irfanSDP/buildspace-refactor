<?php

class migrateSdprpBalanceVendorTask extends sfBaseTask
{
    protected $sdprpCon;
    protected $eprojectCon;
    protected $buildspaceCon;

    const VALIDITY_MONTH      = 1;
    const VALIDITY_WEEK       = 2;
    const VALIDITY_DAY        = 4;
    const VALIDITY_PERCENTAGE = 8;

    const SECTION_COMPANY_DETAILS = 1;
    const SECTION_COMPANY_PERSONNEL = 2;
    const SECTION_PROJECT_TRACK_RECORD = 3;
    const SECTION_SUPPLIER_CREDIT_FACILITIES = 4;
    const SECTION_PAYMENT = 5;

    protected function configure()
    {
        $this->namespace           = 'sdprp';
        $this->name                = 'migrate-balance-vendor';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [{$this->name}|INFO] task does things.
Call it with:

  [php symfony {$this->name}|INFO]
EOF;
    }

    protected function getEprojectConnection()
    {
        if(!$this->eprojectCon)
        {
            $databaseManager = new sfDatabaseManager($this->configuration);

            $this->eprojectCon = $databaseManager->getDatabase('eproject_conn')->getConnection();
        }

        return $this->eprojectCon;
    }

    protected function getBuildspaceConnection()
    {
        if(!$this->buildspaceCon)
        {
            $databaseManager = new sfDatabaseManager($this->configuration);

            $this->buildspaceCon = $databaseManager->getDatabase('main_conn')->getConnection();
        }

        return $this->buildspaceCon;
    }

    protected function execute($arguments = array(), $options = array())
    {
        ini_set('memory_limit','2048M');

        // initialize the database connection
        $eprojectConn = $this->getEprojectConnection();

        $this->logSection('Migration', 'Querying records...');

        $stmt = $eprojectConn->prepare("SELECT vc.level_1, vp.*
        FROM sdp_vendor_profile vp
        JOIN sdp_vendor_categories vc ON vp.vendor_id = vc.vendor_id
        ORDER BY vp.vendor_id");

        $stmt->execute();

        $sdprpVendorProfiles = $stmt->fetchAll(PDO::FETCH_ASSOC);
       
        $eprojectStates = $this->getEprojectStates();
        $eprojectBusinessEntityTypes = $this->getEprojectBusinessEntityTypes();
        $eprojectContractGroups = $this->getEprojectContracGroups();

        $sdprpStates = [
            'malaysia' => [
                'WILAYAH PERSEKUTUAN (LABUAN)' => 'Federal Territory of Labuan',
                'WILAYAH PERSEKUTUAN (PUTRAJAYA)' => 'Putrajaya',
                'WILAYAH PERSEKUTUAN (KUALA LUMPUR)' => 'Kuala Lumpur',
                'other' => 'Selangor',
                'null' => 'Kuala Lumpur'
            ]
        ];

        $sdprpBusinessEntityTypes = [
            'privateltd' => 'Private Limited',
            'publicltd' => 'Public Limited',
            'soleproprietary' => 'Sole Proprieter',
            'partnership' => 'Partnership',
            'advance chemtech  (wholly owned by albe advance sdn bhd)' => 'Private Limited'
        ];

        $sdprpCoTypes = [
            'enterprises' => 'ENTERPRISE',
            'individu' => 'INDIVIDUAL',
            'keporasi' => 'KOPERASI',
            'perhidmatan dan pengangutan' => 'Perkhidmatan dan Pengangkutan',
            'sdn. bhd.' => 'SENDIRIAN BERHAD',
            'sdn bhd' => 'SENDIRIAN BERHAD',
            'sendiran berhad' => 'SENDIRIAN BERHAD',
            '1' => 'OTHERS'
        ];

        $excludeTaxNo = [
            '-',
            '--- Please Select ---',
            ',',
            '..OG 03546502060',
            '(-)',
            '(E) 2912727-00',
            '(the file no. will be release after audit in June 2014)',
            0,
            '0',
            '001',
            '111',
            '1234',
            '2018-12-22 23:59:59.000',
            'in process',
            'In Processing',
            'N',
            'N.A.',
            'N/ A',
            'N/A',
            'N/A in the process of applying',
            'N/A yet',
            'NA',
            'NAx',
            'NIL',
            'NL',
            'No',
            'Not Available',
            'Not available yet',
            'not yet register',
            'Not yet registered',
            'NULL',
            'pending',
            'Registration soon',
            'TBA',
            'TBC',
            'To be advised',
            'will provide soon',
            'will submit by Oct 2014'
        ];
        
        $stmt = $eprojectConn->prepare("SELECT c.reference_no
        FROM companies c");

        $stmt->execute();

        $existingReferenceNo = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        
        $stmt = $eprojectConn->prepare("SELECT c.reference_id
        FROM companies c
        WHERE reference_id ILIKE 'SDPRP%'
        ORDER BY reference_id DESC");

        $stmt->execute();

        $latestReferenceId = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        $referenceIdCount = ($latestReferenceId) ? (int)str_replace('SDPRP', '', $latestReferenceId) + 1 : 1;

        $companies = [];
        $companyToUpdate = [];

        $vendorIds = [];
        $vendorCategoryVendorIds = [];

        foreach($sdprpVendorProfiles as $idx => $sdprpVendorProfile)
        {
            $vendorId = trim($sdprpVendorProfile['vendor_id']);

            $vendorIds[] = $vendorId;

            if(!array_key_exists($vendorId, $companies))
            {
                $sdprpCountry = trim(strtolower($sdprpVendorProfile['country']));

                $sdprpState = trim(strtolower($sdprpVendorProfile['state']));
                $sdprpState = empty($sdprpState) ? 'null' : $sdprpState;

                $sdprpCity = trim(strtolower($sdprpVendorProfile['city']));

                if(!empty($sdprpCountry) && strlen($sdprpCountry) > 0 && array_key_exists($sdprpCountry, $sdprpStates))
                {
                    $sdprpStates[$sdprpCountry] = array_change_key_case($sdprpStates[$sdprpCountry]);

                    if(array_key_exists($sdprpState, $sdprpStates[$sdprpCountry]))
                    {
                        $sdprpVendorProfile['state'] = $sdprpStates[$sdprpCountry][$sdprpState];
                    }
                }

                if($sdprpCity == strtolower($sdprpVendorProfile['state']) or $sdprpCity == 'nil')
                {
                    $sdprpVendorProfile['city'] = null;
                }

                $sdprpCoType = trim(strtolower($sdprpVendorProfile['co_type']));

                if(!empty($sdprpCoType) && strlen($sdprpCoType) > 0 && array_key_exists($sdprpCoType, $sdprpBusinessEntityTypes))
                {
                    $sdprpVendorProfile['co_type'] = $sdprpBusinessEntityTypes[$sdprpCoType];
                }

                if(!empty($sdprpCoType) && strlen($sdprpCoType) > 0 && array_key_exists($sdprpCoType, $sdprpCoTypes))
                {
                    $sdprpVendorProfile['co_type'] = $sdprpCoTypes[$sdprpCoType];
                }

                $stateId = $eprojectStates[$sdprpCountry][strtolower(trim($sdprpVendorProfile['state']))]['state_id'];
                $countryId = $eprojectStates[$sdprpCountry][strtolower(trim($sdprpVendorProfile['state']))]['country_id'];

                $address = trim($sdprpVendorProfile['addr1']);
                $address .= (trim($sdprpVendorProfile['addr2'])) ? "\n".trim($sdprpVendorProfile['addr2']) : null;
                $address .= (trim($sdprpVendorProfile['addr3'])) ? "\n".trim($sdprpVendorProfile['addr3']) : null;
                $address .= (trim($sdprpVendorProfile['postcode'])) ? "\n".trim($sdprpVendorProfile['postcode']) : null;
                $address .= (trim($sdprpVendorProfile['city'])) ? "\n".trim($sdprpVendorProfile['city']) : null;

                $referenceNo = trim($sdprpVendorProfile['co_reg_no']);

                if(in_array($referenceNo, $existingReferenceNo))
                {
                    $referenceNo = 'SDPRP/CO/REG/'.sprintf('%07d', $vendorId);
                }

                $registrationId = self::generateRawRegistrationIdentifier($referenceNo);
                $taxRegistrationNo = trim($sdprpVendorProfile['gst_registration_no']);

                if(!empty($taxRegistrationNo) && in_array($taxRegistrationNo, $excludeTaxNo))
                {
                    $taxRegistrationNo = null;
                }

                $taxRegistrationId = null;
                if( $taxRegistrationNo)
                {
                    $taxRegistrationId = self::generateRawRegistrationIdentifier($taxRegistrationNo);
                }

                $contractGroupId = null;
                $contractGroupName = trim(strtolower($sdprpVendorProfile['level_1']));

                if(array_key_exists($contractGroupName, $eprojectContractGroups))
                {
                    $contractGroupId = $eprojectContractGroups[$contractGroupName];
                }
                
                $bumiputeraEquity = trim(strtolower($sdprpVendorProfile['bumiputra_holding_percentage']));
                $nonBumiputeraEquity = trim(strtolower($sdprpVendorProfile['nonbumiputraholding_percentage']));
                $foreignEquity = trim(strtolower($sdprpVendorProfile['foreign_holding_percentage']));

                $email = trim($sdprpVendorProfile['email']);

                if($email)
                {
                    $list = explode(';', $email);
                    if(!empty($list))
                    {
                        $email = $list[0];
                    }
                    else
                    {
                        $list = explode(',', $email);
                        if(!empty($list))
                        {
                            $email = $list[0];
                        }
                    }
                }

                $referenceId = 'SDPRP'.sprintf('%011d', $referenceIdCount);

                if($registrationId == '1003019p' or $registrationId == '306236u')
                {
                    $companyToUpdate[$registrationId] = [
                        'vendor_id' => $vendorId,
                        'contract_group_category_id' => $contractGroupId,
                        'activation_date' => $sdprpVendorProfile['appl_creation_date'],
                        'expiry_date' => strtotime($sdprpVendorProfile['registration_expiry_date']) < strtotime('2000-01-01') ? date('Y-m-d H:i:s', strtotime($sdprpVendorProfile['appl_creation_date'].' +36 month')) : $sdprpVendorProfile['registration_expiry_date']
                    ];
                }
                else
                {
                    $vendorCategoryVendorIds[] = $vendorId;

                    $companies[$vendorId] = [
                        'vendor_id' => $vendorId,
                        'vendor_name' => trim($sdprpVendorProfile['vendor_name']),
                        'address' => $address,
                        'main_contact' => trim($sdprpVendorProfile['person_incharge']) ? trim($sdprpVendorProfile['person_incharge']) : '-',
                        'email' => ($email) ? $email : '-',
                        'telephone_number' => trim($sdprpVendorProfile['tel_no']) ? trim($sdprpVendorProfile['tel_no']) : '-',
                        'country_id' => $countryId,
                        'state_id' => $stateId,
                        'reference_no' => $referenceNo,
                        'reference_id' => $referenceId,
                        'contract_group_category_id' => $contractGroupId,
                        'registration_id' => $registrationId,
                        'tax_registration_no' => $taxRegistrationNo,
                        'tax_registration_id' => $taxRegistrationId,
                        'business_entity_type_id' => (!empty($sdprpVendorProfile['co_type']) && array_key_exists(strtolower($sdprpVendorProfile['co_type']), $eprojectBusinessEntityTypes)) ? $eprojectBusinessEntityTypes[strtolower($sdprpVendorProfile['co_type'])] : null,
                        'business_entity_type_name' => (!empty($sdprpVendorProfile['co_type']) && !array_key_exists(strtolower($sdprpVendorProfile['co_type']), $eprojectBusinessEntityTypes)) ? $sdprpVendorProfile['co_type'] : null,
                        'is_bumiputera' => (trim(strtolower($sdprpVendorProfile['bumiputera_status'])) == 'bumiputra') ? 1 : 0,
                        'bumiputera_equity' => (is_numeric($bumiputeraEquity)) ? $bumiputeraEquity : 0,
                        'non_bumiputera_equity' => (is_numeric($nonBumiputeraEquity)) ? $nonBumiputeraEquity : 0,
                        'foreigner_equity' => (is_numeric($foreignEquity)) ? $foreignEquity : 0,
                        'activation_date' => $sdprpVendorProfile['appl_creation_date'],
                        'expiry_date' => strtotime($sdprpVendorProfile['registration_expiry_date']) < strtotime('2000-01-01') ? date('Y-m-d H:i:s', strtotime($sdprpVendorProfile['appl_creation_date'].' +36 month')) : $sdprpVendorProfile['registration_expiry_date']
                    ];
    
                    $referenceIdCount++;
                }
            }
        }
        
        foreach($companyToUpdate as $registrationId => $company)
        {
            try
            {
                $this->logSection('Companies Migration', 'Updating records...');

                $eprojectConn->beginTransaction();
                
                $stmt = $eprojectConn->prepare("UPDATE companies SET
                third_party_app_identifier = 'SDPRP', third_party_vendor_id=".$company['vendor_id'].", activation_date='".$company['activation_date']."', expiry_date='".$company['expiry_date']."'
                WHERE registration_id = '".$registrationId."'");

                $stmt->execute();
                
                $eprojectConn->commit();

                $this->logSection('Companies Migration', 'Successfully updated '.$registrationId.' Company!');
            }
            catch(Exception $e)
            {
                $eprojectConn->rollBack();
                throw $e;
            }
        }

        if(!empty($companies))
        {
            self::arrayBatch($companies, 500, function($batch) {
                
                $eprojectConn = $this->getEprojectConnection();

                $this->logSection('Companies Migration', 'Inserting '.count($batch).' company records...');

                $insertCompanies = [];
                $questionMarks = [];

                $date = date('Y-m-d H:i:s');

                foreach($batch as  $company)
                {
                    $record = [
                        mb_strtoupper($company['vendor_name'], 'UTF-8'),
                        $company['address'],
                        $company['main_contact'],
                        $company['email'],
                        $company['telephone_number'],
                        $company['country_id'],
                        $company['state_id'],
                        mb_strtoupper($company['reference_no'], 'UTF-8'),
                        $company['reference_id'],
                        $company['contract_group_category_id'],
                        1,
                        $company['registration_id'],
                        $company['tax_registration_no'],
                        $company['tax_registration_id'],
                        $company['business_entity_type_id'],
                        $company['business_entity_type_name'],
                        $company['is_bumiputera'],
                        $company['bumiputera_equity'],
                        $company['non_bumiputera_equity'],
                        $company['foreigner_equity'],
                        'SDPRP',
                        $company['vendor_id'],
                        $company['activation_date'],
                        $company['expiry_date'],
                        $date,
                        $date
                    ];
    
                    $insertCompanies = array_merge($insertCompanies, $record);
                    $questionMarks[] = '('.implode(',', array_fill(0, 26, '?')).')';
                }

                if(!empty($insertCompanies))
                {
                    try
                    {
                        $this->logSection('Companies Migration', 'Inserting records...');

                        $eprojectConn->beginTransaction();
                        
                        $stmt = $eprojectConn->prepare("INSERT INTO companies
                        (name, address, main_contact, email, telephone_number, country_id, state_id, reference_no, reference_id, contract_group_category_id, confirmed,
                        registration_id, tax_registration_no, tax_registration_id, business_entity_type_id, business_entity_type_name, is_bumiputera, bumiputera_equity,
                        non_bumiputera_equity, foreigner_equity, third_party_app_identifier, third_party_vendor_id, activation_date, expiry_date, created_at, updated_at)
                        VALUES " . implode(',', $questionMarks)." RETURNING third_party_vendor_id, id");

                        $stmt->execute($insertCompanies);

                        $returnedRecords = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
                        
                        $eprojectConn->commit();

                        $this->logSection('Companies Migration', 'Successfully migrated '.count($batch).' Companies!');
                    }
                    catch(Exception $e)
                    {
                        $eprojectConn->rollBack();

                        throw $e;
                        return $this->logSection('Companies Migration', $e);
                    }

                    unset($insertCompanies);
                }
            });
        }

        $this->createCompanyVendorCategories($vendorCategoryVendorIds);

        $this->createVendorRegistrations($vendorIds);

        $this->createVendors($vendorIds);

        $this->createCompanyDirectors($vendorIds);

        $this->createShareHolders($vendorIds);

        $this->createBuildspaceCompanies($vendorCategoryVendorIds);

        return $this->logSection('Companies Migration', 'Successfully migrated '.count($vendorIds).' Companies!');
    }

    protected function createVendors(Array $vendorIds)
    {
        if(!empty($vendorIds))
        {
            $eprojectConn = $this->getEprojectConnection();

            $this->logSection('Vendors Migration', 'Inserting '.count($vendorIds).' vendor records...');

            $stmt = $eprojectConn->prepare("SELECT c.id AS company_id, vwc.id AS vendor_work_category_id
                FROM vendor_categories vc
                JOIN contract_group_categories cg ON vc.contract_group_category_id = cg.id
                JOIN sdp_vendor_categories svc ON TRIM(svc.level_1) = TRIM(cg.name) AND TRIM(svc.level_2) = TRIM(vc.name)
                JOIN vendor_work_categories vwc ON TRIM(vwc.name) = TRIM(svc.level_3)
                JOIN vendor_category_vendor_work_category x ON x.vendor_category_id = vc.id AND vendor_work_category_id = vwc.id
                JOIN companies c ON c.third_party_vendor_id = svc.vendor_id AND c.third_party_app_identifier = 'SDPRP'
                WHERE svc.vendor_id IN (".implode(',', $vendorIds).")
                GROUP BY c.id, vwc.id
                ORDER BY c.id, vwc.id
            ");

            $stmt->execute();

            $vendorWorkCategoryIds = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $insertRecords = [];
            $questionMarks = [];

            $date = date('Y-m-d H:i:s');

            foreach($vendorWorkCategoryIds as $vendorWorkCategoryId)
            {
                $record = [
                    $vendorWorkCategoryId['vendor_work_category_id'],
                    $vendorWorkCategoryId['company_id'],
                    1,//type active
                    $date,
                    $date
                ];

                $insertRecords = array_merge($insertRecords, $record);
                $questionMarks[] = '('.implode(',', array_fill(0, count($record), '?')).')';
            }

            if(!empty($insertRecords))
            {
                try
                {
                    $this->logSection('Vendors Migration', 'Inserting records...');

                    $eprojectConn->beginTransaction();
                    
                    $stmt = $eprojectConn->prepare("INSERT INTO vendors
                    (vendor_work_category_id, company_id, type, created_at, updated_at)
                    VALUES " . implode(',', $questionMarks));

                    $stmt->execute($insertRecords);
                    
                    $eprojectConn->commit();

                    $this->logSection('Vendors Migration', 'Successfully migrated Vendors!');
                }
                catch(Exception $e)
                {
                    $eprojectConn->rollBack();

                    throw $e;
                }

                unset($insertRecords);
            }
        }
    }

    protected function createVendorRegistrations(Array $vendorIds)
    {
        if(!empty($vendorIds))
        {
            $eprojectConn = $this->getEprojectConnection();

            $this->logSection('Vendor Registration Migration', 'Inserting '.count($vendorIds).' vendor registration records...');

            $stmt = $eprojectConn->prepare("SELECT c.id
                FROM companies c
                WHERE c.third_party_vendor_id IN (".implode(',', $vendorIds).")
                AND c.third_party_app_identifier = 'SDPRP'
            ");

            $stmt->execute();

            $companyIds = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

            $insertVendorRegistrations = [];
            $questionMarks = [];

            $date = date('Y-m-d H:i:s');

            $insertVendorProfiles = [];
            $vendorProfileQuestionMarks = [];

            foreach($companyIds as  $companyId)
            {
                $record = [
                    $companyId,
                    8,//status completed
                    $date,
                    $date,
                    "EXPORTED FROM DATA MIGRATION"
                ];

                $insertVendorRegistrations = array_merge($insertVendorRegistrations, $record);
                $questionMarks[] = '('.implode(',', array_fill(0, count($record), '?')).')';

                $vendorProfile = [
                    $companyId,
                    $date,
                    $date
                ];

                $insertVendorProfiles = array_merge($insertVendorProfiles, $vendorProfile);
                $vendorProfileQuestionMarks[] = '('.implode(',', array_fill(0, count($vendorProfile), '?')).')';
            }

            if(!empty($insertVendorRegistrations))
            {
                try
                {
                    $this->logSection('Vendor Profile Migration', 'Inserting records...');

                    $eprojectConn->beginTransaction();
                    
                    $stmt = $eprojectConn->prepare("INSERT INTO vendor_profiles
                    (company_id, created_at, updated_at)
                    VALUES " . implode(',', $vendorProfileQuestionMarks));

                    $stmt->execute($insertVendorProfiles);
                    
                    $eprojectConn->commit();

                    $this->logSection('Vendor Profiles Migration', 'Successfully migrated '.count($companyIds).' Vendor Profiles!');
                }
                catch(Exception $e)
                {
                    $eprojectConn->rollBack();

                    throw $e;
                }

                try
                {
                    $this->logSection('Companies Migration', 'Inserting records...');

                    $eprojectConn->beginTransaction();
                    
                    $stmt = $eprojectConn->prepare("INSERT INTO vendor_registrations
                    (company_id, status, created_at, updated_at, processor_remarks)
                    VALUES " . implode(',', $questionMarks) ." RETURNING id");

                    $stmt->execute($insertVendorRegistrations);
                    
                    $returnedRecords = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

                    $eprojectConn->commit();

                    $this->logSection('Vendor Registrations Migration', 'Successfully migrated '.count($companyIds).' Vendor Registrations!');

                    $this->createVendorRegistrationSections($returnedRecords);
                }
                catch(Exception $e)
                {
                    $eprojectConn->rollBack();

                    throw $e;
                }

                unset($insertVendorRegistrations);
            }
        }
    }

    protected function createVendorRegistrationSections(Array $vendorRegistrationIds)
    {
        if(!empty($vendorRegistrationIds))
        {
            $eprojectConn = $this->getEprojectConnection();

            $this->logSection('Vendors Registration Sections Migration', 'Inserting vendor registration section records...');

            $sections = [
                self::SECTION_COMPANY_DETAILS,
                self::SECTION_COMPANY_PERSONNEL,
                self::SECTION_PROJECT_TRACK_RECORD,
                self::SECTION_SUPPLIER_CREDIT_FACILITIES,
                self::SECTION_PAYMENT,
            ];

            $date = date('Y-m-d H:i:s');

            foreach($sections as $section)
            {
                $insertRecords = [];
                $questionMarks = [];

                foreach($vendorRegistrationIds as $vendorRegistrationId)
                {
                    $record = [
                        $vendorRegistrationId,
                        $section,
                        8,//status completed
                        1, //ammendment status not required
                        $date,
                        $date
                    ];

                    $insertRecords = array_merge($insertRecords, $record);
                    $questionMarks[] = '('.implode(',', array_fill(0, count($record), '?')).')';
                }

                if(!empty($insertRecords))
                {
                    try
                    {
                        $this->logSection('Vendor Registration Section Migration', 'Inserting records...');

                        $eprojectConn->beginTransaction();
                        
                        $stmt = $eprojectConn->prepare("INSERT INTO vendor_registration_sections
                        (vendor_registration_id, section, status_id, amendment_status, created_at, updated_at)
                        VALUES " . implode(',', $questionMarks));

                        $stmt->execute($insertRecords);
                        
                        $eprojectConn->commit();

                        $this->logSection('Vendor Registration Section Migration', 'Successfully migrated Vendor Registration Sections!');
                    }
                    catch(Exception $e)
                    {
                        $eprojectConn->rollBack();

                        throw $e;
                    }

                    unset($insertRecords);
                }
            }
        }
    }

    protected function createCompanyVendorCategories(Array $vendorIds)
    {
        if(!empty($vendorIds))
        {
            $eprojectConn = $this->getEprojectConnection();

            $stmt = $eprojectConn->prepare("SELECT c.id AS company_id, vc.id AS vendor_category_id
                FROM vendor_categories vc
                JOIN contract_group_categories cg ON vc.contract_group_category_id = cg.id
                JOIN sdp_vendor_categories svc ON TRIM(svc.level_1) = TRIM(cg.name) AND TRIM(svc.level_2) = TRIM(vc.name)
                JOIN companies c ON c.third_party_vendor_id = svc.vendor_id AND c.third_party_app_identifier = 'SDPRP'
                WHERE svc.vendor_id IN (".implode(',', $vendorIds).")
                GROUP BY c.id, vc.id
                ORDER BY c.id, vc.id
            ");

            $stmt->execute();

            $vendorCategoryIds = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $insertRecords = [];
            $questionMarks = [];

            $record = [];
            foreach($vendorCategoryIds as $vendorCategoryId)
            {
                $record = [
                    $vendorCategoryId['company_id'], $vendorCategoryId['vendor_category_id']
                ];

                $insertRecords = array_merge($insertRecords, $record);
                $questionMarks[] = '('.implode(',', array_fill(0, count($record), '?')).')';
            }

            if(!empty($insertRecords))
            {
                $eprojectConn = $this->getEprojectConnection();

                try
                {
                    $this->logSection('Company Vendor Category Migration', 'Inserting records...');

                    $eprojectConn->beginTransaction();
                    
                    $stmt = $eprojectConn->prepare("INSERT INTO company_vendor_category
                    (company_id, vendor_category_id)
                    VALUES " . implode(',', $questionMarks));

                    $stmt->execute($insertRecords);
                    
                    $eprojectConn->commit();

                    $this->logSection('Company Vendor Category Migration', 'Successfully migrated Company Vendor Categories!');
                }
                catch(Exception $e)
                {
                    $eprojectConn->rollBack();

                    throw $e;
                }

                unset($insertRecords);
            }
        }
    }

    protected function createCompanyDirectors(Array $vendorIds)
    {
        $eprojectConn = $this->getEprojectConnection();

        $stmt = $eprojectConn->prepare("SELECT r.id AS vendor_registration_id, o.name, o.ic_passport_no_co_reg_no_other, o.identification_type, designation, o.email, o.contactnumber
            FROM sdp_owners_directors o
            JOIN companies c ON c.third_party_vendor_id = o.vendor_id AND c.third_party_app_identifier = 'SDPRP'
            JOIN vendor_registrations r ON r.company_id = c.id
            WHERE o.vendor_id IN (".implode(',', $vendorIds).")
            ORDER BY c.id, o.name");

        $stmt->execute();

        $directors = $stmt->fetchAll(PDO::FETCH_ASSOC);

        self::arrayBatch($directors, 500, function($batch)use($eprojectConn) {
            $date = date('Y-m-d H:i:s');

            $insertRecords = [];
            $questionMarks = [];

            foreach($batch as $director)
            {
                $record = [
                    1,//type director,
                    mb_strtoupper(trim($director['name']), 'UTF-8'),
                    trim($director['ic_passport_no_co_reg_no_other']),
                    trim($director['email']),
                    trim($director['contactnumber']),
                    trim($director['designation']),
                    $director['vendor_registration_id'],
                    $date,
                    $date
                ];

                $insertRecords = array_merge($insertRecords, $record);
                $questionMarks[] = '('.implode(',', array_fill(0, 9, '?')).')';
            }

            if(!empty($insertRecords))
            {
                try
                {
                    $this->logSection('Owner/Director Info Migration', 'Inserting records...');

                    $eprojectConn->beginTransaction();
                    
                    $stmt = $eprojectConn->prepare("INSERT INTO company_personnel
                    (type, name, identification_number, email_address, contact_number, designation, vendor_registration_id, created_at, updated_at)
                    VALUES " . implode(',', $questionMarks));

                    $stmt->execute($insertRecords);
                    
                    $eprojectConn->commit();

                    $this->logSection('Owner/Director Info Migration', 'Successfully migrated Owner/Director Info!');
                }
                catch(Exception $e)
                {
                    $eprojectConn->rollBack();

                    throw $e;
                }

                unset($insertRecords);
            }
        });
    }

    protected function createShareHolders(Array $vendorIds)
    {
        $eprojectConn = $this->getEprojectConnection();

        $stmt = $eprojectConn->prepare("SELECT r.id AS vendor_registration_id, s.name_compname, s.ic_passport_no_co_reg_no, s.shareholdings_percentage, s.identification_type, s.designation, s.email
            FROM sdp_shareholders s
            JOIN companies c ON c.third_party_vendor_id = s.vendor_id AND c.third_party_app_identifier = 'SDPRP'
            JOIN vendor_registrations r ON r.company_id = c.id
            WHERE s.vendor_id IN (".implode(',', $vendorIds).")
            ORDER BY c.id, s.name_compname");

        $stmt->execute();

        $shareholders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        self::arrayBatch($shareholders, 500, function($batch)use($eprojectConn) {
            $date = date('Y-m-d H:i:s');

            $insertRecords = [];
            $questionMarks = [];

            foreach($batch as $shareholder)
            {
                $holdingPercentage = (trim($shareholder['shareholdings_percentage'])) ? trim($shareholder['shareholdings_percentage']) : null;
                $record = [
                    2,//type shareholder,
                    mb_strtoupper(trim($shareholder['name_compname']), 'UTF-8'),
                    trim($shareholder['ic_passport_no_co_reg_no']),
                    trim($shareholder['email']),
                    trim($shareholder['designation']),
                    $holdingPercentage,
                    $shareholder['vendor_registration_id'],
                    $date,
                    $date
                ];

                $insertRecords = array_merge($insertRecords, $record);
                $questionMarks[] = '('.implode(',', array_fill(0, 9, '?')).')';
            }

            if(!empty($insertRecords))
            {
                try
                {
                    $this->logSection('Shareholders Info Migration', 'Inserting records...');

                    $eprojectConn->beginTransaction();
                    
                    $stmt = $eprojectConn->prepare("INSERT INTO company_personnel
                    (type, name, identification_number, email_address, designation, holding_percentage, vendor_registration_id, created_at, updated_at)
                    VALUES " . implode(',', $questionMarks));

                    $stmt->execute($insertRecords);
                    
                    $eprojectConn->commit();

                    $this->logSection('Shareholders Info Migration', 'Successfully migrated Shareholders Info!');
                }
                catch(Exception $e)
                {
                    $eprojectConn->rollBack();

                    throw $e;
                }

                unset($insertRecords);
            }
        });
    }

    protected function createBuildspaceCompanies(Array $vendorIds)
    {
        $buildspaceConn = $this->getBuildspaceConnection();
        $eprojectConn = $this->getEprojectConnection();

        $stmt = $eprojectConn->prepare("SELECT comp.reference_id, comp.name, comp.reference_no, comp.main_contact, comp.email, comp.telephone_number,
            comp.updated_at, comp.created_at, comp.address, LOWER(c.country) AS country, LOWER(s.name) as state_name
            FROM companies comp
            JOIN countries c ON c.id = comp.country_id
            JOIN states s ON s.id = comp.state_id
            WHERE comp.third_party_app_identifier = 'SDPRP'
            AND comp.third_party_vendor_id IN (".implode(',', $vendorIds).")
        ");

        $stmt->execute();

        $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $buildspaceConn->prepare("SELECT TRIM(LOWER(s.name)), s.id
            FROM bs_subregions s ORDER BY s.name
        ");

        $stmt->execute();

        $states = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $stmt = $buildspaceConn->prepare("SELECT TRIM(LOWER(c.country)), c.id
            FROM bs_regions c
        ");

        $stmt->execute();

        $countries = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        self::arrayBatch($companies, 500, function($batch)use($buildspaceConn, $states, $countries) {
            $insertRecords = [];
            $questionMarks = [];

            foreach($batch as $company)
            {
                $shortname = substr($company['name'], 0, 20);
                $phoneNumber = substr($company['telephone_number'], 0, 20);
                $record = [
                    $company['reference_id'],
                    mb_strtoupper($company['name'], 'UTF-8'),
                    $shortname,
                    mb_strtoupper($company['reference_no'], 'UTF-8'),
                    $company['main_contact'],
                    $company['email'],
                    ($phoneNumber) ? $phoneNumber : '-',
                    '-',
                    ($phoneNumber) ? $phoneNumber : '-',
                    $company['address'],
                    $states[$company['state_name']],
                    $countries[$company['country']],
                    $company['updated_at'],
                    $company['created_at']
                ];

                $insertRecords = array_merge($insertRecords, $record);
                $questionMarks[] = '('.implode(',', array_fill(0, 14, '?')).')';
            }

            if($insertRecords)
            {
                try
                {
                    $this->logSection('Buildspace Companies Migration', 'Inserting records...');

                    $buildspaceConn->beginTransaction();
                    
                    $stmt = $buildspaceConn->prepare("INSERT INTO bs_companies
                    (reference_id, name, shortname, registration_no, contact_person_name, contact_person_email, phone_number, fax_number, contact_person_direct_line, address, sub_region_id, region_id, created_at, updated_at)
                    VALUES " . implode(',', $questionMarks));

                    $stmt->execute($insertRecords);
                    
                    $buildspaceConn->commit();

                    $this->logSection('Buildspace Companies Migration', 'Successfully migrated Buildspace Companies!');
                }
                catch(Exception $e)
                {
                    $buildspaceConn->rollBack();

                    throw $e;
                    return $this->logSection('Buildspace Companies Migration', $e);
                }

                    unset($insertRecords);
            }
        });
    }
    
    protected function getEprojectStates()
    {
        $eprojectConn = $this->getEprojectConnection();

        $stmt = $eprojectConn->prepare("SELECT c.id AS country_id, c.country, s.id AS state_id, s.name
        FROM states s
        JOIN countries c ON s.country_id = c.id
        ORDER BY c.country, s.name");

        $stmt->execute();

        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $states = [];

        foreach($records as $record)
        {
            $countryName = trim(strtolower($record['country']));
            $stateName = trim(strtolower($record['name']));

            if(!array_key_exists($countryName, $states))
            {
                $states[$countryName] = [];
            }

            $states[$countryName][$stateName] = [
                'country_id' => $record['country_id'],
                'state_id' => $record['state_id']
            ]; 
        }

        return $states;
    }

    protected function getEprojectBusinessEntityTypes()
    {
        $eprojectConn = $this->getEprojectConnection();

        $stmt = $eprojectConn->prepare("SELECT LOWER(name) as name, id
        FROM business_entity_types
        ORDER BY name");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    protected function getEprojectContracGroups()
    {
        $eprojectConn = $this->getEprojectConnection();

        $stmt = $eprojectConn->prepare("SELECT LOWER(name), id
        FROM contract_group_categories");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    protected static function generateRawRegistrationIdentifier($string)
    {
        // Accepts only characters from the Latin alphabet and the Hindu-Arabic numeral system.
        $patterns     = array( '/[^a-zA-Z0-9]/' );
        $replacements = array( '' );
        $string       = preg_replace($patterns, $replacements, $string);

        $string = strtolower($string);

        $string = substr($string, 0, 20);

        return $string;
    }

    protected static function arrayBatch($arr, $batchSize, $closure) {
        $batch = [];
        foreach($arr as $i) {
            $batch[] = $i;
            // See if we have the right amount in the batch
            if(count($batch) === $batchSize) {
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