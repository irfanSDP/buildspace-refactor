<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use PCK\ConsultantManagement\ConsultantManagementListOfConsultant;
use PCK\ConsultantManagement\ConsultantManagementOpenRfpVerifierVersion;
use PCK\ConsultantManagement\ConsultantManagementConsultantRfp;
use PCK\ConsultantManagement\ConsultantManagementCallingRfp;
use PCK\ConsultantManagement\ConsultantManagementOpenRfp;
use PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp;

use PCK\Companies\Company;

class ConsultantManagementRfpRevision extends Model
{
    protected $table = 'consultant_management_rfp_revisions';

    protected $fillable = ['vendor_category_rfp_id', 'revision'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function(self $model)
        {
            $rev = ConsultantManagementRfpRevision::select(\DB::raw("MAX(revision) AS revision"))
            ->where('vendor_category_rfp_id', '=', $model->vendor_category_rfp_id)
            ->first();

            $revision = ($rev) ? $rev->revision + 1 : 1;

            $model->revision = $revision;
        });

        static::created(function(self $model)
        {
            $user = \Confide::user();

            if($model->revision > 1)
            {
                $rev = ConsultantManagementRfpRevision::where('vendor_category_rfp_id', '=', $model->vendor_category_rfp_id)
                ->where('revision', '=', ($model->revision - 1 ))
                ->first();

                $prevListOfConsultant = $rev->listOfConsultant;

                $listOfConsultant = new ConsultantManagementListOfConsultant;

                $listOfConsultant->consultant_management_rfp_revision_id = $model->id;
                $listOfConsultant->proposed_fee = $prevListOfConsultant->proposed_fee;
                $listOfConsultant->calling_rfp_date = $prevListOfConsultant->calling_rfp_date;
                $listOfConsultant->closing_rfp_date = $prevListOfConsultant->closing_rfp_date;
                $listOfConsultant->created_by = $user->id;
                $listOfConsultant->updated_by = $user->id;
                $listOfConsultant->created_at = date('Y-m-d H:i:s');
                $listOfConsultant->updated_at = date('Y-m-d H:i:s');

                $listOfConsultant->save();
            }
            else
            {
                $recommendationOfConsultant = $model->consultantManagementVendorCategoryRfp->recommendationOfConsultant;

                $listOfConsultant = new ConsultantManagementListOfConsultant;

                $listOfConsultant->consultant_management_rfp_revision_id = $model->id;
                $listOfConsultant->proposed_fee = $recommendationOfConsultant->proposed_fee;
                $listOfConsultant->calling_rfp_date = $recommendationOfConsultant->calling_rfp_proposed_date;
                $listOfConsultant->closing_rfp_date = $recommendationOfConsultant->closing_rfp_proposed_date;
                $listOfConsultant->created_by = $user->id;
                $listOfConsultant->updated_by = $user->id;
                $listOfConsultant->created_at = date('Y-m-d H:i:s');
                $listOfConsultant->updated_at = date('Y-m-d H:i:s');

                $listOfConsultant->save();
            }
        });
    }

    public function consultantManagementVendorCategoryRfp()
    {
        return $this->belongsTo(ConsultantManagementVendorCategoryRfp::class, 'vendor_category_rfp_id');
    }

    public function listOfConsultant()
    {
        return $this->hasOne(ConsultantManagementListOfConsultant::class, 'consultant_management_rfp_revision_id');
    }

    public function callingRfp()
    {
        return $this->hasOne(ConsultantManagementCallingRfp::class, 'consultant_management_rfp_revision_id');
    }

    public function openRfp()
    {
        return $this->hasOne(ConsultantManagementOpenRfp::class, 'consultant_management_rfp_revision_id');
    }

    public function consultantRfp()
    {
        return $this->hasMany(ConsultantManagementConsultantRfp::class, 'consultant_management_rfp_revision_id');
    }

    public function isLatestRevision()
    {
        $otherLatestVersion = ConsultantManagementRfpRevision::select(\DB::raw("MAX(consultant_management_rfp_revisions.revision) AS revision"))
            ->join('consultant_management_vendor_categories_rfp', 'consultant_management_vendor_categories_rfp.id', '=', 'consultant_management_rfp_revisions.vendor_category_rfp_id')
            ->where('consultant_management_vendor_categories_rfp.id', '=', $this->vendor_category_rfp_id)
            ->where('consultant_management_rfp_revisions.revision', '>', $this->revision)
            ->groupBy('consultant_management_rfp_revisions.id')
            ->first();
        
        return (!$otherLatestVersion);
    }

    public function getAwardedConsultant()
    {
        return Company::select('companies.*')->join('consultant_management_consultant_rfp', 'companies.id', '=', 'consultant_management_consultant_rfp.company_id')
        ->where('consultant_management_consultant_rfp.consultant_management_rfp_revision_id', '=', $this->id)
        ->where('consultant_management_consultant_rfp.awarded', '=', true)
        ->first();
    }
}