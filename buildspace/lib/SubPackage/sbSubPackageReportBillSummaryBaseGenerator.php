<?php

class sbSubPackageReportBillSummaryBaseGenerator extends sfBuildspaceBQMasterFunction {

	protected function setOrientationAndSize($orientation = false, $pageFormat = false)
	{
		if($orientation)
		{
			$this->orientation = $orientation;
			$this->setPageFormat($this->generatePageFormat( ($pageFormat) ? $pageFormat : self::PAGE_FORMAT_A4 ));
		}
		else
		{
			$count = count($this->tendererIds);

			if($count <= 4)
			{
				$this->orientation = ($count <= 1) ? self::ORIENTATION_PORTRAIT : self::ORIENTATION_LANDSCAPE;
				$this->setPageFormat($this->generatePageFormat(self::PAGE_FORMAT_A4));
			}
			else
			{
				$this->orientation = self::ORIENTATION_LANDSCAPE;
				$this->setPageFormat($this->generatePageFormat(self::PAGE_FORMAT_A3));
			}
		}
	}

	protected function generatePageFormat($format)
	{
		switch(strtoupper($format))
		{
		   /*
			*  For now we only handle A4 format. If there's necessity to handle other page
			* format we need to add to this method
			*/
			case self::PAGE_FORMAT_A4 :
				$width = $this->orientation == self::ORIENTATION_PORTRAIT ? 595 : 800;
				$height = $this->orientation == self::ORIENTATION_PORTRAIT ? 800 : 595;
				$pf = array(
					'page_format' => self::PAGE_FORMAT_A4,
					'minimum-font-size' => $this->fontSize,
					'width' => $width,
					'height' => $height,
					'pdf_margin_top' => 8,
					'pdf_margin_right' => 10,
					'pdf_margin_bottom' => 1,
					'pdf_margin_left' => 10
				);
				break;
			case self::PAGE_FORMAT_A3 :
				$width = $this->orientation == self::ORIENTATION_PORTRAIT ? 800 : 1000;
				$height = $this->orientation == self::ORIENTATION_PORTRAIT ? 1000 : 800;
				$pf = array(
					'page_format' => self::PAGE_FORMAT_A3,
					'minimum-font-size' => $this->fontSize,
					'width' => $width,
					'height' => $height,
					'pdf_margin_top' => 8,
					'pdf_margin_right' => 10,
					'pdf_margin_bottom' => 1,
					'pdf_margin_left' => 10
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
					'pdf_margin_right' => 10,
					'pdf_margin_bottom' => 3,
					'pdf_margin_left' => 10
				);
		}

		return $pf;
	}

	public function getMaxRows()
	{
		$pageFormat = $this->getPageFormat();

		switch($pageFormat['page_format'])
		{
			case self::PAGE_FORMAT_A4:
				if($this->orientation == self::ORIENTATION_PORTRAIT)
				{
					if(count($this->tenderers))
					{
						if(count($this->tenderers) <= 1)
						{
							$maxRows = 55;
						}
						else
						{
							$maxRows = 65;
						}
					}
					else
					{
						$maxRows = 55;
					}
				}
				else
				{
					$maxRows = 35;
				}
				break;
			default:
				$maxRows = $this->orientation == self::ORIENTATION_PORTRAIT ? 110 : 55;
		}

		return $maxRows;
	}

}