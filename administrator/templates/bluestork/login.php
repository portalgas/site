<?php
// no direct access
defined('_JEXEC') or die;

jimport('joomla.filesystem.file');

$app = JFactory::getApplication();
$doc = JFactory::getDocument();

$doc->addScript('templates/'.$this->template.'/js/bootstrap.min.js');
$doc->addStyleSheet('templates/'.$this->template.'/css/bootstrap.min.css');
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <jdoc:include type="head" />
    
	<style>
	.logo {
	    background: url("/images/cake/loghi/0/150h50.png") no-repeat scroll 0 0 rgba(0, 0, 0, 0);
	    min-height: 75px;
	    width: 100%;
	}
	.navbar-barra {
		background: none repeat scroll 0 0 #0A659E;
		height: 30px;
	} 	
	.main-center {
	    margin: 0 auto;
	    margin-top: 100px;
	    max-width: 450px;
	    padding: 10px 40px 40px 40px;
	}
	.main-login {
	    background-color: #fff;
	    -moz-border-radius: 2px;
	    -webkit-border-radius: 2px;
	    border-radius: 2px;
	    -moz-box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);
	    -webkit-box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);
	    box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);
	}

	.footer a {
		color:#bbb;
	}
	.footer a:hover {
		color:#fff;
	}	
	.footer {
		position: fixed;
		bottom: 0;
		width:100%;
		color: #bbb;
		background: none repeat scroll 0 0 #222222;
		padding:5px;
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
	</style>


    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
        
  </head>

  <body>


	<nav class="navbar navbar-fixed-top">
		<div class="container">
      		<a href="/"><div class="logo"></div></a>
      	</div>
	    <div class="navbar-barra">
	    	<div class="container visible-lg visible-md visible-sm">
	    		<div style="font-size: 14px; color: #fff;">Gestionale web per Gruppi d'acquisto solidale e D.E.S.</div>
	    	</div>	
	    </div>
    </nav>
    
    <div class="container">
		<div class="main-login main-center">
			<jdoc:include type="message" />
			<jdoc:include type="component" />
		</div>
    </div>

    <footer class="footer">
		<div class="container">
			<div class="col-md-6">
				<a href="/12-portalgas/2-termini-di-utilizzo" target="_blank" title="Leggi le condizioni di utilizzo di PortAlGas">Termini di utilizzo</a> 
				&nbsp;|&nbsp;
				<a href="/12-portalgas/103-bilancio" target="_blank" title="Leggi il bilancio di PortAlGas">Bilancio</a>
			</div>
			<div class="col-6 pull-right">Copyright &copy; <?php echo date('Y');?> PortAlGas. All Rights Reserved.</div>
		</div>
	</footer>

	<noscript>
		<?php echo JText::_('JGLOBAL_WARNJAVASCRIPT') ?>
	</noscript>
	
  </body>
</html>