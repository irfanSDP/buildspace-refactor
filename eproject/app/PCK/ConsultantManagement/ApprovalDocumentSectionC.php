<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use PCK\ConsultantManagement\ApprovalDocument;
use PCK\ConsultantManagement\ApprovalDocumentSectionCDetails;

class ApprovalDocumentSectionC extends Model
{
    protected $table = 'consultant_management_approval_document_section_c';

    public function approvalDocument()
    {
        return $this->belongsTo(ApprovalDocument::class, 'consultant_management_approval_document_id');
    }

    public function details()
    {
        return $this->hasMany(ApprovalDocumentSectionCDetails::class, 'consultant_management_approval_document_section_c_id');
    }
}