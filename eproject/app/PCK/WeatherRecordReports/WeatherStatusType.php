<?php namespace PCK\WeatherRecordReports;

interface WeatherStatusType {

	const CLOUDY = 1;
	const CLOUDY_TEXT = 'Cloudy';

	const LITTLE_RAIN = 2;
	const LITTLE_RAIN_TEXT = 'Little Rain';

	const THUNDERSTORM = 4;
	const THUNDERSTORM_TEXT = 'Thunderstorm';

	const FLOOD = 8;
	const FLOOD_TEXT = 'Flood';

	const SUNNY = 16;
	const SUNNY_TEXT = 'Sunny';

	const VERY_STRONG_WIND = 32;
	const VERY_STRONG_WIND_TEXT = 'Very Strong Wind';

}