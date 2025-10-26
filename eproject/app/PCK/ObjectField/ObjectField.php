<?php namespace PCK\ObjectField;

use Illuminate\Database\Eloquent\Model;
use PCK\Base\ModuleAttachmentTrait;

class ObjectField extends Model
{
    use ModuleAttachmentTrait;

    protected $table = 'object_fields';

    const COMPANY__COMPANY_PERSONNEL_DIRECTOR = 'directors';
    const COMPANY__COMPANY_PERSONNEL_SHAREHOLDER = 'shareholder';
    const COMPANY__COMPANY_PERSONNEL_COMPANY_HEAD = 'company_head';

    const VENDOR_PROFILE_VENDOR_FILE_UPLOAD = 'vendor_profile_vendor_file_upload';
    const VENDOR_PROFILE_CLIENT_FILE_UPLOAD = 'vendor_profile_client_file_upload';

    const VENDOR_REGISTRATION_PAYMENT_PAID       = 'vendor_registration_payment_paid';
    const VENDOR_REGISTRATION_PAYMENT_SUCCESSFUL = 'vendor_registration_payment_successful';

    const CONSULTANT_MANAGEMENT_PHASE_PROJECT_BRIEF = 'consultant_management_phase_project_brief';
    const CONSULTANT_MANAGEMENT_LIST_OF_CONSULTANT_GENERAL = 'consultant_management_list_of_consultant_general';

    const PROCESSOR_ATTACHMENTS_COMPANY_DETAILS             = 'processor_attachments_company_details';
    const PROCESSOR_ATTACHMENTS_VENDOR_REGISTRATION_FORM    = 'processor_attachments_vendor_registration_form';
    const PROCESSOR_ATTACHMENTS_COMPANY_PERSONNELS          = 'processor_attachments_company_personnels';
    const PROCESSOR_ATTACHMENTS_PROJECT_TRACK_RECORDS       = 'processor_attachments_project_track_records';
    const PROCESSOR_ATTACHMENTS_VENDOR_PREQUALIFICATIONS    = 'processor_attachments_vendor_prequalifications';
    const PROCESSOR_ATTACHMENTS_SUPPLIER_CREDIT_FACILITIES  = 'processor_attachments_supplier_credit_facilities';

    const PROJECT_REPORT = 'project_report';

    public function attachments()
    {
        return $this->morphMany('PCK\ModuleUploadedFiles\ModuleUploadedFile', 'uploadable', null, null, 'id')->orderBy('id');
    }

    public static function findRecord($object, $field)
    {
        return self::where('object_id', $object->id)->where('object_type', get_class($object))->where('field', trim($field))->first();
    }

    public static function findOrCreateNew($object, $field)
    {
        $record = self::findRecord($object, $field);

        if(is_null($record))
        {
            $record              = new self();
            $record->object_id   = $object->id;
            $record->object_type = get_class($object);
            $record->field       = trim($field);
            $record->save();

            $record = self::find($record->id);
        }

        return $record;
    }
}