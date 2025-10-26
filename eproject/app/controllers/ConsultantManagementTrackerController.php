<?php
use Carbon\Carbon;

use PCK\ConsultantManagement\ConsultantManagementContract;
use PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp;
use PCK\ConsultantManagement\ConsultantManagementRecommendationOfConsultantVerifierVersion;
use PCK\ConsultantManagement\ConsultantManagementListOfConsultantVerifierVersion;
use PCK\ConsultantManagement\ConsultantManagementCallingRfpVerifierVersion;
use PCK\ConsultantManagement\ConsultantManagementOpenRfpVerifierVersion;

use PCK\Companies\Company;
use PCK\Users\User;
use PCK\VendorRegistration\VendorRegistration;
use PCK\CompanyPersonnel\CompanyPersonnel;

class ConsultantManagementTrackerController extends \BaseController
{
    public function index(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $consultantManagementContract = $vendorCategoryRfp->consultantManagementContract;
        $recommendationOfConsultant = $vendorCategoryRfp->recommendationOfConsultant;

        $rfpRevision = $vendorCategoryRfp->getLatestRfpRevision();

        $callingRfp = null;

        if($rfpRevision)
        {
            $callingRfp = $rfpRevision->callingRfp;
        }

        return View::make('consultant_management.tracker.index', compact('vendorCategoryRfp', 'recommendationOfConsultant', 'callingRfp'));
    }

    public function recommendationOfConsultantVerifierLogs(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $recommendationOfConsultant = $vendorCategoryRfp->recommendationOfConsultant;
        $data = [];

        if(!$recommendationOfConsultant)
        {
            return Response::json($data);
        }

        $selectedVerifiers = User::select("users.id", "users.name")
            ->join('consultant_management_recommendation_of_consultant_verifiers', 'users.id', '=', 'consultant_management_recommendation_of_consultant_verifiers.user_id')
            ->where('consultant_management_recommendation_of_consultant_id', $recommendationOfConsultant->id)
            ->whereNull('consultant_management_recommendation_of_consultant_verifiers.deleted_at')
            ->orderBy('consultant_management_recommendation_of_consultant_verifiers.id', 'asc')
            ->get();

        $latestVersion = ConsultantManagementRecommendationOfConsultantVerifierVersion::select(\DB::raw("MAX(version) AS version"))
            ->join('consultant_management_recommendation_of_consultant_verifiers', 'consultant_management_recommendation_of_consultant_verifiers.id', '=', 'consultant_management_roc_verifier_versions.consultant_management_recommendation_of_consultant_verifier_id')
            ->join('consultant_management_recommendation_of_consultants', 'consultant_management_recommendation_of_consultants.id', '=', 'consultant_management_recommendation_of_consultant_verifiers.consultant_management_recommendation_of_consultant_id')
            ->where('consultant_management_recommendation_of_consultants.id', '=', $recommendationOfConsultant->id)
            ->whereNull('consultant_management_recommendation_of_consultant_verifiers.deleted_at')
            ->groupBy('consultant_management_recommendation_of_consultants.id')
            ->first();

        $approvals = ConsultantManagementRecommendationOfConsultantVerifierVersion::select("consultant_management_roc_verifier_versions.id AS id", "users.id AS user_id",
        "consultant_management_roc_verifier_versions.version", "consultant_management_roc_verifier_versions.status", "consultant_management_roc_verifier_versions.remarks", "consultant_management_roc_verifier_versions.updated_at")
        ->join('consultant_management_recommendation_of_consultant_verifiers', 'consultant_management_recommendation_of_consultant_verifiers.id', '=', 'consultant_management_roc_verifier_versions.consultant_management_recommendation_of_consultant_verifier_id')
        ->join('users', 'users.id', '=', 'consultant_management_recommendation_of_consultant_verifiers.user_id')
        ->join('consultant_management_recommendation_of_consultants', 'consultant_management_recommendation_of_consultants.id', '=', 'consultant_management_recommendation_of_consultant_verifiers.consultant_management_recommendation_of_consultant_id')
        ->where('consultant_management_recommendation_of_consultants.vendor_category_rfp_id', '=', $vendorCategoryRfp->id)
        ->where('consultant_management_roc_verifier_versions.version', '=', ($latestVersion) ? $latestVersion->version : -1)
        ->whereNull('consultant_management_recommendation_of_consultant_verifiers.deleted_at')
        ->orderBy('consultant_management_roc_verifier_versions.id', 'asc')
        ->get();
        
        foreach($selectedVerifiers as $verifier)
        {
            $item = [
                'id'         => $verifier->id,
                'name'       => trim($verifier->name),
                'remarks'    => "",
                'version'    => ($latestVersion) ? $latestVersion->version : "",
                'status'     => ($latestVersion) ? ConsultantManagementRecommendationOfConsultantVerifierVersion::STATUS_PENDING : "",
                'status_txt' => ($latestVersion) ? ConsultantManagementRecommendationOfConsultantVerifierVersion::STATUS_PENDING_TEXT : "",
                'updated_at' => ""
            ];

            foreach($approvals as $approval)
            {
                if($approval->user_id == $verifier->id)
                {
                    $item['remarks']    = trim($approval->remarks);
                    $item['status']     = $approval->status;
                    $item['status_txt'] = $approval->getStatusText();
                    $item['updated_at'] =  date('d/m/Y H:i:s', strtotime($approval->updated_at));
                }
            }

            $data[] = $item;
        }

        return Response::json($data);
    }

    public function listOfConsultantVerifierLogs(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $data = [];

        $rfpRevision = $vendorCategoryRfp->getLatestRfpRevision();

        if(!$rfpRevision)
        {
            return Response::json($data);
        }

        $listOfConsultant = $rfpRevision->listOfConsultant;

        if(!$listOfConsultant)
        {
            return Response::json($data);
        }

        $selectedVerifiers = User::select("users.id", "users.name")
            ->join('consultant_management_list_of_consultant_verifiers', 'users.id', '=', 'consultant_management_list_of_consultant_verifiers.user_id')
            ->where('consultant_management_list_of_consultant_id', $listOfConsultant->id)
            ->whereNull('consultant_management_list_of_consultant_verifiers.deleted_at')
            ->orderBy('consultant_management_list_of_consultant_verifiers.id', 'asc')
            ->get();

        $latestVersion = ConsultantManagementListOfConsultantVerifierVersion::select(\DB::raw("MAX(version) AS version"))
            ->join('consultant_management_list_of_consultant_verifiers', 'consultant_management_list_of_consultant_verifiers.id', '=', 'consultant_management_loc_verifier_versions.consultant_management_list_of_consultant_verifier_id')
            ->join('consultant_management_list_of_consultants', 'consultant_management_list_of_consultants.id', '=', 'consultant_management_list_of_consultant_verifiers.consultant_management_list_of_consultant_id')
            ->where('consultant_management_list_of_consultants.id', '=', $listOfConsultant->id)
            ->whereNull('consultant_management_list_of_consultant_verifiers.deleted_at')
            ->groupBy('consultant_management_list_of_consultants.id')
            ->first();

        $approvals = ConsultantManagementListOfConsultantVerifierVersion::select("consultant_management_loc_verifier_versions.id AS id", "users.id AS user_id",
            "consultant_management_loc_verifier_versions.version", "consultant_management_loc_verifier_versions.status", "consultant_management_loc_verifier_versions.remarks", "consultant_management_loc_verifier_versions.updated_at")
            ->join('consultant_management_list_of_consultant_verifiers', 'consultant_management_list_of_consultant_verifiers.id', '=', 'consultant_management_loc_verifier_versions.consultant_management_list_of_consultant_verifier_id')
            ->join('users', 'users.id', '=', 'consultant_management_list_of_consultant_verifiers.user_id')
            ->join('consultant_management_list_of_consultants', 'consultant_management_list_of_consultants.id', '=', 'consultant_management_list_of_consultant_verifiers.consultant_management_list_of_consultant_id')
            ->where('consultant_management_list_of_consultants.id', '=', $listOfConsultant->id)
            ->where('consultant_management_loc_verifier_versions.version', '=', ($latestVersion) ? $latestVersion->version : -1)
            ->whereNull('consultant_management_list_of_consultant_verifiers.deleted_at')
            ->orderBy('consultant_management_loc_verifier_versions.id', 'asc')
            ->get();
        
        foreach($selectedVerifiers as $verifier)
        {
            $item = [
                'id'         => $verifier->id,
                'name'       => trim($verifier->name),
                'remarks'    => "",
                'version'    => ($latestVersion) ? $latestVersion->version : "",
                'status'     => ($latestVersion) ? ConsultantManagementListOfConsultantVerifierVersion::STATUS_PENDING : "",
                'status_txt' => ($latestVersion) ? ConsultantManagementListOfConsultantVerifierVersion::STATUS_PENDING_TEXT : "",
                'updated_at' => ""
            ];

            foreach($approvals as $approval)
            {
                if($approval->user_id == $verifier->id)
                {
                    $item['remarks']    = trim($approval->remarks);
                    $item['status']     = $approval->status;
                    $item['status_txt'] = $approval->getStatusText();
                    $item['updated_at'] =  date('d/m/Y H:i:s', strtotime($approval->updated_at));
                }
            }

            $data[] = $item;
        }

        return Response::json($data);
    }

    public function callingRfpVerifierLogs(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $data = [];

        $rfpRevision = $vendorCategoryRfp->getLatestRfpRevision();

        if(!$rfpRevision)
        {
            return Response::json($data);
        }

        $callingRfp = $rfpRevision->callingRfp;

        if(!$callingRfp)
        {
            return Response::json($data);
        }

        $selectedVerifiers = User::select("users.id", "users.name")
            ->join('consultant_management_calling_rfp_verifiers', 'users.id', '=', 'consultant_management_calling_rfp_verifiers.user_id')
            ->where('consultant_management_calling_rfp_id', $callingRfp->id)
            ->whereNull('consultant_management_calling_rfp_verifiers.deleted_at')
            ->orderBy('consultant_management_calling_rfp_verifiers.id', 'asc')
            ->get();

        $latestVersion = ConsultantManagementCallingRfpVerifierVersion::select(\DB::raw("MAX(version) AS version"))
            ->join('consultant_management_calling_rfp_verifiers', 'consultant_management_calling_rfp_verifiers.id', '=', 'consultant_management_call_rfp_verifier_versions.consultant_management_calling_rfp_verifier_id')
            ->join('consultant_management_calling_rfp', 'consultant_management_calling_rfp.id', '=', 'consultant_management_calling_rfp_verifiers.consultant_management_calling_rfp_id')
            ->where('consultant_management_calling_rfp.id', '=', $callingRfp->id)
            ->whereNull('consultant_management_calling_rfp_verifiers.deleted_at')
            ->groupBy('consultant_management_calling_rfp.id')
            ->first();

        $approvals = ConsultantManagementCallingRfpVerifierVersion::select("consultant_management_call_rfp_verifier_versions.id AS id", "users.id AS user_id",
            "consultant_management_call_rfp_verifier_versions.version", "consultant_management_call_rfp_verifier_versions.status", "consultant_management_call_rfp_verifier_versions.remarks", "consultant_management_call_rfp_verifier_versions.updated_at")
            ->join('consultant_management_calling_rfp_verifiers', 'consultant_management_calling_rfp_verifiers.id', '=', 'consultant_management_call_rfp_verifier_versions.consultant_management_calling_rfp_verifier_id')
            ->join('users', 'users.id', '=', 'consultant_management_calling_rfp_verifiers.user_id')
            ->join('consultant_management_calling_rfp', 'consultant_management_calling_rfp.id', '=', 'consultant_management_calling_rfp_verifiers.consultant_management_calling_rfp_id')
            ->where('consultant_management_calling_rfp.id', '=', $callingRfp->id)
            ->where('consultant_management_call_rfp_verifier_versions.version', '=', ($latestVersion) ? $latestVersion->version : -1)
            ->whereNull('consultant_management_calling_rfp_verifiers.deleted_at')
            ->orderBy('consultant_management_call_rfp_verifier_versions.id', 'asc')
            ->get();
        
        foreach($selectedVerifiers as $verifier)
        {
            $item = [
                'id'         => $verifier->id,
                'name'       => trim($verifier->name),
                'remarks'    => "",
                'version'    => ($latestVersion) ? $latestVersion->version : "",
                'status'     => ($latestVersion) ? ConsultantManagementCallingRfpVerifierVersion::STATUS_PENDING : "",
                'status_txt' => ($latestVersion) ? ConsultantManagementCallingRfpVerifierVersion::STATUS_PENDING_TEXT : "",
                'updated_at' => ""
            ];

            foreach($approvals as $approval)
            {
                if($approval->user_id == $verifier->id)
                {
                    $item['remarks']    = trim($approval->remarks);
                    $item['status']     = $approval->status;
                    $item['status_txt'] = $approval->getStatusText();
                    $item['updated_at'] =  date('d/m/Y H:i:s', strtotime($approval->updated_at));
                }
            }

            $data[] = $item;
        }

        return Response::json($data);
    }

    public function openRfpVerifierLogs(ConsultantManagementVendorCategoryRfp $vendorCategoryRfp)
    {
        $data = [];

        $rfpRevision = $vendorCategoryRfp->getLatestRfpRevision();

        if(!$rfpRevision)
        {
            return Response::json($data);
        }

        $openRfp = $rfpRevision->openRfp;

        if(!$openRfp)
        {
            return Response::json($data);
        }

        $selectedVerifiers = User::select("users.id", "users.name")
            ->join('consultant_management_open_rfp_verifiers', 'users.id', '=', 'consultant_management_open_rfp_verifiers.user_id')
            ->where('consultant_management_open_rfp_id', $openRfp->id)
            ->whereNull('consultant_management_open_rfp_verifiers.deleted_at')
            ->orderBy('consultant_management_open_rfp_verifiers.id', 'asc')
            ->get();

        $latestVersion = ConsultantManagementOpenRfpVerifierVersion::select(\DB::raw("MAX(version) AS version"))
            ->join('consultant_management_open_rfp_verifiers', 'consultant_management_open_rfp_verifiers.id', '=', 'consultant_management_open_rfp_verifier_versions.consultant_management_open_rfp_verifier_id')
            ->join('consultant_management_open_rfp', 'consultant_management_open_rfp.id', '=', 'consultant_management_open_rfp_verifiers.consultant_management_open_rfp_id')
            ->where('consultant_management_open_rfp.id', '=', $openRfp->id)
            ->whereNull('consultant_management_open_rfp_verifiers.deleted_at')
            ->groupBy('consultant_management_open_rfp.id')
            ->first();

        $approvals = ConsultantManagementOpenRfpVerifierVersion::select("consultant_management_open_rfp_verifier_versions.id AS id", "users.id AS user_id",
            "consultant_management_open_rfp_verifier_versions.version", "consultant_management_open_rfp_verifier_versions.status", "consultant_management_open_rfp_verifier_versions.remarks", "consultant_management_open_rfp_verifier_versions.updated_at")
            ->join('consultant_management_open_rfp_verifiers', 'consultant_management_open_rfp_verifiers.id', '=', 'consultant_management_open_rfp_verifier_versions.consultant_management_open_rfp_verifier_id')
            ->join('users', 'users.id', '=', 'consultant_management_open_rfp_verifiers.user_id')
            ->join('consultant_management_open_rfp', 'consultant_management_open_rfp.id', '=', 'consultant_management_open_rfp_verifiers.consultant_management_open_rfp_id')
            ->where('consultant_management_open_rfp.id', '=', $openRfp->id)
            ->where('consultant_management_open_rfp_verifier_versions.version', '=', ($latestVersion) ? $latestVersion->version : -1)
            ->whereNull('consultant_management_open_rfp_verifiers.deleted_at')
            ->orderBy('consultant_management_open_rfp_verifier_versions.id', 'asc')
            ->get();
        
        foreach($selectedVerifiers as $verifier)
        {
            $item = [
                'id'         => $verifier->id,
                'name'       => trim($verifier->name),
                'remarks'    => "",
                'version'    => ($latestVersion) ? $latestVersion->version : "",
                'status'     => ($latestVersion) ? ConsultantManagementOpenRfpVerifierVersion::STATUS_PENDING : "",
                'status_txt' => ($latestVersion) ? ConsultantManagementOpenRfpVerifierVersion::STATUS_PENDING_TEXT : "",
                'updated_at' => ""
            ];

            foreach($approvals as $approval)
            {
                if($approval->user_id == $verifier->id)
                {
                    $item['remarks']    = trim($approval->remarks);
                    $item['status']     = $approval->status;
                    $item['status_txt'] = $approval->getStatusText();
                    $item['updated_at'] =  date('d/m/Y H:i:s', strtotime($approval->updated_at));
                }
            }

            $data[] = $item;
        }

        return Response::json($data);
    }
}