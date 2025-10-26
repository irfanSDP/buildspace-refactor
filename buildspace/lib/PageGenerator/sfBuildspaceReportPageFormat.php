<?php

/*
	class sfBuildspaceReportPageFormat // Must be preceded on this line by nothing or whitespace
*/

trait sfBuildspaceReportPageFormat {

	public function setPageFormat( $pageFormat )
	{
		$this->pageFormat = $pageFormat;
	}

	public function getOrientation()
	{
		return $this->orientation;
	}

	public function getMarginTop()
	{
		return $this->pageFormat['pdf_margin_top'];
	}

	public function getMarginBottom()
	{
		return $this->pageFormat['pdf_margin_bottom'];
	}

	public function getMarginLeft()
	{
		return $this->pageFormat['pdf_margin_left'];
	}

	public function getMarginRight()
	{
		return $this->pageFormat['pdf_margin_right'];
	}

	public function getPageSize()
	{
		return $this->pageFormat['page_format'];
	}

	public function getPrintSettings()
	{
		return $this->printSettings;
	}

	public function setMaxCharactersPerLine()
	{
		$this->MAX_CHARACTERS = 56;

		if ( $this->fontSize == 10 )
		{
			$this->MAX_CHARACTERS = 64;
		}
	}
}