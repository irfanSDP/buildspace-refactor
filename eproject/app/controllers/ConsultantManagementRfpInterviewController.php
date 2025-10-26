<?php
use Carbon\Carbon;

use PCK\ConsultantManagement\ConsultantManagementContract;
use PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp;
use PCK\ConsultantManagement\ConsultantManagementCallingRfp;
use PCK\ConsultantManagement\ConsultantManagementRfpInterview;
use PCK\ConsultantManagement\ConsultantManagementRfpInterviewConsultant;
use PCK\ConsultantManagement\ConsultantManagementRfpInterviewToken;
use PCK\Users\User;
use PCK\Companies\Company;

use PCK\Helpers\Mailer as MailHelper;
use PCK\Notifications\EmailNotifier;

use PCK\Forms\ConsultantManagement\RfpInterviewForm;
use PCK\Forms\ConsultantManagement\RfpInterviewReplyForm;

use PCK\Helpers\DBTransaction;

class ConsultantManagementRfpInterviewController extends \BaseController
{
    private $rfpInterviewForm;
    private $rfpInterviewReplyForm;

    private $emailNotifier;

    public function __construct(RfpInterviewForm $rfpInterviewForm, RfpInterviewReplyForm $rfpInterviewReplyForm, EmailNotifier $emailNotifier)
    {
        $this->rfpInterviewForm = $rfpInterviewForm;
        $this->rfpInterviewReplyForm = $rfpInterviewReplyForm;
        $this->emailNotifier = $emailNotifier;
    }

    public function index(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $callingRfpId)
    {
        $user = \Confide::user();

        $callingRfp = ConsultantManagementCallingRfp::findOrFail((int)$callingRfpId);
        $consultantManagementContract = $vendorCategoryRfp->consultantManagementContract;

        return View::make('consultant_management.rfp_interview.index', compact('vendorCategoryRfp', 'consultantManagementContract', 'callingRfp', 'user'));
    }

    public function list(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $callingRfpId)
    {
        $callingRfp = ConsultantManagementCallingRfp::findOrFail((int)$callingRfpId);

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = ConsultantManagementRfpInterview::select("consultant_management_rfp_interviews.id AS id", "consultant_management_rfp_interviews.title",
        "consultant_management_rfp_interviews.status", "consultant_management_rfp_interviews.created_at",
        "consultant_management_rfp_interviews.details", "consultant_management_rfp_interviews.interview_date")
        ->where('consultant_management_rfp_interviews.vendor_category_rfp_id', '=', $vendorCategoryRfp->id);

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
                            $model->where('consultant_management_rfp_interviews.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('consultant_management_rfp_interviews.created_at', 'desc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];
        
        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'             => $record->id,
                'counter'        => $counter,
                'title'          => trim($record->title),
                'status'         => $record->status,
                'status_txt'     => $record->getStatusText(),
                'details'        => trim($record->details),
                'interview_date' => Carbon::parse($record->interview_date)->format('d-M-Y'),
                'created_at'     => Carbon::parse($record->created_at)->format('d/m/Y'),
                'route:show'     => route('consultant.management.consultant.rfp.interview.show', [$vendorCategoryRfp->id, $callingRfp->id, $record->id]),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function show(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $callingRfpId, $rfpInterviewId)
    {
        $user = \Confide::user();
        $callingRfp = ConsultantManagementCallingRfp::findOrFail((int)$callingRfpId);
        $consultantManagementContract = $vendorCategoryRfp->consultantManagementContract;

        $rfpInterview = ConsultantManagementRfpInterview::findOrFail((int)$rfpInterviewId);

        return View::make('consultant_management.rfp_interview.show', compact('vendorCategoryRfp', 'consultantManagementContract', 'callingRfp', 'user', 'rfpInterview'));
    }

    public function create(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $callingRfpId)
    {
        $user = \Confide::user();

        $callingRfp = ConsultantManagementCallingRfp::findOrFail((int)$callingRfpId);
        $consultantManagementContract = $vendorCategoryRfp->consultantManagementContract;

        $rfpInterview = null;
        $consultants = [];

        return View::make('consultant_management.rfp_interview.edit', compact('vendorCategoryRfp', 'consultantManagementContract', 'callingRfp', 'user', 'rfpInterview', 'consultants'));
    }

    public function edit(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $callingRfpId, $rfpInterviewId)
    {
        $user = \Confide::user();

        $callingRfp = ConsultantManagementCallingRfp::findOrFail((int)$callingRfpId);
        $consultantManagementContract = $vendorCategoryRfp->consultantManagementContract;

        $rfpInterview = ConsultantManagementRfpInterview::findOrFail((int)$rfpInterviewId);

        $consultants = [];

        foreach($rfpInterview->consultants as $consultant)
        {
            $consultants[] = [
                'id' => $consultant->company_id,
                'name' => $consultant->company->name,
                'interview_timestamp' => $consultant->interview_timestamp,
                'remarks' => $consultant->remarks
            ];
        }

        return View::make('consultant_management.rfp_interview.edit', compact('vendorCategoryRfp', 'consultantManagementContract', 'callingRfp', 'user', 'rfpInterview', 'consultants'));
    }

    public function store(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $request = Request::instance();

        $this->rfpInterviewForm->validate($request->all());

        $user  = \Confide::user();
        $callingRfp = ConsultantManagementCallingRfp::findOrFail((int)$request->get('calling_rfp_id'));

        $rfpInterview = ConsultantManagementRfpInterview::find($request->get('id'));

        if(!$rfpInterview)
        {
            $rfpInterview = new ConsultantManagementRfpInterview();

            $rfpInterview->vendor_category_rfp_id = $vendorCategoryRfp->id;
            $rfpInterview->status = ConsultantManagementRfpInterview::STATUS_DRAFT;
            $rfpInterview->created_by = $user->id;
        }

        $rfpInterview->title          = trim($request->get('title'));
        $rfpInterview->details        = trim($request->get('details'));
        $rfpInterview->interview_date = Carbon::parse($request->get('interview_date'))->format('Y-m-d');
        $rfpInterview->updated_by     = $user->id;

        $rfpInterview->save();

        $rfpInterview->consultants()->delete();

        foreach($request->get('consultants') as $consultantData)
        {
            $consultantInterview = new ConsultantManagementRfpInterviewConsultant;

            $consultantInterview->consultant_management_rfp_interview_id = $rfpInterview->id;
            $consultantInterview->company_id          = $consultantData['id'];
            $consultantInterview->interview_timestamp = Carbon::parse($consultantData['interview_timestamp'])->format('Y-m-d H:i:s');
            $consultantInterview->remarks             = trim($consultantData['remarks']);
            $consultantInterview->status              = ConsultantManagementRfpInterviewConsultant::STATUS_UNSET;
            $consultantInterview->created_by          = $user->id;
            $consultantInterview->updated_by          = $user->id;

            $consultantInterview->save();
        }

        \Flash::success(trans('forms.saved'));

        return Redirect::route('consultant.management.consultant.rfp.interview.show', [$vendorCategoryRfp->id, $callingRfp->id, $rfpInterview->id]);
    }

    public function consultantList(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $callingRfpId)
    {
        $callingRfp = ConsultantManagementCallingRfp::findOrFail((int)$callingRfpId);

        $request = Request::instance();

        $query = Company::select("companies.id AS id", "companies.name AS name", "companies.reference_no", "consultant_management_calling_rfp_companies.status")
        ->join('vendors', 'companies.id', '=', 'vendors.company_id')
        ->join('consultant_management_calling_rfp_companies', 'consultant_management_calling_rfp_companies.company_id', '=', 'vendors.company_id')
        ->join('consultant_management_calling_rfp', 'consultant_management_calling_rfp_companies.consultant_management_calling_rfp_id', '=', 'consultant_management_calling_rfp.id')
        ->where('companies.confirmed', '=', true)
        ->where('consultant_management_calling_rfp.id', '=', $callingRfp->id);

        if($request->has('ids') && is_array($request->get('ids')))
        {
            $query->whereNotIn('companies.id', $request->get('ids'));
        }

        $records = $query->orderBy('companies.name', 'asc')
        ->groupBy(\DB::raw('companies.id, consultant_management_calling_rfp_companies.id'))
        ->get();

        $data = [];
        
        foreach($records as $key => $record)
        {
            $data[] = [
                'id'           => $record->id,
                'name'         => trim($record->name),
                'reference_no' => trim($record->reference_no),
                'vendor_code'  => $record->getVendorCode()
            ];
        }

        return Response::json($data);
    }

    public function selectedConsultantList(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $callingRfpId, $rfpInterviewId)
    {
        $callingRfp = ConsultantManagementCallingRfp::findOrFail((int)$callingRfpId);
        $rfpInterview = ConsultantManagementRfpInterview::findOrFail((int)$rfpInterviewId);
        $consultantManagementContract = $vendorCategoryRfp->consultantManagementContract;

        $request = Request::instance();

        $records = Company::select("companies.id AS id", "companies.name", "companies.reference_no", "consultant_management_rfp_interview_consultants.status AS consultant_interview_status",
        "consultant_management_rfp_interview_consultants.id AS consultant_interview_id", "consultant_management_rfp_interview_consultants.interview_timestamp",
        "consultant_management_rfp_interview_consultants.remarks AS consultant_interview_remarks",
        "consultant_management_rfp_interview_consultants.consultant_remarks AS consultant_remarks")
        ->join('consultant_management_rfp_interview_consultants', 'companies.id', '=', 'consultant_management_rfp_interview_consultants.company_id')
        ->join('consultant_management_rfp_interviews', 'consultant_management_rfp_interviews.id', '=', 'consultant_management_rfp_interview_consultants.consultant_management_rfp_interview_id')
        ->where('consultant_management_rfp_interviews.id', '=', $rfpInterview->id)
        ->orderBy('consultant_management_rfp_interview_consultants.interview_timestamp', 'asc')
        ->groupBy(\DB::raw('companies.id, consultant_management_rfp_interview_consultants.id'))
        ->get();

        $data = [];
        
        foreach($records as $key => $record)
        {
            $data[] = [
                'id'                              => $record->id,
                'company_name'                    => trim($record->name),
                'reference_no'                    => trim($record->reference_no),
                'vendor_code'                     => $record->getVendorCode(),
                'consultant_interview_id'         => $record->consultant_interview_id,
                'consultant_interview_status'     => $record->consultant_interview_status,
                'consultant_interview_status_txt' => ConsultantManagementRfpInterviewConsultant::getInterviewStatusText($record->consultant_interview_status),
                'consultant_interview_remarks'    => $record->consultant_interview_remarks,
                'consultant_remarks'              => $record->consultant_remarks,
                'interview_timestamp'             => Carbon::parse($consultantManagementContract->getContractTimeZoneTime($record->interview_timestamp))->format(\Config::get('dates.full_format')),
                'route:resend'                    => route('consultant.management.consultant.rfp.interview.resend', [$vendorCategoryRfp->id, $callingRfp->id, $record->consultant_interview_id]),
            ];
        }

        return Response::json($data);
    }

    public function delete(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $callingRfpId, $rfpInterviewId)
    {
        $callingRfp = ConsultantManagementCallingRfp::findOrFail((int)$callingRfpId);
        $rfpInterview = ConsultantManagementRfpInterview::findOrFail((int)$rfpInterviewId);
        $user = \Confide::user();

        if($rfpInterview->deletable())
        {
            $rfpInterview->delete();

            \Log::info("Delete consultant management rfp interview [interview id: {$rfpInterviewId}]][user id:{$user->id}]");
        }

        return Redirect::route('consultant.management.consultant.rfp.interview.index', [$vendorCategoryRfp->id, $callingRfp->id]);
    }

    public function send(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $request = Request::instance();

        $callingRfp   = ConsultantManagementCallingRfp::findOrFail((int)$request->get('calling_rfp_id'));
        $rfpInterview = ConsultantManagementRfpInterview::findOrFail((int)$request->get('id'));

        try
        {
            $transaction = new DBTransaction();
            $transaction->begin();

            $rfpInterview->status = ConsultantManagementRfpInterview::STATUS_SENT;

            $rfpInterview->save();

            foreach($rfpInterview->consultants as $consultantInterview)
            {
                do {
                    $token = str_random();
                } //check if the token already exists and if it does, try again
                while (ConsultantManagementRfpInterviewToken::where('token', $token)->first());

                $consultantInterviewToken = new ConsultantManagementRfpInterviewToken;
                $consultantInterviewToken->consultant_management_rfp_interview_consultant_id = $consultantInterview->id;
                $consultantInterviewToken->token = $token;

                $consultantInterviewToken->save();
            }

            $transaction->commit();

            foreach($rfpInterview->consultants as $consultantInterview)
            {
                $this->sendEmailToConsultant($consultantInterview);
            }

            $this->sendEmailToRfpUsers($rfpInterview, $callingRfp);

            \Flash::success(trans('forms.saved'));
        }
        catch(\Exception $e)
        {
            $transaction->rollback();
        }

        return Redirect::route('consultant.management.consultant.rfp.interview.show', [$vendorCategoryRfp->id, $callingRfp->id, $rfpInterview->id]);
    }

    public function resend(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $callingRfpId, $rfpInterviewConsultantId)
    {
        $callingRfp = ConsultantManagementCallingRfp::findOrFail((int)$callingRfpId);
        $rfpInterviewConsultant = ConsultantManagementRfpInterviewConsultant::findOrFail((int)$rfpInterviewConsultantId);

        $this->sendEmailToConsultant($rfpInterviewConsultant);
        $this->sendEmailToRfpUsers($rfpInterviewConsultant->consultantManagementRfpInterview, $callingRfp);

        \Flash::success('Email has been successfully sent');

        return Redirect::route('consultant.management.consultant.rfp.interview.show', [$vendorCategoryRfp->id, $callingRfp->id, $rfpInterviewConsultant->consultant_management_rfp_interview_id]);
    }

    private function sendEmailToRfpUsers(ConsultantManagementRfpInterview $rfpInterview, ConsultantManagementCallingRfp $callingRfp)
    {
        $vendorCategoryRfp = $rfpInterview->consultantManagementVendorCategoryRfp;
        $consultantManagementContract = $vendorCategoryRfp->consultantManagementContract;

        $recipients = User::select('users.*')
            ->join('consultant_management_user_roles', 'consultant_management_user_roles.user_id', '=', 'users.id')
            ->whereRaw('consultant_management_user_roles.consultant_management_contract_id = '.$consultantManagementContract->id.'
            AND consultant_management_user_roles.role IN ('.ConsultantManagementContract::ROLE_RECOMMENDATION_OF_CONSULTANT.', '.ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT.')
            AND users.confirmed IS TRUE
            AND users.account_blocked_status IS FALSE')
            ->groupBy('users.id')
            ->get();
        
        if(!empty($recipients))
        {
            $content = [
                'subject' => "Consultant Management - Consultant RFP Interview Invitation (".$consultantManagementContract->Subsidiary->name.")",//need to move this to i10n
                'view' => 'consultant_management.rfp_interview.user_interview_email',
                'data' => [
                    'vendorCategoryName' => $vendorCategoryRfp->vendorCategory->name,
                    'rfpInterview' => $rfpInterview,
                    'interviewLink' => route('consultant.management.consultant.rfp.interview.show', [$vendorCategoryRfp->id, $callingRfp->id, $rfpInterview->id])
                ]
            ];
            
            $this->emailNotifier->sendGeneralEmail($content, $recipients);
        }
    }

    private function sendEmailToConsultant(ConsultantManagementRfpInterviewConsultant $rfpInterviewConsultant)
    {
        $company = $rfpInterviewConsultant->company;
        $rfpInterview = $rfpInterviewConsultant->consultantManagementRfpInterview;
        $vendorCategoryRfp = $rfpInterview->consultantManagementVendorCategoryRfp;
        $consultantManagementContract = $vendorCategoryRfp->consultantManagementContract;

        $interviewTimestamp = Carbon::parse($consultantManagementContract->getContractTimeZoneTime($rfpInterviewConsultant->interview_timestamp))->format('d-M-Y H:i:s');

        $interviewToken = ConsultantManagementRfpInterviewToken::where('consultant_management_rfp_interview_consultant_id', $rfpInterviewConsultant->id)->first();

        if(!$interviewToken)
        {
            return;
        }

        foreach($company->companyAdmin()->get() as $recipient)
        {
            $recipientLocale = $recipient->settings->language->code;
            $subject         = 'RFP Interview Invitation';

            $data['recipientName']          = $recipient->name;
            $data['recipientLocale']        = $recipientLocale;
            $data['companyName']            = $company->name.' ('.$company->reference_no.')';
            $data['vendorCategoryName']     = $vendorCategoryRfp->vendorCategory->name;
            $data['rfpInterviewConsultant'] = $rfpInterviewConsultant;
            $data['rfpInterview']           = $rfpInterview;
            $data['interviewTimestamp']     = $interviewTimestamp;
            $data['link']                   = route('consultant.management.consultant.rfp.interview.reply', [$interviewToken->token, $recipient->email]);

            MailHelper::queue(null, 'consultant_management.rfp_interview.consultant_interview_email', $recipient, $subject, $data);
        }
    }

    public function reply($token, $email)
    {
        $user = User::where('email', '=', trim($email))->first();
        $interviewToken = ConsultantManagementRfpInterviewToken::where('token', $token)->first();
        if (!$interviewToken or !$user or (!$user->is_admin or $interviewToken->consultantManagementRfpInterviewConsultant->company_id != $user->company_id))
        {
            //if the invite doesn't exist do something more graceful than this
            return View::make('errors/404');
        }

        $userLocale  = $user ? $user->settings->language->code : getenv('DEFAULT_LANGUAGE_CODE');
        $rfpInterviewConsultant = $interviewToken->consultantManagementRfpInterviewConsultant;
        $rfpInterview = $rfpInterviewConsultant->consultantManagementRfpInterview;
        $vendorCategoryRfp = $rfpInterview->consultantManagementVendorCategoryRfp;
        $consultantManagementContract = $vendorCategoryRfp->consultantManagementContract;

        $translatedText = null;

        return View::make('consultant_management.rfp_interview.reply', compact('interviewToken', 'rfpInterviewConsultant', 'rfpInterview', 'vendorCategoryRfp', 'consultantManagementContract', 'user', 'translatedText', 'userLocale'));
    }

    public function replyStore()
    {
        $request = Request::instance();

        $this->rfpInterviewReplyForm->validate($request->all());

        $rfpInterviewConsultant = ConsultantManagementRfpInterviewConsultant::findOrFail((int)$request->get('id'));
        $company = Company::findOrFail((int)$request->get('cid'));
        $interviewToken = ConsultantManagementRfpInterviewToken::where('token', $request->get('token'))->first();

        if($rfpInterviewConsultant->company_id != $company->id or !$interviewToken)
        {
            return View::make('errors/404');
        }

        switch((int)$request->get('status'))
        {
            case ConsultantManagementRfpInterviewConsultant::STATUS_ACCEPTED:
                $rfpInterviewConsultant->status = ConsultantManagementRfpInterviewConsultant::STATUS_ACCEPTED;
                break;
            case ConsultantManagementRfpInterviewConsultant::STATUS_DECLINED:
                $rfpInterviewConsultant->status = ConsultantManagementRfpInterviewConsultant::STATUS_DECLINED;
                break;
            default:
                return View::make('errors/404');
        }

        $rfpInterviewConsultant->consultant_remarks = trim($request->get('consultant_remarks'));

        $rfpInterviewConsultant->save();

        $interviewToken->delete();

        \Flash::success(trans('forms.saved'));

        return Redirect::route('consultant.management.consultant.rfp.interview.reply.success', [$rfpInterviewConsultant->id]);
    }

    public function replySuccess($rfpInterviewConsultantId)
    {
        $rfpInterviewConsultant = ConsultantManagementRfpInterviewConsultant::findOrFail((int)$rfpInterviewConsultantId);
        $rfpInterview = $rfpInterviewConsultant->consultantManagementRfpInterview;
        $vendorCategoryRfp = $rfpInterview->consultantManagementVendorCategoryRfp;
        $consultantManagementContract = $vendorCategoryRfp->consultantManagementContract;

        $translatedText = null;
        $userLocale = null;

        return View::make('consultant_management.rfp_interview.success', compact('rfpInterviewConsultant', 'consultantManagementContract', 'translatedText', 'userLocale'));
    }
}