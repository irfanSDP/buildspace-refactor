<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use PCK\ConsultantManagement\ApprovalDocument;

class ApprovalDocumentSectionA extends Model
{
    protected $table = 'consultant_management_approval_document_section_a';

    const APPROVING_AUTHORITY_EMPTY = -1;
    const APPROVING_AUTHORITY_A = 1;
    const APPROVING_AUTHORITY_B = 2;
    const APPROVING_AUTHORITY_C = 4;
    const APPROVING_AUTHORITY_D = 8;
    const APPROVING_AUTHORITY_E = 16;
    const APPROVING_AUTHORITY_F = 32;
    const APPROVING_AUTHORITY_G = 64;

    const APPROVING_AUTHORITY_EMPTY_TEXT = 'Empty';
    const APPROVING_AUTHORITY_A_TEXT = 'GPPA CONSULTANT - UP TO 2.0 MIL - COO, TOWNSHIP/INTEGRATED DEVELOPMENT';
    const APPROVING_AUTHORITY_B_TEXT = 'GPPA CONSULTANT - UP TO 5.0 MIL - GROUP MANAGING DIRECTOR';
    const APPROVING_AUTHORITY_C_TEXT = 'GPPA CONSULTANT - UP TO 20.0 MIL - GROUP TENDER COMMITTE';
    const APPROVING_AUTHORITY_D_TEXT = 'GPPA CONSULTANT - UP TO 50.0 MIL - BOARD TENDER COMMITTE';
    const APPROVING_AUTHORITY_E_TEXT = 'GPPA CONSULTANT - ABOVE 50.0 MIL - BOARD';
    const APPROVING_AUTHORITY_F_TEXT = 'GPPA CONSULTANT - UP TO 3.0 MIL – CEO, PROPERTY DEVELOPMENT';
    const APPROVING_AUTHORITY_G_TEXT = 'GGPA Consultant- UP to 1.0 M – Head , Business Unit';

    public function approvalDocument()
    {
        return $this->belongsTo(ApprovalDocument::class, 'consultant_management_approval_document_id');
    }

    public function getApprovingAuthorityText()
    {
        switch($this->approving_authority)
        {
            case self::APPROVING_AUTHORITY_EMPTY:
                return self::APPROVING_AUTHORITY_EMPTY_TEXT;
            case self::APPROVING_AUTHORITY_A:
                return self::APPROVING_AUTHORITY_A_TEXT;
            case self::APPROVING_AUTHORITY_B:
                return self::APPROVING_AUTHORITY_B_TEXT;
            case self::APPROVING_AUTHORITY_C:
                return self::APPROVING_AUTHORITY_C_TEXT;
            case self::APPROVING_AUTHORITY_D:
                return self::APPROVING_AUTHORITY_D_TEXT;
            case self::APPROVING_AUTHORITY_E:
                return self::APPROVING_AUTHORITY_E_TEXT;
            case self::APPROVING_AUTHORITY_F:
                return self::APPROVING_AUTHORITY_F_TEXT;
            case self::APPROVING_AUTHORITY_G:
                return self::APPROVING_AUTHORITY_G_TEXT;
            default:
                throw new \Exception('Invalid approving authority');
        }
    }
}