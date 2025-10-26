<?php

class TestContractualClaimInterface implements \PCK\ContractualClaim\ContractualClaimInterface {
}

class EmailContentTest extends TestCase {

    public function testEmailSubject()
    {
        $project = new \PCK\Projects\Project;

        $project->reference = str_random(10);

        $this->doTestForSubject($project, null, trans('email.eProjectNotification'));

        $this->doTestForSubject($project, new \PCK\Conversations\Conversation, trans('email.eProjectNotification'));

        $this->doTestForSubject($project, new TestContractualClaimInterface, trans('email.eClaimNotification'));

        $this->doTestForSubject($project, new \PCK\TenderDocumentFolders\TenderDocumentFolder, trans('email.eTenderNotification'));

        $this->doTestForSubject($project, new \PCK\Tenders\Tender, trans('email.eTenderNotification'));

    }

    protected function doTestForSubject($project, $model, $expectedSubject)
    {
        $class = new ReflectionClass('PCK\Notifications\EmailNotifier');

        $generateEmailSubject = $class->getMethod('generateEmailSubject');
        $generateEmailSubject->setAccessible(true);

        $getEmailSubjectMessage = $class->getMethod('getEmailSubjectMessage');
        $getEmailSubjectMessage->setAccessible(true);

        $class = App::make('PCK\Notifications\EmailNotifier');

        $emailSubject = $generateEmailSubject->invokeArgs($class, array( $project, $getEmailSubjectMessage->invokeArgs($class, array( $model )) ));

        self::assertEquals($emailSubject, "[{$project->reference}] " . $expectedSubject);
    }
}