<?php
/**
 * NoNumber Framework Helper File: Protect
 *
 * @package         NoNumber Framework
 * @version         13.9.1
 *
 * @author          Peter van Westen <peter@nonumber.nl>
 * @link            http://www.nonumber.nl
 * @copyright       Copyright © 2013 NoNumber All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

/**
 * Functions
 */
class NNProtect
{
	/**
	 * check if page should be protected for certain extensions
	*/
	public static function isProtectedPage($ext = '', $hastags = 0)
	{
		// return if disabled via url
		// return if current page is raw format
		// return if current page is NoNumber QuickPage
		// return if current page is a JoomFish or Josetta page
		return (
			($ext && JFactory::getApplication()->input->get('disable_' . $ext))
			|| JFactory::getApplication()->input->get('format') == 'raw'
			|| ($hastags
				&& (
					JFactory::getApplication()->input->getInt('nn_qp', 0)
					|| in_array(JFactory::getApplication()->input->get('option'), array('com_joomfishplus', 'com_josetta'))
				))
		);
	}

	/**
	 * check if page is an admin page
	*/
	public static function isAdmin($block_login = 0)
	{
		$options = array('com_acymailing');
		if ($block_login) {
			$options[] = 'com_login';
		}

		return (
			JFactory::getApplication()->isAdmin()
			&& !in_array(JFactory::getApplication()->input->get('option'), $options)
			&& JFactory::getApplication()->input->get('task') != 'preview'
		);
	}

	/**
	 * check if page is an edit page
	*/
	public static function isEditPage()
	{
		$option = JFactory::getApplication()->input->get('option');
		// always return false for these components
		if (in_array($option, array('com_rsevents', 'com_rseventspro'))) {
			return 0;
		}

		$task = JFactory::getApplication()->input->get('task');
		$view = JFactory::getApplication()->input->get('view');
		if (!(strpos($task, '.') === false)) {
			$task = explode('.', $task);
			$task = array_pop($task);
		}
		if (!(strpos($view, '.') === false)) {
			$view = explode('.', $view);
			$view = array_pop($view);
		}

		return (
			in_array($task, array('edit', 'form', 'submission'))
			|| in_array($view, array('edit', 'form'))
			|| in_array(JFactory::getApplication()->input->get('do'), array('edit', 'form'))
			|| in_array(JFactory::getApplication()->input->get('layout'), array('edit', 'form', 'write'))
			|| in_array(JFactory::getApplication()->input->get('option'), array('com_contentsubmit', 'com_cckjseblod'))
			|| NNProtect::isAdmin()
		);
	}

	/**
	 * the regular expression to mach the edit form
	*/
	public static function getFormRegex($regex_format = 0)
	{
		$regex = '(<' . 'form\s[^>]*(id|name)="(adminForm|postform|submissionForm|default_action_user)")';

		if ($regex_format) {
			$regex = '#' . $regex . '#si';
		}

		return $regex;
	}

	/**
	 * protect tags in string
	*/
	public static function protectTags(&$str, $tags = array(), $protected = array())
	{
		if (!is_array($tags)) {
			$tags = array($tags);
		}

		if (empty ($protected)) {
			$protected = array();
			foreach ($tags as $i => $tag) {
				$protected[$i] = base64_encode($tag);
			}
		}

		$str = str_replace($tags, $protected, $str);
	}

	/**
	 * replace any protected tags to original
	 */
	public static function unprotectTags(&$str, $tags = array(), $protected = array())
	{
		if (!is_array($tags)) {
			$tags = array($tags);
		}

		if (empty ($protected)) {
			$protected = array();
			foreach ($tags as $i => $tag) {
				$protected[$i] = base64_encode($tag);
			}
		}

		$str = str_replace($protected, $tags, $str);
	}

	/**
	 * protect complete adminForm (to prevent articles from being created when editing articles and such)
	*/
	public static function protectForm(&$str, $tags = array(), $protected = array())
	{
		if (!self::isEditPage()) {
			return;
		}

		if (!is_array($tags)) {
			$tags = array($tags);
		}

		if (empty ($protected)) {
			$protected = array();
			foreach ($tags as $i => $tag) {
				$protected[$i] = base64_encode($tag);
			}
		}

		$str = preg_replace(self::getFormRegex(1), '<!-- TMP_START_EDITOR -->\1', $str);
		$str = explode('<!-- TMP_START_EDITOR -->', $str);

		foreach ($str as $i => $s) {
			if (!empty($s) != '' && fmod($i, 2)) {
				$pass = 0;
				foreach ($tags as $tag) {
					if (!(strpos($s, $tag) === false)) {
						$pass = 1;
						break;
					}
				}
				if ($pass) {
					$s = explode('</form>', $s, 2);
					// protect tags only inside form fields
					if (preg_match_all('#(<textarea[^>]*>.*?<\/textarea>|<input[^>]*>)#si', $s['0'], $matches, PREG_SET_ORDER) > 0) {
						foreach ($matches as $match) {
							$field = str_replace($tags, $protected, $match['0']);
							$s['0'] = str_replace($match['0'], $field, $s['0']);
						}
					}
					$str[$i] = implode('</form>', $s);
				}
			}
		}

		$str = implode('', $str);
	}

	/**
	 * replace any protected tags to original
	*/
	public static function unprotectForm(&$str, $tags = array(), $protected = array())
	{
		NNProtect::unprotectTags($str, $tags, $protected);
	}

	/**
	 * remove inline comments in scrips and styles
	*/
	public static function removeInlineComments(&$str, $name)
	{
		$str = preg_replace('#\s*/\* (START|END): ' . $name . ' [a-z]* \*/\s*#s', "\n", $str);
	}

	/**
	 * remove tags from title tags
	*/
	public static function removeFromHtmlTagContent(&$str, $tags, $htmltags = array('title'))
	{
		if (!is_array($tags)) {
			$tags = array($tags);
		}
		if (!is_array($htmltags)) {
			$htmltags = array($attribs);
		}
		if (preg_match_all('#(<('. implode('|', $htmltags). ')(?:\s[^>]*?)>)(.*?)(</\2>)#si', $str, $matches, PREG_SET_ORDER) > 0) {
			foreach ($matches as $match) {
				$content = $match['2'];
				foreach ($tags as $tag) {
					$content = preg_replace('#\{/?' . $tag . '.*?\}#si', '', $content);
				}
				$str = str_replace($match['0'], $match['1'] . $content . $match['3'], $str);
			}
		}
	}

	/**
	 * remove tags from tag attributes
	*/
	public static function removeFromHtmlTagAttributes(&$str, $tags, $attribs = array('title', 'alt'))
	{
		if (!is_array($tags)) {
			$tags = array($tags);
		}
		if (!is_array($attribs)) {
			$attribs = array($attribs);
		}
		if (preg_match_all('#\s(?:'. implode('|', $attribs). ')\s*=\s*".*?"#si', $str, $matches, PREG_SET_ORDER) > 0) {
			foreach ($matches as $match) {
				$title = $match['0'];
				foreach ($tags as $tag) {
					$title = preg_replace('#\{/?' . $tag . '.*?\}#si', '', $title);
				}
				$str = str_replace($match['0'], $title, $str);
			}
		}
	}
}
