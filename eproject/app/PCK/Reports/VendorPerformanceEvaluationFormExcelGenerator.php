<?php namespace PCK\Reports;

use PCK\ModuleParameters\VendorManagement\VendorPerformanceEvaluationModuleParameter;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationCompanyForm;

class VendorPerformanceEvaluationFormExcelGenerator extends WeightedNodeFormExcelGenerator {

    public function __construct()
    {
        parent::__construct();

        $this->worksheetTitle = trans('vendorManagement.vendorPerformanceEvaluation');
    }

    public function generateFormInformation($startRow, $form)
    {
        $col1 = self::COLUMN_FORM_INFO_1;
        $col2 = self::COLUMN_FORM_INFO_2;
        $col3 = self::COLUMN_FORM_INFO_3;
        $col4 = self::COLUMN_FORM_INFO_4;

        $row1 = $startRow;
        $row2 = ++$startRow;
        $row3 = ++$startRow;
        $row4 = ++$startRow;
        $row5 = ++$startRow;
        $row6 = ++$startRow;
        $row7 = ++$startRow;

        $this->activeSheet->setCellValue("{$col1}{$row1}", trans('projects.reference').':');
        $this->activeSheet->setCellValue("{$col1}{$row2}", trans('projects.project').':');
        $this->activeSheet->setCellValue("{$col1}{$row3}", trans('companies.companyName').':');
        $this->activeSheet->setCellValue("{$col1}{$row4}", trans('vendorManagement.vendorWorkCategory').':');
        $this->activeSheet->setCellValue("{$col1}{$row5}", trans('vendorManagement.evaluator').':');
        $this->activeSheet->setCellValue("{$col1}{$row6}", trans('vendorManagement.rating').':');

        $this->activeSheet->getStyle("{$col1}{$row1}:{$col1}{$row6}")->applyFromArray($this->getFormInformationLabelStyle());

        $grading = VendorPerformanceEvaluationModuleParameter::first()->vendorManagementGrade;

        $this->activeSheet->setCellValue("{$col2}{$row1}", $form->vendorPerformanceEvaluation->project->reference);
        $this->activeSheet->setCellValue("{$col2}{$row2}", $form->vendorPerformanceEvaluation->project->title);
        $this->activeSheet->setCellValue("{$col2}{$row3}", $form->company->name);
        $this->activeSheet->setCellValue("{$col2}{$row4}", $form->vendorWorkCategory->name);
        $this->activeSheet->setCellValue("{$col2}{$row5}", $form->evaluatorCompany->name);
        $this->activeSheet->setCellValue("{$col2}{$row6}", $grading ? $grading->getGrade($form->score)->description : '');

        $this->activeSheet->getStyle("{$col2}{$row1}:{$col2}{$row6}")->applyFromArray($this->getFormInformationDataStyle());

        $this->activeSheet->mergeCells("{$col2}{$row1}:{$col4}{$row1}");
        $this->activeSheet->mergeCells("{$col2}{$row2}:{$col4}{$row2}");

        $this->activeSheet->setCellValue("{$col3}{$row3}", trans('vendorManagement.form').':');
        $this->activeSheet->setCellValue("{$col3}{$row4}", trans('vendorManagement.status').':');
        $this->activeSheet->setCellValue("{$col3}{$row6}", trans('vendorManagement.score').':');

        $this->activeSheet->getStyle("{$col3}{$row3}:{$col3}{$row6}")->applyFromArray($this->getFormInformationLabelStyle());

        $this->activeSheet->setCellValue("{$col4}{$row3}", $form->weightedNode->name);
        $this->activeSheet->setCellValue("{$col4}{$row4}", VendorPerformanceEvaluationCompanyForm::getStatusText($form->status_id));
        $this->activeSheet->setCellValue("{$col4}{$row6}", $form->score);

        $this->activeSheet->getStyle("{$col4}{$row3}:{$col4}{$row6}")->applyFromArray($this->getFormInformationDataStyle());

        $this->activeSheet->setCellValue("{$col1}{$row7}", trans('general.remarks').':');

        $this->activeSheet->getStyle("{$col1}{$row7}")->applyFromArray($this->getFormInformationLabelStyle());

        $remarks = empty($form->evaluator_remarks) ? trans('general.noRemarks') : $form->evaluator_remarks;

        $this->activeSheet->setCellValue("{$col2}{$row7}", $remarks);

        $this->activeSheet->getStyle("{$col2}{$row7}")->applyFromArray($this->getFormInformationDataStyle());

        $this->activeSheet->mergeCells("{$col2}{$row7}:{$col4}{$row7}");

        return $row7;
    }
}