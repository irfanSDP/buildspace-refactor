<?php 

class sfBQLibraryItemReportGenerator extends sfScheduleOfRateItemReportGenerator {

	protected $bqLibrary;

	public function __construct(BQLibrary $bqLibrary, $descriptionFormat = sfBuildspaceReportBillPageGenerator::DESC_FORMAT_FULL_LINE)
	{
		$this->bqLibrary         = $bqLibrary;
		$this->descriptionFormat = $descriptionFormat;
		$this->printSettings     = BillLayoutSettingTable::getInstance()->getPrintingLayoutSettings(1, true);
		$this->fontSize          = $this->printSettings['layoutSetting']['fontSize'];
		$this->fontType          = self::setFontType($this->printSettings['layoutSetting']['fontTypeName']);
		$this->headSettings      = $this->printSettings['headSettings'];
		$this->descriptionFormat = $descriptionFormat;
		$this->currency          = null;
	}

}