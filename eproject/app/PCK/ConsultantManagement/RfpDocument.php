<?php namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Model;
use PCK\Base\ModuleAttachmentTrait;

use PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp;
use PCK\ModuleUploadedFiles\ModuleUploadedFile;
use PCK\ObjectField\ObjectField;
use PCK\Base\Upload;

class RfpDocument extends Model
{
    use ModuleAttachmentTrait;

    protected $table = 'consultant_management_rfp_documents';

    protected $fillable = ['vendor_category_rfp_id', 'remarks', 'remarks'];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function(self $model)
        {
            $document = RfpDocument::select('consultant_management_rfp_documents.id', 'uploads.id AS upload_id',
            'object_fields.id AS object_field_id', 'module_uploaded_files.id As module_uploaded_file_id')
            ->join('object_fields', 'object_fields.object_id', '=', 'consultant_management_rfp_documents.id')
            ->join('module_uploaded_files', 'module_uploaded_files.uploadable_id', '=', 'object_fields.id')
            ->join('uploads', 'uploads.id', '=', 'module_uploaded_files.upload_id')
            ->where('object_fields.object_type', '=', 'PCK\ConsultantManagement\RfpDocument')
            ->where('module_uploaded_files.uploadable_type', '=', 'PCK\ObjectField\ObjectField')
            ->where('consultant_management_rfp_documents.id', '=', $model->id)
            ->first();

            $objectField = ObjectField::findOrFail($document->object_field_id);
            $objectField->delete();

            $moduleUploadedFile = ModuleUploadedFile::findOrFail($document->module_uploaded_file_id);
            $moduleUploadedFile->delete();

            $upload = Upload::findOrFail($document->upload_id);
            $upload->delete();
        });
    }

    public function consultantManagementVendorCategoryRfp()
    {
        return $this->belongsTo(ConsultantManagementVendorCategoryRfp::class, 'vendor_category_rfp_id');
    }
}