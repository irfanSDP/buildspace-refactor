<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class ConsultantManagementCallingRfpVerifier extends Model
{
    use SoftDeletingTrait;

    protected $dates = ['deleted_at'];
    protected $table = 'consultant_management_calling_rfp_verifiers';

    protected $fillable = ['consultant_management_calling_rfp_id', 'user_id'];

    public function consultantManagementCallingRfp()
    {
        return $this->belongsTo('PCK\ConsultantManagement\ConsultantManagementCallingRfp', 'consultant_management_calling_rfp_id');
    }

    public function verifier()
    {
        return $this->belongsTo('PCK\Users\User', 'user_id');
    }
}