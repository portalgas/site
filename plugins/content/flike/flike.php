<?php
/*------------------------------------------------------------------------
04.# plg_flike
05.# ------------------------------------------------------------------------
06.# Gyula Komar
07.# copyright Copyright (C) 2011 Build Web.eu All Rights Reserved.
08.# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
09.# Websites: http://www.buildweb.eu
10.# Technical Support:  Forum - http://www.buildweb.eu/index.php?option=com_content&view=article&id=58&Itemid=81&lang=en
11.-------------------------------------------------------------------------*/
defined( '_JEXEC' ) or die();
if (!defined('DS')) {define('DS', DIRECTORY_SEPARATOR );}

jimport( 'joomla.event.plugin' );
class plgContentflike extends JPlugin 
{
	function __construct( $subject, $params ) 
	{
		parent::__construct( $subject, $params );
 	}
 
	//for J17
	function onContentPrepare( $context, $article, $params, $limitstart=0 )
	{
		$this->onPrepareContent( $article, $params, $limitstart );
	}
	
	//for J15
	function onPrepareContent( $row, $params, $limitstart=0 )
	{	
		$mainframe = JFactory::getApplication();

		static $first_og=1;

 		$regex = '/{(flike)\s*(.*?)}/i';

		$j15 = version_compare(JVERSION,'1.6.0','<');
		$j30 = version_compare(JVERSION,'3.0.0','>=');
    		if ($j15)
    		{
			$plugin	= JPluginHelper::getPlugin('content', 'flike');
			$pluginParams = new JParameter( $plugin->params );
    		} else
    		{
			$pluginParams = $this->params;
		}


		$send_button=$pluginParams->get('send_button','');
		if ($send_button=="0") $send_button="false";
		if ($send_button=="1") $send_button="true";

		$layout=$pluginParams->get('layout','');

		$show_faces=$pluginParams->get('show_faces','');
		if ($show_faces=="0") $show_faces="false";
		if ($show_faces=="1") $show_faces="true";

		$width=$pluginParams->get('width','');
		$action=$pluginParams->get('action','');
		$colorscheme=$pluginParams->get('colorscheme','');
		$app_id=$pluginParams->get('app_id','');
		$og_url=$pluginParams->get('og_url','');
		$og_type=$pluginParams->get('og_type','article');
		$og_image=$pluginParams->get('og_image','');
		$url_from=$pluginParams->get('url_from','');
		$url_to=$pluginParams->get('url_to','');

		$uri = JURI::getInstance();
		$curl = $uri->toString();

		$config = JFactory::getConfig();

		if ($j15)
		{
			$sitename=$config->getValue('config.sitename');
		} else
		if ($j30)
		{
			$sitename=$config->get('sitename');
		} else {
			$sitename=$config->getInstance('config.sitename');
		}

		$lang = JFactory::getLanguage();
		$lang_tag=$lang->getTag();
		$lang_tag=str_replace("-","_",$lang_tag);

		$matches = array();
		preg_match_all( $regex, $row->text, $matches, PREG_SET_ORDER );
		$skip_og_image=false;

		foreach ($matches as $args) 
		{
			$args=str_replace(" ","&", $args);
			parse_str( $args[2], $pars );

			$str="";

			if (isset($pars['lang'])) {$lang_tag=$pars['lang'];}
			if (isset($pars['image'])) {$og_image=$pars['image'];$skip_og_image=true;}

			$uri = JURI::getInstance();
			$curl = $uri->toString();

			$curl = str_replace("https://","http://",$curl);

			$id="";if (isset($pars['id'])) {$id=$pars['id'];}
			if ($id!="")
			{
				$article = JTable::getInstance('content');
				$article->load($id);
				$slug = $article->get('id').':'.$article->get('alias');
				$catid = $article->get('catid');
				$catslug = $catid ? $catid .':'.$article->get('category_alias') : $catid;
				$sectionid = $article->get('sectionid');
			
				$curl = 'http://';
				if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"]==”on”) {$curl='https://';};
				$curl .= $_SERVER["SERVER_NAME"];
				if ($j15)
				{
					$curl .= JRoute::_(ContentHelperRoute::getArticleRoute($slug, $catslug, $sectionid));
				} else
				{
					$curl .= JRoute::_(ContentHelperRoute::getArticleRoute($slug, $catslug));
				}
			}

			if (isset($pars['url'])) 
			{
				$curl=$pars['url'];
				$curl=str_replace("~","=",$curl);
				$curl=str_replace("#","&",$curl);
			}

			if ($url_from!="") $curl = str_replace($url_from,$url_to,$curl);

			if ($j15)
			{
				$url="<!--plugin name=flike version=1.0.23-->";
			} else
			if ($j30)
			{
				$url="<!--plugin name=flike version=3.0.23-->";
			} else
			{
				$url="<!--plugin name=flike version=1.7.23-->";
			}

			$url.="<script src=\"http://connect.facebook.net/".$lang_tag."/all.js#xfbml=1\"></script><fb:like href=\"".$curl."\" send=\"".$send_button."\" layout=\"".$layout."\" show_faces=\"".$show_faces."\" width=\"".$width."\" action=\"".$action."\" colorscheme=\"".$colorscheme."\"></fb:like>";

			$row->text = preg_replace($regex, $url, $row->text, 1);
		}

		$doc = JFactory::getDocument();
		if ($first_og && (count($matches)>0))
		{
			if ($app_id!="") {$doc->addCustomTag('<meta property="fb:app_id" content="'.$app_id.'"/>');}
			if ($og_url=="1")
			{
				if (!$skip_og_image) if (!$j15)
				{			
					$images=json_decode($row->images);
					if (isset($images->image_fulltext) && !empty($images->image_fulltext)) $og_image=$uri->base().$images->image_fulltext;
					if (isset($images->image_intro) && !empty($images->image_intro)) $og_image=$uri->base().$images->image_intro;
				}

				$doc->addCustomTag('<meta property="og:type" content="'.$og_type.'"/>');
				$doc->addCustomTag('<meta property="og:url" content="'.$curl.'"/>');
				$doc->addCustomTag('<meta property="og:site_name" content="'.$sitename.'"/>');
				$doc->addCustomTag('<meta property="og:locale" content="'.$lang_tag.'"/>');
			}
			if ($og_image!="") $doc->addCustomTag('<meta property="og:image" content="'.$og_image.'"/>');
		}
		$first_og=0;
	}
}
?>