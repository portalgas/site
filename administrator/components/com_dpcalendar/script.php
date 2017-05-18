<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::register('DPCalendarHelper', dirname(__FILE__) . '/admin/helpers/dpcalendar.php');

class Com_DPCalendarInstallerScript
{

	public function install ($parent)
	{
	}

	public function update ($parent)
	{
		$version = $this->getParam('version');
		if (empty($version))
		{
			return;
		}

		if (version_compare($version, '2.0.0') == - 1)
		{
			$this->run("update `#__extensions` set enabled=1 where type = 'plugin' and element = 'dpcalendar'");

			$this->run("ALTER TABLE  `#__dpcalendar_events` ADD INDEX  `idx_start_date` (  `start_date` )");
			$this->run("ALTER TABLE  `#__dpcalendar_events` ADD INDEX  `idx_end_date` (  `end_date` )");

			// Enhance location
			$this->run("ALTER TABLE `#__dpcalendar_events` ADD `latitude` float NULL DEFAULT NULL AFTER `location`");
			$this->run("ALTER TABLE `#__dpcalendar_events` ADD `longitude` float NULL DEFAULT NULL AFTER `latitude`");

			// Migrate to rule
			$this->run("ALTER TABLE `#__dpcalendar_events` ADD `rrule` varchar(255) AFTER `alias`");

			$this->run("select * from `#__dpcalendar_events` where original_id = -1");
			$events = JFactory::getDBO()->loadObjectList();

			foreach ($events as $event)
			{
				$rule = '';

				switch ($event->scheduling)
				{
					case 1:
						if ($event->scheduling_daily_weekdays == 1)
						{
							$rule = 'FREQ=WEEKLY;BYDAY=MO,TU,WE,TH,FR';
						}
						else
						{
							$rule = 'FREQ=DAILY';
						}
						break;
					case 2:
						$rule = 'FREQ=WEEKLY';
						$registry = new JRegistry();
						$registry->loadString($event->scheduling_weekly_days);
						$weeklyDays = $registry->toArray();
						if (count($weeklyDays) > 0)
						{
							$rule .= ';BYDAY=';
						}
						$map = array(
								1 => 'MO',
								2 => 'TU',
								3 => 'WE',
								4 => 'TH',
								5 => 'FR',
								6 => 'SA',
								7 => 'SU'
						);
						foreach ($weeklyDays as $day)
						{
							$rule .= $map[$day] . ',';
						}
						$rule = trim($rule, ',');
						break;
					case 3:
						$rule = 'FREQ=MONTHLY';
						$registry = new JRegistry();
						$registry->loadString($event->scheduling_monthly_days);
						$monthlyDays = $registry->toArray();
						if (count($monthlyDays) > 0)
						{
							$rule .= ';BYMONTHDAY=' . implode(',', $monthlyDays);
						}
						break;
					case 4:
						$rule = 'FREQ=YEARLY';
						break;
				}
				if (! empty($event->scheduling_end_date))
				{
					$rule .= ';UNTIL=' . str_replace('-', '', substr($event->scheduling_end_date, 0, 10)) . '235959Z';
				}

				$this->run("update `#__dpcalendar_events` set rrule='" . $rule . "' where id =" . $event->id);
			}
			$this->run("ALTER TABLE `#__dpcalendar_events` drop `scheduling_start_date`");
			$this->run("ALTER TABLE `#__dpcalendar_events` drop `scheduling_end_date`");
			$this->run("ALTER TABLE `#__dpcalendar_events` drop `scheduling_daily_weekdays`");
			$this->run("ALTER TABLE `#__dpcalendar_events` drop `scheduling_weekly_days`");
			$this->run("ALTER TABLE `#__dpcalendar_events` drop `scheduling_monthly_days`");

			foreach (JFolder::files(JPATH_ADMINISTRATOR . '/language', '.*dpcalendar.*', true, true) as $file)
			{
				JFile::delete($file);
			}
			foreach (JFolder::files(JPATH_SITE . '/language', '.*dpcalendar.*', true, true) as $file)
			{
				JFile::delete($file);
			}
		}
		if (version_compare($version, '2.2.0') == - 1)
		{
			$this->run(
					"CREATE TABLE IF NOT EXISTS `#__dpcalendar_locations` (
					`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					`title` varchar(255) NOT NULL DEFAULT '',
					`alias` varchar(255) NOT NULL DEFAULT '',
					`country` varchar(255) NOT NULL DEFAULT '',
					`province` varchar(255) NOT NULL DEFAULT '',
					`city` varchar(255) NOT NULL DEFAULT '',
					`zip` varchar(255) NOT NULL DEFAULT '',
					`street` varchar(255) NOT NULL DEFAULT '',
					`number` varchar(255) NOT NULL DEFAULT '',
					`room` varchar(255) NOT NULL DEFAULT '',
					`latitude` float DEFAULT NULL,
					`longitude` float DEFAULT NULL,
					`url` varchar(250) NOT NULL DEFAULT '',
					`description` text NOT NULL,
					`date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
					`state` tinyint(1) NOT NULL DEFAULT '0',
					`checked_out` int(11) NOT NULL DEFAULT '0',
					`checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
					`ordering` int(11) NOT NULL DEFAULT '0',
					`params` text NOT NULL,
					`language` char(7) NOT NULL DEFAULT '',
					`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
					`created_by` int(10) unsigned NOT NULL DEFAULT '0',
					`created_by_alias` varchar(255) NOT NULL DEFAULT '',
					`version` int(10) unsigned NOT NULL DEFAULT '0',
					`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
					`modified_by` int(10) unsigned NOT NULL DEFAULT '0',
					`publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
					`publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
					PRIMARY KEY (`id`),
					KEY `idx_checkout` (`checked_out`),
					KEY `idx_state` (`state`),
					KEY `idx_createdby` (`created_by`),
					KEY `idx_language` (`language`)
			) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ");

			$this->run(
					"CREATE TABLE IF NOT EXISTS `#__dpcalendar_events_location` (
					`event_id` int(11) NOT NULL DEFAULT '0',
					`location_id` int(11) NOT NULL DEFAULT '0',
					PRIMARY KEY (`event_id`,`location_id`)
			) DEFAULT CHARSET=utf8;");

			$db = JFactory::getDbo();
			$db->setQuery(
					"select id,location,latitude,longitude from `#__dpcalendar_events` where location is not null and location != '' group by location");
			$locations = $db->loadObjectList();
			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_dpcalendar/tables');
			foreach ($locations as $loc)
			{
				$data = array();
				if ($loc->latitude != 0 && $loc->longitude != 0)
				{
					$data['latitude'] = $loc->latitude;
					$data['longitude'] = $loc->longitude;
					$data['title'] = $loc->location;
					$data['country'] = $loc->location;
				}
				else
				{
					$content = DPCalendarHelper::fetchContent(
							'http://maps.google.com/maps/api/geocode/json?address=' . urlencode($loc->location) . '&sensor=false');
					if (! empty($content))
					{
						$tmp = json_decode($content);

						if ($tmp)
						{
							if ($tmp->status == 'OK')
							{
								if (! empty($tmp->results))
								{
									foreach ($tmp->results[0]->address_components as $part)
									{
										switch ($part->types[0])
										{
											case 'country':
												$data['country'] = $part->long_name;
												break;
											case 'administrative_area_level_1':
												$data['province'] = $part->long_name;
												break;
											case 'locality':
												$data['city'] = $part->long_name;
												break;
											case 'postal_code':
												$data['zip'] = $part->long_name;
												break;
											case 'route':
												$data['street'] = $part->long_name;
												break;
											case 'street_number':
												$data['number'] = $part->long_name;
												break;
										}
									}

									$data['latitude'] = $tmp->results[0]->geometry->location->lat;
									$data['longitude'] = $tmp->results[0]->geometry->location->lng;

									$data['title'] = $tmp->results[0]->formatted_address;
								}
							}
						}
					}
				}

				if (! empty($data))
				{
					$data['state'] = 1;
					$data['language'] = '*';
					$table = JTable::getInstance('Location', 'DPCalendarTable');
					$table->save($data);

					if ($table->id)
					{
						$db->setQuery(
								'insert into #__dpcalendar_events_location (event_id, location_id) select id as event_id, ' . $table->id .
										 " as location_id from #__dpcalendar_events where location='" . $loc->location . "'");
						$db->execute();
					}
				}
			}
			$this->run("ALTER TABLE `#__dpcalendar_events` drop `location`");
			$this->run("ALTER TABLE `#__dpcalendar_events` drop `latitude`");
			$this->run("ALTER TABLE `#__dpcalendar_events` drop `longitude`");
		}

		if (version_compare($version, '3.0.0') == - 1)
		{
			$this->run(
					"CREATE TABLE IF NOT EXISTS `#__dpcalendar_caldav_calendarobjects` (
					`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					`calendardata` mediumblob,
					`uri` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
					`calendarid` int(10) unsigned NOT NULL,
					`lastmodified` int(11) unsigned DEFAULT NULL,
					`etag` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
					`size` int(11) unsigned NOT NULL,
					`componenttype` varchar(8) COLLATE utf8_unicode_ci DEFAULT NULL,
					`firstoccurence` int(11) unsigned DEFAULT NULL,
					`lastoccurence` int(11) unsigned DEFAULT NULL,
					PRIMARY KEY (`id`),
					UNIQUE KEY `calendarid` (`calendarid`,`uri`)
			) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");

			$this->run(
					"CREATE TABLE IF NOT EXISTS `#__dpcalendar_caldav_calendars` (
					`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					`principaluri` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
					`displayname` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
					`uri` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
					`ctag` int(10) unsigned NOT NULL DEFAULT '0',
					`description` text COLLATE utf8_unicode_ci,
					`calendarorder` int(10) unsigned NOT NULL DEFAULT '0',
					`calendarcolor` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
					`timezone` text COLLATE utf8_unicode_ci,
					`components` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
					`transparent` tinyint(1) NOT NULL DEFAULT '0',
					PRIMARY KEY (`id`),
					UNIQUE KEY `principaluri` (`principaluri`,`uri`)
			) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");

			$this->run(
					"CREATE TABLE IF NOT EXISTS `#__dpcalendar_caldav_principals` (
					`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					`uri` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
					`email` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
					`displayname` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
					`vcardurl` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
					`external_id` int(11) unsigned NOT NULL,
					PRIMARY KEY (`id`),
					UNIQUE KEY `uri` (`uri`),
					KEY `external_id` (`external_id`)
			) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");

			$this->run(
					"CREATE TABLE IF NOT EXISTS `#__dpcalendar_caldav_groupmembers` (
					`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					`principal_id` int(10) unsigned NOT NULL,
					`member_id` int(10) unsigned NOT NULL,
					PRIMARY KEY (`id`),
					UNIQUE(principal_id, member_id)
			);");

			$this->run(
					'insert into `#__dpcalendar_caldav_principals`
					(uri, email, displayname, external_id) select concat("principals/", username) as uri, email, name as displayname, id
					from `#__users` u ON DUPLICATE KEY UPDATE email=u.email, displayname=u.name');
			$this->run(
					'insert into `#__dpcalendar_caldav_principals`
					(uri, email, displayname, external_id) select concat("principals/", username, "/calendar-proxy-read") as uri, email, name as displayname, id
					from `#__users` u ON DUPLICATE KEY UPDATE email=u.email, displayname=u.name');
			$this->run(
					'insert into `#__dpcalendar_caldav_principals`
					(uri, email, displayname, external_id) select concat("principals/", username, "/calendar-proxy-write") as uri, email, name as displayname, id
					from `#__users` u ON DUPLICATE KEY UPDATE email=u.email, displayname=u.name');
		}
		if (version_compare($version, '3.3.0') == - 1)
		{
			$this->run('alter table `#__dpcalendar_events` add  `capacity` int( 11 ) null after `hits`');
			$this->run('update `#__dpcalendar_events` set capacity = 0');
			$this->run('alter table `#__dpcalendar_events` add  `capacity_used` int( 11 ) default 0 after `capacity`');
			$this->run(
					"CREATE TABLE IF NOT EXISTS `#__dpcalendar_attendees` (
				  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `event_id` int(11) NOT NULL,
				  `user_id` int(11) NOT NULL DEFAULT '0',
				  `location_id` int(11) NOT NULL DEFAULT '0',
				  `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
				  `telephone` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
				  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
				  `attend_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
				  `remind_time` int(11) NOT NULL,
				  `remind_type` tinyint(1) NOT NULL DEFAULT '1',
				  `reminder_sent_date` datetime DEFAULT NULL,
				  `public` tinyint(1) NOT NULL DEFAULT '1',
				  `state` tinyint(1) NOT NULL DEFAULT '0',
				  PRIMARY KEY (`id`),
				  KEY `event_id` (`event_id`)
				) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
			$this->run('alter table  `#__dpcalendar_events` add `recurrence_id` varchar(255) DEFAULT NULL AFTER `rrule`');
			$this->run("update `#__dpcalendar_events` set recurrence_id = DATE_FORMAT(start_date, '%Y%m%dT%H%i%sZ') where original_id > 0");
			$this->run('alter table `#__dpcalendar_locations` change `latitude` `latitude` decimal( 9, 6 ) null default null');
		}

		if (version_compare($version, '4.0.0') == - 1)
		{
			$this->run(
					"ALTER IGNORE TABLE  `#__dpcalendar_attendees` ADD  `transaction_id` VARCHAR( 255 ) NULL DEFAULT NULL ,
ADD  `price` DECIMAL( 10, 2 ) NOT NULL DEFAULT  '0.00',
ADD  `processor` VARCHAR( 255 ) DEFAULT NULL,
ADD  `net_amount` DECIMAL( 10, 2 ) NOT NULL DEFAULT  '0.00',
ADD  `tax_amount` DECIMAL( 10, 2 ) NOT NULL DEFAULT  '0.00',
ADD  `gross_amount` DECIMAL( 10, 2 ) NOT NULL DEFAULT  '0.00',
ADD  `payment_fee` DECIMAL( 10, 2 ) NOT NULL DEFAULT  '0.00',
ADD  `tax_percent` FLOAT DEFAULT NULL,
ADD  `txn_type` VARCHAR( 255 ) NOT NULL ,
ADD  `payer_id` VARCHAR( 255 ) NOT NULL ,
ADD  `payer_email` VARCHAR( 255 ) NOT NULL;");

			$this->run("ALTER IGNORE TABLE `#__dpcalendar_events` ADD COLUMN `price` DECIMAL(10, 2) NOT NULL DEFAULT '0.00' AFTER `capacity_used`");
			$this->run("ALTER IGNORE TABLE `#__dpcalendar_events` ADD COLUMN `tax` TINYINT(1) NOT NULL DEFAULT '0' AFTER `price`");
			$this->run("ALTER IGNORE TABLE `#__dpcalendar_events` ADD COLUMN `ordertext` TEXT NOT NULL AFTER `tax`");
			$this->run("ALTER IGNORE TABLE `#__dpcalendar_events` ADD COLUMN `orderurl` TEXT NOT NULL AFTER `ordertext`");
			$this->run("ALTER IGNORE TABLE `#__dpcalendar_events` ADD COLUMN `canceltext` TEXT NOT NULL AFTER `orderurl`");
			$this->run("ALTER IGNORE TABLE `#__dpcalendar_events` ADD COLUMN `cancelurl` TEXT NOT NULL AFTER `canceltext`");
			$this->run("ALTER IGNORE TABLE `#__dpcalendar_events` ADD COLUMN `plugintype` TEXT NOT NULL");
			$this->run(
					"CREATE TABLE IF NOT EXISTS `#__dpcalendar_extcalendars` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `plugin` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `color` varchar(250) NOT NULL DEFAULT '',
  `state` tinyint(1) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `params` text NOT NULL,
  `language` char(7) NOT NULL DEFAULT '',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(10) unsigned NOT NULL DEFAULT '0',
  `created_by_alias` varchar(255) NOT NULL DEFAULT '',
  `version` int(10) unsigned NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(10) unsigned NOT NULL DEFAULT '0',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `idx_plugin` (`plugin`),
  KEY `idx_state` (`state`),
  KEY `idx_createdby` (`created_by`),
  KEY `idx_language` (`language`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");
		}

		if (version_compare($version, '4.0.1') == - 1)
		{
			if (JFile::exists(JPATH_ADMINISTRATOR . '/components/com_dpcalendar/models/events.php'))
			{
				JFile::delete(JPATH_ADMINISTRATOR . '/components/com_dpcalendar/models/events.php');
			}
		}
		if (version_compare($version, '4.0.5') == - 1)
		{
			$db = JFactory::getDBO();
			$db->setQuery("select * from #__dpcalendar_extcalendars where plugin = 'google' or plugin = 'caldav'");

			foreach ($db->loadObjectList() as $cal)
			{
				$params = new JRegistry();
				$params->loadString($cal->params);
				$params->set('action-create', true);
				$params->set('action-edit', true);
				$params->set('action-delete', true);

				$db->setQuery('update #__dpcalendar_extcalendars set params = ' . $db->q($params->toString()) . ' where id = ' . (int) $cal->id);
				$db->query();
			}
		}
		if (version_compare($version, '4.0.6') == - 1)
		{
			$db = JFactory::getDBO();
			$db->setQuery('delete from #__dpcalendar_extcalendars where plugin = ' . $db->q('') . ' or plugin is null');
			$db->query();
		}
		if (version_compare($version, '4.1.0') == - 1)
		{
			$this->run("ALTER TABLE  `#__dpcalendar_locations` CHANGE  `latitude`  `latitude` DECIMAL( 20, 15 ) NULL DEFAULT  '0.0'");
			$this->run("ALTER TABLE  `#__dpcalendar_locations` CHANGE  `longitude`  `longitude` DECIMAL( 20, 15 ) NULL DEFAULT  '0.0'");
			$this->run("ALTER TABLE  `#__dpcalendar_extcalendars` ADD  `access_content` INT( 11 ) NOT NULL DEFAULT  '1'");
		}
		if (version_compare($version, '4.1.1') == - 1)
		{
			$this->run("ALTER TABLE  `#__dpcalendar_locations` CHANGE  `latitude`  `latitude` DECIMAL( 12, 8 ) DEFAULT NULL");
			$this->run("ALTER TABLE  `#__dpcalendar_locations` CHANGE  `longitude`  `longitude` DECIMAL( 12, 8 ) DEFAULT NULL");
		}
		if (version_compare($version, '4.1.2') == - 1)
		{
			if (DPCalendarHelper::isJoomlaVersion('3'))
			{
				$this->run(
						'INSERT INTO `#__content_types`
					(`type_id`, `type_title`, `type_alias`, `table`, `rules`, `field_mappings`, `router`, `content_history_options`)
					VALUES (NULL,
					\'DPCalendar Category\',
					\'com_dpcalendar.category\',
					\'{"special":{"dbtable":"#__categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},
						"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable",
						"config":"array()"}}\', \'\', \'{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published",
						"core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description",
						"core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params",
						"core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null",
						"core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id",
						"core_xreference":"null", "asset_id":"asset_id"}, "special": {"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level",
						"path":"path","extension":"extension","note":"note"}}\',
					\'DPCalendarHelper::getCalendarRoute\', \'\')');
			}
		}
	}

	public function uninstall ($parent)
	{
	}

	public function preflight ($type, $parent)
	{
	}

	public function postflight ($type, $parent)
	{
		if (JFile::exists(JPATH_SITE . '/components/com_jcomments/jcomments.php'))
		{
			JFile::copy(JPATH_ADMINISTRATOR . '/components/com_dpcalendar/libraries/jcomments' . DS . 'com_dpcalendar.plugin.php',
					JPATH_SITE . '/components/com_jcomments/plugins');
		}

		if (version_compare(PHP_VERSION, '5.3.0') < 0)
		{
			JFolder::delete(JPATH_PLUGINS . '/system/dpcalendar');
		}

		if ($type == 'install')
		{
			$this->run("update `#__extensions` set enabled=1 where type = 'plugin' and element = 'dpcalendar'");

			$this->run("update `#__extensions` set enabled=1 where type = 'plugin' and element = 'dpcalendar_manual'");

			$this->run(
					"insert into `#__modules_menu` (menuid, moduleid) select 0 as menuid, id as moduleid from `#__modules` where module like 'mod_dpcalendar%'");

			// Create default table
			JTable::addIncludePath(JPATH_LIBRARIES . '/joomla/database/table');
			$category = JTable::getInstance('Category');
			$category->extension = 'com_dpcalendar';
			$category->title = 'Uncategorised';
			$category->alias = 'uncategorised';
			$category->description = '';
			$category->published = 1;
			$category->access = 1;
			$category->params = '{"category_layout":"","image":"","color":"3366CC"}';
			$category->metadata = '{"author":"","robots":""}';
			$category->language = '*';
			$category->setLocation(1, 'last-child');
			$category->store(true);
			$category->rebuildPath($category->id);

			if (DPCalendarHelper::isJoomlaVersion('3'))
			{
				$this->run(
						'INSERT INTO `#__content_types`
					(`type_id`, `type_title`, `type_alias`, `table`, `rules`, `field_mappings`, `router`, `content_history_options`)
					VALUES (NULL,
					\'DPCalendar Category\',
					\'com_dpcalendar.category\',
					\'{"special":{"dbtable":"#__categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},
						"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable",
						"config":"array()"}}\', \'\', \'{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published",
						"core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description",
						"core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params",
						"core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null",
						"core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id",
						"core_xreference":"null", "asset_id":"asset_id"}, "special": {"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level",
						"path":"path","extension":"extension","note":"note"}}\',
					\'DPCalendarHelper::getCalendarRoute\', \'\')');
			}
		}
	}

	private function run ($query)
	{
		try
		{
			$db = JFactory::getDBO();
			$db->setQuery($query);
			$db->query();
		}
		catch (Exception $e)
		{
			echo $e;
		}
	}

	private function getParam ($name)
	{
		$db = JFactory::getDbo();
		$db->setQuery('SELECT manifest_cache FROM `#__extensions` WHERE name = "com_dpcalendar"');
		$manifest = json_decode($db->loadResult(), true);
		return $manifest[$name];
	}
}
