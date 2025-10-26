<?php
use Carbon\Carbon;

use PCK\Users\User;
use PCK\Companies\Company;
use PCK\VendorCategory\VendorCategory;
use PCK\ConsultantManagement\ConsultantManagementContract;
use PCK\ConsultantManagement\ConsultantManagementSubsidiary;
use PCK\ConsultantManagement\ConsultantManagementCallingRfp;
use PCK\ConsultantManagement\ConsultantManagementCallingRfpCompany;
use PCK\ConsultantManagement\ConsultantManagementConsultantRfp;
use PCK\ConsultantManagement\ConsultantManagementConsultantRfpProposedFee;
use PCK\ConsultantManagement\ConsultantManagementConsultantRfpCommonInformation;
use PCK\ConsultantManagement\ConsultantManagementQuestionnaire;
use PCK\ConsultantManagement\ConsultantManagementQuestionnaireOption;
use PCK\ConsultantManagement\ConsultantManagementExcludeQuestionnaire;
use PCK\ConsultantManagement\ConsultantManagementRfpQuestionnaire;
use PCK\ConsultantManagement\ConsultantManagementRfpQuestionnaireOption;
use PCK\ConsultantManagement\ConsultantManagementConsultantQuestionnaire;
use PCK\ConsultantManagement\ConsultantManagementConsultantQuestionnaireReply;
use PCK\ConsultantManagement\ConsultantManagementConsultantRfpQuestionnaireReply;
use PCK\ConsultantManagement\ConsultantManagementConsultantReplyAttachment;
use PCK\ConsultantManagement\ConsultantManagementConsultantRfpReplyAttachment;
use PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp;
use PCK\ConsultantManagement\LetterOfAward;
use PCK\ConsultantManagement\ConsultantUser;

use PCK\Notifications\EmailNotifier;

use PCK\Helpers\PdfHelper;

use PCK\Helpers\ModuleAttachment;
use PCK\ObjectField\ObjectField;
use PCK\Base\Upload;

use PCK\Forms\ConsultantManagement\ConsultantRfpCommonInfoForm;
use PCK\Forms\ConsultantManagement\ConsultantRfpProposedFeeForm;

class ConsultantManagementConsultantController extends \BaseController
{
    private $consultantRfpCommonInfoForm;
    private $consultantRfpProposedFeeForm;
    private $emailNotifier;

    public function __construct(ConsultantRfpCommonInfoForm $consultantRfpCommonInfoForm, ConsultantRfpProposedFeeForm $consultantRfpProposedFeeForm, EmailNotifier $emailNotifier)
    {
        $this->consultantRfpCommonInfoForm = $consultantRfpCommonInfoForm;
        $this->consultantRfpProposedFeeForm = $consultantRfpProposedFeeForm;
        $this->emailNotifier = $emailNotifier;
    }

    public function callingRfpIndex()
    {
        return View::make('consultant_management.consultant.calling_rfp.index');
    }

    public function callingRfpList()
    {
        $user = \Confide::user();

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = ConsultantManagementContract::select("consultant_management_calling_rfp.id AS id", "consultant_management_contracts.title AS title",
        "consultant_management_contracts.reference_no AS reference_no", "vendor_categories.name AS rfp_name", "consultant_management_calling_rfp.closing_rfp_date",
        "countries.country AS country_name", "states.name AS state_name", "consultant_management_contracts.created_at AS created_at")
        ->join('consultant_management_vendor_categories_rfp', 'consultant_management_vendor_categories_rfp.consultant_management_contract_id', '=', 'consultant_management_contracts.id')
        ->join('vendor_categories', 'vendor_categories.id', '=', 'consultant_management_vendor_categories_rfp.vendor_category_id')
        ->join(\DB::raw("(SELECT MAX(rev.revision) AS revision, rev.vendor_category_rfp_id
        FROM consultant_management_rfp_revisions rev
        JOIN consultant_management_calling_rfp crfp ON rev.id = crfp.consultant_management_rfp_revision_id
        JOIN consultant_management_calling_rfp_companies crfpc ON crfpc.consultant_management_calling_rfp_id = crfp.id
        WHERE crfpc.company_id = ".$user->company_id." AND crfpc.status = ".ConsultantManagementCallingRfpCompany::STATUS_YES."
        AND crfp.status = ".ConsultantManagementCallingRfp::STATUS_APPROVED."
        AND crfp.closing_rfp_date >= '".date('Y-m-d H:i:s')."' AND crfp.calling_rfp_date <= '".date('Y-m-d H:i:s')."'
        GROUP BY rev.vendor_category_rfp_id) maxrev"), 'maxrev.vendor_category_rfp_id', '=', 'consultant_management_vendor_categories_rfp.id')
        ->join('consultant_management_rfp_revisions', function($join){
            $join->on('consultant_management_rfp_revisions.vendor_category_rfp_id', '=', 'consultant_management_vendor_categories_rfp.id');
            $join->on('consultant_management_rfp_revisions.revision','=', 'maxrev.revision');
        })
        ->join('consultant_management_calling_rfp', 'consultant_management_calling_rfp.consultant_management_rfp_revision_id', '=', 'consultant_management_rfp_revisions.id')
        ->join('consultant_management_calling_rfp_companies', 'consultant_management_calling_rfp_companies.consultant_management_calling_rfp_id', '=', 'consultant_management_calling_rfp.id')
        ->join('countries', 'consultant_management_contracts.country_id', '=', 'countries.id')
        ->join('states', 'consultant_management_contracts.state_id', '=', 'states.id')
        ->where('consultant_management_calling_rfp_companies.company_id', '=', $user->company_id)
        ->where('consultant_management_calling_rfp_companies.status', '=', ConsultantManagementCallingRfpCompany::STATUS_YES)
        ->where('consultant_management_calling_rfp.status', '=', ConsultantManagementCallingRfp::STATUS_APPROVED)
        ->whereRaw("consultant_management_calling_rfp.closing_rfp_date >= '".date('Y-m-d H:i:s')."' ")
        ->whereRaw("consultant_management_calling_rfp.calling_rfp_date <= '".date('Y-m-d H:i:s')."' ");

        //tabulator filters
        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(!isset($filters['value']) || is_array($filters['value'])) continue;

                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'title':
                        if(strlen($val) > 0)
                        {
                            $model->where('consultant_management_contracts.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference_no':
                        if(strlen($val) > 0)
                        {
                            $model->where('consultant_management_contracts.reference_no', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'rfp_name':
                        if(strlen($val) > 0)
                        {
                            $model->where('vendor_categories.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->groupBy(\DB::raw('consultant_management_contracts.id, vendor_categories.id, consultant_management_calling_rfp.id, countries.id, states.id'))
        ->orderBy('consultant_management_contracts.created_at', 'desc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];
        
        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'            => $record->id,
                'counter'       => $counter,
                'title'         => trim($record->title),
                'rfp_name'      => trim($record->rfp_name),
                'reference_no'  => trim($record->reference_no),
                'country'       => trim($record->country_name),
                'state'         => trim($record->state_name),
                'created_at'    => Carbon::parse($record->created_at)->format('d/m/Y'),
                'closing_date'  => Carbon::parse($record->closing_rfp_date)->format('d/m/Y H:i:s'),
                'route:show'    => route('consultant.management.consultant.calling.rfp.show', [$record->id])
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function callingRfpShow(ConsultantManagementCallingRfp $callingRfp)
    {
        if(!$callingRfp->isCallingRFpStillOpen())
        {
            return Redirect::route('consultant.management.consultant.calling.rfp.index');
        }
        
        $user = \Confide::user();

        $rfpRevision = $callingRfp->consultantManagementRfpRevision;
        $vendorCategoryRfp = $rfpRevision->consultantManagementVendorCategoryRfp;
        $consultantManagementContract = $vendorCategoryRfp->consultantManagementContract;

        $consultantRfp = ConsultantManagementConsultantRfp::where('consultant_management_rfp_revision_id', '=', $rfpRevision->id)
        ->where('company_id', '=', $user->company_id)
        ->first();

        return View::make('consultant_management.consultant.calling_rfp.show', compact('callingRfp', 'vendorCategoryRfp', 'consultantManagementContract', 'consultantRfp', 'user'));
    }

    public function proposedFeeStore()
    {
        $this->consultantRfpProposedFeeForm->validate(Input::all());

        $user   = \Confide::user();
        $inputs = Input::all();

        $consultantManagementSubsidiary  = ConsultantManagementSubsidiary::findOrFail((int)$inputs['consultant_management_subsidiary_id']);

        $callingRfp  = ConsultantManagementCallingRfp::findOrFail((int)$inputs['id']);
        $rfpRevision = $callingRfp->consultantManagementRfpRevision;

        $consultantRfp = ConsultantManagementConsultantRfp::where('consultant_management_rfp_revision_id', '=', $rfpRevision->id)
        ->where('company_id', '=', $user->company_id)
        ->first();

        if(!$consultantRfp)
        {
            $consultantRfp = new ConsultantManagementConsultantRfp();

            $consultantRfp->consultant_management_rfp_revision_id = $rfpRevision->id;
            $consultantRfp->company_id = $user->company_id;
            $consultantRfp->created_by = $user->id;
            $consultantRfp->updated_by = $user->id;

            $consultantRfp->save();
        }

        $consultantRfpProposedFee = ConsultantManagementConsultantRfpProposedFee::where('consultant_management_consultant_rfp_id', '=', $consultantRfp->id)
        ->where('consultant_management_subsidiary_id', '=', $consultantManagementSubsidiary->id)
        ->first();
        
        if(!$consultantRfpProposedFee)
        {
            $consultantRfpProposedFee = new ConsultantManagementConsultantRfpProposedFee();

            $consultantRfpProposedFee->consultant_management_consultant_rfp_id = $consultantRfp->id;
            $consultantRfpProposedFee->consultant_management_subsidiary_id = $consultantManagementSubsidiary->id;
            $consultantRfpProposedFee->created_by = $user->id;
        }

        $consultantRfpProposedFee->proposed_fee_amount = $inputs['proposed_fee_amount'];

        $vendorCategoryRfp = $rfpRevision->consultantManagementVendorCategoryRfp;

        $consultantRfpProposedFee->proposed_fee_percentage = ($vendorCategoryRfp->cost_type == ConsultantManagementVendorCategoryRfp::COST_TYPE_LUMP_SUM_COST) ? 0 : $inputs['proposed_fee_percentage'];
        
        $consultantRfpProposedFee->updated_by = $user->id;

        $consultantRfpProposedFee->save();

        \Flash::success(trans('forms.saved'));

        return Redirect::route('consultant.management.consultant.calling.rfp.show', [$callingRfp->id]);
    }

    public function commonInfoStore()
    {
        $this->consultantRfpCommonInfoForm->validate(Input::all());

        $user   = \Confide::user();
        $inputs = Input::all();

        $callingRfp  = ConsultantManagementCallingRfp::findOrFail((int)$inputs['id']);
        $rfpRevision = $callingRfp->consultantManagementRfpRevision;

        $consultantRfp = ConsultantManagementConsultantRfp::where('consultant_management_rfp_revision_id', '=', $rfpRevision->id)
        ->where('company_id', '=', $user->company_id)
        ->first();

        if(!$consultantRfp)
        {
            $consultantRfp = new ConsultantManagementConsultantRfp();

            $consultantRfp->consultant_management_rfp_revision_id = $rfpRevision->id;
            $consultantRfp->company_id = $user->company_id;
            $consultantRfp->created_by = $user->id;
            $consultantRfp->updated_by = $user->id;

            $consultantRfp->save();
        }

        $commonInformation = $consultantRfp->commonInformation;

        if(!$commonInformation)
        {
            $commonInformation = new ConsultantManagementConsultantRfpCommonInformation();

            $commonInformation->consultant_management_consultant_rfp_id = $consultantRfp->id;
            $commonInformation->created_by = $user->id;
            $commonInformation->created_at = date('Y-m-d H:i:s');
        }

        $commonInformation->name_in_loa    = trim($inputs['name_in_loa']);
        $commonInformation->remarks        = trim($inputs['remarks']);
        $commonInformation->contact_name   = trim($inputs['contact_name']);
        $commonInformation->contact_number = trim($inputs['contact_number']);
        $commonInformation->contact_email  = trim($inputs['contact_email']);
        $commonInformation->updated_by     = $user->id;
        $commonInformation->updated_at     = date('Y-m-d H:i:s');

        $commonInformation->save();

        \Flash::success(trans('forms.saved'));

        return Redirect::route('consultant.management.consultant.calling.rfp.show', [$callingRfp->id]);
    }

    public function awardedRfpIndex()
    {
        return View::make('consultant_management.consultant.awarded_rfp.index');
    }

    public function awardedRfpList()
    {
        $user = \Confide::user();

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = ConsultantManagementContract::select("consultant_management_calling_rfp.id AS id", "consultant_management_consultant_rfp.id AS consultant_rfp_id",
        "consultant_management_contracts.title AS title", "consultant_management_contracts.reference_no AS reference_no",
        "vendor_categories.name AS rfp_name", "consultant_management_calling_rfp.closing_rfp_date",
        "countries.country AS country_name", "states.name AS state_name", "consultant_management_contracts.created_at AS created_at")
        ->join('consultant_management_vendor_categories_rfp', 'consultant_management_vendor_categories_rfp.consultant_management_contract_id', '=', 'consultant_management_contracts.id')
        ->join('vendor_categories', 'vendor_categories.id', '=', 'consultant_management_vendor_categories_rfp.vendor_category_id')
        ->join(\DB::raw("(SELECT MAX(rev.revision) AS revision, rev.vendor_category_rfp_id
        FROM consultant_management_rfp_revisions rev
        JOIN consultant_management_calling_rfp crfp ON rev.id = crfp.consultant_management_rfp_revision_id
        JOIN consultant_management_calling_rfp_companies crfpc ON crfpc.consultant_management_calling_rfp_id = crfp.id
        WHERE crfpc.company_id = ".$user->company_id." AND crfpc.status = ".ConsultantManagementCallingRfpCompany::STATUS_YES."
        AND crfp.status = ".ConsultantManagementCallingRfp::STATUS_APPROVED."
        GROUP BY rev.vendor_category_rfp_id) maxrev"), 'maxrev.vendor_category_rfp_id', '=', 'consultant_management_vendor_categories_rfp.id')
        ->join('consultant_management_rfp_revisions', function($join){
            $join->on('consultant_management_rfp_revisions.vendor_category_rfp_id', '=', 'consultant_management_vendor_categories_rfp.id');
            $join->on('consultant_management_rfp_revisions.revision','=', 'maxrev.revision');
        })
        ->join('consultant_management_letter_of_awards', 'consultant_management_letter_of_awards.vendor_category_rfp_id', '=', 'consultant_management_rfp_revisions.vendor_category_rfp_id')
        ->join('consultant_management_calling_rfp', 'consultant_management_calling_rfp.consultant_management_rfp_revision_id', '=', 'consultant_management_rfp_revisions.id')
        ->join('consultant_management_calling_rfp_companies', 'consultant_management_calling_rfp_companies.consultant_management_calling_rfp_id', '=', 'consultant_management_calling_rfp.id')
        ->join('consultant_management_consultant_rfp', 'consultant_management_consultant_rfp.consultant_management_rfp_revision_id', '=', 'consultant_management_calling_rfp.consultant_management_rfp_revision_id')
        ->join('countries', 'consultant_management_contracts.country_id', '=', 'countries.id')
        ->join('states', 'consultant_management_contracts.state_id', '=', 'states.id')
        ->where('consultant_management_calling_rfp_companies.company_id', '=', $user->company_id)
        ->where('consultant_management_calling_rfp_companies.status', '=', ConsultantManagementCallingRfpCompany::STATUS_YES)
        ->where('consultant_management_calling_rfp.status', '=', ConsultantManagementCallingRfp::STATUS_APPROVED)
        ->where('consultant_management_letter_of_awards.status', '=', LetterOfAward::STATUS_APPROVED)
        ->where('consultant_management_consultant_rfp.company_id', '=', $user->company_id)
        ->whereRaw("consultant_management_consultant_rfp.awarded IS TRUE ");

        //tabulator filters
        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(!isset($filters['value']) || is_array($filters['value'])) continue;

                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'title':
                        if(strlen($val) > 0)
                        {
                            $model->where('consultant_management_contracts.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference_no':
                        if(strlen($val) > 0)
                        {
                            $model->where('consultant_management_contracts.reference_no', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'rfp_name':
                        if(strlen($val) > 0)
                        {
                            $model->where('vendor_categories.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->groupBy(\DB::raw('consultant_management_contracts.id, vendor_categories.id, consultant_management_calling_rfp.id, consultant_management_consultant_rfp.id, countries.id, states.id'))
        ->orderBy('consultant_management_contracts.created_at', 'desc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];
        
        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'                => $record->id,
                'consultant_rfp_id' => $record->consultant_rfp_id,
                'counter'           => $counter,
                'title'             => trim($record->title),
                'rfp_name'          => trim($record->rfp_name),
                'reference_no'      => trim($record->reference_no),
                'country'           => trim($record->country_name),
                'state'             => trim($record->state_name),
                'created_at'        => Carbon::parse($record->created_at)->format('d/m/Y'),
                'closing_date'      => Carbon::parse($record->closing_rfp_date)->format('d/m/Y H:i:s'),
                'route:show'        => route('consultant.management.consultant.awarded.rfp.show', [$record->consultant_rfp_id])
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function awardedRfpShow($consultantManagementConsultantRfpId)
    {
        $consultantRfp                = ConsultantManagementConsultantRfp::findOrFail((int)$consultantManagementConsultantRfpId);
        $rfpRevision                  = $consultantRfp->consultantManagementRfpRevision;
        $vendorCategoryRfp            = $rfpRevision->consultantManagementVendorCategoryRfp;
        $callingRfp                   = $rfpRevision->callingRfp;
        $consultantManagementContract = $vendorCategoryRfp->consultantManagementContract;
        $user                         = \Confide::user();

        if($consultantRfp->company_id != $user->company_id || !$consultantRfp->awarded)
        {
            return Redirect::route('consultant.management.consultant.awarded.rfp.index');
        }

        return View::make('consultant_management.consultant.awarded_rfp.show', compact('consultantRfp', 'rfpRevision', 'vendorCategoryRfp', 'callingRfp', 'consultantManagementContract', 'user'));
    }

    public function printLetterOfAward($consultantManagementConsultantRfpId)
    {
        $consultantRfp     = ConsultantManagementConsultantRfp::findOrFail((int)$consultantManagementConsultantRfpId);
        $rfpRevision       = $consultantRfp->consultantManagementRfpRevision;
        $vendorCategoryRfp = $rfpRevision->consultantManagementVendorCategoryRfp;
        $letterOfAward     = $vendorCategoryRfp->letterOfAward;
        $user              = \Confide::user();

        if($consultantRfp->company_id != $user->company_id || !$consultantRfp->awarded || $letterOfAward->status != LetterOfAward::STATUS_APPROVED)
        {
            return Redirect::route('consultant.management.consultant.awarded.rfp.index');
        }

        return $this->generatePdf($letterOfAward);
    }

    protected function generatePdf($letterOfAward)
    {
        $curentClauseNumber = 1;
        $numberingString = '';
        $clauseHtml = '';
        foreach ($letterOfAward->getStructuredClauses() as $clause)
        {
            $clauseHtml.= $this->renderClauses($clause, $curentClauseNumber, $numberingString);
            
            if($clause['displayNumbering'])
            {
                $curentClauseNumber++;
            }
        }

        $data = [
            'signatory'     => $letterOfAward->signatory,
            'clauseHtml'    => $clauseHtml,
            'printSettings' => [
                'clause_font_size' => 15,
            ]
        ];

        $pdfHelper = new PdfHelper('consultant_management.letter_of_award.print.layout', $data);

        $headerView = View::make('consultant_management.letter_of_award.print.header_layout_style', [
            'fontSize'    => 15,
            'letterhead'  => $letterOfAward->letterhead,
            'referenceNo' => ($letterOfAward instanceof LetterOfAward) ? $letterOfAward->reference_number : null,
            'watermark'   => null
        ]);

        $headerHeightInPixels = 50;
        $marginTop            = 15 + $headerHeightInPixels / 4;
        $marginBottom         = 20;
        $marginLeft           = 15;
        $marginRight          = 15;

        $marginTopOption    = ' --margin-top ' . $marginTop;
        $marginBottomOption = ' --margin-bottom ' . $marginBottom;
        $marginLeftOption   = ' --margin-left ' . $marginLeft;
        $marginRightOption  = ' --margin-right ' . $marginRight;

        $headerSpacing = ($letterOfAward instanceof LetterOfAward) ? 3 : 5;//make space for LOA ref no. Template does not have ref no
        $headerOptions = ' --header-spacing ' . $headerSpacing;

        $footerOptions = ' --footer-font-size 10 --footer-right "Page [page] of [topage]"';

        $headerView = PdfHelper::removeBreaksFromHtml($headerView);

        $pdfHelper->setHeaderHtml($headerView);
        $pdfHelper->setOptions(' --encoding utf-8  --disable-smart-shrinking ' . $marginTopOption . $marginBottomOption . $marginRightOption . $marginLeftOption . $headerOptions . $footerOptions);

        return $pdfHelper->printPDF();
    }

    public function questionnaireIndex()
    {
        return View::make('consultant_management.consultant.questionnaires.index');
    }

    public function questionnaireRfpList()
    {
        $user = \Confide::user();

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = ConsultantManagementContract::select("consultant_management_consultant_questionnaires.id AS id", "consultant_management_contracts.title AS title",
        "consultant_management_contracts.reference_no AS reference_no", "vendor_categories.name AS rfp_name", "consultant_management_calling_rfp.closing_rfp_date",
        "countries.country AS country_name", "states.name AS state_name", "consultant_management_contracts.created_at AS created_at",  "consultant_management_consultant_questionnaires.published_date AS questionnaire_published_date")
        ->join('consultant_management_vendor_categories_rfp', 'consultant_management_vendor_categories_rfp.consultant_management_contract_id', '=', 'consultant_management_contracts.id')
        ->join('vendor_categories', 'vendor_categories.id', '=', 'consultant_management_vendor_categories_rfp.vendor_category_id')
        ->join(\DB::raw("(SELECT MAX(rev.revision) AS revision, rev.vendor_category_rfp_id
        FROM consultant_management_rfp_revisions rev
        JOIN consultant_management_calling_rfp crfp ON rev.id = crfp.consultant_management_rfp_revision_id
        JOIN consultant_management_calling_rfp_companies crfpc ON crfpc.consultant_management_calling_rfp_id = crfp.id
        WHERE crfpc.company_id = ".$user->company_id." AND crfpc.status = ".ConsultantManagementCallingRfpCompany::STATUS_YES."
        AND crfp.status = ".ConsultantManagementCallingRfp::STATUS_APPROVED."
        GROUP BY rev.vendor_category_rfp_id) maxrev"), 'maxrev.vendor_category_rfp_id', '=', 'consultant_management_vendor_categories_rfp.id')
        ->join('consultant_management_rfp_revisions', function($join){
            $join->on('consultant_management_rfp_revisions.vendor_category_rfp_id', '=', 'consultant_management_vendor_categories_rfp.id');
            $join->on('consultant_management_rfp_revisions.revision','=', 'maxrev.revision');
        })
        ->join('consultant_management_calling_rfp', 'consultant_management_calling_rfp.consultant_management_rfp_revision_id', '=', 'consultant_management_rfp_revisions.id')
        ->join('consultant_management_calling_rfp_companies', 'consultant_management_calling_rfp_companies.consultant_management_calling_rfp_id', '=', 'consultant_management_calling_rfp.id')
        ->join('consultant_management_consultant_questionnaires', function($join){
            $join->on('consultant_management_consultant_questionnaires.vendor_category_rfp_id', '=', 'consultant_management_vendor_categories_rfp.id');
            $join->on('consultant_management_consultant_questionnaires.company_id','=', 'consultant_management_calling_rfp_companies.company_id');
        })
        ->join('countries', 'consultant_management_contracts.country_id', '=', 'countries.id')
        ->join('states', 'consultant_management_contracts.state_id', '=', 'states.id')
        ->where('consultant_management_calling_rfp_companies.company_id', '=', $user->company_id)
        ->where('consultant_management_calling_rfp_companies.status', '=', ConsultantManagementCallingRfpCompany::STATUS_YES)
        ->where('consultant_management_calling_rfp.status', '=', ConsultantManagementCallingRfp::STATUS_APPROVED)
        ->where('consultant_management_consultant_questionnaires.status', '=', ConsultantManagementConsultantQuestionnaire::STATUS_PUBLISHED);

        //tabulator filters
        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(!isset($filters['value']) || is_array($filters['value'])) continue;

                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'title':
                        if(strlen($val) > 0)
                        {
                            $model->where('consultant_management_contracts.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference_no':
                        if(strlen($val) > 0)
                        {
                            $model->where('consultant_management_contracts.reference_no', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'rfp_name':
                        if(strlen($val) > 0)
                        {
                            $model->where('vendor_categories.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->groupBy(\DB::raw('consultant_management_contracts.id, vendor_categories.id, consultant_management_consultant_questionnaires.id, consultant_management_calling_rfp.id, countries.id, states.id'))
        ->orderBy('consultant_management_contracts.created_at', 'desc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];
        
        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'                  => $record->id,
                'counter'             => $counter,
                'title'               => trim($record->title),
                'rfp_name'            => trim($record->rfp_name),
                'reference_no'        => trim($record->reference_no),
                'country'             => trim($record->country_name),
                'state'               => trim($record->state_name),
                'created_at'          => Carbon::parse($record->created_at)->format('d/m/Y'),
                'closing_date'        => Carbon::parse($record->closing_rfp_date)->format('d/m/Y H:i:s'),
                'questionnaire_date'  => ($record->questionnaire_published_date) ? Carbon::parse($record->questionnaire_published_date)->format('d/m/Y H:i:s') : '-',
                'route:show'          => route('consultant.management.consultant.rfp.questionnaire.show', [$record->id])
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function questionnaireShow($questionnaireId)
    {
        $user = \Confide::user();

        $questionnaires = ConsultantManagementConsultantQuestionnaire::where('id', '=', $questionnaireId)
        ->where('company_id', '=', $user->company_id)
        ->where('status', '=', ConsultantManagementConsultantQuestionnaire::STATUS_PUBLISHED)
        ->first();//just to make sure non company user cannot view questionnaires

        if(!$questionnaires)
        {
            return Redirect::route('consultant.management.consultant.rfp.questionnaire.index');
        }

        $consultantManagementContract = $questionnaires->consultantManagementVendorCategoryRfp->consultantManagementContract;

        return View::make('consultant_management.consultant.questionnaires.show', compact('questionnaires', 'consultantManagementContract', 'user'));
    }

    public function questionnaireReply()
    {
        $user           = \Confide::user();
        $request        = Request::instance();
        $questionnaires = ConsultantManagementConsultantQuestionnaire::where('id', '=', $request->get('qid'))
        ->where('company_id', '=', $user->company_id)
        ->where('status', '=', ConsultantManagementConsultantQuestionnaire::STATUS_PUBLISHED)
        ->first();//just to make sure non company user cannot view questionnaires

        if($request->get('type') == 'general')
        {
            $question = ConsultantManagementQuestionnaire::findOrFail($request->get('id'));
        }
        elseif($request->get('type') == 'rfp')
        {
            $question = ConsultantManagementRfpQuestionnaire::findOrFail($request->get('id'));
        }

        if(!$questionnaires)
        {
            return Response::json([
                'success' => false
            ]);
        }
        
        try
        {
            $reply = null;

            if($request->get('type') == 'general')
            {
                ConsultantManagementConsultantQuestionnaireReply::where('consultant_management_questionnaire_id', '=', $question->id)
                ->where('consultant_management_consultant_questionnaire_id', '=', $questionnaires->id)
                ->delete();

                switch($question->type)
                {
                    case ConsultantManagementQuestionnaire::TYPE_TEXT:
                        $reply = new ConsultantManagementConsultantQuestionnaireReply;
                        $reply->consultant_management_questionnaire_id = $question->id;
                        $reply->consultant_management_consultant_questionnaire_id = $questionnaires->id;
                        $reply->text = trim($request->get('text'));
                        $reply->created_by = $user->id;
                        $reply->updated_by = $user->id;
                        
                        $reply->save();

                        break;
                    case ConsultantManagementQuestionnaire::TYPE_MULTI_SELECT:
                        foreach($request->get('options') as $optionId)
                        {
                            $reply = new ConsultantManagementConsultantQuestionnaireReply;
                            $reply->consultant_management_questionnaire_id = $question->id;
                            $reply->consultant_management_consultant_questionnaire_id = $questionnaires->id;
                            $reply->consultant_management_questionnaire_option_id = $optionId;
                            $reply->created_by = $user->id;
                            $reply->updated_by = $user->id;

                            $reply->save();
                        }
                        break;
                    case ConsultantManagementQuestionnaire::TYPE_SINGLE_SELECT:
                        if(!empty($request->get('options')))
                        {
                            $reply = new ConsultantManagementConsultantQuestionnaireReply;
                            $reply->consultant_management_questionnaire_id = $question->id;
                            $reply->consultant_management_consultant_questionnaire_id = $questionnaires->id;
                            $reply->consultant_management_questionnaire_option_id = (int)$request->get('options');
                            $reply->created_by = $user->id;
                            $reply->updated_by = $user->id;

                            $reply->save();
                        }
                        break;
                }
            }
            elseif($request->get('type') == 'rfp')
            {
                ConsultantManagementConsultantRfpQuestionnaireReply::where('consultant_management_rfp_questionnaire_id', '=', $question->id)
                ->where('consultant_management_consultant_questionnaire_id', '=', $questionnaires->id)
                ->delete();

                switch($question->type)
                {
                    case ConsultantManagementRfpQuestionnaire::TYPE_TEXT:
                        $reply = new ConsultantManagementConsultantRfpQuestionnaireReply;
                        $reply->consultant_management_rfp_questionnaire_id = $question->id;
                        $reply->consultant_management_consultant_questionnaire_id = $questionnaires->id;
                        $reply->text = trim($request->get('text'));
                        $reply->created_by = $user->id;
                        $reply->updated_by = $user->id;
                        
                        $reply->save();

                        break;
                    case ConsultantManagementRfpQuestionnaire::TYPE_MULTI_SELECT:
                        foreach($request->get('options') as $optionId)
                        {
                            $reply = new ConsultantManagementConsultantRfpQuestionnaireReply;
                            $reply->consultant_management_rfp_questionnaire_id = $question->id;
                            $reply->consultant_management_consultant_questionnaire_id = $questionnaires->id;
                            $reply->consultant_management_rfp_questionnaire_option_id = $optionId;
                            $reply->created_by = $user->id;
                            $reply->updated_by = $user->id;

                            $reply->save();
                        }
                        break;
                    case ConsultantManagementRfpQuestionnaire::TYPE_SINGLE_SELECT:
                        if(!empty($request->get('options')))
                        {
                            $reply = new ConsultantManagementConsultantRfpQuestionnaireReply;
                            $reply->consultant_management_rfp_questionnaire_id = $question->id;
                            $reply->consultant_management_consultant_questionnaire_id = $questionnaires->id;
                            $reply->consultant_management_rfp_questionnaire_option_id = (int)$request->get('options');
                            $reply->created_by = $user->id;
                            $reply->updated_by = $user->id;

                            $reply->save();
                        }
                        break;
                }
            }

            $success = true;
        }
        catch(\Exception $e)
        {
            $reply = null;
            $success = false;
        }
        

        return Response::json([
            'success' => $success,
            'submitted_date' => ($reply) ? Carbon::parse($reply->created_at)->format('d/m/Y H:i:s') : null
        ]);
    }

    public function attachmentList($questionId)
    {
        $user    = \Confide::user();
        $request = Request::instance();

        $consultantQuestionnaire = ConsultantManagementConsultantQuestionnaire::where('id', '=', $request->get('qid'))
        ->where('company_id', '=', $user->company_id)
        ->where('status', '=', ConsultantManagementConsultantQuestionnaire::STATUS_PUBLISHED)
        ->first();//just to make sure non company user cannot view questionnaires

        if(!$consultantQuestionnaire)
        {
            return Response::json([]);
        }

        $question = null;

        if($request->get('type')=='general')
        {
            $question = ConsultantManagementQuestionnaire::findOrFail($questionId);

            $model = ConsultantManagementConsultantReplyAttachment::select('uploads.id AS id', 'uploads.filename', 'uploads.extension', 'uploads.created_at AS uploaded_at',
            'consultant_management_consultant_reply_attachments.id AS reply_id')
            ->join('object_fields', 'object_fields.object_id', '=', 'consultant_management_consultant_reply_attachments.id')
            ->join('module_uploaded_files', 'module_uploaded_files.uploadable_id', '=', 'object_fields.id')
            ->join('uploads', 'uploads.id', '=', 'module_uploaded_files.upload_id')
            ->where('object_fields.object_type', '=', 'PCK\ConsultantManagement\ConsultantManagementConsultantReplyAttachment')
            ->where('module_uploaded_files.uploadable_type', '=', 'PCK\ObjectField\ObjectField')
            ->where('consultant_management_consultant_reply_attachments.consultant_management_questionnaire_id', '=', $question->id)
            ->where('consultant_management_consultant_reply_attachments.consultant_management_consultant_questionnaire_id', '=', $consultantQuestionnaire->id);
        }
        elseif($request->get('type')=='rfp')
        {
            $question = ConsultantManagementRfpQuestionnaire::findOrFail($questionId);

            $model = ConsultantManagementConsultantRfpReplyAttachment::select('uploads.id', 'uploads.filename', 'uploads.extension', 'uploads.created_at',
            'consultant_management_consultant_rfp_reply_attachments.id AS reply_id')
            ->join('object_fields', 'object_fields.object_id', '=', 'consultant_management_consultant_rfp_reply_attachments.id')
            ->join('module_uploaded_files', 'module_uploaded_files.uploadable_id', '=', 'object_fields.id')
            ->join('uploads', 'uploads.id', '=', 'module_uploaded_files.upload_id')
            ->where('object_fields.object_type', '=', 'PCK\ConsultantManagement\ConsultantManagementConsultantRfpReplyAttachment')
            ->where('module_uploaded_files.uploadable_type', '=', 'PCK\ObjectField\ObjectField')
            ->where('consultant_management_consultant_rfp_reply_attachments.consultant_management_rfp_questionnaire_id', '=', $question->id)
            ->where('consultant_management_consultant_rfp_reply_attachments.consultant_management_consultant_questionnaire_id', '=', $consultantQuestionnaire->id);
        }

        $uploadedFiles = $model->orderBy('uploads.filename', 'asc')->get();

        $attachments = [];
        
        foreach($uploadedFiles as $uploadedFile)
        {
            $attachments[] = [
                'id'             => $uploadedFile->id,
                'title'          => trim($uploadedFile->filename),
                'type'           => 'file',
                'extension'      => $uploadedFile->extension,
                'deletable'      => $uploadedFile->deletable(),
                'uploaded_at'    => Carbon::parse($uploadedFile->uploaded_at)->format('d/m/Y H:i:s'),
                'route:download' => route('consultant.management.consultant.rfp.questionnaire.attachments.download', [$uploadedFile->id]),
                'route:delete'   => route('consultant.management.consultant.rfp.questionnaire.attachments.delete', [$uploadedFile->id])
            ];
        }

        return Response::json($attachments);
    }

    public function attachmentUpload()
    {
        $request = Request::instance();
        $user    = \Confide::user();
        
        $uploadedFiles = $request->get('uploaded_files');

        $success = false;

        $consultantQuestionnaire = ConsultantManagementConsultantQuestionnaire::where('id', '=', $request->get('qid'))
        ->where('company_id', '=', $user->company_id)
        ->where('status', '=', ConsultantManagementConsultantQuestionnaire::STATUS_PUBLISHED)
        ->first();//just to make sure non company user cannot view questionnaires

        if(!$consultantQuestionnaire)
        {
            return Response::json([
                'success' => false
            ]);
        }

        $reply = null;

        try
        {
            if(is_array($uploadedFiles) && !empty($uploadedFiles))
            {
                if($request->get('type') == 'general')
                {
                    $question = ConsultantManagementQuestionnaire::findOrFail($request->get('id'));

                    $reply = ConsultantManagementConsultantQuestionnaireReply::where('consultant_management_questionnaire_id', '=', $question->id)
                    ->where('consultant_management_consultant_questionnaire_id', '=', $consultantQuestionnaire->id)
                    ->first();

                    if(!$reply)
                    {
                        $reply = new ConsultantManagementConsultantQuestionnaireReply;
                        $reply->consultant_management_questionnaire_id = $question->id;
                        $reply->consultant_management_consultant_questionnaire_id = $consultantQuestionnaire->id;
                        $reply->created_by = $user->id;
                        $reply->updated_by = $user->id;

                        $reply->save();
                    }

                    $replyAttachment = ConsultantManagementConsultantReplyAttachment::where('consultant_management_questionnaire_id', '=', $question->id)
                    ->where('consultant_management_consultant_questionnaire_id', '=', $consultantQuestionnaire->id)
                    ->first();

                    if(!$replyAttachment)
                    {
                        $replyAttachment = new ConsultantManagementConsultantReplyAttachment;
                        $replyAttachment->consultant_management_questionnaire_id = $question->id;
                        $replyAttachment->consultant_management_consultant_questionnaire_id = $consultantQuestionnaire->id;
                        $replyAttachment->created_by = $user->id;
                        $replyAttachment->updated_by = $user->id;

                        $replyAttachment->save();
                    }
                }
                elseif($request->get('type') == 'rfp')
                {
                    $question = ConsultantManagementRfpQuestionnaire::findOrFail($request->get('id'));
                    
                    $reply = ConsultantManagementConsultantRfpQuestionnaireReply::where('consultant_management_rfp_questionnaire_id', '=', $question->id)
                    ->where('consultant_management_consultant_questionnaire_id', '=', $consultantQuestionnaire->id)
                    ->first();

                    if(!$reply)
                    {
                        $reply = new ConsultantManagementConsultantRfpQuestionnaireReply;
                        $reply->consultant_management_rfp_questionnaire_id = $question->id;
                        $reply->consultant_management_consultant_questionnaire_id = $consultantQuestionnaire->id;
                        $reply->created_by = $user->id;
                        $reply->updated_by = $user->id;

                        $reply->save();
                    }

                    $replyAttachment = ConsultantManagementConsultantRfpReplyAttachment::where('consultant_management_rfp_questionnaire_id', '=', $question->id)
                    ->where('consultant_management_consultant_questionnaire_id', '=', $consultantQuestionnaire->id)
                    ->first();

                    if(!$replyAttachment)
                    {
                        $replyAttachment = new ConsultantManagementConsultantRfpReplyAttachment;
                        $replyAttachment->consultant_management_rfp_questionnaire_id = $question->id;
                        $replyAttachment->consultant_management_consultant_questionnaire_id = $consultantQuestionnaire->id;
                        $replyAttachment->created_by = $user->id;
                        $replyAttachment->updated_by = $user->id;

                        $replyAttachment->save();
                    }
                }

                $object = ObjectField::findOrCreateNew($replyAttachment, $replyAttachment->getTable());

                foreach($uploadedFiles as $uploadId)
                {
                    \PCK\ModuleUploadedFiles\ModuleUploadedFile::create(array(
                        'upload_id' => $uploadId,
                        'uploadable_id' => $object->id,
                        'uploadable_type' => get_class($object)
                    ));
                }
            }

            $success = true;
        }
        catch (\Exception $e)
        {
            $success = false;
        }

        return [
            'success' => $success,
            'submitted_at' => ($reply) ? Carbon::parse($reply->created_at)->format('d/m/Y H:i:s') : '-',
        ];
    }

    public function attachmentDownload($uploadId)
    {
        $request = Request::instance();
        $user    = \Confide::user();
        $upload  = Upload::findOrFail($uploadId);

        $filepath = base_path().DIRECTORY_SEPARATOR.$upload->path.DIRECTORY_SEPARATOR.$upload->filename;

        return \PCK\Helpers\Files::download($filepath, $upload->filename);
    }

    public function attachmentDelete($uploadId)
    {
        $request = Request::instance();
        $user    = \Confide::user();
        $upload  = Upload::findOrFail($uploadId);

        $consultantQuestionnaire = ConsultantManagementConsultantQuestionnaire::where('id', '=', $request->get('qid'))
        ->where('company_id', '=', $user->company_id)
        ->where('status', '=', ConsultantManagementConsultantQuestionnaire::STATUS_PUBLISHED)
        ->first();//just to make sure non company user cannot view questionnaires

        if($request->get('type') == 'general')
        {
            $question = ConsultantManagementQuestionnaire::findOrFail($request->get('id'));
        }
        elseif($request->get('type') == 'rfp')
        {
            $question = ConsultantManagementRfpQuestionnaire::findOrFail($request->get('id'));
        }

        $success = false;

        if($question && $consultantQuestionnaire)
        {
            try
            {
                $upload->delete();

                $success = true;
            }
            catch(\Exception $e)
            {
                $success = false;
            }
        }

        return [
            'success' => $success
        ];
    }

    public function questionnaireNotify()
    {
        $user           = \Confide::user();
        $request        = Request::instance();
        $questionnaires = ConsultantManagementConsultantQuestionnaire::where('id', '=', $request->get('id'))
        ->where('company_id', '=', $user->company_id)
        ->where('status', '=', ConsultantManagementConsultantQuestionnaire::STATUS_PUBLISHED)
        ->first();//just to make sure non company user cannot view questionnaires

        if(!$questionnaires)
        {
            return Redirect::route('consultant.management.consultant.rfp.questionnaire.index');
        }
        
        $contract = $questionnaires->consultantManagementVendorCategoryRfp->consultantManagementContract;
        
        $recipients = User::select('users.*')
            ->join('consultant_management_user_roles', 'consultant_management_user_roles.user_id', '=', 'users.id')
            ->whereRaw('consultant_management_user_roles.consultant_management_contract_id = '.$contract->id.'
            AND consultant_management_user_roles.role IN ('.ConsultantManagementContract::ROLE_RECOMMENDATION_OF_CONSULTANT.', '.ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT.')
            AND consultant_management_user_roles.editor IS TRUE
            AND users.confirmed IS TRUE
            AND users.account_blocked_status IS FALSE')
            ->get();

        if(!empty($recipients))
        {
            $content = [
                'subject' => "Consultant Management - Questionnare Replies from Consultant (".$user->company->name.")",//need to move this to i10n
                'view' => 'consultant_management.email.consultant_questionnaire_notify',
                'data' => [
                    'developmentPlanningTitle' => $contract->title,
                    'subsidiaryName' => $contract->Subsidiary->name,
                    'vendorCategoryName' => $questionnaires->consultantManagementVendorCategoryRfp->vendorCategory->name,
                    'companyName' => $user->company->name,
                    'submitter' => $user->name,
                    'route' => route('consultant.management.consultant.questionnaire.show', [$questionnaires->vendor_category_rfp_id, $user->company_id])
                ]
            ];
            
            $this->emailNotifier->sendGeneralEmail($content, $recipients);
        }

        \Flash::success("Sucessfully notified PIC about the questionnaire replies");

        return Redirect::route('consultant.management.consultant.rfp.questionnaire.show', [$questionnaires->id]);
    }

    public function userManagementIndex()
    {
        return View::make('consultant_management.consultant.user_management.index');
    }

    public function userManagementUnassignedList()
    {
        $user    = \Confide::user();
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = User::select("users.id", "users.name", "users.email", "users.is_admin");

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(!isset($filters['value']) || is_array($filters['value'])) continue;

                $val = trim($filters['value']);

                $searchStr = '%'.urldecode($val).'%';

                switch(trim(strtolower($filters['field'])))
                {
                    case 'name':
                        if(strlen($val) > 0)
                        {
                            $model->where('users.name', 'ILIKE', $searchStr);
                        }
                        break;
                    case 'email':
                        if(strlen($val) > 0)
                        {
                            $model->where('users.email', 'ILIKE', $searchStr);
                        }
                        break;
                }
            }
        }

       $model->whereRaw("
            NOT EXISTS (
                SELECT 1
                FROM consultant_management_consultant_users cu
                WHERE cu.user_id = users.id
            )
        ");

        $model->where('users.company_id', '=', $user->company_id)
        ->where('users.confirmed', '=', true)
        ->where('users.account_blocked_status', '=', false)
        ->orderBy('users.name', 'asc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];
        
        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'       => $record->id,
                'counter'  => $counter,
                'name'     => $record->name,
                'email'    => $record->email,
                'is_admin' => $record->is_admin
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function userManagementAssignedList()
    {
        $user    = \Confide::user();
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = User::select("users.id", "users.name", "users.email", "users.is_admin")
        ->join('consultant_management_consultant_users', 'consultant_management_consultant_users.user_id', '=', 'users.id');

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(!isset($filters['value']) || is_array($filters['value'])) continue;

                $val = trim($filters['value']);

                $searchStr = '%'.urldecode($val).'%';

                switch(trim(strtolower($filters['field'])))
                {
                    case 'name':
                        if(strlen($val) > 0)
                        {
                            $model->where('users.name', 'ILIKE', $searchStr);
                        }
                        break;
                    case 'email':
                        if(strlen($val) > 0)
                        {
                            $model->where('users.email', 'ILIKE', $searchStr);
                        }
                        break;
                }
            }
        }

        $model->where('users.company_id', '=', $user->company_id)
        ->where('users.confirmed', '=', true)
        ->where('users.account_blocked_status', '=', false)
        ->orderBy('users.name', 'asc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];
        
        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'           => $record->id,
                'counter'      => $counter,
                'name'         => $record->name,
                'email'        => $record->email,
                'is_admin'     => $record->is_admin,
                'route:delete' => route('consultant.management.consultant.user.management.unassign', [$record->id])
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function userManagementAssign()
    {
        $user   = \Confide::user();
        $inputs = Input::all();

        if(array_key_exists('users', $inputs) && !empty($inputs['users']))
        {
            $filteredUserIds = User::selectRaw("DISTINCT users.id")
            ->whereIn('users.id', $inputs['users'])
            ->where('users.company_id', '=', $user->company_id)
            ->where('users.confirmed', '=', true)
            ->where('users.account_blocked_status', '=', false)
            ->lists('id');

            $assignedUserIds = User::select("users.id")
            ->join('consultant_management_consultant_users', 'users.id', '=', 'consultant_management_consultant_users.user_id')
            ->where('users.company_id', '=', $user->company_id)
            ->where('users.confirmed', '=', true)
            ->where('users.account_blocked_status', '=', false)
            ->lists('id');

            $filteredUserIds = (!empty($assignedUserIds)) ? array_diff($filteredUserIds, $assignedUserIds) : $filteredUserIds;

            if(!empty($filteredUserIds))
            {
                $data = [];
                foreach($filteredUserIds as $id)
                {
                    $data[] = [
                        'user_id'    => $id,
                        'is_admin'   => true,
                        'created_by' => $user->id,
                        'updated_by' => $user->id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                }

                ConsultantUser::insert($data);
            }
        }

        return Response::json([
            'success' => true
        ]);
    }

    public function userManagementUnassign($userId)
    {
        $user         = \Confide::user();
        $unassignUser = User::findOrFail($userId);

        if($user->company_id != $unassignUser->company_id)
        {
            Flash::error('Invalid user to be removed');
            return Redirect::back();
        }

        $totalUsers = ConsultantUser::select('consultant_management_consultant_users.id')
        ->join('users', 'users.id', '=', 'consultant_management_consultant_users.user_id')
        ->where('users.company_id', '=', $unassignUser->company_id)
        ->where('users.confirmed', '=', true)
        ->where('users.account_blocked_status', '=', false)
        ->where('users.id', '<>', $unassignUser->id)
        ->count();
        
        if($totalUsers > 0)
        {
            ConsultantUser::where('user_id', '=', $unassignUser->id)->delete();
        }
        else
        {
            Flash::error('At least one user must remains in Consultant Management');
        }

        return Redirect::back();
    }
}