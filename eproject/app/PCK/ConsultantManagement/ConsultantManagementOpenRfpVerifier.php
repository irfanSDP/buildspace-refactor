<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class ConsultantManagementOpenRfpVerifier extends Model
{
    use SoftDeletingTrait;

    protected $dates = ['deleted_at'];
    protected $table = 'consultant_management_open_rfp_verifiers';

    protected $fillable = ['consultant_management_open_rfp_id', 'user_id'];

    public function consultantManagementOpenRfp()
    {
        return $this->belongsTo('PCK\ConsultantManagement\ConsultantManagementOpenRfp', 'consultant_management_open_rfp_id');
    }

    public function verifier()
    {
        return $this->belongsTo('PCK\Users\User', 'user_id');
    }
}