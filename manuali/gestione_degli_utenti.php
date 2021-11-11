<?php require('_inc_header.php');?>
 
    <div class="container">
      


        <div class="col-sm-8 cakeContainer" role="main">

		<h1 id="la-gestione-degli-utenti-in-portalgas" class="page-header">La gestione degli utenti in PortAlGas</h1>
		
		<p>PortAlGas gestisce i seguenti ruoli</p>
		
<p>
<div class="table-responsive">
   <table class="table table-bordered">
		<thead>
			<tr>
				<th>Ruolo</th>
				<th>Nota</th>
				<th>Abilitato</th>
			</tr>
		</thead>   
		<tbody>
			<tr>
				<td>Manager</td>
				<td>Ha la gestione del G.A.S.
					<ul>
						<li>utenti</li>
						<li>categorie degli articoli</li>
					</ul>
				</td>
				<td>Tutti</td>
			</tr>
			<tr>
				<td>Tesoriere</td>
				<td>Ha la gestione dei pagamenti ai fornitori</td>
				<td>G.A.S. configurati con il pagamento <strong>dopo</strong> la consegna</td>
			</tr>
			<tr>
				<td>Manager consegne</td>
				<td>Ha la gestione delle consegne</td>
				<td>Tutti</td>
			</tr>
			<tr>
				<td>Referente</td>
				<td>Ha la gestione dei <strong>solo</strong> produttori associati</td>
				<td>Tutti</td>
			</tr>
			<tr>
				<td>Super-Referente</td>
				<td>Ha la gestione di <strong>tutti</strong> i produttori</td>
				<td>Tutti</td>
			</tr>
			<tr>
				<td>Cassiere</td>
				<td>Ha la gestione, durante la consegna, degli ordini di tutti i produttori<br />Gestisce il modulo Cassa</td>
				<td>G.A.S. configurati con il pagamento <strong>alla</strong> la consegna</td>
			</tr>
			<tr>
				<td>Referente-Cassiere</td>
				<td>Ha la gestione, durante la consegna, degli ordini dei produttori associati</td>
				<td>G.A.S. configurati con il pagamento <strong>alla</strong> la consegna</td>
			</tr>
			<tr>
				<td>Referente-Tesoriere</td>
				<td>Ha la gestione dopo la consegna dei pagamenti dei produttori associati</td>
				<td>G.A.S. configurati con il pagamento <strong>dopo</strong> la consegna</td>
			</tr>
			<tr>
				<td>Gruppo generico</td>
				<td>Per raggruppare utenti per uso statistico</td>
				<td>Tutti</td>
			</tr>
			<tr>
				<td>Gruppo attività calendario</td>
				<td>Ha la gestione delle attività del calendario</td>
				<td>Tutti</td>
			</tr>
		</tbody>
	</table>
</div>
</p>

		<h1 id="il-manager-del-gas" class="page-header">Il Manager del G.A.S.</h1>
				
		<p>Solo il Manager del G.A.S. potrà accedere al modulo per gestire tutti i ruoli degli utenti del proprio G.A.S.</p>
		
		<p>Per accedere al modulo, dal menù superiore, cliccare su "Nome del G.A.S." => "Ruoli"
		
		<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/utenti-menu-ruoli.png" class="img-responsive"></a>

		<p>Si presenterà l’elenco di tutti i ruoli per cui il G.A.S. è abilitato</p>
		
		<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/utenti-elenco.ruoli.png" class="img-responsive"></a>
		


<p>Di seguito le voci di menù al quale un utente con ruolo di <strong>Manager</strong> del G.A.S. potrà accedere:</p>
<p>
<ul>
	<li>Il mio profilo</li>
	<li>Gestione completa</li>
	<li>Nuovo utente</li>
	<li>Visualizzazione rapida</li>
	<li>Importa utenti</li>
	<li>Ruoli</li>
</ul>


		<!-- idem problemi.php#problemi-registrazione-portalgas --> 
		<h1 id="registrazione-utenti" class="page-header">Registrazione utenti</h1>

			<p>
			Per effettuare una corretta registrazione, questi sono i punti da seguire:
			</p>
			<p>
			<ul>
				<li>Contattare personalmente il GAS a quale desiderate aderire</li>
				<li>Registratevi alla pagina <a target="_blank" href="http://www.portalgas.it/registrati">www.portalgas.it/registrati</a></li>
				<li>PortAlGas invierà una <b>mail</b> per confermare la mail inserita durante la registrazione, leggere <a href="http://manuali.portalgas.it/problemi.php#problemi-con-le-mail-di-portalgas">problemi con le mail di PortAlGas</a></li>
				<li>Cliccando sulla mail ricevuta si confermerà la mail inserita durante la registrazione (l'utenza viene così <b>attivata</b>).</li>
				<li>Il manager del GAS dovrà <b>abilitarvi</b> per permettervi di accedere e verrà inviata un'altra <b>mail</b> con l'avvenuta abilitazione</li>
				<li>Accedete alla sito <a target="_blank" href="http://www.portalgas.it/registrati">www.portalgas.it</a></li>
			</ul>
			</p>

<h1 class="page-header" id="importa-utenti">Importa utenti</h1>

<p>Questo modulo è disponibile solamente per gli utenti con ruolo di <strong>Manager</strong> del G.A.S.</p>

<p>Cliccando su questa voce di menù si accede al modulo per importare con un file con estensione csv gli utenti in PortAlGas.</p>

<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/utenti-importa.png" class="img-responsive"></a>

<p>Il file dev'essere formattato nel modo che segue</p>

<p>
<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/utenti-file-csv.png" class="img-responsive"></a>
</p>

<div class="alert alert-info" role="alert">
	<strong>Nota: </strong> per evitare problemi di carico su PortAlGas, sono consentiti file con un massimo di <strong>80</strong> righe.
</div>


		<h1 id="la-gestione-dei-referenti" class="page-header">La gestione dei referenti</h1>
				
		<p>Solo i Referente e i Co-Referenti del G.A.S. e il Super-Referente potranno al modulo per gestire l'associazione tra</p>
		
		<p>
		<ul>
			<li>utenti</li>
			<li>produttori</li>
		</ul>
		</p>
		
		<p>Per accedere al modulo, dal menù superiore, cliccare su "Referenti" => "Referenti"
		
		<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/utenti-menu-referenti.png" class="img-responsive"></a>

		<p>Si presenterà l’elenco di tutti gli utenti associati ai produttori con il ruolo di referente</p>

		<div class="alert alert-info" role="alert">
			<strong>Nota: </strong> per la differenza tra referente e co-referente  <a href="front-end.php#la-voce-di-menu-consegne">leggi qui</a>
		</div>
		
		
		
		<h1 id="il-referente-dei-produttori" class="page-header">Il referente dei produttori</h1>
				
		<p>Per creare o modificare un referente leggi su <a href="#la-gestione-dei-referenti">La gestione dei referenti</a></p>
		
		<p>Il referente potrà effettuare le seguenti operazioni rispetto al produttore/i di cui è referente</p>
	
		<p>
		<ul>
			<li>modifare l'anagrafica del produttore, si legga <a href="/gestione_dei_produttori.php#anagrafica-del-produttore">anagrafica del produttore</a></li>
			<li>gestire gli articoli</li>
			<li>gestire l'ordine durante tutto il suo ciclo</li>
			<li>gestire gli acquisti effettuati dai gasisti</li>
			<li>visualizzare le consegne e richiedere al "Manager delle consegne" l'apertura di una nuova consegna</li>
		</ul>
		</p>
			
		<p>La gestione delle consegne è abilitare per il Manager delle consegne</p>
			
			

							
		</div> <!-- col-sm-8 -->
		
		<div class="col-sm-3" role="complementary">

				<ul id="myScrollspy" class="nav nav-contenitore nav-stacked affix-top hidden-print hidden-xs hidden-sm" data-spy="affix" data-offset-top="125">
					<li class="active"><a href="#la-gestione-degli-utenti-in-portalgas">La gestione degli utenti in PortAlGas</a></li>
					<li><a href="#il-manager-del-gas">Il Manager del G.A.S.</a></li>
					<li><a href="#registrazione-utenti">Registrazione utenti</a></li>
					<li><a href="#importa-utenti">Importa utenti</a></li>
					<li><a href="#la-gestione-dei-referenti">La gestione dei referenti</a></li>
					<li><a href="#il-referente-dei-produttori">Il referente dei produttori</a></li>
				</ul>
		
		
		</div> <!-- col-sm-3 -->
	
	
	</div>	  <!-- container -->
  

<?php require('_inc_footer.php');?>