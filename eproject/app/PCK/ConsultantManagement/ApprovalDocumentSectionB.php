<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use PCK\ConsultantManagement\ApprovalDocument;

class ApprovalDocumentSectionB extends Model
{
    protected $table = 'consultant_management_approval_document_section_b';

    public function approvalDocument()
    {
        return $this->belongsTo(ApprovalDocument::class, 'consultant_management_approval_document_id');
    }
}