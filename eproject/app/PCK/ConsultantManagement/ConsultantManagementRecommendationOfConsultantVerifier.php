<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

use PCK\Users\User;
use PCK\ConsultantManagement\ConsultantManagementRecommendationOfConsultant;
use PCK\ConsultantManagement\ConsultantManagementRecommendationOfConsultantVerifierVersion;

class ConsultantManagementRecommendationOfConsultantVerifier extends Model
{
    use SoftDeletingTrait;

    protected $dates = ['deleted_at'];
    protected $table = 'consultant_management_recommendation_of_consultant_verifiers';

    protected $fillable = ['consultant_management_recommendation_of_consultant_id', 'user_id'];

    public function consultantManagementRecommendationOfConsultant()
    {
        return $this->belongsTo(ConsultantManagementRecommendationOfConsultant::class, 'consultant_management_recommendation_of_consultant_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function version()
    {
        return $this->hasOne(ConsultantManagementRecommendationOfConsultantVerifierVersion::class, 'consultant_management_recommendation_of_consultant_verifier_id');
    }
}