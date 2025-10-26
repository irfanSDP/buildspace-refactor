<?php namespace PCK\Reports;

use PCK\Companies\Company;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class TenderersReportGenerator extends ReportGenerator {

    private $colNumber              = 'B';
    private $colName                = 'C';
    private $colSubmittedDate       = 'D';
    private $colTenderAmount        = 'E';
    private $colWithdrawnTenders    = 'F';
    private $colParticipatedTenders = 'G';
    private $colOngoingProjects     = 'H';
    private $colCompletedProjects   = 'I';

    public function generate($project)
    {
        $this->currentRow = 2;

        $this->createHeader();
        $this->setColumnWidths();

        $records = Company::select('companies.id', 'companies.name', 'company_tender.tender_amount', 'company_tender.updated_at', 'company_tender.submitted')
            ->join('company_tender', 'company_tender.company_id', '=', 'companies.id')
            ->join('tenders', 'tenders.id', '=', 'company_tender.tender_id')
            ->join('projects', 'projects.id', '=', 'tenders.project_id')
            ->where('tenders.id', '=', $project->latestTender->id)
            ->whereNull('projects.deleted_at')
            ->orderBy('companies.name', 'asc')
            ->get();

        $recordCount = $records->count();

        foreach($records as $key => $record)
        {
            $this->currentRow++;

            $isLastRecord = (($key +1) === $recordCount);
            $itemRowStyle = $isLastRecord ? $this->getLastItemRowStyle() : $this->getItemRowStyle();

            $this->activeSheet->getStyle("{$this->colNumber}{$this->currentRow}:{$this->colCompletedProjects}{$this->currentRow}")->applyFromArray($itemRowStyle);

            $this->activeSheet->setCellValue("{$this->colNumber}{$this->currentRow}", ($key + 1));
            $this->activeSheet->setCellValue("{$this->colName}{$this->currentRow}", $record->name);
            $this->activeSheet->setCellValue("{$this->colSubmittedDate}{$this->currentRow}", $record->submitted ? $project->getProjectTimeZoneTime($record->updated_at)->format(\Config::get('dates.submitted_at')) : null);
            $this->activeSheet->setCellValue("{$this->colTenderAmount}{$this->currentRow}", $record->submitted ? $record->tender_amount : null);
            $this->activeSheet->setCellValue("{$this->colWithdrawnTenders}{$this->currentRow}", $record->getWithdrawnTenders()->count());
            $this->activeSheet->setCellValue("{$this->colParticipatedTenders}{$this->currentRow}", $record->getParticipatedLatestTenders()->count());
            $this->activeSheet->setCellValue("{$this->colOngoingProjects}{$this->currentRow}", $record->ongoingProjects->count());
            $this->activeSheet->setCellValue("{$this->colCompletedProjects}{$this->currentRow}", $record->completedProjects->count());

            $this->activeSheet->getStyle("{$this->colTenderAmount}{$this->currentRow}")->applyFromArray(['alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
                'vertical'   => Alignment::VERTICAL_CENTER
            ]]);
        }

        $this->activeSheet->setTitle(trans('tenders.tenderersReport'));

        return $this->output($this->spreadsheet, trans('tenders.tenderersReport'));
    }
    
    private function createHeader()
    {
        $headerStartRow = $this->currentRow;

        $this->addHeaderColumns([
            trans('general.no'),
            trans('companies.name'),
            trans('tenders.submittedDate'),
            trans('tenders.amount'),
            trans('tenders.withdrawnTenders'),
            trans('tenders.participatedTenders'),
            trans('tenders.ongoingProjects'),
            trans('tenders.completedProjects'),
        ], $this->colNumber, $headerStartRow);

        $this->activeSheet->getStyle("{$this->colTenderAmount}")->getNumberFormat()->setFormatCode("#,##0.00");
    }

    private function setColumnWidths()
    {
        $this->activeSheet->getColumnDimension("A")->setWidth(1.3);
        $this->activeSheet->getColumnDimension("{$this->colNumber}")->setWidth(10);
        $this->activeSheet->getColumnDimension("{$this->colName}")->setWidth(70);
        $this->activeSheet->getColumnDimension("{$this->colSubmittedDate}")->setWidth(15);
        $this->activeSheet->getColumnDimension("{$this->colTenderAmount}")->setWidth(20);
        $this->activeSheet->getColumnDimension("{$this->colWithdrawnTenders}")->setWidth(20);
        $this->activeSheet->getColumnDimension("{$this->colParticipatedTenders}")->setWidth(20);
        $this->activeSheet->getColumnDimension("{$this->colOngoingProjects}")->setWidth(20);
        $this->activeSheet->getColumnDimension("{$this->colCompletedProjects}")->setWidth(20);
    }
}