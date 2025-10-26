<?php namespace PCK\Reports;

use Carbon\Carbon;
use PCK\Tenders\Tender;
use PCK\Projects\Project;
use PCK\TendererTechnicalEvaluationInformation\TendererTechnicalEvaluationInformation;
use PCK\TechnicalEvaluationSetReferences\TechnicalEvaluationSetReference;
use PCK\TechnicalEvaluationTendererOption\TechnicalEvaluationTendererOption;
use PCK\Verifier\Verifier;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class TechnicalAssessmentFormReportGenerator extends ReportGenerator {

    private $colNumber      = 'A';
    private $colCompanyName = 'B';
    private $colRemarks     = 'H';
    private $colScore       = 'N';

    public function generate(Tender $tender, TechnicalEvaluationSetReference $setReference, $selectedTenderers, $notSelectedTenderers)
    {
        $this->currentRow = 1;
    
        $this->generateProjectTitle($tender->project);

        $this->currentRow++;

        $this->generateTargetedDateOfAward($tender);

        $this->currentRow++;
        $this->currentRow++;

        $this->generateDeclaration();

        $this->currentRow++;
        $this->currentRow++;

        $text = 'A) ' . trans('general.pass') . '-' . trans('technicalEvaluation.qualifiedTenderers') . '.';
        $this->generateTenderersTable($tender, $setReference, $text, $selectedTenderers);

        $this->currentRow++;
        $this->currentRow++;

        $text = 'B) ' . trans('general.fail') . '-' . trans('technicalEvaluation.disqualifiedTenderers') . '.';
        $this->generateTenderersTable($tender, $setReference, $text, $notSelectedTenderers);

        $this->currentRow++;
        $this->currentRow++;

        $this->generateRemarks($tender->technicalEvaluation->remarks);

        $this->currentRow++;
        $this->currentRow++;

        $this->generateVerifierLogs($tender);

        if($password = getenv('DEFAULT_EXCEL_EXPORT_PASSWORD'))
        {
            $this->protectWorkSheet($password);
            $this->protectWorkBook($password);
        }

        return $this->output($this->spreadsheet, trans('technicalEvaluation.assessmentConfirmationForm'));
    }

    private function generateProjectTitle(Project $project)
    {
        $this->mergeColumns('A', $this->currentRow, 2);
        $this->activeSheet->setCellValue("A{$this->currentRow}", trans('projects.title'));

        $this->mergeColumns('D', $this->currentRow, 20);
        $this->activeSheet->setCellValue("D{$this->currentRow}", $project->title);
    }

    private function generateTargetedDateOfAward(Tender $tender)
    {
        $this->mergeColumns('A', $this->currentRow, 2);
        $this->activeSheet->setCellValue("A{$this->currentRow}", trans('technicalEvaluation.targetedDateOfAward'));

        $this->mergeColumns('D', $this->currentRow, 20);
        $this->activeSheet->setCellValue("D{$this->currentRow}", $tender->project->getProjectTimeZoneTime(Carbon::parse($tender->technicalEvaluation->targeted_date_of_award)->format(\Config::get('dates.submitted_at'))));
    }

    private function generateDeclaration()
    {
        $this->mergeColumns('A', $this->currentRow, 20);
        $this->activeSheet->setCellValue("A{$this->currentRow}", trans('technicalEvaluation.iHerebyConfirmThat') . ': -');
    }

    private function generateTenderersTable(Tender $tender, TechnicalEvaluationSetReference $setReference, $text, $tenderers)
    {
        $tenderersRemarks = TendererTechnicalEvaluationInformation::where('tender_id', $tender->id)->lists('remarks', 'company_id');

        $this->mergeColumns('A', $this->currentRow, 20);
        $this->activeSheet->setCellValue("A{$this->currentRow}", $text);

        $this->currentRow++;

        $this->activeSheet->setCellValue("{$this->colNumber}{$this->currentRow}", trans('general.no'));
        $this->activeSheet->getStyle("{$this->colNumber}{$this->currentRow}")->applyFromArray($this->getColumnHeaderStyle());

        $this->mergeColumns($this->colCompanyName, $this->currentRow, 5);
        $this->activeSheet->setCellValue("{$this->colCompanyName}{$this->currentRow}", trans('companies.companyName'));
        $this->activeSheet->getStyle("{$this->colCompanyName}{$this->currentRow }:G{$this->currentRow}")->applyFromArray($this->getColumnHeaderStyle());

        $this->mergeColumns($this->colRemarks, $this->currentRow, 5);
        $this->activeSheet->setCellValue("{$this->colRemarks}{$this->currentRow}", trans('general.remarks'));
        $this->activeSheet->getStyle("{$this->colRemarks}{$this->currentRow}:M{$this->currentRow}")->applyFromArray($this->getColumnHeaderStyle());

        $this->activeSheet->setCellValue("{$this->colScore}{$this->currentRow}", trans('general.score'));
        $this->activeSheet->getStyle("{$this->colScore}{$this->currentRow}")->applyFromArray($this->getColumnHeaderStyle());

        $this->currentRow++;

        foreach($tenderers as $key => $tenderer)
        {
            $rowStyle = (($key + 1) == count($tenderers)) ? $this->getLastItemRowStyle() : $this->getItemRowStyle();
            $remarks  = array_key_exists($tenderer->id, $tenderersRemarks) ? $tenderersRemarks[$tenderer->id] : null;

            $this->activeSheet->setCellValue("{$this->colNumber}{$this->currentRow}", ($key + 1));
            $this->activeSheet->getStyle("{$this->colNumber}{$this->currentRow}:G{$this->currentRow}")->applyFromArray($rowStyle);

            $this->mergeColumns($this->colCompanyName, $this->currentRow, 5);
            $this->activeSheet->setCellValue("{$this->colCompanyName}{$this->currentRow}", $tenderer->name);
            $this->activeSheet->getStyle("{$this->colCompanyName}{$this->currentRow}:G{$this->currentRow}")->applyFromArray($rowStyle);

            $this->mergeColumns($this->colRemarks, $this->currentRow, 5);
            $this->activeSheet->setCellValue("{$this->colRemarks}{$this->currentRow}", $remarks);
            $this->activeSheet->getStyle("{$this->colRemarks}{$this->currentRow}:M{$this->currentRow}")->applyFromArray($rowStyle);

            $this->activeSheet->setCellValue("{$this->colScore}{$this->currentRow}", number_format(TechnicalEvaluationTendererOption::getTendererScore($tenderer, $setReference->set), 2));
            $this->activeSheet->getStyle("{$this->colScore}{$this->currentRow}")->applyFromArray($rowStyle);

            $this->currentRow++;
        }
    }

    private function generateRemarks($remarks)
    {
        $this->mergeColumns('A', $this->currentRow, 13);
        $this->activeSheet->setCellValue("A{$this->currentRow}", $remarks);

        $numberOfLines = substr_count($remarks, "\n" );
        $endindRows    = $this->currentRow + $numberOfLines;

        $this->activeSheet->mergeCells("A{$this->currentRow}:N{$endindRows}");
        $this->activeSheet->getStyle("A{$this->currentRow}:N{$endindRows}")->getAlignment()->setWrapText(true);
    }

    private function generateVerifierLogs(Tender $tender)
    {
        $this->mergeColumns('A', $this->currentRow, 16);
        $this->activeSheet->setCellValue("A{$this->currentRow}", trans('verifiers.assignedVerifiers'));

        $this->currentRow++;

        $this->activeSheet->setCellValue("A{$this->currentRow}", trans('general.no'));
        $this->activeSheet->getStyle("A{$this->currentRow}")->applyFromArray($this->getColumnHeaderStyle());

        $this->mergeColumns('B', $this->currentRow, 5);
        $this->activeSheet->setCellValue("B{$this->currentRow}", trans('users.name'));
        $this->activeSheet->getStyle("B{$this->currentRow }:G{$this->currentRow}")->applyFromArray($this->getColumnHeaderStyle());

        $this->mergeColumns('H', $this->currentRow, 1);
        $this->activeSheet->setCellValue("H{$this->currentRow}", trans('verifiers.status'));
        $this->activeSheet->getStyle("H{$this->currentRow}:I{$this->currentRow}")->applyFromArray($this->getColumnHeaderStyle());

        $this->mergeColumns('J', $this->currentRow, 2);
        $this->activeSheet->setCellValue("J{$this->currentRow}", trans('verifiers.verifiedAt'));
        $this->activeSheet->getStyle("J{$this->currentRow}:L{$this->currentRow}")->applyFromArray($this->getColumnHeaderStyle());

        $this->mergeColumns('M', $this->currentRow, 4);
        $this->activeSheet->setCellValue("M{$this->currentRow}", trans('verifiers.remarks'));
        $this->activeSheet->getStyle("M{$this->currentRow}:Q{$this->currentRow}")->applyFromArray($this->getColumnHeaderStyle());

        $this->currentRow++;

        $assignedVerifierRecords = Verifier::getAssignedVerifierRecords($tender->technicalEvaluation, true);

        if($assignedVerifierRecords->isEmpty())
        {
            $this->mergeColumns('A', $this->currentRow, 16);
            $this->activeSheet->setCellValue("A{$this->currentRow}", trans('general.noRecordsFound'));
            $this->activeSheet->getStyle("A{$this->currentRow}:Q{$this->currentRow}")->applyFromArray($this->getLastItemRowStyle());

            return;
        }

        foreach($assignedVerifierRecords as $key => $record)
        {
            $rowStyle = (($key + 1) == count($assignedVerifierRecords)) ? $this->getLastItemRowStyle() : $this->getItemRowStyle();

            $this->activeSheet->setCellValue("A{$this->currentRow}", ($key + 1));
            $this->activeSheet->getStyle("A{$this->currentRow}")->applyFromArray($rowStyle);

            $this->mergeColumns('B', $this->currentRow, 5);
            $this->activeSheet->setCellValue("B{$this->currentRow}", $record->verifier->name);
            $this->activeSheet->getStyle("B{$this->currentRow }:G{$this->currentRow}")->applyFromArray($rowStyle);

            if( $record->approved === true )
            {
                $statusText = trans("verifiers.approved");
            }
            elseif($record->approved === false)
            {
                $statusText = trans("verifiers.rejected");
            }
            else
            {
                $statusText = trans("verifiers.unverified");
            }

            $this->mergeColumns('H', $this->currentRow, 1);
            $this->activeSheet->setCellValue("H{$this->currentRow}", $statusText);
            $this->activeSheet->getStyle("H{$this->currentRow}:I{$this->currentRow}")->applyFromArray($rowStyle);

            $timestamp = $tender->project ? $tender->project->getProjectTimeZoneTime($record->verified_at) : $record->verified_at;

            $this->mergeColumns('J', $this->currentRow, 2);
            $this->activeSheet->setCellValue("J{$this->currentRow}", \Carbon\Carbon::parse($timestamp)->format(\Config::get('dates.created_at')));
            $this->activeSheet->getStyle("J{$this->currentRow}:L{$this->currentRow}")->applyFromArray($rowStyle);

            $this->mergeColumns('M', $this->currentRow, 4);
            $this->activeSheet->setCellValue("M{$this->currentRow}", $record->remarks);
            $this->activeSheet->getStyle("M{$this->currentRow}:Q{$this->currentRow}")->applyFromArray($rowStyle);

            $this->currentRow++;
        }
    }
}