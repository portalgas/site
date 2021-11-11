<?php
// no direct access
defined('_JEXEC') or die;

jimport('joomla.filesystem.file');

$app = JFactory::getApplication();
$doc = JFactory::getDocument();

$doc->addStyleSheet('templates/system/css/system.css');
$doc->addStyleSheet('templates/'.$this->template.'/css/template.css');

if ($this->direction == 'rtl') {
	$doc->addStyleSheet('templates/'.$this->template.'/css/template_rtl.css');
}

/** Load specific language related css */
$lang = JFactory::getLanguage();
$file = 'language/'.$lang->getTag().'/'.$lang->getTag().'.css';
if (JFile::exists($file)) {
	$doc->addStyleSheet($file);
}

JHtml::_('behavior.noframes');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" >
<head>
<jdoc:include type="head" />

<!--[if IE 7]>
<link href="templates/<?php echo  $this->template ?>/css/ie7.css" rel="stylesheet" type="text/css" />
<![endif]-->

<script type="text/javascript">
	window.addEvent('domready', function () {
		document.getElementById('form-login').username.select();
		document.getElementById('form-login').username.focus();
	});
</script>






	 	
<style type="text/css">
.clearafter:after {
  clear: both;
  display: block;
  content: ".";
  height: 0;
  visibility: hidden;
}
.clearbreak,
.clear {
  clear: both;
}

.clearfix:before, .clearfix:after {
	content: "";
	display: table;
	line-height: 0;
}

/*
 * struttura
 */
.wrapper{
	width: 960px;
	margin: auto;
}
			
#header {
    background: none repeat scroll 0 0 #FFF;
    padding: 5px 0;
    height: 75px;
}

#header-top-left {
	width: 25%;
	float: left;
}
#header-top-right {
	width: 50%;
	float: right;
}
#header .logo {
    background: url("/images/cake/loghi/0/150h50.png") no-repeat scroll 0 0 rgba(0, 0, 0, 0);
    min-height: 75px;
    width: 100%;
}
#header .logo a {
    display: block;
    min-height: 45px;
    text-indent: -99999em;
    width: 100%;
}
#header-menu {
    background: none repeat scroll 0 0 #0A659E;
    height: 50px;
}
.header-menu-green {
    background: none repeat scroll 0 0 #4B6A38 !important;
}

#header-menu-left {
	width: 70%;
	float: left;
}
#header-menu-right {
	width: 30%;
	float: left;
	text-align:right;
	line-height:24px;
	display:inline-block;
}

#body-content {
    background: none repeat scroll 0 0 rgba(255, 255, 255, 0.6);
    padding: 0 0 20px;
    position: relative;
}
#main-content {
	width: 100%;
    float: left;
}						
#content-left {
	width: 30%;
}
#content-right {
	width: 30%;
}
#content-middle {
	width: 70%;
}
#content-left, #content-middle, #content-right {
    float: left;
}
#content-left, #content-right {
    height: auto;
}
#content-right-inner {
    margin-left: 20px;
}	


#header-menu [class*="menu"] > li:hover {
    background: none repeat scroll 0 0 rgba(255, 255, 255, 0.2);
}
#header-menu [class*="menu"] li.parent {
    position: relative;
}
#header-menu .icons[class*="menu"] > li {
	margin:0px;
    padding: 15px 10px 15px 0;
}
#header-menu [class*="menu"] > li {
    float: left;
    font-size: 14px;
    padding: 18px 22px;
    transition: all 0.4s ease-in-out 0s;
}
#header-menu ul[class*="menu"] > li > a, 
#header-menu ul[class*="menu"] > li > span {
    font-weight: bold;
    line-height: 24px;
    margin: 0;
    padding: 1px 10px;
    text-transform: uppercase;
}
#header-menu [class*="menu"] li span, 
#header-menu [class*="menu"] li a {
    color: #FFFFFF;
    display: block;
    line-height: 40px;
    padding: 0 10px;
    text-decoration: none;
    text-transform: capitalize;
    transition: all 0.2s ease-in-out 0s;
}
#header-menu [class*="menu"] li span {
    cursor: default;
}
#header-menu [class*="menu"] > li {
    font-size: 14px;
}
#header-menu [class*="menu"] [class*="menu"], 
#header-menu [class*="menu"] ul {
    font-family: "oswaldbook",Arial,Helvetica,sans-serif;
    list-style: none outside none;
}

html body, html select, html input, html form, html textarea {
    font-family: Segoe UI,Arial,Verdana,Tahoma,sans-serif;
}
html, body {
    line-height: 1.5;
    margin: 0;
    padding: 0;
}
body {
    background: none repeat scroll 0 0 #FFFFFF;
    color: #777777;
    font-size: 85%;
    overflow-x: hidden;
}

ol li, ul li {
    list-style-position: inside;
}
ul, ul li {
    list-style: none outside none;
}
ul, ul li, ol, ol li, p, form, input {
    margin: 0;
    padding: 0;
}
li {
    line-height: 20px;
}
a, a:link, a:visited {
    color: #0060A6;  // #1B57B1
    outline: medium none;
    text-decoration: none;
    transition: all 0.2s ease-in-out 0s;
}

legend {
	display:none;
}
fieldset {
	border:none;
	margin:0;
	padding:0;
}


.invalid {
	border-color:red !important;
}
label.invalid {
	color:red;
}
.invalid { border-color: #ff0000; }
label.invalid { color: #ff0000; }

input[type="password"], 
input[type="text"], 
input[type="email"]  {
    -moz-appearance: none;
    -moz-border-bottom-colors: none;
    -moz-border-left-colors: none;
    -moz-border-right-colors: none;
    -moz-border-top-colors: none;
    -moz-box-sizing: border-box;
    background: none repeat scroll 0 0 #FFFFFF;
    border-color: #C0C0C0 #D9D9D9 #D9D9D9;
    border-image: none;
    border-radius: 1px;
    border-style: solid;
    border-width: 1px;
    color: #404040;
    display: inline-block;
    font-size: 15px;
    height: 32px;
    margin: 0 0 5px 0;
    padding: 0 4px;
}
input[type="file"], input[type="image"], input[type="submit"], input[type="reset"], input[type="button"], input[type="radio"], input[type="checkbox"] {
    width: auto;
}
label, select, button, input[type="button"], input[type="reset"], input[type="submit"], input[type="radio"], input[type="checkbox"] {
    cursor: pointer;
}


#form-login .button-holder {
    background-color: #0a659e;
    border-radius: 3px;
    clear: right;
    float: right;
    font-weight: bold;
    margin-top: 10px;
    padding: 5px;
    text-align: center;
}
#form-login .button-holder a {
    padding: 3px 20px;
    color: #fff;
}
#form-login .button-holder a:hover {
    color: #fff;
}

/* System Messages */
.error {
   padding:0px;
   margin-bottom: 20px;
}

.error h2 {
        color:#000 !important;
        font-size:1.4em !important;
        text-transform:uppercase;
        padding:0 0 0 0px !important
}

#system-message
{
	margin:10px 0 20px 0;
	border-left:0;
	border-right:0;
}

#system-message dt
{
	font-weight: bold;
}
#system-message dd
{
	margin: 0 0 15px 0;
	font-weight: bold;
	text-indent: 0px;
	padding:0
}
#system-message dd ul
{
   color: #FFFFFF;
    font-size: 120%;
    list-style: none outside none;
	padding: 0px;
}
#system-message dd ul li
{
	line-height:1.5em
}

/* System Standard Messages */
#system-message dt.message
{
	position:absolute;
	top:-2000px;
	left:-3000px;
}
#system-message dd.message ul {
    background: url("/images/cake/system-message-green.jpg") no-repeat scroll 0 50% #3FB724;
}

#system-message dt.error
{
	position:absolute;
	top:-2000px;
	left:-3000px;
}
#system-message dt.notice
{
	position:absolute;
	top:-2000px;
	left:-3000px;
}
#system-message dd.notice ul { color: #000;margin:10px 0 }

#system-message
{
    margin-bottom: 0px;
    padding: 0;
}
#system-message dt
{
    font-weight: normal;
}
#system-message dd
{
    font-weight: normal;
    padding: 0;
}
#system-message dd.error ul {
    background: url("/images/cake/system-message-red.jpg") no-repeat scroll 0 50% #F63031;
    border-bottom: medium none;
    border-top: medium none;
}
#system-message dd.notice ul {
    background: url("/images/cake/system-message-yellow.jpg") no-repeat scroll 0 50% #FFDE00;
    border-bottom: medium none;
    border-top: medium none;
}
#system-message dd.message ul li {
    padding: 15px 10px 0 135px;
    text-align: left;
}
#system-message dd ul {
    height: 91px;
    margin: 0;
    padding: 0;
}
#system-message > .error > ul, #system-message > .warning > ul, #system-message > .notice > ul {
    color: #fff;
}

input#mod-login-username, input#mod-login-password, select#lang {
    width: 65%;
}
input[type="password"], input[type="text"], input[type="email"]  {
	height:26px;
}

.footer {
	position: fixed;
	bottom: 0;
	width:100%;
	color: #bbb;
}
.footer a {
	color:#bbb;
}
.footer a:hover {
	color:#fff;
}
#footer-top {
    background: none repeat scroll 0 0 #222222;
    padding: 30px 0 20px;
}
#footer-top-right {
	width: 33.33%;
	float: right;
}
#footer-tops-left {
	width: 33.33%;
	float: left;
}
#footer-top-middle {
	width: 33.34%;
	float: left;
}

#footer-bottom {
    background: none repeat scroll 0 0 #000000;
    padding: 20px 0;
}
#footer-bottom .copyright {
    padding-top: 10px;
    text-align: right;
}
</style>

</head>
<body>
	<a name="top" id="top"></a>
	<div class="clearfix">

			<div id="header">
				<div class="wrapper">
					<div id="header-top-left">
						<div>
							<div class="logo">
								<a href="/index.php">.</a>
								<div style="position: absolute; font-size: 14px; color: rgb(10, 101, 158); opacity: 0.7; top: 65px;">Gestionale web per Gruppi d'acquisto solidale e D.E.S.</div>
							</div>
							<div class="clearbreak"></div>
						</div>
					</div>	

					<div id="header-top-right">
					</div>		
				</div>
			</div>

			<div id="header-menu">
				<div class="wrapper">
					<div class="clearfix">
						<div id="header-menu-left">
						</div>
		
						<div id="header-menu-right">
						</div>
					</div>
				</div>
			</div>

	</div>




	<div class="" id="body-content">
		<div class="wrapper">
	
				<div class="clearbreak"></div>

				
				

		<div id="main-content">
			<div id="content_inner">
			
			
			
	<div id="content-box">
			<div id="element-box" class="login">
				<div class="m wbg">
					<!-- h1>Accesso al back-office di PortAlGas</h1 -->
					<jdoc:include type="message" />
					<jdoc:include type="component" />
					<?php
					/* 
					 * fractis
					<p><?php echo JText::_('COM_LOGIN_VALID') ?></p>
					<p><a href="<?php echo JURI::root(); ?>"><?php echo JText::_('COM_LOGIN_RETURN_TO_SITE_HOME_PAGE') ?></a></p>
					<div id="lock"></div>
				    */
					?>
				</div>
			</div>
			<noscript>
				<?php echo JText::_('JGLOBAL_WARNJAVASCRIPT') ?>
			</noscript>
	</div>

	
	
	
	
	
	
	
	
	
	
	
				</div>		
		</div>

			<div class="clearbreak"></div>
	</div>
</div>






</div>

			<div class="footer">
				<div id="footer-top">
					<div class="wrapper">
						
							<span>
								<a target="_blank" href="https://facebook.com/portalgas.it"><img border="0" src="/images/cake/ico-social-fb.png" alt="PortAlGas su facebook" title="PortAlGas su facebook"> Facebook</a>&nbsp; 
							</span>
							<span>
								<a target="_blank" href="https://www.youtube.com/channel/UCo1XZkyDWhTW5Aaoo672HBA"><img border="0" src="/images/cake/ico-social-youtube.png" alt="PortAlGas su YouTube" title="PortAlGas su YouTube"> YouTube</a>&nbsp;
							</span>
							<span>
								<a href="http://manuali.portalgas.it" target="_blank"><img border="0" title="I manuali di PortAlGas" alt="I manuali di PortAlGas" src="/images/cake/ico-manual.png"> Manuali</a>&nbsp;
							</span>						
							
							<!-- span>
								<a target="_blank" href="/mobile"><img border="0" src="/images/cake/ico-mobile.png" alt="PortAlGas per tablet e mobile" title="PortAlGas per tablet e mobile"> Mobile</a> 
							</span>		
						
							<span style="float:right">
								<a target="_blank" href="https://itunes.apple.com/us/app/portalgas/id1133263691">
									<img border="0" title="vai allo store di Itunes" src="/images/appstore.png"></a>		
							</span>						
							<span style="float:right">
								<a target="_blank" href="https://play.google.com/store/apps/details?id=com.ionicframework.portalgas">
				     				<img border="0" title="vai allo store di Google" src="/images/googleplay.png"></a>
							</span -->

						<div class="clearbreak"></div>
					</div>
				</div>
 
				<div id="footer-bottom">
					<div class="wrapper">
						<div>
							<div class="" style="float:left;">
								<span>
									<a href="/12-portalgas/2-termini-di-utilizzo" target="_blank" title="Leggi le condizioni di utilizzo di PortAlGas">Termini di utilizzo</a> |
								</span>
								<span>
									<a href="/12-portalgas/103-bilancio" target="_blank" title="Leggi il bilancio di PortAlGas">Bilancio</a>
								</span>
							</div>
						</div>							
						<div>
							<div class="">
								<div class="copyright">Copyright &copy; <?php echo date('Y');?> PortAlGas. All Rights Reserved.</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			

	<jdoc:include type="modules" name="debug" />
	</body>
</html>				