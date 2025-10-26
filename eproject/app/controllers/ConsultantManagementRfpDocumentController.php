<?php
use Carbon\Carbon;

use PCK\ConsultantManagement\ConsultantManagementContract;
use PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp;
use PCK\ConsultantManagement\RfpDocument;
use PCK\Companies\Company;
use PCK\Users\User;

use PCK\Helpers\ModuleAttachment;
use PCK\ObjectField\ObjectField;

use PCK\Forms\ConsultantManagement\CallingRfpForm;
use PCK\Forms\ConsultantManagement\GeneralVerifyForm;

class ConsultantManagementRfpDocumentController extends \BaseController
{
    private $callingRfpForm;
    private $generalVerifyForm;

    public function __construct(CallingRfpForm $callingRfpForm, GeneralVerifyForm $generalVerifyForm)
    {
        $this->callingRfpForm = $callingRfpForm;
        $this->generalVerifyForm = $generalVerifyForm;
    }

    public function index(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $user = \Confide::user();
        $consultantManagementContract = $vendorCategoryRfp->consultantManagementContract;

        if(!$user->isConsultantManagementEditorByRole($consultantManagementContract, ConsultantManagementContract::ROLE_RECOMMENDATION_OF_CONSULTANT) and
        !$user->isConsultantManagementEditorByRole($consultantManagementContract, ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT))
        {
            return View::make('errors/404');
        }

        return View::make('consultant_management.rfp_documents.index', compact('vendorCategoryRfp', 'user'));
    }

    public function list(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $request = Request::instance();

        $model = RfpDocument::select('consultant_management_rfp_documents.id', 'uploads.filename',
            'consultant_management_rfp_documents.remarks',
            'consultant_management_rfp_documents.vendor_category_rfp_id',
            'uploads.extension')
            ->join('object_fields', 'object_fields.object_id', '=', 'consultant_management_rfp_documents.id')
            ->join('module_uploaded_files', 'module_uploaded_files.uploadable_id', '=', 'object_fields.id')
            ->join('uploads', 'uploads.id', '=', 'module_uploaded_files.upload_id')
            ->where('object_fields.object_type', '=', 'PCK\ConsultantManagement\RfpDocument')
            ->where('module_uploaded_files.uploadable_type', '=', 'PCK\ObjectField\ObjectField')
            ->where('consultant_management_rfp_documents.vendor_category_rfp_id', '=', $vendorCategoryRfp->id);

        $uploadedFiles = $model->orderBy('uploads.filename', 'asc')->get();

        $documents = [];
        
        foreach($uploadedFiles as $uploadedFile)
        {
            $documents[] = [
                'id'             => $uploadedFile->id,
                'title'          => trim($uploadedFile->filename),
                'remarks'        => trim($uploadedFile->remarks),
                'type'           => 'file',
                'extension'      => $uploadedFile->extension,
                'route:download' => route('consultant.management.rfp.documents.download', [$vendorCategoryRfp->id, $uploadedFile->id]),
                'route:delete'   => route('consultant.management.rfp.documents.delete', [$vendorCategoryRfp->id, $uploadedFile->id])
            ];
        }

        return Response::json($documents);
    }

    public function upload(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $request = Request::instance();
        $user    = \Confide::user();
        $consultantManagementContract = $vendorCategoryRfp->consultantManagementContract;

        if(!$user->isConsultantManagementEditorByRole($consultantManagementContract, ConsultantManagementContract::ROLE_RECOMMENDATION_OF_CONSULTANT) and
        !$user->isConsultantManagementEditorByRole($consultantManagementContract, ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT))
        {
            return View::make('errors/404');
        }

        $uploadedFiles = $request->get('uploaded_files');

        $success = false;

        try
        {
            if(is_array($uploadedFiles) && !empty($uploadedFiles))
            {
                foreach($uploadedFiles as $uploadFile)
                {
                    $document = new RfpDocument;

                    $document->vendor_category_rfp_id = $vendorCategoryRfp->id;
                    $document->created_by = $user->id;
                    $document->updated_by = $user->id;

                    $document->save();

                    $object = ObjectField::findOrCreateNew($document, $document->getTable());
                    
                    ModuleAttachment::saveAttachments($object, ['uploaded_files' => [$uploadFile]]);
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

    public function download(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $documentId)
    {
        $document = RfpDocument::findOrFail($documentId);
        $user     = \Confide::user();

        $document = RfpDocument::select('consultant_management_rfp_documents.id', 'uploads.filename',
            'uploads.path', 'uploads.extension')
            ->join('object_fields', 'object_fields.object_id', '=', 'consultant_management_rfp_documents.id')
            ->join('module_uploaded_files', 'module_uploaded_files.uploadable_id', '=', 'object_fields.id')
            ->join('uploads', 'uploads.id', '=', 'module_uploaded_files.upload_id')
            ->where('object_fields.object_type', '=', 'PCK\ConsultantManagement\RfpDocument')
            ->where('module_uploaded_files.uploadable_type', '=', 'PCK\ObjectField\ObjectField')
            ->where('consultant_management_rfp_documents.id', '=',$document->id)
            ->first();

        $filepath = base_path().DIRECTORY_SEPARATOR.$document->path.DIRECTORY_SEPARATOR.$document->filename;

        return \PCK\Helpers\Files::download($filepath, $document->filename);
    }

    public function delete(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $documentId)
    {
        $document = RfpDocument::findOrFail($documentId);
        $user     = \Confide::user();
        $consultantManagementContract = $vendorCategoryRfp->consultantManagementContract;

        if(!$user->isConsultantManagementEditorByRole($consultantManagementContract, ConsultantManagementContract::ROLE_RECOMMENDATION_OF_CONSULTANT) and
        !$user->isConsultantManagementEditorByRole($consultantManagementContract, ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT))
        {
            return View::make('errors/404');
        }

        try
        {
            $document->delete();
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

    public function remarkStore(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $request = Request::instance();
        $user    = \Confide::user();

        $document = RfpDocument::findOrFail((int)$request->get('id'));

        $consultantManagementContract = $vendorCategoryRfp->consultantManagementContract;

        if(!$user->isConsultantManagementEditorByRole($consultantManagementContract, ConsultantManagementContract::ROLE_RECOMMENDATION_OF_CONSULTANT) and
        !$user->isConsultantManagementEditorByRole($consultantManagementContract, ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT))
        {
            return View::make('errors/404');
        }

        $remarks = $request->get('remarks');

        $success = false;

        try
        {
            $document->remarks = trim($request->get('remarks'));
            $document->updated_by = $user->id;

            $document->save();

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

    public function consultantDocumentList(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp, $companyId)
    {
        $company = Company::findOrFail((int)$companyId);

        $request = Request::instance();

        $model = RfpDocument::select('consultant_management_rfp_documents.id', 'uploads.filename',
            'consultant_management_rfp_documents.remarks',
            'consultant_management_rfp_documents.vendor_category_rfp_id',
            'uploads.extension')
            ->join('object_fields', 'object_fields.object_id', '=', 'consultant_management_rfp_documents.id')
            ->join('module_uploaded_files', 'module_uploaded_files.uploadable_id', '=', 'object_fields.id')
            ->join('uploads', 'uploads.id', '=', 'module_uploaded_files.upload_id')
            ->where('object_fields.object_type', '=', 'PCK\ConsultantManagement\RfpDocument')
            ->where('module_uploaded_files.uploadable_type', '=', 'PCK\ObjectField\ObjectField')
            ->where('consultant_management_rfp_documents.vendor_category_rfp_id', '=', $vendorCategoryRfp->id);

        $uploadedFiles = $model->orderBy('uploads.filename', 'asc')->get();

        $documents = [];
        
        foreach($uploadedFiles as $uploadedFile)
        {
            $documents[] = [
                'id'             => $uploadedFile->id,
                'title'          => trim($uploadedFile->filename),
                'remarks'        => trim($uploadedFile->remarks),
                'type'           => 'file',
                'extension'      => $uploadedFile->extension,
                'route:download' => route('consultant.management.rfp.documents.download', [$vendorCategoryRfp->id, $uploadedFile->id])
            ];
        }

        return Response::json($documents);
    }
}