<?php

use PCK\Base\Upload;
use PCK\Projects\Project;
use PCK\Tenders\Tender;
use PCK\OpenTenderAwardRecommendation\OpenTenderAwardRecommendation;
use PCK\OpenTenderAwardRecommendation\OpenTenderAwardRecommendationStatus;
use PCK\OpenTenderAwardRecommendation\OpenTenderAwardRecommendationFiles\OpenTenderAwardRecommendationFile;
use PCK\OpenTenderAwardRecommendation\OpenTenderAwardRecommendationFiles\OpenTenderAwardRecommendationFileRepository;
use PCK\Filters\OpenTenderFilters;

class OpenTenderAwardRecommendationAttachmentsController extends \BaseController {

    private $fileRepository;

    public function __construct(OpenTenderAwardRecommendationFileRepository $fileRepository)
    {
        $this->fileRepository = $fileRepository;
    }

    public function index(Project $project, $tenderId) {
        $user   = \Confide::user();
        $tender = Tender::find($tenderId);

        $getAttachmentsRouteString = (is_null($user->getAssignedCompany($project)) && $user->isTopManagementVerifier()) ? 'topManagementVerifiers.open_tender.award_recommendation.report.attachment.get' : 'open_tender.award_recommendation.report.attachment.get';

        $canEdit = true;

        if($user->isTopManagementVerifier() && ! $user->hasCompanyProjectRole($project, OpenTenderFilters::accessRoles($project)))
        {
            $canEdit = false;
        }

        $data = [
            'user'                  => $user,
            'project'               => $project,
            'tender'                => $tender,
            'canUploadDeleteFile'   => $tender->openTenderAwardRecommendtion->status == OpenTenderAwardRecommendationStatus::EDITABLE && $user->isEditor($project) && $canEdit,
            'isEditor'              => $user->isEditor($project),
            'getAttachmentsRoute'   => route($getAttachmentsRouteString, [$project->id, $tender->id]),
        ];

        return View::make('open_tender_award_recommendation.attachments.index', $data);
    }

    public function getUploadedFiles(Project $project, $tenderId) {
        return $this->fileRepository->getUploadedFiles($project, $tenderId);
    }

    public function upload(Project $project, $tenderId) {
        $inputs = Input::all();
        $file = $inputs['file'];
        $tenderId = $inputs['tenderId'];
        $upload = new Upload;

        try
        {
            $upload->process($file);
        }
        catch(Exception $exception)
        {
            // Something went wrong. Log it.
            Log::error($exception);
            $errors = array(
                'name'  => $file->getClientOriginalName(),
                'size'  => $file->getSize(),
                'error' => $exception->getMessage()
            );

            // Return error
            return Response::json($errors, 400);
        }

        if( $upload->id )
        {
            $fileParts = pathinfo($upload->filename);

            $openTenderAwardRecommendationFile = new OpenTenderAwardRecommendationFile();
            $openTenderAwardRecommendationFile->tender_id = $tenderId;
            $openTenderAwardRecommendationFile->filename = $fileParts['filename'];
            $openTenderAwardRecommendationFile->cabinet_file_id = $upload->id;
            $openTenderAwardRecommendationFile->save();

            $success               = new stdClass();
            $success->name         = $upload->filename;
            $success->size         = $upload->size;
            $success->url          = $upload->download_url;
            $success->thumbnailUrl = $upload->generateThumbnailURL();
            $success->deleteUrl    = route('open_tender.award_recommendation.report.attachment.delete', [$project->id, $upload->id]);
            $success->deleteType   = 'POST';
            $success->fileID       = $upload->id;

            return Response::json(array( 'files' => array( $success ) ), 200);
        }
    }

    public function download($project, $id) {
       return $this->fileRepository->download($project, $id);
    }

    public function uploadDelete($project, $id) {
        if( ! Request::ajax() )
        {
            App::abort(404);
        }

        $fileName = $this->fileRepository->uploadDelete($project, $id);

        $success = new stdClass();
        $success->{$fileName} = true;

        return Response::json(array( 'files' => array( $success ) ), 200);
    }
}
