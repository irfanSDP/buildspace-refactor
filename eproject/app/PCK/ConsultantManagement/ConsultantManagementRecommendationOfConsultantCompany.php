<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use PCK\Users\User;
use PCK\ConsultantManagement\ConsultantManagementRecommendationOfConsultant;
use PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp;

class ConsultantManagementRecommendationOfConsultantCompany extends Model
{
    protected $table = 'consultant_management_recommendation_of_consultant_companies';

    protected $fillable = ['vendor_category_rfp_id', 'company_id', 'status'];

    const STATUS_PENDING = 1;
    const STATUS_YES = 2;
    const STATUS_NO = 4;

    const STATUS_PENDING_TEXT = 'Pending';
    const STATUS_YES_TEXT = 'Yes';
    const STATUS_NO_TEXT = 'No';

    public function consultantManagementVendorCategoryRfp()
    {
        return $this->belongsTo('PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp', 'vendor_category_rfp_id');
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