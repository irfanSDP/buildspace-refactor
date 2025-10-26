<?php namespace PCK\VendorRegistration;

use Illuminate\Support\Facades\DB;
use PCK\Companies\Company;
use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\ModuleParameters\VendorManagement\VendorManagementGrade;
use PCK\ModuleParameters\VendorManagement\VendorRegistrationAndPrequalificationModuleParameter;

class VendorProfileRepository
{
    public function list($request)
    {
        $limit = ((int) $request->get('size')) ? (int) $request->get('size') : 1;
        $page = ((int) $request->get('page')) ? ((int) $request->get('page')) : 1;

        $offset = $limit * ($page - 1);

        $globalPreQGrade = VendorRegistrationAndPrequalificationModuleParameter::first()->vendorManagementGrade;

        $countQuery = $this->buildQuery($request, $globalPreQGrade, true);
        $countResult = DB::select(DB::raw($countQuery));
        $rowCount = $countResult[0]->row_count;

        $dataQuery = $this->buildQuery($request, $globalPreQGrade);
        $dataQuery .= " OFFSET {$offset} LIMIT {$limit}";

        $records = DB::select(DB::raw($dataQuery));

        usort($records, function($a, $b) {
            return $b->id <=> $a->id;
        });

        $data = [];

        foreach ($records as $key => $record) {
            $counter = ($page - 1) * $limit + $key + 1;

            $data[] = [
                'id' => $record->id,
                'counter' => $counter,
                'name' => $record->name,
                'vendor_code' => Company::getVendorCodeFromId($record->id),
                'reference_no' => $record->reference_no,
                'state' => $record->state,
                'country' => $record->country,
                'route:show' => route('vendorProfile.show', array($record->id)),
                'tagging' => null,
                'preQualification' => null,
                'appraisalScore' => null,
                'bimLevel' => null,
                'activationDate' => $record->activation_date,
                'expiryDate' => $record->expiry_date,
                'reputableDevelopers' => null,
                'vendor_group' => $record->vendor_group,
                'vendorSubworkCategory' => null,
                'remarks' => null,
                'vendor_status_text' => is_null($record->vendor_status_text) ? '' : $record->vendor_status_text,
                'vendor_status' => $record->vendor_status,
                'tags' => implode(', ', json_decode($record->tags, true)),
                'tagsArray' => json_decode($record->tags, true),
                'cidbGrade' => $record->cidb_grade,
                'bimInformation' => $record->bim_level,
                'status' => VendorRegistration::getStatusText($record->vr_status),
                'submission_type_text' => VendorRegistration::getSubmissionTypeText($record->submission_type),
                'submission_type' => $record->submission_type,
                'pre_qualification_score' => $record->preq_score,
                'pre_qualification_grade' => $record->preq_grade,
            ];
        }

        $totalPages = ceil($rowCount / $limit);

        return [$data, $totalPages];
    }

    private function buildQuery($request, VendorManagementGrade $globalPreQGrade, $counting = false)
    {
        $selectClause = "c.id, c.name, ctry.country, s.name AS state, c.reference_no, cg.id AS cidb_grade_id, cg.grade AS cidb_grade, cgc.id AS vendor_group_id, cgc.name AS vendor_group, 
                         cg.id AS cidb_grade_id, cg.grade AS cidb_grade_name, bl.id AS bim_level_id, bl.name AS bim_level, 
                         TO_CHAR(c.expiry_date, 'DD/MM/YYYY') AS expiry_date,
                         TO_CHAR(c.activation_date, 'DD/MM/YYYY') AS activation_date,
                         TO_CHAR(c.deactivation_date, 'DD/MM/YYYY') AS deactivation_date,
                         TO_CHAR(c.deactivated_at, 'DD/MM/YYYY') AS deactivated_at,
                         COALESCE(cvc.vendor_categories, '[]'::json) AS vendor_categories, COALESCE(vwc.vendor_work_categories, '[]'::json) AS vendor_work_categories,
                         CASE 
                             WHEN c.activation_date IS NOT NULL 
                                 AND (c.expiry_date IS NULL OR c.expiry_date > NOW()) 
                                 AND c.deactivated_at IS NULL
                             THEN " . Company::STATUS_ACTIVE . "
                             WHEN c.expiry_date IS NOT NULL 
                                 AND c.expiry_date <= NOW() 
                                 AND c.deactivated_at IS NULL
                             THEN " . Company::STATUS_EXPIRED . "
                             WHEN c.activation_date IS NOT NULL 
                                 AND c.deactivated_at IS NOT NULL
                             THEN " . Company::STATUS_DEACTIVATED . "
                         END vendor_status,
                         CASE 
                             WHEN c.activation_date IS NOT NULL 
                                 AND (c.expiry_date IS NULL OR c.expiry_date > NOW()) 
                                 AND c.deactivated_at IS NULL
                             THEN 'ACTIVE'
                             WHEN c.expiry_date IS NOT NULL 
                                 AND c.expiry_date <= NOW() 
                                 AND c.deactivated_at IS NULL
                             THEN 'EXPIRED'
                             WHEN c.activation_date IS NOT NULL 
                                 AND c.deactivated_at IS NOT NULL
                             THEN 'DEACTIVATED'
                         END vendor_status_text,
                         c.company_status, vr.submission_type, vr.status AS vr_status, preq.average_score AS preq_score, gl.id AS grade_level_id, gl.description AS preq_grade, taglist.tags, taglist.tag_ids";

        if ($counting)
        {
            $selectClause = "COUNT(DISTINCT c.id) AS row_count";
        }

        $query = "
            WITH global_grade_levels AS (
                SELECT id, description,
                    COALESCE(
                        LAG(score_upper_limit) OVER (
                            PARTITION BY vendor_management_grade_id
                            ORDER BY score_upper_limit ASC
                        ), -1
                    ) + 1 AS score_lower_limit,
                    score_upper_limit
                FROM vendor_management_grade_levels
                WHERE vendor_management_grade_id = {$globalPreQGrade->id}
            ),
            eligible_vendor_registrations AS (
                SELECT DISTINCT company_id 
                FROM vendor_registrations
                WHERE deleted_at IS NULL 
                AND revision = 0
                AND status = " . VendorRegistration::STATUS_COMPLETED . "
            ),
            ranked_vendor_registrations AS (
                SELECT ROW_NUMBER() OVER (PARTITION BY vr.company_id ORDER BY vr.revision DESC) AS ranking, vr.*
                FROM vendor_registrations vr 
                INNER JOIN eligible_vendor_registrations e ON e.company_id = vr.company_id 
                WHERE vr.deleted_at IS NULL
            ),
            latest_vendor_registrations AS (
                SELECT * 
                FROM ranked_vendor_registrations
                WHERE ranking = 1
            )
            SELECT 
            {$selectClause}
            FROM companies c 
            INNER JOIN latest_vendor_registrations vr ON vr.company_id = c.id
            INNER JOIN contract_group_categories cgc ON cgc.id = c.contract_group_category_id 
            INNER JOIN countries ctry ON ctry.id = c.country_id 
            INNER JOIN states s ON s.id = c.state_id 
            LEFT JOIN cidb_grades cg ON cg.id = c.cidb_grade 
            LEFT JOIN building_information_modelling_levels bl ON bl.id = c.bim_level_id 
            LEFT JOIN LATERAL (
                SELECT JSON_AGG(DISTINCT vc.name ORDER BY vc.name) AS vendor_categories
                FROM company_vendor_category cvc
                JOIN vendor_categories vc ON vc.id = cvc.vendor_category_id
                WHERE cvc.company_id = c.id
            ) cvc ON true
            LEFT JOIN LATERAL (
            SELECT JSON_AGG(vwc.name ORDER BY vwc.name) AS vendor_work_categories
            FROM (
                SELECT DISTINCT v.vendor_work_category_id
                FROM vendors v
                WHERE v.company_id = c.id
            ) d
            JOIN vendor_work_categories vwc ON vwc.id = d.vendor_work_category_id
            ) vwc ON true
            LEFT JOIN LATERAL (
                SELECT ROUND(AVG(vpq.score))::int AS average_score
                FROM vendor_pre_qualifications vpq
                WHERE vpq.vendor_registration_id = vr.id
                AND vpq.deleted_at IS NULL
            ) preq ON TRUE
            LEFT JOIN global_grade_levels gl ON preq.average_score BETWEEN gl.score_lower_limit AND gl.score_upper_limit
            LEFT JOIN LATERAL (
                SELECT 
                COALESCE(JSON_AGG(DISTINCT t.id), '[]'::JSON) AS tag_ids, 
                COALESCE(JSON_AGG(DISTINCT t.name ORDER BY t.name), '[]'::JSON) AS tags
                FROM object_tags ot
                JOIN tags t ON t.id = ot.tag_id AND t.category = 1
                WHERE ot.object_class = 'PCK\Companies\Company'
                AND ot.object_id = c.id
            ) taglist ON TRUE
            WHERE TRUE
            AND cgc.type = " . ContractGroupCategory::TYPE_EXTERNAL;

        $tagIds = [];
        $tagName = null;

        // advanced search
        if (strlen(trim($request->get('criteria_search_str'))) > 0) 
        {
            $searchStr = '%' . urldecode(trim($request->get('criteria_search_str'))) . '%';

            switch ($request->get('search_criteria')) {
                case 'company_name':
                    $query .= " AND c.name ILIKE '" . $searchStr . "' ";
                    break;
                case 'reference_no':
                    $query .= " AND c.reference_no ILIKE '" . $searchStr . "' ";
                    break;
                case 'vendor_category':
                    $query .= " AND EXISTS (
                    	SELECT 1
                    	FROM json_array_elements_text(COALESCE(cvc.vendor_categories, '[]'::json)) AS vc(cat)
                    	WHERE vc.cat ILIKE '" . $searchStr . "'
                    ) ";
                    break;
                case 'vendor_work_category':
                    $query .= " AND EXISTS (
                    	SELECT 1
                    	FROM json_array_elements_text(COALESCE(vwc.vendor_work_categories, '[]'::json)) AS vwc(wcat)
                    	WHERE vwc.wcat ILIKE '" . $searchStr . "'
                    ) ";
                    break;
                case 'state':
                    $query .= " AND s.name ILIKE '" . $searchStr . "' ";
                    break;
            }
        }

        if($request->has('vendor_status') && strlen(trim($request->get('vendor_status')) > 0))
        {
            switch(trim($request->get('vendor_status')))
            {
                case Company::STATUS_ACTIVE:
                    $query .= " AND c.activation_date IS NOT NULL 
                            AND (c.expiry_date IS NULL OR c.expiry_date > NOW()) 
                            AND c.deactivated_at IS NULL";
                    break;
                case Company::STATUS_EXPIRED:
                    $query .= " AND c.expiry_date IS NOT NULL
                            AND c.expiry_date <= NOW() 
                            AND c.deactivated_at IS NULL";
                    break;
                case Company::STATUS_DEACTIVATED:
                    $query .= " AND c.activation_date IS NOT NULL 
                            AND c.deactivated_at IS NOT NULL";
                    break;
            }
        }

        if($request->has('contract_group_category_id') && (int)$request->get('contract_group_category_id') > 0)
        {
            $query .= " AND cgc.id = " . (int)$request->get('contract_group_category_id') . " ";
        }

        if($request->has('company_status'))
        {
            $query .= " AND c.company_status IN (" . implode(',', $request->get('company_status')) . ") ";
        }

        if(($request->has('activation_date_from') and strlen(trim($request->get('activation_date_from'))) > 0) and ($request->has('activation_date_to') and strlen(trim($request->get('activation_date_to'))) > 0))
        {
            $activationDateFrom = date('Y-m-d', strtotime(trim($request->get('activation_date_from'))));
            $activationDateTo = date('Y-m-d', strtotime(trim($request->get('activation_date_to'))));

            $query .= " AND c.activation_date::date BETWEEN '" . $activationDateFrom . "'::date AND '" . $activationDateTo . "'::date ";
        }

        if(($request->has('expiry_date_from') and strlen(trim($request->get('expiry_date_from'))) > 0) and ($request->has('expiry_date_to') and strlen(trim($request->get('expiry_date_to'))) > 0))
        {
            $expiryDateFrom = date('Y-m-d', strtotime(trim($request->get('expiry_date_from'))));
            $expiryDateTo = date('Y-m-d', strtotime(trim($request->get('expiry_date_to'))));

            $query .= " AND c.expiry_date::date BETWEEN '" . $expiryDateFrom . "'::date AND '" . $expiryDateTo . "'::date ";
        }

        if(($request->has('deactivation_date_from') and strlen(trim($request->get('deactivation_date_from'))) > 0) and ($request->has('deactivation_date_to') and strlen(trim($request->get('deactivation_date_to'))) > 0))
        {
            $deactivationDateFrom = date('Y-m-d', strtotime(trim($request->get('deactivation_date_from'))));
            $deactivationDateTo = date('Y-m-d', strtotime(trim($request->get('deactivation_date_to'))));

            $query .= " AND c.deactivation_date::date BETWEEN '" . $deactivationDateFrom . "'::date AND '" . $deactivationDateTo . "'::date ";
        }

        //tabulator filters
        if ($request->has('filters')) {
            foreach ($request->get('filters') as $filters) {
                if (!isset($filters['value']) || is_array($filters['value']))
                    continue;

                $val = trim($filters['value']);

                switch (trim(strtolower($filters['field']))) {
                    case 'vendor_code':
                        if (strlen($val) > 0) {
                            $vendorCodePrefix = getenv('VENDOR_CODE_PREFIX') ? getenv('VENDOR_CODE_PREFIX') : "BSP";
                            $vendorCodePadLength = getenv('VENDOR_CODE_PAD_LENGTH') ? getenv('VENDOR_CODE_PAD_LENGTH') : 5;

                            $query .= " AND '" . $vendorCodePrefix . "' || LPAD(c.id::text, " . $vendorCodePadLength . ", '0') ILIKE '%" . $val . "%' ";
                        }
                        break;
                    case 'vendor_group':
                        if ((int) $val > 0) {
                            $query .= " AND cgc.id = " . (int) $val . " ";
                        }
                        break;
                    case 'activationdate':
                        if (strlen($val) > 0) {
                            $query .= " AND TO_CHAR(c.activation_date, 'DD/MM/YYYY') ILIKE '%" . $val . "%' ";
                        }
                        break;
                    case 'expirydate':
                        if (strlen($val) > 0) {
                            $query .= " AND TO_CHAR(c.expiry_date, 'DD/MM/YYYY') ILIKE '%" . $val . "%' ";
                        }
                        break;
                    case 'name':
                        if (strlen($val) > 0) {
                            $query .= " AND c.name ILIKE '%" . $val . "%' ";
                        }
                        break;
                    case 'reference_no':
                        if (strlen($val) > 0) {
                            $query .= " AND c.reference_no ILIKE '%" . $val . "%' ";
                        }
                        break;
                    case 'state':
                        if (strlen($val) > 0) {
                            $query .= " AND s.name ILIKE '%" . $val . "%' ";
                        }
                        break;
                    case 'country':
                        if (strlen($val) > 0) {
                            $query .= " AND ctry.country ILIKE '%" . $val . "%' ";
                        }
                        break;
                    case 'tids':
                        if (strlen($val) > 0) {
                            $tagIds[] = (int) $val;
                        }
                        break;
                    case 'tags':
                        if (strlen($val) > 0) {
                            $tagName = $val;
                        }
                        break;
                    case 'vendor_status_text':
                        if ((int) $val > 0) {
                            switch ($val) {
                                case Company::STATUS_ACTIVE:
                                    $query .= " AND c.activation_date IS NOT NULL 
                                            AND (c.expiry_date IS NULL OR c.expiry_date > NOW()) 
                                            AND c.deactivated_at IS NULL";
                                    break;
                                case Company::STATUS_EXPIRED:
                                    $query .= " AND c.expiry_date IS NOT NULL
                                            AND c.expiry_date <= NOW() 
                                            AND c.deactivated_at IS NULL";
                                    break;
                                case Company::STATUS_DEACTIVATED:
                                    $query .= " AND c.activation_date IS NOT NULL 
                                            AND c.deactivated_at IS NOT NULL";
                                    break;
                            }
                        }
                        break;
                    case 'status':
                        if ((int) $val > 0) {
                            $query .= " AND vr.status = " . (int) $val;
                        }
                        break;
                    case 'submission_type':
                        if ((int) $val > 0) {
                            $query .= " AND vr.submission_type = " . (int) $val;
                        }
                        break;
                    case 'pre_qualification_grade':
                        if ((int) $val > 0) {
                            $query .= " AND gl.id = " . (int) $val . " ";
                        }
                        break;
                    case 'cidbgrade':
                        if ($val && $val != 'All') {
                            $query .= " AND cg.grade = '" . $val . "'";
                        }
                        break;
                    case 'biminformation':
                        if (strlen($val) > 0 && $val != 'All') {
                            $query .= " AND bl.name = '" . $val . "'";
                        }
                        break;
                }
            }
        }

        // for tags
        if (count($tagIds) > 0 || strlen($tagName) > 0) {
            $query .= "
            AND EXISTS (
                SELECT 1
                FROM object_tags otf
                JOIN tags tf ON tf.id = otf.tag_id AND tf.category = 1
                WHERE otf.object_class = 'PCK\Companies\Company'
                AND otf.object_id    = c.id
        ";

            if (count($tagIds) > 0) {
                $query .= " AND otf.tag_id IN (" . implode(',', $tagIds) . ") ";
            }

            if (strlen($tagName) > 0) {
                $query .= " AND tf.name ILIKE '%" . $tagName . "%' ";
            }

            $query .= " ) ";
        }

        return $query;
    }
}