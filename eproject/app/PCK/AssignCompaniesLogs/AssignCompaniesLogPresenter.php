<?php namespace PCK\AssignCompaniesLogs;

use Carbon\Carbon;
use Laracasts\Presenter\Presenter;

class AssignCompaniesLogPresenter extends Presenter {

    public function log_text_format($count)
    {
        $createdBy       = $this->user;
        $updatedAt       = Carbon::parse($this->project->getProjectTimeZoneTime($this->updated_at))->format(\Config::get('dates.created_and_updated_at_formatting'));
        $operationText   = 'Created';
        $inDetailLogs    = null;
        $inDetailLogData = array();

        if( $count > 0 )
        {
            $operationText = 'Last Updated';
        }

        $returnText = "{$operationText} by {$createdBy->name} at <span class=\"dateSubmitted\">{$updatedAt}</span>";

        if( $this->tenderDocumentPermissionLog )
        {
            $inDetailLogData[] = "<strong>Group Access to Tender Documents:</strong> {$this->project->getRoleName($this->tenderDocumentPermissionLog->contractGroup->group)}";
        }

        foreach($this->inDetailLogs as $inDetailLog)
        {
            $inDetailLogData[] = "<strong>{$this->project->getRoleName($inDetailLog->contractGroup->group)}:</strong> {$inDetailLog->company->name}";
        }

        if( count($inDetailLogData) )
        {
            $inDetailLogs = implode(', ', $inDetailLogData);

            return "{$returnText}:<br/>{$inDetailLogs}";
        }

        return $returnText;
    }

}