<?php

use PCK\TrackRecordProject\TrackRecordProject;
use PCK\VendorCategory\VendorCategory;
use PCK\VendorWorkCategory\VendorWorkCategory;
use PCK\PropertyDeveloper\PropertyDeveloper;
use PCK\Settings\SystemSettings;
use PCK\VendorPreQualification\TemplateForm;
use PCK\VendorPreQualification\VendorPreQualification;
use PCK\Forms\TrackRecordProjectForm;
use Carbon\Carbon;
use PCK\VendorRegistration\Section;
use PCK\Helpers\ModuleAttachment;
use PCK\Base\Helpers;
use PCK\TrackRecordProject\ProjectTrackRecordSetting;
use PCK\VendorManagement\InstructionSetting;
use PCK\VendorRegistration\VendorCategoryTemporaryRecord;
use PCK\TrackRecordProject\TrackRecordProjectVendorWorkSubcategory;
use PCK\VendorRegistration\VendorRegistration;
use PCK\Countries\CountryRepository;
use PCK\ObjectLog\ObjectLog;

class ProjectTrackRecordController extends \BaseController {

    protected $trackRecordProjectForm;
    protected $countryRepository;

    public function __construct(TrackRecordProjectForm $trackRecordProjectForm, CountryRepository $countryRepository)
    {
        $this->trackRecordProjectForm = $trackRecordProjectForm;
        $this->countryRepository = $countryRepository;
    }

    public function index()
    {
        $user = \Confide::user();

        $records = TrackRecordProject::where('vendor_registration_id', '=', $user->company->vendorRegistration->id)
            ->orderBy('id', 'desc')
            ->get();

        $completedProjectsData = [];
        $currentProjectsData = [];

        foreach($records as $record)
        {
            $vendorWorkSubcategories = [];

            foreach($record->trackRecordProjectVendorWorkSubcategories as $trackRecordProjectVendorWorkSubcategory)
            {
                array_push($vendorWorkSubcategories, $trackRecordProjectVendorWorkSubcategory->vendorWorkSubcategory->name);
            }

            $row = [
                'id'                           => $record->id,
                'title'                        => $record->title,
                'propertyDeveloper'            => $record->propertyDeveloper ? $record->propertyDeveloper->name : $record->property_developer_text,
                'vendorCategory'               => $record->vendorCategory->name,
                'vendorWorkCategory'           => $record->vendorWorkCategory->name,
                'vendorSubWorkCategory'        => (count($vendorWorkSubcategories) > 0) ? implode(', ', $vendorWorkSubcategories) : '-',
                'route:edit'                   => route('vendors.vendorRegistration.projectTrackRecord.edit', $record->id),
                'route:delete'                 => route('vendors.vendorRegistration.projectTrackRecord.destroy', $record->id),
                'year_of_site_possession'      => Carbon::parse($record->year_of_site_possession)->format('Y'),
                'year_of_completion'           => Carbon::parse($record->year_of_completion)->format('Y'),
                'qlassic_year_of_achievement'  => is_null($record->qlassic_year_of_achievement) ? null : Carbon::parse($record->qlassic_year_of_achievement)->format('Y'),
                'conquas_year_of_achievement'  => is_null($record->conquas_year_of_achievement) ? null : Carbon::parse($record->conquas_year_of_achievement)->format('Y'),
                'year_of_recognition_awards'   => is_null($record->year_of_recognition_awards) ? null : Carbon::parse($record->year_of_recognition_awards)->format('Y'),
                'has_qlassic_or_conquas_score' => $record->has_qlassic_or_conquas_score,
                'awards_received'              => $record->awards_received,
                'qlassic_score'                => $record->qlassic_score,
                'conquas_score'                => $record->conquas_score,
                'project_amount'               => $record->project_amount,
                'currency'                     => $record->country->currency_code,
                'project_amount_remarks'       => $record->project_amount_remarks,
                'shassic_score'                => $record->shassic_score,
                'remarks'                      => $record->remarks,
                'route:getDownloads'           => route('vendors.vendorRegistration.projectTrackRecord.downloads.get', array($record->id)),
                'attachments_count'            => $record->attachments->count(),
            ];

            switch($record->type)
            {
                case TrackRecordProject::TYPE_CURRENT:
                    $currentProjectsData[] = $row;
                    break;
                case TrackRecordProject::TYPE_COMPLETED:
                    $completedProjectsData[] = $row;
                    break;
                default:
                    throw new Exception("Invalid type");
            }
        }

        $section = $user->company->vendorRegistration->getSection(Section::SECTION_PROJECT_TRACK_RECORD);

        $instructionSettings = InstructionSetting::first();

        return View::make('vendor_registration.track_record_projects.index', compact('completedProjectsData', 'currentProjectsData', 'section', 'instructionSettings'));
    }

    public function create()
    {
        $user = \Confide::user();

        $vendorGroup = $user->company->contractGroupCategory;

        $vendorCategories = VendorCategory::where('contract_group_category_id', '=', $vendorGroup->id)
            ->orderBy('name', 'asc')
            ->get();

        $vendorWorkCategoryId    = Input::old('vendor_work_category_id');
        $vendorWorkSubCategoryId = Input::old('vendor_work_subcategory_id[]');

        JavaScript::put(compact('vendorWorkCategoryId', 'vendorWorkSubCategoryId'));

        $propertyDeveloperIds = PropertyDeveloper::orderBy('name', 'asc')->where('hidden', '=', false)->lists('name', 'id');

        if( SystemSettings::getValue('allow_other_property_developers') ) $propertyDeveloperIds['others'] = trans('forms.othersPleaseSpecify');

        $typeOptions = [
            TrackRecordProject::TYPE_CURRENT => trans('vendorManagement.currentProjects'),
            TrackRecordProject::TYPE_COMPLETED => trans('vendorManagement.completedProjects'),
        ];

        $setting = ProjectTrackRecordSetting::first();

        $vendorCategorySelections = [];

        $temporaryVendorCagetoryIds = VendorCategoryTemporaryRecord::getTemporaryVendorCategoryIds($user->company->vendorRegistration);

        if(count($temporaryVendorCagetoryIds) > 0)
        {
            foreach($temporaryVendorCagetoryIds as $id)
            {
                array_push($vendorCategorySelections, VendorCategory::find($id));
            }
        }
        else
        {
            $vendorCategorySelections = $user->company->vendorCategories()->orderBy('id', 'ASC')->get();
        }

        $countryCurrencies = $this->countryRepository->getCountryCurrencies();

        $defaultCountryId = $this->countryRepository->getDefaultCountry()->id;

        return View::make('vendor_registration.track_record_projects.create', compact('propertyDeveloperIds', 'typeOptions', 'setting', 'vendorCategorySelections', 'countryCurrencies', 'defaultCountryId'));
    }

    public function processorCreate($vendorRegistrationId)
    {
        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);

        $vendorGroup = $vendorRegistration->company->contractGroupCategory;

        $vendorCategories = VendorCategory::where('contract_group_category_id', '=', $vendorGroup->id)
            ->orderBy('name', 'asc')
            ->get();

        $vendorWorkCategoryId    = Input::old('vendor_work_category_id');
        $vendorWorkSubCategoryId = Input::old('vendor_work_subcategory_id[]');

        JavaScript::put(compact('vendorWorkCategoryId', 'vendorWorkSubCategoryId'));

        $propertyDeveloperIds = PropertyDeveloper::orderBy('name', 'asc')->where('hidden', '=', false)->lists('name', 'id');

        if( SystemSettings::getValue('allow_other_property_developers') ) $propertyDeveloperIds['others'] = trans('forms.othersPleaseSpecify');

        $typeOptions = [
            TrackRecordProject::TYPE_CURRENT => trans('vendorManagement.currentProjects'),
            TrackRecordProject::TYPE_COMPLETED => trans('vendorManagement.completedProjects'),
        ];

        $setting = ProjectTrackRecordSetting::first();

        $vendorCategorySelections = [];

        $temporaryVendorCagetoryIds = VendorCategoryTemporaryRecord::getTemporaryVendorCategoryIds($vendorRegistration);

        if(count($temporaryVendorCagetoryIds) > 0)
        {
            foreach($temporaryVendorCagetoryIds as $id)
            {
                array_push($vendorCategorySelections, VendorCategory::find($id));
            }
        }
        else
        {
            $vendorCategorySelections = $vendorRegistration->company->vendorCategories()->orderBy('id', 'ASC')->get();
        }

        $countryCurrencies = $this->countryRepository->getCountryCurrencies();

        $defaultCountryId = $this->countryRepository->getDefaultCountry()->id;

        return View::make('vendor_management.approval.project_track_record.create', compact('propertyDeveloperIds', 'typeOptions', 'setting', 'vendorCategorySelections', 'vendorRegistration', 'countryCurrencies', 'defaultCountryId'));
    }

    public function store()
    {
        $user = \Confide::user();

        $input = Input::get();

        $input['vendor_work_subcategory_id']   = isset($input['vendor_work_subcategory_id']) ? $input['vendor_work_subcategory_id'] : [];
        $input['shassic_score']                = (trim($input['shassic_score']) == '') ? null : $input['shassic_score'];
        $input['has_qlassic_or_conquas_score'] = isset($input['has_qlassic_or_conquas_score']);

        $dateFields = [
            'year_of_site_possession',
            'year_of_completion',
            'qlassic_year_of_achievement',
            'conquas_year_of_achievement',
            'year_of_recognition_awards',
        ];

        foreach($dateFields as $dateField)
        {
            $input[$dateField] = empty($input[$dateField]) ? null : $input[$dateField];
        }

        $this->trackRecordProjectForm->validate($input);

        if( $input['property_developer_id'] == 'others' ) $input['property_developer_id'] = null;

        $input['vendor_registration_id'] = \Confide::user()->company->vendorRegistration->id;

        foreach($dateFields as $dateField)
        {
            $input[$dateField] = empty($input[$dateField]) ? null : Carbon::createFromDate($input[$dateField]);
        }

        $trackRecordProject = TrackRecordProject::create($input);

        TrackRecordProjectVendorWorkSubcategory::syncVendorWorkSubcategories($trackRecordProject, $input['vendor_work_subcategory_id']);

        ModuleAttachment::saveAttachments($trackRecordProject, $input);

        if($user->company->vendorRegistration->isSubmissionTypeNew() || $user->company->vendorRegistration->isSubmissionTypeUpdate() || $user->company->vendorRegistration->isSubmissionTypeRenewal())
        {
            VendorPreQualification::syncLatestForms($user->company->vendorRegistration);
        }

        if($user->company->vendorRegistration->isDraft() && $user->company->vendorRegistration->processor)
        {
            $this->markSectionAsAmended($user->company->vendorRegistration);
        }

        return Redirect::route('vendors.vendorRegistration.projectTrackRecord');
    }

    public function processorStore($vendorRegistrationId)
    {
        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);

        $input = Input::get();

        $input['vendor_work_subcategory_id']   = isset($input['vendor_work_subcategory_id']) ? $input['vendor_work_subcategory_id'] : [];
        $input['shassic_score']                = (trim($input['shassic_score']) == '') ? null : $input['shassic_score'];
        $input['has_qlassic_or_conquas_score'] = isset($input['has_qlassic_or_conquas_score']);

        $dateFields = [
            'year_of_site_possession',
            'year_of_completion',
            'qlassic_year_of_achievement',
            'conquas_year_of_achievement',
            'year_of_recognition_awards',
        ];

        foreach($dateFields as $dateField)
        {
            $input[$dateField] = empty($input[$dateField]) ? null : $input[$dateField];
        }

        $this->trackRecordProjectForm->validate($input);

        if( $input['property_developer_id'] == 'others' ) $input['property_developer_id'] = null;

        $input['vendor_registration_id'] = $vendorRegistration->id;

        foreach($dateFields as $dateField)
        {
            $input[$dateField] = empty($input[$dateField]) ? null : Carbon::createFromDate($input[$dateField]);
        }

        $trackRecordProject = TrackRecordProject::create($input);

        TrackRecordProjectVendorWorkSubcategory::syncVendorWorkSubcategories($trackRecordProject, $input['vendor_work_subcategory_id']);

        ModuleAttachment::saveAttachments($trackRecordProject, $input);

        if($vendorRegistration->isSubmissionTypeNew() || $vendorRegistration->isSubmissionTypeUpdate() || $vendorRegistration->isSubmissionTypeRenewal())
        {
            VendorPreQualification::syncLatestForms($vendorRegistration);
        }

        ObjectLog::recordAction($trackRecordProject->vendorRegistration, ObjectLog::ACTION_CREATE, ObjectLog::MODULE_VENDOR_REGISTRATION_PROJECT_TRACK_RECORD);

        return Redirect::route('vendorManagement.approval.projectTrackRecord', [$vendorRegistration->id]);
    }

    public function edit($trackRecordProjectId)
    {
        $trackRecordProject = TrackRecordProject::find($trackRecordProjectId);

        $trackRecordProject->year_of_site_possession     = Carbon::parse($trackRecordProject->year_of_site_possession)->format('Y');
        $trackRecordProject->year_of_completion          = Carbon::parse($trackRecordProject->year_of_completion)->format('Y');
        $trackRecordProject->qlassic_year_of_achievement = is_null($trackRecordProject->qlassic_year_of_achievement) ? null : Carbon::parse($trackRecordProject->qlassic_year_of_achievement)->format('Y');
        $trackRecordProject->conquas_year_of_achievement = is_null($trackRecordProject->conquas_year_of_achievement) ? null : Carbon::parse($trackRecordProject->conquas_year_of_achievement)->format('Y');
        $trackRecordProject->year_of_recognition_awards  = is_null($trackRecordProject->year_of_recognition_awards) ? null : Carbon::parse($trackRecordProject->year_of_recognition_awards)->format('Y');

        $user = \Confide::user();

        $vendorGroup = $user->company->contractGroupCategory;

        $vendorCategoryId           = Input::old('vendor_category_id') ?? $trackRecordProject->vendor_category_id;
        $vendorWorkCategoryId       = Input::old('vendor_work_category_id') ?? $trackRecordProject->vendor_work_category_id;
        $vendorWorkSubCategoryIds   = Input::old('vendor_work_subcategory_id[]') ?? $trackRecordProject->trackRecordProjectVendorWorkSubcategories->lists('vendor_work_subcategory_id');

        JavaScript::put(compact('vendorCategoryId', 'vendorWorkCategoryId', 'vendorWorkSubCategoryIds'));

        $propertyDeveloperIds = PropertyDeveloper::orderBy('name', 'asc')->where('hidden', '=', false)->lists('name', 'id');

        if( SystemSettings::getValue('allow_other_property_developers') ) $propertyDeveloperIds['others'] = trans('forms.othersPleaseSpecify');

        $uploadedFiles = $this->getAttachmentDetails($trackRecordProject);

        $typeOptions = [
            TrackRecordProject::TYPE_CURRENT => trans('vendorManagement.currentProjects'),
            TrackRecordProject::TYPE_COMPLETED => trans('vendorManagement.completedProjects'),
        ];

        $setting = ProjectTrackRecordSetting::first();

        $vendorCategorySelections = [];

        $temporaryVendorCagetoryIds = VendorCategoryTemporaryRecord::getTemporaryVendorCategoryIds($user->company->vendorRegistration);

        if(count($temporaryVendorCagetoryIds) > 0)
        {
            foreach($temporaryVendorCagetoryIds as $id)
            {
                array_push($vendorCategorySelections, VendorCategory::find($id));
            }
        }
        else
        {
            $vendorCategorySelections = $user->company->vendorCategories()->orderBy('id', 'ASC')->get();
        }

        $countryCurrencies = $this->countryRepository->getCountryCurrencies();

        return View::make('vendor_registration.track_record_projects.edit', compact('trackRecordProject', 'propertyDeveloperIds', 'uploadedFiles', 'typeOptions', 'setting', 'vendorCategorySelections', 'countryCurrencies'));
    }

    public function processorEdit($trackRecordProjectId)
    {
        $trackRecordProject = TrackRecordProject::find($trackRecordProjectId);

        $trackRecordProject->year_of_site_possession     = Carbon::parse($trackRecordProject->year_of_site_possession)->format('Y');
        $trackRecordProject->year_of_completion          = Carbon::parse($trackRecordProject->year_of_completion)->format('Y');
        $trackRecordProject->qlassic_year_of_achievement = is_null($trackRecordProject->qlassic_year_of_achievement) ? null : Carbon::parse($trackRecordProject->qlassic_year_of_achievement)->format('Y');
        $trackRecordProject->conquas_year_of_achievement = is_null($trackRecordProject->conquas_year_of_achievement) ? null : Carbon::parse($trackRecordProject->conquas_year_of_achievement)->format('Y');
        $trackRecordProject->year_of_recognition_awards  = is_null($trackRecordProject->year_of_recognition_awards) ? null : Carbon::parse($trackRecordProject->year_of_recognition_awards)->format('Y');

        $vendorRegistration = $trackRecordProject->vendorRegistration;

        $vendorGroup = $vendorRegistration->contractGroupCategory;

        $vendorCategoryId           = Input::old('vendor_category_id') ?? $trackRecordProject->vendor_category_id;
        $vendorWorkCategoryId       = Input::old('vendor_work_category_id') ?? $trackRecordProject->vendor_work_category_id;
        $vendorWorkSubCategoryIds   = Input::old('vendor_work_subcategory_id[]') ?? $trackRecordProject->trackRecordProjectVendorWorkSubcategories->lists('vendor_work_subcategory_id');

        JavaScript::put(compact('vendorCategoryId', 'vendorWorkCategoryId', 'vendorWorkSubCategoryIds'));

        $propertyDeveloperIds = PropertyDeveloper::orderBy('name', 'asc')->where('hidden', '=', false)->lists('name', 'id');

        if( SystemSettings::getValue('allow_other_property_developers') ) $propertyDeveloperIds['others'] = trans('forms.othersPleaseSpecify');

        $uploadedFiles = $this->getAttachmentDetails($trackRecordProject);

        $typeOptions = [
            TrackRecordProject::TYPE_CURRENT => trans('vendorManagement.currentProjects'),
            TrackRecordProject::TYPE_COMPLETED => trans('vendorManagement.completedProjects'),
        ];

        $setting = ProjectTrackRecordSetting::first();

        $vendorCategorySelections = [];

        $temporaryVendorCagetoryIds = VendorCategoryTemporaryRecord::getTemporaryVendorCategoryIds($vendorRegistration);

        if(count($temporaryVendorCagetoryIds) > 0)
        {
            foreach($temporaryVendorCagetoryIds as $id)
            {
                array_push($vendorCategorySelections, VendorCategory::find($id));
            }
        }
        else
        {
            $vendorCategorySelections = $vendorRegistration->company->vendorCategories()->orderBy('id', 'ASC')->get();
        }

        $countryCurrencies = $this->countryRepository->getCountryCurrencies();

        return View::make('vendor_management.approval.project_track_record.edit', compact('trackRecordProject', 'propertyDeveloperIds', 'uploadedFiles', 'typeOptions', 'setting', 'vendorCategorySelections', 'vendorRegistration', 'countryCurrencies'));
    }

    public function update($trackRecordProjectId)
    {
        $trackRecordProject = TrackRecordProject::find($trackRecordProjectId);
        $vendorRegistration = $trackRecordProject->vendorRegistration;

        $input = Input::get();

        $input['vendor_work_subcategory_id']   = isset($input['vendor_work_subcategory_id']) ? $input['vendor_work_subcategory_id'] : [];
        $input['shassic_score']                = (trim($input['shassic_score']) == '') ? null : $input['shassic_score'];
        $input['has_qlassic_or_conquas_score'] = isset($input['has_qlassic_or_conquas_score']);

        $dateFields = [
            'year_of_site_possession',
            'year_of_completion',
            'qlassic_year_of_achievement',
            'conquas_year_of_achievement',
            'year_of_recognition_awards',
        ];

        foreach($dateFields as $dateField)
        {
            $input[$dateField] = empty($input[$dateField]) ? null : $input[$dateField];
        }

        $this->trackRecordProjectForm->validate($input);

        if( $input['property_developer_id'] == 'others' ) $input['property_developer_id'] = null;

        foreach($dateFields as $dateField)
        {
            $input[$dateField] = empty($input[$dateField]) ? null : Carbon::createFromDate($input[$dateField]);
        }

        $trackRecordProject->update($input);

        TrackRecordProjectVendorWorkSubcategory::syncVendorWorkSubcategories($trackRecordProject, $input['vendor_work_subcategory_id']);

        ModuleAttachment::saveAttachments($trackRecordProject, $input);

        if($vendorRegistration->isSubmissionTypeNew() || $vendorRegistration->isSubmissionTypeUpdate() || $vendorRegistration->isSubmissionTypeRenewal())
        {
            VendorPreQualification::syncLatestForms($vendorRegistration);
        }

        if($vendorRegistration->isProcessing())
        {
            ObjectLog::recordAction($trackRecordProject->vendorRegistration, ObjectLog::ACTION_EDIT, ObjectLog::MODULE_VENDOR_REGISTRATION_PROJECT_TRACK_RECORD);

            return Redirect::route('vendorManagement.approval.projectTrackRecord', [$vendorRegistration->id]);
        }

        if($vendorRegistration->isDraft() && $vendorRegistration->processor)
        {
            $this->markSectionAsAmended($vendorRegistration);
        }

        return Redirect::route('vendors.vendorRegistration.projectTrackRecord');
    }

    public function destroy($recordId)
    {
        try
        {
            $record = TrackRecordProject::find($recordId);

            if($record->vendorRegistration->isSubmissionTypeNew() || $record->vendorRegistration->isSubmissionTypeUpdate() || $record->vendorRegistration->isSubmissionTypeRenewal())
            {
                VendorPreQualification::syncLatestForms($record->vendorRegistration);
            }

            $record->delete();

            if($record->vendorRegistration->isDraft() && $record->vendorRegistration->processor)
            {
                $this->markSectionAsAmended($record->vendorRegistration);
            }

            $user = \Confide::user();

            $isVendor = ($record->vendorRegistration->company->id == $user->company->id);

            if( ! $isVendor )
            {
                ObjectLog::recordAction($record->vendorRegistration, ObjectLog::ACTION_DELETE, ObjectLog::MODULE_VENDOR_REGISTRATION_PROJECT_TRACK_RECORD);
            }

            \Flash::success(trans('forms.deleted'));
        }
        catch(\Exception $e)
        {
            \Log::error($e->getMessage());
            \Log::error($e->getTraceAsString());
            \Flash::error(trans('forms.cannotBeDeleted:inUse'));
        }

        return Redirect::back();
    }

    public function resolve($vendorRegistrationId)
    {
        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);

        $section = $vendorRegistration->getSection(Section::SECTION_PROJECT_TRACK_RECORD);

        $section->amendment_status  = Section::AMENDMENT_STATUS_NOT_REQUIRED;
        $section->amendment_remarks = '';
        $section->save();

        return Redirect::back();
    }

    public function getAttachmentsList($trackRecordProjectId)
	{
        $trackRecordProject = TrackRecordProject::find($trackRecordProjectId);

		$uploadedFiles = $this->getAttachmentDetails($trackRecordProject);

		$data = array();

		foreach($uploadedFiles as $file)
		{
			$file['imgSrc']      = $file->generateThumbnailURL();
			$file['deleteRoute'] = $file->generateDeleteURL();
			$file['size']	     = Helpers::formatBytes($file->size);

			$data[] = $file;
		}

		return $data;
	}

    public function getDownloadList($trackRecordProjectId)
    {
        $trackRecordProject = TrackRecordProject::find($trackRecordProjectId);

        $uploadedFiles = $this->getAttachmentDetails($trackRecordProject);

        $data = array();

        foreach($uploadedFiles as $file)
        {
            $data[] = [
                'filename'    => $file->filename,
                'download_url' => $file->download_url,
                'uploaded_by'  => $file->createdBy->name,
                'uploaded_at'  => Carbon::parse($file->created_at)->format(\Config::get('dates.created_at')),
            ];
        }

        return $data;
    }

    public function markSectionAsAmended(VendorRegistration $vendorRegistration)
    {
        $section = $vendorRegistration->getSection(Section::SECTION_PROJECT_TRACK_RECORD);

        $section->amendment_status = Section::AMENDMENT_STATUS_MADE;
        $section->save();

        return true;
    }

    public function getActionLogs($vendorRegistrationId)
    {
        $vendorRegistration = VendorRegistration::find($vendorRegistrationId);

        $actionLogs = ObjectLog::getActionLogs($vendorRegistration, ObjectLog::MODULE_VENDOR_REGISTRATION_PROJECT_TRACK_RECORD);
        
        return Response::json($actionLogs);
    }
}