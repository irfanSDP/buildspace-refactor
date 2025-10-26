<?php

Event::listen('user.newlyRegistered', 'PCK\SystemEvents\UserEvents@sendNewlyRegisteredEmail');
Event::listen('vendor.newlyRegistered', 'PCK\SystemEvents\UserEvents@sendNewlyRegisteredVendorEmail');

Event::listen('system.updateProjectStatus', 'PCK\SystemEvents\ProjectEvents@updateProjectStatus');
Event::listen('system.updateTenderFormStatus', 'PCK\SystemEvents\TenderEvents@updateTenderStatus');
Event::listen('system.updateTechnicalEvaluationStatus', 'PCK\SystemEvents\TenderEvents@updateTechnicalEvaluationStatus');

Event::listen('system.notifyOpenTenderVerifiers', 'PCK\SystemEvents\OpenTenderEvents@sendMailRequestingVerification');
Event::listen('system.notifyTechnicalEvaluationVerifiers', 'PCK\SystemEvents\OpenTenderEvents@sendMailRequestingTechnicalEvaluationVerification');

Event::listen('system.sendEmailNotification', 'PCK\Notifications\EmailNotifier@sendNotification');
Event::listen('system.sendEmailNotificationByUsers', 'PCK\Notifications\EmailNotifier@sendNotificationByUsers');

Event::listen('system.sendSystemNotification', 'PCK\Notifications\SystemNotifier@sendNotification');
Event::listen('system.sendSystemNotificationByUsers', 'PCK\Notifications\SystemNotifier@sendNotificationByUsers');

Event::listen('system.sendSystemNotificationToCompanyAdminOnly', 'PCK\Notifications\SystemNotifier@sendNotificationToCompanyAdmin');
Event::listen('system.sendProjectDocumentSystemNotificationToSelectedGroupUsers', 'PCK\Notifications\SystemNotifier@sendProjectDocumentNotificationToSelectedGroupUsers');

Event::listen('tenderForms.sendNotification', 'PCK\Notifications\EmailNotifier@sendTenderVerifierNotification');
Event::listen('tenderForms.sendNotification', 'PCK\Notifications\SystemNotifier@sendTenderVerifierNotification');

Event::listen('tenderForms.sendSystemNotification', 'PCK\Notifications\SystemNotifier@sendTenderVerifierNotification');

Event::listen('system.sendEmailNotificationForConfirmationStatus', 'PCK\Notifications\EmailNotifier@sendNotificationForConfirmationStatus');
Event::listen('system.sendEmailNotificationForConfirmationStatusReply', 'PCK\Notifications\EmailNotifier@sendNotificationForConfirmationStatusReply');

Event::listen('vendorWorkCategory.workCategoriesUpdated', 'PCK\SystemEvents\VendorManagementEvents@vendorWorkCategorySyncWorkCategories');

Event::listen('auth.login', function($user) {
    $now = \Carbon\Carbon::now();

    $rows[] = [
        'user_id'    => $user->id,
        'created_at' => $now,
        'updated_at' => $now,
    ];

    \DB::table('user_logins')->insert($rows);
});