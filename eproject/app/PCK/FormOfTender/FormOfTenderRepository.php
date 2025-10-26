<?php namespace PCK\FormOfTender;

use Carbon\Carbon;
use Confide;
use DB;
use Illuminate\Database\Eloquent\Collection;
use PCK\Companies\CompanyRepository;
use PCK\ContractGroups\Types\Role;
use PCK\Projects\Project;
use PCK\TenderDocumentFolders\TenderDocumentFolder;
use PCK\Tenders\Tender;
use PCK\Users\User;
use PCK\FormOfTender\FormOfTender;

class FormOfTenderRepository {

    use ClauseRepositoryTrait, TenderAlternativeRepositoryTrait;

    private $companyRepository;

    public function __construct(CompanyRepository $companyRepository)
    {
        $this->companyRepository = $companyRepository;
    }

    public function getAllComponents($formOfTenderId)
    {
        $formOfTender = FormOfTender::find($formOfTenderId);

        return array(
            'header'      => $formOfTender->header,
            'address'     => $formOfTender->address,
            'clauses'     => $this->getClausesAndTenderAlternativesMarkers($formOfTenderId),
            'settings'    => $formOfTender->printSettings,
        );
    }

    /**
     * Get all tenders under the same project.
     *
     * @param $tenderId
     *
     * @return mixed
     */
    public function getTendersOfSameProject($tenderId)
    {
        $project        = Tender::find($tenderId)->project;
        return $project->tenders->sortByDesc('count');
    }

    /**
     * Replicates a resource and associates it with the current form of tender.
     *
     * @param $template
     * @param $formOfTenderId
     *
     * @return mixed
     */
    public function copyFrom($template, $formOfTenderId)
    {
        $resource = $template->replicate();

        $resource->form_of_tender_id = $formOfTenderId;
        $resource->save();

        return $resource;
    }

    /**
     * Saves the Print Settings.
     *
     * @param $settings
     * @param $input
     *
     * @return mixed
     */
    public function savePrintSettings($settings, $input)
    {
        $settings->margin_top          = $input['margin_top'];
        $settings->margin_bottom       = $input['margin_bottom'];
        $settings->margin_left         = $input['margin_left'];
        $settings->margin_right        = $input['margin_right'];
        $settings->include_header_line = ( $input['include_header_line'] == 1 ) ? true : false;
        $settings->header_spacing      = $input['header_spacing'];
        $settings->footer_text         = $input['footer_text'];
        $settings->footer_font_size    = $input['footer_font_size'];
        $settings->font_size           = $input['font_size'];
        $settings->title_text          = $input['title_text'];

        return $settings->save();
    }

    /**
     * Get the resource of the latest tender (out of all previous tenders) of the tender's project.
     * (Think 'next elder sibling').
     *
     * @param $resourceIdentifier
     * @param $tenderId
     *
     * @return mixed
     */
    // public function getLatestOrNewResource($resourceIdentifier, $tenderId)
    public function getPreviousTendeResource($resourceIdentifier, $tenderId)
    {
        $thisTender     = Tender::find($tenderId);
        $projectTenders = $this->getTendersOfSameProject($tenderId); //descending order

        // Get latest.
        foreach($projectTenders as $tender)
        {
            if( $tender->count > $thisTender->count ) continue;

            $template = $this->getResourceByFormOfTenderId($resourceIdentifier, $tender->formOfTender->id);

            if( isset( $template ) && $template->count() > 0 ) break;
        }

        return $template;
    }

    public function getResourceByFormOfTenderId($resourceIdentifier, $formOfTenderId)
    {
        $formOfTender = FormOfTender::find($formOfTenderId);

        switch($resourceIdentifier)
        {
            case Constants::HEADER:
                $resource = $formOfTender->header;
                break;
            case Constants::ADDRESS:
                $resource = $formOfTender->address;
                break;
            case Constants::CLAUSES:
                $resource = $this->findClausesByFormOfTenderId($formOfTenderId);
                break;
            case Constants::PRINT_SETTINGS:
                $resource = $formOfTender->printSettings;
                break;
            case Constants::TENDER_ALTERNATIVES:
                $resource = $this->findTenderAlternativesByFormOfTenderId($formOfTenderId);
                break;
            case Constants::TENDER_ALTERNATIVES_POSITION:
                $resource = $formOfTender->tenderAlternativePositions;
                break;
            default:
                throw new \Exception("No resource with that name exists");
        }

        return $resource;
    }

    /**
     * Adds an entry to the log.
     *
     * @param      $formOfTenderId
     *
     * @return bool
     */
    public function addLogEntry($formOfTenderId)
    {
        $log = new Log;
        $log->user()->associate(Confide::user());
        $log->form_of_tender_id = $formOfTenderId;

        return $log->save();
    }

    /**
     * Get all TenderDocumentFolders that start with the Default Addendum Folder Name.
     *
     * @param $tenderId
     *
     * @return mixed
     */
    public function getTenderAddendumFolders($tenderId)
    {
        $projectId    = Tender::find($tenderId)->project->id;

        $bqFileRecord = TenderDocumentFolder::where('project_id', '=', $projectId)
            ->where('name', '=', TenderDocumentFolder::DEFAULT_BQ_FILES_FOLDER_NAME)
            ->where('system_generated_folder', '=', true)
            ->first();

        return TenderDocumentFolder::where('project_id', '=', $projectId)
            ->where('name', 'ILIKE', TenderDocumentFolder::DEFAULT_ADDENDUM_FOLDER_NAME . '%')
            ->where('system_generated_folder', '=', true)
            ->where('root_id', '=', $bqFileRecord->id)
            ->orderBy('id', 'ASC')
            ->get();
    }

    /**
     * Get all Clauses and Tender Alternatives position markers for the tender's form of tender.
     *
     * @param      $formOfTenderId
     * @param bool $isTemplate
     * @param bool $isNew
     *
     * @return array
     */
    public function getClausesAndTenderAlternativesMarkers($formOfTenderId)
    {
        $formOfTender = FormOfTender::find($formOfTenderId);

        $regularClauses = $this->findClausesByFormOfTenderId($formOfTenderId);

        $tenderAlternativesMarkers = $formOfTender->tenderAlternativePositions;

        return $this->sortClausesAndTenderAlternativesMarkers($regularClauses, $tenderAlternativesMarkers);
    }

    /**
     * Arranges the Clauses and Tender Alternatives markers in the correct (i.e. preset) order.
     *
     * @param $tenderAlternativesMarkers
     * @param $regularClauses
     *
     * @return array
     */
    private function sortClausesAndTenderAlternativesMarkers($regularClauses, $tenderAlternativesMarkers = null)
    {
        $parentClauses = new Collection();

        if( ! $tenderAlternativesMarkers ) $tenderAlternativesMarkers = new Collection();

        while( ( $tenderAlternativesMarkers->count() != 0 ) || ( $regularClauses->count() != 0 ) )
        {
            $parentClauses = $this->pushNext($parentClauses, $regularClauses, $tenderAlternativesMarkers);
        }

        return $parentClauses;
    }

    /**
     * Pushes the correct (in terms of order) item into the array.
     *
     * @param $parentClauses
     * @param $regularClauses
     * @param $tenderAlternativesMarkers
     *
     * @return mixed
     */
    private function pushNext($parentClauses, &$regularClauses, &$tenderAlternativesMarkers)
    {
        $currentKey = count($parentClauses);
        if( ( $tenderAlternativesMarkers->first() != null ) && ( $tenderAlternativesMarkers->first()->position == $currentKey ) )
        {
            $parentClauses->push($tenderAlternativesMarkers->shift());
        }
        elseif( $regularClauses->first() != null )
        {

            $parentClauses->push($regularClauses->shift());
        }
        elseif( $regularClauses->count() == 0 )
        {
            $parentClauses->push($tenderAlternativesMarkers->shift());
        }

        return $parentClauses;
    }

    /**
     * Determines if the form of tender is editable.
     *
     * @param      $tenderId
     *
     * @param bool $isTemplate
     *
     * @return mixed
     */
    public function tenderIsEditable($tenderId, $isTemplate = false)
    {
        if( $isTemplate ) return true;

        return in_array(Tender::find($tenderId)->current_form_type, array( Project::STATUS_TYPE_RECOMMENDATION_OF_TENDERER, Project::STATUS_TYPE_LIST_OF_TENDERER ));
    }

    /**
     * Updates the header resource and logs the update.
     *
     * @param $header
     * @param $contentData
     */
    public function updateHeader($header, $contentData)
    {
        $header->header_text = $contentData;
        $header->save();

        $this->addLogEntry($header->formOfTender->id);
    }

    /**
     * Updates the address resource and logs the update.
     *
     * @param $address
     * @param $contentData
     */
    public function updateAddress($address, $contentData)
    {
        $address->address = $contentData;
        $address->save();

        $this->addLogEntry($address->formOfTender->id);
    }

    /**
     * Checks if the current user has permission to edit.
     * True if user is an 'editor'.
     *
     * @param      $tenderId
     * @param bool $isTemplate
     *
     * @return mixed
     */
    public function isAuthorisedToEdit($tenderId, $isTemplate = false)
    {
        if( $isTemplate ) return true;

        $tender  = Tender::find($tenderId);
        $project = Project::find($tender->project_id);

        $user = Confide::user();

        $result = DB::select('SELECT is_contract_group_project_owner FROM contract_group_project_users WHERE user_id = :user_id AND project_id = :project_id', array(
            'user_id'    => $user->id,
            'project_id' => $project->id,
        ));

        return ( isset( $result[0] ) ? $result[0]->is_contract_group_project_owner : false );
    }

    /**
     * Checks if the form of tender is editable.
     *
     * @param      $tenderId
     * @param bool $isTemplate
     *
     * @return bool
     */
    public function isEditable($tenderId, $isTemplate = false)
    {
        $authorisedToEdit = $this->isAuthorisedToEdit($tenderId, $isTemplate);

        $tenderIsEditable = $this->tenderIsEditable($tenderId, $isTemplate);

        return ( $authorisedToEdit && $tenderIsEditable );
    }

    /**
     * Returns an array of all groups with full access to Form Of Tender.
     *
     * @param $tenderId
     *
     * @return array
     */
    public function getFullAccessGroups($tenderId)
    {
        return array(
            Role::PROJECT_OWNER,
            Role::GROUP_CONTRACT,
            Tender::find($tenderId)->project->getCallingTenderRole(),
        );
    }

    /**
     * Determines if the Form of Tender with contractor input is at a stage where it can be viewed (by the developers).
     *
     * @param $tenderId
     *
     * @return bool
     */
    public function completedFormIsNowViewable($tenderId)
    {
        $tender = Tender::find($tenderId);

        $tenderClosingDate = Carbon::parse($tender->tender_closing_date);
        $now               = Carbon::now();

        $viewable = false;

        if( ( $tender->open_tender_status == Tender::OPEN_TENDER_STATUS_OPENED ) && ( $now->gte($tenderClosingDate) ) )
        {
            $viewable = true;
        }

        return $viewable;
    }

    /**
     * Checks if the current user has permission to view the Form of Tender.
     * Checks by group (contract group).
     *
     * @param $companyId
     *
     * @param $tenderId
     *
     * @return bool
     */
    public function hasPermissionToView($companyId, $tenderId)
    {
        $user      = Confide::user();
        $tender    = Tender::find($tenderId);
        $userGroup = $user->getAssignedCompany($tender->project)->getContractGroup($tender->project)->group;

        if( in_array($userGroup, $this->getFullAccessGroups($tenderId)) )
        {
            return $this->completedFormIsNowViewable($tenderId);
        }

        if( $userGroup == Role::CONTRACTOR )
        {
            // Must be from the same company to view.
            if( $user->getAssignedCompany($tender->project)->id == $companyId ) return true;
        }

        return false;
    }

    public function canViewBlankFormOfTender(Tender $tender, User $user)
    {
        if( $this->canEditFormOfTender($tender->project, $user) ) return true;

        if( $user->getAssignedCompany($tender->project)->getContractGroup($tender->project)->group == Role::CONTRACTOR ) return true;

        return false;
    }

    public function canEditFormOfTender(Project $project, User $user)
    {
        $allowed = ( $company = $user->getAssignedCompany($project) ) && $user->getAssignedCompany($project)->contractGroupCategory->includesContractGroups(array(
                \PCK\ContractGroups\ContractGroup::getIdByGroup(\PCK\ContractGroups\Types\Role::PROJECT_OWNER),
                \PCK\ContractGroups\ContractGroup::getIdByGroup(\PCK\ContractGroups\Types\Role::GROUP_CONTRACT),
            ));

        return $allowed || $user->hasCompanyProjectRole($project, $project->getCallingTenderRole());
    }

    public function createNewResources(Tender $tender, $templateId = null)
    {
        $formOfTender = new FormOfTender();
        $formOfTender->tender_id = $tender->id;
        $formOfTender->is_template = false;
        $formOfTender->save();

        // header
        $headerTemplate = $this->getPreviousTendeResource(Constants::HEADER, $tender->id);
        $headerText = $headerTemplate ? $headerTemplate->header_text : $tender->project->title;
        
        $formOfTenderHeader = new Header();
        $formOfTenderHeader->form_of_tender_id = $formOfTender->id;
        $formOfTenderHeader->header_text = $headerText;
        $formOfTenderHeader->save();

        // address
        $addressTemplate = $this->getPreviousTendeResource(Constants::ADDRESS, $tender->id);

        if(!$addressTemplate)
        {
            $addressTemplate = $this->getResourceByFormOfTenderId(Constants::ADDRESS, $templateId);
        }

        $this->copyFrom($addressTemplate, $formOfTender->id);

        // print settings
        $printSettingsTemplate = $this->getPreviousTendeResource(Constants::PRINT_SETTINGS, $tender->id);

        if(!$printSettingsTemplate)
        {
            $printSettingsTemplate = $this->getResourceByFormOfTenderId(Constants::PRINT_SETTINGS, $templateId);
        }

        $this->copyFrom($printSettingsTemplate, $formOfTender->id);

        // clauses
        $clausesTemplate = $this->getPreviousTendeResource(Constants::CLAUSES, $tender->id);
        
        if(count($clausesTemplate) < 1)
        {
            $clausesTemplate = $this->getResourceByFormOfTenderId(Constants::CLAUSES, $templateId);
        }
        
        $this->copyClausesFrom($clausesTemplate, $formOfTender->id);

        // tender alternatives
        $tenderAlternativeTemplates = $this->getPreviousTendeResource(Constants::TENDER_ALTERNATIVES, $tender->id);

        if(count($tenderAlternativeTemplates) < 1)
        {
            $tenderAlternativeTemplates = $this->getResourceByFormOfTenderId(Constants::TENDER_ALTERNATIVES, $templateId);
        }

        foreach($tenderAlternativeTemplates as $template)
        {
            $formOfTenderTenderAlternative = new TenderAlternative();
            $formOfTenderTenderAlternative->form_of_tender_id = $formOfTender->id;
            $formOfTenderTenderAlternative->tender_alternative_class_name = $template->tender_alternative_class_name;
            $formOfTenderTenderAlternative->custom_description = $template->custom_description;
            $formOfTenderTenderAlternative->show = $template->show;
            $formOfTenderTenderAlternative->save();
        }

        // tender alternative positions
        $tenderAlternativePositionTemplates = $this->getPreviousTendeResource(Constants::TENDER_ALTERNATIVES_POSITION, $tender->id);

        if($templateId && count($tenderAlternativePositionTemplates) < 1)
        {
            $tenderAlternativePositionTemplates = $this->getResourceByFormOfTenderId(Constants::TENDER_ALTERNATIVES_POSITION, $templateId);
        }

        foreach($tenderAlternativePositionTemplates as $template)
        {
            $formOfTenderTenderAlternativePosition = new TenderAlternativesPosition();
            $formOfTenderTenderAlternativePosition->form_of_tender_id = $formOfTender->id;
            $formOfTenderTenderAlternativePosition->position = $template->position;
            $formOfTenderTenderAlternativePosition->save();
        }
    }

    public function createNewTemplate($inputs)
    {
        $formOfTender = new FormOfTender();
        $formOfTender->tender_id = null;
        $formOfTender->is_template = true;
        $formOfTender->name = $inputs['name'];
        $formOfTender->save();

        $formOfTenderHeader = new Header();
        $formOfTenderHeader->form_of_tender_id = $formOfTender->id;
        $formOfTenderHeader->header_text = Header::DEFAULT_TEXT;
        $formOfTenderHeader->save();

        $formOfTenderAddress = new Address();
        $formOfTenderAddress->form_of_tender_id = $formOfTender->id;
        $formOfTenderAddress->address = Address::DEFAULT_TEXT;
        $formOfTenderAddress->save();

        $formOfTenderClause = new Clause();
        $formOfTenderClause->form_of_tender_id = $formOfTender->id;
        $formOfTenderClause->clause = Clause::DEFEAULT_TEXT;
        $formOfTenderClause->parent_id = 0;
        $formOfTenderClause->sequence_number = 1;
        $formOfTenderClause->save();

        $formOfTenderPrintSettings = new PrintSettings();
        $formOfTenderPrintSettings->form_of_tender_id = $formOfTender->id;
        $formOfTenderPrintSettings->margin_top = PrintSettings::DEFAULT_MARGIN;
        $formOfTenderPrintSettings->margin_bottom = PrintSettings::DEFAULT_MARGIN;
        $formOfTenderPrintSettings->margin_left = PrintSettings::DEFAULT_MARGIN;
        $formOfTenderPrintSettings->margin_right = PrintSettings::DEFAULT_MARGIN;
        $formOfTenderPrintSettings->include_header_line = PrintSettings::DEFAULT_INCLUDE_HEADER_LINE;
        $formOfTenderPrintSettings->header_spacing = PrintSettings::DEFAULT_HEADER_SPACING;
        $formOfTenderPrintSettings->footer_text = PrintSettings::DEFAULT_FOOTER_TEXT;
        $formOfTenderPrintSettings->footer_font_size = PrintSettings::DEFAULT_FOOTER_FONT_SIZE;
        $formOfTenderPrintSettings->font_size = PrintSettings::DEFAULT_FONT_SIZE;
        $formOfTenderPrintSettings->title_text = PrintSettings::DEFAULT_TITLE;
        $formOfTenderPrintSettings->save();

        $this->createNewEmptyTenderAlternatives($formOfTender->id);
    }
}