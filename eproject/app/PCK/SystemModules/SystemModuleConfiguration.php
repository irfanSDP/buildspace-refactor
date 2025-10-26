<?php namespace PCK\SystemModules;

use Illuminate\Database\Eloquent\Model;

class SystemModuleConfiguration extends Model {
	CONST MODULE_ID_INPSECTION = 1;
	CONST MODULE_ID_VENDOR_MANAGEMENT = 2;
    CONST MODULE_ID_DIGITAL_STAR = 3;
	CONST MODULE_ID_LMS_Elearning = 4;
    CONST MODULE_ID_EBIDDING_MODES = 5;

	public $timestamps = false;

	protected $fillable = ['module_id'];

	public static function getModuleName($moduleId)
	{
		$names = [
			self::MODULE_ID_INPSECTION => 'Inspection',
			self::MODULE_ID_VENDOR_MANAGEMENT => 'Vendor Management',
            self::MODULE_ID_DIGITAL_STAR => 'Digital Star',
			self::MODULE_ID_LMS_Elearning => 'VCOBC eLearning',
            self::MODULE_ID_EBIDDING_MODES => 'eBidding Modes',
		];

		return $names[$moduleId];
	}

	public static function getModuleIds()
	{
		return [
			self::MODULE_ID_INPSECTION,
			self::MODULE_ID_VENDOR_MANAGEMENT,
            self::MODULE_ID_DIGITAL_STAR,
			self::MODULE_ID_LMS_Elearning,
            self::MODULE_ID_EBIDDING_MODES,
		];
	}

	public static function initiateModules()
	{
		foreach(self::getModuleIds() as $moduleId)
		{
			self::firstOrCreate(['module_id' => $moduleId]);
		}
	}

	public static function isEnabled($moduleId)
	{
		if( ! in_array($moduleId, self::getModuleIds()) ) return false;

		$record = self::firstOrCreate(['module_id' => $moduleId]);

		// $record->is_enabled is not included if the model is newly created, as it is a default value.
		return $record->is_enabled ?? false;
	}

	public static function enable($moduleId, $enable)
	{
		if( ! in_array($moduleId, self::getModuleIds()) ) return false;

		$record = self::firstOrNew(['module_id' => $moduleId]);

		$record->is_enabled = $enable;

		return $record->save();
	}
}