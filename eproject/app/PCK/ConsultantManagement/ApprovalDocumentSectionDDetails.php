<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use PCK\ConsultantManagement\ApprovalDocumentSectionD;
use PCK\Companies\Company;

class ApprovalDocumentSectionDDetails extends Model
{
    protected $table = 'consultant_management_section_d_details';

    public function sectionD()
    {
        return $this->belongsTo(ApprovalDocumentSectionD::class, 'consultant_management_approval_document_section_d_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}