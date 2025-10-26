<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use PCK\ConsultantManagement\ApprovalDocument;
use PCK\ConsultantManagement\ApprovalDocumentSectionDDetails;
use PCK\ConsultantManagement\ApprovalDocumentSectionDServiceFee;

class ApprovalDocumentSectionD extends Model
{
    protected $table = 'consultant_management_approval_document_section_d';

    public function approvalDocument()
    {
        return $this->belongsTo(ApprovalDocument::class, 'consultant_management_approval_document_id');
    }

    public function details()
    {
        return $this->hasMany(ApprovalDocumentSectionDDetails::class, 'consultant_management_approval_document_section_d_id');
    }

    public function consultantServiceFees()
    {
        return $this->hasMany(ApprovalDocumentSectionDServiceFee::class, 'consultant_management_approval_document_section_d_id');
    }
}