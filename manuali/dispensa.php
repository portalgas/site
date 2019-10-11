<?php require('_inc_header.php');?>
 
<div class="container">

<div class="col-sm-8 cakeContainer" role="main">

		<h1 class="page-header" id="la-dispensa-di-portalgas">La dispensa di PortAlGas</h1>

		<p>Il modulo dispensa permette di
			<p>
				<ul>
					<li>ad ogni <b>referente</b>, di inserire in dispensa articoli dei propri produttori</li>
					<li>ad ogni <b>gasista</b>, di prenotare gli articoli in dispensa ritirandoli ad una data consegna</li>
					<li>ad ogni <b>referente</b> durante la gestione di un ordine, di associare all'utente dispensa articoli dei propri produttori</li>
					<li>ad ogni <b>referente</b> durante la gestione dei colli, di associare all'utente dispensa gli articoli interessati</li>
					<li>al <b>cassiere</b>, di gestire il pagamento</li>
					<li>al <b>tesoriere</b>, di gestire la richiesta di pagamento</li>
				</ul>
			</p>
		</p>
		
		<p>Per abilitare il modulo dispensa bisogna contattare i gestori di PortAlGas all'indirizzo <a href="mailto:contatti@portalgas.it">contatti@portalgas.it</a>:</p>
		<p>
		<ul>
			<li>verrà abilitato il modulo</li>
			<li>verrà creato un utenza per gestire la dispensa, per esempio dispensa@miogas.portalgas.it</li>
		</ul>
		</p>
		<p>
			<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/storeroom-menu.png" /></a>
		</p>
		
		<p>Esistono 2 diverse modalità:
		<ul>
			<li>Dispensa semplice</li>
			<li>Dispensa con possibilità d'acquisto da parte dei gasisti del proprio GAS</li>
		</ul>	
		</p>
		
		<h1 class="page-header" id="la-dispensa-semplice">La dispensa semplice</h1>
		
		<p>Si possono inserire / modificare / visualizzare gli articoli in dispensa</p>
		<p>Si ha così un'anagrafica degli articoli inseriti in dispensa, esportabile con il modulo delle stampe.</p>
		
		<h2 id="eliminare-articolo" class="page-header">Eliminare un articolo</h2>
		
		<p>Se si desidera eliminare un articolo dalla dispensa, accedere alla pagina "Cosa c'è in dispensa" e, in corrispondenza dell'articolo cliccare su <div class="action actionEdit"></div></p>
		
		<p>Impostare la colonna "Modifica quantità in dispensa" in corrispondenza dell'articolo evidenziato il giallo la quantità 0 (zero)</p>
		
		<p>Salvando l'articolo verrà eliminato dalla dispensa</p>
		
		<div role="alert" class="alert alert-info">
			<strong>Nota: </strong> l'utente <b>dispensa</b> potrà modificare tutti gli articoli in dispensa<br/>
			i <b>referenti</b> potranno modificare solo gli articoli dei produttori di cui sono referenti<br/>
		</div>
		
		
		<h1 class="page-header" id="la-dispensa-con-possibilita-di-acquisto">La dispensa con possibilità di acquisto</h1>
		
		<p>Oltre alle funzionalità della <a href="dispensa.php#la-dispensa-semplice">"dispensa semplice"</a> si permette ai gasiti di effettuare gli acquisti degli articoli in dispensa</p>
		
		<h2 id="configurazione-delle-consegne" class="page-header">Configurazione delle consegne</h2>
		
		<p>Le consegne avranno il <b>flag che abilita o no</b> la gestione della dispensa; solo gli utenti con il <a href="gestione_degli_utenti.php">ruolo "manager dele consegne"</a> sono abilitati a creare e modificare le consegne</p>

		<p>
			<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/storeroom-delivery" /></a>
		</p>
		
		<h2 id="i-referenti" class="page-header">I referenti</h2>
		
		<p>I referenti dei produttori per il quale sono abilitati, potranno</p> 
		<p>
		<ul>
			<li>associare eventuali articoli all'utente dispensa (per esempio dispensa@miogas.portalgas.it)</li>
			<li>inserire articoli in dispensa
			
					<p>
						<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/storeroom-add.png" /></a>
					</p>			
			</li>
		</ul>
		</p>
		</p>

		<p>
			il referente potrà associare eventuali articoli all'utente dispensa utilizzando il 
			<ul>
				<li><a href="moduli.php#modulo-per-la-gestione-degli-acquisti-nel-dettaglio">Modulo per la “gestione degli acquisti nel dettaglio”</a>
				
					<p>
						<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/storeroom-gestione-dettaglio.png" /></a>
					</p>				
				</li>
				<li><a href="moduli.php#modulo-per-la-gestione-dei-colli">Modulo per la “gestione dei colli”</a>				
					<p>
						<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/storeroom-gestione-colli.png" /></a>
					</p>	
				</li>
			</ul>
		</p>
		<p>
			Alla <b>chiusura della consegna</b> gli articoli nel carrello dell'utente dispensa andranno in automatico in dispensa
		</p>
		
		<h2 id="i-gasisti" class="page-header">I gasisti</h2>
		
		<p>I gasisti potranno prenotare gli articoli in dispensa solo per le consegne che gestiscono la dispensa</p>
		
		<p>Un esempio di prenotazione di un articolo da parte di un utente lato front-end</p>
		<p>
			<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/storeroom-fe.png" /></a>
		</p>

		<p>Un esempio di prenotazione di un articolo da parte di un utente lato back-office</p>
		<p>
			<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/storeroom-add-user.png" /></a>
		</p>		
				
</div> <!-- col-sm-8 -->

<div class="col-sm-3" id="myScrollspy" role="complementary">

		<ul class="nav nav-contenitore nav-stacked affix-top hidden-print hidden-xs hidden-sm" data-spy="affix" data-offset-top="125">
			<li><a href="#la-dispensa-di-portalgas">La dispensa di PortAlGas</a></li>
			<li><a href="#la-dispensa-semplice">La dispensa semplice</a>
				<ul class="nav">
					<li><a href="#eliminare-articolo">Eliminare un articolo</a></li>
				</ul>			
			</li>
			<li><a href="#la-dispensa-con-possibilita-di-acquisto">La dispensa con possibilità di acquisto</a>
				<ul class="nav">
					<li><a href="#configurazione-delle-consegne">Configurazione delle consegne</a></li>
					<li><a href="#i-referenti">I referenti</a></li>				
					<li><a href="#i-gasisti">I gasisti</a></li>							
				</ul>		
			</li>
		</ul>


</div> <!-- col-sm-3 -->


</div>	  <!-- container -->
  

<?php require('_inc_footer.php');?>