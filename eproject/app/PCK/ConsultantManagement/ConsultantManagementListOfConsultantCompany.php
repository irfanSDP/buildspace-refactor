<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use PCK\Users\User;
use PCK\ConsultantManagement\ConsultantManagementListOfConsultant;

class ConsultantManagementListOfConsultantCompany extends Model
{
    protected $table = 'consultant_management_list_of_consultant_companies';

    protected $fillable = ['consultant_management_list_of_consultant_id', 'company_id', 'status'];

    const STATUS_PENDING = 1;
    const STATUS_YES = 2;
    const STATUS_NO = 4;

    const STATUS_PENDING_TEXT = 'Pending';
    const STATUS_YES_TEXT = 'Yes';
    const STATUS_NO_TEXT = 'No';

    public function consultantManagementListOfConsultant()
    {
        return $this->belongsTo('PCK\ConsultantManagement\ConsultantManagementListOfConsultant', 'consultant_management_list_of_consultant_id');
    }

    public function company()
    {
        return $this->belongsTo('PCK\Companies\Company', 'company_id');
    }

    public function getStatusText()
    {
        return self::getStatusTextByStatus($this->status);
    }

    public static function getStatusTextByStatus($status)
    {
        switch($status)
        {
            case self::STATUS_PENDING:
                return self::STATUS_PENDING_TEXT;
            case self::STATUS_YES:
                return self::STATUS_YES_TEXT;
            case self::STATUS_NO:
                return self::STATUS_NO_TEXT;
            default:
                throw new \Exception('Invalid status');
        }
    }
}