<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class ConsultantManagementListOfConsultantVerifier extends Model
{
    use SoftDeletingTrait;

    protected $dates = ['deleted_at'];
    protected $table = 'consultant_management_list_of_consultant_verifiers';

    protected $fillable = ['consultant_management_list_of_consultant_id', 'user_id'];

    public function consultantManagementListOfConsultant()
    {
        return $this->belongsTo('PCK\ConsultantManagement\ConsultantManagementListOfConsultant', 'consultant_management_list_of_consultant_id');
    }

    public function verifier()
    {
        return $this->belongsTo('PCK\Users\User', 'user_id');
    }
}