<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use PCK\ConsultantManagement\ApprovalDocumentSectionC;
use PCK\Companies\Company;

class ApprovalDocumentSectionCDetails extends Model
{
    protected $table = 'consultant_management_section_c_details';

    public function sectionC()
    {
        return $this->belongsTo(ApprovalDocumentSectionC::class, 'consultant_management_approval_document_section_c_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}