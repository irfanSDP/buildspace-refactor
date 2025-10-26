<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use PCK\ConsultantManagement\ApprovalDocumentSectionAppendix;

class ApprovalDocumentSectionAppendixDetails extends Model
{
    protected $table = 'consultant_management_section_appendix_details';

    public function sectionAppendix()
    {
        return $this->belongsTo(ApprovalDocumentSectionC::class, 'consultant_management_approval_document_section_appendix_id');
    }
}