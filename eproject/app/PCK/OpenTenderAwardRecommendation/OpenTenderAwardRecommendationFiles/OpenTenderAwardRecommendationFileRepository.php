<?php namespace PCK\OpenTenderAwardRecommendation\OpenTenderAwardRecommendationFiles;

use PCK\Projects\Project;
use PCK\Users\User;
use PCK\Helpers\DataTables;

class OpenTenderAwardRecommendationFileRepository {

    public function getUploadedFiles(Project $project, $tenderId) {
        $inputs = \Input::all();
        $user   = \Confide::user();

        $idColumn      = "open_tender_award_recommendation_files.id";
        $selectColumns = [$idColumn];

        $fileNameColumn = array(
            'fileName' => 0,
        );

        $allColumns = array(
            'open_tender_award_recommendation_files' => $fileNameColumn
        );

        $query = OpenTenderAwardRecommendationFile::where('tender_id', $tenderId)->orderBy('id', 'ASC');

        $dataTable = new DataTables($query, $inputs, $allColumns, $idColumn, $selectColumns);

        $dataTable->addAllStatements();
        $results = $dataTable->getResults();
        $dataArray = [];

        $routeString = (is_null($user->getAssignedCompany($project)) && $user->isTopManagementVerifier()) ? 'topManagementVerifiers.open_tender.award_recommendation.report.attachment.download' : 'open_tender.award_recommendation.report.attachment.download';

        foreach ( $results as $index => $object )
        {
            $indexNo = ( $index + 1 ) + ( $dataTable->properties->pagingOffset );
            $record = OpenTenderAwardRecommendationFile::find($object->id);

            array_push($dataArray, [
                'indexNo'           => $indexNo,
                'cabinet_id'        => $record->fileProperties->id,
                'fileName'          => $record->fileProperties->filename,
                'uploaded_by'       => User::find($record->fileProperties->user_id)->name,
                'date'              => $project->getProjectTimeZoneTime(\Carbon\Carbon::parse($record->fileProperties->created_at))->format(\Config::get('dates.submission_date_formatting')),
                'delete_route'      => route('open_tender.award_recommendation.report.attachment.delete', [$project->id, $record->fileProperties->id]),
                'download_route'    => route($routeString, [$project->id, $record->fileProperties->id]),
            ]);
        }

        return $dataTable->dataTableResponse($dataArray);
    }

    public function download(Project $project, $id)
    {
        $file = OpenTenderAwardRecommendationFile::where('cabinet_file_id', $id)->first();
        $cabinetInfo = $file->fileProperties;

        if( ! $file )
        {
            \App::abort(404);
        }

        return \PCK\Helpers\Files::download(
            $cabinetInfo->physicalPath() . $cabinetInfo->filename,
            $file->filename . '.' . $cabinetInfo->extension, array(
            'Content-Type: ' . $cabinetInfo->mimetype,
        ));
    }

    public function uploadDelete(Project $project, $id)
    {
        $file = OpenTenderAwardRecommendationFile::where('cabinet_file_id', $id)->first();
        $file->fileProperties->delete();
        $file->delete();

        return $file->fileProperties->filename;
    }
}

