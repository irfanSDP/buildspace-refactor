<?php namespace PCK\VendorManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;
use PCK\SystemModules\SystemModuleConfiguration;
use PCK\Users\User;

class VendorManagementUserPermission extends Model {

    use SoftDeletingTrait;

    const TYPE_DASHBOARD                 = 1;
    const TYPE_VENDOR_PROFILE_VIEW       = 2;
    const TYPE_VENDOR_PROFILE_EDIT       = 3;
    const TYPE_APPROVAL_REGISTRATION     = 4;
    const TYPE_ACTIVE_VENDOR_LIST        = 5;
    const TYPE_FORM_TEMPLATES            = 6;
    const TYPE_PERFORMANCE_EVALUATION    = 7;
    const TYPE_PAYMENT                   = 8;
    const TYPE_SETTINGS_AND_MAINTENANCE  = 9;
    const TYPE_GRADE_MAINTENANCE         = 10;
    const TYPE_NOMINATED_WATCH_LIST_VIEW = 11;
    const TYPE_NOMINATED_WATCH_LIST_EDIT = 12;
    const TYPE_WATCH_LIST_VIEW           = 13;
    const TYPE_WATCH_LIST_EDIT           = 14;
    const TYPE_DEACTIVATED_VENDOR_LIST   = 15;
    const TYPE_UNSUCCESSFUL_VENDOR_LIST  = 16;
    const TYPE_VENDOR_REGISTRATION_VERIFIER = 17;
    const TYPE_DIGITAL_STAR = 18;
    const TYPE_DIGITAL_STAR_TEMPLATE = 19;
    const TYPE_DIGITAL_STAR_DASHBOARD = 20;

    const TYPE_DIGITAL_STAR_EVALUATOR = 21;
    const TYPE_DIGITAL_STAR_EVALUATOR_COMPANY = 22;     // Note: Company evaluators are Processors
    const TYPE_DIGITAL_STAR_EVALUATOR_PROJECT = 23;

    const TYPE_DIGITAL_STAR_VERIFIER = 24;
    const TYPE_DIGITAL_STAR_VERIFIER_COMPANY = 25;
    const TYPE_DIGITAL_STAR_VERIFIER_PROJECT = 26;

    const TYPE_VENDOR_LISTS        = 5;

    protected $fillable = ['user_id', 'type', 'created_by', 'updated_by'];

    public function user()
    {
        return $this->belongsTo('PCK\Users\User');
    }

    public static function getAllTypes()
    {
        $data = [
            self::TYPE_VENDOR_REGISTRATION_VERIFIER,
            self::TYPE_DASHBOARD,
            self::TYPE_VENDOR_PROFILE_VIEW,
            self::TYPE_VENDOR_PROFILE_EDIT,
            self::TYPE_APPROVAL_REGISTRATION,
            self::TYPE_ACTIVE_VENDOR_LIST,
            self::TYPE_NOMINATED_WATCH_LIST_VIEW,
            self::TYPE_NOMINATED_WATCH_LIST_EDIT,
            self::TYPE_WATCH_LIST_VIEW,
            self::TYPE_WATCH_LIST_EDIT,
            self::TYPE_DEACTIVATED_VENDOR_LIST,
            self::TYPE_UNSUCCESSFUL_VENDOR_LIST,
            self::TYPE_PERFORMANCE_EVALUATION,
            self::TYPE_FORM_TEMPLATES,
            self::TYPE_PAYMENT,
            self::TYPE_SETTINGS_AND_MAINTENANCE,
            self::TYPE_GRADE_MAINTENANCE,
        ];

        if (SystemModuleConfiguration::isEnabled(SystemModuleConfiguration::MODULE_ID_DIGITAL_STAR)) {
            $data[] = self::TYPE_DIGITAL_STAR_TEMPLATE;
            $data[] = self::TYPE_DIGITAL_STAR;
            $data[] = self::TYPE_DIGITAL_STAR_DASHBOARD;
            $data[] = self::TYPE_DIGITAL_STAR_EVALUATOR_COMPANY;
            $data[] = self::TYPE_DIGITAL_STAR_EVALUATOR_PROJECT;
            $data[] = self::TYPE_DIGITAL_STAR_VERIFIER_COMPANY;
            $data[] = self::TYPE_DIGITAL_STAR_VERIFIER_PROJECT;
        }
        return $data;
    }

    public static function getGroupSubTypes($type) {
        switch ($type) {
            case self::TYPE_DIGITAL_STAR_EVALUATOR:
                return [
                    self::TYPE_DIGITAL_STAR_EVALUATOR_COMPANY,
                    self::TYPE_DIGITAL_STAR_EVALUATOR_PROJECT,
                ];
            case self::TYPE_DIGITAL_STAR_VERIFIER:
                return [
                    self::TYPE_DIGITAL_STAR_VERIFIER_COMPANY,
                    self::TYPE_DIGITAL_STAR_VERIFIER_PROJECT,
                ];
            default:
                return [];
        }
    }

    public static function getHeaders()
    {
        $data = [
            self::TYPE_VENDOR_REGISTRATION_VERIFIER => trans('vendorManagement.registrationVerifier'),
            self::TYPE_DASHBOARD                    => trans('vendorManagement.dashboard'),
            self::TYPE_VENDOR_PROFILE_VIEW          => trans('vendorManagement.vendorProfile(view)'),
            self::TYPE_VENDOR_PROFILE_EDIT          => trans('vendorManagement.vendorProfile(edit)'),
            self::TYPE_APPROVAL_REGISTRATION        => trans('vendorManagement.registrationApproval'),
            self::TYPE_ACTIVE_VENDOR_LIST           => trans('vendorManagement.activeVendorList'),
            self::TYPE_NOMINATED_WATCH_LIST_VIEW    => trans('vendorManagement.nomineesForWatchListView'),
            self::TYPE_NOMINATED_WATCH_LIST_EDIT    => trans('vendorManagement.nomineesForWatchListEdit'),
            self::TYPE_WATCH_LIST_VIEW              => trans('vendorManagement.watchListView'),
            self::TYPE_WATCH_LIST_EDIT              => trans('vendorManagement.watchListEdit'),
            self::TYPE_DEACTIVATED_VENDOR_LIST      => trans('vendorManagement.deactivatedVendorList'),
            self::TYPE_UNSUCCESSFUL_VENDOR_LIST     => trans('vendorManagement.unsuccessfulVendorList'),
            self::TYPE_PERFORMANCE_EVALUATION       => trans('vendorManagement.vendorPerformanceEvaluation'),
            self::TYPE_FORM_TEMPLATES               => trans('vendorManagement.formTemplates'),
            self::TYPE_PAYMENT                      => trans('vendorManagement.payment'),
            self::TYPE_SETTINGS_AND_MAINTENANCE     => trans('vendorManagement.settingsAndMaintenance'),
            self::TYPE_GRADE_MAINTENANCE            => trans('vendorManagement.gradeManagement'),
        ];

        if (SystemModuleConfiguration::isEnabled(SystemModuleConfiguration::MODULE_ID_DIGITAL_STAR)) {
            $data[self::TYPE_DIGITAL_STAR_TEMPLATE] = trans('digitalStar/userPermission.digitalStarTemplate');
            $data[self::TYPE_DIGITAL_STAR] = trans('digitalStar/userPermission.digitalStar');
            $data[self::TYPE_DIGITAL_STAR_DASHBOARD] = trans('digitalStar/userPermission.digitalStarDashboard');
            $data[self::TYPE_DIGITAL_STAR_EVALUATOR] = trans('digitalStar/userPermission.digitalStarEvaluator');
            $data[self::TYPE_DIGITAL_STAR_VERIFIER] = trans('digitalStar/userPermission.digitalStarVerifier');
        }
        return $data;
    }

    public static function getSubHeaders($group) {
        switch ($group) {
            case self::TYPE_DIGITAL_STAR_EVALUATOR:
                return [
                    self::TYPE_DIGITAL_STAR_EVALUATOR_COMPANY => trans('digitalStar/userPermission.digitalStarEvaluatorCompany'),
                    self::TYPE_DIGITAL_STAR_EVALUATOR_PROJECT => trans('digitalStar/userPermission.digitalStarEvaluatorProject'),
                ];
            case self::TYPE_DIGITAL_STAR_VERIFIER:
                return [
                    self::TYPE_DIGITAL_STAR_VERIFIER_COMPANY => trans('digitalStar/userPermission.digitalStarVerifierCompany'),
                    self::TYPE_DIGITAL_STAR_VERIFIER_PROJECT => trans('digitalStar/userPermission.digitalStarVerifierProject'),
                ];
            default:
                return [];
        }
    }

    public static function getTypeNames()
    {
        $data = [
            self::TYPE_VENDOR_REGISTRATION_VERIFIER => trans('vendorManagement.registrationVerifier'),
            self::TYPE_DASHBOARD                    => trans('vendorManagement.dashboard'),
            self::TYPE_VENDOR_PROFILE_VIEW          => trans('vendorManagement.vendorProfile(view)'),
            self::TYPE_VENDOR_PROFILE_EDIT          => trans('vendorManagement.vendorProfile(edit)'),
            self::TYPE_APPROVAL_REGISTRATION        => trans('vendorManagement.registrationApproval'),
            self::TYPE_ACTIVE_VENDOR_LIST           => trans('vendorManagement.activeVendorList'),
            self::TYPE_NOMINATED_WATCH_LIST_VIEW    => trans('vendorManagement.nomineesForWatchListView'),
            self::TYPE_NOMINATED_WATCH_LIST_EDIT    => trans('vendorManagement.nomineesForWatchListEdit'),
            self::TYPE_WATCH_LIST_VIEW              => trans('vendorManagement.watchListView'),
            self::TYPE_WATCH_LIST_EDIT              => trans('vendorManagement.watchListEdit'),
            self::TYPE_DEACTIVATED_VENDOR_LIST      => trans('vendorManagement.deactivatedVendorList'),
            self::TYPE_UNSUCCESSFUL_VENDOR_LIST     => trans('vendorManagement.unsuccessfulVendorList'),
            self::TYPE_PERFORMANCE_EVALUATION       => trans('vendorManagement.vendorPerformanceEvaluation'),
            self::TYPE_FORM_TEMPLATES               => trans('vendorManagement.formTemplates'),
            self::TYPE_PAYMENT                      => trans('vendorManagement.payment'),
            self::TYPE_SETTINGS_AND_MAINTENANCE     => trans('vendorManagement.settingsAndMaintenance'),
            self::TYPE_GRADE_MAINTENANCE            => trans('vendorManagement.gradeManagement'),
        ];

        if (SystemModuleConfiguration::isEnabled(SystemModuleConfiguration::MODULE_ID_DIGITAL_STAR)) {
            $data[self::TYPE_DIGITAL_STAR_TEMPLATE] = trans('digitalStar/userPermission.digitalStarTemplate');
            $data[self::TYPE_DIGITAL_STAR]                 = trans('digitalStar/userPermission.digitalStar');
            $data[self::TYPE_DIGITAL_STAR_DASHBOARD]       = trans('digitalStar/userPermission.digitalStarDashboard');
            $data[self::TYPE_DIGITAL_STAR_EVALUATOR]       = trans('digitalStar/userPermission.digitalStarEvaluator');
            $data[self::TYPE_DIGITAL_STAR_EVALUATOR_COMPANY] = trans('digitalStar/userPermission.digitalStarEvaluatorCompany');
            $data[self::TYPE_DIGITAL_STAR_EVALUATOR_PROJECT] = trans('digitalStar/userPermission.digitalStarEvaluatorProject');
            $data[self::TYPE_DIGITAL_STAR_VERIFIER]          = trans('digitalStar/userPermission.digitalStarVerifier');
            $data[self::TYPE_DIGITAL_STAR_VERIFIER_COMPANY] = trans('digitalStar/userPermission.digitalStarVerifierCompany');
            $data[self::TYPE_DIGITAL_STAR_VERIFIER_PROJECT] = trans('digitalStar/userPermission.digitalStarVerifierProject');
        }
        return $data;
    }

    public static function updatePermissions(array $userIds, array $types, bool $grant)
    {
        $currentUser = \Confide::user();
        $timestamp   = \Carbon\Carbon::now();

        foreach($types as $type)
        {
            if($grant)
            {
                $existingRecordIds = self::whereIn('user_id', $userIds)
                    ->where('type', '=', $type)
                    ->lists('id');

                $existingRecordUserIds = self::whereIn('id', $existingRecordIds)
                    ->lists('user_id');

                $nonExistantRecordUserIds = array_diff($userIds, $existingRecordUserIds);

                self::whereIn('id', $existingRecordIds)->update(array(
                    'updated_by' => $currentUser->id,
                    'updated_at' => $timestamp,
                ));

                $records = array();

                foreach($nonExistantRecordUserIds as $newUserId)
                {
                    $records[] = array(
                        'user_id'    => $newUserId,
                        'type'       => $type,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                        'created_by' => $currentUser->id,
                        'updated_by' => $currentUser->id,
                    );
                }

                if(!empty($records)) self::insert($records);
            }
            else
            {
                self::whereIn('user_id', $userIds)
                    ->where('type', '=', $type)
                    ->update(array(
                        'updated_by' => $currentUser->id,
                        'deleted_at' => $timestamp,
                    ));
            }
        }
    }

    public static function getUserPermissions()
    {
        $output = [];

        $userPermissions = VendorManagementUserPermission::all();

        foreach($userPermissions->groupBy('user_id') as $userId => $permissions)
        {
            $output[$userId] = [];

            foreach($permissions as $permission)
            {
                $output[$userId][] = $permission->type;
            }
        }

        return $output;
    }

    public static function getPermissionUsers()
    {
        $output = [];

        $userPermissions = VendorManagementUserPermission::all();

        foreach($userPermissions->groupBy('type') as $type => $permissions)
        {
            $output[$type] = [];

            foreach($permissions as $permission)
            {
                $output[$type][] = $permission->user_id;
            }
        }

        return $output;
    }

    public static function hasPermission($user, $permissionType)
    {
        return self::where('user_id', '=', $user->id)
            ->where('type', '=', $permissionType)
            ->exists();
    }

    public static function getUserIds($permissionType)
    {
        $permissionUsers = self::getPermissionUsers();
        return array_key_exists($permissionType, $permissionUsers) ? $permissionUsers[$permissionType] : [];
    }

    public static function getUsers($permissionType)
    {
        $userIds = self::getUserIds($permissionType);

        return User::whereIn('id', $userIds)
            ->orderBy('id', 'desc')
            ->get();
    }
}