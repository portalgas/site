<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class DPCalendarHelperLocation
{

	private static $locationCache = null;

	private static $googleLanguages = array(
			'ar',
			'eu',
			'bg',
			'bn',
			'ca',
			'cs',
			'da',
			'de',
			'el',
			'en',
			'en-AU',
			'en-GB',
			'es',
			'eu',
			'fa',
			'fi',
			'fil',
			'fr',
			'gl',
			'gu',
			'hi',
			'hr',
			'hu',
			'id',
			'it',
			'iw',
			'ja',
			'kn',
			'ko',
			'lt',
			'lv',
			'nl',
			'ml',
			'mr',
			'nl',
			'nn',
			'no',
			'or',
			'pl',
			'pt',
			'pt-BR',
			'pt-PT',
			'rm',
			'ro',
			'ru',
			'sk',
			'sl',
			'sr',
			'sv',
			'tl',
			'ta',
			'te',
			'th',
			'tr',
			'uk',
			'vi',
			'zh-CN',
			'zh-TW'
	);

	public static function format ($locations)
	{
		if (! is_array($locations))
		{
			$locations = array(
					$locations
			);
		}
		$buffer = '';
		foreach ($locations as $index => $location)
		{
			if (! empty($location->street))
			{
				$buffer .= (! empty($location->number) ? $location->number . ' ' : '') . $location->street . ', ';
			}
			if (! empty($location->city))
			{
				$buffer .= (! empty($location->zip) ? $location->zip . ' ' : '') . $location->city . ', ';
			}
			if (! empty($location->province))
			{
				$buffer .= $location->province . ', ';
			}
			if (! empty($location->country))
			{
				$buffer .= $location->country . ', ';
			}
			if ($index < count($locations) - 1)
			{
				$buffer .= '; ';
			}
		}
		$buffer = trim($buffer, ', ');
		return $buffer;
	}

	public static function get ($location, $fill = true)
	{
		if (self::$locationCache == null)
		{
			$cache = JFactory::getCache('com_dpcalendar_location', '');
			$cache->setCaching(true);
			$cache->setLifeTime(86400);
			self::$locationCache = $cache;
		}

		if ($fill)
		{
			$locObject = self::$locationCache->get($location);
			if ($locObject !== false)
			{
				return $locObject;
			}
		}

		$lang = DPCalendarHelper::getFrLanguage();
		if (! in_array($lang, self::$googleLanguages))
		{
			$lang = substr($lang, 0, strpos($lang, '-'));
		}
		if (! in_array($lang, self::$googleLanguages))
		{
			$lang = '';
		}
		else
		{
			$lang = '&language=' . $lang;
		}
		$url = 'https://maps.google.com/maps/api/geocode/json?';

		if ($key = DPCalendarHelper::getComponentParameter('map_api_google_key'))
		{
			$url .= 'key=' . $key . '&';
		}
		$url .= 'address=' . urlencode($location) . '&sensor=false' . $lang;
		$content = DPCalendarHelper::fetchContent($url);
		if ($content instanceof Exception)
		{
			JFactory::getApplication()->enqueueMessage((string) $content->getMessage(), 'warning');
		}

		$locObject = new JObject();
		$locObject->id = md5($location);
		$locObject->title = $location;
		$locObject->alias = JApplication::stringURLSafe($location);
		$locObject->state = 1;
		$locObject->language = '*';
		if (! empty($content) && ! ($content instanceof Exception))
		{
			$tmp = json_decode($content);

			if ($tmp)
			{
				if ($tmp->status == 'OK')
				{
					if (! empty($tmp->results))
					{
						if ($fill)
						{
							foreach ($tmp->results[0]->address_components as $part)
							{
								if (empty($part->types))
								{
									continue;
								}
								switch ($part->types[0])
								{
									case 'country':
										$locObject->country = $part->long_name;
										break;
									case 'administrative_area_level_1':
										$locObject->province = $part->long_name;
										break;
									case 'locality':
										$locObject->city = $part->long_name;
										break;
									case 'postal_code':
										$locObject->zip = $part->long_name;
										break;
									case 'route':
										$locObject->street = $part->long_name;
										break;
									case 'street_number':
										$locObject->number = $part->long_name;
										break;
								}
							}
						}
						else
						{
							$locObject->title = $location;
							$locObject->country = $location;
						}

						$locObject->latitude = $tmp->results[0]->geometry->location->lat;
						$locObject->longitude = $tmp->results[0]->geometry->location->lng;

						if ($fill)
						{
							$locObject->$locObject = self::$locationCache->store($locObject, $location);
						}
					}
				}
			}
		}
		return $locObject;
	}

	public static function getLocations ($locationIds)
	{
		if (empty($locationIds))
		{
			return array();
		}
		JLoader::import('joomla.application.component.model');
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_dpcalendar' . DS . 'models', 'DPCalendarModel');

		$model = JModelLegacy::getInstance('Locations', 'DPCalendarModel');
		$model->getState();
		$model->setState('filter.search', 'ids:' . implode(',', $locationIds));

		return $model->getItems();
	}

	public static function within ($location, $latitude, $longitude, $radius)
	{
		if (empty($location->latitude) || empty($location->longitude) || empty($latitude) || empty($longitude))
		{
			return false;
		}
		$latitude = (float) $latitude;
		$longitude = (float) $longitude;

		$longitudeMin = $longitude - $radius / abs(cos(deg2rad($longitude)) * 69);
		$longitudeMax = $longitude + $radius / abs(cos(deg2rad($longitude)) * 69);
		$latitudeMin = $latitude - ($radius / 69);
		$latitudeMax = $latitude + ($radius / 69);

		return $location->longitude > $longitudeMin && $location->longitude < $longitudeMax && $location->latitude > $latitudeMin &&
				 $location->latitude < $latitudeMax;
	}
}
