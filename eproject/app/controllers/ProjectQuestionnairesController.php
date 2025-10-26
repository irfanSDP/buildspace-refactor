<?php
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use PCK\Projects\Project;
use PCK\Companies\Company;
use PCK\Users\User;
use PCK\Users\UserRepository;
use PCK\ContractGroups\Types\Role;
use PCK\ContractGroups\ContractGroup;

use PCK\ContractorQuestionnaire\Questionnaire;
use PCK\ContractorQuestionnaire\Question;
use PCK\ContractorQuestionnaire\Option;
use PCK\ContractorQuestionnaire\Reply;
use PCK\ContractorQuestionnaire\ReplyAttachment;

use PCK\Notifications\EmailNotifier;

use PCK\Helpers\ModuleAttachment;
use PCK\ObjectField\ObjectField;
use PCK\Base\Upload;

use PCK\Forms\ContractorQuestionnaire\QuestionForm;

use PCK\Helpers\StringOperations;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Helper\Html;

class ProjectQuestionnairesController extends \BaseController
{
    private $userRepo;
    private $emailNotifier;
    private $questionForm;

    public function __construct(
        UserRepository $userRepo,
        EmailNotifier $emailNotifier,
        QuestionForm $questionForm
    )
    {
        $this->userRepo      = $userRepo;
        $this->emailNotifier = $emailNotifier;
        $this->questionForm  = $questionForm;
    }

    public function index(Project $project)
    {
        return View::make('tenders.questionnaires.index', compact('project'));
    }

    public function contractorList(Project $project)
    {
        $user = \Confide::user();

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = Company::select("companies.id AS id", "companies.name", "companies.reference_no")
        ->join('company_tender_calling_tender_information AS ctcti', 'ctcti.company_id', '=', 'companies.id')
        ->join('tender_calling_tender_information AS tcti', 'ctcti.tender_calling_tender_information_id', '=', 'tcti.id')
        ->where('tcti.tender_id', '=', $project->latestTender->id);

        //tabulator filters
        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(!isset($filters['value']) || is_array($filters['value'])) continue;

                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'name':
                        if(strlen($val) > 0)
                        {
                            $model->where('companies.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference_no':
                        if(strlen($val) > 0)
                        {
                            $model->where('companies.reference_no', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('companies.name', 'asc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $questionnaires = Questionnaire::select('contractor_questionnaires.company_id', 'contractor_questionnaires.status', 'contractor_questionnaires.published_date')
        ->where('contractor_questionnaires.project_id', '=', $project->id)
        ->get();

        $questionnaireByCompanies = [];
        foreach($questionnaires as $questionnaire)
        {
            $questionnaireByCompanies[$questionnaire->company_id] = $questionnaire;
        }

        unset($questionnaires);

        $data = [];
        
        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'                   => $record->id,
                'counter'              => $counter,
                'name'                 => trim($record->name),
                'reference_no'         => $record->reference_no,
                'questionnaire_status' => (array_key_exists($record->id, $questionnaireByCompanies)) ? $questionnaireByCompanies[$record->id]->status : Questionnaire::STATUS_UNPUBLISHED,
                'published_date'       => (array_key_exists($record->id, $questionnaireByCompanies) && $questionnaireByCompanies[$record->id]->status == Questionnaire::STATUS_PUBLISHED) ? Carbon::parse($questionnaireByCompanies[$record->id]->published_date)->format('d/m/Y H:i:s') : '-',
                'route:questionnaire'  => route('projects.questionnaires.show', [$project->id, $record->id])
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function show(Project $project, $companyId)
    {
        $company    = Company::findOrFail((int)$companyId);
        $user       = \Confide::user();
        $verifiers  = $user->getAssignedCompany($project)->getVerifierList($project);
        $isEditable = $user->isEditor($project) && $project->status_id !== Project::STATUS_TYPE_POST_CONTRACT;

        $questionnaire = Questionnaire::where('project_id', '=', $project->id)
        ->where('company_id', '=', $company->id)
        ->first();

        if ($questionnaire) {
            $totalReplies = Reply::leftJoin('contractor_questionnaire_options', function($join){
                    $join->on('contractor_questionnaire_options.contractor_questionnaire_question_id', '=', 'contractor_questionnaire_replies.contractor_questionnaire_question_id');
                    $join->on('contractor_questionnaire_options.id','=', 'contractor_questionnaire_replies.contractor_questionnaire_option_id');
                })
                ->join('contractor_questionnaire_questions', 'contractor_questionnaire_replies.contractor_questionnaire_question_id', '=', 'contractor_questionnaire_questions.id')
                ->where('contractor_questionnaire_questions.contractor_questionnaire_id', '=', $questionnaire->id)
                ->count();
        } else {
            $totalReplies = 0;
        }

        return View::make('tenders.questionnaires.show', compact('project', 'company', 'user', 'verifiers', 'isEditable', 'questionnaire', 'totalReplies'));
    }

    public function contractorQuestionList(Project $project, $companyId)
    {
        $company = Company::findOrFail((int)$companyId);
        $user    = \Confide::user();

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = Question::select("contractor_questionnaire_questions.id AS id", "contractor_questionnaire_questions.question",
        "contractor_questionnaire_questions.type", "contractor_questionnaire_questions.required", "contractor_questionnaire_questions.created_at")
        ->join('contractor_questionnaires', 'contractor_questionnaire_questions.contractor_questionnaire_id', '=', 'contractor_questionnaires.id')
        ->where('contractor_questionnaires.project_id', '=', $project->id)
        ->where('contractor_questionnaires.company_id', '=', $company->id);

        //tabulator filters
        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(!isset($filters['value']) || is_array($filters['value'])) continue;

                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'question':
                        if(strlen($val) > 0)
                        {
                            $model->where('contractor_questionnaire_questions.question', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('contractor_questionnaire_questions.created_at', 'desc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];
        
        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'         => $record->id,
                'counter'    => $counter,
                'question'   => trim($record->question),
                'type'       => $record->type,
                'type_txt'   => $record->getTypeText(),
                'required'   => $record->required,
                'created_at' => Carbon::parse($record->created_at)->format('d/m/Y'),
                'route:show' => route('projects.questionnaires.question.show', [$project->id, $record->id])
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function contractorReplyList(Project $project, $companyId)
    {
        $company = Company::findOrFail((int)$companyId);
        $user    = \Confide::user();

        $questionnaire = Questionnaire::where('project_id', '=', $project->id)
        ->where('company_id', '=', $company->id)
        ->first();

        $records = [];

        if($questionnaire)
        {
            $counter = 1;

            $questions = $questionnaire->questions;

            $replies = Reply::select('contractor_questionnaire_replies.id',
            'contractor_questionnaire_replies.contractor_questionnaire_question_id',
            'contractor_questionnaire_replies.created_at',
            'contractor_questionnaire_replies.text', 'contractor_questionnaire_options.text AS option_text')
            ->leftJoin('contractor_questionnaire_options', function($join){
                $join->on('contractor_questionnaire_options.contractor_questionnaire_question_id', '=', 'contractor_questionnaire_replies.contractor_questionnaire_question_id');
                $join->on('contractor_questionnaire_options.id','=', 'contractor_questionnaire_replies.contractor_questionnaire_option_id');
            })
            ->join('contractor_questionnaire_questions', 'contractor_questionnaire_replies.contractor_questionnaire_question_id', '=', 'contractor_questionnaire_questions.id')
            ->where('contractor_questionnaire_questions.contractor_questionnaire_id', '=', $questionnaire->id)
            ->orderBy('contractor_questionnaire_questions.created_at', 'DESC')
            ->get();

            $attachments = ReplyAttachment::select(\DB::raw('contractor_questionnaire_reply_attachments.contractor_questionnaire_question_id,
            COUNT(uploads.id) AS count'))
            ->join('contractor_questionnaire_questions', 'contractor_questionnaire_reply_attachments.contractor_questionnaire_question_id', '=', 'contractor_questionnaire_questions.id')
            ->join('object_fields', 'object_fields.object_id', '=', 'contractor_questionnaire_reply_attachments.id')
            ->join('module_uploaded_files', 'module_uploaded_files.uploadable_id', '=', 'object_fields.id')
            ->join('uploads', 'uploads.id', '=', 'module_uploaded_files.upload_id')
            ->where('object_fields.object_type', '=', 'PCK\ContractorQuestionnaire\ReplyAttachment')
            ->where('module_uploaded_files.uploadable_type', '=', 'PCK\ObjectField\ObjectField')
            ->where('contractor_questionnaire_questions.contractor_questionnaire_id', '=', $questionnaire->id)
            ->groupBy('contractor_questionnaire_reply_attachments.contractor_questionnaire_question_id')
            ->lists('count', 'contractor_questionnaire_question_id');

            foreach($questions as $record)
            {
                $data = [
                    'id'                     => $record->id,
                    'counter'                => $counter,
                    'question'               => trim($record->question),
                    'type'                   => $record->type,
                    'type_txt'               => $record->getTypeText(),
                    'replies'                => [],
                    'with_attachment'        => $record->with_attachment,
                    'attachment_count'       => array_key_exists($record->id, $attachments) ? $attachments[$record->id] : 0,
                    'submitted_date'         => '-',
                    'required'               => $record->required,
                    'route:attachment-list'  => $record->with_attachment ? route('projects.questionnaires.contractor.attachments', [$project->id, $record->id]) : ''
                ];

                $replyCount = 1;
                foreach($replies as $idx => $reply)
                {
                    if($reply->contractor_questionnaire_question_id == $record->id)
                    {
                        if($record->type == Question::TYPE_TEXT or $record->type == Question::TYPE_ATTACHMENT_ONLY)
                        {
                            $replyTxt = mb_strlen(trim($reply->text)) ? trim($reply->text) : "-";
                        }
                        else
                        {
                            $replyTxt = mb_strlen(trim($reply->option_text)) ? $replyCount.'. '.trim($reply->option_text) : "-";
                        }

                        $data['replies'][] = $replyTxt;
                        $data['submitted_date'] = Carbon::parse($reply->created_at)->format('d/m/Y H:i:s');

                        $replyCount++;
                        unset($replies[$idx]);
                    }
                }

                $records[] = $data;
                
                $counter++;
            }
        }

        return Response::json($records);
    }

    public function questionShow(Project $project, $questionId)
    {
        $question = Question::findOrFail((int)$questionId);
        $company  = $question->questionnaire->company;
        $user     = \Confide::user();
        $isEditor = $user->isEditor($project);

        return View::make('tenders.questionnaires.questions.show', compact('project', 'question', 'company', 'user', 'isEditor'));
    }

    public function questionCreate(Project $project, $companyId)
    {
        $company  = Company::findOrFail((int)$companyId);
        $question = null;
        $user     = \Confide::user();

        if(!$user->isEditor($project))
        {
            return Redirect::route('projects.questionnaires.show', [$project->id, $company->id]);
        }

        $typeList = [
            Question::TYPE_TEXT            => Question::TYPE_TEXT_TEXT,
            Question::TYPE_ATTACHMENT_ONLY => Question::TYPE_ATTACHMENT_ONLY_TEXT,
            Question::TYPE_MULTI_SELECT    => Question::TYPE_MULTI_SELECT_TEXT,
            Question::TYPE_SINGLE_SELECT   => Question::TYPE_SINGLE_SELECT_TEXT
        ];

        return View::make('tenders.questionnaires.questions.edit', compact('question', 'project', 'company', 'user', 'typeList'));
    }

    public function questionEdit(Project $project, $questionId)
    {
        $question = Question::findOrFail((int)$questionId);
        $company  = $question->questionnaire->company;
        $user     = \Confide::user();

        if(!$user->isEditor($project))
        {
            return Redirect::route('projects.questionnaires.question.show', [$project->id, $question->id]);
        }

        $typeList = [
            Question::TYPE_TEXT            => Question::TYPE_TEXT_TEXT,
            Question::TYPE_ATTACHMENT_ONLY => Question::TYPE_ATTACHMENT_ONLY_TEXT,
            Question::TYPE_MULTI_SELECT    => Question::TYPE_MULTI_SELECT_TEXT,
            Question::TYPE_SINGLE_SELECT   => Question::TYPE_SINGLE_SELECT_TEXT
        ];

        return View::make('tenders.questionnaires.questions.edit', compact('question', 'project', 'company', 'user', 'typeList'));
    }

    public function questionStore(Project $project)
    {
        $this->questionForm->validate(Input::all());

        $input   = Input::all();
        $company = Company::findOrFail((int)$input['cid']);
        $user    = \Confide::user();

        if(!$user->isEditor($project))
        {
            return Redirect::route('projects.questionnaires.show', [$project->id, $company->id]);
        }

        $questionnaire = Questionnaire::where('project_id', '=', $project->id)
        ->where('company_id', '=', $company->id)
        ->first();

        if(!$questionnaire)
        {
            $questionnaire = new Questionnaire();

            $questionnaire->project_id = $project->id;
            $questionnaire->company_id = $company->id;
            $questionnaire->status     = Questionnaire::STATUS_UNPUBLISHED;
            $questionnaire->created_by = $user->id;
            $questionnaire->updated_by = $user->id;

            $questionnaire->save();
        }

        $question = Question::find($input['id']);

        if(!$question)
        {
            $question = new Question();

            $question->contractor_questionnaire_id = $questionnaire->id;
            $question->created_by                  = $user->id;
        }

        $question->question        = trim($input['question']);
        $question->type            = $input['type'];
        $question->required        = $input['required'];
        $question->with_attachment = array_key_exists('with_attachment', $input);
        $question->updated_by      = $user->id;

        $question->save();

        $question->options()->delete();

        if($question->type == Question::TYPE_MULTI_SELECT or $question->type == Question::TYPE_SINGLE_SELECT)
        {
            foreach($input['options'] as $idx => $fields)
            {
                $option = new Option();

                $option->contractor_questionnaire_question_id = $question->id;
                $option->text                                 = trim($fields['text']);
                $option->order                                = $idx;
                $option->created_by                           = $user->id;
                $option->updated_by                           = $user->id;

                $option->save();
            }
        }

        \Flash::success(trans('forms.saved'));

        return Redirect::route('projects.questionnaires.question.show', [$project->id, $question->id]);
    }

    public function questionDelete(Project $project, $questionId)
    {
        $question = Question::findOrFail((int)$questionId);
        $company  = $question->questionnaire->company;
        $user     = \Confide::user();

        if(!$user->isEditor($project))
        {
            return Redirect::route('projects.questionnaires.question.show', [$project->id, $question->id]);
        }

        if($question->deletable())
        {
            $question->delete();

            \Log::info("Delete contractor questionnaire question [question id: {$questionId}]][company id: {$company->id}][user id:{$user->id}]");
        }

        return Redirect::route('projects.questionnaires.show', [$project->id, $company->id]);
    }

    public function publish(Project $project)
    {
        $request = Request::instance();
        $company = Company::findOrFail((int)$request->get('cid'));
        $user    = \Confide::user();

        if(!$user->isEditor($project))
        {
            return Redirect::route('projects.questionnaires.show', [$project->id, $company->id]);
        }
        
        $questionnaire = Questionnaire::where('project_id', '=', $project->id)
        ->where('company_id', '=', $company->id)
        ->first();

        if(!$questionnaire)
        {
            $questionnaire = new Questionnaire();

            $questionnaire->project_id     = $project->id;
            $questionnaire->company_id     = $company->id;
            $questionnaire->status         = Questionnaire::STATUS_PUBLISHED;
            $questionnaire->published_date = date('Y-m-d H:i:s');
            $questionnaire->created_by     = $user->id;
        }
        else
        {
            if($questionnaire->status == Questionnaire::STATUS_UNPUBLISHED)
            {
                $questionnaire->status         = Questionnaire::STATUS_PUBLISHED;
                $questionnaire->published_date = date('Y-m-d H:i:s');
            }
            else
            {
                $questionnaire->status           = Questionnaire::STATUS_UNPUBLISHED;
                $questionnaire->unpublished_date = date('Y-m-d H:i:s');
            }
        }

        $questionnaire->updated_by = $user->id;

        $questionnaire->save();

        if($questionnaire->status == Questionnaire::STATUS_PUBLISHED)
        {
            $selectedUsersByCompanies = $this->userRepo->getSelectedProjectUsersGroupByCompany($project);
            $recipients    = [];

            foreach($selectedUsersByCompanies as $cid => $selectedUsers)
            {
                foreach($selectedUsers as $selectedUser)
                {
                    $recipients[$selectedUser->id] = $selectedUser;
                }
            }
            
            unset($selectedUsersByCompanies);

            if(!empty($recipients))
            {
                $recipients = array_values($recipients);
                $content = [
                    'subject' => "Tender Management - Questionnaire Published",//need to move this to i10n
                    'view' => 'tenders.questionnaires.email.publish',
                    'data' => [
                        'projectTitle' => $project->title,
                        'contractNumber' => $project->reference,
                        'companyName' => $company->name,
                        'companyReferenceNumber' => $company->reference_no,
                        'actionName' => 'published',
                        'creator' => $user->name,
                        'forContractor' => false,
                        'route' => route('projects.questionnaires.show', [$project->id, $company->id])
                    ]
                ];

                $this->emailNotifier->sendGeneralEmail($content, $recipients);
            }

            $contractorUsers = User::select('users.*')
                ->whereRaw('users.company_id = '.$company->id.'
                AND users.confirmed IS TRUE
                AND users.account_blocked_status IS FALSE')
                ->groupBy('users.id')
                ->get();
            
            if(!empty($contractorUsers))
            {
                $content = [
                    'subject' => "Tender Management - Questionnaire Published",//need to move this to i10n
                    'view' => 'tenders.questionnaires.email.publish',
                    'data' => [
                        'projectTitle' => $project->title,
                        'contractNumber' => $project->reference,
                        'companyName' => $company->name,
                        'companyReferenceNumber' => $company->reference_no,
                        'actionName' => 'published',
                        'creator' => $user->name,
                        'forContractor' => true,
                        'route' => route('contractor.questionnaires.show', [$project->id])
                    ]
                ];

                $this->emailNotifier->sendGeneralEmail($content, $contractorUsers);
            }
        }

        \Flash::success(trans('forms.saved'));

        return Redirect::route('projects.questionnaires.show', [$project->id, $company->id]);
    }

    public function contractorQuestionnaireIndex()
    {
        return View::make('tenders.questionnaires.contractor.index');
    }

    public function contractorQuestionnaireProjectList()
    {
        $user = \Confide::user();

        if(!$user->company)
        {
            return Response::json([
                'last_page' => 0,
                'data'      => []
            ]);
        }

        $company = $user->company;

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = Project::select("projects.id AS id", "projects.title", "projects.reference", "projects.created_at",
        "states.name AS state_name", "countries.country AS country_name", "contractor_questionnaires.published_date")
        ->join(\DB::raw("(SELECT MAX(t.id) AS tender_id, t.project_id
        FROM tenders t
        JOIN projects p ON t.project_id = p.id
        WHERE p.deleted_at IS NULL
        GROUP BY t.project_id) latest_tender"), 'latest_tender.project_id', '=', 'projects.id')
        ->join('tenders', 'tenders.id', '=', 'latest_tender.tender_id')
        ->join('contractor_questionnaires', 'contractor_questionnaires.project_id', '=', 'tenders.project_id')
        ->join('states', 'projects.state_id', '=', 'states.id')
        ->join('countries', 'projects.country_id', '=', 'countries.id')
        ->where('contractor_questionnaires.company_id', '=', $company->id)
        ->where('contractor_questionnaires.status', '=', Questionnaire::STATUS_PUBLISHED);

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
                            $model->where('projects.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'reference_no':
                        if(strlen($val) > 0)
                        {
                            $model->where('projects.reference', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('contractor_questionnaires.published_date', 'desc');

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
                'reference_no'        => trim($record->reference),
                'title'               => trim($record->title),
                'state'               => trim($record->state_name),
                'country'             => trim($record->country_name),
                'created_at'          => Carbon::parse($record->created_at)->format('d/m/Y'),
                'tender_closing_date' => ($record->latestTender) ? Carbon::parse($record->latestTender->tender_closing_date)->format('d/m/Y') : '-',
                'questionnaire_date'  => Carbon::parse($record->published_date)->format('d/m/Y'),
                'route:show'          => route('contractor.questionnaires.show', [$record->id])
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function contractorQuestionnaireShow($projectId)
    {
        //set $projectObj instead $project variable so the form route upload_file_modal.blade will use general upload route instead project upload route
        $projectObj = Project::findOrFail((int)$projectId);
        $user    = \Confide::user();

        if(!$user->company)
        {
            return Redirect::route('contractor.questionnaires.index');
        }

        $company = $user->company;

        $questionnaire = Questionnaire::where('project_id', '=', $projectObj->id)
        ->where('company_id', '=', $company->id)
        ->where('status', '=', Questionnaire::STATUS_PUBLISHED)
        ->first();//just to make sure non company user cannot view questionnaires

        if(!$questionnaire)
        {
            return Redirect::route('contractor.questionnaires.index');
        }

        return View::make('tenders.questionnaires.contractor.show', compact('questionnaire', 'projectObj', 'user'));
    }

    public function contractorQuestionnaireReply()
    {
        $user           = \Confide::user();
        $request        = Request::instance();
        $questionnaire  = Questionnaire::where('id', '=', $request->get('qid'))
        ->where('company_id', '=', $user->company_id)
        ->where('status', '=', Questionnaire::STATUS_PUBLISHED)
        ->first();//just to make sure non company user cannot view questionnaires

        $question = Question::findOrFail($request->get('id'));

        if(!$questionnaire)
        {
            return Response::json([
                'success' => false
            ]);
        }
        
        try
        {
            $reply = null;

            Reply::where('contractor_questionnaire_question_id', '=', $question->id)
            ->delete();

            switch($question->type)
            {
                case Question::TYPE_TEXT:
                    $reply = new Reply;
                    $reply->contractor_questionnaire_question_id = $question->id;
                    $reply->text = trim($request->get('text'));
                    $reply->created_by = $user->id;
                    $reply->updated_by = $user->id;
                    
                    $reply->save();

                    break;
                case Question::TYPE_MULTI_SELECT:
                    foreach($request->get('options') as $optionId)
                    {
                        $reply = new Reply;
                        $reply->contractor_questionnaire_question_id = $question->id;
                        $reply->contractor_questionnaire_option_id = $optionId;
                        $reply->created_by = $user->id;
                        $reply->updated_by = $user->id;

                        $reply->save();
                    }
                    break;
                case Question::TYPE_SINGLE_SELECT:
                    if(!empty($request->get('options')))
                    {
                        $reply = new Reply;
                        $reply->contractor_questionnaire_question_id = $question->id;
                        $reply->contractor_questionnaire_option_id = (int)$request->get('options');
                        $reply->created_by = $user->id;
                        $reply->updated_by = $user->id;

                        $reply->save();
                    }
                    break;
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

    public function contractorQuestionnaireAttachmentList($questionId)
    {
        $user    = \Confide::user();
        $request = Request::instance();

        $questionnaire = Questionnaire::where('id', '=', $request->get('qid'))
        ->where('company_id', '=', $user->company_id)
        ->where('status', '=', Questionnaire::STATUS_PUBLISHED)
        ->first();//just to make sure non company user cannot view questionnaires

        if(!$questionnaire)
        {
            return Response::json([]);
        }

        $question = null;

        $question = Question::findOrFail($questionId);

        $model = ReplyAttachment::select('uploads.id', 'uploads.filename', 'uploads.extension', 'uploads.created_at',
        'contractor_questionnaire_reply_attachments.id AS reply_id')
        ->join('object_fields', 'object_fields.object_id', '=', 'contractor_questionnaire_reply_attachments.id')
        ->join('module_uploaded_files', 'module_uploaded_files.uploadable_id', '=', 'object_fields.id')
        ->join('uploads', 'uploads.id', '=', 'module_uploaded_files.upload_id')
        ->where('object_fields.object_type', '=', 'PCK\ContractorQuestionnaire\ReplyAttachment')
        ->where('module_uploaded_files.uploadable_type', '=', 'PCK\ObjectField\ObjectField')
        ->where('contractor_questionnaire_reply_attachments.contractor_questionnaire_question_id', '=', $question->id);

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
                'route:download' => route('contractor.questionnaires.attachments.download', [$uploadedFile->id]),
                'route:delete'   => route('contractor.questionnaires.attachments.delete', [$uploadedFile->id])
            ];
        }

        return Response::json($attachments);
    }

    public function contractorQuestionnaireAttachmentUpload()
    {
        $request = Request::instance();
        $user    = \Confide::user();
        
        $uploadedFiles = $request->get('uploaded_files');

        $success = false;

        $questionnaire = Questionnaire::where('id', '=', $request->get('qid'))
        ->where('company_id', '=', $user->company_id)
        ->where('status', '=', Questionnaire::STATUS_PUBLISHED)
        ->first();//just to make sure non company user cannot view questionnaires

        if(!$questionnaire)
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
                $question = Question::findOrFail($request->get('id'));
                    
                $reply = Reply::where('contractor_questionnaire_question_id', '=', $question->id)
                ->first();

                if(!$reply)
                {
                    $reply = new Reply;
                    $reply->contractor_questionnaire_question_id = $question->id;
                    $reply->created_by = $user->id;
                    $reply->updated_by = $user->id;

                    $reply->save();
                }

                $replyAttachment = ReplyAttachment::where('contractor_questionnaire_question_id', '=', $question->id)
                ->first();

                if(!$replyAttachment)
                {
                    $replyAttachment = new ReplyAttachment;
                    $replyAttachment->contractor_questionnaire_question_id = $question->id;
                    $replyAttachment->created_by = $user->id;
                    $replyAttachment->updated_by = $user->id;

                    $replyAttachment->save();
                }

                $object = ObjectField::findOrCreateNew($replyAttachment, $replyAttachment->getTable());

                foreach($uploadedFiles as $uploadId)
                {
                    \PCK\ModuleUploadedFiles\ModuleUploadedFile::create([
                        'upload_id'       => $uploadId,
                        'uploadable_id'   => $object->id,
                        'uploadable_type' => get_class($object)
                    ]);
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

    public function contractorQuestionnaireAttachmentDownload($uploadId)
    {
        $request = Request::instance();
        $user    = \Confide::user();
        $upload  = Upload::findOrFail($uploadId);

        $filepath = base_path().DIRECTORY_SEPARATOR.$upload->path.DIRECTORY_SEPARATOR.$upload->filename;

        return \PCK\Helpers\Files::download($filepath, $upload->filename);
    }

    public function contractorQuestionnaireAttachmentDelete($uploadId)
    {
        $request = Request::instance();
        $user    = \Confide::user();
        $upload  = Upload::findOrFail($uploadId);

        $questionnaire = Questionnaire::where('id', '=', $request->get('qid'))
        ->where('company_id', '=', $user->company_id)
        ->where('status', '=', Questionnaire::STATUS_PUBLISHED)
        ->first();//just to make sure non company user cannot view questionnaires

        $question = Question::findOrFail($request->get('id'));

        $success = false;

        if($question && $questionnaire)
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

    public function contractorQuestionnaireNotify()
    {
        $user           = \Confide::user();
        $request        = Request::instance();
        $questionnaire  = Questionnaire::where('id', '=', $request->get('id'))
        ->where('company_id', '=', $user->company_id)
        ->where('status', '=', Questionnaire::STATUS_PUBLISHED)
        ->first();//just to make sure non company user cannot view questionnaires

        if(!$questionnaire)
        {
            return Redirect::route('contractor.questionnaires.index');
        }
        
        $project = $questionnaire->project;

        $buCompany             = $project->selectedCompanies()->where('contract_group_id', '=', ContractGroup::getIdByGroup(Role::PROJECT_OWNER))->first();
        $gcdCompany            = $project->selectedCompanies()->where('contract_group_id', '=', ContractGroup::getIdByGroup(Role::GROUP_CONTRACT))->first();
        $tenderDocumentCompany = $project->selectedCompanies()->where('contract_group_id', '=', $project->contractGroupTenderDocumentPermission->contract_group_id)->first();

        $userIds = $buCompany->getProjectEditors($project)->lists('id');

        if($gcdCompany)
        {
            $userIds = array_merge($userIds, $gcdCompany->getProjectEditors($project)->lists('id'));
        }

        if($tenderDocumentCompany)
        {
            $userIds = array_merge($userIds, $tenderDocumentCompany->getProjectEditors($project)->lists('id'));
        }

        $userIds = array_unique($userIds);

        $recipients = array_map(function ($id) { 
            return User::find($id);
        }, $userIds);

        if(!empty($recipients))
        {
            $content = [
                'subject' => "Tender Management - Questionnare Replies from Contractor (".$user->company->name.")",//need to move this to i10n
                'view' => 'tenders.questionnaires.email.notify',
                'data' => [
                    'projectTitle' => $project->title,
                    'contractNumber' => $project->reference,
                    'companyName' => $user->company->name,
                    'companyReferenceNumber' => $user->company->reference_no,
                    'submitter' => $user->name,
                    'route' => route('projects.questionnaires.show', [$project->id, $user->company->id])
                ]
            ];
            
            $this->emailNotifier->sendGeneralEmail($content, $recipients);
        }

        \Flash::success("Sucessfully notified PIC about the questionnaire replies");

        return Redirect::route('contractor.questionnaires.show', [$project->id]);
    }

    public function contractorAttachmentList(Project $project, $questionId)
    {
        $question = Question::findOrFail((int)$questionId);
        $user    = \Confide::user();

        $model = ReplyAttachment::select('uploads.id', 'uploads.filename', 'uploads.extension', 'uploads.created_at',
        'contractor_questionnaire_reply_attachments.id AS reply_id')
        ->join('object_fields', 'object_fields.object_id', '=', 'contractor_questionnaire_reply_attachments.id')
        ->join('module_uploaded_files', 'module_uploaded_files.uploadable_id', '=', 'object_fields.id')
        ->join('uploads', 'uploads.id', '=', 'module_uploaded_files.upload_id')
        ->where('object_fields.object_type', '=', 'PCK\ContractorQuestionnaire\ReplyAttachment')
        ->where('module_uploaded_files.uploadable_type', '=', 'PCK\ObjectField\ObjectField')
        ->where('contractor_questionnaire_reply_attachments.contractor_questionnaire_question_id', '=', $question->id);

        $uploadedFiles = $model->orderBy('uploads.filename', 'asc')->get();

        $attachments = [];
        
        foreach($uploadedFiles as $uploadedFile)
        {
            $attachments[] = [
                'id'             => $uploadedFile->id,
                'title'          => trim($uploadedFile->filename),
                'type'           => 'file',
                'extension'      => $uploadedFile->extension,
                'uploaded_at'    => Carbon::parse($uploadedFile->uploaded_at)->format('d/m/Y H:i:s'),
                'route:download' => route('contractor.questionnaires.attachments.download', [$uploadedFile->id])
            ];
        }

        return Response::json($attachments);
    }

    public function contractorReplyPrint(Project $project, $companyId)
    {
        //$request = Request::instance();

        $company = Company::findOrFail((int)$companyId);
        //$user    = \Confide::user();

        $questionnaire = Questionnaire::where('project_id', '=', $project->id)
            ->where('company_id', '=', $company->id)
            ->first();

        if (! $questionnaire) {
            return Redirect::back();
        }

        $questions = $questionnaire->questions;

        $replies = Reply::select('contractor_questionnaire_replies.id',
            'contractor_questionnaire_replies.contractor_questionnaire_question_id',
            'contractor_questionnaire_replies.created_at',
            'contractor_questionnaire_replies.text', 'contractor_questionnaire_options.text AS option_text')
            ->leftJoin('contractor_questionnaire_options', function($join){
                $join->on('contractor_questionnaire_options.contractor_questionnaire_question_id', '=', 'contractor_questionnaire_replies.contractor_questionnaire_question_id');
                $join->on('contractor_questionnaire_options.id','=', 'contractor_questionnaire_replies.contractor_questionnaire_option_id');
            })
            ->join('contractor_questionnaire_questions', 'contractor_questionnaire_replies.contractor_questionnaire_question_id', '=', 'contractor_questionnaire_questions.id')
            ->where('contractor_questionnaire_questions.contractor_questionnaire_id', '=', $questionnaire->id)
            ->orderBy('contractor_questionnaire_questions.created_at', 'DESC')
            ->get();

        $attachments = ReplyAttachment::select(\DB::raw('contractor_questionnaire_reply_attachments.contractor_questionnaire_question_id,
            COUNT(uploads.id) AS count'))
            ->join('contractor_questionnaire_questions', 'contractor_questionnaire_reply_attachments.contractor_questionnaire_question_id', '=', 'contractor_questionnaire_questions.id')
            ->join('object_fields', 'object_fields.object_id', '=', 'contractor_questionnaire_reply_attachments.id')
            ->join('module_uploaded_files', 'module_uploaded_files.uploadable_id', '=', 'object_fields.id')
            ->join('uploads', 'uploads.id', '=', 'module_uploaded_files.upload_id')
            ->where('object_fields.object_type', '=', 'PCK\ContractorQuestionnaire\ReplyAttachment')
            ->where('module_uploaded_files.uploadable_type', '=', 'PCK\ObjectField\ObjectField')
            ->where('contractor_questionnaire_questions.contractor_questionnaire_id', '=', $questionnaire->id)
            ->groupBy('contractor_questionnaire_reply_attachments.contractor_questionnaire_question_id')
            ->lists('count', 'contractor_questionnaire_question_id');

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()->setCreator('Buildspace');

        $activeSheet = $spreadsheet->getActiveSheet();
        $activeSheet->setTitle(StringOperations::shorten($company->name, 31));
        $activeSheet->setAutoFilter('A1:I1');

        $html = new Html();

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

        $headers = [
            //'no'                     => 'No',
            'id'                     => 'ID',
            'question'               => 'Question',
            'type'                   => 'Type',
            'type_txt'               => 'Type (text)',
            'replies'                => 'Reply',
            'with_attachment'        => 'With Attachment',
            'attachment_count'       => 'Total Attachments',
            'submitted_date'         => 'Submitted Date',
            'required'               => 'Mandatory'
            //'route:attachment-list'  => 'Attachment List (Route)'
        ];

        $headerCount = 1;
        foreach ($headers as $key => $val)
        {
            $cell = StringOperations::numberToAlphabet($headerCount)."1";
            $activeSheet->setCellValue($cell, $val);
            $activeSheet->getStyle($cell)->applyFromArray($headerStyle);

            $activeSheet->getColumnDimension(StringOperations::numberToAlphabet($headerCount))->setAutoSize(true);

            $headerCount++;
        }

        $data = [];
        //$counter = 1;

        foreach($questions as $record)
        {
            $replyCount = 1;

            $replyList = [];

            foreach($replies as $idx => $reply)
            {
                if($reply->contractor_questionnaire_question_id == $record->id)
                {
                    if($record->type == Question::TYPE_TEXT or $record->type == Question::TYPE_ATTACHMENT_ONLY)
                    {
                        $replyTxt = mb_strlen(trim($reply->text)) ? trim($reply->text) : "-";
                    }
                    else
                    {
                        $replyTxt = mb_strlen(trim($reply->option_text)) ? $replyCount.'. '.trim($reply->option_text) : "-";
                    }

                    $replyList[] = $replyTxt;
                    //$data['submitted_date'] = Carbon::parse($reply->created_at)->format('d/m/Y H:i:s');

                    $replyCount++;
                    unset($replies[$idx]);
                }
            }

            $data[] = [
                //$counter,
                $record->id,
                $html->toRichTextObject(trim($record->question)),
                $record->type,
                $record->getTypeText(),
                implode("\n", $replyList),
                $record->with_attachment ? 'Yes' : 'No',
                array_key_exists($record->id, $attachments) ? $attachments[$record->id] : '0',
                $record->getReplySubmittedDate(),
                $record->required ? 'Yes' : 'No'
                //$record->with_attachment ? route('projects.questionnaires.contractor.attachments', [$project->id, $record->id]) : ''
            ];

            unset($replyList);

            //$counter++;
        }

        $activeSheet->fromArray($data, null, 'A2');

        $activeSheet->getColumnDimension('A')->setAutoSize(false)->setWidth(8);
        $activeSheet->getColumnDimension('B')->setAutoSize(false)->setWidth(20);
        $activeSheet->getColumnDimension('C')->setAutoSize(false)->setWidth(10);
        $activeSheet->getColumnDimension('D')->setAutoSize(false)->setWidth(20);
        $activeSheet->getColumnDimension('E')->setAutoSize(false)->setWidth(100);
        $activeSheet->getColumnDimension('F')->setAutoSize(false)->setWidth(25);
        $activeSheet->getColumnDimension('G')->setAutoSize(false)->setWidth(25);
        $activeSheet->getColumnDimension('H')->setAutoSize(false)->setWidth(20);
        $activeSheet->getColumnDimension('I')->setAutoSize(false)->setWidth(15);

        $highest_row = $activeSheet->getHighestRow();     // Highest row no. with content
        for ($row = 1; $row <= $highest_row; $row++) {   // Loop through rows
            $activeSheet->getStyle('A'.$row)->getAlignment()->setWrapText(true);
            $activeSheet->getStyle('B'.$row)->getAlignment()->setWrapText(true);
            $activeSheet->getStyle('C'.$row)->getAlignment()->setWrapText(true)->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $activeSheet->getStyle('D'.$row)->getAlignment()->setWrapText(true)->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $activeSheet->getStyle('E'.$row)->getAlignment()->setWrapText(true);
            $activeSheet->getStyle('F'.$row)->getAlignment()->setWrapText(true)->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $activeSheet->getStyle('G'.$row)->getAlignment()->setWrapText(true)->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $activeSheet->getStyle('H'.$row)->getAlignment()->setWrapText(true)->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $activeSheet->getStyle('I'.$row)->getAlignment()->setWrapText(true)->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $writer = new Xlsx($spreadsheet);

        $filepath = \PCK\Helpers\Files::getTmpFileUri();

        $writer->save($filepath);

        $filename = 'Questionnaire-'.$company->name;

        return \PCK\Helpers\Files::download($filepath, "{$filename}.".\PCK\Helpers\Files::EXTENSION_EXCEL);
    }
}