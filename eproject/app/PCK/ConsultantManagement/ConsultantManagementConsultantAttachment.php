<?php namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Model;
use PCK\Base\ModuleAttachmentTrait;

use PCK\ModuleUploadedFiles\ModuleUploadedFile;
use PCK\ObjectField\ObjectField;
use PCK\Base\Upload;

class ConsultantManagementConsultantAttachment extends Model
{
    use ModuleAttachmentTrait;

    protected $table = 'consultant_management_consultant_attachments';

    protected $fillable = ['consultant_management_attachment_setting_id', 'vendor_category_rfp_id', 'company_id', 'remarks'];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function(self $model)
        {
            $attachment = ConsultantManagementConsultantAttachment::select('consultant_management_consultant_attachments.id', 'uploads.id AS upload_id',
            'object_fields.id AS object_field_id', 'module_uploaded_files.id As module_uploaded_file_id')
            ->join('object_fields', 'object_fields.object_id', '=', 'consultant_management_consultant_attachments.id')
            ->join('module_uploaded_files', 'module_uploaded_files.uploadable_id', '=', 'object_fields.id')
            ->join('uploads', 'uploads.id', '=', 'module_uploaded_files.upload_id')
            ->where('object_fields.object_type', '=', 'PCK\ConsultantManagement\ConsultantManagementConsultantAttachment')
            ->where('module_uploaded_files.uploadable_type', '=', 'PCK\ObjectField\ObjectField')
            ->where('consultant_management_consultant_attachments.id', '=', $model->id)
            ->first();

            $objectField = ObjectField::findOrFail($attachment->object_field_id);
            $objectField->delete();

            $moduleUploadedFile = ModuleUploadedFile::findOrFail($attachment->module_uploaded_file_id);
            $moduleUploadedFile->delete();

            $upload = Upload::findOrFail($attachment->upload_id);
            $upload->delete();
        });
    }

    public function consultantManagementAttachmentSetting()
    {
        return $this->belongsTo('PCK\ConsultantManagement\ConsultantManagementAttachmentSetting', 'consultant_management_attachment_setting_id');
    }

    public function consultantManagementVendorCategoryRfp()
    {
        return $this->belongsTo('PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp', 'vendor_category_rfp_id');
    }

    public function company()
    {
        return $this->belongsTo('PCK\Companies\Company', 'company_id');
    }

    public function deletable()
    {
        return true;
    }
}