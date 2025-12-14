<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	Templates.bluestork
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.filesystem.file');

$app = JFactory::getApplication();
$doc = JFactory::getDocument();

$uri = JFactory::getURI();
$component = $uri->getVar('option');

$doc->addStyleSheet('templates/system/css/system.css');

if($component!='com_cake') {
	$doc->addStyleSheet('templates/'.$this->template.'/css/template.css');

	if ($this->direction == 'rtl') {
		$doc->addStyleSheet('templates/'.$this->template.'/css/template_rtl.css');
	}
}

/** Load specific language related css */
$lang = JFactory::getLanguage();
$file = 'language/'.$lang->getTag().'/'.$lang->getTag().'.css';
if (JFile::exists($file)) {
	$doc->addStyleSheet($file);
}

if ($this->params->get('textBig')) {
	$doc->addStyleSheet('templates/'.$this->template.'/css/textbig.css');
}

if ($this->params->get('highContrast')) {
	$doc->addStyleSheet('templates/'.$this->template.'/css/highcontrast.css');
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo  $this->language; ?>" lang="<?php echo  $this->language; ?>" dir="<?php echo  $this->direction; ?>" >
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<jdoc:include type="head" />

<!--[if IE 7]>
<link href="templates/<?php echo  $this->template ?>/css/ie7.css" rel="stylesheet" type="text/css" />
<![endif]-->

<!--[if gte IE 8]>
<link href="templates/<?php echo  $this->template ?>/css/ie8.css" rel="stylesheet" type="text/css" />
<![endif]-->
<?php
if($component=='com_cake') {
?>
	<script>
	function getInternetExplorerVersion()
	{
	  var rv = -1;
	  if (navigator.appName == 'Microsoft Internet Explorer')
	  {
		var ua = navigator.userAgent;
		var re  = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
		if (re.exec(ua) != null)
		  rv = parseFloat( RegExp.$1 );
	  }
	  else if (navigator.appName == 'Netscape')
	  {
		var ua = navigator.userAgent;
		var re  = new RegExp("Trident/.*rv:([0-9]{1,}[\.0-9]{0,})");
		if (re.exec(ua) != null)
		  rv = parseFloat( RegExp.$1 );
	  }
	  return rv;
	}
	
	var browser_version = getInternetExplorerVersion();
	if(browser_version==-1) {
		document.write('<script type=\"text/javascript\" src=\"<?php echo $this->baseurl ?>/../components/com_cake/app/webroot/js/jquery/jquery-3.2.1.min.js\"><\/script>');
		document.write('<script type=\"text/javascript\" src=\"<?php echo $this->baseurl ?>/../components/com_cake/app/webroot/js/jquery/jquery-ui.min.js\"><\/script>');
		document.write('<script type=\"text/javascript\" src=\"<?php echo $this->baseurl ?>/../components/com_cake/app/webroot/js/i18n/ui.datepicker-it.js\"><\/script>');
		document.write('<script type=\"text/javascript\" src=\"<?php echo $this->baseurl ?>/../components/com_cake/app/webroot/js/genericBackOffice-v09.min.js?v=20251212\"><\/script>');
	}
	else {
		document.write('<script type=\"text/javascript\" src=\"<?php echo $this->baseurl ?>/../components/com_cake/app/webroot/js/jquery/jquery-1.10.1.min.js\"><\/script>');
		document.write('<script type=\"text/javascript\" src=\"<?php echo $this->baseurl ?>/../components/com_cake/app/webroot/js/jquery/jquery-ui.min.js\"><\/script>');
		document.write('<script type=\"text/javascript\" src=\"<?php echo $this->baseurl ?>/../components/com_cake/app/webroot/js/jquery/jquery-ui.min.js\"><\/script>');
		document.write('<script type=\"text/javascript\" src=\"<?php echo $this->baseurl ?>/../components/com_cake/app/webroot/js/ie-arcgis.com.min.js\"><\/script>');
		document.write('<script type=\"text/javascript\" src=\"<?php echo $this->baseurl ?>/../components/com_cake/app/webroot/js/genericBackOffice-v02-IE.min.js\"><\/script>');	
	}
	</script>

	
	<link rel="stylesheet" type="text/css" href="<?php echo $this->baseurl ?>/../components/com_cake/app/webroot/ui-themes/base/jquery-ui.css">
	
	
	<script type="text/javascript" src="<?php echo $this->baseurl ?>/../components/com_cake/app/webroot/js/ckeditor.4/ckeditor.js"></script>
	<script type="text/javascript" src="<?php echo $this->baseurl ?>/../components/com_cake/app/webroot/js/ckeditor.4/adapters/jquery.js"></script>
	
	<link rel="stylesheet" type="text/css" href="<?php echo $this->baseurl ?>/../components/com_cake/app/webroot/css/style-min.css?20140128">
	<link rel="stylesheet" type="text/css" href="<?php echo $this->baseurl ?>/../components/com_cake/app/webroot/css/styleBackoffice-v13-min.css">
<?php
}
?>
</head>
<body>

	<jdoc:include type="modules" name="menu" />
	
	<div class="clr"></div>
	<div>
		<div id="toolbar-box">
			<div class="m">
				<jdoc:include type="modules" name="toolbar" />
				<jdoc:include type="modules" name="title" />
			</div>
		</div>
		<?php if (!JRequest::getInt('hidemainmenu')): ?>
			<jdoc:include type="modules" name="submenu" style="rounded" id="box-submenu" />
		<?php endif; ?>
		<jdoc:include type="message" />
		<div id="box-principal"> <!-- padding -->
				<jdoc:include type="component" />
				<div class="clr"></div>
		</div>
		<noscript>
			<?php echo  JText::_('JGLOBAL_WARNJAVASCRIPT') ?>
		</noscript>
	</div>

	
	
	<?php
	/*
	<jdoc:include type="modules" name="footer" style="none"  />
	
	<div id="footer">
		<p class="copyright">
			<?php $joomla= '<a href="http://www.joomla.org">Joomla!&#174;</a>';
				echo JText::sprintf('JGLOBAL_ISFREESOFTWARE', $joomla) ?>
		</p>
	</div>
	*/
	?>
</body>

<style>
.myOpen > .dropdown-toggle {
    background-color: #eee;
    border-color: #337ab7;
}
.navbar-default .navbar-nav .myOpen .dropdown-menu > li > a {
    color: #777;
}
.myOpen > .dropdown-menu {display: block;}
</style>
<script>
function openSubMenu() {
	/*console.log("openSubMenu width "+$(this).width());*/
	if( $(this).width() <= 750 ) {
		$('.dropdown-submenu').addClass('myOpen');
	} else {
		$('.dropdown-submenu').removeClass('myOpen');
	}
}
$(window).resize(function() {
    openSubMenu();
});

$(function() {
	$( ".dropdown-toggle").on( "click", function() {
		/*console.log($(this).text()+"");*/
		openSubMenu();
	});
});	
</script>
</html>