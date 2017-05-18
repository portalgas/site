<?php 
// No direct access.
defined('_JEXEC') or die;

$app				= JFactory::getApplication();
$doc				= JFactory::getDocument();
$templateparams		= $app->getTemplate(true)->params;
$organizationSEO    = $templateparams->get('organizationSEO');

//$doc->addStyleSheet($this->baseurl.'/templates/'.$this->template.'/css/default-min.css');

$i=0;
$arrayServices[$i]['group'] = '';
$arrayServices[$i]['icon'] = 'fa-university';
$arrayServices[$i]['title'] = "Il tuo G.A.S.";
$arrayServices[$i]['text'] = "La tua Pagina sul Sito<ul><li>sotto-dominio: <b>miogas</b>.portalgas.it</li><li>una home page (pagina pubblica)</li><li>la pagina  delle consegne (pagina pubblica)</li><li>la pagina  degli acquisti (pagina accessibile solo dopo autenticazione)</li><li>la pagina  delle stampe (pagina accessibile solo dopo autenticazione)</li><li>a pagina  della mappa di gmaps dei propri gasisti (pagina accessibile solo dopo autenticazione)</li></ul>";
$i++;
$arrayServices[$i]['group'] = '';
$arrayServices[$i]['icon'] = 'fa-calendar';
$arrayServices[$i]['title'] = "Consegne";
$arrayServices[$i]['text'] = "Imposta la data e il luogo della consegna. Possibilità di aggiungere delle note alla Consegna e di definire il tipo di Evidenza da dare alla Nota.";
$i++;
$arrayServices[$i]['group'] = '';
$arrayServices[$i]['icon'] = 'fa-archive';
$arrayServices[$i]['title'] = "Ordini";
$arrayServices[$i]['text'] = "Ogni referente potrà creare i propri ordini e monitorare il loro ciclo completo, dalla Creazione alla gestione sia durante che dopo la consegna. Ogni ordine avrà associati gli articoli validi per quello specifico ordine.";
$i++;
$arrayServices[$i]['group'] = '';
$arrayServices[$i]['icon'] = 'fa-cubes';
$arrayServices[$i]['title'] = "Articoli";
$arrayServices[$i]['text'] = "Gestione Articoli, Filtro Articoli (per categoria; produttore; per tipologia Articolo: biodinamici;biologici; vegetariani; vegani; per celiaci). Gestione delle immagini per gli articoli (ogni articolo può essere corredato di una foto che illustra il prodotto) Modifica Rapida Articoli.";


$i++;
$arrayServices[$i]['group'] = '';
$arrayServices[$i]['icon'] = 'fa-envelope-o';
$arrayServices[$i]['title'] = "Invio delle mail";
$arrayServices[$i]['text'] = "Tutti i gasisti riceveranno le mail per notificare l'apertura o la chiusura degli ordini. I referenti potranno inviare mail a tutti i gasisti o a solo quelli del proprio ordine, inserendo anche gli allegati. I Referenti riceveranno le mail automatiche di controllo dei propri ordini gestiti.";



$i++;
$arrayServices[$i]['group'] = '';
$arrayServices[$i]['icon'] = 'fa-money';
$arrayServices[$i]['title'] = "Pagamento alla consegna";
$arrayServices[$i]['text'] = "Il ruolo del Cassiere:  potrà gestire i pagamenti alla consegna della merce";
$i++;
$arrayServices[$i]['group'] = '';
$arrayServices[$i]['icon'] = 'fa-credit-card';
$arrayServices[$i]['title'] = "Pagamento dopo consegna";
$arrayServices[$i]['text'] = "Il ruolo del Tesoriere:  potrà gestire i pagamenti dopo la consegna creando le richieste di pagamento da inviare ai gasisti e ne monitorizza i pagamenti";
$i++;
$arrayServices[$i]['group'] = '';
$arrayServices[$i]['icon'] = 'fa-magic';
$arrayServices[$i]['title'] = "Pagamento Misto<br />(durante e post consegna)";
$arrayServices[$i]['text'] = "Se Cassiere e Tesoriere sono entrambi ruoli necessari, per gestire entrambi questa è la soluzione più completa per risolvere le modalità di pagamento di tutti gli ordini.";

/*
 *  funzionalita sugli ordni
 */
$i++;
$arrayServices[$i]['group'] = '';
$arrayServices[$i]['icon'] = 'fa-shopping-cart';
$arrayServices[$i]['title'] = "Carrello";
$arrayServices[$i]['text'] = "Controlla gli acquisti di ogni ordine e come referente potrai modificare gli acquisti prima e dopo la consegna.";
$i++;
$arrayServices[$i]['group'] = '';
$arrayServices[$i]['icon'] = 'fa-th-list';
$arrayServices[$i]['title'] = "Dati aggregati per utenti";
$arrayServices[$i]['text'] = "Aggregando gli acquisti per ogni gasista (qualora si volesse gestire solo un importo totale per ogni gasista)";
$i++;
$arrayServices[$i]['group'] = '';
$arrayServices[$i]['icon'] = 'fa-share-alt';
$arrayServices[$i]['title'] = "Dati suddivisi per quantit&agrave;";
$arrayServices[$i]['text'] = "Suddividendo gli acquisti per ogni gasista (qualora si volesse gestire l'importo di ogni singola quantit&agrave; acquistata per ogni gasista)";
$i++;
$arrayServices[$i]['group'] = '';
$arrayServices[$i]['icon'] = 'fa-minus-circle';
$arrayServices[$i]['title'] = "Applica uno sconto";
$arrayServices[$i]['text'] = "Se il produttore vi applica un sconto, scegliete come suddividerlo tra i gasisti che hanno effettuato acquisti.";
$i++;
$arrayServices[$i]['group'] = '';
$arrayServices[$i]['icon'] = 'fa-plus-circle';
$arrayServices[$i]['title'] = "Spese aggiuntive";
$arrayServices[$i]['text'] = "Se si presentano delle spese aggiuntive, scegliete come suddividerlo tra i gasisti che hanno effettuato acquisti.";
$i++;
$arrayServices[$i]['group'] = '';
$arrayServices[$i]['icon'] = 'fa-truck';
$arrayServices[$i]['title'] = "Trasporto";
$arrayServices[$i]['text'] = "Se l'ordine include il trasporto, scegliete come suddividerlo tra i gasisti che hanno effettuato acquisti.";
$i++;
$arrayServices[$i]['group'] = '';
$arrayServices[$i]['icon'] = 'fa-sliders';
$arrayServices[$i]['title'] = "Pezzi di una confezione";
$arrayServices[$i]['text'] = "Indica di quanti elementi è composto un collo. Il referente può gestirli modificando le quantità o riaprendo l'ordine solo per gli articoli con i colli incompleti.";
$i++;
$arrayServices[$i]['group'] = '';
$arrayServices[$i]['icon'] = 'fa-puzzle-piece';
$arrayServices[$i]['title'] = "Bancale";
$arrayServices[$i]['text'] = "Gestisci una quantità massima rispetto a tutti gli acquisti: quando verrà raggiunta tale quantità:<ul><li>l'acquisto sull'articolo sarà bloccato</li><li>PortAlGas invierà una mail ai referenti</li></ul>";
$i++;
$arrayServices[$i]['group'] = '';
$arrayServices[$i]['icon'] = 'fa-lock';
$arrayServices[$i]['title'] = "Antiaccaparramento";
$arrayServices[$i]['text'] = "Quando la disponibilità del prodotto è limitata, gestisci una quantità massima ordinabile per ogni gasista.";
$i++;
$arrayServices[$i]['group'] = '';
$arrayServices[$i]['icon'] = 'fa-tachometer';
$arrayServices[$i]['title'] = "Monitoraggio";
$arrayServices[$i]['text'] = "Il Manager del Gas può tenere “sotto controllo” ordini particolari oppure difficili con un monitoraggio degli stessi.<br />Ogni referente può recuperare i dati dell'ordine del <b>backup</b> notturno";				









$i++;
$arrayServices[$i]['group'] = '';
$arrayServices[$i]['icon'] = 'fa-file-pdf-o';
$arrayServices[$i]['title'] = "Stampe";
$arrayServices[$i]['text'] = "Esporta in pdf, csv o excel. Numerosi report dell'ordine<ul><li>per articolo</li><li>per utente</li><li>per il produttore</li><li>per il tesoriere</li><li>per il cassiere</li><li>per monitorare i colli</li><li>per monitorare le quantità massime e le quantità minime</li><li>per utente in modalità etichetta</li></ul><br />Diversi report generici:<ul><li>elenco anagrafica gasisti</li><li>elenco anagrafica gasisti presenti alla consegna</li><li>elenco articoli di un produttore</li></ul>";



$i++;
$arrayServices[$i]['group'] = '';
$arrayServices[$i]['icon'] = 'fa-road';
$arrayServices[$i]['title'] = "Km0";
$arrayServices[$i]['text'] = "Quanti km ha fatto la tua spesa? Ogni gasista potr&agrave; sempre conoscere quanti km ha percorso la propria merce per giungere fino a lui.";
$i++;
$arrayServices[$i]['group'] = '';
$arrayServices[$i]['icon'] = 'fa-map-marker ';
$arrayServices[$i]['title'] = "GMaps";
$arrayServices[$i]['text'] = "Localizzando i gasisti e i produttori si trova tutto pi&ugrave; facilmente.";






$i++;
$arrayServices[$i]['group'] = '';
$arrayServices[$i]['icon'] = 'fa-users';
$arrayServices[$i]['title'] = "Utenti";
$arrayServices[$i]['text'] = "Anagrafica di tutti i gasisti e gestione dei ruoli: manager del GAS, referenti, co-referenti, gasisti, cassiere, tesoriere, sono questi alcuni dei ruoli che permette di organizzare al meglio il vostro lavoro.";
$i++;
$arrayServices[$i]['group'] = '';
$arrayServices[$i]['icon'] = 'fa-user';
$arrayServices[$i]['title'] = "Referenti";
$arrayServices[$i]['text'] = "Il Referente è il punto centrale di un gas e PortAlGas è creato attorno a questo ruolo fondamentale. Associa ad ogni produttore uno o più referente e co-referenti. Il referente potrà gestire in modo autonomo il ciclo completo di un ordine.";
$i++;
$arrayServices[$i]['group'] = '';
$arrayServices[$i]['icon'] = 'fa-cloud';
$arrayServices[$i]['title'] = "Produttori";
$arrayServices[$i]['text'] = "Anagrafica centralizzata dei produttori permette di condividerli tra i diversi GAS della rete di PortAlGas";



$i++;
$arrayServices[$i]['group'] = '';
$arrayServices[$i]['icon'] = 'fa-bar-chart ';
$arrayServices[$i]['title'] = "Statistiche";
$arrayServices[$i]['text'] = "Statistiche delle consegne, degli ordini, dei produttori, di tutti i relativi acquisti per monitorare l'andamento del tuo GAS";
$i++;
$arrayServices[$i]['group'] = '';
$arrayServices[$i]['icon'] = 'fa-database';
$arrayServices[$i]['title'] = "Server";
$arrayServices[$i]['text'] = "Non dovrai installare niente ne occuparti degli aggioramenti o dei backup. PortalGas risiede su di un server Virtuale e grazie ad una configurazione modulare crea una istanza per ogni singolo GAS che si unisce.";
$i++;
$arrayServices[$i]['group'] = '';
$arrayServices[$i]['icon'] = 'fa-edit';
$arrayServices[$i]['title'] = "Test";
$arrayServices[$i]['text'] = "E' prevista un area di test per poter effettuare in tutta sicurezza le prove per poter prendere dimestichezza con le diverse funzionalità.";
$i++;
$arrayServices[$i]['group'] = '';
$arrayServices[$i]['icon'] = 'fa-life-ring';
$arrayServices[$i]['title'] = "Manuali";
$arrayServices[$i]['text'] = "Un manuale approfondito e sempre in aggiornamento sarà sicuramente utile per muoversi tra i moduli di PortAlGas.";			
?>		
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" >
<head>
<jdoc:include type="head" />

    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

	<script src="<?php echo $this->baseurl ?>/templates/v01/javascript/jquery-1.11.2.min.js"></script>
	<script src="<?php echo $this->baseurl ?>/templates/v01/javascript/bootstrap.min.js"></script>
        
	<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/v01/css/bootstrap.min.css">
	<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/v01/css/font-awesome.min.css">
	

	<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/onepage/css/default01.css">
	
	
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->


<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-48245560-1', 'portalgas.it');
  ga('send', 'pageview');
</script>
</head>



<body id="page-top" class="index">

    <nav class="navbar navbar-default navbar-fixed-top">
        <div class="container">
            <div class="navbar-header page-scroll">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="/">PortAlGas, entra in rete</a>
            </div>

            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav navbar-right">
                    <li class="hidden">
                        <a href="#page-top"></a>
                    </li>
                    <li class="page-scroll">
                        <a href="#idea">L'idea</a>
                    </li>
                    <li class="page-scroll">
                        <a href="#a-chi-si-rivolge">A chi si rivolge</a>
                    </li>
                    <li class="page-scroll">
                        <a href="#che-cosa-fa">Che cosa fa</a>
                    </li>
                    <li class="page-scroll">
                        <a href="#device">I dispositivi</a>
                    </li>
                    <li class="page-scroll">
                        <a href="#contact">Scrivici</a>
                    </li>
                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </div>
        <!-- /.container-fluid -->
    </nav>

    <header>
        <div class="container" style="padding-top: 125px;">
            <div class="row">
                <div class="col-lg-12">
		    
						<a href="/"><div class="cerchio-logo"></div></a>

						<div class="intro-text">
							<span class="name">PortAlGas</span>
							<hr class="star-light">
							<span class="skills">Il gestionale Web per i GAS - gruppi d'acquisto solidale</span>
						</div>
				</div>
			</div>
        </div>
    </header>




   <section id="idea">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2>L'idea di PortAlGas</h2>
                    <hr class="star-primary">
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4 col-lg-offset-2">
                    <p>L'idea di PortAlGas nasce dalla nostra esperienza oramai pluriennale come gasisti: ci siamo posti l'obiettivo di realizzare un applicativo che fosse in grado di dare una risposta a tutte le esigenze che non era stato possibile soddisfare con gli strumenti fino a quel momento utilizzati.</p>
                </div>
                <div class="col-lg-4">
                    <p>Quel progetto è finalmente diventato una realtà: grazie al Gas La Cavagnetta di cui siamo soci, abbiamo iniziato a fare i primi ordini con il nuovo software e a provare tutte le sue nuove funzionalità.</p>
					
					<button type="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" class="btn btn-primary">continua</button>
                </div>
            </div>
        </div>


		<div id="collapseOne" class="row panel-collapse collapse">
			<div class="col-lg-6  col-lg-offset-3 text-center">
				<p>Può essere usato da tutti i Gas: da quelli piccolissimi abilitando la gestione semplificata, a Gas medi e grandi tramite una gestione Modulare con suddivisione dei compiti più capillare e strutturata, abilitando quando necessario diverse funzionalità in base alle proprie esigenze.</p>
			</div>
		</div>

			
    </section>




    <section id="a-chi-si-rivolge" class="success">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2>A chi si rivolge PortAlGas?</h2>
                    <hr class="star-light">
                </div>
            </div>



                <div class="col-sm-4 portfolio-item">
				
					<div class="box box-gas">
						<img src="<?php echo $this->baseurl ?>/templates/onepage/images/gas-piccolo.jpg"/>   
						<div class="mask">
							<h2>Ai Gas piccoli</h2>
							<p>Siete un gruppo di amici e fare tutto per mail è una modalità che avete utilizzato all'inizio ma pian piano è diventata una pratica ingestibile?<br />Vi serve un semplice gestionale con poche funzionalità?</p>
							<!-- a class="info" href="#contact">Scrivici</a -->
						</div>
					</div>
								
                </div>



                <div class="col-sm-4 portfolio-item">
				
					<div class="box box-gas">
						<img src="<?php echo $this->baseurl ?>/templates/onepage/images/gas-medio.jpg"/>   
						<div class="mask">
							<h2>Ai GAS di media dimensione</h2>
							<p>Siete un G.A.S. tra i 20 e 60 iscritti? Avete già una vostra organizzazione che poco si adatta a rigidi strumenti informatici? PortAlGas, con la sua gestione modulare può essere la soluzione per voi!</p>
							<!-- a class="info" href="#contact">Scrivici</a -->
						</div>
					</div>					

                </div>




                <div class="col-sm-4 portfolio-item">
				
					<div class="box box-gas">
						<img src="<?php echo $this->baseurl ?>/templates/onepage/images/gas-grande.jpg"/>   
						<div class="mask">
							<h2>Ai GAS grandi</h2>
							<p>Siete un G.A.S. di grandi dimensioni? La complessità della vostra realtà potrà mettere a dura prova la capacità di PortAlGas di adattarsi, proviamoci!</p>
							<!-- a class="info" href="#contact">Scrivici</a -->
						</div>
					</div>	

				</div>






            </div>
         </div>
    </section>








    <section id="che-cosa-fa" class="services">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2>Cosa fa PortAlGas</h2>
                    <hr class="star-primary">
                </div>
            </div>

			<div class="row">
				<?php
				$group_old = '';
				foreach($arrayServices as $numResults => $arrayService) {
					if(!empty($arrayService['group']) && !empty($group_old) && $group_old != $arrayService['group']) {
					?>
					    <section>
							<div class="container">
								<div class="row">
									<div class="col-lg-12 text-center">
										<h2><?php echo $arrayService['group'];?></h2>
										<hr class="star-primary">
									</div>
								</div>
							</div>
						</section>
					<?php
					}
					echo "\r\n";
					echo '<div class="col-md-3 col-sm-6">';
					echo '	<div class="service-item">';
					echo '		<span class="fa-stack fa-4x">';
					echo '			<i class="fa fa-circle fa-stack-2x"></i>';
					echo '			<i class="fa '.$arrayService['icon'].' fa-stack-1x text-primary"></i>';
					echo '		</span>';
					echo '		<p class="title">'.$arrayService['title'].'</p>';
					
					echo '		<div class="description-wrap">';
					echo '			<div class="service-description">';
					echo '				<div class="m-arrow-wrap"><div class="m-arrow"></div></div>';
					echo '				<p>'.$arrayService['text'].'</p><div class="clearfix"></div>';
					echo '			</div>';
					echo '		</div>';
					
					echo '	</div>';
					echo '</div>';
					
					$group_old = $arrayService['group'];
				}		
				?>
 
            </div>

        </div>
    </section>






	<div class="content-section-a">

        <div class="container">
            <div class="row">
                <div class="col-lg-5 col-sm-6">
                    <hr class="section-heading-spacer">
                    <div class="clearfix"></div>
                    <h2 class="section-heading">Amministrazione</h2>
                    <p class="lead">Un area di PortAlGas &egrave; protetta da autenticazione e permette a chi autorizzato di gestire da un qualsiasi pc il ciclo completo dei propri ordini</p>
                </div>
                <div class="col-lg-5 col-lg-offset-2 col-sm-6">
                    <img alt="" src="<?php echo $this->baseurl ?>/templates/onepage/images/desktop.png" class="img-responsive">
                </div>
            </div>

        </div>
        <!-- /.container -->

    </div>







	<div class="content-section-b" id="device">

        <div class="container">

		    <div class="row">
                <div class="col-lg-5 col-sm-6">
                    <hr class="section-heading-spacer">
                    <div class="clearfix"></div>
                    <h2 class="section-heading">PortAlGas mobile</h2>
                    <p class="lead">Non poteva mancare la versione per smartphone: tieni sotto controllo le consegne aperte, effettua i tuoi acquisti ovunque tu sia!</p>
                    <p class="lead">Integrazione con la vostra pagina Facebook e con Google Calendar!</p>
                </div>
                <div class="col-lg-5 col-lg-offset-2 col-sm-6">
                    <img alt="" src="<?php echo $this->baseurl ?>/templates/onepage/images/phones.jpg" class="img-responsive">
                </div>
            </div>

        </div>
        <!-- /.container -->

    </div>




	<div class="content-section-a">

        <div class="container">

            <div class="row">
                <div class="col-lg-5 col-lg-offset-1 col-sm-push-6  col-sm-6">
                    <hr class="section-heading-spacer">
                    <div class="clearfix"></div>
                    <h2 class="section-heading">Per i gasisti</h2>
                    <p class="lead">Accedi da qualsiasi device per effettuare gli acquisti sugli ordini aperti.<br />Stampa il tuo carello, cerca i gasisti con gmaps, visualizza gli articoli dei produttori</p>
                </div>
                <div class="col-lg-5 col-sm-pull-6  col-sm-6">
                    <img alt="" src="<?php echo $this->baseurl ?>/templates/onepage/images/tablet.jpg" class="img-responsive">
                </div>
            </div>

        </div>
        <!-- /.container -->

    </div>








    <section class="success" id="about">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2>Cosa aspetti</h2>
                    <hr class="star-light">
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4 col-lg-offset-2">
                    <p>Le cose che fa sono tante e sempre in evoluzione, spiegarle tutte non &egrave; facile, provare &egrave; facilissimo!
						<ul class="list-inline">
                            <li>
                                <a class="btn-social btn-outline" href="https://www.facebook.com/pages/Portalgas/677581532361100" target="_blank"><i class="fa fa-fw fa-facebook"></i></a>
                            </li>
                            <li>
                                <a class="btn-social btn-outline" href="https://www.youtube.com/channel/UCo1XZkyDWhTW5Aaoo672HBA" target="_blank"><i class="fa fa-fw fa-youtube"></i></a>
                            </li>
                        </ul>					
					</p>
                </div>
                <div class="col-lg-4">
                    <p>Scrivici e presenta il tuo GAS, raccontaci quanti siete, che modalità di pagamento utilizzate, quali sono le vostre esigenze e saremo contenti di allargare la rete di PortAlGas</p>
                </div>
                <div class="col-lg-8 col-lg-offset-2 text-center">
                    <a title="scarica la presentazione del gestionale per gruppi d'acquisto solidate PortAlGas" href="http://www.portalgas.it/images/manuali/presentazione-slide.pdf" target="_blank" class="btn btn-lg btn-outline">
                        <i class="fa fa-globe"></i> scarica il pdf
                    </a>
                </div>
            </div>
        </div>
    </section>

	<section id="contact">
		<jdoc:include type="component" />
	</section>
	
<footer>

	<div class="footer-above">
		<div class="container">
			<div class="row">
				<div class="footer-col col-md-3 col-xs-12 col-sm-6 text-left">

					<ul class="social">
						<li>
							<a target="_blank" href="https://www.facebook.com/pages/Portalgas/677581532361100"><img border="0" src="/images/cake/ico-social-fb.png" alt="PortAlGas su facebook" title="PortAlGas su facebook"> Facebook</a>
						</li>
						<li>
							<a target="_blank" href="https://www.youtube.com/channel/UCo1XZkyDWhTW5Aaoo672HBA"><img border="0" src="/images/cake/ico-social-youtube.png" alt="PortAlGas su YouTube" title="PortAlGas su YouTube"> YouTube</a>
						</li>
						<li>
							<a target="_blank" href="/mobile"><img border="0" src="/images/cake/ico-mobile.png" alt="PortAlGas per tablet e mobile" title="PortAlGas per tablet e mobile"> Mobile</a>
						</li>
					</ul>			
				
				</div>
				<div class="footer-col col-md-3 col-xs-12 col-sm-6 text-center">

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
			</div>
		</div>
	</div>


	<div class="footer-below">
        <div class="container">
                <div class="row">
                    <div class="col-lg-12 text-center">
						Copyright &copy; 2015 PortAlGas. All Rights Reserved.
						<span class="pull-right">
							<a href="mailto:info@portalgas.it" title="Scrivi una mail a info@portalgas.it">info@portalgas.it</a>
						</span>
                    </div>
                </div>
        </div>
	</div>
</footer>

    <div class="scroll-top page-scroll visible-xs visble-sm">
        <a class="btn btn-primary" href="#page-top">
            <i class="fa fa-chevron-up"></i>
        </a>
    </div>


    <script src="http://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js"></script>
    <script src="<?php echo $this->baseurl ?>/templates/onepage/javascript/classie.js"></script>
    <script src="<?php echo $this->baseurl ?>/templates/onepage/javascript/cbpAnimatedHeader.js"></script>


    <script src="<?php echo $this->baseurl ?>/templates/onepage/javascript/jqBootstrapValidation.js"></script>
    <script src="<?php echo $this->baseurl ?>/templates/onepage/javascript/default.js"></script>

</body>

</html>
