<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class DPCalendarHelperMessage
{

	/**
	 * Pre-processes the message text in $text, replacing merge tags with those
	 * fetched based on subscription $sub
	 *
	 * @param string $text
	 *        	The message to process
	 * @param DPCalendarTablePayment $sub
	 *        	A payment object
	 * @param array $extras
	 *        	Extra params
	 *
	 * @return string The processed string
	 */
	public static function processPaymentTags ($text, $payment)
	{
		try
		{
			$configuration = (array) $payment;
			$configuration['currency'] = DPCalendarHelper::getComponentParameter('currency', 'USD');
			$configuration['currencySymbol'] = DPCalendarHelper::getComponentParameter('currency_symbol', '$');

			$m = new Mustache();
			return $m->render($text, $configuration);
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
		}
	}

	/**
	 * Processes the language merge tags ([IFLANG langCode], [/IFLANG]) in some
	 * block of text.
	 *
	 * @param string $text
	 *        	The text to process
	 * @param string $lang
	 *        	Which language to keep. Null means the default language.
	 *
	 * @return string
	 */
	public static function processLanguage ($text, $lang = null)
	{
		// Get the default language
		if (empty($lang))
		{
			$enableTranslation = JFactory::getApplication()->getLanguageFilter();

			if ($enableTranslation)
			{
				$lang = JFactory::getLanguage()->getTag();
			}
			else
			{
				$user = JFactory::getUser();
				if (property_exists($user, 'language'))
				{
					$lang = $user->$user->getParam('language', 'en-GB');
				}
				else
				{
					$params = $user->params;
					if (! is_object($params))
					{
						JLoader::import('joomla.registry.registry');
						$params = new JRegistry($params);
					}
					if (version_compare(JVERSION, '3.0', 'ge'))
					{
						$lang = $params->get('language', '');
					}
					else
					{
						$lang = $params->getValue('language', '');
					}
				}
				if (empty($lang))
				{
					$lang = JComponentHelper::getParams('com_languages')->get('site', 'en-GB');
				}
			}
		}

		// Find languages
		$translations = array();
		while (strpos($text, '[IFLANG ') !== false)
		{
			$start = strpos($text, '[IFLANG ');
			$end = strpos($text, '[/IFLANG]');
			$langEnd = strpos($text, ']', $start);
			$langCode = substr($text, $start + 8, $langEnd - $start - 8);
			$langText = substr($text, $langEnd + 1, $end - $langEnd - 1);
			$translations[$langCode] = $langText;

			if ($start > 0)
			{
				$temp = substr($text, 0, $start - 1);
			}
			else
			{
				$temp = 0;
			}
			$temp .= substr($text, $end + 9);
			$text = $temp;
		}
		if (! empty($text))
		{
			if (! array_key_exists('*', $translations))
			{
				$translations['*'] = $text;
			}
		}

		$siteLang = JComponentHelper::getParams('com_languages')->get('site', 'en-GB');

		if (array_key_exists($lang, $translations))
		{
			return $translations[$lang];
		}
		elseif (array_key_exists($siteLang, $translations))
		{
			return $translations[$siteLang];
		}
		elseif (array_key_exists('*', $translations))
		{
			return $translations['*'];
		}
		else
		{
			return $text;
		}
	}

	/**
	 * Main function to detect if we're running in a CLI environment and we're
	 * admin
	 *
	 * @return array isCLI and isAdmin. It's not an associtive array, so we can
	 *         use list.
	 */
	protected static function isCliAdmin ()
	{
		static $isCLI = null;
		static $isAdmin = null;

		if (is_null($isCLI) && is_null($isAdmin))
		{
			try
			{
				if (is_null(JFactory::$application))
				{
					$isCLI = true;
				}
				else
				{
					$isCLI = JFactory::getApplication() instanceof Exception;
				}
			}
			catch (Exception $e)
			{
				$isCLI = true;
			}

			if ($isCLI)
			{
				$isAdmin = false;
			}
			else
			{
				$isAdmin = ! JFactory::$application ? false : JFactory::getApplication()->isAdmin();
			}
		}

		return array(
				$isCLI,
				$isAdmin
		);
	}
}
