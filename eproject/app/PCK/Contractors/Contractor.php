<?php namespace PCK\Contractors;

use Illuminate\Database\Eloquent\Model;

class Contractor extends Model implements ContractorDetails\JobLimitSymbol
{

	protected $with = array( 'currentCPEGrade', 'previousCPEGrade' );

    protected $fillable = [
        'previous_cpe_grade_id',
        'current_cpe_grade_id',
        'registration_status',
        'job_limit_sign',
        'job_limit_number',
        'cidb_category',
        'remarks',
        'registered_date'
    ];

	public function company()
	{
		return $this->belongsTo('PCK\Companies\Company');
	}

	public function workCategories()
	{
		return $this->belongsToMany('PCK\WorkCategories\WorkCategory')->withTimestamps();
	}

	public function workSubcategories()
	{
		return $this->belongsToMany('PCK\WorkCategories\WorkSubcategory')->withTimestamps();
	}

	public function registrationStatus()
	{
		return $this->belongsTo('PCK\Contractors\ContractorDetails\RegistrationStatus');
	}

	public function previousCPEGrade()
	{
		return $this->belongsTo('PCK\CPEGrades\PreviousCPEGrade', 'previous_cpe_grade_id');
	}

	public function currentCPEGrade()
	{
		return $this->belongsTo('PCK\CPEGrades\CurrentCPEGrade', 'current_cpe_grade_id');
	}

	/**
	 * Returns an array of the ids of all work categories associated with the calling Contractor object
	 *
	 * @return array
	 */
	public function getAllWorkCategoriesId()
	{
		return $this->workCategories->lists('id');
	}

	/**
	 * Returns an array of the ids of all work subcategories associated with the calling Contractor object
	 *
	 * @return array
	 */
	public function getAllWorkSubcategoriesId()
	{
		return $this->workSubcategories->lists('id');
	}

    /**
     * Returns the Job Limit Symbol (range symbol) representing the given jobLimitSymbol id
     *
     * @param $id
     * @return string
     */
    public static function getJobLimitSymbolSymbolById($id)
    {
        switch ($id)
        {
            case self::JOB_LIMIT_SYMBOL_GREATER_THAN:
                return self::JOB_LIMIT_SYMBOL_GREATER_THAN_SYMBOL;
            case self::JOB_LIMIT_SYMBOL_LESS_THAN:
                return self::JOB_LIMIT_SYMBOL_LESS_THAN_SYMBOL;
        }
    }

    /**
     * Returns the Job Limit Symbol text representing the given jobLimitSymbol id
     *
     * @param $id
     * @return string
     */
    public static function getJobLimitSymbolTextById($id)
    {
        switch ($id)
        {
            case self::JOB_LIMIT_SYMBOL_GREATER_THAN:
                return self::JOB_LIMIT_SYMBOL_GREATER_THAN_TEXT;
            case self::JOB_LIMIT_SYMBOL_LESS_THAN:
                return self::JOB_LIMIT_SYMBOL_LESS_THAN_TEXT;
        }
    }

    /**
     * Checks to see if this Contractor is associated with a company
     *
     * @return bool
     */
    public function hasCompany()
    {
        return $this->company != null || isset($this->company);
    }
}