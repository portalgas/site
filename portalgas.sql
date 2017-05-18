SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


DROP TABLE IF EXISTS `jos_users`;
CREATE TABLE IF NOT EXISTS `jos_users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `username` varchar(150) NOT NULL DEFAULT '',
  `email` varchar(100) NOT NULL DEFAULT '',
  `password` varchar(100) NOT NULL DEFAULT '',
  `usertype` varchar(25) NOT NULL DEFAULT '',
  `block` tinyint(4) NOT NULL DEFAULT '0',
  `sendEmail` tinyint(4) DEFAULT '0',
  `gid` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `registerDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lastvisitDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `activation` varchar(100) NOT NULL DEFAULT '',
  `params` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_assets`;
CREATE TABLE IF NOT EXISTS `j_assets` (
  `id` int(10) unsigned NOT NULL COMMENT 'Primary Key',
  `parent_id` int(11) NOT NULL DEFAULT '0' COMMENT 'Nested set parent.',
  `lft` int(11) NOT NULL DEFAULT '0' COMMENT 'Nested set lft.',
  `rgt` int(11) NOT NULL DEFAULT '0' COMMENT 'Nested set rgt.',
  `level` int(10) unsigned NOT NULL COMMENT 'The cached level in the nested tree.',
  `name` varchar(50) NOT NULL COMMENT 'The unique name for the asset.\n',
  `title` varchar(100) NOT NULL COMMENT 'The descriptive title for the asset.',
  `rules` varchar(5120) NOT NULL COMMENT 'JSON encoded access control.'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_associations`;
CREATE TABLE IF NOT EXISTS `j_associations` (
  `id` varchar(50) NOT NULL COMMENT 'A reference to the associated item.',
  `context` varchar(50) NOT NULL COMMENT 'The context of the associated item.',
  `key` char(32) NOT NULL COMMENT 'The key for the association computed from an md5 on associated ids.'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_banners`;
CREATE TABLE IF NOT EXISTS `j_banners` (
  `id` int(11) NOT NULL,
  `cid` int(11) NOT NULL DEFAULT '0',
  `type` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `imptotal` int(11) NOT NULL DEFAULT '0',
  `impmade` int(11) NOT NULL DEFAULT '0',
  `clicks` int(11) NOT NULL DEFAULT '0',
  `clickurl` varchar(200) NOT NULL DEFAULT '',
  `state` tinyint(3) NOT NULL DEFAULT '0',
  `catid` int(10) unsigned NOT NULL DEFAULT '0',
  `description` text NOT NULL,
  `custombannercode` varchar(2048) NOT NULL,
  `sticky` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `metakey` text NOT NULL,
  `params` text NOT NULL,
  `own_prefix` tinyint(1) NOT NULL DEFAULT '0',
  `metakey_prefix` varchar(255) NOT NULL DEFAULT '',
  `purchase_type` tinyint(4) NOT NULL DEFAULT '-1',
  `track_clicks` tinyint(4) NOT NULL DEFAULT '-1',
  `track_impressions` tinyint(4) NOT NULL DEFAULT '-1',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `reset` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `language` char(7) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_banner_clients`;
CREATE TABLE IF NOT EXISTS `j_banner_clients` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `contact` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `extrainfo` text NOT NULL,
  `state` tinyint(3) NOT NULL DEFAULT '0',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `metakey` text NOT NULL,
  `own_prefix` tinyint(4) NOT NULL DEFAULT '0',
  `metakey_prefix` varchar(255) NOT NULL DEFAULT '',
  `purchase_type` tinyint(4) NOT NULL DEFAULT '-1',
  `track_clicks` tinyint(4) NOT NULL DEFAULT '-1',
  `track_impressions` tinyint(4) NOT NULL DEFAULT '-1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_banner_tracks`;
CREATE TABLE IF NOT EXISTS `j_banner_tracks` (
  `track_date` datetime NOT NULL,
  `track_type` int(10) unsigned NOT NULL,
  `banner_id` int(10) unsigned NOT NULL,
  `count` int(10) unsigned NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_categories`;
CREATE TABLE IF NOT EXISTS `j_categories` (
  `id` int(11) NOT NULL,
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `lft` int(11) NOT NULL DEFAULT '0',
  `rgt` int(11) NOT NULL DEFAULT '0',
  `level` int(10) unsigned NOT NULL DEFAULT '0',
  `path` varchar(255) NOT NULL DEFAULT '',
  `extension` varchar(50) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `note` varchar(255) NOT NULL DEFAULT '',
  `description` mediumtext NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `params` text NOT NULL,
  `metadesc` varchar(1024) NOT NULL COMMENT 'The meta description for the page.',
  `metakey` varchar(1024) NOT NULL COMMENT 'The meta keywords for the page.',
  `metadata` varchar(2048) NOT NULL COMMENT 'JSON encoded metadata properties.',
  `created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  `language` char(7) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_contact_details`;
CREATE TABLE IF NOT EXISTS `j_contact_details` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `con_position` varchar(255) DEFAULT NULL,
  `address` text,
  `suburb` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `postcode` varchar(100) DEFAULT NULL,
  `telephone` varchar(255) DEFAULT NULL,
  `fax` varchar(255) DEFAULT NULL,
  `misc` mediumtext,
  `image` varchar(255) DEFAULT NULL,
  `imagepos` varchar(20) DEFAULT NULL,
  `email_to` varchar(255) DEFAULT NULL,
  `default_con` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `params` text NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `catid` int(11) NOT NULL DEFAULT '0',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `mobile` varchar(255) NOT NULL DEFAULT '',
  `webpage` varchar(255) NOT NULL DEFAULT '',
  `sortname1` varchar(255) NOT NULL,
  `sortname2` varchar(255) NOT NULL,
  `sortname3` varchar(255) NOT NULL,
  `language` char(7) NOT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(10) unsigned NOT NULL DEFAULT '0',
  `created_by_alias` varchar(255) NOT NULL DEFAULT '',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(10) unsigned NOT NULL DEFAULT '0',
  `metakey` text NOT NULL,
  `metadesc` text NOT NULL,
  `metadata` text NOT NULL,
  `featured` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Set if article is featured.',
  `xreference` varchar(50) NOT NULL COMMENT 'A reference to enable linkages to external data sets.',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_content`;
CREATE TABLE IF NOT EXISTS `j_content` (
  `id` int(10) unsigned NOT NULL,
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `title_alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT 'Deprecated in Joomla! 3.0',
  `introtext` mediumtext NOT NULL,
  `fulltext` mediumtext NOT NULL,
  `state` tinyint(3) NOT NULL DEFAULT '0',
  `sectionid` int(10) unsigned NOT NULL DEFAULT '0',
  `mask` int(10) unsigned NOT NULL DEFAULT '0',
  `catid` int(10) unsigned NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(10) unsigned NOT NULL DEFAULT '0',
  `created_by_alias` varchar(255) NOT NULL DEFAULT '',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `images` text NOT NULL,
  `urls` text NOT NULL,
  `attribs` varchar(5120) NOT NULL,
  `version` int(10) unsigned NOT NULL DEFAULT '1',
  `parentid` int(10) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `metakey` text NOT NULL,
  `metadesc` text NOT NULL,
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  `metadata` text NOT NULL,
  `featured` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Set if article is featured.',
  `language` char(7) NOT NULL COMMENT 'The language code for the article.',
  `xreference` varchar(50) NOT NULL COMMENT 'A reference to enable linkages to external data sets.'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_contenttemplater`;
CREATE TABLE IF NOT EXISTS `j_contenttemplater` (
  `id` int(11) unsigned NOT NULL,
  `name` text NOT NULL,
  `description` text NOT NULL,
  `content` text NOT NULL,
  `params` text NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `checked_out` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_content_frontpage`;
CREATE TABLE IF NOT EXISTS `j_content_frontpage` (
  `content_id` int(11) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_content_rating`;
CREATE TABLE IF NOT EXISTS `j_content_rating` (
  `content_id` int(11) NOT NULL DEFAULT '0',
  `rating_sum` int(10) unsigned NOT NULL DEFAULT '0',
  `rating_count` int(10) unsigned NOT NULL DEFAULT '0',
  `lastip` varchar(50) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_core_log_searches`;
CREATE TABLE IF NOT EXISTS `j_core_log_searches` (
  `search_term` varchar(128) NOT NULL DEFAULT '',
  `hits` int(10) unsigned NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_extensions`;
CREATE TABLE IF NOT EXISTS `j_extensions` (
  `extension_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` varchar(20) NOT NULL,
  `element` varchar(100) NOT NULL,
  `folder` varchar(100) NOT NULL,
  `client_id` tinyint(3) NOT NULL,
  `enabled` tinyint(3) NOT NULL DEFAULT '1',
  `access` int(10) unsigned NOT NULL DEFAULT '1',
  `protected` tinyint(3) NOT NULL DEFAULT '0',
  `manifest_cache` text NOT NULL,
  `params` text NOT NULL,
  `custom_data` text NOT NULL,
  `system_data` text NOT NULL,
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ordering` int(11) DEFAULT '0',
  `state` int(11) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_finder_filters`;
CREATE TABLE IF NOT EXISTS `j_finder_filters` (
  `filter_id` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_alias` varchar(255) NOT NULL,
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `map_count` int(10) unsigned NOT NULL DEFAULT '0',
  `data` text NOT NULL,
  `params` mediumtext
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_finder_links`;
CREATE TABLE IF NOT EXISTS `j_finder_links` (
  `link_id` int(10) unsigned NOT NULL,
  `url` varchar(255) NOT NULL,
  `route` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `indexdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `md5sum` varchar(32) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `state` int(5) DEFAULT '1',
  `access` int(5) DEFAULT '0',
  `language` varchar(8) NOT NULL,
  `publish_start_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_end_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `start_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `list_price` double unsigned NOT NULL DEFAULT '0',
  `sale_price` double unsigned NOT NULL DEFAULT '0',
  `type_id` int(11) NOT NULL,
  `object` mediumblob NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_finder_links_terms0`;
CREATE TABLE IF NOT EXISTS `j_finder_links_terms0` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_finder_links_terms1`;
CREATE TABLE IF NOT EXISTS `j_finder_links_terms1` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_finder_links_terms2`;
CREATE TABLE IF NOT EXISTS `j_finder_links_terms2` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_finder_links_terms3`;
CREATE TABLE IF NOT EXISTS `j_finder_links_terms3` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_finder_links_terms4`;
CREATE TABLE IF NOT EXISTS `j_finder_links_terms4` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_finder_links_terms5`;
CREATE TABLE IF NOT EXISTS `j_finder_links_terms5` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_finder_links_terms6`;
CREATE TABLE IF NOT EXISTS `j_finder_links_terms6` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_finder_links_terms7`;
CREATE TABLE IF NOT EXISTS `j_finder_links_terms7` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_finder_links_terms8`;
CREATE TABLE IF NOT EXISTS `j_finder_links_terms8` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_finder_links_terms9`;
CREATE TABLE IF NOT EXISTS `j_finder_links_terms9` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_finder_links_termsa`;
CREATE TABLE IF NOT EXISTS `j_finder_links_termsa` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_finder_links_termsb`;
CREATE TABLE IF NOT EXISTS `j_finder_links_termsb` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_finder_links_termsc`;
CREATE TABLE IF NOT EXISTS `j_finder_links_termsc` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_finder_links_termsd`;
CREATE TABLE IF NOT EXISTS `j_finder_links_termsd` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_finder_links_termse`;
CREATE TABLE IF NOT EXISTS `j_finder_links_termse` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_finder_links_termsf`;
CREATE TABLE IF NOT EXISTS `j_finder_links_termsf` (
  `link_id` int(10) unsigned NOT NULL,
  `term_id` int(10) unsigned NOT NULL,
  `weight` float unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_finder_taxonomy`;
CREATE TABLE IF NOT EXISTS `j_finder_taxonomy` (
  `id` int(10) unsigned NOT NULL,
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `state` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `access` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ordering` tinyint(1) unsigned NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_finder_taxonomy_map`;
CREATE TABLE IF NOT EXISTS `j_finder_taxonomy_map` (
  `link_id` int(10) unsigned NOT NULL,
  `node_id` int(10) unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_finder_terms`;
CREATE TABLE IF NOT EXISTS `j_finder_terms` (
  `term_id` int(10) unsigned NOT NULL,
  `term` varchar(75) NOT NULL,
  `stem` varchar(75) NOT NULL,
  `common` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `phrase` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `weight` float unsigned NOT NULL DEFAULT '0',
  `soundex` varchar(75) NOT NULL,
  `links` int(10) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_finder_terms_common`;
CREATE TABLE IF NOT EXISTS `j_finder_terms_common` (
  `term` varchar(75) NOT NULL,
  `language` varchar(3) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_finder_tokens`;
CREATE TABLE IF NOT EXISTS `j_finder_tokens` (
  `term` varchar(75) NOT NULL,
  `stem` varchar(75) NOT NULL,
  `common` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `phrase` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `weight` float unsigned NOT NULL DEFAULT '1',
  `context` tinyint(1) unsigned NOT NULL DEFAULT '2'
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_finder_tokens_aggregate`;
CREATE TABLE IF NOT EXISTS `j_finder_tokens_aggregate` (
  `term_id` int(10) unsigned NOT NULL,
  `map_suffix` char(1) NOT NULL,
  `term` varchar(75) NOT NULL,
  `stem` varchar(75) NOT NULL,
  `common` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `phrase` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `term_weight` float unsigned NOT NULL,
  `context` tinyint(1) unsigned NOT NULL DEFAULT '2',
  `context_weight` float unsigned NOT NULL,
  `total_weight` float unsigned NOT NULL
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_finder_types`;
CREATE TABLE IF NOT EXISTS `j_finder_types` (
  `id` int(10) unsigned NOT NULL,
  `title` varchar(100) NOT NULL,
  `mime` varchar(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_languages`;
CREATE TABLE IF NOT EXISTS `j_languages` (
  `lang_id` int(11) unsigned NOT NULL,
  `lang_code` char(7) NOT NULL,
  `title` varchar(50) NOT NULL,
  `title_native` varchar(50) NOT NULL,
  `sef` varchar(50) NOT NULL,
  `image` varchar(50) NOT NULL,
  `description` varchar(512) NOT NULL,
  `metakey` text NOT NULL,
  `metadesc` text NOT NULL,
  `sitename` varchar(1024) NOT NULL DEFAULT '',
  `published` int(11) NOT NULL DEFAULT '0',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_menu`;
CREATE TABLE IF NOT EXISTS `j_menu` (
  `id` int(11) NOT NULL,
  `menutype` varchar(24) NOT NULL COMMENT 'The type of menu this item belongs to. FK to #__menu_types.menutype',
  `title` varchar(255) NOT NULL COMMENT 'The display title of the menu item.',
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'The SEF alias of the menu item.',
  `note` varchar(255) NOT NULL DEFAULT '',
  `path` varchar(1024) NOT NULL COMMENT 'The computed path of the menu item based on the alias field.',
  `link` varchar(1024) NOT NULL COMMENT 'The actually link the menu item refers to.',
  `type` varchar(16) NOT NULL COMMENT 'The type of link: Component, URL, Alias, Separator',
  `published` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'The published state of the menu link.',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'The parent menu item in the menu tree.',
  `level` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The relative level in the tree.',
  `component_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to #__extensions.id',
  `ordering` int(11) NOT NULL DEFAULT '0' COMMENT 'The relative ordering of the menu item in the tree.',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to #__users.id',
  `checked_out_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'The time the menu item was checked out.',
  `browserNav` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'The click behaviour of the link.',
  `access` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The access level required to view the menu item.',
  `img` varchar(255) NOT NULL COMMENT 'The image of the menu item.',
  `template_style_id` int(10) unsigned NOT NULL DEFAULT '0',
  `params` text NOT NULL COMMENT 'JSON encoded data for the menu item.',
  `lft` int(11) NOT NULL DEFAULT '0' COMMENT 'Nested set lft.',
  `rgt` int(11) NOT NULL DEFAULT '0' COMMENT 'Nested set rgt.',
  `home` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Indicates if this menu item is the home or default page.',
  `language` char(7) NOT NULL DEFAULT '',
  `client_id` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_menu_types`;
CREATE TABLE IF NOT EXISTS `j_menu_types` (
  `id` int(10) unsigned NOT NULL,
  `menutype` varchar(24) NOT NULL,
  `title` varchar(48) NOT NULL,
  `description` varchar(255) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_messages`;
CREATE TABLE IF NOT EXISTS `j_messages` (
  `message_id` int(10) unsigned NOT NULL,
  `user_id_from` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id_to` int(10) unsigned NOT NULL DEFAULT '0',
  `folder_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `date_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `state` tinyint(1) NOT NULL DEFAULT '0',
  `priority` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `subject` varchar(255) NOT NULL DEFAULT '',
  `message` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_messages_cfg`;
CREATE TABLE IF NOT EXISTS `j_messages_cfg` (
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `cfg_name` varchar(100) NOT NULL DEFAULT '',
  `cfg_value` varchar(255) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_modules`;
CREATE TABLE IF NOT EXISTS `j_modules` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL DEFAULT '',
  `note` varchar(255) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `position` varchar(50) NOT NULL DEFAULT '',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `module` varchar(50) DEFAULT NULL,
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `showtitle` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `params` text NOT NULL,
  `client_id` tinyint(4) NOT NULL DEFAULT '0',
  `language` char(7) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_modules_menu`;
CREATE TABLE IF NOT EXISTS `j_modules_menu` (
  `moduleid` int(11) NOT NULL DEFAULT '0',
  `menuid` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_newsfeeds`;
CREATE TABLE IF NOT EXISTS `j_newsfeeds` (
  `catid` int(11) NOT NULL DEFAULT '0',
  `id` int(10) unsigned NOT NULL,
  `name` varchar(100) NOT NULL DEFAULT '',
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `link` varchar(200) NOT NULL DEFAULT '',
  `filename` varchar(200) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `numarticles` int(10) unsigned NOT NULL DEFAULT '1',
  `cache_time` int(10) unsigned NOT NULL DEFAULT '3600',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `rtl` tinyint(4) NOT NULL DEFAULT '0',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `language` char(7) NOT NULL DEFAULT '',
  `params` text NOT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(10) unsigned NOT NULL DEFAULT '0',
  `created_by_alias` varchar(255) NOT NULL DEFAULT '',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(10) unsigned NOT NULL DEFAULT '0',
  `metakey` text NOT NULL,
  `metadesc` text NOT NULL,
  `metadata` text NOT NULL,
  `xreference` varchar(50) NOT NULL COMMENT 'A reference to enable linkages to external data sets.',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_overrider`;
CREATE TABLE IF NOT EXISTS `j_overrider` (
  `id` int(10) NOT NULL COMMENT 'Primary Key',
  `constant` varchar(255) NOT NULL,
  `string` text NOT NULL,
  `file` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_redirect_links`;
CREATE TABLE IF NOT EXISTS `j_redirect_links` (
  `id` int(10) unsigned NOT NULL,
  `old_url` varchar(255) NOT NULL,
  `new_url` varchar(255) NOT NULL,
  `referer` varchar(150) NOT NULL,
  `comment` varchar(255) NOT NULL,
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  `published` tinyint(4) NOT NULL,
  `created_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_schemas`;
CREATE TABLE IF NOT EXISTS `j_schemas` (
  `extension_id` int(11) NOT NULL,
  `version_id` varchar(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_session`;
CREATE TABLE IF NOT EXISTS `j_session` (
  `session_id` varchar(200) NOT NULL DEFAULT '',
  `client_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `guest` tinyint(4) unsigned DEFAULT '1',
  `time` varchar(14) DEFAULT '',
  `data` mediumtext,
  `userid` int(11) DEFAULT '0',
  `username` varchar(150) DEFAULT '',
  `usertype` varchar(50) DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_template_styles`;
CREATE TABLE IF NOT EXISTS `j_template_styles` (
  `id` int(10) unsigned NOT NULL,
  `template` varchar(50) NOT NULL DEFAULT '',
  `client_id` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `home` char(7) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `params` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_updates`;
CREATE TABLE IF NOT EXISTS `j_updates` (
  `update_id` int(11) NOT NULL,
  `update_site_id` int(11) DEFAULT '0',
  `extension_id` int(11) DEFAULT '0',
  `categoryid` int(11) DEFAULT '0',
  `name` varchar(100) DEFAULT '',
  `description` text NOT NULL,
  `element` varchar(100) DEFAULT '',
  `type` varchar(20) DEFAULT '',
  `folder` varchar(20) DEFAULT '',
  `client_id` tinyint(3) DEFAULT '0',
  `version` varchar(10) DEFAULT '',
  `data` text NOT NULL,
  `detailsurl` text NOT NULL,
  `infourl` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Available Updates';

DROP TABLE IF EXISTS `j_update_categories`;
CREATE TABLE IF NOT EXISTS `j_update_categories` (
  `categoryid` int(11) NOT NULL,
  `name` varchar(20) DEFAULT '',
  `description` text NOT NULL,
  `parent` int(11) DEFAULT '0',
  `updatesite` int(11) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Update Categories';

DROP TABLE IF EXISTS `j_update_sites`;
CREATE TABLE IF NOT EXISTS `j_update_sites` (
  `update_site_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT '',
  `type` varchar(20) DEFAULT '',
  `location` text NOT NULL,
  `enabled` int(11) DEFAULT '0',
  `last_check_timestamp` bigint(20) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Update Sites';

DROP TABLE IF EXISTS `j_update_sites_extensions`;
CREATE TABLE IF NOT EXISTS `j_update_sites_extensions` (
  `update_site_id` int(11) NOT NULL DEFAULT '0',
  `extension_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Links extensions to update sites';

DROP TABLE IF EXISTS `j_usergroups`;
CREATE TABLE IF NOT EXISTS `j_usergroups` (
  `id` int(10) unsigned NOT NULL COMMENT 'Primary Key',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Adjacency List Reference Id',
  `lft` int(11) NOT NULL DEFAULT '0' COMMENT 'Nested set lft.',
  `rgt` int(11) NOT NULL DEFAULT '0' COMMENT 'Nested set rgt.',
  `title` varchar(100) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_users`;
CREATE TABLE IF NOT EXISTS `j_users` (
  `id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL DEFAULT '0',
  `supplier_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `username` varchar(150) NOT NULL DEFAULT '',
  `email` varchar(100) NOT NULL DEFAULT '',
  `password` varchar(100) NOT NULL DEFAULT '',
  `usertype` varchar(25) NOT NULL DEFAULT '',
  `block` tinyint(4) NOT NULL DEFAULT '0',
  `sendEmail` tinyint(4) DEFAULT '0',
  `registerDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lastvisitDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `activation` varchar(100) NOT NULL DEFAULT '',
  `params` text NOT NULL,
  `lastResetTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Date of last password reset',
  `resetCount` int(11) NOT NULL DEFAULT '0' COMMENT 'Count of password resets since lastResetTime'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
DROP TRIGGER IF EXISTS `j_users_Trigger`;
DELIMITER $$
CREATE TRIGGER `j_users_Trigger` AFTER DELETE ON `j_users`
 FOR EACH ROW BEGIN
delete from k_des_suppliers_referents where user_id = old.id and organization_id = old.organization_id;
delete from k_suppliers_organizations_referents where user_id = old.id and organization_id = old.organization_id;
delete from k_summary_payments where user_id = old.id and organization_id = old.organization_id;
delete from k_summary_orders where user_id = old.id and organization_id = old.organization_id;
delete from k_storerooms where user_id = old.id and organization_id = old.organization_id;
delete from k_request_payments where user_id = old.id and organization_id = old.organization_id;
delete from k_carts where user_id = old.id and organization_id = old.organization_id;
delete from k_bookmarks_articles where user_id = old.id and organization_id = old.organization_id;
delete from k_cashes where user_id = old.id and organization_id = old.organization_id;
delete from j_user_notes where user_id = old.id;
delete from j_user_profiles where user_id = old.id;
delete from j_user_usergroup_map where user_id = old.id;
END
$$
DELIMITER ;

DROP TABLE IF EXISTS `j_user_notes`;
CREATE TABLE IF NOT EXISTS `j_user_notes` (
  `id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `catid` int(10) unsigned NOT NULL DEFAULT '0',
  `subject` varchar(100) NOT NULL DEFAULT '',
  `body` text NOT NULL,
  `state` tinyint(3) NOT NULL DEFAULT '0',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_user_id` int(10) unsigned NOT NULL,
  `modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `review_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_user_profiles`;
CREATE TABLE IF NOT EXISTS `j_user_profiles` (
  `user_id` int(11) NOT NULL,
  `profile_key` varchar(100) NOT NULL,
  `profile_value` text NOT NULL,
  `ordering` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Simple user profile storage table';

DROP TABLE IF EXISTS `j_user_usergroup_map`;
CREATE TABLE IF NOT EXISTS `j_user_usergroup_map` (
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Foreign Key to #__users.id',
  `group_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Foreign Key to #__usergroups.id'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_viewlevels`;
CREATE TABLE IF NOT EXISTS `j_viewlevels` (
  `id` int(10) unsigned NOT NULL COMMENT 'Primary Key',
  `title` varchar(100) NOT NULL DEFAULT '',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `rules` varchar(5120) NOT NULL COMMENT 'JSON encoded access control.'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `j_weblinks`;
CREATE TABLE IF NOT EXISTS `j_weblinks` (
  `id` int(10) unsigned NOT NULL,
  `catid` int(11) NOT NULL DEFAULT '0',
  `sid` int(11) NOT NULL DEFAULT '0',
  `title` varchar(250) NOT NULL DEFAULT '',
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `url` varchar(250) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `hits` int(11) NOT NULL DEFAULT '0',
  `state` tinyint(1) NOT NULL DEFAULT '0',
  `checked_out` int(11) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `archived` tinyint(1) NOT NULL DEFAULT '0',
  `approved` tinyint(1) NOT NULL DEFAULT '1',
  `access` int(11) NOT NULL DEFAULT '1',
  `params` text NOT NULL,
  `language` char(7) NOT NULL DEFAULT '',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(10) unsigned NOT NULL DEFAULT '0',
  `created_by_alias` varchar(255) NOT NULL DEFAULT '',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(10) unsigned NOT NULL DEFAULT '0',
  `metakey` text NOT NULL,
  `metadesc` text NOT NULL,
  `metadata` text NOT NULL,
  `featured` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Set if link is featured.',
  `xreference` varchar(50) NOT NULL COMMENT 'A reference to enable linkages to external data sets.',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_articles`;
CREATE TABLE IF NOT EXISTS `k_articles` (
  `id` int(11) unsigned NOT NULL,
  `organization_id` int(11) NOT NULL,
  `supplier_organization_id` int(11) unsigned NOT NULL,
  `prod_gas_article_id` int(11) NOT NULL DEFAULT '0' COMMENT 'promotions',
  `supplier_id` int(11) NOT NULL DEFAULT '0' COMMENT 'promotions',
  `category_article_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `codice` varchar(25) DEFAULT NULL,
  `nota` text,
  `ingredienti` text,
  `prezzo` double(11,2) NOT NULL,
  `qta` double(11,2) NOT NULL COMMENT 'qta + um = confezione',
  `um` enum('PZ','GR','HG','KG','ML','DL','LT') NOT NULL COMMENT 'qta + um = confezione',
  `um_riferimento` enum('PZ','GR','HG','KG','ML','DL','LT') NOT NULL,
  `pezzi_confezione` int(11) NOT NULL,
  `qta_minima` int(11) NOT NULL,
  `qta_massima` int(11) NOT NULL,
  `qta_minima_order` int(11) NOT NULL DEFAULT '0' COMMENT 'qta_minima rispetto a tutti gli acquisti',
  `qta_massima_order` int(11) NOT NULL COMMENT 'arrivati alla qta indicata, l ordine sull articolo sara bloccato',
  `qta_multipli` int(11) NOT NULL,
  `alert_to_qta` int(11) NOT NULL COMMENT 'arrivati alla qta indicata il sistema inviera una mail ai referenti',
  `bio` enum('N','Y') NOT NULL,
  `img1` varchar(50) DEFAULT NULL,
  `stato` enum('Y','N') NOT NULL DEFAULT 'N',
  `flag_presente_articlesorders` enum('Y','N') NOT NULL DEFAULT 'Y',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
DROP TRIGGER IF EXISTS `k_articles_Trigger`;
DELIMITER $$
CREATE TRIGGER `k_articles_Trigger` AFTER DELETE ON `k_articles`
 FOR EACH ROW BEGIN
  delete from k_articles_articles_types where article_id = old.id and organization_id = old.organization_id;
  delete from k_articles_orders where article_id = old.id and article_organization_id = old.organization_id;
  delete from k_storerooms where article_id = old.id and organization_id = old.organization_id;
  delete from k_carts where article_id = old.id and article_organization_id = old.organization_id;
  delete from k_carts_splits where article_id = old.id and article_organization_id = old.organization_id;
  delete from k_bookmarks_articles where article_id = old.id and organization_id = old.organization_id;
  END
$$
DELIMITER ;

DROP TABLE IF EXISTS `k_articles_articles_types`;
CREATE TABLE IF NOT EXISTS `k_articles_articles_types` (
  `organization_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `article_type_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_articles_orders`;
CREATE TABLE IF NOT EXISTS `k_articles_orders` (
  `organization_id` int(11) NOT NULL,
  `order_id` int(11) unsigned NOT NULL,
  `article_organization_id` int(11) NOT NULL COMMENT 'se ordine DES l''articolo puo'' riferirsi ad un''altro gas',
  `article_id` int(11) unsigned NOT NULL,
  `qta_cart` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `prezzo` double(11,2) NOT NULL,
  `pezzi_confezione` int(11) NOT NULL,
  `qta_minima` int(11) NOT NULL,
  `qta_massima` int(11) NOT NULL,
  `qta_minima_order` int(11) NOT NULL DEFAULT '0' COMMENT 'qta_minima rispetto a tutti gli acquisti',
  `qta_massima_order` int(11) NOT NULL COMMENT 'arrivati alla qta indicata, l ordine sull articolo sara bloccato',
  `qta_multipli` int(11) NOT NULL,
  `alert_to_qta` int(11) NOT NULL COMMENT 'arrivati alla qta indicata il sistema inviera una mail ai referenti',
  `send_mail` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'se N invia mail al referente, ex QTAMAX',
  `flag_bookmarks` enum('N','Y') NOT NULL DEFAULT 'N' COMMENT 'se Y e'' gia'' stato processato se e'' tra preferiti degli utenti',
  `stato` enum('Y','N','LOCK','QTAMAXORDER') NOT NULL DEFAULT 'Y',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
DROP TRIGGER IF EXISTS `articles_orders_Trigger`;
DELIMITER $$
CREATE TRIGGER `articles_orders_Trigger` AFTER DELETE ON `k_articles_orders`
 FOR EACH ROW BEGIN
 delete from k_carts where order_id = old.order_id and article_id = old.article_id  and article_organization_id = old.article_organization_id and organization_id = old.organization_id;
 END
$$
DELIMITER ;

DROP TABLE IF EXISTS `k_articles_types`;
CREATE TABLE IF NOT EXISTS `k_articles_types` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `label` varchar(75) NOT NULL,
  `descrizione` varchar(256) DEFAULT NULL,
  `sort` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_backup_articles_orders`;
CREATE TABLE IF NOT EXISTS `k_backup_articles_orders` (
  `organization_id` int(11) NOT NULL,
  `order_id` int(11) unsigned NOT NULL,
  `article_organization_id` int(11) NOT NULL,
  `article_id` int(11) unsigned NOT NULL,
  `qta_cart` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `prezzo` double(11,2) NOT NULL,
  `pezzi_confezione` int(11) NOT NULL,
  `qta_minima` int(11) NOT NULL,
  `qta_massima` int(11) NOT NULL,
  `qta_minima_order` int(11) NOT NULL DEFAULT '0',
  `qta_massima_order` int(11) NOT NULL,
  `qta_multipli` int(11) NOT NULL,
  `alert_to_qta` int(11) NOT NULL,
  `send_mail` enum('Y','N') NOT NULL DEFAULT 'N',
  `flag_bookmarks` enum('N','Y') NOT NULL DEFAULT 'N',
  `stato` enum('Y','N','LOCK','QTAMAXORDER') NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_backup_carts`;
CREATE TABLE IF NOT EXISTS `k_backup_carts` (
  `organization_id` int(11) NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `order_id` int(11) unsigned NOT NULL,
  `article_organization_id` int(11) NOT NULL,
  `article_id` int(11) unsigned NOT NULL,
  `qta` int(11) NOT NULL,
  `deleteToReferent` enum('Y','N') NOT NULL DEFAULT 'N',
  `qta_forzato` int(11) NOT NULL,
  `importo_forzato` double(11,2) unsigned NOT NULL,
  `nota` text,
  `inStoreroom` enum('Y','N') NOT NULL DEFAULT 'N',
  `stato` enum('Y','N') NOT NULL,
  `created` datetime DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_backup_orders_articles_orders`;
CREATE TABLE IF NOT EXISTS `k_backup_orders_articles_orders` (
  `organization_id` int(11) NOT NULL,
  `order_id` int(11) unsigned NOT NULL,
  `article_organization_id` int(11) NOT NULL,
  `article_id` int(11) unsigned NOT NULL,
  `qta_cart` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `prezzo` double(11,2) NOT NULL,
  `pezzi_confezione` int(11) NOT NULL,
  `qta_minima` int(11) NOT NULL,
  `qta_massima` int(11) NOT NULL,
  `qta_minima_order` int(11) NOT NULL DEFAULT '0',
  `qta_massima_order` int(11) NOT NULL,
  `qta_multipli` int(11) NOT NULL,
  `alert_to_qta` int(11) NOT NULL,
  `send_mail` enum('Y','N') NOT NULL DEFAULT 'N',
  `flag_bookmarks` enum('N','Y') NOT NULL DEFAULT 'N',
  `stato` enum('Y','N','LOCK','QTAMAXORDER') NOT NULL DEFAULT 'Y',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
DROP TRIGGER IF EXISTS `k_backup_orders_articles_orders_Trigger`;
DELIMITER $$
CREATE TRIGGER `k_backup_orders_articles_orders_Trigger` AFTER DELETE ON `k_backup_orders_articles_orders`
 FOR EACH ROW BEGIN
 delete from k_backup_orders_carts where order_id = old.order_id and article_id = old.article_id  and article_organization_id = old.article_organization_id and organization_id = old.organization_id;
 END
$$
DELIMITER ;

DROP TABLE IF EXISTS `k_backup_orders_carts`;
CREATE TABLE IF NOT EXISTS `k_backup_orders_carts` (
  `organization_id` int(11) NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `order_id` int(11) unsigned NOT NULL,
  `article_organization_id` int(11) NOT NULL,
  `article_id` int(11) unsigned NOT NULL,
  `qta` int(11) NOT NULL,
  `deleteToReferent` enum('Y','N') NOT NULL DEFAULT 'N',
  `qta_forzato` int(11) NOT NULL,
  `importo_forzato` double(11,2) unsigned NOT NULL,
  `nota` text,
  `inStoreroom` enum('Y','N') NOT NULL DEFAULT 'N',
  `stato` enum('Y','N') NOT NULL DEFAULT 'Y',
  `created` datetime DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_backup_orders_orders`;
CREATE TABLE IF NOT EXISTS `k_backup_orders_orders` (
  `id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `supplier_organization_id` int(11) unsigned NOT NULL,
  `delivery_id` int(11) unsigned NOT NULL,
  `prod_gas_promotion_id` int(11) NOT NULL DEFAULT '0',
  `des_order_d` int(11) NOT NULL DEFAULT '0',
  `data_inizio` date NOT NULL,
  `data_fine` date NOT NULL,
  `data_fine_validation` date NOT NULL,
  `data_incoming_order` date NOT NULL,
  `nota` text,
  `hasTrasport` enum('Y','N') NOT NULL DEFAULT 'N',
  `trasport_type` enum('QTA','WEIGHT','USERS') DEFAULT NULL,
  `trasport` double(11,2) NOT NULL,
  `hasCostMore` enum('Y','N') NOT NULL DEFAULT 'N',
  `cost_more_type` enum('QTA','WEIGHT','USERS') NOT NULL,
  `cost_more` double(11,2) NOT NULL,
  `hasCostLess` enum('Y','N') NOT NULL DEFAULT 'N',
  `cost_less_type` enum('QTA','WEIGHT','USERS') NOT NULL,
  `cost_less` double(11,2) NOT NULL,
  `typeGest` enum('AGGREGATE','SPLIT') DEFAULT NULL,
  `state_code` varchar(50) NOT NULL,
  `mail_open_send` enum('Y','N') NOT NULL DEFAULT 'N',
  `mail_open_data` datetime NOT NULL,
  `mail_close_data` datetime NOT NULL,
  `mail_open_testo` text NOT NULL,
  `type_draw` enum('SIMPLE','COMPLETE','PROMOTION') NOT NULL DEFAULT 'SIMPLE',
  `tot_importo` double(11,2) NOT NULL,
  `qta_massima` int(11) NOT NULL,
  `qta_massima_um` enum('PZ','KG','LT') DEFAULT NULL,
  `send_mail_qta_massima` enum('Y','N') NOT NULL DEFAULT 'Y',
  `importo_massimo` int(11) NOT NULL,
  `send_mail_importo_massimo` enum('Y','N') NOT NULL DEFAULT 'Y',
  `tesoriere_nota` text NOT NULL,
  `tesoriere_fattura_importo` double(11,2) NOT NULL,
  `tesoriere_doc1` varchar(50) NOT NULL,
  `tesoriere_data_pay` date NOT NULL,
  `tesoriere_importo_pay` double(11,2) NOT NULL,
  `tesoriere_stato_pay` enum('Y','N') NOT NULL DEFAULT 'N',
  `tesoriere_sorce` enum('REFERENTE','CASSIERE') NOT NULL DEFAULT 'REFERENTE',
  `isVisibleFrontEnd` enum('Y','N') NOT NULL DEFAULT 'Y',
  `isVisibleBackOffice` enum('Y','N') NOT NULL DEFAULT 'Y',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
DROP TRIGGER IF EXISTS `k_backup_orders_orders_Trigger`;
DELIMITER $$
CREATE TRIGGER `k_backup_orders_orders_Trigger` AFTER DELETE ON `k_backup_orders_orders`
 FOR EACH ROW BEGIN
delete from k_backup_orders_articles_orders where order_id = old.id and organization_id = old.organization_id;
 END
$$
DELIMITER ;

DROP TABLE IF EXISTS `k_bookmarks_articles`;
CREATE TABLE IF NOT EXISTS `k_bookmarks_articles` (
  `id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `supplier_organization_id` int(11) unsigned NOT NULL,
  `article_organization_id` int(11) NOT NULL COMMENT 'se ordine DES l''articolo puo'' riferirsi ad un''altro gas',
  `article_id` int(11) unsigned NOT NULL,
  `qta` int(11) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_bookmarks_mails`;
CREATE TABLE IF NOT EXISTS `k_bookmarks_mails` (
  `id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `supplier_organization_id` int(11) NOT NULL,
  `order_open` enum('Y','N') NOT NULL DEFAULT 'Y',
  `order_close` enum('Y','N') NOT NULL DEFAULT 'Y',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `k_carts`;
CREATE TABLE IF NOT EXISTS `k_carts` (
  `organization_id` int(11) NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `order_id` int(11) unsigned NOT NULL,
  `article_organization_id` int(11) NOT NULL COMMENT 'se ordine DES l''articolo puo'' riferirsi ad un''altro gas',
  `article_id` int(11) unsigned NOT NULL,
  `qta` int(11) NOT NULL COMMENT 'valore di default 0 perche'' se faccio acquisto con INSERT da backoffice il valore va in qta_finale',
  `deleteToReferent` enum('Y','N') NOT NULL DEFAULT 'N',
  `qta_forzato` int(11) NOT NULL,
  `importo_forzato` double(11,2) unsigned NOT NULL,
  `nota` text,
  `inStoreroom` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'se Y e'' gia'' stato copiato in dispensa',
  `stato` enum('Y','N') NOT NULL DEFAULT 'Y',
  `created` datetime DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'data utilizzata per avere l''ultimo inserito'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
DROP TRIGGER IF EXISTS `k_carts_Trigger`;
DELIMITER $$
CREATE TRIGGER `k_carts_Trigger` AFTER DELETE ON `k_carts`
 FOR EACH ROW BEGIN
delete from k_carts_splits where organization_id = old.organization_id and user_id = old.user_id and order_id = old.order_id and article_organization_id = old.article_organization_id and article_id = old.article_id ;
 END
$$
DELIMITER ;

DROP TABLE IF EXISTS `k_carts_splits`;
CREATE TABLE IF NOT EXISTS `k_carts_splits` (
  `organization_id` int(11) NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `order_id` int(11) unsigned NOT NULL,
  `article_organization_id` int(11) NOT NULL COMMENT 'se ordine DES l''articolo puo'' riferirsi ad un''altro gas',
  `article_id` int(11) unsigned NOT NULL,
  `num_split` int(11) unsigned NOT NULL,
  `importo_forzato` double(11,2) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_cashes`;
CREATE TABLE IF NOT EXISTS `k_cashes` (
  `id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT 'se 0: voce di spesa generica',
  `nota` text,
  `importo` double(11,2) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
DROP TRIGGER IF EXISTS `k_cashes_Trigger`;
DELIMITER $$
CREATE TRIGGER `k_cashes_Trigger` AFTER DELETE ON `k_cashes`
 FOR EACH ROW BEGIN
delete from k_cashes_histories where organization_id = old.organization_id and cash_id = old.id;
 END
$$
DELIMITER ;

DROP TABLE IF EXISTS `k_cashes_histories`;
CREATE TABLE IF NOT EXISTS `k_cashes_histories` (
  `id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `cash_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nota` text,
  `importo` double(11,2) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_categories`;
CREATE TABLE IF NOT EXISTS `k_categories` (
  `id` int(10) unsigned NOT NULL,
  `parent_id` int(10) DEFAULT NULL,
  `lft` int(10) DEFAULT NULL,
  `rght` int(10) DEFAULT NULL,
  `name` varchar(255) DEFAULT '',
  `description` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `k_categories_articles`;
CREATE TABLE IF NOT EXISTS `k_categories_articles` (
  `id` int(10) unsigned NOT NULL,
  `organization_id` int(11) NOT NULL,
  `parent_id` int(10) DEFAULT NULL,
  `lft` int(10) DEFAULT NULL,
  `rght` int(10) DEFAULT NULL,
  `name` varchar(255) DEFAULT '',
  `description` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
DROP TRIGGER IF EXISTS `k_categories_articles_Trigger`;
DELIMITER $$
CREATE TRIGGER `k_categories_articles_Trigger` AFTER DELETE ON `k_categories_articles`
 FOR EACH ROW BEGIN 
update k_articles set category_article_id = 0 where category_article_id = old.id and organization_id = organization_id; 
 	END
$$
DELIMITER ;

DROP TABLE IF EXISTS `k_categories_suppliers`;
CREATE TABLE IF NOT EXISTS `k_categories_suppliers` (
  `id` int(10) unsigned NOT NULL,
  `parent_id` int(10) DEFAULT NULL,
  `lft` int(10) DEFAULT NULL,
  `rght` int(10) DEFAULT NULL,
  `name` varchar(255) DEFAULT '',
  `description` varchar(255) DEFAULT NULL,
  `j_category_id` int(11) NOT NULL COMMENT 'id riferito alla categoria #__categories'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
DROP TRIGGER IF EXISTS `k_categories_suppliers_Trigger`;
DELIMITER $$
CREATE TRIGGER `k_categories_suppliers_Trigger` AFTER DELETE ON `k_categories_suppliers`
 FOR EACH ROW BEGIN 
update k_suppliers set category_supplier_id = 0 where category_supplier_id = old.id; 
update k_suppliers_organizations set category_supplier_id = 0 where category_supplier_id = old.id; 
 	END
$$
DELIMITER ;

DROP TABLE IF EXISTS `k_counters`;
CREATE TABLE IF NOT EXISTS `k_counters` (
  `id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `table` varchar(25) NOT NULL,
  `counter` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_deliveries`;
CREATE TABLE IF NOT EXISTS `k_deliveries` (
  `id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `luogo` varchar(255) NOT NULL,
  `data` date NOT NULL,
  `orario_da` time NOT NULL,
  `orario_a` time NOT NULL,
  `nota` text,
  `nota_evidenza` enum('NO','MESSAGE','NOTICE','ALERT') NOT NULL,
  `isToStoreroom` enum('Y','N') NOT NULL COMMENT 'se Y, un articolo in dispensa posso associarlo alla consegna',
  `isToStoreroomPay` enum('Y','N') NOT NULL,
  `stato_elaborazione` enum('OPEN','CLOSE') NOT NULL COMMENT 'se CLOSE in backoffice non le vedo + da elaborare',
  `isVisibleFrontEnd` enum('Y','N') NOT NULL DEFAULT 'Y',
  `isVisibleBackOffice` enum('Y','N') NOT NULL DEFAULT 'Y',
  `sys` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'se Y e'' un elemento di sistema e non puo'' essere eliminato',
  `gcalendar_event_id` varchar(50) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
DROP TRIGGER IF EXISTS `k_deliveries_Trigger`;
DELIMITER $$
CREATE TRIGGER `k_deliveries_Trigger` AFTER DELETE ON `k_deliveries`
 FOR EACH ROW BEGIN
delete from k_orders where delivery_id = old.id and organization_id = old.organization_id;
delete from k_storerooms where delivery_id = old.id and organization_id = old.organization_id;
delete from k_summary_orders where delivery_id = old.id and organization_id = old.organization_id;
delete from k_request_payments_storerooms where delivery_id = old.id and organization_id = old.organization_id;
delete from k_backup_orders_orders where delivery_id = old.id and organization_id = old.organization_id;
 END
$$
DELIMITER ;

DROP TABLE IF EXISTS `k_des`;
CREATE TABLE IF NOT EXISTS `k_des` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
DROP TRIGGER IF EXISTS `k_des_Trigger`;
DELIMITER $$
CREATE TRIGGER `k_des_Trigger` AFTER DELETE ON `k_des`
 FOR EACH ROW BEGIN
delete from k_des_organizations where des_id = old.id;
delete from k_des_suppliers where des_id = old.id;
 END
$$
DELIMITER ;

DROP TABLE IF EXISTS `k_des_orders`;
CREATE TABLE IF NOT EXISTS `k_des_orders` (
  `id` int(11) NOT NULL,
  `des_id` int(11) NOT NULL,
  `des_supplier_id` int(11) NOT NULL,
  `luogo` varchar(255) NOT NULL,
  `nota` text NOT NULL,
  `nota_evidenza` enum('NO','MESSAGE','NOTICE','ALERT') NOT NULL,
  `data_fine_max` date NOT NULL,
  `hasTrasport` enum('Y','N') NOT NULL DEFAULT 'N',
  `trasport` double(11,2) NOT NULL,
  `hasCostMore` enum('Y','N') NOT NULL DEFAULT 'N',
  `cost_more` double(11,2) NOT NULL,
  `hasCostLess` enum('Y','N') NOT NULL DEFAULT 'N',
  `cost_less` double(11,2) NOT NULL,
  `state_code` varchar(50) NOT NULL DEFAULT 'OPEN',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `organization_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
DROP TRIGGER IF EXISTS `k_des_orders_Trigger`;
DELIMITER $$
CREATE TRIGGER `k_des_orders_Trigger` AFTER DELETE ON `k_des_orders`
 FOR EACH ROW BEGIN
delete from k_des_orders_organizations where des_order_id = old.id;
 END
$$
DELIMITER ;

DROP TABLE IF EXISTS `k_des_orders_actions`;
CREATE TABLE IF NOT EXISTS `k_des_orders_actions` (
  `id` int(11) NOT NULL,
  `controller` varchar(50) NOT NULL,
  `action` varchar(50) NOT NULL,
  `permission` varchar(512) NOT NULL,
  `permission_or` varchar(512) NOT NULL,
  `query_string` varchar(100) NOT NULL,
  `flag_menu` enum('Y','N') DEFAULT 'N' COMMENT 'se Y gestisco il menu del referente, titolare',
  `label` varchar(75) NOT NULL,
  `label_more` varchar(25) NOT NULL,
  `css_class` varchar(50) NOT NULL,
  `img` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_des_orders_organizations`;
CREATE TABLE IF NOT EXISTS `k_des_orders_organizations` (
  `id` int(11) NOT NULL,
  `des_id` int(11) NOT NULL,
  `des_order_id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `luogo` varchar(225) NOT NULL,
  `data` date NOT NULL,
  `orario` time NOT NULL,
  `contatto_nominativo` varchar(150) NOT NULL,
  `contatto_telefono` varchar(20) NOT NULL,
  `contatto_mail` varchar(100) NOT NULL,
  `nota` text NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_des_organizations`;
CREATE TABLE IF NOT EXISTS `k_des_organizations` (
  `id` int(11) NOT NULL,
  `des_id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_des_suppliers`;
CREATE TABLE IF NOT EXISTS `k_des_suppliers` (
  `id` int(11) NOT NULL,
  `des_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `own_organization_id` int(11) NOT NULL COMMENT 'organization che gestisce gli ordini del produttore',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
DROP TRIGGER IF EXISTS `k_des_suppliers_Trigger`;
DELIMITER $$
CREATE TRIGGER `k_des_suppliers_Trigger` AFTER DELETE ON `k_des_suppliers`
 FOR EACH ROW BEGIN
delete from k_des_orders where des_supplier_id = old.id;
 END
$$
DELIMITER ;

DROP TABLE IF EXISTS `k_des_suppliers_referents`;
CREATE TABLE IF NOT EXISTS `k_des_suppliers_referents` (
  `des_id` int(11) NOT NULL,
  `des_supplier_id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL COMMENT 'join con user_usergroup_map, usergroups',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_events`;
CREATE TABLE IF NOT EXISTS `k_events` (
  `id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `event_type_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT '0',
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `nota` text COLLATE utf8_unicode_ci NOT NULL,
  `start` datetime NOT NULL,
  `end` datetime NOT NULL,
  `date_alert_mail` date DEFAULT NULL COMMENT 'quando inviare la mail ai gasisti',
  `date_alert_fe` date DEFAULT NULL,
  `all_day` tinyint(1) NOT NULL DEFAULT '1',
  `status` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Scheduled',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `isVisibleFrontEnd` enum('Y','N') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Y',
  `created` date DEFAULT NULL,
  `modified` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `k_events_users`;
CREATE TABLE IF NOT EXISTS `k_events_users` (
  `organization_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `k_event_types`;
CREATE TABLE IF NOT EXISTS `k_event_types` (
  `id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `name` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `color` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `k_loops_deliveries`;
CREATE TABLE IF NOT EXISTS `k_loops_deliveries` (
  `id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `luogo` varchar(156) NOT NULL,
  `orario_da` time NOT NULL,
  `orario_a` time NOT NULL,
  `nota` text,
  `nota_evidenza` enum('NO','MESSAGE','NOTICE','ALERT') NOT NULL,
  `data_master` date NOT NULL COMMENT 'data per la ricorsione',
  `data_master_reale` date NOT NULL COMMENT 'data che compare',
  `data_copy` date NOT NULL,
  `data_copy_reale` date NOT NULL COMMENT 'data della consegna copiata, cosi permetto allo user di modificarla senza cambiare la ricorsione',
  `user_id` int(11) unsigned NOT NULL,
  `flag_send_mail` enum('Y','N') DEFAULT 'N',
  `rules` text,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_mails`;
CREATE TABLE IF NOT EXISTS `k_mails` (
  `id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `mittente` varchar(50) NOT NULL,
  `dest_options` enum('ORGANIZATIONS','USERS_CART','USERS','REFERENTI','SUPPLIERS','DES') NOT NULL,
  `dest_options_qta` enum('ALL','SOME') NOT NULL,
  `dest_ids` text NOT NULL,
  `subject` varchar(256) NOT NULL,
  `body` text NOT NULL,
  `allegato` varchar(256) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_monitoring_orders`;
CREATE TABLE IF NOT EXISTS `k_monitoring_orders` (
  `id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `order_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_monitoring_suppliers_organizations`;
CREATE TABLE IF NOT EXISTS `k_monitoring_suppliers_organizations` (
  `id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `supplier_organization_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `mail_order_data_fine` enum('Y','N') NOT NULL DEFAULT 'N',
  `mail_order_close` enum('Y','N') DEFAULT 'N',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_msgs`;
CREATE TABLE IF NOT EXISTS `k_msgs` (
  `id` int(11) NOT NULL,
  `template_id` int(11) NOT NULL DEFAULT '0',
  `organization_id` int(11) DEFAULT '0',
  `name` varchar(75) NOT NULL,
  `testo` text NOT NULL,
  `flag_attivo` enum('Y','N') NOT NULL DEFAULT 'Y',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_orders`;
CREATE TABLE IF NOT EXISTS `k_orders` (
  `id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `supplier_organization_id` int(11) unsigned NOT NULL,
  `delivery_id` int(11) unsigned NOT NULL,
  `prod_gas_promotion_id` int(11) NOT NULL DEFAULT '0',
  `des_order_id` int(11) NOT NULL DEFAULT '0',
  `data_inizio` date NOT NULL,
  `data_fine` date NOT NULL,
  `data_fine_validation` date NOT NULL,
  `data_incoming_order` date NOT NULL,
  `nota` text,
  `hasTrasport` enum('Y','N') NOT NULL DEFAULT 'N',
  `trasport_type` enum('QTA','WEIGHT','USERS') DEFAULT NULL,
  `trasport` double(11,2) NOT NULL,
  `hasCostMore` enum('Y','N') NOT NULL DEFAULT 'N',
  `cost_more_type` enum('QTA','WEIGHT','USERS') NOT NULL,
  `cost_more` double(11,2) NOT NULL,
  `hasCostLess` enum('Y','N') NOT NULL DEFAULT 'N',
  `cost_less_type` enum('QTA','WEIGHT','USERS') NOT NULL,
  `cost_less` double(11,2) NOT NULL,
  `typeGest` enum('AGGREGATE','SPLIT') DEFAULT NULL COMMENT 'tipologia di gestione a POST delivery',
  `state_code` varchar(50) NOT NULL,
  `mail_open_send` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'se Y un cron invia la mail',
  `mail_open_data` datetime NOT NULL COMMENT 'data di invio della mail per l''apertura ordine',
  `mail_close_data` datetime NOT NULL COMMENT 'data di invio della mail per la chiusura ordine',
  `mail_open_testo` text NOT NULL,
  `type_draw` enum('SIMPLE','COMPLETE','PROMOTION') NOT NULL DEFAULT 'SIMPLE' COMMENT 'definisce come visualizzare l''ecommerce',
  `tot_importo` double(11,2) NOT NULL COMMENT 'totale importo degli acquisti degli utenti',
  `qta_massima` int(11) NOT NULL COMMENT 'indica qta max di tutti gli acquisti',
  `qta_massima_um` enum('PZ','KG','LT') DEFAULT NULL COMMENT 'indica qta max di tutti gli acquisti',
  `send_mail_qta_massima` enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'se Y invia mail al referente per qta_massima',
  `importo_massimo` int(11) NOT NULL COMMENT 'indica importo max di tutti gli acquisti',
  `send_mail_importo_massimo` enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'se Y invia mail al referente per importo_massimo',
  `tesoriere_nota` text NOT NULL,
  `tesoriere_fattura_importo` double(11,2) NOT NULL,
  `tesoriere_doc1` varchar(100) NOT NULL,
  `tesoriere_data_pay` date NOT NULL,
  `tesoriere_importo_pay` double(11,2) NOT NULL,
  `tesoriere_stato_pay` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'se Y il produttore e'' stato pagato, ctrl per delivery.stato_elaborazione = CLOSE',
  `tesoriere_sorce` enum('REFERENTE','CASSIERE') NOT NULL DEFAULT 'REFERENTE' COMMENT 'chi ha inviato l''ordine al tesoriere',
  `isVisibleFrontEnd` enum('Y','N') NOT NULL DEFAULT 'Y',
  `isVisibleBackOffice` enum('Y','N') NOT NULL DEFAULT 'Y',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
DROP TRIGGER IF EXISTS `k_orders_Trigger`;
DELIMITER $$
CREATE TRIGGER `k_orders_Trigger` AFTER DELETE ON `k_orders`
 FOR EACH ROW BEGIN
delete from k_summary_orders where order_id = old.id and organization_id = old.organization_id;
delete from k_summary_order_trasports where order_id = old.id and organization_id = old.organization_id;
delete from k_summary_order_cost_lesses where order_id = old.id and organization_id = old.organization_id;
delete from k_summary_order_cost_mores where order_id = old.id and organization_id = old.organization_id;
delete from k_articles_orders where order_id = old.id and organization_id = old.organization_id;
delete from k_request_payments_orders where order_id = old.id and organization_id = old.organization_id;
delete from k_monitoring_orders where order_id = old.id and organization_id = old.organization_id;
delete from k_des_orders_organizations where order_id = old.id and organization_id = old.organization_id;
delete from k_prod_gas_promotions_organizations where order_id = old.id and organization_id = old.organization_id;
 END
$$
DELIMITER ;

DROP TABLE IF EXISTS `k_orders_actions`;
CREATE TABLE IF NOT EXISTS `k_orders_actions` (
  `id` int(11) NOT NULL,
  `controller` varchar(50) NOT NULL,
  `action` varchar(50) NOT NULL,
  `permission` varchar(512) NOT NULL,
  `permission_or` varchar(512) NOT NULL,
  `query_string` varchar(100) NOT NULL,
  `flag_menu` enum('Y','N') DEFAULT 'N' COMMENT 'se Y gestisco il menu del referente, cassiere o tesoriere',
  `label` varchar(75) NOT NULL,
  `label_more` varchar(25) NOT NULL,
  `css_class` varchar(50) NOT NULL,
  `img` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_organizations`;
CREATE TABLE IF NOT EXISTS `k_organizations` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `descrizione` text NOT NULL,
  `indirizzo` varchar(50) CHARACTER SET latin1 NOT NULL,
  `localita` varchar(50) CHARACTER SET latin1 NOT NULL,
  `cap` varchar(5) CHARACTER SET latin1 NOT NULL,
  `provincia` char(2) CHARACTER SET latin1 NOT NULL,
  `telefono` varchar(20) CHARACTER SET latin1 DEFAULT NULL,
  `telefono2` varchar(20) CHARACTER SET latin1 DEFAULT NULL,
  `mail` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  `www` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  `www2` varchar(100) CHARACTER SET latin1 NOT NULL COMMENT 'sito personale',
  `sede_logistica_1` varchar(256) CHARACTER SET latin1 NOT NULL,
  `sede_logistica_2` varchar(256) CHARACTER SET latin1 NOT NULL,
  `sede_logistica_3` varchar(256) CHARACTER SET latin1 NOT NULL,
  `sede_logistica_4` varchar(256) CHARACTER SET latin1 NOT NULL,
  `cf` varchar(16) CHARACTER SET latin1 DEFAULT NULL,
  `piva` varchar(11) CHARACTER SET latin1 DEFAULT NULL,
  `banca` varchar(250) CHARACTER SET latin1 DEFAULT NULL,
  `banca_iban` varchar(27) CHARACTER SET latin1 NOT NULL,
  `lat` varchar(15) CHARACTER SET latin1 NOT NULL,
  `lng` varchar(15) CHARACTER SET latin1 NOT NULL,
  `img1` varchar(15) CHARACTER SET latin1 NOT NULL COMMENT 'logo = all''id dell''articolo',
  `template_id` int(11) NOT NULL,
  `j_group_registred` int(11) NOT NULL COMMENT 'id del gruppo di joomla per l''accesso alle pagine dell''organizzazione del frontend dopo la login',
  `j_page_category_id` int(11) NOT NULL DEFAULT '0' COMMENT 'indica la categoria delle pagine di un GAS, ex PagesCavagnetta ',
  `j_seo` varchar(25) CHARACTER SET latin1 NOT NULL COMMENT 'suffisso x gli url SEO',
  `gcalendar_id` varchar(100) DEFAULT NULL,
  `type` enum('GAS','PRODGAS','PROD') CHARACTER SET latin1 NOT NULL,
  `paramsConfig` text CHARACTER SET latin1 NOT NULL,
  `paramsFields` text CHARACTER SET latin1 NOT NULL,
  `paramsPay` text CHARACTER SET latin1 NOT NULL,
  `stato` enum('Y','N') CHARACTER SET latin1 NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
DROP TRIGGER IF EXISTS `k_organizations_Trigger`;
DELIMITER $$
CREATE TRIGGER `k_organizations_Trigger` AFTER DELETE ON `k_organizations`
 FOR EACH ROW BEGIN
delete from k_suppliers_organizations where organization_id = old.id;
delete from j_users where organization_id = old.id;
delete from k_deliveries where organization_id = old.id;
 END
$$
DELIMITER ;

DROP TABLE IF EXISTS `k_organizations_pays`;
CREATE TABLE IF NOT EXISTS `k_organizations_pays` (
  `id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `year` varchar(4) NOT NULL,
  `data_pay` date NOT NULL,
  `beneficiario_pay` varchar(50) NOT NULL,
  `tot_users` int(11) unsigned NOT NULL,
  `tot_orders` int(11) NOT NULL,
  `tot_suppliers_organizations` int(11) NOT NULL DEFAULT '0',
  `tot_articles` int(11) NOT NULL,
  `importo` double(11,2) NOT NULL,
  `type_pay` enum('RITENUTA','RICEVUTA') NOT NULL DEFAULT 'RICEVUTA',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `k_pdf_carts`;
CREATE TABLE IF NOT EXISTS `k_pdf_carts` (
  `id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `uuid` varchar(25) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `delivery_id` int(11) NOT NULL DEFAULT '0',
  `delivery_data` date NOT NULL,
  `delivery_luogo` varchar(225) NOT NULL,
  `delivery_importo` float(11,2) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
DROP TRIGGER IF EXISTS `k_pdf_carts_trigger`;
DELIMITER $$
CREATE TRIGGER `k_pdf_carts_trigger` AFTER DELETE ON `k_pdf_carts`
 FOR EACH ROW delete from k_pdf_carts_orders where pdf_cart_id = old.id and organization_id = old.organization_id
$$
DELIMITER ;

DROP TABLE IF EXISTS `k_pdf_carts_orders`;
CREATE TABLE IF NOT EXISTS `k_pdf_carts_orders` (
  `id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `pdf_cart_id` int(11) NOT NULL DEFAULT '0',
  `supplier_id` int(11) NOT NULL DEFAULT '0',
  `supplier_img1` varchar(50) DEFAULT NULL,
  `supplier_organizations_id` int(11) NOT NULL DEFAULT '0',
  `supplier_organizations_name` varchar(100) DEFAULT NULL,
  `order_importo` float(11,2) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_prod_carts`;
CREATE TABLE IF NOT EXISTS `k_prod_carts` (
  `organization_id` int(11) NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `prod_delivery_id` int(11) unsigned NOT NULL,
  `article_organization_id` int(11) unsigned NOT NULL,
  `article_id` int(11) unsigned NOT NULL,
  `qta` int(11) NOT NULL COMMENT 'valore di default 0 perche'' se faccio acquisto con INSERT da backoffice il valore va in qta_finale',
  `deleteToReferent` enum('Y','N') NOT NULL DEFAULT 'N',
  `nota` text,
  `stato` enum('Y','N') NOT NULL,
  `created` datetime DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'data utilizzata per avere l''ultimo inserito'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_prod_carts_splits`;
CREATE TABLE IF NOT EXISTS `k_prod_carts_splits` (
  `organization_id` int(11) NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `prod_delivery_id` int(11) unsigned NOT NULL,
  `article_organization_id` int(11) NOT NULL COMMENT 'se ordine DES l''articolo puo'' riferirsi ad un''altro gas',
  `article_id` int(11) unsigned NOT NULL,
  `num_split` int(11) unsigned NOT NULL,
  `importo_forzato` double(11,2) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_prod_deliveries`;
CREATE TABLE IF NOT EXISTS `k_prod_deliveries` (
  `id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `supplier_organization_id` int(11) NOT NULL,
  `prod_group_id` int(11) NOT NULL,
  `name` varchar(256) NOT NULL,
  `data_inizio` date NOT NULL,
  `data_fine` date NOT NULL,
  `prod_delivery_state_id` int(11) NOT NULL,
  `ricorrenza_num` int(2) NOT NULL DEFAULT '0',
  `ricorrenza_type` enum('','DAYS','WEEKS','MONTHS') NOT NULL DEFAULT '',
  `type_draw` enum('SIMPLE','COMPLETE') NOT NULL DEFAULT 'SIMPLE',
  `stato_elaborazione` enum('OPEN','CLOSE') NOT NULL DEFAULT 'OPEN',
  `isVisibleFrontEnd` enum('Y','N') NOT NULL,
  `isVisibleBackOffice` enum('Y','N') NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_prod_deliveries_articles`;
CREATE TABLE IF NOT EXISTS `k_prod_deliveries_articles` (
  `organization_id` int(11) NOT NULL,
  `prod_delivery_id` int(11) unsigned NOT NULL,
  `article_organization_id` int(11) NOT NULL COMMENT 'se ordine DES l''articolo puo'' riferirsi ad un''altro gas',
  `article_id` int(11) unsigned NOT NULL,
  `qta_cart` int(11) NOT NULL,
  `prezzo` double(11,2) NOT NULL,
  `pezzi_confezione` int(11) NOT NULL,
  `qta_minima` int(11) NOT NULL,
  `qta_massima` int(11) NOT NULL,
  `qta_minima_order` int(11) NOT NULL DEFAULT '0' COMMENT 'qta_minima rispetto a tutti gli acquisti',
  `qta_massima_order` int(11) NOT NULL COMMENT 'arrivati alla qta indicata, l ordine sull articolo sara bloccato',
  `qta_multipli` int(11) NOT NULL,
  `alert_to_qta` int(11) NOT NULL COMMENT 'arrivati alla qta indicata il sistema inviera una mail ai referenti',
  `stato` enum('Y','N','LOCK','QTAMAX') NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_prod_deliveries_states`;
CREATE TABLE IF NOT EXISTS `k_prod_deliveries_states` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `label` varchar(75) NOT NULL,
  `intro` varchar(256) NOT NULL COMMENT 'utilizzato peri TITLE delle immagini',
  `descrizione` text NOT NULL,
  `flag_produttore` enum('Y','N') DEFAULT NULL,
  `sort` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `k_prod_gas_articles`;
CREATE TABLE IF NOT EXISTS `k_prod_gas_articles` (
  `id` int(11) unsigned NOT NULL,
  `supplier_id` int(11) unsigned NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `codice` varchar(25) DEFAULT NULL,
  `nota` text,
  `ingredienti` text,
  `prezzo` double(11,2) NOT NULL,
  `qta` double(11,2) NOT NULL COMMENT 'qta + um = confezione',
  `um` enum('PZ','GR','HG','KG','ML','DL','LT') NOT NULL DEFAULT 'PZ' COMMENT 'qta + um = confezione',
  `um_riferimento` enum('PZ','GR','HG','KG','ML','DL','LT') NOT NULL DEFAULT 'PZ',
  `pezzi_confezione` int(11) NOT NULL,
  `qta_minima` int(11) NOT NULL,
  `qta_multipli` int(11) NOT NULL,
  `bio` enum('N','Y') NOT NULL DEFAULT 'N',
  `img1` varchar(50) DEFAULT NULL,
  `stato` enum('Y','N') NOT NULL DEFAULT 'Y',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
DROP TRIGGER IF EXISTS `prod-gas-articles`;
DELIMITER $$
CREATE TRIGGER `prod-gas-articles` AFTER DELETE ON `k_prod_gas_articles`
 FOR EACH ROW BEGIN
delete from k_prod_gas_articles_promotions where prod_gas_article_id = old.id ;
END
$$
DELIMITER ;

DROP TABLE IF EXISTS `k_prod_gas_articles_promotions`;
CREATE TABLE IF NOT EXISTS `k_prod_gas_articles_promotions` (
  `id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `prod_gas_promotion_id` int(11) unsigned NOT NULL,
  `prod_gas_article_id` int(11) unsigned NOT NULL,
  `qta` int(11) NOT NULL DEFAULT '0',
  `prezzo_unita` double(11,2) NOT NULL DEFAULT '0.00',
  `importo` double(11,2) NOT NULL DEFAULT '0.00',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_prod_gas_promotions`;
CREATE TABLE IF NOT EXISTS `k_prod_gas_promotions` (
  `id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `img1` varchar(50) DEFAULT NULL,
  `data_inizio` date NOT NULL,
  `data_fine` date NOT NULL,
  `importo_originale` double(11,2) NOT NULL,
  `importo_scontato` double(11,2) NOT NULL,
  `nota` text,
  `state_code` varchar(50) NOT NULL,
  `stato` enum('Y','N') NOT NULL DEFAULT 'Y',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
DROP TRIGGER IF EXISTS `order-prod-gas-promotions`;
DELIMITER $$
CREATE TRIGGER `order-prod-gas-promotions` AFTER DELETE ON `k_prod_gas_promotions`
 FOR EACH ROW BEGIN
delete from k_prod_gas_promotions_organizations where prod_gas_promotion_id = old.id ;
delete from k_prod_gas_articles_promotions where prod_gas_promotion_id = old.id ;
END
$$
DELIMITER ;

DROP TABLE IF EXISTS `k_prod_gas_promotions_organizations`;
CREATE TABLE IF NOT EXISTS `k_prod_gas_promotions_organizations` (
  `id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `prod_gas_promotion_id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL DEFAULT '0',
  `hasTrasport` enum('Y','N') NOT NULL DEFAULT 'N',
  `trasport` double(11,2) NOT NULL DEFAULT '0.00',
  `hasCostMore` enum('Y','N') NOT NULL DEFAULT 'N',
  `cost_more` double(11,2) NOT NULL DEFAULT '0.00',
  `nota` text,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_prod_groups`;
CREATE TABLE IF NOT EXISTS `k_prod_groups` (
  `id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_prod_users_groups`;
CREATE TABLE IF NOT EXISTS `k_prod_users_groups` (
  `id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `prod_group_id` int(11) NOT NULL,
  `sort` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_request_payments`;
CREATE TABLE IF NOT EXISTS `k_request_payments` (
  `id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `num` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `stato_elaborazione` enum('WAIT','OPEN','CLOSE') NOT NULL,
  `stato_elaborazione_date` date NOT NULL,
  `nota` text COMMENT 'se creo una richiesta di pagamento non legata ad un ordine',
  `data_send` date NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
DROP TRIGGER IF EXISTS `k_request_payments_Trigger`;
DELIMITER $$
CREATE TRIGGER `k_request_payments_Trigger` AFTER DELETE ON `k_request_payments`
 FOR EACH ROW BEGIN
delete from k_summary_payments where request_payment_id = old.id and organization_id = old.organization_id;
delete from k_request_payments_generics where request_payment_id = old.id and organization_id = old.organization_id;
delete from k_request_payments_orders where request_payment_id = old.id and organization_id = old.organization_id;
delete from k_request_payments_storerooms where request_payment_id = old.id and organization_id = old.organization_id;
 END
$$
DELIMITER ;

DROP TABLE IF EXISTS `k_request_payments_generics`;
CREATE TABLE IF NOT EXISTS `k_request_payments_generics` (
  `id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `request_payment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `importo` double(11,2) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `k_request_payments_orders`;
CREATE TABLE IF NOT EXISTS `k_request_payments_orders` (
  `id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `delivery_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `request_payment_id` int(11) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `k_request_payments_storerooms`;
CREATE TABLE IF NOT EXISTS `k_request_payments_storerooms` (
  `id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `delivery_id` int(11) NOT NULL,
  `request_payment_id` int(11) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `k_stat_articles_orders`;
CREATE TABLE IF NOT EXISTS `k_stat_articles_orders` (
  `organization_id` int(11) NOT NULL,
  `stat_order_id` int(11) unsigned NOT NULL,
  `article_organization_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL COMMENT 'lo prendo da articles così se cambia l articolo ho il suo valore',
  `codice` varchar(25) DEFAULT NULL COMMENT 'lo prendo da articles così se cambia l articolo ho il suo valore',
  `prezzo` double(11,2) NOT NULL COMMENT 'lo prendo da articles così se cambia l articolo ho il suo valore',
  `qta` double(11,2) NOT NULL COMMENT 'lo prendo da articles così se cambia l articolo ho il suo valore',
  `um` enum('PZ','GR','HG','KG','ML','DL','LT') NOT NULL COMMENT 'lo prendo da articles così se cambia l articolo ho il suo valore',
  `um_riferimento` enum('PZ','GR','HG','KG','ML','DL','LT') NOT NULL COMMENT 'lo prendo da articles così se cambia l articolo ho il suo valore'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_stat_carts`;
CREATE TABLE IF NOT EXISTS `k_stat_carts` (
  `organization_id` int(11) NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `article_organization_id` int(11) NOT NULL,
  `article_id` int(11) unsigned NOT NULL,
  `stat_order_id` int(11) unsigned NOT NULL,
  `qta` int(11) NOT NULL,
  `importo` double(11,2) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_stat_deliveries`;
CREATE TABLE IF NOT EXISTS `k_stat_deliveries` (
  `id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `luogo` varchar(156) NOT NULL,
  `data` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_stat_orders`;
CREATE TABLE IF NOT EXISTS `k_stat_orders` (
  `id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `supplier_organization_id` int(11) unsigned NOT NULL,
  `supplier_organization_name` varchar(225) DEFAULT NULL,
  `supplier_img1` varchar(50) DEFAULT NULL,
  `stat_delivery_id` int(11) unsigned NOT NULL,
  `data_inizio` date DEFAULT NULL,
  `data_fine` date DEFAULT NULL,
  `importo` double(11,2) NOT NULL,
  `tesoriere_fattura_importo` double(11,2) DEFAULT NULL,
  `tesoriere_doc1` varchar(50) DEFAULT NULL,
  `tesoriere_data_pay` date DEFAULT NULL,
  `tesoriere_importo_pay` double(11,2) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_storerooms`;
CREATE TABLE IF NOT EXISTS `k_storerooms` (
  `id` int(11) NOT NULL,
  `organization_id` int(11) unsigned NOT NULL,
  `delivery_id` int(11) unsigned DEFAULT '0',
  `user_id` int(11) unsigned NOT NULL,
  `article_id` int(11) unsigned NOT NULL,
  `article_organization_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `qta` int(11) NOT NULL,
  `prezzo` double(11,2) NOT NULL,
  `stato` enum('Y','N') NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_summary_deliveries_pos`;
CREATE TABLE IF NOT EXISTS `k_summary_deliveries_pos` (
  `id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `delivery_id` int(11) NOT NULL,
  `importo` double(11,2) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_summary_des_orders`;
CREATE TABLE IF NOT EXISTS `k_summary_des_orders` (
  `id` int(11) NOT NULL,
  `des_id` int(11) NOT NULL,
  `des_order_id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `importo_orig` double(11,2) NOT NULL,
  `importo` double(11,2) NOT NULL COMMENT 'somma di summary_order_trasports.importo + summary_order_trasports.importo_trasport',
  `importo_pagato` double(11,2) NOT NULL,
  `modalita` enum('DEFINED','CONTANTI','BONIFICO','BANCOMAT') NOT NULL DEFAULT 'DEFINED',
  `nota` text NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_summary_des_order_cost_lesses`;
CREATE TABLE IF NOT EXISTS `k_summary_des_order_cost_lesses` (
  `id` int(11) NOT NULL,
  `des_id` int(11) NOT NULL,
  `des_order_id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `importo_cost_less` double(11,2) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_summary_des_order_cost_mores`;
CREATE TABLE IF NOT EXISTS `k_summary_des_order_cost_mores` (
  `id` int(11) NOT NULL,
  `des_id` int(11) NOT NULL,
  `des_order_id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `importo_cost_more` double(11,2) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_summary_des_order_trasports`;
CREATE TABLE IF NOT EXISTS `k_summary_des_order_trasports` (
  `id` int(11) NOT NULL,
  `des_id` int(11) NOT NULL,
  `des_order_id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `importo_trasport` double(11,2) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_summary_orders`;
CREATE TABLE IF NOT EXISTS `k_summary_orders` (
  `id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `delivery_id` int(11) NOT NULL,
  `order_id` int(11) unsigned NOT NULL COMMENT 'se e'' 0 arriva da storeroom',
  `importo` double(11,2) NOT NULL COMMENT 'somma di summary_order_trasports.importo + summary_order_trasports.importo_trasport',
  `importo_pagato` double(11,2) NOT NULL,
  `modalita` enum('DEFINED','CONTANTI','BONIFICO','BANCOMAT') NOT NULL DEFAULT 'DEFINED',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_summary_order_cost_lesses`;
CREATE TABLE IF NOT EXISTS `k_summary_order_cost_lesses` (
  `id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `order_id` int(11) unsigned NOT NULL COMMENT 'se e'' 0 arriva da storeroom',
  `importo` double(11,2) NOT NULL,
  `peso` int(11) NOT NULL COMMENT 'indica la somma di tutti i gr o lt degli articoli acquistati, serve per il calcolo del trasporto a peso, se 0 ci sono UM diverse gr, hg o kg',
  `importo_cost_less` double(11,2) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_summary_order_cost_mores`;
CREATE TABLE IF NOT EXISTS `k_summary_order_cost_mores` (
  `id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `order_id` int(11) unsigned NOT NULL COMMENT 'se e'' 0 arriva da storeroom',
  `importo` double(11,2) NOT NULL,
  `peso` int(11) NOT NULL COMMENT 'indica la somma di tutti i gr o lt degli articoli acquistati, serve per il calcolo del trasporto a peso, se 0 ci sono UM diverse gr, hg o kg',
  `importo_cost_more` double(11,2) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_summary_order_trasports`;
CREATE TABLE IF NOT EXISTS `k_summary_order_trasports` (
  `id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `order_id` int(11) unsigned NOT NULL COMMENT 'se e'' 0 arriva da storeroom',
  `importo` double(11,2) NOT NULL,
  `peso` int(11) NOT NULL COMMENT 'indica la somma di tutti i gr o lt degli articoli acquistati, serve per il calcolo del trasporto a peso, se 0 ci sono UM diverse gr, hg o kg',
  `importo_trasport` double(11,2) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_summary_payments`;
CREATE TABLE IF NOT EXISTS `k_summary_payments` (
  `id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `request_payment_id` int(11) NOT NULL,
  `importo_dovuto` double(11,2) NOT NULL,
  `importo_richiesto` double(11,2) NOT NULL,
  `importo_pagato` double(11,2) NOT NULL,
  `modalita` enum('DEFINED','CONTANTI','BONIFICO','BANCOMAT') NOT NULL,
  `stato` enum('DAPAGARE','SOLLECITO1','SOLLECITO2','PAGATO','SOSPESO') NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_suppliers`;
CREATE TABLE IF NOT EXISTS `k_suppliers` (
  `id` int(11) NOT NULL,
  `category_supplier_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) DEFAULT NULL,
  `nome` varchar(50) DEFAULT NULL,
  `cognome` varchar(50) DEFAULT NULL,
  `descrizione` text,
  `indirizzo` varchar(50) DEFAULT NULL,
  `localita` varchar(50) DEFAULT NULL,
  `cap` varchar(5) DEFAULT NULL,
  `provincia` char(2) DEFAULT NULL,
  `lat` varchar(15) NOT NULL,
  `lng` varchar(15) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `telefono2` varchar(20) DEFAULT NULL,
  `fax` varchar(20) DEFAULT NULL,
  `mail` varchar(100) DEFAULT NULL,
  `www` varchar(100) DEFAULT NULL,
  `nota` text,
  `cf` varchar(16) DEFAULT NULL,
  `piva` varchar(11) DEFAULT NULL,
  `conto` varchar(50) DEFAULT NULL,
  `j_content_id` int(11) NOT NULL DEFAULT '0' COMMENT 'id riferito all''articolo #__content',
  `img1` varchar(50) NOT NULL COMMENT 'img che comparira con il modulo gas_content_image',
  `stato` enum('Y','N','T','PG') NOT NULL DEFAULT 'Y' COMMENT 'T = temporanea, creato dal referente e non ancora approvata',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
DROP TRIGGER IF EXISTS `k_suppliers_Trigger`;
DELIMITER $$
CREATE TRIGGER `k_suppliers_Trigger` AFTER DELETE ON `k_suppliers`
 FOR EACH ROW BEGIN
delete from k_suppliers_organizations where supplier_id = old.id;
delete from k_des_suppliers where supplier_id = old.id;
delete from k_suppliers_votes where supplier_id = old.id;
 END
$$
DELIMITER ;

DROP TABLE IF EXISTS `k_suppliers_organizations`;
CREATE TABLE IF NOT EXISTS `k_suppliers_organizations` (
  `id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `name` varchar(225) NOT NULL COMMENT 'ripeto il valore di table.suppliers cosi'' quando prendo l''elenco non devo fare la join',
  `category_supplier_id` int(11) NOT NULL DEFAULT '0' COMMENT 'ripeto il valore di table.suppliers cosi'' quando prendo l''elenco non devo fare la join',
  `frequenza` varchar(50) DEFAULT NULL,
  `owner_articles` enum('SUPPLIER','REFERENT') NOT NULL DEFAULT 'REFERENT' COMMENT 'indica se il listino degli articoli associati puo modificarlo il produttore o il referente',
  `can_view_orders` enum('Y','N') NOT NULL DEFAULT 'N',
  `can_view_orders_users` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'permessi per vedere gli ordini e gli acquisti dei gasisti',
  `mail_order_open` enum('Y','N') NOT NULL DEFAULT 'Y',
  `mail_order_close` enum('Y','N') NOT NULL DEFAULT 'Y',
  `stato` enum('Y','N') CHARACTER SET utf16 COLLATE utf16_bin NOT NULL DEFAULT 'Y',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
DROP TRIGGER IF EXISTS `k_suppliers_organizations_Trigger`;
DELIMITER $$
CREATE TRIGGER `k_suppliers_organizations_Trigger` AFTER DELETE ON `k_suppliers_organizations`
 FOR EACH ROW BEGIN
delete from k_suppliers_organizations_referents where supplier_organization_id = old.id and organization_id = old.organization_id;
delete from k_suppliers_organizations_jcontents where supplier_organization_id = old.id and organization_id = old.organization_id;
delete from k_articles where supplier_organization_id = old.id and organization_id = old.organization_id;
delete from k_orders where supplier_organization_id = old.id and organization_id = old.organization_id;
delete from k_bookmarks_articles where supplier_organization_id = old.id and organization_id = old.organization_id;
delete from k_monitoring_suppliers_organizations where supplier_organization_id = old.id and organization_id = old.organization_id;
 END
$$
DELIMITER ;

DROP TABLE IF EXISTS `k_suppliers_organizations_jcontents`;
CREATE TABLE IF NOT EXISTS `k_suppliers_organizations_jcontents` (
  `id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `supplier_organization_id` int(11) NOT NULL,
  `title` varchar(225) DEFAULT NULL,
  `introtext` mediumtext CHARACTER SET utf8,
  `fulltext` mediumtext CHARACTER SET utf8,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `k_suppliers_organizations_referents`;
CREATE TABLE IF NOT EXISTS `k_suppliers_organizations_referents` (
  `organization_id` int(11) NOT NULL,
  `supplier_organization_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL COMMENT 'join con user_usergroup_map, usergroups',
  `type` enum('REFERENTE','COREFERENTE') CHARACTER SET utf8 NOT NULL DEFAULT 'REFERENTE' COMMENT 'lato front-end viene evidenziata la differenza',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `k_suppliers_votes`;
CREATE TABLE IF NOT EXISTS `k_suppliers_votes` (
  `id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL DEFAULT '0',
  `organization_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `nota` text,
  `voto` int(11) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_templates_des_orders_states`;
CREATE TABLE IF NOT EXISTS `k_templates_des_orders_states` (
  `template_id` int(11) NOT NULL,
  `state_code` varchar(50) NOT NULL,
  `group_id` int(11) NOT NULL,
  `action_controller` varchar(25) NOT NULL,
  `action_action` varchar(50) NOT NULL,
  `flag_menu` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'se Y gestisco il menu del referente, titolare',
  `sort` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_templates_des_orders_states_orders_actions`;
CREATE TABLE IF NOT EXISTS `k_templates_des_orders_states_orders_actions` (
  `template_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `state_code` varchar(50) NOT NULL,
  `des_order_action_id` int(11) NOT NULL,
  `sort` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_templates_orders_states`;
CREATE TABLE IF NOT EXISTS `k_templates_orders_states` (
  `template_id` int(11) NOT NULL,
  `state_code` varchar(50) NOT NULL,
  `group_id` int(11) NOT NULL,
  `action_controller` varchar(25) NOT NULL,
  `action_action` varchar(50) NOT NULL,
  `flag_menu` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'se Y gestisco il menu del referente, cassiere o tesoriere',
  `sort` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_templates_orders_states_orders_actions`;
CREATE TABLE IF NOT EXISTS `k_templates_orders_states_orders_actions` (
  `template_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `state_code` varchar(50) NOT NULL,
  `order_action_id` int(11) NOT NULL,
  `sort` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `k_templates_prod_gas_promotions_states`;
CREATE TABLE IF NOT EXISTS `k_templates_prod_gas_promotions_states` (
  `template_id` int(11) NOT NULL,
  `state_code` varchar(50) NOT NULL,
  `group_id` int(11) NOT NULL,
  `action_controller` varchar(25) NOT NULL,
  `action_action` varchar(50) NOT NULL,
  `flag_menu` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'se Y gestisco il menu del referente, titolare',
  `sort` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


ALTER TABLE `jos_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usertype` (`usertype`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `gid_block` (`gid`,`block`),
  ADD KEY `username` (`username`),
  ADD KEY `email` (`email`);

ALTER TABLE `j_assets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_asset_name` (`name`),
  ADD KEY `idx_lft_rgt` (`lft`,`rgt`),
  ADD KEY `idx_parent_id` (`parent_id`);

ALTER TABLE `j_associations`
  ADD PRIMARY KEY (`context`,`id`),
  ADD KEY `idx_key` (`key`);

ALTER TABLE `j_banners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_state` (`state`),
  ADD KEY `idx_own_prefix` (`own_prefix`),
  ADD KEY `idx_metakey_prefix` (`metakey_prefix`),
  ADD KEY `idx_banner_catid` (`catid`),
  ADD KEY `idx_language` (`language`);

ALTER TABLE `j_banner_clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_own_prefix` (`own_prefix`),
  ADD KEY `idx_metakey_prefix` (`metakey_prefix`);

ALTER TABLE `j_banner_tracks`
  ADD PRIMARY KEY (`track_date`,`track_type`,`banner_id`),
  ADD KEY `idx_track_date` (`track_date`),
  ADD KEY `idx_track_type` (`track_type`),
  ADD KEY `idx_banner_id` (`banner_id`);

ALTER TABLE `j_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cat_idx` (`extension`,`published`,`access`),
  ADD KEY `idx_access` (`access`),
  ADD KEY `idx_checkout` (`checked_out`),
  ADD KEY `idx_path` (`path`),
  ADD KEY `idx_left_right` (`lft`,`rgt`),
  ADD KEY `idx_alias` (`alias`),
  ADD KEY `idx_language` (`language`);

ALTER TABLE `j_contact_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_access` (`access`),
  ADD KEY `idx_checkout` (`checked_out`),
  ADD KEY `idx_state` (`published`),
  ADD KEY `idx_catid` (`catid`),
  ADD KEY `idx_createdby` (`created_by`),
  ADD KEY `idx_featured_catid` (`featured`,`catid`),
  ADD KEY `idx_language` (`language`),
  ADD KEY `idx_xreference` (`xreference`);

ALTER TABLE `j_content`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_access` (`access`),
  ADD KEY `idx_checkout` (`checked_out`),
  ADD KEY `idx_state` (`state`),
  ADD KEY `idx_catid` (`catid`),
  ADD KEY `idx_createdby` (`created_by`),
  ADD KEY `idx_featured_catid` (`featured`,`catid`),
  ADD KEY `idx_language` (`language`),
  ADD KEY `idx_xreference` (`xreference`);

ALTER TABLE `j_contenttemplater`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`,`published`);

ALTER TABLE `j_content_frontpage`
  ADD PRIMARY KEY (`content_id`);

ALTER TABLE `j_content_rating`
  ADD PRIMARY KEY (`content_id`);

ALTER TABLE `j_extensions`
  ADD PRIMARY KEY (`extension_id`),
  ADD KEY `element_clientid` (`element`,`client_id`),
  ADD KEY `element_folder_clientid` (`element`,`folder`,`client_id`),
  ADD KEY `extension` (`type`,`element`,`folder`,`client_id`);

ALTER TABLE `j_finder_filters`
  ADD PRIMARY KEY (`filter_id`);

ALTER TABLE `j_finder_links`
  ADD PRIMARY KEY (`link_id`),
  ADD KEY `idx_type` (`type_id`),
  ADD KEY `idx_title` (`title`),
  ADD KEY `idx_md5` (`md5sum`),
  ADD KEY `idx_url` (`url`(75)),
  ADD KEY `idx_published_list` (`published`,`state`,`access`,`publish_start_date`,`publish_end_date`,`list_price`),
  ADD KEY `idx_published_sale` (`published`,`state`,`access`,`publish_start_date`,`publish_end_date`,`sale_price`);

ALTER TABLE `j_finder_links_terms0`
  ADD PRIMARY KEY (`link_id`,`term_id`),
  ADD KEY `idx_term_weight` (`term_id`,`weight`),
  ADD KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`);

ALTER TABLE `j_finder_links_terms1`
  ADD PRIMARY KEY (`link_id`,`term_id`),
  ADD KEY `idx_term_weight` (`term_id`,`weight`),
  ADD KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`);

ALTER TABLE `j_finder_links_terms2`
  ADD PRIMARY KEY (`link_id`,`term_id`),
  ADD KEY `idx_term_weight` (`term_id`,`weight`),
  ADD KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`);

ALTER TABLE `j_finder_links_terms3`
  ADD PRIMARY KEY (`link_id`,`term_id`),
  ADD KEY `idx_term_weight` (`term_id`,`weight`),
  ADD KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`);

ALTER TABLE `j_finder_links_terms4`
  ADD PRIMARY KEY (`link_id`,`term_id`),
  ADD KEY `idx_term_weight` (`term_id`,`weight`),
  ADD KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`);

ALTER TABLE `j_finder_links_terms5`
  ADD PRIMARY KEY (`link_id`,`term_id`),
  ADD KEY `idx_term_weight` (`term_id`,`weight`),
  ADD KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`);

ALTER TABLE `j_finder_links_terms6`
  ADD PRIMARY KEY (`link_id`,`term_id`),
  ADD KEY `idx_term_weight` (`term_id`,`weight`),
  ADD KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`);

ALTER TABLE `j_finder_links_terms7`
  ADD PRIMARY KEY (`link_id`,`term_id`),
  ADD KEY `idx_term_weight` (`term_id`,`weight`),
  ADD KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`);

ALTER TABLE `j_finder_links_terms8`
  ADD PRIMARY KEY (`link_id`,`term_id`),
  ADD KEY `idx_term_weight` (`term_id`,`weight`),
  ADD KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`);

ALTER TABLE `j_finder_links_terms9`
  ADD PRIMARY KEY (`link_id`,`term_id`),
  ADD KEY `idx_term_weight` (`term_id`,`weight`),
  ADD KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`);

ALTER TABLE `j_finder_links_termsa`
  ADD PRIMARY KEY (`link_id`,`term_id`),
  ADD KEY `idx_term_weight` (`term_id`,`weight`),
  ADD KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`);

ALTER TABLE `j_finder_links_termsb`
  ADD PRIMARY KEY (`link_id`,`term_id`),
  ADD KEY `idx_term_weight` (`term_id`,`weight`),
  ADD KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`);

ALTER TABLE `j_finder_links_termsc`
  ADD PRIMARY KEY (`link_id`,`term_id`),
  ADD KEY `idx_term_weight` (`term_id`,`weight`),
  ADD KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`);

ALTER TABLE `j_finder_links_termsd`
  ADD PRIMARY KEY (`link_id`,`term_id`),
  ADD KEY `idx_term_weight` (`term_id`,`weight`),
  ADD KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`);

ALTER TABLE `j_finder_links_termse`
  ADD PRIMARY KEY (`link_id`,`term_id`),
  ADD KEY `idx_term_weight` (`term_id`,`weight`),
  ADD KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`);

ALTER TABLE `j_finder_links_termsf`
  ADD PRIMARY KEY (`link_id`,`term_id`),
  ADD KEY `idx_term_weight` (`term_id`,`weight`),
  ADD KEY `idx_link_term_weight` (`link_id`,`term_id`,`weight`);

ALTER TABLE `j_finder_taxonomy`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`),
  ADD KEY `state` (`state`),
  ADD KEY `ordering` (`ordering`),
  ADD KEY `access` (`access`),
  ADD KEY `idx_parent_published` (`parent_id`,`state`,`access`);

ALTER TABLE `j_finder_taxonomy_map`
  ADD PRIMARY KEY (`link_id`,`node_id`),
  ADD KEY `link_id` (`link_id`),
  ADD KEY `node_id` (`node_id`);

ALTER TABLE `j_finder_terms`
  ADD PRIMARY KEY (`term_id`),
  ADD UNIQUE KEY `idx_term` (`term`),
  ADD KEY `idx_term_phrase` (`term`,`phrase`),
  ADD KEY `idx_stem_phrase` (`stem`,`phrase`),
  ADD KEY `idx_soundex_phrase` (`soundex`,`phrase`);

ALTER TABLE `j_finder_terms_common`
  ADD KEY `idx_word_lang` (`term`,`language`),
  ADD KEY `idx_lang` (`language`);

ALTER TABLE `j_finder_tokens`
  ADD KEY `idx_word` (`term`),
  ADD KEY `idx_context` (`context`);

ALTER TABLE `j_finder_tokens_aggregate`
  ADD KEY `token` (`term`),
  ADD KEY `keyword_id` (`term_id`);

ALTER TABLE `j_finder_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `title` (`title`);

ALTER TABLE `j_languages`
  ADD PRIMARY KEY (`lang_id`),
  ADD UNIQUE KEY `idx_sef` (`sef`),
  ADD UNIQUE KEY `idx_image` (`image`),
  ADD UNIQUE KEY `idx_langcode` (`lang_code`),
  ADD KEY `idx_access` (`access`),
  ADD KEY `idx_ordering` (`ordering`);

ALTER TABLE `j_menu`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_client_id_parent_id_alias_language` (`client_id`,`parent_id`,`alias`,`language`),
  ADD KEY `idx_componentid` (`component_id`,`menutype`,`published`,`access`),
  ADD KEY `idx_menutype` (`menutype`),
  ADD KEY `idx_left_right` (`lft`,`rgt`),
  ADD KEY `idx_alias` (`alias`),
  ADD KEY `idx_path` (`path`(333)),
  ADD KEY `idx_language` (`language`);

ALTER TABLE `j_menu_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_menutype` (`menutype`);

ALTER TABLE `j_messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `useridto_state` (`user_id_to`,`state`);

ALTER TABLE `j_messages_cfg`
  ADD UNIQUE KEY `idx_user_var_name` (`user_id`,`cfg_name`);

ALTER TABLE `j_modules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `published` (`published`,`access`),
  ADD KEY `newsfeeds` (`module`,`published`),
  ADD KEY `idx_language` (`language`);

ALTER TABLE `j_modules_menu`
  ADD PRIMARY KEY (`moduleid`,`menuid`);

ALTER TABLE `j_newsfeeds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_access` (`access`),
  ADD KEY `idx_checkout` (`checked_out`),
  ADD KEY `idx_state` (`published`),
  ADD KEY `idx_catid` (`catid`),
  ADD KEY `idx_createdby` (`created_by`),
  ADD KEY `idx_language` (`language`),
  ADD KEY `idx_xreference` (`xreference`);

ALTER TABLE `j_overrider`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `j_redirect_links`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_link_old` (`old_url`),
  ADD KEY `idx_link_modifed` (`modified_date`);

ALTER TABLE `j_schemas`
  ADD PRIMARY KEY (`extension_id`,`version_id`);

ALTER TABLE `j_session`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `whosonline` (`guest`,`usertype`),
  ADD KEY `userid` (`userid`),
  ADD KEY `time` (`time`);

ALTER TABLE `j_template_styles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_template` (`template`),
  ADD KEY `idx_home` (`home`);

ALTER TABLE `j_updates`
  ADD PRIMARY KEY (`update_id`);

ALTER TABLE `j_update_categories`
  ADD PRIMARY KEY (`categoryid`);

ALTER TABLE `j_update_sites`
  ADD PRIMARY KEY (`update_site_id`);

ALTER TABLE `j_update_sites_extensions`
  ADD PRIMARY KEY (`update_site_id`,`extension_id`);

ALTER TABLE `j_usergroups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_usergroup_parent_title_lookup` (`parent_id`,`title`),
  ADD KEY `idx_usergroup_title_lookup` (`title`),
  ADD KEY `idx_usergroup_adjacency_lookup` (`parent_id`),
  ADD KEY `idx_usergroup_nested_set_lookup` (`lft`,`rgt`) USING BTREE;

ALTER TABLE `j_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usertype` (`usertype`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_block` (`block`),
  ADD KEY `username` (`username`),
  ADD KEY `email` (`email`),
  ADD KEY `organization_id` (`organization_id`);

ALTER TABLE `j_user_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_category_id` (`catid`);

ALTER TABLE `j_user_profiles`
  ADD UNIQUE KEY `idx_user_id_profile_key` (`user_id`,`profile_key`);

ALTER TABLE `j_user_usergroup_map`
  ADD PRIMARY KEY (`user_id`,`group_id`);

ALTER TABLE `j_viewlevels`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_assetgroup_title_lookup` (`title`);

ALTER TABLE `j_weblinks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_access` (`access`),
  ADD KEY `idx_checkout` (`checked_out`),
  ADD KEY `idx_state` (`state`),
  ADD KEY `idx_catid` (`catid`),
  ADD KEY `idx_createdby` (`created_by`),
  ADD KEY `idx_featured_catid` (`featured`,`catid`),
  ADD KEY `idx_language` (`language`),
  ADD KEY `idx_xreference` (`xreference`);

ALTER TABLE `k_articles`
  ADD PRIMARY KEY (`id`,`organization_id`),
  ADD KEY `organization_id` (`organization_id`),
  ADD KEY `supplier_organization_id` (`supplier_organization_id`);

ALTER TABLE `k_articles_articles_types`
  ADD UNIQUE KEY `unique_fields` (`organization_id`,`article_id`,`article_type_id`) USING BTREE,
  ADD KEY `index_article_id` (`article_id`),
  ADD KEY `index_organization_id` (`organization_id`) USING BTREE;

ALTER TABLE `k_articles_orders`
  ADD PRIMARY KEY (`organization_id`,`article_organization_id`,`article_id`,`order_id`),
  ADD KEY `index_organization_id` (`organization_id`),
  ADD KEY `index_article_id` (`article_id`),
  ADD KEY `index_order_id` (`order_id`),
  ADD KEY `index_article_organization_id` (`article_organization_id`);

ALTER TABLE `k_articles_types`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `k_backup_articles_orders`
  ADD KEY `index_organization_id` (`organization_id`),
  ADD KEY `index_order_id` (`order_id`),
  ADD KEY `index_article_organization_id` (`article_organization_id`),
  ADD KEY `index_article_id` (`article_id`);

ALTER TABLE `k_backup_carts`
  ADD KEY `index_organization_id` (`organization_id`),
  ADD KEY `index_user_id` (`user_id`),
  ADD KEY `index_order_id` (`order_id`),
  ADD KEY `index_article_organization_id` (`article_organization_id`),
  ADD KEY `index_article_id` (`article_id`);

ALTER TABLE `k_backup_orders_articles_orders`
  ADD PRIMARY KEY (`organization_id`,`article_organization_id`,`article_id`,`order_id`),
  ADD KEY `index_organization_id` (`organization_id`),
  ADD KEY `index_article_id` (`article_id`),
  ADD KEY `index_order_id` (`order_id`),
  ADD KEY `index_article_organization_id` (`article_organization_id`);

ALTER TABLE `k_backup_orders_carts`
  ADD PRIMARY KEY (`organization_id`,`user_id`,`order_id`,`article_organization_id`,`article_id`),
  ADD KEY `index_organization_id` (`organization_id`),
  ADD KEY `index_user_id` (`user_id`),
  ADD KEY `index_order_id` (`order_id`),
  ADD KEY `index_article_id` (`article_id`),
  ADD KEY `index_article_organization_id` (`article_organization_id`);

ALTER TABLE `k_backup_orders_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `index_organization_id` (`organization_id`) USING BTREE,
  ADD KEY `index_supplier_organization_id` (`supplier_organization_id`) USING BTREE,
  ADD KEY `index_delivery_id` (`delivery_id`) USING BTREE;

ALTER TABLE `k_bookmarks_articles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `organization_id` (`organization_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `supplier_organization_id` (`supplier_organization_id`);

ALTER TABLE `k_bookmarks_mails`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique` (`organization_id`,`user_id`,`supplier_organization_id`),
  ADD KEY `organization_id` (`organization_id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `k_carts`
  ADD PRIMARY KEY (`organization_id`,`user_id`,`order_id`,`article_organization_id`,`article_id`),
  ADD KEY `index_organization_id` (`organization_id`),
  ADD KEY `index_user_id` (`user_id`),
  ADD KEY `index_order_id` (`order_id`),
  ADD KEY `index_article_id` (`article_id`),
  ADD KEY `index_article_organization_id` (`article_organization_id`);

ALTER TABLE `k_carts_splits`
  ADD PRIMARY KEY (`organization_id`,`user_id`,`order_id`,`article_organization_id`,`article_id`,`num_split`);

ALTER TABLE `k_cashes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique` (`organization_id`,`user_id`) USING BTREE,
  ADD KEY `organization_id` (`organization_id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `k_cashes_histories`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `index_cash_id` (`cash_id`);

ALTER TABLE `k_categories`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `k_categories_articles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `index_organization_id` (`organization_id`) USING BTREE;

ALTER TABLE `k_categories_suppliers`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `k_counters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `index_organization_id` (`organization_id`) USING BTREE;

ALTER TABLE `k_deliveries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `index_organization_id` (`organization_id`) USING BTREE;

ALTER TABLE `k_des`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `k_des_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `des_id` (`des_id`);

ALTER TABLE `k_des_orders_actions`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `k_des_orders_organizations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQUE` (`des_id`,`des_order_id`,`organization_id`),
  ADD KEY `organization_id` (`organization_id`),
  ADD KEY `des_id` (`des_id`);

ALTER TABLE `k_des_organizations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique` (`des_id`,`organization_id`),
  ADD KEY `des_id` (`des_id`);

ALTER TABLE `k_des_suppliers`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `k_des_suppliers_referents`
  ADD PRIMARY KEY (`des_id`,`des_supplier_id`,`organization_id`,`user_id`,`group_id`) USING BTREE;

ALTER TABLE `k_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `organization_id` (`organization_id`);

ALTER TABLE `k_events_users`
  ADD PRIMARY KEY (`organization_id`,`user_id`,`event_id`) USING BTREE;

ALTER TABLE `k_event_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `organization_id` (`organization_id`);

ALTER TABLE `k_loops_deliveries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `organization_id` (`organization_id`);

ALTER TABLE `k_mails`
  ADD PRIMARY KEY (`id`),
  ADD KEY `index_organization_id` (`organization_id`) USING BTREE,
  ADD KEY `index_user_id` (`user_id`) USING BTREE;

ALTER TABLE `k_monitoring_orders`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `k_monitoring_suppliers_organizations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique` (`organization_id`,`supplier_organization_id`);

ALTER TABLE `k_msgs`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `k_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `index_organization_id` (`organization_id`) USING BTREE,
  ADD KEY `index_supplier_organization_id` (`supplier_organization_id`) USING BTREE,
  ADD KEY `index_delivery_id` (`delivery_id`) USING BTREE;

ALTER TABLE `k_orders_actions`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `k_organizations`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `k_organizations_pays`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `k_pdf_carts`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `undex_organiz_user_id` (`organization_id`,`user_id`);

ALTER TABLE `k_pdf_carts_orders`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `undex_organiz_user_id` (`organization_id`,`user_id`);

ALTER TABLE `k_prod_carts`
  ADD PRIMARY KEY (`organization_id`,`user_id`,`prod_delivery_id`,`article_organization_id`,`article_id`);

ALTER TABLE `k_prod_carts_splits`
  ADD PRIMARY KEY (`organization_id`,`user_id`,`prod_delivery_id`,`article_organization_id`,`article_id`,`num_split`);

ALTER TABLE `k_prod_deliveries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `organization_id` (`organization_id`);

ALTER TABLE `k_prod_deliveries_articles`
  ADD PRIMARY KEY (`organization_id`,`prod_delivery_id`,`article_organization_id`,`article_id`);

ALTER TABLE `k_prod_deliveries_states`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `k_prod_gas_articles`
  ADD PRIMARY KEY (`id`,`supplier_id`),
  ADD KEY `supplier_id` (`supplier_id`);

ALTER TABLE `k_prod_gas_articles_promotions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `index_key` (`supplier_id`,`prod_gas_promotion_id`) USING BTREE;

ALTER TABLE `k_prod_gas_promotions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `index_supplier_id` (`supplier_id`) USING BTREE;

ALTER TABLE `k_prod_gas_promotions_organizations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique-key` (`supplier_id`,`prod_gas_promotion_id`,`organization_id`),
  ADD KEY `index_supplier_id` (`supplier_id`) USING BTREE;

ALTER TABLE `k_prod_groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `organization_id` (`organization_id`);

ALTER TABLE `k_prod_users_groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `organization_id` (`organization_id`);

ALTER TABLE `k_request_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `organization_id` (`organization_id`);

ALTER TABLE `k_request_payments_generics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `organization_id` (`organization_id`),
  ADD KEY `request_payment_id` (`request_payment_id`);

ALTER TABLE `k_request_payments_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique` (`organization_id`,`delivery_id`,`order_id`,`request_payment_id`),
  ADD KEY `organization_id` (`organization_id`),
  ADD KEY `request_payment_id` (`request_payment_id`),
  ADD KEY `delivery_id` (`delivery_id`),
  ADD KEY `order_id` (`order_id`);

ALTER TABLE `k_request_payments_storerooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `organization_id` (`organization_id`),
  ADD KEY `request_payment_id` (`request_payment_id`);

ALTER TABLE `k_stat_articles_orders`
  ADD PRIMARY KEY (`organization_id`,`stat_order_id`,`article_id`),
  ADD KEY `index_organization_id` (`organization_id`),
  ADD KEY `index_stat_order_id` (`stat_order_id`),
  ADD KEY `index_article_organization_id` (`article_organization_id`),
  ADD KEY `index_article_id` (`article_id`);

ALTER TABLE `k_stat_carts`
  ADD PRIMARY KEY (`organization_id`,`user_id`,`article_id`,`stat_order_id`),
  ADD KEY `index_organization_id` (`organization_id`),
  ADD KEY `index_user_id` (`user_id`) USING BTREE,
  ADD KEY `index_article_organization_id` (`article_organization_id`),
  ADD KEY `index_article_id` (`article_id`),
  ADD KEY `index_stat_order_id` (`stat_order_id`);

ALTER TABLE `k_stat_deliveries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `index_organization_id` (`organization_id`) USING BTREE;

ALTER TABLE `k_stat_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `index_organization_id` (`organization_id`) USING BTREE,
  ADD KEY `index_stat_delivery_id` (`stat_delivery_id`) USING BTREE,
  ADD KEY `index_supplier_organization_id` (`supplier_organization_id`) USING BTREE;

ALTER TABLE `k_storerooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `organization_id` (`organization_id`),
  ADD KEY `delivery_id` (`delivery_id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `k_summary_deliveries_pos`
  ADD PRIMARY KEY (`id`,`organization_id`,`user_id`),
  ADD KEY `index_organization_id` (`organization_id`),
  ADD KEY `index_user_id` (`user_id`),
  ADD KEY `index_delivery_id` (`delivery_id`);

ALTER TABLE `k_summary_des_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `index_des_id` (`des_id`),
  ADD KEY `index_des_order_id` (`des_order_id`),
  ADD KEY `index_organization_id` (`organization_id`);

ALTER TABLE `k_summary_des_order_cost_lesses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `organization_id` (`organization_id`),
  ADD KEY `des_order_id` (`des_order_id`),
  ADD KEY `des_id` (`des_id`);

ALTER TABLE `k_summary_des_order_cost_mores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `organization_id` (`organization_id`),
  ADD KEY `des_order_id` (`des_order_id`),
  ADD KEY `des_id` (`des_id`);

ALTER TABLE `k_summary_des_order_trasports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `organization_id` (`organization_id`),
  ADD KEY `des_order_id` (`des_order_id`),
  ADD KEY `des_id` (`des_id`);

ALTER TABLE `k_summary_orders`
  ADD PRIMARY KEY (`id`,`organization_id`,`user_id`,`order_id`),
  ADD UNIQUE KEY `index_unique` (`organization_id`,`user_id`,`delivery_id`,`order_id`),
  ADD KEY `organization_id` (`organization_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `order_id` (`order_id`);

ALTER TABLE `k_summary_order_cost_lesses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `index_unique` (`organization_id`,`user_id`,`order_id`),
  ADD KEY `organization_id` (`organization_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `order_id` (`order_id`);

ALTER TABLE `k_summary_order_cost_mores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `index_unique` (`organization_id`,`user_id`,`order_id`),
  ADD KEY `organization_id` (`organization_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `order_id` (`order_id`);

ALTER TABLE `k_summary_order_trasports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `index_unique` (`organization_id`,`user_id`,`order_id`),
  ADD KEY `organization_id` (`organization_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `order_id` (`order_id`);

ALTER TABLE `k_summary_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `index_organization_id` (`organization_id`) USING BTREE,
  ADD KEY `index_request_payment_id` (`request_payment_id`) USING BTREE,
  ADD KEY `index_user_id` (`user_id`) USING BTREE;

ALTER TABLE `k_suppliers`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `k_suppliers_organizations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `index_supplier_id` (`supplier_id`) USING BTREE,
  ADD KEY `index_organization_id` (`organization_id`) USING BTREE;

ALTER TABLE `k_suppliers_organizations_jcontents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `organization_id` (`organization_id`),
  ADD KEY `supplier_organization_id` (`supplier_organization_id`);

ALTER TABLE `k_suppliers_organizations_referents`
  ADD PRIMARY KEY (`organization_id`,`user_id`,`supplier_organization_id`,`group_id`,`type`),
  ADD KEY `index_organization_id` (`organization_id`),
  ADD KEY `index_user_id` (`user_id`) USING BTREE,
  ADD KEY `index_supplier_organization_id` (`supplier_organization_id`);

ALTER TABLE `k_suppliers_votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique-key` (`supplier_id`,`organization_id`) USING BTREE,
  ADD KEY `index_supplier_id_organization_id` (`supplier_id`,`organization_id`);

ALTER TABLE `k_templates_des_orders_states`
  ADD PRIMARY KEY (`template_id`,`state_code`,`group_id`) USING BTREE;

ALTER TABLE `k_templates_des_orders_states_orders_actions`
  ADD PRIMARY KEY (`template_id`,`state_code`,`group_id`,`des_order_action_id`);

ALTER TABLE `k_templates_orders_states`
  ADD PRIMARY KEY (`template_id`,`state_code`,`group_id`),
  ADD KEY `index_template_id` (`template_id`),
  ADD KEY `index_state_code` (`state_code`);

ALTER TABLE `k_templates_orders_states_orders_actions`
  ADD PRIMARY KEY (`template_id`,`state_code`,`group_id`,`order_action_id`),
  ADD KEY `index_template_id` (`template_id`),
  ADD KEY `state_code` (`state_code`);

ALTER TABLE `k_templates_prod_gas_promotions_states`
  ADD KEY `index` (`template_id`,`state_code`,`group_id`);


ALTER TABLE `jos_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `j_assets`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key';
ALTER TABLE `j_banners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `j_banner_clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `j_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `j_contact_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `j_content`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `j_contenttemplater`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `j_extensions`
  MODIFY `extension_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `j_finder_filters`
  MODIFY `filter_id` int(10) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `j_finder_links`
  MODIFY `link_id` int(10) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `j_finder_taxonomy`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `j_finder_terms`
  MODIFY `term_id` int(10) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `j_finder_types`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `j_languages`
  MODIFY `lang_id` int(11) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `j_menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `j_menu_types`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `j_messages`
  MODIFY `message_id` int(10) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `j_modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `j_newsfeeds`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `j_overrider`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'Primary Key';
ALTER TABLE `j_redirect_links`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `j_template_styles`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `j_updates`
  MODIFY `update_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `j_update_categories`
  MODIFY `categoryid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `j_update_sites`
  MODIFY `update_site_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `j_usergroups`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key';
ALTER TABLE `j_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `j_user_notes`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `j_viewlevels`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key';
ALTER TABLE `j_weblinks`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_articles_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_backup_orders_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_bookmarks_articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_bookmarks_mails`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_cashes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_cashes_histories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_categories`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_categories_articles`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_categories_suppliers`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_counters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_deliveries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_des`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_des_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_des_orders_organizations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_des_organizations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_des_suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_event_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_loops_deliveries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_mails`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_monitoring_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_monitoring_suppliers_organizations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_msgs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_orders_actions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_organizations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_organizations_pays`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_pdf_carts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_pdf_carts_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_prod_deliveries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_prod_deliveries_states`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_prod_gas_articles`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_prod_gas_articles_promotions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_prod_gas_promotions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_prod_gas_promotions_organizations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_prod_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_prod_users_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_request_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_request_payments_generics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_request_payments_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_request_payments_storerooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_stat_deliveries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_stat_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_storerooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_summary_deliveries_pos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_summary_des_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_summary_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_summary_order_cost_lesses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_summary_order_cost_mores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_summary_order_trasports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_summary_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_suppliers_organizations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_suppliers_organizations_jcontents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `k_suppliers_votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
