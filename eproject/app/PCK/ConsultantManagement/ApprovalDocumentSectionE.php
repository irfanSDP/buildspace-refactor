<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use PCK\ConsultantManagement\ApprovalDocument;

class ApprovalDocumentSectionE extends Model
{
    protected $table = 'consultant_management_approval_document_section_e';

    public function approvalDocument()
    {
        return $this->belongsTo(ApprovalDocument::class, 'consultant_management_approval_document_id');
    }
}