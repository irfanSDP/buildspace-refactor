<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use PCK\ConsultantManagement\ApprovalDocument;
use PCK\ConsultantManagement\ApprovalDocumentSectionAppendixDetails;

class ApprovalDocumentSectionAppendix extends Model
{
    protected $table = 'consultant_management_approval_document_section_appendix';

    public function approvalDocument()
    {
        return $this->belongsTo(ApprovalDocument::class, 'consultant_management_approval_document_id');
    }

    public function details()
    {
        return $this->hasMany(ApprovalDocumentSectionAppendixDetails::class, 'consultant_management_approval_document_section_appendix_id');
    }
}