<?php namespace PCK\Reports;

use PCK\Users\User;

class AllUsersReportGenerator extends ReportGenerator {

    private $colNumber             = 'B';
    private $colName               = 'C';
    private $colDesignation        = 'D';
    private $colEmail              = 'E';
    private $colContactNumber      = 'F';
    private $colCompanyName        = 'G';
    private $colRole               = 'H';
    private $colStatus             = 'I';
    private $colBlocked            = 'J';
    private $colAdmin              = 'K';

    const STANDARD_HEADER_GRAY = 'D1D1D1';

    public function generate()
    {
        $this->currentRow = 2;

        $this->createHeader();
        $this->setColumnWidths();

        $this->activeSheet->getStyle("{$this->colNumber}{$this->currentRow}:{$this->colAdmin}{$this->currentRow}")->applyFromArray($this->getTitleStyle(self::STANDARD_HEADER_GRAY));

        $count = 0;
        $recordCount = User::all()->count();

        foreach(User::orderBy('id', 'ASC')->get() as $user)
        {
            $this->currentRow++;

            $isLastRecord = (($count +1) === $recordCount);
            $itemRowStyle = $isLastRecord ? $this->getLastItemRowStyle() : $this->getItemRowStyle();

            $this->activeSheet->getStyle("{$this->colNumber}{$this->currentRow}:{$this->colAdmin}{$this->currentRow}")->applyFromArray($itemRowStyle);

            $this->activeSheet->setCellValue("{$this->colNumber}{$this->currentRow}", ($count + 1));
            $this->activeSheet->setCellValue("{$this->colName}{$this->currentRow}", $user->name);
            $this->activeSheet->setCellValue("{$this->colDesignation}{$this->currentRow}", $user->designation);
            $this->activeSheet->setCellValue("{$this->colEmail}{$this->currentRow}", $user->email);
            $this->activeSheet->setCellValue("{$this->colContactNumber}{$this->currentRow}", $user->contact_number);
            $this->activeSheet->setCellValue("{$this->colCompanyName}{$this->currentRow}", $user->is_super_admin ? null : $user->company->name);
            $this->activeSheet->setCellValue("{$this->colRole}{$this->currentRow}", $user->is_super_admin ? null : $user->company->contractGroupCategory->name);
            $this->activeSheet->setCellValue("{$this->colStatus}{$this->currentRow}", $user->confirmed ? trans('users.confirmed') : trans('users.pending'));
            $this->activeSheet->setCellValue("{$this->colBlocked}{$this->currentRow}", $user->account_blocked_status ? trans('users.yes') : trans('users.no'));
            $this->activeSheet->setCellValue("{$this->colAdmin}{$this->currentRow}", $user->is_admin ? trans('users.yes') : trans('users.no'));
        
            $count++;
        }

        return $this->output($this->spreadsheet, trans('users.allUsers'));
    }
    
    private function createHeader()
    {
        $headerStartRow = $this->currentRow;

        $this->addHeaderColumns([
            trans('general.no'),
            trans('users.name'),
            trans('users.designation'),
            trans('users.email'),
            trans('users.contactNumber'),
            trans('companies.name'),
            trans('companies.role'),
            trans('users.status'),
            trans('users.blocked'),
            trans('users.admin'),
        ], $this->colNumber, $headerStartRow);
    }

    private function setColumnWidths()
    {
        $this->activeSheet->getColumnDimension("A")->setWidth(1.3);
        $this->activeSheet->getColumnDimension("{$this->colNumber}")->setWidth(10);
        $this->activeSheet->getColumnDimension("{$this->colName}")->setWidth(50);
        $this->activeSheet->getColumnDimension("{$this->colDesignation}")->setWidth(50);
        $this->activeSheet->getColumnDimension("{$this->colEmail}")->setWidth(50);
        $this->activeSheet->getColumnDimension("{$this->colContactNumber}")->setWidth(20);
        $this->activeSheet->getColumnDimension("{$this->colCompanyName}")->setWidth(50);
        $this->activeSheet->getColumnDimension("{$this->colRole}")->setWidth(25);
        $this->activeSheet->getColumnDimension("{$this->colStatus}")->setWidth(12);
        $this->activeSheet->getColumnDimension("{$this->colBlocked}")->setWidth(10);
        $this->activeSheet->getColumnDimension("{$this->colAdmin}")->setWidth(10);
    }

    public function getTitleStyle($fillColor = null)
    {
        $titleStyle = parent::getTitleStyle();

        if(!is_null($fillColor))
        {
            $titleStyle['fill'] = [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => [
                    'argb' => $fillColor,
                ],
            ];
        }

        return $titleStyle;
    }
}

