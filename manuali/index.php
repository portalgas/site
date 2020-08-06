<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PortAlGas - manuale</title>

	<link rel="stylesheet" href="css/bootstrap.css">
	<link rel="stylesheet" href="css/bootstrap-theme.min.css">


    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
	
	
<style>
body {
	padding-top: 25px;
}

.glyphicon:after {
    border-top: 9px solid #e85425;
}
.glyphicon:after {
    border-left: 9px solid transparent;
    border-right: 9px solid transparent;
    border-top: 9px solid #e85425;
    bottom: -8px;
    content: " ";
    display: block;
    height: 0;
    left: 50%;
    margin-left: -9px;
    position: absolute;
    width: 0;
    z-index: 2;
}
.glyphicon {
    font-size: 38px;
    padding: 35px 0 0;
}
.glyphicon {
    background: none repeat scroll 0 0 #e85425;
    border-radius: 50%;
    color: #fff;
    height: 99px;
    line-height: 1;
    margin-bottom: 29px;
    position: relative;
    text-align: center;
    width: 99px;
}
h3 {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 7px;
}
.box-intro {
    text-align: center;
	min-height: 350px;
}
.box-intro-ico {
    text-align: center;
}
.lead {
	font-size:14px;
	color: rgb(10, 101, 158);
}
.footer {
    background-color: #000;
    color: #fff;
    height: 60px;
    padding: 20px;
}
.footer .copyright {
	text-align: right;
	color: #777;
}

</style>

  </head>
  <body>

<div class="container">
	
	<div class="header">
	    <img class="img-responsive" alt="Gestionale per G.A.S. (gruppo d'acquisto solidale) e D.E.S. (distretto economia solidale)" src="https://www.portalgas.it/images/cake/loghi/0/150h50.png" />
	    <p class="lead">Manuali di riferimento per l'utilizzo del gestionale PortAlGas per<br />i <b>G</b>ruppi d'<b>a</b>cquisto <b>s</b>olidali (G.A.S.) e i <b>D</b>istretti di <b>e</b>conomia <b>s</b>olidale (D.E.S.)</p>
		<hr class="half-rule">
	</div>
	  
<div class="row">
	     
	 <a href="gestione_degli_utenti.php">
      	<div class="col-sm-1 col-md-4 box-intro">
	        <span aria-hidden="true" class="glyphicon glyphicon-user"></span>
    	    <h3>Utenti</h3>
        	<p>Ruoli, referenti, importazione</p>
      	</div>
     </a>
     
     <a href="gestione_degli_articoli.php">
			<div class="col-sm-1 col-md-4 box-intro">
        		<span aria-hidden="true" class="glyphicon glyphicon-file"></span>
        		<h3>Articoli</h3>
        		<p>Anagrafica, modifica, importazione, stampa, associazione agli ordini</p>
     		</div>
     </a>

	  <a href="gestione_dei_produttori.php">
      	<div class="col-sm-1 col-md-4 box-intro">
	        <span aria-hidden="true" class="glyphicon glyphicon-globe"></span>
    	    <h3>Produttori</h3>
        	<p>Anagrafica, ricerca nell'anagrafica centralizzata, creazione di un nuovo produtore</p>
      	</div>
	  </a>
	  
	  <a href="gestione_delle_consegne.php">
	      <div class="col-sm-1 col-md-4 box-intro">
    	    <span aria-hidden="true" class="glyphicon glyphicon-shopping-cart"></span>
        	<h3>Le consegne</h3>
        	<p>Anagrafica, creazione</p>
      	  </div>
     </a>
	   
	  <a href="gestione_degli_ordini.php">
	      <div class="col-sm-1 col-md-4 box-intro">
    	    <span aria-hidden="true" class="glyphicon glyphicon-phone-alt"></span>
        	<h3>Gli ordini</h3>
        	<p>Anagrafica, associazione agli articoli, monitorare</p>
      	  </div>
     </a>
	 
	  <a href="gestione_degli_ordini_ciclo.php">
	      <div class="col-sm-1 col-md-4 box-intro">
    	    <span aria-hidden="true" class="glyphicon glyphicon-refresh"></span>
        	<h3>Ciclo dell'ordine</h3>
        	<p>Ciclo di un ordine: prossima apertura, aperto, in carico al referente prima e dopo la consegna, merce arrivata, al cassiere, al tesoriere, chiuso</p>
      	  </div>
     </a>
	 
     <a href="moduli.php">
      	<div class="col-sm-1 col-md-4 box-intro">
        	<span aria-hidden="true" class="glyphicon glyphicon-th"></span>
        	<h3>Moduli</h3>
        	<p>Colli, acquisti nel dettaglio, acquisti aggregati per importo, acquisti suddivisi per quantit&agrave;, costo aggiunivo, sconto, trasporto, cassa, stampe, invio mail</p>
      	</div>
 	</a>
 
	<a href="gestione_del_cassiere.php">
	  <div class="col-sm-1 col-md-4 box-intro">
		<span aria-hidden="true" class="glyphicon glyphicon-euro"></span>
		<h3>Cassa e Cassiere</h3>
		<p>Gestisci i saldi e i debiti verso la cassa, gestisci gli ordini con gli importi di cassa, gestisci il prepagato</p>
	  </div>
	</a>
 
	<a href="gestione_del_tesoriere.php">
     	 <div class="col-sm-1 col-md-4 box-intro">
        	 <span aria-hidden="true" class="glyphicon glyphicon-euro"></span>
        	<h3>Tesoriere</h3>
        	<p>presa in carico degli ordini, elaborazione degli ordini, richiesta di pagamento, pagamento produttori</p>
      	</div>
	</a>
	
	<a href="dispensa.php">
     	 <div class="col-sm-1 col-md-4 box-intro">
        	 <span aria-hidden="true" class="glyphicon glyphicon-folder-open"></span>
        	<h3>Dispensa</h3>
        	<p>inserisci gli articoli in dispensa, permetti ai gasisti di acquistare, gestisci i pagamenti</p>
      	</div>
	</a>
	
	<a href="problemi.php">
     	 <div class="col-sm-1 col-md-4 box-intro">
        	 <span aria-hidden="true" class="glyphicon glyphicon-warning-sign"></span>
        	<h3>Problemi</h3>
        	<p>Stampe, invio mail, ricevere mail, visualizzazione</p>
      	</div>
	</a>

	<a href="front-end.php">
     	 <div class="col-sm-1 col-md-4 box-intro">
        	 <span aria-hidden="true" class="glyphicon glyphicon-cloud"></span>
        	<h3>Il sito</h3>
        	<p>Consegne, acquisti, profilo personale, personalizzazione notifica mail, Rss</p>
      	</div>
	</a>
	
	<a href="faq.php">
     	 <div class="col-sm-1 col-md-4 box-intro">
        	 <span aria-hidden="true" class="glyphicon glyphicon-question-sign"></span>
        	<h3>F.A.Q.</h3>
        	<p>Le domande più frequenti che ci vengono rivolti</p>
      	</div>
	</a>
	
	<a href="des.php">
     	 <div class="col-sm-1 col-md-4 box-intro">
        	 <span aria-hidden="true" class="glyphicon glyphicon-globe"></span>
        	<h3>D.E.S.</h3>
        	<p>Distretto di economia solidale</p>
      	</div>
	</a>
	
	<a href="social-integration.php">
     	 <div class="col-sm-1 col-md-4 box-intro">
        	 <span aria-hidden="true" class="glyphicon glyphicon-link"></span>
        	<h3>Social</h3>
        	<p>Integrazione con Rss, Facebook, GCalendar, Joomla, WordPress</p>
      	</div>
	</a>
	
	<a href="https://www.youtube.com/channel/UCo1XZkyDWhTW5Aaoo672HBA">
     	 <div class="col-sm-1 col-md-4 box-intro">
        	 <span aria-hidden="true" class="glyphicon glyphicon-facetime-video"></span>
        	<h3>YouTube</h3>
        	<p>I video per facilitare l'apprendimento di PortAlGas</p>
      	</div>
	</a>

	<a href="use-case.php">
     	 <div class="col-sm-1 col-md-4 box-intro">
        	 <span aria-hidden="true" class="glyphicon glyphicon-random"></span>
        	<h3>Casi d'uso</h3>
        	<p>Esempi pratici per gestire al meglio i propri ordini</p>
      	</div>
	</a>
		
</div> <!-- row -->
</div> <!-- container -->


<div class="container">
	
	<div class="col-sm-2 box-intro-ico">
		<a target="_blank" href="https://itunes.apple.com/us/app/portalgas/id1133263691">
		<img title="vai allo store di Itunes" src="https://www.portalgas.it/images/appstore.png" border="0"></a>
	</div>
	<div class="col-sm-2 box-intro-ico">
		<a href="https://play.google.com/store/apps/details?id=com.ionicframework.portalgas">
		<img border="0" title="vai allo store di Google" src="https://www.portalgas.it/images/googleplay.png"></a>
	</div>

	<div class="col-sm-2 box-intro-ico">
		<a target="_blank" href="https://facebook.com/portalgas.it"><img src="https://www.portalgas.it/images/cake/ico-social-fb.png" alt="PortAlGas su facebook" title="PortAlGas su facebook" border="0"> Facebook</a>
	</div>
	<div class="col-sm-2 box-intro-ico">
						<a target="_blank" href="https://www.youtube.com/channel/UCo1XZkyDWhTW5Aaoo672HBA"><img src="https://www.portalgas.it/images/cake/ico-social-youtube.png" alt="PortAlGas su YouTube" title="PortAlGas su YouTube" border="0"> YouTube</a>
	</div>
	<div class="col-sm-2 box-intro-ico">
						<a target="_blank" href="https://www.portalgas.it/mobile"><img src="https://www.portalgas.it/images/cake/ico-mobile.png" alt="PortAlGas per tablet e mobile" title="PortAlGas per tablet e mobile" border="0"> Mobile</a>
	</div>
    
</div>
  
  
<div class="footer" role="contentinfo">
	<div class="copyright">Copyright &copy; <?php echo date('Y');?> PortAlGas. All Rights Reserved.</div>
</div> 

    <script src="js/jquery-1.11.2.min.js"></script>
    <script src="js/bootstrap.min.js"></script>

  </body>
</html>