<?php

use Carbon\Carbon;
use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\VendorRegistration\VendorRegistration;
use PCK\VendorCategory\VendorCategory;
use PCK\ModuleParameters\VendorManagement\VendorRegistrationAndPrequalificationModuleParameter;
use PCK\ModuleParameters\VendorManagement\VendorPerformanceEvaluationModuleParameter;
use PCK\ModuleParameters\VendorManagement\VendorManagementGradeLevel;
use PCK\VendorPreQualification\VendorPreQualification;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluation;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationCompanyForm;
use PCK\VendorPerformanceEvaluation\Cycle;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationCompanyFormEvaluationLog;
use PCK\Companies\Company;
use PCK\Base\Helpers;
use PCK\Countries\Country;
use PCK\Countries\CountryRepository;
use PCK\Vendor\Vendor;
use PCK\Subsidiaries\Subsidiary;
use PCK\ContractGroups\ContractGroup;
use PCK\ContractGroups\Types\Role;
use PCK\Helpers\StringOperations;
use PCK\Helpers\PathRegistry;
use PCK\Helpers\Files;
use PCK\Reports\VendorPerformanceEvaluationFormExcelGenerator;
use PCK\Reports\VendorPerformanceEvaluationVendorWorkCategoryScoresExcelGenerator;
use PCK\Reports\VendorPerformanceEvaluationVendorCategoryScoresExcelGenerator;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VendorManagementDashboardController extends Controller
{
    public function vpeStatisticsIndex()
    {
        $externalVendors       = ContractGroupCategory::where('type', ContractGroupCategory::TYPE_EXTERNAL)->where('hidden', false)->orderBy('name', 'asc')->get();
        $vendorCategories      = VendorCategory::where('hidden', false)->orderBy('name', 'asc')->get();
        $countries             = Country::all();
        $latestEvaluationCycle = Cycle::latest();
        $historicalCycleIds    = Cycle::where('is_completed', true)->orderBy('id', 'DESC')->limit(2)->lists('id');

        return View::make('vendor_management.dashboard.vpe_statistics_index', [
            'externalVendors'       => $externalVendors,
            'vendorCategories'      => $vendorCategories,
            'countries'             => $countries,
            'latestEvaluationCycle' => $latestEvaluationCycle,
            'historicalCycleIds'    => $historicalCycleIds,
        ]);
    }

    public function vendorStatisticsIndex()
    {
        $preqGrade = VendorRegistrationAndPrequalificationModuleParameter::first()->vendorManagementGrade;
        $vpeGrade  = VendorPerformanceEvaluationModuleParameter::first()->vendorManagementGrade;

        if(is_null($preqGrade))
        {
            Flash::warning(trans('vendorManagement.requiredGradingNotSet', ['module' => trans('vendorManagement.vendorPreQualificationShort')]));

            return Redirect::route('home.index');
        }

        if(is_null($vpeGrade))
        {
            Flash::warning(trans('vendorManagement.requiredGradingNotSet', ['module' => trans('vendorManagement.vendorPerformanceEvaluationShort')]));

            return Redirect::route('home.index');
        }

        $externalVendors         = ContractGroupCategory::where('type', ContractGroupCategory::TYPE_EXTERNAL)->where('hidden', false)->orderBy('name', 'asc')->get();
        $registrationStatuses    = Company::getStatusDescriptions();
        $companyStatuses         = Company::getCompanyStatusDescriptions();
        $latestCompletedVpeCycle = Cycle::latestCompleted();
        $preqGradeLevels         = [];
        $vpeGradeLevels          = [];

        unset($registrationStatuses[Company::STATUS_DRAFT]);

        $countriesQuery = "SELECT DISTINCT ctry.id, ctry.country
                           FROM companies c 
                           INNER JOIN contract_group_categories cgc ON cgc.id = c.contract_group_category_id 
                           INNER JOIN countries ctry ON ctry.id = c.country_id 
                           WHERE cgc.type = " . ContractGroupCategory::TYPE_EXTERNAL . "
                           AND cgc.hidden IS FALSE
                           AND c.confirmed IS TRUE
                           AND c.deactivated_at IS NULL 
                           AND c.activation_date IS NOT NULL 
                           ORDER BY ctry.country ASC;";

        $countries = DB::select(DB::raw($countriesQuery));

        $preqGrade = VendorRegistrationAndPrequalificationModuleParameter::first()->vendorManagementGrade;

        if($preqGrade)
        {
            $preqGradeLevels = $preqGrade->levels()->orderBy('score_upper_limit', 'DESC')->lists('description', 'id');
        }

        $vpeGrade = VendorPerformanceEvaluationModuleParameter::first()->vendorManagementGrade;

        if($vpeGrade)
        {
            $vpeGradeLevels = $vpeGrade->levels()->orderBy('score_upper_limit', 'DESC')->lists('description', 'id');
        }

        return View::make('vendor_management.dashboard.vendor_statistics_index', [
            'externalVendors'         => $externalVendors,
            'countries'               => $countries,
            'registrationStatuses'    => $registrationStatuses,
            'companyStatuses'         => $companyStatuses,
            'preqGradeLevels'         => $preqGradeLevels,
            'vpeGradeLevels'          => $vpeGradeLevels,
            'latestCompletedVpeCycle' => $latestCompletedVpeCycle,
        ]);
    }

    public function evaluatedProjectsTotal()
    {
        $latestEvaluationCycle = Cycle::latest();

        $query = "SELECT DISTINCT vpe.project_id AS project_id
                  FROM vendor_performance_evaluations vpe
                  INNER JOIN projects p ON p.id = vpe.project_id 
                  WHERE vpe.vendor_performance_evaluation_cycle_id = {$latestEvaluationCycle->id}
                  AND vpe.deleted_at IS NULL
                  AND p.deleted_at IS NULL
                  ORDER BY vpe.project_id ASC;";

        $projectIds = array_column(DB::select(DB::raw($query)),  'project_id');

        return array('total' => count($projectIds));
    }

    public function overallVendorPerformanceStatisticsTable()
    {
        $externalVendorGroups  = ContractGroupCategory::where('type', ContractGroupCategory::TYPE_EXTERNAL)->where('hidden', false)->orderBy('id', 'ASC')->lists('name', 'id');
        $latestEvaluationCycle = Cycle::latest();
        
        if(is_null($latestEvaluationCycle))
        {
            return array(
                'data'         => [],
                'vendorGroups' => $externalVendorGroups,
            );
        }

        // gets all subsidiaries involved in vpe project for the latest vpe cycle
        $subsidiaryQuery = "SELECT DISTINCT s.id AS subsidiary_id
                            FROM vendor_performance_evaluation_company_forms forms
                            INNER JOIN vendor_performance_evaluations vpe ON vpe.id = forms.vendor_performance_evaluation_id
                            INNER JOIN projects p ON p.id = vpe.project_id
                            INNER JOIN subsidiaries s ON s.id = p.subsidiary_id 
                            WHERE vpe.vendor_performance_evaluation_cycle_id = {$latestEvaluationCycle->id}
                            AND forms.deleted_at IS NULL
                            AND vpe.deleted_at IS NULL
                            AND p.deleted_at IS NULL
                            ORDER BY s.id ASC;";

        $queryResults = DB::select(DB::raw($subsidiaryQuery));

        $subsidiaryIds = array_column($queryResults, 'subsidiary_id');

        // gets the top parent of subsidiaries
        $topParentsBySubsidiaryIds = Subsidiary::getTopParentsGroupedBySubsidiaryIds($subsidiaryIds);

        // gets the number of projects grouped by subsidiaries for the latest vpe cycle
        $assignedProjectsQuery = "SELECT s.id AS subsidiary_id, COUNT(vpe.project_id) AS project_count
                                  FROM vendor_performance_evaluation_company_forms forms
                                  INNER JOIN vendor_performance_evaluations vpe ON vpe.id = forms.vendor_performance_evaluation_id
                                  INNER JOIN projects p ON p.id = vpe.project_id 
                                  INNER JOIN subsidiaries s ON s.id = p.subsidiary_id 
                                  WHERE s.id IN (" . implode(',', $subsidiaryIds) . ")
                                  AND vpe.vendor_performance_evaluation_cycle_id = {$latestEvaluationCycle->id}
                                  AND forms.deleted_at IS NULL
                                  AND vpe.deleted_at IS NULL
                                  AND p.deleted_at IS NULL
                                  GROUP BY s.id
                                  ORDER BY s.id ASC;";

        $assignedProjectsQueryResults = DB::select(DB::raw($assignedProjectsQuery));

        $assignedProjects = [];

        foreach($assignedProjectsQueryResults as $result)
        {
            $assignedProjects[$result->subsidiary_id] = $result->project_count;
        }

        //get the number of assigned vendor_appraisals grouped by subsidiaries and vendor groups
        $vendorAppraisalsQuery = "SELECT s.id AS subsidiary_id, cgc.id AS vendor_group_id, COUNT(c.id) AS assigned_vendor_count
                                  FROM vendor_performance_evaluation_company_forms forms
                                  INNER JOIN vendor_performance_evaluations vpe ON vpe.id = forms.vendor_performance_evaluation_id 
                                  INNER JOIN projects p ON p.id = vpe.project_id 
                                  INNER JOIN subsidiaries s ON s.id = p.subsidiary_id
                                  INNER JOIN companies c ON c.id = forms.company_id 
                                  INNER JOIN contract_group_categories cgc ON cgc.id = c.contract_group_category_id 
                                  WHERE s.id IN (" . implode(',', $subsidiaryIds) . ")
                                  AND vpe.vendor_performance_evaluation_cycle_id = {$latestEvaluationCycle->id}
                                  AND forms.deleted_at IS NULL
                                  AND vpe.deleted_at IS NULL
                                  AND p.deleted_at IS NULL
                                  AND cgc.hidden IS FALSE
                                  AND cgc.type = " . ContractGroupCategory::TYPE_EXTERNAL . "
                                  AND c.confirmed IS TRUE
                                  GROUP BY s.id, cgc.id
                                  ORDER BY s.id ASC, cgc.id ASC;";

        $vendorAppraisalsQueryResults = DB::select(DB::raw($vendorAppraisalsQuery));

        $vendorAppraisals = [];

        foreach($vendorAppraisalsQueryResults as $result)
        {
            $vendorAppraisals[$result->subsidiary_id][$result->vendor_group_id] = $result->assigned_vendor_count;
        }

        // gets the number of completed evaluations grouped by subsidiaries for the latest vpe cycle
        $completedEvaluationsQuery = "SELECT s.id AS subsidiary_id, COUNT(vpe.project_id) AS project_count
                                      FROM vendor_performance_evaluation_company_forms forms
                                      INNER JOIN vendor_performance_evaluations vpe ON vpe.id = forms.vendor_performance_evaluation_id 
                                      INNER JOIN projects p ON p.id = vpe.project_id 
                                      INNER JOIN subsidiaries s ON s.id = p.subsidiary_id 
                                      WHERE s.id IN (" . implode(',', $subsidiaryIds) . ")
                                      AND vpe.vendor_performance_evaluation_cycle_id = {$latestEvaluationCycle->id}
                                      AND forms.status_id = " . VendorPerformanceEvaluationCompanyForm::STATUS_COMPLETED . "
                                      AND forms.deleted_at IS NULL
                                      AND vpe.deleted_at IS NULL
                                      AND p.deleted_at IS NULL
                                      GROUP by s.id 
                                      ORDER BY s.id ASC;";

        $completedEvaluationsQueryResults = DB::select(DB::raw($completedEvaluationsQuery));

        $completedEvaluations = [];

        foreach($completedEvaluationsQueryResults as $result)
        {
            $completedEvaluations[$result->subsidiary_id] = $result->project_count;
        }

        $rowData = [];

        $rootSubsidiaryIds = array_unique(array_column($topParentsBySubsidiaryIds, 'id'));

        // prepare data structure
        foreach($rootSubsidiaryIds as $subId)
        {
            $rowData[$subId]['subsidiary'] = null;
            $rowData[$subId]['projectsAssigned'] = 0;

            foreach($externalVendorGroups as $vendorGroupId => $vendorGroup)
            {
                $rowData[$subId]["{$vendorGroupId}_assignedEvaluations"] = 0;
            }

            $rowData[$subId]['totalEvaluations'] = 0;
            $rowData[$subId]['completedEvaluations'] = 0;
            $rowData[$subId]['completionPercentage'] = 0;
            $rowData[$subId]['pendingEvaluations'] = 0;
        }

        // populate data structure
        foreach($topParentsBySubsidiaryIds as $subsidiaryId => $topParentSubsidiary)
        {
            // name
            $rowData[$topParentSubsidiary['id']]['subsidiary'] = $topParentSubsidiary['name'];

            // assigned projects
            $assignedProjectsCount = array_key_exists($subsidiaryId, $assignedProjects) ? $assignedProjects[$subsidiaryId] : 0;

            $rowData[$topParentSubsidiary['id']]['projectsAssigned'] += $assignedProjectsCount;

            // vendor appraisals by vendor groups
            if(array_key_exists($subsidiaryId, $vendorAppraisals))
            {
                foreach($vendorAppraisals[$subsidiaryId] as $vendorGroupId => $vendorCount)
                {
                    $rowData[$topParentSubsidiary['id']]["{$vendorGroupId}_assignedEvaluations"] += $vendorCount;

                    $rowData[$topParentSubsidiary['id']]['totalEvaluations'] += $vendorCount;
                }
            }

            // completed evaluations
            $completedEvaluationsCount = array_key_exists($subsidiaryId, $completedEvaluations) ? $completedEvaluations[$subsidiaryId] : 0;

            $rowData[$topParentSubsidiary['id']]['completedEvaluations'] += $completedEvaluationsCount;
        }

        // populate completion percentage and pending evaluations count
        foreach($rowData as $rootSubsidiaryId => $row)
        {
            $rowData[$rootSubsidiaryId]['completionPercentage'] = round(Helpers::divide($row['completedEvaluations'], $row['totalEvaluations']) * 100);
            $rowData[$rootSubsidiaryId]['pendingEvaluations'] = $row['totalEvaluations'] - $row['completedEvaluations'];
        }

        return array(
            'data'         => array_values($rowData),
            'vendorGroups' => $externalVendorGroups,
        );
    }

    public function overallVendorPerformanceStatisticsExcelExport()
    {
        $latestEvaluationCycle = Cycle::latest();

        $contractGroupId = ContractGroup::getIdByGroup(Role::PROJECT_OWNER);

        $query = "WITH RECURSIVE subsidiary_relations_cte AS (
                      SELECT id, name, identifier, company_id, parent_id, array[id]::INTEGER[] AS path_array 
                      FROM subsidiaries 
                      WHERE id IN (SELECT DISTINCT subsidiary_id FROM vpe_statistics_cte)
                      UNION ALL
                      SELECT s.id, s.name, s.identifier, s.company_id, s.parent_id, ARRAY_APPEND(sr.path_array, s.id::INTEGER) AS path_array
                      FROM subsidiaries s 
                      INNER JOIN subsidiary_relations_cte sr ON sr.parent_id = s.id
                  ),
                  vpe_statistics_cte AS (
                      SELECT s.id subsidiary_id, buco.id AS business_unit_id, buco.name AS business_unit, p.id AS project_id, p.title AS project_title, c.id AS vendor_id, c.name AS vendor, forms.id AS form_id, forms.status_id AS form_status,
                      CASE
                          WHEN (forms.status_id = " . VendorPerformanceEvaluationCompanyForm::STATUS_COMPLETED . ")
                          THEN forms.score 
                          ELSE NULL
                      END AS score,
                      cgc.id AS vendor_group_id, cgc.name AS vendor_group, 
	                  ARRAY_TO_JSON(ARRAY(SELECT DISTINCT * FROM UNNEST(ARRAY_AGG(vc.id ORDER BY vc.id ASC)) AS res ORDER BY res ASC)) AS vendor_category_ids, 
	                  ARRAY_TO_JSON(ARRAY(SELECT DISTINCT * FROM UNNEST(ARRAY_AGG(vc.name ORDER BY vc.id ASC)) AS res ORDER BY res ASC)) AS vendor_categories, 
	                  vwc.id AS vendor_work_category_id, vwc.name AS vendor_work_category,
	                  ARRAY(SELECT DISTINCT * FROM UNNEST(ARRAY_AGG(evl.user_id)) AS res ORDER BY res ASC) AS evaluator_ids, ARRAY(SELECT DISTINCT * FROM UNNEST(ARRAY_AGG(evaluators.name)) AS res ORDER BY res ASC) AS evaluators
                      FROM vendor_performance_evaluation_company_forms forms
                      INNER JOIN vendor_performance_evaluations vpe ON vpe.id = forms.vendor_performance_evaluation_id 
	                  INNER JOIN projects p ON p.id = vpe.project_id 
	                  INNER JOIN subsidiaries s ON s.id = p.subsidiary_id 
	                  INNER JOIN companies buco ON buco.id = p.business_unit_id
	                  INNER JOIN companies c ON c.id = forms.company_id 
  	                  INNER JOIN contract_group_categories cgc ON cgc.id = c.contract_group_category_id 
	                  INNER JOIN vendor_work_categories vwc ON vwc.id = forms.vendor_work_category_id 
	                  INNER JOIN vendor_category_vendor_work_category vcvwc ON vcvwc.vendor_work_category_id = vwc.id
	                  INNER JOIN vendor_categories vc ON vc.id = vcvwc.vendor_category_id
                      LEFT OUTER JOIN vendor_performance_evaluators evl ON evl.vendor_performance_evaluation_id = vpe.id
                      LEFT OUTER JOIN users evaluators ON evaluators.id = evl.user_id 
                      WHERE vpe.vendor_performance_evaluation_cycle_id = {$latestEvaluationCycle->id}
                      AND forms.deleted_at IS NULL
                      AND vpe.deleted_at IS NULL
                      AND p.deleted_at IS NULL
                      AND c.confirmed IS TRUE
                      AND cgc.hidden IS FALSE
                      AND cgc.type = " . ContractGroupCategory::TYPE_EXTERNAL . "
                      AND vc.hidden IS FALSE
                      AND vwc.hidden IS FALSE
                      GROUP BY forms.id, s.id, buco.id, p.id, c.id, forms.status_id, forms.score, cgc.id, vwc.id
                      ORDER by s.id ASC, buco.id ASC, p.id ASC, c.id ASC, vwc.id ASC
                  ),
                  project_editors_cte AS (
                      SELECT ROW_NUMBER() OVER (PARTITION BY pu.project_id ORDER BY pu.created_at ASC) AS rank, pu.project_id, pu.user_id AS bu_editor_id, u.name AS bu_editor
                      FROM contract_group_project_users pu
                      INNER JOIN users u ON u.id = pu.user_id 
                      WHERE pu.project_id IN (
                          SELECT project_id 
                          FROM vpe_statistics_cte
                          WHERE form_status = " . VendorPerformanceEvaluationCompanyForm::STATUS_DRAFT . "
                      )
                      AND pu.is_contract_group_project_owner IS TRUE
                      AND pu.contract_group_id = {$contractGroupId}
                      ORDER BY pu.project_id DESC
                  ),
                  company_form_evaluation_logs_cte AS (
                      SELECT ROW_NUMBER() OVER (PARTITION BY el.vendor_performance_evaluation_company_form_id ORDER BY el.id DESC) AS rank, el.vendor_performance_evaluation_company_form_id, el.created_by AS last_submitter_id, u.name AS last_submitter, el.id
                      FROM vendor_performance_evaluation_company_form_evaluation_logs el
                      INNER JOIN users u ON u.id = el.created_by
                      WHERE vendor_performance_evaluation_company_form_id IN (
                          SELECT form_id
                          FROM vpe_statistics_cte
                          WHERE form_status = " . VendorPerformanceEvaluationCompanyForm::STATUS_SUBMITTED . "
                      )
                      AND el.action_type = " . VendorPerformanceEvaluationCompanyFormEvaluationLog::SUBMITTED . "
                      ORDER BY el.vendor_performance_evaluation_company_form_id ASC
                  ),
                  pending_verification_evaluation_forms_cte AS (
                      SELECT ROW_NUMBER() OVER (PARTITION BY object_id ORDER BY sequence_number ASC) AS rank, v.object_id AS form_id, v.verifier_id AS verifier_id, u.name AS verifier
                      FROM verifiers v
                      INNER JOIN users u ON u.id = v.verifier_id
                      WHERE v.object_type = '" . VendorPerformanceEvaluationCompanyForm::class . "'
                      AND v.object_id IN(
                          SELECT form_id
                          FROM vpe_statistics_cte
                          WHERE form_status = " . VendorPerformanceEvaluationCompanyForm::STATUS_PENDING_VERIFICATION . "
                      )
                      AND v.deleted_at IS NULL
                      AND v.approved IS NULL
                      ORDER BY v.object_id ASC
                  ),
                  completed_verification_evaluation_forms_cte AS (
                      SELECT ROW_NUMBER() OVER (PARTITION BY object_id ORDER BY sequence_number DESC) AS rank, v.object_id AS form_id, v.verifier_id AS verifier_id, u.name AS verifier
                      FROM verifiers v
                      INNER JOIN users u ON u.id = v.verifier_id
                      WHERE v.object_type = '" . VendorPerformanceEvaluationCompanyForm::class . "'
                      AND v.object_id IN(
                          SELECT form_id
                          FROM vpe_statistics_cte
                          WHERE form_status = " . VendorPerformanceEvaluationCompanyForm::STATUS_COMPLETED . "
                      )
                      AND v.deleted_at IS NULL
                      AND v.approved IS NOT NULL
                      ORDER BY v.object_id ASC
                  ),
                  final_results_cte AS (
                      SELECT sr.path_array[1] AS top_subsidiary_id, sr.name AS top_subsidiary, vpe_stat.*, pe.bu_editor_id, pe.bu_editor, cfel.last_submitter_id, cfel.last_submitter, 
                      pvef.verifier_id AS pending_verifier_id, pvef.verifier AS pending_verifier, cvef.verifier_id AS completed_verifier_id, cvef.verifier AS completed_verifier
                      FROM subsidiary_relations_cte sr
                      INNER JOIN vpe_statistics_cte vpe_stat ON vpe_stat.subsidiary_id = sr.path_array[1]
                      LEFT OUTER JOIN project_editors_cte pe ON pe.project_id = vpe_stat.project_id AND pe.rank = 1
                      LEFT OUTER JOIN company_form_evaluation_logs_cte cfel ON cfel.vendor_performance_evaluation_company_form_id = vpe_stat.form_id AND cfel.rank = 1
                      LEFT OUTER JOIN pending_verification_evaluation_forms_cte pvef ON pvef.form_id = vpe_stat.form_id AND pvef.rank = 1
                      LEFT OUTER JOIN completed_verification_evaluation_forms_cte cvef ON cvef.form_id = vpe_stat.form_id AND cvef.rank = 1
                      WHERE sr.parent_id IS NULL
                      ORDER BY sr.id ASC, vpe_stat.business_unit_id ASC, vpe_stat.project_id ASC, vpe_stat.vendor_id ASC, vpe_stat.vendor_work_category_id ASC
                  )
                  SELECT frcte.top_subsidiary, frcte.business_unit, frcte.project_title, frcte.vendor, frcte.score::TEXT, frcte.vendor_group, frcte.vendor_categories, frcte.vendor_work_category, frcte.form_status,
                    CASE
                        WHEN(frcte.form_status = " . VendorPerformanceEvaluationCompanyForm::STATUS_DRAFT. ") THEN 
                            CASE
                                WHEN((ARRAY_LENGTH(frcte.evaluators, 1) > 0) AND (frcte.evaluators[1] IS NOT NULL)) THEN frcte.evaluators[1]::TEXT --first evaluator
                                ELSE frcte.bu_editor::TEXT
                            END
                        WHEN(frcte.form_status = " . VendorPerformanceEvaluationCompanyForm::STATUS_SUBMITTED. ") THEN
                            CASE
                                WHEN (frcte.last_submitter IS NOT NULL) THEN frcte.last_submitter::TEXT
                                ELSE frcte.evaluators[1]::TEXT
                            END
                        WHEN(frcte.form_status = " . VendorPerformanceEvaluationCompanyForm::STATUS_PENDING_VERIFICATION. ") THEN frcte.pending_verifier::TEXT
                        WHEN(frcte.form_status = " . VendorPerformanceEvaluationCompanyForm::STATUS_COMPLETED. ") THEN frcte.completed_verifier::TEXT
                    END AS appraiser
                  FROM final_results_cte frcte;";

        $queryResults = DB::select(DB::raw($query));

        $rowData = [];

        foreach($queryResults as $key => $result)
        {
            $row['number']               = $key + 1;
            $row['top_subsidiary']       = $result->top_subsidiary;
            $row['business_unit']        = $result->business_unit;
            $row['project_title']        = $result->project_title;
            $row['vendor']               = $result->vendor;
            $row['score']                = $result->score;
            $row['vendor_group']         = $result->vendor_group;
            $row['vendor_categories']    = implode(', ', json_decode($result->vendor_categories));
            $row['vendor_work_category'] = $result->vendor_work_category;
            $row['form_status']          = VendorPerformanceEvaluationCompanyForm::getStatusText($result->form_status);
            $row['appraiser']            = $result->appraiser;

            $rowData[] = $row;
        }

        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'ffffff']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '187bcd']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER
            ]
        ];

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()->setCreator("Buildspace");

        $activeSheet = $spreadsheet->getActiveSheet();
        $activeSheet->setAutoFilter('A1:K1');

        $headers = [
            trans('general.no'),
            trans('subsidiaries.subsidiary'),
            trans('projects.businessUnit'),
            trans('projects.projectTitle'),
            trans('vendorManagement.vendor'),
            trans('general.score'),
            trans('vendorManagement.vendorGroup'),
            trans('vendorManagement.vendorCategories'),
            trans('vendorManagement.vendorWorkCategory'),
            trans('general.status'),
            trans('vendorPerformanceEvaluation.appraiser'),
        ];

        $headerCount = 1;
        foreach($headers as $key => $val)
        {
            $cell = StringOperations::numberToAlphabet($headerCount)."1";
            $activeSheet->setCellValue($cell, $val);
            $activeSheet->getStyle($cell)->applyFromArray($headerStyle);

            $activeSheet->getColumnDimension(StringOperations::numberToAlphabet($headerCount))->setAutoSize(true);
            
            $headerCount++;
        }

        $activeSheet->fromArray($rowData, null, 'A2');

        $writer = new Xlsx($spreadsheet);

        $filepath = \PCK\Helpers\Files::getTmpFileUri();

        $writer->save($filepath);

        $filename = 'Overall-Vendor-Performance-Statistics_'.date("dmYHis");

        return \PCK\Helpers\Files::download($filepath, "{$filename}.".\PCK\Helpers\Files::EXTENSION_EXCEL);
    }

    public function vendorGroupTotalEvaluationsByRating()
    {
        $latestComplatedVpeCycle = Cycle::getLatestCompletedCycle();

        $vendorGroups = ContractGroupCategory::where('type', ContractGroupCategory::TYPE_EXTERNAL)->where('hidden', '=', false)->orderBy('id', 'ASC')->lists('name', 'id');

        if(!$latestComplatedVpeCycle->vendorManagementGrade || !$latestComplatedVpeCycle || empty($vendorGroups)) {
            return array(
                'ratings' => [],
                'data'    => [],
            );
        }

        $vendorManagementGradeLevels = [];

        foreach($latestComplatedVpeCycle->vendorManagementGrade->levels()->orderBy('score_upper_limit', 'DESC')->get() as $level)
        {
            $vendorManagementGradeLevels[$level->id] = [
                'description'       => $level->description,
                'score_upper_limit' => $level->score_upper_limit,
            ];
        }

        $query = "WITH global_vpe_grade_levels AS (
                      SELECT ROW_NUMBER() OVER (PARTITION BY vendor_management_grade_id ORDER BY score_upper_limit ASC) AS rank, id, description, score_upper_limit 
                      FROM vendor_management_grade_levels
                      WHERE vendor_management_grade_id = {$latestComplatedVpeCycle->vendorManagementGrade->id}
                      ORDER BY score_upper_limit ASC
                  ),
                  vendor_average_scores AS (
                      SELECT cgc.id AS vendor_group_id, cgc.name AS vendor_group, c.id AS company_id, c.name AS company, ROUND(AVG(s.score)) AS average_score
                      FROM vendors v
                      INNER JOIN companies c ON c.id = v.company_id 
                      INNER JOIN contract_group_categories cgc ON cgc.id = c.contract_group_category_id 
                      INNER JOIN vendor_work_categories vwc ON vwc.id = v.vendor_work_category_id 
                      INNER JOIN vendor_evaluation_cycle_scores s ON s.id = v.vendor_evaluation_cycle_score_id AND s.vendor_work_category_id = v.vendor_work_category_id 
                      INNER JOIN vendor_performance_evaluations vpe ON vpe.vendor_performance_evaluation_cycle_id = s.vendor_performance_evaluation_cycle_id 
	                  INNER JOIN vendor_performance_evaluation_company_forms forms ON forms.vendor_performance_evaluation_id = vpe.id AND forms.vendor_work_category_id = vwc.id AND forms.company_id = c.id
                      WHERE s.vendor_performance_evaluation_cycle_id = {$latestComplatedVpeCycle->id}
                      AND c.contract_group_category_id IN (" . implode(', ', array_keys($vendorGroups)) . ") 
                      AND vpe.deleted_at IS NULL
                      AND vpe.status_id = " . VendorPerformanceEvaluation::STATUS_COMPLETED . "
                      AND forms.deleted_at IS NULL
                      AND forms.status_id = " . VendorPerformanceEvaluationCompanyForm::STATUS_COMPLETED . "
                      GROUP BY cgc.id, c.id
                      ORDER BY cgc.id ASC, c.id ASC
                  ),
                  graded_vendor_average_scores AS (
                      SELECT ROW_NUMBER() OVER (PARTITION BY vas.vendor_group_id, vas.company_id ORDER BY vas.vendor_group_id ASC, vas.vendor_group, vas.company_id ASC) AS rank, vas.vendor_group_id, vas.vendor_group, vas.company_id, vas.company, vas.average_score, gl.rank grade_level_rank, gl.id AS grade_level_id, gl.score_upper_limit, gl.description
                      FROM vendor_average_scores vas
                      INNER JOIN global_vpe_grade_levels gl ON gl.score_upper_limit >= vas.average_score
                  )
                  SELECT gvas.vendor_group_id, gvas.vendor_group, gvas.grade_level_id, gvas.description AS grade_description, count(gvas.company_id) AS vendor_count
                  FROM graded_vendor_average_scores gvas
                  WHERE gvas.rank = 1
                  GROUP BY gvas.vendor_group_id, gvas.vendor_group, gvas.grade_level_id, gvas.grade_level_rank, gvas.description
                  ORDER BY gvas.vendor_group_id ASC, gvas.grade_level_rank DESC;";

        $queryResults = [];

        foreach(DB::select(DB::raw($query)) as $result)
        {
            $queryResults[$result->vendor_group_id]['id']          = $result->vendor_group_id;
            $queryResults[$result->vendor_group_id]['vendorGroup'] = $result->vendor_group;

            foreach($vendorManagementGradeLevels as $levelId => $level)
            {
                if(($result->grade_level_id == $levelId))
                {
                    $queryResults[$result->vendor_group_id]["{$levelId}_count"] = $result->vendor_count;
                }
            }
        }

        $data = [];

        foreach($queryResults as $vendorGroupId => $result)
        {
            $data[$vendorGroupId]['id']          = $result['id'];
            $data[$vendorGroupId]['vendorGroup'] = $result['vendorGroup'];

            foreach($vendorManagementGradeLevels as $levelId => $level)
            {
                $data[$vendorGroupId]["{$levelId}_count"] = array_key_exists("{$levelId}_count", $result) ? $result["{$levelId}_count"] : 0;
            }
        }

        $ratings = [];

        foreach($latestComplatedVpeCycle->vendorManagementGrade->levels()->orderBy('score_upper_limit', 'DESC')->get() as $key => $rating)
        {
            $ratings[$key] = array(
                'id'          => $rating->id,
                'description' => $rating->description,
            );
        }

        return array(
            'data'    => array_values($data),
            'ratings' => $ratings,
        );
    }

    public function vendorCategoryTotalEvaluationsByRating()
    {
        $latestComplatedVpeCycle = Cycle::getLatestCompletedCycle();

        $vendorGroups = ContractGroupCategory::where('type', ContractGroupCategory::TYPE_EXTERNAL)->where('hidden', '=', false)->orderBy('id', 'ASC')->lists('name', 'id');

        if(!$latestComplatedVpeCycle->vendorManagementGrade || !$latestComplatedVpeCycle || empty($vendorGroups))
        {
            return array(
                'ratings' => [],
                'data'    => [],
            );
        }

        $vendorManagementGradeLevels = [];

        foreach($latestComplatedVpeCycle->vendorManagementGrade->levels()->orderBy('score_upper_limit', 'DESC')->get() as $level)
        {
            $vendorManagementGradeLevels[$level->id] = [
                'description'       => $level->description,
                'score_upper_limit' => $level->score_upper_limit,
            ];
        }

        $vendorGroupIds = implode(', ', array_keys($vendorGroups));

        $query = "WITH global_vpe_grade_levels AS (
                      SELECT ROW_NUMBER() OVER (PARTITION BY vendor_management_grade_id ORDER BY score_upper_limit ASC) AS rank, id, description, score_upper_limit 
                      FROM vendor_management_grade_levels
                      WHERE vendor_management_grade_id = {$latestComplatedVpeCycle->vendorManagementGrade->id}
                      ORDER BY score_upper_limit ASC
                  ),
                  vendor_average_scores AS (
                      SELECT vc.id AS vendor_category_id, vc.name AS vendor_category, c.id AS company_id, c.name AS company, ROUND(AVG(s.score)) AS average_score
                      FROM vendors v
                      INNER JOIN companies c ON c.id = v.company_id
                      INNER JOIN vendor_work_categories vwc ON vwc.id = v.vendor_work_category_id
                      INNER JOIN company_vendor_category cvc ON cvc.company_id = c.id
                      INNER JOIN vendor_categories vc ON vc.id = cvc.vendor_category_id 
                      INNER JOIN contract_group_categories cgc ON cgc.id = c.contract_group_category_id 
                      INNER JOIN vendor_evaluation_cycle_scores s ON s.id = v.vendor_evaluation_cycle_score_id AND s.vendor_work_category_id = v.vendor_work_category_id
                      INNER JOIN vendor_performance_evaluations vpe ON vpe.vendor_performance_evaluation_cycle_id = s.vendor_performance_evaluation_cycle_id 
                      INNER JOIN vendor_performance_evaluation_company_forms forms ON forms.vendor_performance_evaluation_id = vpe.id AND forms.vendor_work_category_id = vwc.id AND forms.company_id = c.id
                      WHERE s.vendor_performance_evaluation_cycle_id = {$latestComplatedVpeCycle->id}
                      AND cgc.id IN ({$vendorGroupIds})
                      AND vpe.deleted_at IS NULL
                      AND vpe.status_id = " . VendorPerformanceEvaluation::STATUS_COMPLETED . "
                      AND forms.deleted_at IS NULL
                      AND forms.status_id = " . VendorPerformanceEvaluationCompanyForm::STATUS_COMPLETED . "
                      GROUP BY vc.id, c.id
                      ORDER BY vc.id ASC, c.id ASC
                  ),
                  graded_vendor_average_scores AS (
                      SELECT ROW_NUMBER() OVER (PARTITION BY vas.vendor_category_id, vas.company_id ORDER BY vas.vendor_category_id ASC, vas.vendor_category, vas.company_id ASC) AS rank, vas.vendor_category_id, vas.vendor_category, vas.company_id, vas.company, vas.average_score, gl.rank grade_level_rank, gl.id AS grade_level_id, gl.score_upper_limit, gl.description
                      FROM vendor_average_scores vas
                      INNER JOIN global_vpe_grade_levels gl ON gl.score_upper_limit >= vas.average_score
                  )
                  SELECT gvas.vendor_category_id, gvas.vendor_category, gvas.grade_level_rank, gvas.grade_level_id, gvas.description, COUNT(gvas.company_id) AS vendor_count
                  FROM graded_vendor_average_scores gvas
                  WHERE gvas.rank = 1
                  GROUP BY gvas.vendor_category_id, gvas.vendor_category, gvas.grade_level_rank, gvas.grade_level_id, gvas.description
                  ORDER BY gvas.vendor_category_id ASC, gvas.grade_level_rank DESC;";

        $queryResults = [];

        foreach(DB::select(DB::raw($query)) as $result)
        {
            $queryResults[$result->vendor_category_id]['id']             = $result->vendor_category_id;
            $queryResults[$result->vendor_category_id]['vendorCategory'] = $result->vendor_category;

            foreach($vendorManagementGradeLevels as $levelId => $level)
            {
                if(($result->grade_level_id == $levelId))
                {
                    $queryResults[$result->vendor_category_id]["{$levelId}_count"] = $result->vendor_count;
                }
            }
        }

        $data = [];

        foreach($queryResults as $venddorCategoryId => $result)
        {
            $data[$venddorCategoryId]['id']             = $result['id'];
            $data[$venddorCategoryId]['vendorCategory'] = $result['vendorCategory'];

            foreach($vendorManagementGradeLevels as $levelId => $level)
            {
                $data[$venddorCategoryId]["{$levelId}_count"] = array_key_exists("{$levelId}_count", $result) ? $result["{$levelId}_count"] : 0;
            }
        }

        $ratings = [];

        foreach($latestComplatedVpeCycle->vendorManagementGrade->levels()->orderBy('score_upper_limit', 'DESC')->get() as $key => $rating)
        {
            $ratings[$key] = array(
                'id'          => $rating->id,
                'description' => $rating->description,
            );
        }

        return array(
            'data'    => array_values($data),
            'ratings' => $ratings,
        );
    }

    public function topEvaluationScorers()
    {
        $vendorCategoryId = Input::get('vendor_category_id');

        $vendorCategory = VendorCategory::find($vendorCategoryId);

        $historicalCycles = Cycle::where('is_completed', true)->orderBy('id', 'DESC')->limit(2)->get();

        if(! $vendorCategory || $vendorCategory->vendorWorkCategories->isEmpty() || ! ($historicalCycles->count() > 0))
        {
            return Response::json([]);
        }

        $historicalCycleIds = implode(', ', $historicalCycles->lists('id'));

        // top 20 highest cycle scores for each vpe cycle
        $query = "WITH historical_cycle_scores AS (
                      SELECT RANK() OVER (PARTITION BY vecs.vendor_performance_evaluation_cycle_id ORDER BY vecs.score DESC), *
                      FROM vendor_evaluation_cycle_scores vecs 
                      WHERE vecs.vendor_performance_evaluation_cycle_id IN ({$historicalCycleIds})
                      AND vecs.score > 0
                      ORDER BY vecs.vendor_performance_evaluation_cycle_id DESC
                  )
                  SELECT hcs.rank, hcs.id, hcs.vendor_performance_evaluation_cycle_id, c.id AS company_id, c.name AS company, vwc.id AS vendor_work_category_id, vwc.name AS vendor_work_category, ROUND(hcs.score) AS score
                  FROM historical_cycle_scores hcs
                  INNER JOIN companies c ON c.id = hcs.company_id 
                  INNER JOIN vendor_work_categories vwc ON vwc.id = hcs.vendor_work_category_id 
                  INNER JOIN vendor_category_vendor_work_category vcvwc ON vcvwc.vendor_work_category_id = vwc.id
                  INNER JOIN vendor_categories vc ON vc.id = vcvwc.vendor_category_id 
                  WHERE vc.hidden IS FALSE
                  AND vwc.hidden IS FALSE
                  AND c.confirmed IS TRUE
                  AND hcs.rank <= 20
                  AND vc.id = {$vendorCategory->id}
                  GROUP BY hcs.rank, hcs.id, hcs.vendor_performance_evaluation_cycle_id, c.id, vwc.id, ROUND(hcs.score)
                  ORDER BY hcs.vendor_performance_evaluation_cycle_id DESC, hcs.rank ASC;";

        $queryResults = DB::select(DB::raw($query));

        $groupedResults = [];

        foreach($queryResults as $result)
        {
            $groupedResults[$result->company_id][$result->vendor_work_category_id] = [
                'company'   => $result->company,
                'vendor_work_category' => $result->vendor_work_category,
            ];
        }

        // group cycle scores
        foreach($queryResults as $result)
        {
            foreach($historicalCycles->lists('id') as $cycleId)
            {
                $groupedResults[$result->company_id][$result->vendor_work_category_id]["{$result->vendor_performance_evaluation_cycle_id}_score"] = $result->score;
            }
        }

        $data = [];

        foreach($groupedResults as $companyId => $companyRecords)
        {
            foreach($companyRecords as $vendorWorkCategoryId => $records)
            {
                $row = [
                    'company'              => $records['company'],
                    'vendor_work_category' => $records['vendor_work_category'],
                ];

                foreach($historicalCycles->lists('id') as $cycleId)
                {
                    $row["{$cycleId}_score"] = isset($groupedResults[$companyId][$vendorWorkCategoryId]["{$cycleId}_score"]) ? $groupedResults[$companyId][$vendorWorkCategoryId]["{$cycleId}_score"] : null;
                }

                $data[] = $row;
            }
        }

        return Response::json($data);
    }

    public function totalEvaluatedByVendorGroup()
    {
        $vendorGroups = ContractGroupCategory::where('type', ContractGroupCategory::TYPE_EXTERNAL)->where('hidden', '=', false)->orderBy('name', 'asc')->lists('id');

        $latestEvaluationCycle = Cycle::getLatestCompletedCycle();

        $data = [];

        if($latestEvaluationCycle)
        {
            $data = \DB::select(\DB::raw("SELECT cgc.id, cgc.name as vendor_group,
                   COUNT(cgc.id) filter (where f.status_id = ".VendorPerformanceEvaluationCompanyForm::STATUS_COMPLETED.") as completed,
                   COUNT(cgc.id) filter (where f.status_id != ".VendorPerformanceEvaluationCompanyForm::STATUS_COMPLETED.") as in_progress
                FROM contract_group_categories cgc
                JOIN companies c ON cgc.id = c.contract_group_category_id
                JOIN vendor_performance_evaluation_company_forms f ON f.company_id = c.id
                JOIN vendor_performance_evaluations eval ON eval.id = f.vendor_performance_evaluation_id
                WHERE eval.vendor_performance_evaluation_cycle_id = {$latestEvaluationCycle->id}
                AND c.contract_group_category_id IN (".implode(',', $vendorGroups).")
                AND f.deleted_at IS NULL
                AND eval.deleted_at IS NULL
                GROUP BY cgc.id, cgc.name;
            "));
        }

        return Response::json($data);
    }

    public function totalEvaluatedByVendorCategory()
    {
        $vendorGroups = ContractGroupCategory::where('type', ContractGroupCategory::TYPE_EXTERNAL)->where('hidden', '=', false)->orderBy('name', 'asc')->lists('id');

        $latestEvaluationCycle = Cycle::getLatestCompletedCycle();

        $data = [];

        if($latestEvaluationCycle)
        {
            $data = \DB::select(\DB::raw("SELECT vc.id, vc.name as vendor_category,
                   COUNT(vc.id) filter (where f.status_id = ".VendorPerformanceEvaluationCompanyForm::STATUS_COMPLETED.") as completed,
                   COUNT(vc.id) filter (where f.status_id != ".VendorPerformanceEvaluationCompanyForm::STATUS_COMPLETED.") as in_progress
                FROM vendor_categories vc
                JOIN contract_group_categories cgc on cgc.id = vc.contract_group_category_id
                JOIN companies c ON cgc.id = c.contract_group_category_id
                JOIN vendor_performance_evaluation_company_forms f ON f.company_id = c.id
                JOIN vendor_performance_evaluations eval ON eval.id = f.vendor_performance_evaluation_id
                WHERE eval.vendor_performance_evaluation_cycle_id = {$latestEvaluationCycle->id}
                AND c.contract_group_category_id IN (".implode(',', $vendorGroups).")
                AND f.deleted_at IS NULL
                AND eval.deleted_at IS NULL
                GROUP BY vc.id, vc.name;
            "));
        }

        return Response::json($data);
    }

    public function averageScores()
    {
        $latestEvaluationCycle = Cycle::getLatestCompletedCycle();

        $data = [];

        if($latestEvaluationCycle)
        {
            $data = \DB::select(\DB::raw("select cgc.id, cgc.name, ROUND(AVG(vecs.score)) as average_score
                FROM vendor_evaluation_cycle_scores vecs
                JOIN companies c on c.id = vecs.company_id
                JOIN contract_group_categories cgc on cgc.id = c.contract_group_category_id
                WHERE vecs.vendor_performance_evaluation_cycle_id = {$latestEvaluationCycle->id}
                GROUP BY cgc.id, cgc.name;
            "));
        }

        return Response::json($data);
    }

    public function registrationStatisticsNewlyRegisteredVendorsByDate()
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $dateFromQuery = trim($request->get('dateFrom')) == '' ? '' : (" AND c.activation_date >= to_date('" . trim($request->get('dateFrom')) . "', 'DD/MM/YYYY')");
        $dateToQuery   = trim($request->get('dateTo')) == '' ? '' : (" AND c.activation_date <= to_date('" . trim($request->get('dateTo')) . "', 'DD/MM/YYYY')");

        $query = "WITH vendor_registrations AS (
                      SELECT * FROM vendor_registrations WHERE deleted_at IS NULL AND status = " . VendorRegistration::STATUS_COMPLETED . " AND revision = 0
                  )
                  SELECT LPAD(EXTRACT(DAY FROM DATE_TRUNC('day', c.activation_date))::TEXT, 2, '0') AS day, LPAD(EXTRACT(MONTH FROM DATE_TRUNC('month', c.activation_date))::TEXT, 2, '0') AS month, EXTRACT(YEAR FROM c.activation_date) AS year, 
                  cgc.name AS vendor_group,
                  vc.name AS vendor_category, 
                  COUNT(DISTINCT c.id) AS total,
                  ARRAY_TO_JSON(ARRAY_AGG(c.id)) AS company_ids
                  FROM companies c 
                  INNER JOIN vendor_registrations vr ON vr.company_id = c.id
                  INNER JOIN contract_group_categories cgc ON cgc.id = c.contract_group_category_id 
                  INNER JOIN company_vendor_category cvc ON cvc.company_id = c.id
                  INNER JOIN vendor_categories vc ON vc.id = cvc.vendor_category_id
                  WHERE cgc.type = " . ContractGroupCategory::TYPE_EXTERNAL . "
                  AND cgc.hidden IS FALSE
                  AND vc.hidden IS FALSE
                  {$dateFromQuery}
                  {$dateToQuery}";

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                $val = trim($filters['value']);
                switch(trim(strtolower($filters['field'])))
                {
                    case 'day':
                        if(strlen($val) > 0)
                        {
                            $query .= " AND LPAD(EXTRACT(DAY FROM DATE_TRUNC('day', c.activation_date))::TEXT, 2, '0') ILIKE '%{$val}%'";
                        }
                        break;
                    case 'month':
                        if(strlen($val) > 0)
                        {
                            $query .= " AND LPAD(EXTRACT(MONTH FROM DATE_TRUNC('month', c.activation_date))::TEXT, 2, '0') ILIKE '%{$val}%'";
                        }
                        break;
                    case 'year':
                        if(strlen($val) > 0)
                        {
                            $query .= " AND EXTRACT(YEAR FROM c.activation_date)::TEXT ILIKE '%{$val}%'";
                        }
                        break;
                    case 'vendor_group':
                        if(strlen($val) > 0)
                        {
                            $query .= " AND cgc.name ILIKE '%{$val}%'";
                        }
                        break;
                    case 'vendor_category':
                        if(strlen($val) > 0)
                        {
                            $query .= " AND vc.name ILIKE '%{$val}%'";
                        }
                        break;
                }
            }
        }

        $query .= " GROUP BY cgc.id, vc.id, DATE_TRUNC('day', c.activation_date), DATE_TRUNC('month', c.activation_date), EXTRACT(YEAR FROM c.activation_date)";
        $query .= " ORDER BY DATE_TRUNC('day', c.activation_date) DESC, DATE_TRUNC('month', c.activation_date) DESC, EXTRACT(YEAR FROM c.activation_date) DESC, cgc.id ASC, vc.id ASC";

        $allRecords = DB::select(DB::raw($query));
        $rowCount   = count($allRecords);

        $totalVendorsCount = array_sum(array_column($allRecords, 'total'));

        $offset = $limit * ($page - 1);

        $query .= " LIMIT {$limit} OFFSET {$offset};";

        $totalPages = ceil( $rowCount / $limit );

        $results = DB::select(DB::raw($query));

        $resultData = [];

        foreach($results as $key => $result)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $resultData[] = [
                'counter'         => $counter,
                'day'             => $result->day,
                'month'           => $result->month,
                'year'            => $result->year,
                'vendor_group'    => $result->vendor_group,
                'vendor_category' => $result->vendor_category,
                'total'           => $result->total,
                'company_ids'     => implode(',', json_decode($result->company_ids)),     
            ];
        }

        $data['totalVendorsCount'] = $totalVendorsCount;
        $data['data'] = [
            'last_page' => $totalPages,
            'data'      => $resultData,
        ];

        return Response::json($data);

    }

    public function registrationStatisticsNewlyRegisteredVendorsList()
    {
        $inputs = Input::all();

        $companyIds = explode(',', trim($inputs['companyIds']));

        $companies = Company::whereIn('id', $companyIds)->lists('name', 'id');

        $data = [];

        foreach($companies as $id => $name)
        {
            $data[] = ['name' => $name];
        }

        return Response::json($data);
    }

    public function exportVendorPerformanceEvaluationForms()
    {
        $cycle = Cycle::getLatestCompletedCycle();

        $logPath = PathRegistry::vendorPerformanceEvaluationFormReportsProgressLog($cycle->id);
        $filepath = PathRegistry::vendorPerformanceEvaluationFormReports($cycle->id);

        if(!file_exists($filepath))
        {
            App::abort(404);
        }
        if(file_exists($logPath))
        {
            App::abort(404);
        }

        $filename = trans('vendorManagement.vendorPerformanceEvaluation').' - '.$cycle->remarks;

        return Files::download($filepath, "{$filename}.".Files::EXTENSION_ZIP);
    }

    public function getVendorPerformanceEvaluationFormsProgress()
    {
        $cycle = Cycle::getLatestCompletedCycle();

        $logPath = PathRegistry::vendorPerformanceEvaluationFormReportsProgressLog($cycle->id);
        $filepath = PathRegistry::vendorPerformanceEvaluationFormReports($cycle->id);

        $isComplete = file_exists($filepath) && ! file_exists($logPath);

        $completionPercentage = $isComplete ? 100 : 0;

        if (file_exists($logPath))
        {
            $exist = true;
            try
            {
                $string = Files::getSplFileObjectLastLine($logPath);

                preg_match('/\[(\d+)%\]/', $string, $matches);

                $completionPercentage = $matches[1];
            }
            catch(\Exception $e)
            {
                \Log::error($e->getMessage());
            }
        } else {
            $exist = false;
        }

        return [
            'completion_percentage' => $completionPercentage,
            'is_complete'           => $isComplete,
            'exist'                 => $exist
        ];
    }

    public function exportVendorPerformanceEvaluationVendorWorkCategoryScores()
    {
        $reportGenerator = new VendorPerformanceEvaluationVendorWorkCategoryScoresExcelGenerator();

        return $reportGenerator->generate();
    }

    public function exportVendorPerformanceEvaluationVendorCategoryScores()
    {
        $reportGenerator = new VendorPerformanceEvaluationVendorCategoryScoresExcelGenerator();

        return $reportGenerator->generate();
    }

    public function getVendorStatistics()
    {
        if( ! Request::ajax() )
        {
            App::abort(404);
        }

        $preqGrade = VendorRegistrationAndPrequalificationModuleParameter::first()->vendorManagementGrade;
        $vpeGrade  = VendorPerformanceEvaluationModuleParameter::first()->vendorManagementGrade;
        $latestCompletedVpeCycle = Cycle::latestCompleted();

        $inputs                  = Input::all();
        $countryId               = ($inputs['country'] == '') ? null : $inputs['country'];
        $stateId                 = ($inputs['state'] == '') ? null : $inputs['state'];
        $vendorGroupId           = ($inputs['vendor_group'] == '') ? null : $inputs['vendor_group'];
        $vendorCategoryId        = ($inputs['vendor_category'] == '') ? null : $inputs['vendor_category'];
        $vendorWorkCategoryId    = ($inputs['vendor_work_category'] == '') ? null : $inputs['vendor_work_category'];
        $vendorWorkSubCategoryId = ($inputs['vendor_work_subcategory'] == '') ? null : $inputs['vendor_work_subcategory'];
        $registrationStatus      = ($inputs['registration_status'] == '') ? null : $inputs['registration_status']; 
        $companyStatus           = ($inputs['company_status'] == '') ? null : $inputs['company_status'];
        $preqGradeLevelId        = ($inputs['preq_grade'] == '') ? null : $inputs['preq_grade'];
        $vpeGradeLevelId         = ($inputs['vpe_grade'] == '') ? null : $inputs['vpe_grade'];

        $preqGradeLevel = $preqGradeLevelId ? VendorManagementGradeLevel::find($preqGradeLevelId) : null;
        $vpeGradeLevel  = $vpeGradeLevelId ? VendorManagementGradeLevel::find($vpeGradeLevelId) : null;

        $countryClause               = is_null($countryId) ? null : " AND ctry.id = {$countryId} ";
        $stateClause                 = is_null($stateId) ? null : " AND s.id = {$stateId} ";
        $vendorGroupClause           = is_null($vendorGroupId) ? null : " AND cgc.id = {$vendorGroupId} ";
        $vendorCategoryClause        = is_null($vendorCategoryId) ? null : " AND vc.id = {$vendorCategoryId} ";
        $vendorWorkCategoryClause    = is_null($vendorWorkCategoryId) ? null : " AND vwc.id = {$vendorWorkCategoryId} ";
        $vendorWorkSubCategoryClause = is_null($vendorWorkSubCategoryId) ? null : " AND vws.id = {$vendorWorkSubCategoryId} ";

        if($inputs['identifier'] == 'vendorsByVendorWorkSubCategory')
        {
            $vendorWorkSubCategoryClause .= " AND vws.id IS NOT NULL ";
        }

        if($inputs['identifier'] == 'vendorsByVendorWorkCategory')
        {
            $vendorWorkCategoryClause .= " AND vwc.id IS NOT NULL ";
        }

        $companyStatusClause = is_null($companyStatus) ? null : " AND c.company_status = {$companyStatus} ";

        $vendorWorkCategoryPreqClause = is_null($vendorWorkCategoryId) ? null : " AND vpq.vendor_work_category_id = {$vendorWorkCategoryId} ";
        $vendorWorkCategoryVpeClause  = is_null($vendorWorkCategoryId) ? null : " AND cs.vendor_work_category_id = {$vendorWorkCategoryId} ";
        $vendorWorkCategoryTrpClause  = is_null($vendorWorkCategoryId) ? null : " AND trp.vendor_work_category_id = {$vendorWorkCategoryId} ";

        $vendorWorkSubCategoryTrpClause = is_null($vendorWorkSubCategoryId) ? null : " AND trpvws.vendor_work_subcategory_id = {$vendorWorkSubCategoryId} ";

        $preqGradeClause = is_null($preqGradeLevel) ? null : " HAVING ROUND(AVG(vpq.score)) >= {$preqGradeLevel->score_lower_limit} AND ROUND(AVG(vpq.score)) <= {$preqGradeLevel->score_upper_limit} ";
        $vpeGradeClause  = is_null($vpeGradeLevel) ? null : " HAVING ROUND(AVG(cs.deliberated_score)) >= {$vpeGradeLevel->score_lower_limit} AND ROUND(AVG(cs.deliberated_score)) <= {$vpeGradeLevel->score_upper_limit} ";

        $completedPreqJoinClause = null;
        $preqGradeJoinClause     = null;

        if($preqGradeLevel || $inputs['identifier'] == 'vendorsByPreqRating')
        {
            $completedPreqJoinClause = " INNER JOIN latest_completed_preq lcvpq ON lcvpq.company_id = c.id ";
            $preqGradeJoinClause     = " INNER JOIN preq_grade_levels_cte preqlvl ON preqlvl.score_upper_limit >= lcvpq.average_preq_score AND preqlvl.score_lower_limit <= lcvpq.average_preq_score ";
        }

        $completedVpeJoinClause = null;
        $vpeGradeJoinClause     = null;

        if($latestCompletedVpeCycle && ($vpeGradeLevel || $inputs['identifier'] == 'vendorsByVpeRating'))
        {
            $completedVpeJoinClause = " INNER JOIN latest_completed_vpe lcvpe ON lcvpe.company_id = c.id ";
            $vpeGradeJoinClause     = " INNER JOIN vpe_grade_levels_cte vpelvl ON vpelvl.score_upper_limit >= lcvpe.average_vpe_score AND vpelvl.score_lower_limit <= lcvpe.average_vpe_score ";
        }

        $registrationStatusClause = null;

        if($registrationStatus)
        {
            switch($registrationStatus)
            {
                case Company::STATUS_ACTIVE:
                    $registrationStatusClause = " AND c.activation_date IS NOT NULL AND (c.expiry_date > NOW()) AND c.deactivated_at IS NULL ";
                    break;
                case Company::STATUS_EXPIRED:
                    $registrationStatusClause = " AND c.expiry_date IS NOT NULL AND (c.expiry_date <= NOW()) AND deactivated_at IS NULL ";
                    break;
                case Company::STATUS_DEACTIVATED:
                    $registrationStatusClause = " AND c.deactivated_at IS NOT NULL ";
                    break;
            }
        }

        $selectClause  = null;
        $groupByClause = null;
        $orderByClause = null;

        switch($inputs['identifier'])
        {
            case 'vendorsByCountry':
                $selectClause  = " SELECT ctry.id AS country_id, TRIM(ctry.country) AS country, COUNT(DISTINCT c.id) AS vendor_count ";
                $groupByClause = " GROUP BY ctry.id ";
                $orderByClause = " ORDER BY ctry.id ASC ";
                break;
            case 'vendorsByState':
                $selectClause  = " SELECT s.id AS state_id, TRIM(s.name) AS state, COUNT(DISTINCT c.id) AS vendor_count ";
                $groupByClause = " GROUP BY s.id ";
                $orderByClause = " ORDER BY s.id ASC ";
                break;
            case 'vendorsByVendorCategory':
                $selectClause  = " SELECT vc.id AS vendor_category_id, vc.name AS vendor_category, COUNT(DISTINCT c.id) AS vendor_count ";
                $groupByClause = " GROUP BY vc.id ";
                $orderByClause = " ORDER BY vc.id ASC ";
                break;
            case 'vendorsByVendorWorkCategory':
                $selectClause  = " SELECT vwc.id AS vendor_work_category_id, vwc.name AS vendor_work_category, COUNT(DISTINCT c.id) AS vendor_count ";
                $groupByClause = " GROUP BY vwc.id ";
                $orderByClause = " ORDER BY vwc.id ASC ";
                break;
            case 'vendorsByVendorWorkSubCategory':
                $selectClause  = "  SELECT vws.id AS vendor_work_subcategory_id, vws.name AS vendor_work_subcategory, COUNT(DISTINCT c.id) AS vendor_count";
                $groupByClause = " GROUP BY vws.id ";
                $orderByClause = " ORDER BY vws.id ASC ";
                break;
            case 'vendorsByVendorGroup':
                $selectClause  = " SELECT cgc.id, cgc.name AS vendor_group, COUNT(DISTINCT c.id) AS company_count ";
                $orderByClause = " ORDER BY cgc.id ";
                $groupByClause = " GROUP BY cgc.id ";
                break;
            case 'vendorsByRegistrationStatus':
                $selectClause  = " SELECT CASE
                                       WHEN(c.deactivated_at IS NOT NULL) THEN " . Company::STATUS_DEACTIVATED . "
                                       WHEN(c.expiry_date IS NOT NULL AND (c.expiry_date <= NOW()) AND deactivated_at IS NULL) THEN " . Company::STATUS_EXPIRED . "
                                       WHEN(c.activation_date IS NOT NULL AND c.deactivated_at IS NULL) THEN " . Company::STATUS_ACTIVE . "
                                   END AS registration_status, COUNT(DISTINCT c.id) AS vendor_count ";
                $groupByClause = " GROUP BY registration_status ";
                $orderByClause = " ORDER BY registration_status ASC ";
                break;
            case 'vendorsByCompanyStatus':
                $selectClause  = " SELECT c.company_status, COUNT(DISTINCT c.id) AS vendor_count ";
                $groupByClause = " GROUP BY c.company_status ";
                $orderByClause = " ORDER BY c.company_status ASC ";
                break;
            case 'vendorsByPreqRating':
                $selectClause  = " SELECT preqlvl.id, preqlvl.description, COUNT(DISTINCT c.id) AS vendor_count ";
                $groupByClause = " GROUP BY preqlvl.id, preqlvl.description ";
                $orderByClause = " ORDER BY preqlvl.id DESC ";
                break;
            case 'vendorsByVpeRating':
                $selectClause  = " SELECT vpelvl.id, vpelvl.description, COUNT(DISTINCT c.id) AS vendor_count ";
                $groupByClause = " GROUP BY vpelvl.id, vpelvl.description ";
                $orderByClause = " ORDER BY vpelvl.id DESC ";
                break;
        }

        $latestCompletedVpeQuery = null;

        if($latestCompletedVpeCycle)
        {
            $latestCompletedVpeQuery = "latest_completed_vpe AS (
                                            SELECT cs.company_id, ROUND(AVG(cs.deliberated_score)) AS average_vpe_score
                                            FROM vendor_evaluation_cycle_scores cs
                                            WHERE cs.vendor_performance_evaluation_cycle_id = {$latestCompletedVpeCycle->id}
                                            AND cs.deleted_at IS NULL
                                            {$vendorWorkCategoryVpeClause}
                                            GROUP BY cs.company_id 
                                            {$vpeGradeClause}
                                        ),";
        }

        $query = "WITH vendor_registrations_cte AS (
                      SELECT ROW_NUMBER() OVER (PARTITION BY company_id ORDER BY revision DESC) AS rank, * 
                      FROM vendor_registrations 
                      WHERE deleted_at IS NULL 
                      AND status = " . VendorRegistration::STATUS_COMPLETED . "
                  ),
                  preq_grade_levels_cte AS (
                      SELECT id, description, score_upper_limit, LEAD((score_upper_limit + 1), 1, 0) OVER (ORDER BY score_upper_limit DESC) AS score_lower_limit
                      FROM vendor_management_grade_levels
                      WHERE vendor_management_grade_id = {$preqGrade->id}
                      ORDER BY score_upper_limit DESC
                  ),
                  vpe_grade_levels_cte AS (
                      SELECT id, description, score_upper_limit, LEAD((score_upper_limit + 1), 1, 0) OVER (ORDER BY score_upper_limit DESC) AS score_lower_limit
                      FROM vendor_management_grade_levels
                      WHERE vendor_management_grade_id = {$vpeGrade->id}
                      ORDER BY score_upper_limit DESC
                  ),
                  latest_completed_preq AS (
                      SELECT vr.company_id, ROUND(AVG(vpq.score)) AS average_preq_score
                      FROM vendor_pre_qualifications vpq 
                      INNER JOIN vendor_registrations_cte vr ON vr.id = vpq.vendor_registration_id AND vr.rank = 1
                      WHERE vpq.status_id = " . VendorPreQualification::STATUS_COMPLETED . "
                      AND vpq.deleted_at IS NULL
                      AND vpq.score IS NOT NULL
                      {$vendorWorkCategoryPreqClause}
                      GROUP BY vr.company_id
                      {$preqGradeClause}
                  ),
                  {$latestCompletedVpeQuery}
                  latest_track_record_projects_vws AS (
                      SELECT DISTINCT vr.company_id, trp.vendor_work_category_id, trpvws.vendor_work_subcategory_id 
                      FROM track_record_projects trp 
                      INNER JOIN vendor_registrations_cte vr ON vr.id = trp.vendor_registration_id AND vr.rank = 1
                      LEFT JOIN track_record_project_vendor_work_subcategories trpvws ON trpvws.track_record_project_id = trp.id
                      WHERE TRUE
                      {$vendorWorkCategoryTrpClause}
                      {$vendorWorkSubCategoryTrpClause}
                  )
                  {$selectClause}
                  FROM companies c
                  INNER JOIN vendor_registrations_cte vrcte ON vrcte.company_id = c.id AND vrcte.rank = 1
                  INNER JOIN countries ctry ON ctry.id = c.country_id 
                  INNER JOIN states s ON s.id = c.state_id 
                  INNER JOIN contract_group_categories cgc ON cgc.id = c.contract_group_category_id AND cgc.type = " . ContractGroupCategory::TYPE_EXTERNAL . " AND cgc.hidden IS FALSE
                  INNER JOIN company_vendor_category cvc ON cvc.company_id = c.id
                  INNER JOIN vendor_categories vc ON vc.id = cvc.vendor_category_id
                  {$completedPreqJoinClause}
                  {$preqGradeJoinClause}
                  {$completedVpeJoinClause}
                  {$vpeGradeJoinClause}
                  LEFT OUTER JOIN latest_track_record_projects_vws trpvws ON trpvws.company_id = c.id
                  LEFT OUTER JOIN vendor_work_categories vwc ON vwc.id = trpvws.vendor_work_category_id
                  LEFT OUTER JOIN vendor_work_subcategories vws ON vws.id = trpvws.vendor_work_subcategory_id
                  WHERE c.confirmed IS TRUE
                  {$registrationStatusClause}
                  {$countryClause}
                  {$stateClause}
                  {$vendorGroupClause}
                  {$vendorCategoryClause}
                  {$vendorWorkCategoryClause}
                  {$vendorWorkSubCategoryClause}
                  {$companyStatusClause}
                  {$groupByClause}
                  {$orderByClause};";

        $queryResults = DB::select(DB::raw($query));

        $data = [];

        switch($inputs['identifier'])
        {
            case 'vendorsByCountry':
                $data = $this->getVendorsByCountryReturnFormat($queryResults);
                break;
            case 'vendorsByState':
                $data = $this->getVendorsByStateReturnFormat($queryResults);
                break;
            case 'vendorsByVendorCategory':
            case 'vendorsByVendorWorkCategory':
            case 'vendorsByVendorWorkSubCategory':
                $data = $queryResults;
                break;
            case 'vendorsByVendorGroup':
                $data = $this->getVendorsByVendorGroupReturnFormat($queryResults);
                break;
            case 'vendorsByRegistrationStatus':
                $data = $this->getVendorsByRegistrationStatusReturnFormat($queryResults);
                break;
            case 'vendorsByCompanyStatus':
                $data = $this->getVendorsByCompanyStatusReturnFormat($queryResults);
                break;
            case 'vendorsByPreqRating':
                $data = $this->getVendorsByPreqRatingReturnFormat($queryResults);
                break;
            case 'vendorsByVpeRating':
                $data = $this->getVendorsByVpeRatingReturnFormat($queryResults);
                break;
        }

        return $data;
    }

    private function getVendorsByCountryReturnFormat($queryResults)
    {
        $labels = [];
        $series = [];

        if(count($queryResults) > 0)
        {
            foreach($queryResults as $result)
            {
                array_push($labels, $result->country);
                array_push($series, $result->vendor_count);
            }
        }

        return Response::json([
            'labels' => $labels,
            'series' => $series,
        ]);
    }

    private function getVendorsByStateReturnFormat($queryResults)
    {
        $labels = [];
        $series = [];

        if(count($queryResults) > 0)
        {
            foreach($queryResults as $result)
            {
                array_push($labels, $result->state);
                array_push($series, $result->vendor_count);
            }
        }

        return Response::json([
            'labels' => $labels,
            'series' => $series,
        ]);
    }

    private function getVendorsByRegistrationStatusReturnFormat($queryResults)
    {
        $labels = [];
        $series = [];

        if(count($queryResults) > 0)
        {
            foreach($queryResults as $result)
            {
                $registrationStatus = Company::getStatusDescriptions($result->registration_status);

                array_push($labels, $registrationStatus);
                array_push($series, $result->vendor_count);
            }
        }

        return Response::json([
            'labels' => $labels,
            'series' => $series,
        ]);
    }

    private function getVendorsByCompanyStatusReturnFormat($queryResults)
    {
        $labels = [];
        $series = [];

        if(count($queryResults) > 0)
        {
            foreach($queryResults as $result)
            {
                $companyStatus = Company::getCompanyStatusDescriptions($result->company_status);

                array_push($labels, $companyStatus);
                array_push($series, $result->vendor_count);
            }
        }

        return Response::json([
            'labels' => $labels,
            'series' => $series,
        ]);
    }

    private function getVendorsByVendorGroupReturnFormat($queryResults)
    {
        $labels = [];
        $series = [];

        if(count($queryResults) > 0)
        {
            foreach($queryResults as $result)
            {
                array_push($labels, $result->vendor_group);
                array_push($series, $result->company_count);
            }
        }

        return Response::json([
            'labels' => $labels,
            'series' => $series,
        ]);
    }

    private function getVendorsByPreqRatingReturnFormat($queryResults)
    {
        $labels = [];
        $series = [];

        if(count($queryResults) > 0)
        {
            foreach($queryResults as $result)
            {
                $labelValue  = $result->description;
                $seriesValue = $result->vendor_count;

                array_push($labels, $result->description);
                array_push($series, $result->vendor_count);
            }
        }

        return Response::json([
            'labels' => $labels,
            'series' => $series,
        ]);
    }

    private function getVendorsByVpeRatingReturnFormat($queryResults)
    {
        $labels = [];
        $series = [];

        if(count($queryResults) > 0)
        {
            foreach($queryResults as $result)
            {
                $labelValue  = $result->description;
                $seriesValue = $result->vendor_count;

                array_push($labels, $result->description);
                array_push($series, $result->vendor_count);
            }
        }

        return Response::json([
            'labels' => $labels,
            'series' => $series,
        ]);
    }
}