<?php namespace PCK\TenderInterviews;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use PCK\ContractGroups\ContractGroup;
use PCK\Tenders\Tender;

class TenderInterviewInformation extends Model {

    protected $table = "tender_interview_information";

    protected $fillable = [
        'tender_id',
        'contract_group_id',
        'date_and_time',
    ];

    public function tender()
    {
        return $this->belongsTo('PCK\Tenders\Tender');
    }

    public function contractGroup()
    {
        return $this->belongsTo('PCK\ContractGroups\ContractGroup');
    }

    /**
     * Sets the contract group for the Tender Interview information.
     *
     * @param Tender        $tender
     * @param ContractGroup $contractGroup
     *
     * @return mixed
     */
    public static function setContractGroup(Tender $tender, ContractGroup $contractGroup)
    {
        $model = self::where('tender_id', '=', $tender->id)->first();

        $model->contract_group_id = $contractGroup->id;

        return $model->save();
    }

    /**
     * Returns true if this object and each of its company interviews have all been modified.
     *
     * @return bool
     */
    public function isModified()
    {
        if( $this->created_at == $this->updated_at ) return false;

        foreach($this->getCompanyInterviews() as $companyInterview)
        {
            if( $companyInterview->created_at == $companyInterview->updated_at ) return false;
        }

        return true;
    }

    /**
     * Returns true if the Tender Interview should be activated.
     *
     * @return bool
     */
    public function isActivated()
    {
        if( ! $this->isModified() ) return false;

        if( empty( $this->getVenue() ) || empty( $this->date_and_time ) ) return false;

        return true;
    }

    public function getCompanyInterviews()
    {
        $selectedCompanyIds = array();

        foreach(Tender::find($this->tender_id)->listOfTendererInformation->selectedContractors as $contractor)
        {
            if( ! $contractor->pivot->deleted_at ) array_push($selectedCompanyIds, $contractor->id);
        }

        return TenderInterview::where('tender_id', '=', $this->tender_id)
            ->whereIn('company_id', $selectedCompanyIds)
            ->orderBy('date_and_time', 'ASC')
            ->get();
    }

    public function getVenue()
    {
        if( $this->getCompanyInterviews()->isEmpty() ) return null;

        return $this->getCompanyInterviews()->first()->venue;
    }

    public function getDiscussionDate($format = 'dates.standard_spaced')
    {
        if( ! $this->date_and_time ) return null;

        return Carbon::parse($this->date_and_time)->format(\Config::get($format));
    }

    public function getDiscussionTime($format = 'dates.time_only')
    {
        if( ! $this->date_and_time ) return null;

        return Carbon::parse($this->date_and_time)->format(\Config::get($format));
    }
}