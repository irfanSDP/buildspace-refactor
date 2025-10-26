<?php namespace PCK\WeatherRecordReports;

trait WeatherStatusTypeTrait {

	public function getWeatherStatusAttribute($value)
	{
		return self::getWeatherStatusText($value);
	}

	public static function getWeatherStatusText($value)
	{
		switch ($value)
		{
			case WeatherStatusType::LITTLE_RAIN:
				$text = WeatherStatusType::LITTLE_RAIN_TEXT;
				break;

			case WeatherStatusType::CLOUDY:
				$text = WeatherStatusType::CLOUDY_TEXT;
				break;

			case WeatherStatusType::FLOOD:
				$text = WeatherStatusType::FLOOD_TEXT;
				break;

			case WeatherStatusType::THUNDERSTORM:
				$text = WeatherStatusType::THUNDERSTORM_TEXT;
				break;

			case WeatherStatusType::SUNNY:
				$text = WeatherStatusType::SUNNY_TEXT;
				break;

			case WeatherStatusType::VERY_STRONG_WIND:
				$text = WeatherStatusType::VERY_STRONG_WIND_TEXT;
				break;

			default:
				throw new \InvalidArgumentException('Invalid Weather Status\'s Type');
		}

		return $text;
	}

	public static function generateWeatherStatusDropDownData()
	{
		$data[WeatherStatusType::LITTLE_RAIN]      = WeatherStatusType::LITTLE_RAIN_TEXT;
		$data[WeatherStatusType::CLOUDY]           = WeatherStatusType::CLOUDY_TEXT;
		$data[WeatherStatusType::FLOOD]            = WeatherStatusType::FLOOD_TEXT;
		$data[WeatherStatusType::THUNDERSTORM]     = WeatherStatusType::THUNDERSTORM_TEXT;
		$data[WeatherStatusType::SUNNY]            = WeatherStatusType::SUNNY_TEXT;
		$data[WeatherStatusType::VERY_STRONG_WIND] = WeatherStatusType::VERY_STRONG_WIND_TEXT;

		return $data;
	}

}