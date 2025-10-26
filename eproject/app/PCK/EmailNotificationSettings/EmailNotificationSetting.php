<?php namespace PCK\EmailNotificationSettings;

use Illuminate\Database\Eloquent\Model;
use PCK\ModuleParameters\VendorManagement\VendorRegistrationAndPrequalificationModuleParameter;
use PCK\ModuleParameters\VendorManagement\VendorProfileModuleParameter;
use PCK\MyCompanyProfiles\MyCompanyProfile;

class EmailNotificationSetting extends Model
{
    protected $table = 'email_notification_settings';

    // external users
    const NOTIFICATION_TO_UNREGISTEDRED_VENDOR_ON_LOGIN_ACCOUNT_CREATION       = 1;
    const NOTIFICATION_TO_VENDOR_RFI_DURING_REGISTRATION_AND_RENEWAL           = 2;
    const REMINDER_TO_VENDOR_ON_DELETING_DATA_FOR_UNATTENDED_REGISTRATION_DATA = 3;
    const NOTIFICATION_TO_VENDOR_ON_SUCESSFUL_REGISTRATION_AND_RENEWAL         = 4;
    const NOTIFICATION_TO_VENDOR_ON_EXPIRY_OF_VENDOR_ACTIVE_STATUS             = 5;
    const REMINDER_TO_VENDOR_TO_RENEW                                          = 6;
    const NOTIFICATION_TO_VENDOR_ON_DEACTIVATING_VENDOR_ACCOUNT                = 7;

    // internal users
    const VENDOR_REGISTRATION_NOTIFICATION_TO_APPROVE_TEMPLATE                 = 8;
    const VENDOR_REGISTRATION_NOTIFICATION_TO_APPROVE_VENDOR_REGISTRATION      = 9;
    const NOTIFICATION_LEAD_EVALUATOR_START_OF_VPE                             = 10;
    const NOTIFICATION_LEAD_EVALUATOR_REMOVAL_OF_PROJECT_FROM_VPE_SUCCESSFUL   = 11;
    const NOTIFICATION_LEAD_EVALUATOR_REMOVAL_OF_PROJECT_FROM_VPE_UNSUCCESSFUL = 12;
    const NOTIFICATION_LEAD_EVALUATOR_VPE_SCORE_SUBMITTED_FROM_EVALUATOR       = 13;
    const NOTIFICATION_EVALUATOR_START_OF_VPE                                  = 14;
    const NOTIFICATION_EVALUATOR_VPE_REJECTION_AND_RESUBMISSION                = 15;
    const NOTIFICATION_INITIATOR_REQUEST_OF_REMOVAL_OF_PROJECT_FROM_VPE        = 16;

    public static function getEmailNotificationSettingsIdentifiersForExternalUsers()
    {
        return [
            self::NOTIFICATION_TO_UNREGISTEDRED_VENDOR_ON_LOGIN_ACCOUNT_CREATION,
            self::NOTIFICATION_TO_VENDOR_RFI_DURING_REGISTRATION_AND_RENEWAL,
            self::REMINDER_TO_VENDOR_ON_DELETING_DATA_FOR_UNATTENDED_REGISTRATION_DATA,
            self::NOTIFICATION_TO_VENDOR_ON_SUCESSFUL_REGISTRATION_AND_RENEWAL,
            self::NOTIFICATION_TO_VENDOR_ON_EXPIRY_OF_VENDOR_ACTIVE_STATUS,
            self::REMINDER_TO_VENDOR_TO_RENEW,
            self::NOTIFICATION_TO_VENDOR_ON_DEACTIVATING_VENDOR_ACCOUNT,
        ];
    }

    public static function getEmailNotificationSettingsIdentifiersForInternalUsers()
    {
        return [
            self::VENDOR_REGISTRATION_NOTIFICATION_TO_APPROVE_TEMPLATE,
            self::VENDOR_REGISTRATION_NOTIFICATION_TO_APPROVE_VENDOR_REGISTRATION,
            self::NOTIFICATION_LEAD_EVALUATOR_START_OF_VPE,
            self::NOTIFICATION_LEAD_EVALUATOR_REMOVAL_OF_PROJECT_FROM_VPE_SUCCESSFUL,
            self::NOTIFICATION_LEAD_EVALUATOR_REMOVAL_OF_PROJECT_FROM_VPE_UNSUCCESSFUL,
            self::NOTIFICATION_LEAD_EVALUATOR_VPE_SCORE_SUBMITTED_FROM_EVALUATOR,
            self::NOTIFICATION_EVALUATOR_START_OF_VPE,
            self::NOTIFICATION_EVALUATOR_VPE_REJECTION_AND_RESUBMISSION,
            self::NOTIFICATION_INITIATOR_REQUEST_OF_REMOVAL_OF_PROJECT_FROM_VPE,
        ]; 
    }

    public static function getEmailNotificationSettingDescriptions($identifier)
    {
        $mapping = [
            self::NOTIFICATION_TO_UNREGISTEDRED_VENDOR_ON_LOGIN_ACCOUNT_CREATION       => trans('emailNotificationSettings.notificationToUnregisteredVendorOnAccountCreation'),
            self::NOTIFICATION_TO_VENDOR_RFI_DURING_REGISTRATION_AND_RENEWAL           => trans('emailNotificationSettings.notificationToVendorRfiDuringRegistrationAndRenewal'),
            self::REMINDER_TO_VENDOR_ON_DELETING_DATA_FOR_UNATTENDED_REGISTRATION_DATA => trans('emailNotificationSettings.reminderToVendorOnDeletingDataForUnattendedRegistrationData'),
            self::NOTIFICATION_TO_VENDOR_ON_SUCESSFUL_REGISTRATION_AND_RENEWAL         => trans('emailNotificationSettings.notificationToVendorOnSucessfulRegistrationAndRenewal'),
            self::NOTIFICATION_TO_VENDOR_ON_EXPIRY_OF_VENDOR_ACTIVE_STATUS             => trans('emailNotificationSettings.notificationToVendorOnExpiryOfVendorActiveStatus'),
            self::REMINDER_TO_VENDOR_TO_RENEW                                          => trans('emailNotificationSettings.reminderToVendorToRenew'),
            self::NOTIFICATION_TO_VENDOR_ON_DEACTIVATING_VENDOR_ACCOUNT                => trans('emailNotificationSettings.notificationToVendorOnDeactivatingVendorAccount'),
            self::VENDOR_REGISTRATION_NOTIFICATION_TO_APPROVE_TEMPLATE                 => trans('emailNotificationSettings.vendorRegistrationNotificationToApproveTemplate'),
            self::VENDOR_REGISTRATION_NOTIFICATION_TO_APPROVE_VENDOR_REGISTRATION      => trans('emailNotificationSettings.vendorRegistrationNotificationToApproveVendorRegistration'),
            self::NOTIFICATION_LEAD_EVALUATOR_START_OF_VPE                             => trans('emailNotificationSettings.notificationLeadEvaluatorStartOfVpe'),
            self::NOTIFICATION_LEAD_EVALUATOR_REMOVAL_OF_PROJECT_FROM_VPE_SUCCESSFUL   => trans('emailNotificationSettings.notificationLeadEvaluatorRemovalOfProjectFromVpeSuccessful'),
            self::NOTIFICATION_LEAD_EVALUATOR_REMOVAL_OF_PROJECT_FROM_VPE_UNSUCCESSFUL => trans('emailNotificationSettings.notificationLeadEvaluatorRemovalOfProjectFromVpeUnsuccessful'),
            self::NOTIFICATION_LEAD_EVALUATOR_VPE_SCORE_SUBMITTED_FROM_EVALUATOR       => trans('emailNotificationSettings.notificationLeadEvaluatorVpeScoreSubmittedFromEvaluator'),
            self::NOTIFICATION_EVALUATOR_START_OF_VPE                                  => trans('emailNotificationSettings.notificationEvaluatorStartOfVpe'),
            self::NOTIFICATION_EVALUATOR_VPE_REJECTION_AND_RESUBMISSION                => trans('emailNotificationSettings.notificationEvaluatorVpeRejectionAndResubmission'),
            self::NOTIFICATION_INITIATOR_REQUEST_OF_REMOVAL_OF_PROJECT_FROM_VPE        => trans('emailNotificationSettings.notificationInitiatorRequestOfRemovalOfProjectFromVpe'),
        ];

        return is_null($identifier) ? $mapping : $mapping[$identifier];
    }

    public function getEmailSubject()
    {
        $myCompanyProfile = MyCompanyProfile::first();
        $emailSubject     = null;

        switch($this->setting_identifier)
        {
            case self::NOTIFICATION_TO_UNREGISTEDRED_VENDOR_ON_LOGIN_ACCOUNT_CREATION:
                $emailSubject = $myCompanyProfile->name . ' - ' . trans('vendorManagement.loginAccCreationSuccessful');
                break; 
            case self::NOTIFICATION_TO_VENDOR_RFI_DURING_REGISTRATION_AND_RENEWAL:
                $emailSubject = $myCompanyProfile->name . ' - ' . trans('vendorManagement.requestForInformation');
                break;
            case self::REMINDER_TO_VENDOR_ON_DELETING_DATA_FOR_UNATTENDED_REGISTRATION_DATA:
                $emailSubject = $myCompanyProfile->name . ' - ' . trans('vendorManagement.deletionOfRegistrationData');
                break;
            case self::NOTIFICATION_TO_VENDOR_ON_SUCESSFUL_REGISTRATION_AND_RENEWAL:
                $emailSubject = $myCompanyProfile->name . ' - ' . trans('vendorManagement.vendorRegistrationSuccessful');
                break;
            case self::NOTIFICATION_TO_VENDOR_ON_EXPIRY_OF_VENDOR_ACTIVE_STATUS:
                $emailSubject = $myCompanyProfile->name . ' - ' . trans('vendorManagement.expiryOfVendorAccountValidityPeriod');
                break;
            case self::REMINDER_TO_VENDOR_TO_RENEW:
                $emailSubject = $myCompanyProfile->name . ' - ' . trans('vendorManagement.reminderToRenewVendorAccount');
                break;
            case self::NOTIFICATION_TO_VENDOR_ON_DEACTIVATING_VENDOR_ACCOUNT:
                $emailSubject = $myCompanyProfile->name . ' - ' . trans('vendorManagement.vendorAccountDeactivated');
                break;
            default:
                // should not be happening
        }

        return  $emailSubject;
    }

    public function getPreviewContents()
    {
        $contents = [];

        array_push($contents, trans('companies.company') . ' : [' . trans('vendorManagement.companyName', ['companyName' => trans('companies.companyName')]). ']');

        switch($this->setting_identifier)
        {
            case self::NOTIFICATION_TO_UNREGISTEDRED_VENDOR_ON_LOGIN_ACCOUNT_CREATION:   
                array_push($contents, trans('vendorManagement.tempAccountCreatedValidForPeriod', ['days' => VendorRegistrationAndPrequalificationModuleParameter::getValue('valid_period_of_temp_login_acc_to_unreg_vendor_value')]));
                array_push($contents, trans('vendorManagement.url', ['url' => '[' . trans('vendorManagement.systemGeneratedLink') . ']']));
                array_push($contents, trans('vendorManagement.receivedEmailButDidNotRegister', ['here' => ('[' . trans('general.here') . ']')]));
                array_push($contents, trans('vendorManagement.logInUsingEmailFromNowOn', ['email' => ('[' . trans('general.email') . ']')]));
                break;
            case self::NOTIFICATION_TO_VENDOR_RFI_DURING_REGISTRATION_AND_RENEWAL:
                break;
            case self::REMINDER_TO_VENDOR_ON_DELETING_DATA_FOR_UNATTENDED_REGISTRATION_DATA:
                array_push($contents, trans('vendorManagement.registrationDataWillBeDeletedPermanently', ['period' => '[' . trans('vendorManagement.period') . ']', 'unit' => '[' . trans('vendorManagement.unit') . ']']));
                break;
            case self::NOTIFICATION_TO_VENDOR_ON_SUCESSFUL_REGISTRATION_AND_RENEWAL:
                array_push($contents, trans('vendorManagement.vendorRegistrationAndPreqIsSuccessful'));
                break;
            case self::NOTIFICATION_TO_VENDOR_ON_EXPIRY_OF_VENDOR_ACTIVE_STATUS:
                array_push($contents, trans('vendorManagement.vendorAccountValidityWillExpire', ['period' => '[' . trans('vendorManagement.period') . ']', 'unit' => '[' . trans('vendorManagement.unit') . ']']));
                break;
            case self::REMINDER_TO_VENDOR_TO_RENEW:
                array_push($contents, trans('vendorManagement.emailReminderVendorAccountWillBeDeleted', ['period' => '[' . trans('vendorManagement.period') . ']', 'unit' => '[' . trans('vendorManagement.unit') . ']']));
                break;
            case self::NOTIFICATION_TO_VENDOR_ON_DEACTIVATING_VENDOR_ACCOUNT:
                array_push($contents, trans('vendorManagement.vendorAccountHasBeenDeactivated'));
                break;
            default:
                // should not be happening
        }

        array_push($contents, nl2br($this->modifiable_contents));

        return $contents;
    }
}