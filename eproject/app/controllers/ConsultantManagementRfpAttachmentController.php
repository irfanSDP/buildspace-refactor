<?php
use Carbon\Carbon;

use PCK\ConsultantManagement\ConsultantManagementContract;
use PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp;
use PCK\ConsultantManagement\ConsultantManagementAttachmentSetting;
use PCK\ConsultantManagement\ConsultantManagementExcludeAttachmentSetting;
use PCK\ConsultantManagement\ConsultantManagementRfpAttachmentSetting;
use PCK\ConsultantManagement\ConsultantManagementConsultantAttachment;
use PCK\ConsultantManagement\ConsultantManagementConsultantRfpAttachment;
use PCK\Users\User;
use PCK\Companies\Company;
use PCK\VendorCategory\VendorCategory;

use PCK\Helpers\ModuleAttachment;
use PCK\ObjectField\ObjectField;

use PCK\Forms\ConsultantManagement\RfpAttachmentSettingForm;

class ConsultantManagementRfpAttachmentController extends \BaseController
{
    private $rfpAttachmentSettingForm;

    public function __construct(RfpAttachmentSettingForm $rfpAttachmentSettingForm)
    {
        $this->rfpAttachmentSettingForm = $rfpAttachmentSettingForm;
    }

    public function index(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        return View::make('consultant_management.rfp_attachments.index', compact('vendorCategoryRfp'));
    }

    public function generalList(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $user = \Confide::user();

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = ConsultantManagementAttachmentSetting::select("consultant_management_attachment_settings.id AS id", "consultant_management_attachment_settings.title AS title",
        "consultant_management_attachment_settings.mandatory AS mandatory", "consultant_management_exclude_attachment_settings.id AS exclude_id")
        ->leftJoin('consultant_management_exclude_attachment_settings', function($join) use($vendorCategoryRfp){
            $join->on('consultant_management_exclude_attachment_settings.consultant_management_attachment_setting_id', '=', 'consultant_management_attachment_settings.id');
            $join->on('consultant_management_exclude_attachment_settings.vendor_category_rfp_id','=', \DB::raw($vendorCategoryRfp->id));
        })
        ->where('consultant_management_attachment_settings.consultant_management_contract_id', '=', $vendorCategoryRfp->consultant_management_contract_id);

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
                            $model->where('consultant_management_attachment_settings.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('consultant_management_attachment_settings.created_at', 'desc');

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
                'title'        => trim($record->title),
                'mandatory'    => $record->mandatory,
                'exclude'      => ($record->exclude_id) ? 'yes' : 'no',
                'route:update' => route('consultant.management.rfp.general.attachment.settings.store', [$vendorCategoryRfp->id])
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function generalSettingStore(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $user  = \Confide::user();
        $input = Input::all();

        $attachmentSetting = ConsultantManagementAttachmentSetting::findOrFail($input['id']);

        $success = false;
        switch($input['field'])
        {
            case 'exclude':
                $excludeAttachmentSetting = ConsultantManagementExcludeAttachmentSetting::where('consultant_management_attachment_setting_id', '=', $attachmentSetting->id)
                ->where('vendor_category_rfp_id', '=', $vendorCategoryRfp->id)
                ->first();

                if($input['val']=='yes' && !$excludeAttachmentSetting)
                {
                    $excludeAttachmentSetting = new ConsultantManagementExcludeAttachmentSetting;

                    $excludeAttachmentSetting->consultant_management_attachment_setting_id = $attachmentSetting->id;
                    $excludeAttachmentSetting->vendor_category_rfp_id = $vendorCategoryRfp->id;
                    $excludeAttachmentSetting->created_by = $user->id;
                    $excludeAttachmentSetting->updated_by = $user->id;

                    $excludeAttachmentSetting->save();
                }
                elseif($input['val']=='no' && $excludeAttachmentSetting)
                {
                    $excludeAttachmentSetting->delete();

                    $excludeAttachmentSetting = null;
                }

                $success = true;
                break;
        }

        return Response::json([
            'updated' => $success,
            'item' => [
                'id' => $attachmentSetting->id,
                'exclude' => ($excludeAttachmentSetting) ? 'yes' : 'no'
            ]
        ]);
    }

    public function rfpList(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $user = \Confide::user();

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = ConsultantManagementRfpAttachmentSetting::select("consultant_management_rfp_attachment_settings.id AS id", "consultant_management_rfp_attachment_settings.title AS title",
        "consultant_management_rfp_attachment_settings.mandatory AS mandatory", "consultant_management_rfp_attachment_settings.created_at AS created_at")
        ->join('consultant_management_vendor_categories_rfp', 'consultant_management_rfp_attachment_settings.vendor_category_rfp_id', '=', 'consultant_management_vendor_categories_rfp.id')
        ->where('consultant_management_vendor_categories_rfp.id', '=', $vendorCategoryRfp->id);

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
                            $model->where('consultant_management_rfp_attachment_settings.title', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('consultant_management_rfp_attachment_settings.created_at', 'desc');

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
                'title'        => trim($record->title),
                'mandatory'    => $record->mandatory,
                'created_at'   => Carbon::parse($record->created_at)->format('d/m/Y'),
                'route:show' => route('consultant.management.rfp.attachment.settings.show', [$vendorCategoryRfp->id, $record->id])
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function rfpSettingCreate(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $rfpAttachmentSetting = null;

        return View::make('consultant_management.rfp_attachments.edit', compact('vendorCategoryRfp', 'rfpAttachmentSetting'));
    }

    public function rfpSettingEdit(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $rfpAttachmentSettingId)
    {
        $rfpAttachmentSetting = ConsultantManagementRfpAttachmentSetting::findOrFail((int)$rfpAttachmentSettingId);

        return View::make('consultant_management.rfp_attachments.edit', compact('vendorCategoryRfp', 'rfpAttachmentSetting'));
    }

    public function rfpSettingShow(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $rfpAttachmentSettingId)
    {
        $rfpAttachmentSetting = ConsultantManagementRfpAttachmentSetting::findOrFail((int)$rfpAttachmentSettingId);
        $user  = \Confide::user();

        return View::make('consultant_management.rfp_attachments.show', compact('rfpAttachmentSetting', 'vendorCategoryRfp', 'user'));
    }

    public function rfpSettingStore(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $this->rfpAttachmentSettingForm->validate(Input::all());

        $user  = \Confide::user();
        $input = Input::all();

        $rfpAttachmentSetting = ConsultantManagementRfpAttachmentSetting::find($input['id']);

        if(!$rfpAttachmentSetting)
        {
            $rfpAttachmentSetting = new ConsultantManagementRfpAttachmentSetting();

            $rfpAttachmentSetting->vendor_category_rfp_id = $vendorCategoryRfp->id;
            $rfpAttachmentSetting->created_by = $user->id;
        }

        $rfpAttachmentSetting->title      = trim($input['title']);
        $rfpAttachmentSetting->mandatory  = $input['mandatory'];
        $rfpAttachmentSetting->updated_by = $user->id;

        $rfpAttachmentSetting->save();

        \Flash::success(trans('forms.saved'));

        return Redirect::route('consultant.management.rfp.attachment.settings.index', [$vendorCategoryRfp->id]);
    }

    public function rfpSettingDelete(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $rfpAttachmentSettingId)
    {
        $rfpAttachmentSetting = ConsultantManagementRfpAttachmentSetting::findOrFail((int)$rfpAttachmentSettingId);
        $user                 = \Confide::user();

        if($rfpAttachmentSetting->deletable())
        {
            $rfpAttachmentSetting->delete();
        }

        return Redirect::route('consultant.management.rfp.attachment.settings.index', [$vendorCategoryRfp->id]);
    }

    public function consultantAttachmentDirectoryList(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $companyId)
    {
        $company = Company::findOrFail($companyId);

        $attachmentSettings = ConsultantManagementAttachmentSetting::select("consultant_management_attachment_settings.id AS id", "consultant_management_attachment_settings.title AS title",
        "consultant_management_attachment_settings.mandatory AS mandatory")
        ->whereRaw("NOT EXISTS (
            SELECT 1
            FROM consultant_management_exclude_attachment_settings
            WHERE consultant_management_exclude_attachment_settings.consultant_management_attachment_setting_id = consultant_management_attachment_settings.id
            AND consultant_management_exclude_attachment_settings.vendor_category_rfp_id = ".$vendorCategoryRfp->id."
        )")
        ->where('consultant_management_attachment_settings.consultant_management_contract_id', '=', $vendorCategoryRfp->consultant_management_contract_id)
        ->orderBy('consultant_management_attachment_settings.title', 'asc')
        ->get();

        $rfpAttachmentSettings = ConsultantManagementRfpAttachmentSetting::select("consultant_management_rfp_attachment_settings.id AS id", "consultant_management_rfp_attachment_settings.title AS title",
        "consultant_management_rfp_attachment_settings.mandatory AS mandatory")
        ->join('consultant_management_vendor_categories_rfp', 'consultant_management_rfp_attachment_settings.vendor_category_rfp_id', '=', 'consultant_management_vendor_categories_rfp.id')
        ->where('consultant_management_vendor_categories_rfp.id', '=', $vendorCategoryRfp->id)
        ->orderBy('consultant_management_rfp_attachment_settings.title', 'asc')
        ->get();

        $directories = [];

        foreach($attachmentSettings as $attachmentSetting)
        {
            $directories[] = [
                'id'        => 'as-'.$attachmentSetting->id,
                'title'     => $attachmentSetting->title,
                'mandatory' => $attachmentSetting->mandatory,
                'type'      => 'dir',
                'extension' => 'Folder'
            ];
        }
        
        foreach($rfpAttachmentSettings as $attachmentSetting)
        {
            $directories[] = [
                'id'        => 'ras-'.$attachmentSetting->id,
                'title'     => $attachmentSetting->title,
                'mandatory' => $attachmentSetting->mandatory,
                'type'      => 'dir',
                'extension' => 'Folder'
            ];
        }

        return Response::json($directories);
    }

    public function consultantAttachmentList(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $companyId)
    {
        $company = Company::findOrFail($companyId);
        $request = Request::instance();

        $ids = explode('-', $request->get('id'));
        $isRfpAttachmentSetting = true;
        if($ids[0]=='as')
        {
            $isRfpAttachmentSetting = false;
            $attachmentSetting = ConsultantManagementAttachmentSetting::findOrFail($ids[1]);

            $model = ConsultantManagementConsultantAttachment::select('consultant_management_consultant_attachments.id', 'uploads.filename',
            'consultant_management_consultant_attachments.remarks', 'consultant_management_consultant_attachments.consultant_management_attachment_setting_id AS attachment_setting_id',
            'uploads.extension')
            ->join('object_fields', 'object_fields.object_id', '=', 'consultant_management_consultant_attachments.id')
            ->join('module_uploaded_files', 'module_uploaded_files.uploadable_id', '=', 'object_fields.id')
            ->join('uploads', 'uploads.id', '=', 'module_uploaded_files.upload_id')
            ->where('object_fields.object_type', '=', 'PCK\ConsultantManagement\ConsultantManagementConsultantAttachment')
            ->where('module_uploaded_files.uploadable_type', '=', 'PCK\ObjectField\ObjectField')
            ->where('consultant_management_consultant_attachments.consultant_management_attachment_setting_id', '=', $attachmentSetting->id)
            ->where('consultant_management_consultant_attachments.vendor_category_rfp_id', '=', $vendorCategoryRfp->id)
            ->where('consultant_management_consultant_attachments.company_id', '=', $company->id);
        }
        else
        {
            $attachmentSetting = ConsultantManagementRfpAttachmentSetting::findOrFail($ids[1]);

            $model = ConsultantManagementConsultantRfpAttachment::select('consultant_management_consultant_rfp_attachments.id', 'uploads.filename',
            'consultant_management_consultant_rfp_attachments.remarks',
            'consultant_management_consultant_rfp_attachments.consultant_management_rfp_attachment_setting_id AS attachment_setting_id',
            'uploads.extension')
            ->join('object_fields', 'object_fields.object_id', '=', 'consultant_management_consultant_rfp_attachments.id')
            ->join('module_uploaded_files', 'module_uploaded_files.uploadable_id', '=', 'object_fields.id')
            ->join('uploads', 'uploads.id', '=', 'module_uploaded_files.upload_id')
            ->where('object_fields.object_type', '=', 'PCK\ConsultantManagement\ConsultantManagementConsultantRfpAttachment')
            ->where('module_uploaded_files.uploadable_type', '=', 'PCK\ObjectField\ObjectField')
            ->where('consultant_management_consultant_rfp_attachments.consultant_management_rfp_attachment_setting_id', '=', $attachmentSetting->id)
            ->where('consultant_management_consultant_rfp_attachments.vendor_category_rfp_id', '=', $vendorCategoryRfp->id)
            ->where('consultant_management_consultant_rfp_attachments.company_id', '=', $company->id);
        }

        $uploadedFiles = $model->orderBy('uploads.filename', 'asc')->get();

        $attachments = [];
        
        foreach($uploadedFiles as $uploadedFile)
        {
            $attachments[] = [
                'id'                    => $uploadedFile->id,
                'title'                 => trim($uploadedFile->filename),
                'remarks'               => trim($uploadedFile->remarks),
                'attachment_setting_id' => ($isRfpAttachmentSetting) ? 'ras-'.$uploadedFile->attachment_setting_id : 'as-'.$uploadedFile->attachment_setting_id,
                'type'                  => 'file',
                'extension'             => $uploadedFile->extension,
                'deletable'             => $uploadedFile->deletable(),
                'route:download'        => route('consultant.management.consultant.attachment.download', [$vendorCategoryRfp->id, $company->id, ($isRfpAttachmentSetting) ? 'ras-'.$uploadedFile->id : 'as-'.$uploadedFile->id]),
                'route:delete'          => route('consultant.management.consultant.attachment.delete', [$vendorCategoryRfp->id, $company->id, ($isRfpAttachmentSetting) ? 'ras-'.$uploadedFile->id : 'as-'.$uploadedFile->id])
            ];
        }

        return Response::json($attachments);
    }

    public function consultantAttachmentUpload(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $companyId)
    {
        $company = Company::findOrFail($companyId);
        $request = Request::instance();
        $user    = \Confide::user();
        
        $uploadedFiles = $request->get('uploaded_files');

        $success = false;

        try
        {
            $ids = explode('-', $request->get('id'));
            if($ids[0]=='as')
            {
                $attachmentSetting = ConsultantManagementAttachmentSetting::findOrFail($ids[1]);

                if(is_array($uploadedFiles) && !empty($uploadedFiles))
                {
                    foreach($uploadedFiles as $uploadFile)
                    {
                        $consultantAttachment = new ConsultantManagementConsultantAttachment;

                        $consultantAttachment->consultant_management_attachment_setting_id = $attachmentSetting->id;
                        $consultantAttachment->vendor_category_rfp_id = $vendorCategoryRfp->id;
                        $consultantAttachment->company_id = $company->id;
                        $consultantAttachment->created_by = $user->id;
                        $consultantAttachment->updated_by = $user->id;

                        $consultantAttachment->save();

                        $object = ObjectField::findOrCreateNew($consultantAttachment, $consultantAttachment->getTable());
                        
                        ModuleAttachment::saveAttachments($object, ['uploaded_files' => [$uploadFile]]);
                    }
                }
            }
            else
            {
                $attachmentSetting = ConsultantManagementRfpAttachmentSetting::findOrFail($ids[1]);
                
                if(is_array($uploadedFiles) && !empty($uploadedFiles))
                {
                    foreach($uploadedFiles as $uploadFile)
                    {
                        $consultantAttachment = new ConsultantManagementConsultantRfpAttachment;

                        $consultantAttachment->consultant_management_rfp_attachment_setting_id = $attachmentSetting->id;
                        $consultantAttachment->vendor_category_rfp_id = $vendorCategoryRfp->id;
                        $consultantAttachment->company_id = $company->id;
                        $consultantAttachment->created_by = $user->id;
                        $consultantAttachment->updated_by = $user->id;

                        $consultantAttachment->save();

                        $object = ObjectField::findOrCreateNew($consultantAttachment, $consultantAttachment->getTable());
                        
                        ModuleAttachment::saveAttachments($object, ['uploaded_files' => [$uploadFile]]);
                    }
                }
            }

            $success = true;
        }
        catch (\Exception $e)
        {
            $success = false;
        }

        return [
            'success' => $success
        ];
    }

    public function consultantAttachmentDownload(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $companyId, $attachmentId)
    {
        $company = Company::findOrFail($companyId);
        $user    = \Confide::user();

        $ids = explode('-', $attachmentId);
        if($ids[0]=='as')
        {
            $attachment = ConsultantManagementConsultantAttachment::select('consultant_management_consultant_attachments.id', 'uploads.filename',
            'uploads.path', 'uploads.extension')
            ->join('object_fields', 'object_fields.object_id', '=', 'consultant_management_consultant_attachments.id')
            ->join('module_uploaded_files', 'module_uploaded_files.uploadable_id', '=', 'object_fields.id')
            ->join('uploads', 'uploads.id', '=', 'module_uploaded_files.upload_id')
            ->where('object_fields.object_type', '=', 'PCK\ConsultantManagement\ConsultantManagementConsultantAttachment')
            ->where('module_uploaded_files.uploadable_type', '=', 'PCK\ObjectField\ObjectField')
            ->where('consultant_management_consultant_attachments.id', '=', $ids[1])
            ->first();
        }
        else
        {
            $attachment = ConsultantManagementConsultantRfpAttachment::select('consultant_management_consultant_rfp_attachments.id', 'uploads.filename',
            'uploads.path', 'uploads.extension')
            ->join('object_fields', 'object_fields.object_id', '=', 'consultant_management_consultant_rfp_attachments.id')
            ->join('module_uploaded_files', 'module_uploaded_files.uploadable_id', '=', 'object_fields.id')
            ->join('uploads', 'uploads.id', '=', 'module_uploaded_files.upload_id')
            ->where('object_fields.object_type', '=', 'PCK\ConsultantManagement\ConsultantManagementConsultantRfpAttachment')
            ->where('module_uploaded_files.uploadable_type', '=', 'PCK\ObjectField\ObjectField')
            ->where('consultant_management_consultant_rfp_attachments.id', '=',$ids[1])
            ->first();
        }

        $filepath = base_path().DIRECTORY_SEPARATOR.$attachment->path.DIRECTORY_SEPARATOR.$attachment->filename;

        return \PCK\Helpers\Files::download($filepath, $attachment->filename);
    }

    public function consultantAttachmentDelete(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $companyId, $attachmentId)
    {
        $company = Company::findOrFail($companyId);
        $user    = \Confide::user();

        $ids = explode('-', $attachmentId);

        if($ids[0]=='as')
        {
            $attachment = ConsultantManagementConsultantAttachment::findOrFail($ids[1]);
        }
        else
        {
            $attachment = ConsultantManagementConsultantRfpAttachment::findOrFail($ids[1]);
        }

        try
        {
            if($attachment->deletable())
            {
                $attachment->delete();
            }

            $success = true;
        }
        catch(\Exception $e)
        {
            $success = false;
        }
        
        return [
            'success' => $success
        ];
    }

    public function consultantAttachmentUploadedList(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $companyId)
    {
        $company = Company::findOrFail($companyId);
        $request = Request::instance();

        $attachmentSettings = ConsultantManagementAttachmentSetting::select("consultant_management_attachment_settings.id AS id", "consultant_management_attachment_settings.title AS title",
        "consultant_management_attachment_settings.mandatory AS mandatory")
        ->whereRaw("NOT EXISTS (
            SELECT 1
            FROM consultant_management_exclude_attachment_settings
            WHERE consultant_management_exclude_attachment_settings.consultant_management_attachment_setting_id = consultant_management_attachment_settings.id
            AND consultant_management_exclude_attachment_settings.vendor_category_rfp_id = ".$vendorCategoryRfp->id."
        )")
        ->where('consultant_management_attachment_settings.consultant_management_contract_id', '=', $vendorCategoryRfp->consultant_management_contract_id)
        ->orderBy('consultant_management_attachment_settings.title', 'asc')
        ->get();

        $uploadedFiles = ConsultantManagementConsultantAttachment::select('consultant_management_consultant_attachments.id', 'uploads.filename',
        'consultant_management_consultant_attachments.remarks', 'consultant_management_consultant_attachments.consultant_management_attachment_setting_id AS attachment_setting_id')
        ->join('object_fields', 'object_fields.object_id', '=', 'consultant_management_consultant_attachments.id')
        ->join('module_uploaded_files', 'module_uploaded_files.uploadable_id', '=', 'object_fields.id')
        ->join('uploads', 'uploads.id', '=', 'module_uploaded_files.upload_id')
        ->where('object_fields.object_type', '=', 'PCK\ConsultantManagement\ConsultantManagementConsultantAttachment')
        ->where('module_uploaded_files.uploadable_type', '=', 'PCK\ObjectField\ObjectField')
        ->where('consultant_management_consultant_attachments.vendor_category_rfp_id', '=', $vendorCategoryRfp->id)
        ->where('consultant_management_consultant_attachments.company_id', '=', $company->id)
        ->orderBy('uploads.filename', 'asc')
        ->get();

        $generalAttachments = [];
        foreach($uploadedFiles as $uploadedFile)
        {
            if(!array_key_exists($uploadedFile->attachment_setting_id, $generalAttachments))
            {
                $generalAttachments[$uploadedFile->attachment_setting_id] = [];
            }

            $generalAttachments[$uploadedFile->attachment_setting_id][] = $uploadedFile;
        }

        $rfpAttachmentSettings = ConsultantManagementRfpAttachmentSetting::select("consultant_management_rfp_attachment_settings.id AS id", "consultant_management_rfp_attachment_settings.title AS title",
        "consultant_management_rfp_attachment_settings.mandatory AS mandatory")
        ->join('consultant_management_vendor_categories_rfp', 'consultant_management_rfp_attachment_settings.vendor_category_rfp_id', '=', 'consultant_management_vendor_categories_rfp.id')
        ->where('consultant_management_vendor_categories_rfp.id', '=', $vendorCategoryRfp->id)
        ->orderBy('consultant_management_rfp_attachment_settings.title', 'asc')
        ->get();

        $uploadedFiles = ConsultantManagementConsultantRfpAttachment::select('consultant_management_consultant_rfp_attachments.id', 'uploads.filename',
        'consultant_management_consultant_rfp_attachments.remarks',
        'consultant_management_consultant_rfp_attachments.consultant_management_rfp_attachment_setting_id AS attachment_setting_id')
        ->join('object_fields', 'object_fields.object_id', '=', 'consultant_management_consultant_rfp_attachments.id')
        ->join('module_uploaded_files', 'module_uploaded_files.uploadable_id', '=', 'object_fields.id')
        ->join('uploads', 'uploads.id', '=', 'module_uploaded_files.upload_id')
        ->where('object_fields.object_type', '=', 'PCK\ConsultantManagement\ConsultantManagementConsultantRfpAttachment')
        ->where('module_uploaded_files.uploadable_type', '=', 'PCK\ObjectField\ObjectField')
        ->where('consultant_management_consultant_rfp_attachments.vendor_category_rfp_id', '=', $vendorCategoryRfp->id)
        ->where('consultant_management_consultant_rfp_attachments.company_id', '=', $company->id)
        ->orderBy('uploads.filename', 'asc')
        ->get();

        $rfpAttachments = [];
        foreach($uploadedFiles as $uploadedFile)
        {
            if(!array_key_exists($uploadedFile->attachment_setting_id, $rfpAttachments))
            {
                $rfpAttachments[$uploadedFile->attachment_setting_id] = [];
            }

            $rfpAttachments[$uploadedFile->attachment_setting_id][] = $uploadedFile;
        }

        $data = [];

        foreach($attachmentSettings as $attachmentSetting)
        {
            $parent = [
                'id'               => 'as-'.$attachmentSetting->id,
                'title'            => trim($attachmentSetting->title),
                'mandatory'        => $attachmentSetting->mandatory,
                'type'             => 'folder',
                'total_attachment' => 0,
                '_children'        => [],
                'route:download'   => null,
            ];

            if(array_key_exists($attachmentSetting->id, $generalAttachments))
            {
                foreach($generalAttachments[$attachmentSetting->id] as $attachment)
                {
                    $parent['_children'][] = [
                        'id'             => 'ca-'.$attachment->id,
                        'title'          => trim($attachment->filename),
                        'mandatory'      => $attachmentSetting->mandatory,
                        'type'           => 'file',
                        'route:download' => route('consultant.management.consultant.attachment.download', [$vendorCategoryRfp->id, $company->id, 'as-'.$attachment->id])
                    ];
                }

                $parent['total_attachment'] = count($generalAttachments[$attachmentSetting->id]);
            }

            $data[] = $parent;
        }

        foreach($rfpAttachmentSettings as $attachmentSetting)
        {
            $parent = [
                'id'               => 'ras-'.$attachmentSetting->id,
                'title'            => trim($attachmentSetting->title),
                'mandatory'        => $attachmentSetting->mandatory,
                'type'             => 'folder',
                'total_attachment' => 0,
                '_children'        => [],
                'route:download'   => null,
            ];

            if(array_key_exists($attachmentSetting->id, $rfpAttachments))
            {
                foreach($rfpAttachments[$attachmentSetting->id] as $attachment)
                {
                    $parent['_children'][] = [
                        'id'             => 'cra-'.$attachment->id,
                        'title'          => trim($attachment->filename),
                        'mandatory'      => $attachmentSetting->mandatory,
                        'type'           => 'file',
                        'route:download' => route('consultant.management.consultant.attachment.download', [$vendorCategoryRfp->id, $company->id, 'ras-'.$attachment->id])
                    ];
                }

                $parent['total_attachment'] = count($rfpAttachments[$attachmentSetting->id]);
            }

            $data[] = $parent;
        }
        
        return Response::json($data);
    }
}