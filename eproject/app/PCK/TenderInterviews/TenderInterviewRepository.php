<?php namespace PCK\TenderInterviews;

use Carbon\Carbon;
use Confide;
use PCK\Base\BaseModuleRepository;
use PCK\ContractGroupProjectUsers\ContractGroupProjectUser;
use PCK\ContractGroups\ContractGroup;
use PCK\ContractGroups\Types\Role;
use PCK\Helpers\DataTables;
use PCK\Helpers\Key;
use PCK\Notifications\EmailNotifier;
use PCK\Projects\Project;
use PCK\Tenders\Tender;

class TenderInterviewRepository extends BaseModuleRepository {

    private $emailNotifier;

    public function __construct(EmailNotifier $emailNotifier)
    {
        $this->emailNotifier = $emailNotifier;
    }

    /**
     * Finds a Tender Interview by company id and tender id.
     *
     * @param $companyId
     * @param $tenderId
     *
     * @return bool
     */
    public function findByCompanyIdAndTenderId($companyId, $tenderId)
    {
        $model = TenderInterview::where('company_id', '=', $companyId)
            ->where('tender_id', '=', $tenderId)
            ->first();

        return is_null($model) ? false : $model;
    }

    /**
     * Find by tender id.
     *
     * @param $tenderId
     *
     * @return mixed
     */
    public function findByTenderId($tenderId)
    {
        return TenderInterview::where('tender_id', '=', $tenderId)
            ->get();
    }

    /**
     * Creates a new interview.
     *
     * @param $companyId
     * @param $tenderId
     *
     * @return TenderInterview
     */
    public function createNew($companyId, $tenderId)
    {
        $interviewInfo = $this->findOrNewTenderInterviewInformationByTender($tenderId);

        $templateInterview = $interviewInfo->getCompanyInterviews()->last();

        return TenderInterview::create(array(
            'company_id'                      => $companyId,
            'tender_interview_information_id' => $interviewInfo->id,
            'tender_id'                       => $tenderId,
            'venue'                           => $templateInterview->venue ?? '',
            'date_and_time'                   => Carbon::now(),
            'status'                          => TenderInterview::STATUS_DEFAULT,
        ));
    }

    /**
     * Finds an interview by company id and tender id.
     * Creates a new one if it does not exist.
     *
     * @param $companyId
     * @param $tenderId
     *
     * @return bool|TenderInterviewRepository
     */
    public function findByCompanyAndTenderIdOrNew($companyId, $tenderId)
    {
        if( ! ( $model = $this->findByCompanyIdAndTenderId($companyId, $tenderId) ) )
        {
            $model = $this->createNew($companyId, $tenderId);
        }

        return $model;
    }

    /**
     * Finds the interview information by tender id.
     * Creates a new one if it does not exist.
     *
     * @param $tenderId
     *
     * @return TenderInterviewInformation
     */
    public function findOrNewTenderInterviewInformationByTender($tenderId)
    {
        if( ! $model = TenderInterviewInformation::where('tender_id', '=', $tenderId)->first() )
        {
            $model = TenderInterviewInformation::create(array( 'tender_id' => $tenderId, 'date_and_time' => Carbon::now() ));
        }

        return $model;
    }

    /**
     * Returns data of interviews associated with the tender and the contractors involved.
     *
     * @param       $input
     * @param       $tenderId
     * @param array $selectedContractorsId
     *
     * @return array
     */
    public function getData($input, $tenderId, array $selectedContractorsId)
    {
        // Create tender_interviews if they do not yet exist.
        $tenderInterviews = array();

        foreach($selectedContractorsId as $contractorId)
        {
            $tenderInterviews[ $contractorId ] = $this->findByCompanyAndTenderIdOrNew($contractorId, $tenderId);
        }

        $allColumns = array(
            'companies'         => array( 'name' => 1 ),
            'tender_interviews' => array( 'date_and_time' => 2 ),
        );

        $idColumn      = 'companies.id';
        $selectColumns = array( $idColumn, 'companies.name', 'tender_interviews.date_and_time' );

        $query = \DB::table("companies as companies");

        $dataTable = new DataTables($query, $input, $allColumns, $idColumn, $selectColumns);

        $dataTable->properties->query->join('tender_interviews as tender_interviews', 'companies.id', '=', 'tender_interviews.company_id')
            ->where('tender_interviews.tender_id', '=', $tenderId)
            ->whereIn('tender_interviews.company_id', $selectedContractorsId);

        $dataTable->properties->query->orderBy('tender_interviews.date_and_time', 'ASC');

        $dataTable->addAllStatements();

        $results = $dataTable->getResults();

        $resultsArray = array();

        foreach($results as $arrayIndex => $stdObject)
        {
            $indexNo = ( $arrayIndex + 1 ) + ( $dataTable->properties->pagingOffset );

            $resultsArray[] = array(
                'indexNo'    => $indexNo,
                'company'    => $stdObject->name,
                'unmodified' => ( $tenderInterviews[ $stdObject->id ]->created_at == $tenderInterviews[ $stdObject->id ]->updated_at ),
                'time'       => $tenderInterviews[ $stdObject->id ]->date_and_time,
                'status'     => TenderInterview::getText($tenderInterviews[ $stdObject->id ]->status),
                'venue'      => $tenderInterviews[ $stdObject->id ]->venue,
                'companyId'  => $stdObject->id,
            );
        }

        return $dataTable->dataTableResponse($resultsArray);
    }

    /**
     * Updates all interviews associated with a tender.
     *
     * @param $tenderId
     * @param $date
     * @param $venue
     * @param $companies
     *
     * @return bool
     */
    public function massUpdate($tenderId, $date, $venue, $companies)
    {
        if( empty( $venue = trim($venue) ) ) return false;

        try
        {
            foreach($companies as $companyData)
            {
                $interview                = TenderInterview::where('company_id', '=', $companyData['id'])
                    ->where('tender_id', '=', $tenderId)
                    ->first();
                $interview->venue         = $venue;
                $interview->date_and_time = Carbon::createFromFormat('Y-m-d g:i A', $date . ' ' . $companyData['time']);
                $interview->save();
            }
        }
        catch(\Exception $e)
        {
            return false;
        }

        return true;
    }

    public function sendTenderMeetingRequest(Project $project, $tenderId)
    {
        if( ! $this->findOrNewTenderInterviewInformationByTender($tenderId)->isActivated() ) return false;

        $contractGroup = ContractGroup::where('group', '=', $project->getCallingTenderRole())->first();

        // Send to calling tender editors as well as BU Editors.
        $callingTenderEditorsIds = ContractGroupProjectUser::where('contract_group_id', '=', $contractGroup->id)
            ->where('project_id', '=', $project->id)
            ->where('is_contract_group_project_owner', '=', true)
            ->lists('user_id');

        $businessUnitGroup = ContractGroup::where('group', '=', Role::PROJECT_OWNER)->first();

        $businessUnitEditorIds = ContractGroupProjectUser::where('contract_group_id', '=', $businessUnitGroup->id)
            ->where('project_id', '=', $project->id)
            ->where('is_contract_group_project_owner', '=', true)
            ->lists('user_id');

        $groupContractIds = ContractGroup::where('group', '=', Role::GROUP_CONTRACT)->first();

        $groupContractEditorIds = ContractGroupProjectUser::where('contract_group_id', '=', $groupContractIds->id)
            ->where('project_id', '=', $project->id)
            ->where('is_contract_group_project_owner', '=', true)
            ->lists('user_id');

        $allIds = array_merge($callingTenderEditorsIds, $businessUnitEditorIds, $groupContractEditorIds);

        $recipientIds = array_unique($allIds);

        $this->emailNotifier->sendTenderMeetingRequest($project, $tenderId, $recipientIds);

        return true;
    }

    /**
     * Sends an email request for a tender interview to each of the selected (and not deleted) contractors.
     *
     * @param Project $project
     * @param         $tenderId
     *
     * @return bool
     */
    public function sendRequestToContractors(Project $project, $tenderId)
    {
        if( ! $this->findOrNewTenderInterviewInformationByTender($tenderId)->isActivated() ) return false;

        $selectedCompanies = array();
        $interviews        = array();
        $tender            = Tender::find($tenderId);
        $user              = Confide::user();

        TenderInterviewInformation::setContractGroup($tender, $user->getAssignedCompany($project)->getContractGroup($project));

        foreach($tender->tenderInterviewInfo->getCompanyInterviews() as $interview)
        {
            $interview->key = Key::createKey('tender_interviews', 'key');

            $interview->save();

            $interviews[ $interview->company->id ] = $interview;

            $selectedCompanies[] = $interview->company;
        }

        $this->emailNotifier->sendTenderInterviewRequest($project, $selectedCompanies, $interviews);

        return true;
    }

    /**
     * Returns a list of the tender interview statuses.
     *
     * @return array
     */
    public function getStatusDropDownListing()
    {
        return TenderInterview::getStatusDropDownListing();
    }

    /**
     * Updates a tender interview based on the reply of the contractor.
     *
     * @param $key
     * @param $selectedOption
     *
     * @return bool
     */
    public function saveReply($key, $selectedOption)
    {
        $interview = TenderInterview::where('key', '=', $key)->first();

        $interview->status = $selectedOption;

        $interview->key = null;

        if( ! $contractGroup = $interview->info->contractGroup )
        {
            $contractGroup = ContractGroup::where('group', '=', $interview->tender->project->getCallingTenderRole())
                ->first();
        }

        $editorIds = ContractGroupProjectUser::where('contract_group_id', '=', $contractGroup->id)
            ->where('project_id', '=', $interview->tender->project->id)
            ->where('is_contract_group_project_owner', '=', true)
            ->lists('user_id');

        $this->emailNotifier->notifyEditorsOnInterviewReply($interview->tender->project, $interview, $editorIds);

        return $interview->save();
    }

    /**
     * Updates the Tender Interview Information Discussion Time.
     *
     * @param $tenderId
     * @param $date
     * @param $time
     *
     * @return mixed
     */
    public function updateTenderInterviewInformationDiscussionTime($tenderId, $date, $time)
    {
        try
        {
            $model                = $this->findOrNewTenderInterviewInformationByTender($tenderId);
            $model->date_and_time = Carbon::createFromFormat('Y-m-d g:i A', $date . ' ' . $time);
            $success              = $model->save();
        }
        catch(\Exception $e)
        {
            $success = false;
        }

        return $success;
    }

    public function updateTenderInterview($tenderId, array $input)
    {
        $updateInterviewInformationSuccess = $this->updateTenderInterviewInformationDiscussionTime($tenderId, $input['date'], $input['discussionTime']);
        $updateInterviewSuccess            = $this->massUpdate($tenderId, $input['date'], $input['venue'], $input['companies']);

        return ( $updateInterviewInformationSuccess && $updateInterviewSuccess );
    }

}