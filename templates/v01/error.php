<?php 
// No direct access.
defined('_JEXEC') or die;

$app				= JFactory::getApplication();
$doc				= JFactory::getDocument();
$templateparams		= $app->getTemplate(true)->params;
$organizationSEO    = $templateparams->get('organizationSEO');
?>
		
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" >
<head>
<jdoc:include type="head" />

    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

	<script src="<?php echo $this->baseurl ?>/templates/v01/javascript/jquery-1.11.2.min.js"></script>
	<script src="<?php echo $this->baseurl ?>/templates/v01/javascript/bootstrap.min.js"></script>
        
	<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/v01/css/bootstrap.css">
	<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/v01/css/font-awesome.min.css">
	<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/v01/css/my-bootstrap.css">
	
	
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    
   
<script type="text/javascript">
	$.noConflict();
</script>
</head>
<body>

   <a name="top" id="top"></a>

   <header class="container">
		<div class="col-xs-6">
			<div class="logo hidden-xs">
				<a href="/index.php"></a>
			</div>
		</div>					
		<div class="col-xs-6">
		</div>
   </header>
   

   <nav role="navigation" class="navbar navbar-default"></nav>


<div class="container">
	
		<div class="col-xs-12">

			<div style="font-size:22px;height:350px;padding: 50px 0;">
			Si è verificato un errore.<br />
			Non è possibile trovare la pagina richiesta.
			
			<p style="padding-top:15px;font-size: 14px;"><?php echo $this->error->getCode(); ?> - <?php echo htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8'); ?></p>
			
			<p>
				<?php if ($this->debug) :
					echo $this->renderBacktrace();
				endif; ?>
			</p>
		
		
		
			<p style="font-size:16px;padding: 25px 0;"><a href="/" title="Torna alla Home Page">Torna alla Home Page</a></p>
			</div>			
		
		</div>	

</div>

<footer>
	<div class="container">
			<div class="col-xs-4">

				<ul class="social">
					<li>
						<a href="https://www.facebook.com/pages/Portalgas/677581532361100" target="_blank"><img border="0" title="PortAlGas su facebook" alt="PortAlGas su facebook" src="/images/cake/ico-social-fb.png" /> Facebook</a>
					</li>
				</ul>
			
			</div>
			<div class="col-xs-4 text-align-center">
			</div>
			<div class="col-xs-4 text-align-right">
				<a title="Leggi le condizioni di utilizzo di PortAlGas" href="/12-portalgas/2-termini-di-utilizzo">Termini di utilizzo</a>
			</div>
	</div>
	<div class="container">
			<div class="col-xs-12 text-align-center">
				Copyright &copy; <?php echo date('Y');?> PortAlGas. All Rights Reserved.
				<span class="pull-right">
					<a title="Scrivi una mail a info@portalgas.it" href="mailto:info@portalgas.it">info@portalgas.it</a>
				</span>
			</div>
	</div>
</footer>
				

<jdoc:include type="modules" name="debug" />
		
	</body>
</html>				