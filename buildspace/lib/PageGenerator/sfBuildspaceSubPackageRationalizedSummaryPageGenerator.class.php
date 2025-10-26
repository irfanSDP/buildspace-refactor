<?php

class sfBuildspaceSubPackageRationalizedSummaryPageGenerator extends sfBuildspaceBQSubPackageRationalizedPageGenerator
{
    public function getMaxRows()
    {
        $pageFormat = $this->getPageFormat();

        switch($pageFormat['page_format'])
        {
            case self::PAGE_FORMAT_A4:
                switch($this->numberOfBillColumns)
                {
                    case 3:
                        $maxRows = 47;
                        break;
                    case 4:
                        $maxRows = 53;
                        break;
                    case 5:
                        $maxRows = 60;
                        break;
                    default:
                        $maxRows = 46;
                }
                $maxRows = $this->orientation == self::ORIENTATION_PORTRAIT ? 70 : $maxRows;
                break;
            default:
                switch($this->numberOfBillColumns)
                {
                    case 3:
                        $maxRows = 47;
                        break;
                    case 4:
                        $maxRows = 53;
                        break;
                    case 5:
                        $maxRows = 60;
                        break;
                    default:
                        $maxRows = 46;
                }
                $maxRows = $this->orientation == self::ORIENTATION_PORTRAIT ? 70 : $maxRows;
        }

        return $maxRows;
    }
}