<?php

class sfBuildspaceSubPackageCompanySummaryPageGenerator extends sfBuildspaceSubPackageCompanyBQPageGenerator
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

    protected function setPageFormat($format)
    {
        switch(strtoupper($format))
        {
            /*
             *  For now we only handle A4 format. If there's necessity to handle other page
             * format we need to add to this method
             */
            case 'A4' :
                $width = $this->orientation == self::ORIENTATION_PORTRAIT ? 595 : 800;
                $height = $this->orientation == self::ORIENTATION_PORTRAIT ? 800 : 595;
                $pf = array(
                    'page_format' => self::PAGE_FORMAT_A4,
                    'minimum-font-size' => $this->fontSize,
                    'width' => $width,
                    'height' => $height,
                    'pdf_margin_top' => 8,
                    'pdf_margin_right' => 4,
                    'pdf_margin_bottom' => 1,
                    'pdf_margin_left' => 24
                );
                break;
            // DEFAULT ISO A4
            default:
                $width = $this->orientation == self::ORIENTATION_PORTRAIT ? 595 : 800;
                $height = $this->orientation == self::ORIENTATION_PORTRAIT ? 800 : 595;
                $pf = array(
                    'page_format' => self::PAGE_FORMAT_A4,
                    'minimum-font-size' => $this->fontSize,
                    'width' => $width,
                    'height' => $height,
                    'pdf_margin_top' => 8,
                    'pdf_margin_right' => 4,
                    'pdf_margin_bottom' => 3,
                    'pdf_margin_left' => 24
                );
        }
        return $pf;
    }
    
}