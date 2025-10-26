<?php namespace PCK\Tenders;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

use PCK\Tenders\Tender;
use PCK\Tenders\CompanyTender;

class CompanyTenderTenderAlternative extends Model {

    protected $table = 'company_tender_tender_alternatives';
    protected $primaryKey = ['company_tender_id', 'tender_alternative_id'];
    public $incrementing = false;

    protected $fillable = array('tender_alternative_id', 'tender_amount', 'other_bill_type_amount_except_prime_cost_provisional', 'supply_of_material_amount', 'original_tender_amount', 'discounted_percentage', 'discounted_amount', 'completion_period', 'contractor_adjustment_amount', 'contractor_adjustment_percentage', 'earnest_money', 'remarks');

    public function companyTender()
    {
        return $this->hasOne(CompanyTender::class, 'company_tender_id');
    }

    /**
     * Set the keys for a save update query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery(Builder $query)
    {
        $keys = $this->getKeyName();
        if(!is_array($keys)){
            return parent::setKeysForSaveQuery($query);
        }

        foreach($keys as $keyName){
            $query->where($keyName, '=', $this->getKeyForSaveQuery($keyName));
        }

        return $query;
    }

    /**
     * Get the primary key value for a save query.
     *
     * @param mixed $keyName
     * @return mixed
     */
    protected function getKeyForSaveQuery($keyName = null)
    {
        if(is_null($keyName)){
            $keyName = $this->getKeyName();
        }

        if (isset($this->original[$keyName])) {
            return $this->original[$keyName];
        }

        return $this->getAttribute($keyName);
    }
}