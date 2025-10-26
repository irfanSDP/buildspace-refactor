<?php

use PCK\Helpers\Key;
use PCK\TenderInterviews\TenderInterviewRepository;
use PCK\Settings\Language;
use PCK\TenderInterviews\TenderInterview;

class TenderInterviewsController extends \BaseController {

    private $tenderInterviewRepository;

    public function __construct(TenderInterviewRepository $tenderInterviewRepository)
    {
        $this->tenderInterviewRepository = $tenderInterviewRepository;
    }

    /**
     * Gets the data for the tender interviews of the tender.
     * Formatted for dataTables.
     *
     * @param $project
     * @param $tenderId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTenderInterviewData($project, $tenderId)
    {
        $selectedContractorsId = Input::get('selectedContractors') ? Input::get('selectedContractors') : array();

        $results = $this->tenderInterviewRepository->getData(Input::all(), $tenderId, $selectedContractorsId);

        foreach($results['aaData'] as $i => $record)
        {
            $time = $project->getProjectTimeZoneTime($record['time']);

            $results["aaData"][ $i ]["time"] = \Carbon\Carbon::parse($time)->format(Config::get('dates.time_only'));
            $results["aaData"][ $i ]["date"] = \Carbon\Carbon::parse($time)->format('Y-m-d');
        }

        return Response::json($results);
    }

    /**
     * Updates the tender interview.
     *
     * @param $project
     * @param $tenderId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateTenderInterview($project, $tenderId)
    {
        $input = Input::all();

        $input['date']           = $project->getAppTimeZoneTime($input['date'] ?? null);
        $input['discussionTime'] = $project->getAppTimeZoneTime($input['discussionTime'] ?? null);

        foreach($input['companies'] as $key => $companyData)
        {
            $input['companies'][ $key ]['time'] = $project->getAppTimeZoneTime($companyData['time'] ?? null);
        }

        $success = $this->tenderInterviewRepository->updateTenderInterview($tenderId, $input);

        return Response::json(array( 'success' => $success ));
    }

    /**
     * Sends an email request for a tender interview to the selected (and not deleted) contractors.
     *
     * @param $project
     * @param $tenderId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendTenderInterview($project, $tenderId)
    {
        $input = Input::all();

        $input['date']           = $project->getAppTimeZoneTime($input['date'] ?? null);
        $input['discussionTime'] = $project->getAppTimeZoneTime($input['discussionTime'] ?? null);

        foreach($input['companies'] as $key => $companyData)
        {
            $input['companies'][ $key ]['time'] = $project->getAppTimeZoneTime($companyData['time'] ?? null);
        }

        $success = $this->tenderInterviewRepository->updateTenderInterview($tenderId, $input);

        if( ! $success ) return Response::json(array( 'success' => false ));

        try
        {
            $this->tenderInterviewRepository->sendTenderMeetingRequest($project, $tenderId);
            $this->tenderInterviewRepository->sendRequestToContractors($project, $tenderId);

            $success = true;
        }
        catch(Exception $exception)
        {
            $success = false;
        }

        return Response::json(array( 'success' => $success ));
    }

    /**
     * Returns a view for contractors to confirm their attendance for the interview.
     *
     * @param $key
     *
     * @return \Illuminate\View\View
     */
    public function request($key)
    {
        if( ! Key::keyInTable('tender_interviews', $key, 'key') ) return View::make('errors/404');

        $user                     = Confide::user();
        $userLocale               = $user ? $user->settings->language->code : getenv('DEFAULT_LANGUAGE_CODE');
        $languages                = Language::getLanguageListing();
        $translatedTextByLanguage = $this->getTenderInterviewTranslations(array_keys($languages));

        return View::make('unauthenticated_forms.confirmStatus', array(
            'title'            => 'Tender Interview',
            'listOptions'      => $this->tenderInterviewRepository->getStatusDropDownListing(),
            'route'            => 'tender_interview.confirm',
            'key'              => $key,
            'user'             => $user,
            'userLocale'       => $userLocale,
            'translatedText'   => json_encode($translatedTextByLanguage),
            'languages'        => $languages,
        ));
    }

    private function getTenderInterviewTranslations(array $languageIds)
    {
        foreach($languageIds as $languageId)
        {
            $translations[$languageId] = array(
                'languageLabel'                  => trans('settings.language', [], 'messages', $languageId),
                'pleaseConfirmInterestToTender'  => trans('tenders.pleaseConfirmInterestToTender', [], 'messages', $languageId),
                'currentlyLoggedInAs'            => trans('projects.currentlyLoggedInAs',[], 'messages', $languageId),
                'project'                        => trans('projects.project', [], 'messages', $languageId),
                'descriptionOfWork'              => trans('projects.descriptionOfWork', [], 'messages', $languageId),
                'statusConfirmationIsSuccessful' => trans('projects.statusConfirmationIsSuccessful', [], 'messages', $languageId),
                'commitmentYes'                  => TenderInterview::getText(TenderInterview::STATUS_YES, $languageId),
                'commitmentNo'                   => TenderInterview::getText(TenderInterview::STATUS_NO, $languageId),
            );
        }

        return $translations;
    }

    public function confirmStatus($key)
    {
        $input = Input::all();
        $success = false;

        if( ! Key::keyInTable('tender_interviews', $key, 'key') )
        {
            Flash::error('Oops, something went wrong. The link may be outdated. Please try again later, and check for updates.');
        }
        else
        {
            $success = $this->tenderInterviewRepository->saveReply($key, $input['option']);
        }

        return View::make('unauthenticated_forms.statusConfirmed', array(
            'title'            => 'Tender Interview',
            'success'          => $success,
            'translatedText'   => json_encode($this->getTenderInterviewTranslations(array($input['selectedLocale']))),
            'userLocale'       => $input['selectedLocale'],
        ));
    }

}