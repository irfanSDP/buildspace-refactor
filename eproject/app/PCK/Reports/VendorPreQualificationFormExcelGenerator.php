<?php namespace PCK\Reports;

use PCK\VendorPreQualification\VendorGroupGrade;
use PCK\VendorPreQualification\VendorPreQualification;

class VendorPreQualificationFormExcelGenerator extends WeightedNodeFormExcelGenerator {

    public function __construct()
    {
        parent::__construct();

        $this->worksheetTitle = trans('vendorManagement.vendorPreQualification');
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

        $this->activeSheet->setCellValue("{$col1}{$row1}", trans('companies.companyName').':');
        $this->activeSheet->setCellValue("{$col1}{$row2}", trans('vendorManagement.vendorWorkCategory').':');
        $this->activeSheet->setCellValue("{$col1}{$row3}", trans('vendorManagement.rating').':');

        $this->activeSheet->getStyle("{$col1}{$row1}:{$col1}{$row3}")->applyFromArray($this->getFormInformationLabelStyle());

        $grading = VendorGroupGrade::getGradeByGroup($form->vendorRegistration->company->contract_group_category_id);

        $this->activeSheet->setCellValue("{$col2}{$row1}", $form->vendorRegistration->company->name);
        $this->activeSheet->setCellValue("{$col2}{$row2}", $form->vendorWorkCategory->name);
        $this->activeSheet->setCellValue("{$col2}{$row3}", $grading ? $grading->getGrade($form->score)->description : '');

        $this->activeSheet->getStyle("{$col2}{$row1}:{$col2}{$row3}")->applyFromArray($this->getFormInformationDataStyle());

        $this->activeSheet->setCellValue("{$col3}{$row1}", trans('vendorManagement.form').':');
        $this->activeSheet->setCellValue("{$col3}{$row2}", trans('vendorManagement.status').':');
        $this->activeSheet->setCellValue("{$col3}{$row3}", trans('vendorManagement.score').':');

        $this->activeSheet->getStyle("{$col3}{$row1}:{$col3}{$row3}")->applyFromArray($this->getFormInformationLabelStyle());

        $this->activeSheet->setCellValue("{$col4}{$row1}", $form->weightedNode->name);
        $this->activeSheet->setCellValue("{$col4}{$row2}", VendorPreQualification::getStatusText($form->status_id));
        $this->activeSheet->setCellValue("{$col4}{$row3}", $form->score);

        $this->activeSheet->getStyle("{$col4}{$row1}:{$col4}{$row3}")->applyFromArray($this->getFormInformationDataStyle());

        return $row3;
    }
}