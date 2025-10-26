<?php namespace PCK\Tenders;

use Carbon\Carbon;
use PCK\Helpers\ModelOperations;
use PCK\Projects\Project;
use PCK\Companies\Company;
use PCK\Base\TimestampFormatterTrait;
use Illuminate\Database\Eloquent\Model;
use PCK\TenderFormVerifierLogs\FormLevelStatus;
use PCK\TenderInterviews\TenderInterview;

class OpenTenderIndustryCode extends Model {

    protected $fillable = ['tender_id','created_by','cidb_code_id', 'cidb_grade_id', 'vendor_category_id', 'vendor_work_category_id'];

    public function tender()
    {
        return $this->belongsTo('PCK\Tenders\Tender');
    }

    public function createdBy()
    {
        return $this->belongsTo('PCK\Users\User', 'created_by');
    }

    public function cidbCode()
    {
        return $this->belongsTo('PCK\CIDBCodes\CIDBCode', 'cidb_code_id');
    }

    public function cidbGrade()
    {
        return $this->belongsTo('PCK\CIDBGrades\CIDBGrade', 'cidb_grade_id');
    }

    public function vendorCategory()
    {
        return $this->belongsTo('PCK\VendorCategory\VendorCategory', 'vendor_category_id');
    }

    public function vendorWorkCategory()
    {
        return $this->belongsTo('PCK\VendorWorkCategory\VendorWorkCategory', 'vendor_work_category_id');
    }
}