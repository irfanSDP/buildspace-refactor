<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

use PCK\Subsidiaries\Subsidiary;
use PCK\ConsultantManagement\LetterOfAward;
use PCK\Users\User;

class LetterOfAwardSubsidiaryRunningNumber extends Model
{
    protected $table = 'consultant_management_loa_subsidiary_running_numbers';
    protected $primaryKey = 'subsidiary_id';
    protected $fillable = ['subsidiary_id', 'next_running_number'];

    public $incrementing = false;

    public function subsidiary()
    {
        return $this->belongsTo(Subsidiary::class, 'subsidiary_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getHighestRunningNumber()
    {
        $max = LetterOfAward::select(\DB::raw("MAX(consultant_management_letter_of_awards.running_number) AS running_number"))
        ->join('consultant_management_vendor_categories_rfp', 'consultant_management_vendor_categories_rfp.id', '=', 'consultant_management_letter_of_awards.vendor_category_rfp_id')
        ->join('consultant_management_contracts', 'consultant_management_contracts.id', '=', 'consultant_management_vendor_categories_rfp.consultant_management_contract_id')
        ->join('consultant_management_loa_subsidiary_running_numbers', 'consultant_management_loa_subsidiary_running_numbers.subsidiary_id', '=', 'consultant_management_contracts.subsidiary_id')
        ->where('consultant_management_loa_subsidiary_running_numbers.subsidiary_id','=', $this->subsidiary_id)
        ->groupBy('consultant_management_contracts.subsidiary_id')
        ->first();

        return ($max) ? $max->running_number : 0;
    }
}