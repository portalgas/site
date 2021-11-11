<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_menu
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/* @var $menu JAdminCSSMenu */

$shownew = (boolean)$params->get('shownew', 1);
$showhelp = 0; // $params->get('showhelp', 1);
$user = JFactory::getUser();
$lang = JFactory::getLanguage();


//
// Site SubMenu
//
$m = new JMenuNode(JText::_('JSITE'), '#');
$menu->addChild($m, true);
unset($m); $m = new JMenuNode(JText::_('MOD_MENU_CONTROL_PANEL'), 'index.php', 'class:cpanel');
$menu->addChild($m);

$menu->addSeparator();
unset($m); $m = new JMenuNode(JText::_('MOD_MENU_USER_PROFILE'), 'index.php?option=com_admin&task=profile.edit&id='.$user->id, 'class:profile');
$menu->addChild($m);
$menu->addSeparator();

if ($user->authorise('core.admin'))
{
	unset($m); $m = new JMenuNode(JText::_('MOD_MENU_CONFIGURATION'), 'index.php?option=com_config', 'class:config');
	$menu->addChild($m);
	$menu->addSeparator();
}

$chm = $user->authorise('core.manage', 'com_checkin');
$cam = $user->authorise('core.manage', 'com_cache');

if ($chm || $cam )
{
	unset($m); $m = new JMenuNode(JText::_('MOD_MENU_MAINTENANCE'), 'index.php?option=com_checkin', 'class:maintenance');
	$menu->addChild($m, true);

	if ($chm)
	{
		unset($m); $m = new JMenuNode(JText::_('MOD_MENU_GLOBAL_CHECKIN'), 'index.php?option=com_checkin', 'class:checkin');
		$menu->addChild($m);
		$menu->addSeparator();
	}
	if ($cam)
	{
		unset($m); $m = new JMenuNode(JText::_('MOD_MENU_CLEAR_CACHE'), 'index.php?option=com_cache', 'class:clear');
		$menu->addChild($m);
		unset($m); $m = new JMenuNode(JText::_('MOD_MENU_PURGE_EXPIRED_CACHE'), 'index.php?option=com_cache&view=purge', 'class:purge');
		$menu->addChild($m);
	}

	$menu->getParent();
}

$menu->addSeparator();
if ($user->authorise('core.admin'))
{
	unset($m); $m = new JMenuNode(JText::_('MOD_MENU_SYSTEM_INFORMATION'), 'index.php?option=com_admin&view=sysinfo', 'class:info');
	$menu->addChild($m);
	$menu->addSeparator();
}

unset($m); $m = new JMenuNode(JText::_('MOD_MENU_LOGOUT'), JRoute::_('index.php?option=com_login&task=logout&'. JSession::getFormToken() .'=1'), 'class:logout');
$menu->addChild($m);

$menu->getParent();

//
// Users Submenu
//
if ($user->authorise('core.manage', 'com_users'))
{
	unset($m); $m = new JMenuNode(JText::_('MOD_MENU_COM_USERS_USERS'), '#');
	$menu->addChild($m, true);
	$createUser = $shownew && $user->authorise('core.create', 'com_users');
	$createGrp = $user->authorise('core.admin', 'com_users');

	unset($m); $m = new JMenuNode(JText::_('MOD_MENU_COM_USERS_USER_MANAGER'), 'index.php?option=com_users&view=users', 'class:user');
	$menu->addChild($m, $createUser);

	if ($createUser)
	{
		unset($m); $m = new JMenuNode(JText::_('MOD_MENU_COM_USERS_ADD_USER'), 'index.php?option=com_users&task=user.add', 'class:newarticle');
		$menu->addChild($m);
		$menu->getParent();
	}

	if ($createGrp)
	{
		unset($m); $m = new JMenuNode(JText::_('MOD_MENU_COM_USERS_GROUPS'), 'index.php?option=com_users&view=groups', 'class:groups');
		$menu->addChild($m, $createUser);
		if ($createUser)
		{
			unset($m); $m = new JMenuNode(JText::_('MOD_MENU_COM_USERS_ADD_GROUP'), 'index.php?option=com_users&task=group.add', 'class:newarticle');
			$menu->addChild($m);
			$menu->getParent();
		}
		unset($m); $m = new JMenuNode(JText::_('MOD_MENU_COM_USERS_LEVELS'), 'index.php?option=com_users&view=levels', 'class:levels');
		$menu->addChild($m, $createUser);

		if ($createUser)
		{
			unset($m); $m = new JMenuNode(JText::_('MOD_MENU_COM_USERS_ADD_LEVEL'), 'index.php?option=com_users&task=level.add', 'class:newarticle');
			$menu->addChild($m);
			$menu->getParent();
		}
	}

	$menu->addSeparator();
	unset($m); $m = new JMenuNode(JText::_('MOD_MENU_COM_USERS_NOTES'), 'index.php?option=com_users&view=notes', 'class:user-note');
	$menu->addChild($m, $createUser);

	if ($createUser)
	{
		unset($m); $m = new JMenuNode(JText::_('MOD_MENU_COM_USERS_ADD_NOTE'), 'index.php?option=com_users&task=note.add', 'class:newarticle');
		$menu->addChild($m);
		$menu->getParent();
	}
	unset($m); $m = new JMenuNode(JText::_('MOD_MENU_COM_USERS_NOTE_CATEGORIES'), 'index.php?option=com_categories&view=categories&extension=com_users', 'class:category');
	$menu->addChild($m, $createUser);

	if ($createUser)
	{
		unset($m); $m = new JMenuNode(JText::_('MOD_MENU_COM_CONTENT_NEW_CATEGORY'), 'index.php?option=com_categories&task=category.add&extension=com_users', 'class:newarticle');
		$menu->addChild($m);
		$menu->getParent();
	}

	/*
	 * disabilitare Mass Mail utenti https://github.com/madasha/joomla-cms/commit/42235c3e0c52853a1488a4044fe75a7acfe44629
	 * 
		administrator/components/com_config/model/form/application.xml
		
		riga 388
				<field
				 name="massmailon"
				 type="radio"
				 class="btn-group btn-group-yesno"
				 default="1"
				 label="COM_CONFIG_FIELD_MAIL_MASSMAILON_LABEL"
				 description="COM_CONFIG_FIELD_MAIL_MASSMAILON_DESC"
				 filter="integer">
				 <option
				 value="1">JYES</option>
				 <option
				 value="0">JNO</option>
				 </field>
		
		administrator/components/com_users/controllers/mail.php
		
		public function send()
		{
			// Redirect to admin index if mass mailer disabled in conf
		 	if (JFactory::getApplication()->get('massmailon') != 1) {
		 		JFactory::getApplication()->redirect(JRoute::_('index.php', false));
		 	}
		
		administrator/language/en-GB/en-GB.com_config.ini
		
			COM_CONFIG_FIELD_MAIL_MASSMAILON_DESC="Select Yes to enable Mass Mail to users function, select No to disable the Mass Mail."
			COM_CONFIG_FIELD_MAIL_MASSMAILON_LABEL="Mass mail enabled"
		
		administrator/components/com_users/views/mail/view.html.php
		
		public function display($tpl = null)
		{
		 	 // Redirect to admin index if mass mailer disabled in conf
			 if (JFactory::getApplication()->get('massmailon') != 1) {
		 		JFactory::getApplication()->redirect(JRoute::_('index.php', false));
			 }
		
		administrator/modules/mod_menu/tmpl/default_enabled.php 
		
	 * if (JFactory::getApplication()->get('massmailon') == 1) {
		$menu->addSeparator();
		$menu->addChild(
			new JMenuNode(JText::_('MOD_MENU_MASS_MAIL_USERS'), 'index.php?option=com_users&view=mail', 'class:massmail')
		);
		
	* }
	*/
	if(in_array(group_id_root,$user->getAuthorisedGroups())) {
		$menu->addSeparator();
		unset($m); $m = new JMenuNode(JText::_('MOD_MENU_MASS_MAIL_USERS'), 'index.php?option=com_users&view=mail', 'class:massmail');
		$menu->addChild($m);
	}

	$menu->getParent();
}

//
// Menus Submenu
//
if ($user->authorise('core.manage', 'com_menus'))
{
	unset($m); $m = new JMenuNode(JText::_('MOD_MENU_MENUS'), '#');
	$menu->addChild($m, true);
	$createMenu = $shownew && $user->authorise('core.create', 'com_menus');
	unset($m); $m = new JMenuNode(JText::_('MOD_MENU_MENU_MANAGER'), 'index.php?option=com_menus&view=menus', 'class:menumgr');
	$menu->addChild($m, $createMenu
	);
	if ($createMenu)
	{	
		unset($m); $m = new JMenuNode(JText::_('MOD_MENU_MENU_MANAGER_NEW_MENU'), 'index.php?option=com_menus&view=menu&layout=edit', 'class:newarticle');
		$menu->addChild($m);
		$menu->getParent();
	}
	$menu->addSeparator();

	// Menu Types
	foreach (ModMenuHelper::getMenus() as $menuType)
	{
		
		$alt = '*' .$menuType->sef. '*';
		if ($menuType->home == 0)
		{
			$titleicon = '';
		}
		elseif ($menuType->home == 1 && $menuType->language == '*')
		{
			$titleicon = ' <span>'.JHtml::_('image', 'menu/icon-16-default.png', '*', array('title' => JText::_('MOD_MENU_HOME_DEFAULT')), true).'</span>';
		}
		elseif ($menuType->home > 1)
		{
			$titleicon = ' <span>'.JHtml::_('image', 'menu/icon-16-language.png', $menuType->home, array('title' => JText::_('MOD_MENU_HOME_MULTIPLE')), true).'</span>';
		}
		else
		{
			$image = JHtml::_('image', 'mod_languages/'.$menuType->image.'.gif', NULL, NULL, true, true);
			if (!$image)
			{
				$titleicon = ' <span>'.JHtml::_('image', 'menu/icon-16-language.png', $alt, array('title' => $menuType->title_native), true).'</span>';
			}
			else
			{
				$titleicon = ' <span>'.JHtml::_('image', 'mod_languages/'.$menuType->image.'.gif', $alt, array('title'=>$menuType->title_native), true).'</span>';
			}
		}
		unset($m); $m = new JMenuNode($menuType->title,	'index.php?option=com_menus&view=items&menutype='.$menuType->menutype, 'class:menu', null, null, $titleicon);
		$menu->addChild($m, $createMenu);

		if ($createMenu)
		{
			unset($m); $m = new JMenuNode(JText::_('MOD_MENU_MENU_MANAGER_NEW_MENU_ITEM'), 'index.php?option=com_menus&view=item&layout=edit&menutype='.$menuType->menutype, 'class:newarticle'); 
			$menu->addChild($m);
			$menu->getParent();
		}
	}
	$menu->getParent();
}

//
// Content Submenu
//
if ($user->authorise('core.manage', 'com_content'))
{
	unset($m); $m = new JMenuNode(JText::_('MOD_MENU_COM_CONTENT'), '#');
	$menu->addChild($m, true);
	$createContent =  $shownew && $user->authorise('core.create', 'com_content');
	unset($m); $m = new JMenuNode(JText::_('MOD_MENU_COM_CONTENT_ARTICLE_MANAGER'), 'index.php?option=com_content', 'class:article');
	$menu->addChild($m, $createContent);
	if ($createContent)
	{
		unset($m); $m = new JMenuNode(JText::_('MOD_MENU_COM_CONTENT_NEW_ARTICLE'), 'index.php?option=com_content&task=article.add', 'class:newarticle');
		$menu->addChild($m);
		$menu->getParent();
	}

	unset($m); $m = new JMenuNode(JText::_('MOD_MENU_COM_CONTENT_CATEGORY_MANAGER'), 'index.php?option=com_categories&extension=com_content', 'class:category'); 
	$menu->addChild($m, $createContent);
	if ($createContent)
	{
		unset($m); $m = new JMenuNode(JText::_('MOD_MENU_COM_CONTENT_NEW_CATEGORY'), 'index.php?option=com_categories&task=category.add&extension=com_content', 'class:newarticle');
		$menu->addChild($m);
		$menu->getParent();
	}
	unset($m); $m = new JMenuNode(JText::_('MOD_MENU_COM_CONTENT_FEATURED'), 'index.php?option=com_content&view=featured', 'class:featured');
	$menu->addChild($m);
	$menu->addSeparator();
	if ($user->authorise('core.manage', 'com_media'))
	{
		unset($m); $m = new JMenuNode(JText::_('MOD_MENU_MEDIA_MANAGER'), 'index.php?option=com_media', 'class:media');
		$menu->addChild($m);
	}

	$menu->getParent();
}

//
// Components Submenu
//

// Get the authorised components and sub-menus.
	$components = ModMenuHelper::getComponents( true );
	
	// Check if there are any components, otherwise, don't render the menu
	if ($components)
	{
		unset($m); $m = new JMenuNode(JText::_('MOD_MENU_COMPONENTS'), '#');
		$menu->addChild($m, true);
	
		foreach ($components as $component)
		{
			if (!empty($component->submenu))
			{
				// This component has a db driven submenu.
				unset($m); $m = new JMenuNode($component->text, $component->link, $component->img);
				$menu->addChild($m, true);
				foreach ($component->submenu as $sub)
				{
					unset($m); $m = new JMenuNode($sub->text, $sub->link, $sub->img);
					$menu->addChild($m);
				}
				$menu->getParent();
			}
			else
			{
				unset($m); $m = new JMenuNode($component->text, $component->link, $component->img);
				$menu->addChild($m);
			}
		}
		$menu->getParent();
	}

	//
	// Extensions Submenu
	//
	$im = $user->authorise('core.manage', 'com_installer');
	$mm = $user->authorise('core.manage', 'com_modules');
	$pm = $user->authorise('core.manage', 'com_plugins');
	$tm = $user->authorise('core.manage', 'com_templates');
	$lm = $user->authorise('core.manage', 'com_languages');
	
	if ($im || $mm || $pm || $tm || $lm)
	{
		unset($m); $m = new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_EXTENSIONS'), '#');
		$menu->addChild($m, true);
	
		if ($im)
		{
			unset($m); $m = new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_EXTENSION_MANAGER'), 'index.php?option=com_installer', 'class:install');
			$menu->addChild($m);
			$menu->addSeparator();
		}
	
		if ($mm)
		{
			unset($m); $m = new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_MODULE_MANAGER'), 'index.php?option=com_modules', 'class:module');
			$menu->addChild($m);
		}
	
		if ($pm)
		{
			unset($m); $m = new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_PLUGIN_MANAGER'), 'index.php?option=com_plugins', 'class:plugin');
			$menu->addChild($m);
		}
	
		if ($tm)
		{
			unset($m); $m = new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_TEMPLATE_MANAGER'), 'index.php?option=com_templates', 'class:themes');
			$menu->addChild($m);
		}
	
		if ($lm)
		{
			unset($m); $m = new JMenuNode(JText::_('MOD_MENU_EXTENSIONS_LANGUAGE_MANAGER'), 'index.php?option=com_languages', 'class:language');
			$menu->addChild($m);
		}
		$menu->getParent();
	}

unset($m); $m = new JMenuNode('<h1>Torna alla gestione del GAS</h1>', 'index.php?option=com_cake&controller=Pages&action=home');
$menu->addChild($m, true);
$menu->getParent();
	
//
// Help Submenu
//
if ($showhelp == 1)
{
	unset($m); $m = new JMenuNode(JText::_('MOD_MENU_HELP'), '#');
	$menu->addChild($m, true);
	unset($m); $m = new JMenuNode(JText::_('MOD_MENU_HELP_JOOMLA'), 'index.php?option=com_admin&view=help', 'class:help');
	$menu->addChild($m);
	$menu->addSeparator();

	unset($m); $m = new JMenuNode(JText::_('MOD_MENU_HELP_SUPPORT_OFFICIAL_FORUM'), 'http://forum.joomla.org', 'class:help-forum', false, '_blank');
	$menu->addChild($m);
	if ($forum_url = $params->get('forum_url'))
	{
		unset($m); $m = new JMenuNode(JText::_('MOD_MENU_HELP_SUPPORT_CUSTOM_FORUM'), $forum_url, 'class:help-forum', false, '_blank');
		$menu->addChild($m);
	}
	$debug = $lang->setDebug(false);
	if ($lang->hasKey('MOD_MENU_HELP_SUPPORT_OFFICIAL_LANGUAGE_FORUM_VALUE') && JText::_('MOD_MENU_HELP_SUPPORT_OFFICIAL_LANGUAGE_FORUM_VALUE') != '')
	{
		$forum_url = 'http://forum.joomla.org/viewforum.php?f=' . (int) JText::_('MOD_MENU_HELP_SUPPORT_OFFICIAL_LANGUAGE_FORUM_VALUE');
		$lang->setDebug($debug);
		unset($m); $m = new JMenuNode(JText::_('MOD_MENU_HELP_SUPPORT_OFFICIAL_LANGUAGE_FORUM'), $forum_url, 'class:help-forum', false, '_blank');
		$menu->addChild($m);
	}
	$lang->setDebug($debug);
	unset($m); $m = new JMenuNode(JText::_('MOD_MENU_HELP_DOCUMENTATION'), 'http://docs.joomla.org', 'class:help-docs', false, '_blank');
	$menu->addChild($m);
	$menu->addSeparator();
	unset($m); $m = new JMenuNode(JText::_('MOD_MENU_HELP_LINKS'), '#', 'class:weblinks');
	$menu->addChild($m, true);
	unset($m); $m = new JMenuNode(JText::_('MOD_MENU_HELP_EXTENSIONS'), 'http://extensions.joomla.org', 'class:help-jed', false, '_blank');
	$menu->addChild($m);
	unset($m); $m = new JMenuNode(JText::_('MOD_MENU_HELP_TRANSLATIONS'), 'http://community.joomla.org/translations.html', 'class:help-trans', false, '_blank');
	$menu->addChild($m);
	unset($m); $m = new JMenuNode(JText::_('MOD_MENU_HELP_RESOURCES'), 'http://resources.joomla.org', 'class:help-jrd', false, '_blank');
	$menu->addChild($m);
	unset($m); $m = new JMenuNode(JText::_('MOD_MENU_HELP_COMMUNITY'), 'http://community.joomla.org', 'class:help-community', false, '_blank');
	$menu->addChild($m);
	unset($m); $m = new JMenuNode(JText::_('MOD_MENU_HELP_SECURITY'), 'http://developer.joomla.org/security.html', 'class:help-security', false, '_blank');
	$menu->addChild($m);
	unset($m); $m = new JMenuNode(JText::_('MOD_MENU_HELP_DEVELOPER'), 'http://developer.joomla.org', 'class:help-dev', false, '_blank');
	$menu->addChild($m);
	unset($m); $m = new JMenuNode(JText::_('MOD_MENU_HELP_SHOP'), 'http://shop.joomla.org', 'class:help-shop', false, '_blank');
	$menu->addChild($m);
	$menu->getParent();
	$menu->getParent();
}