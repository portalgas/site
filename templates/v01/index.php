<?php 
// No direct access.
defined('_JEXEC') or die;

$app				= JFactory::getApplication();
$doc				= JFactory::getDocument();
$templateparams		= $app->getTemplate(true)->params;
$organizationSEO    = $templateparams->get('organizationSEO');
$sitename           = JFactory::getConfig()->get('sitename');
$vue_is_active      = $app->getCfg('VueIsActive');

/*
 * gestione ricerca motori di ricerca, escludo portalGasTest / PortAlGasNext
 */
$noindex = false;
if(strtolower($sitename)!='portalgas')
	$noindex = true;
?>		
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" >
<head>
<?php
if($noindex)
	echo '<meta name="robots" content="noindex">';
?>
<jdoc:include type="head" />

	<meta name="norton-safeweb-site-verification" content="26g7jy0laennobqc9502goi6qmcnayf2lgd00pw5o8-psvut8bnp4ouijcjorctqimliu2bsd01d9zxlhff7nwdrpb6sj7hl09qw0snol3mtlxz8jv-87f1ik52g45ds" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

	<script src="<?php echo $this->baseurl ?>/templates/v01/javascript/jquery-1.11.2.min.js"></script>
	<script src="<?php echo $this->baseurl ?>/templates/v01/javascript/mustache.min.js"></script>
	<script src="<?php echo $this->baseurl ?>/templates/v01/javascript/bootstrap.min.js"></script>
	<script src="<?php echo $this->baseurl ?>/templates/v01/javascript/bootstrap-select.min.js"></script>
    <script src="<?php echo $this->baseurl ?>/templates/v01/javascript/bootstrap-tooltip.js" type="text/javascript"></script>
    <script src="<?php echo $this->baseurl ?>/templates/v01/javascript/bootstrap-touchspin.js" type="text/javascript"></script>
    <script src="<?php echo $this->baseurl ?>/templates/v01/javascript/cookiechoices.js" type="text/javascript"></script>
    
	<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/v01/css/bootstrap.min.css">
	<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/v01/css/bootstrap-select.min.css">
	<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/v01/css/font-awesome.min.css">
	<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/v01/css/my-bootstrap-v03.min.css">
	
	<script type="text/javascript" src="<?php echo $this->baseurl ?>/components/com_cake/app/webroot/js/jquery/jquery-ui-1.10.3.custom.min.js"></script>
	<link rel="stylesheet" type="text/css" href="<?php echo $this->baseurl ?>/components/com_cake/app/webroot/ui-themes/smoothness/jquery-ui-1.10.3.custom.min.css">

	<script type="text/javascript" src="<?php echo $this->baseurl ?>/components/com_cake/app/webroot/js/my-modal-v02.js"></script>
	
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

<style type="text/css">
.lof-ass .lof-css3, .lof-ass .lof-css3 .preload {
    box-shadow: none;
}
.lof-ass .blue .lof-navigator li div {
    background: none repeat scroll 0 0 #C6D5E0;
}
.lof-ass, 
.lof-ass .blue {
    border: medium none;
}
.lof-ass .blue {
    margin-bottom: 25px;
}

.item-page {
	text-align: justify;
}
.item-page img {
	margin:10px 10px 0 0;
}
.contact-image img {
    float: right;
    position: relative;
    top: 125px;
}
.jicons-icons img {
	display:none;
}
</style>

<?php
if($organizationSEO == 'gas-ocasansalvario') {
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('h2').each(function() {
		jQuery(this).addClass('h2-red');
	});
	jQuery('#header-menu').addClass('header-menu-red');

	jQuery('#btn-account').addClass('btn-red');
	jQuery('#btn-account').removeClass('btn-orange');
	
	jQuery('a').each(function() {
		jQuery(this).addClass('red');
	});		
});	
</script>
<?php 
}
?>

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-48245560-1', 'portalgas.it');
  ga('send', 'pageview');
</script>
</head>
<body>
<div class="container">

  <a name="top" id="top"></a>

   <header>
		<div class="hidden-xs col-md-6 col-sm-6">
			<a href="/index.php"><div class="logo hidden-xs"></div>
				<h1 style="position: absolute; font-size: 14px; color: rgb(10, 101, 158); opacity: 0.7; top: 45px;">Gestionale web per Gruppi d'acquisto solidale e D.E.S.</h1>
			</a>
		</div>					
		<div class="col-xs-12 col-md-6 col-sm-6">
			<jdoc:include type="modules" name="position-account" />
			<jdoc:include type="modules" name="position-account-menu" />
		</div>
   </header>
   

   <nav role="navigation" class="navbar navbar-default">
   
        <div class="navbar-header">
            <button type="button" data-target="#navbarCollapse" data-toggle="collapse" class="navbar-toggle">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a href="#" class="navbar-brand visible-xs">PortAlGas</a>
        </div>
        <div id="navbarCollapse" class="collapse navbar-collapse">
        
				<jdoc:include type="modules" name="position-menu-left" />
					
            	<div class="nav navbar-nav navbar-right">
	
					<jdoc:include type="modules" name="position-menu-right" />

				</div>
        </div>
		
    </nav>

	<div class="row">
		<div class="col-xs-12 col-md-12">
			<jdoc:include type="message" />		
		</div>
	</div>
	<div class="row">
		<div class="col-xs-12 col-md-12 hidden-xs hidden-sm">
			<jdoc:include type="modules" name="position-before-content" />
		</div>
	</div>
	
<?php 
if($this->countModules('position-cols-right')==0) {
?>
		<div class="row">
			<div class="col-xs-12 col-md-12">
				<jdoc:include type="component" />
			</div>		
		</div>
<?php 
}
else {
?>		<div class="row">
			<div class="col-xs-12 col-md-9">
				<jdoc:include type="modules" name="position-0" />
				
				<jdoc:include type="component" />
				
				<jdoc:include type="modules" name="position-content-bottom" />
			</div>
			<div class="col-md-3 hidden-xs hidden-sm">
				    <jdoc:include type="modules" name="position-cols-right" />
			</div>
		</div>
			
<?php 
}
?>
</div> <!-- container -->

<footer>

	<div class="footer-above">
		<div class="container">
			<div class="row">
				<div class="footer-col col-md-3 col-xs-12 col-sm-6 text-left">

					<ul class="social">
						<li>
							<a target="_blank" href="https://facebook.com/portalgas.it"><img border="0" src="/images/cake/ico-social-fb.png" alt="PortAlGas su facebook" title="PortAlGas su facebook"> Facebook</a>
						</li>
						<li>
							<a target="_blank" href="https://www.youtube.com/channel/UCo1XZkyDWhTW5Aaoo672HBA"><img border="0" src="/images/cake/ico-social-youtube.png" alt="PortAlGas su YouTube" title="PortAlGas su YouTube"> YouTube</a>
						</li>
						<li>
							<a href="https://manuali.portalgas.it" target="_blank"><img border="0" title="I manuali di PortAlGas" alt="I manuali di PortAlGas" src="/images/cake/ico-manual.png"> Manuali</a>
						</li>						
						<li>
							<a target="_blank" href="/mobile"><img border="0" src="/images/cake/ico-mobile.png" alt="PortAlGas per tablet e mobile" title="PortAlGas per tablet e mobile"> Mobile</a>
						</li>					
						<li>
							<a target="_blank" href="https://github.com/portalgas/site"><img border="0" src="/images/cake/ico-github.png" alt="il codice di PortAlGas disponibile per chi desidera partecipare" title="il codice di PortAlGas disponibile per chi desidera partecipare"> GitHub</a>
						</li>
					</ul>			
				
				</div>
				<div class="footer-col col-md-3 col-xs-12 col-sm-6">

					<?php 
					if($organizationSEO!='portale') {
					?>
						<ul class="social">
							<li>
								<a target="_blank" href="/rss/gas-<?php echo $organizationSEO;?>.rss"><img border="0" src="/images/cake/ico-rss.png" alt="rimani aggiornato con gli Rss di PortAlGas" title="rimani aggiornato con gli Rss di PortAlGas"> Rss per produttore</a>
							</li>
							<li>
								<a target="_blank" href="/rss/gas-<?php echo $organizationSEO;?>2.rss"><img border="0" src="/images/cake/ico-rss.png" alt="rimani aggiornato con gli Rss di PortAlGas" title="rimani aggiornato con gli Rss di PortAlGas"> Rss per consegna</a>
							</li>
						</ul>	
					<?php 
					}
					?>						
										
				</div>
					
				<div class="footer-col col-md-3 col-xs-12 col-sm-6">
					<a target="_blank" href="https://itunes.apple.com/us/app/portalgas/id1133263691">
						<img border="0" title="vai allo store di Itunes" src="/images/appstore.png"></a>								
					<a target="_blank" href="https://play.google.com/store/apps/details?id=com.ionicframework.portalgas">
						<img border="0" title="vai allo store di Google" src="/images/googleplay.png"></a>								
				</div>
				
				<div class="footer-col col-md-3 col-xs-12 col-sm-6 text-right">
					<ul class="social">
						<li>
							<a href="/12-portalgas/2-termini-di-utilizzo" title="Leggi le condizioni di utilizzo di PortAlGas">Termini di utilizzo</a>
						</li>
						<li>
							<a href="/12-portalgas/143-come-sono-utilizzati-i-cookies-da-parte-di-portalgas" title="Leggi come sono utilizzati i cookies da parte di PortAlGas">Utilizzo dei cookies</a>
						</li>
						<li>
							<a href="/12-portalgas/103-bilancio" title="Leggi il bilancio di PortAlGas">Bilancio</a>
						</li>	
					</ul>
				</div>
			</div> <!-- row -->
		</div> <!-- container -->
	</div> <!-- footer-above -->

	<div class="footer-below">
        <div class="container">
                <div class="row">
                    <div class="col-lg-12 text-center">
						Copyright &copy; <?php echo date('Y');?> PortAlGas. All Rights Reserved.
						<span class="pull-right">
							<a href="mailto:info@portalgas.it" title="Scrivi una mail a info@portalgas.it">info@portalgas.it</a>
						</span>
                    </div>
                </div>
        </div>
	</div>
</footer>
						
<script type="text/javascript">
var csrfToken = '';
function callPing() {
	/* console.log("Script.callPing "+pingAjaxUrl); */
	var url = '?option=com_cake&controller=Pages&action=ping&format=notmpl';
	var httpRequest = new XMLHttpRequest();
	httpRequest.open('GET', url);
	httpRequest.setRequestHeader("X-Requested-With", "XMLHttpRequest");
	httpRequest.setRequestHeader("Content-type", "application/json");
	httpRequest.setRequestHeader('X-CSRF-Token', csrfToken);
	httpRequest.send(null);
}

jQuery(document).ready(function () {

	<?php 
	if($vue_is_active) { // Configure::read('Vue.isActive')
	 	// echo 'console.log(jQuery("a:contains(\'Acquista\')").attr("href"));';
	 	echo 'jQuery("a:contains(\'Acquista\')").attr("href", "/?option=com_cake&controller=Connects&action=index&c_to=fai-la-spesa");'; // Configure::read('Neo.portalgas.url')
	 	echo "\n\r";
	 	echo 'jQuery("a:contains(\'Carrello\')").attr("href", "");';
	 	echo 'jQuery("a:contains(\'Carrello\')").attr("href", "/?option=com_cake&controller=Connects&action=index&c_to=user-cart");';
	}
	?>

	jQuery('.selectpicker').selectpicker({
		style: 'btn-default'
	});
	
	jQuery('#tabs').tab();

	jQuery('a').tooltip();
	
	/*
	 *  img notizie
	 */
	jQuery('.blog * img').css('padding','15px').addClass('img-responsive');
	
	/*
	if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {  
	  jQuery('body').append('<div style="position:absolute;top:0;left:0;opacity: 0.8;background-color:#2c3e50;padding:10px;width:45%"><a style="color:#fff" href="https://www.portalgas.it/mobile">Vai alla versione mobile</a></div>');
	}	
	*/
	
	window.setInterval(callPing, 500000); // 1000 = 1 sec (0,14 h)
});
</script>
<jdoc:include type="modules" name="debug" />
	
<script type="text/javascript">
  document.addEventListener('DOMContentLoaded', function(event) {
    cookieChoices.showCookieConsentBar('PortAlGas per offrirti una migliore esperienza su questo sito utilizza cookie tecnici e di profilazione. Il sito consente anche l\'invio di cookie di terze parti, sia tecnici, analitici che di profilazione.',
      'Chiudi messaggio', 'Maggior informazioni', '/12-portalgas/143-come-sono-utilizzati-i-cookies-da-parte-di-portalgas');
  });
</script>  
</body>
</html>				
