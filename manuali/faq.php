<?php require('_inc_header.php');?>
 
    <div class="container">
      

        <div class="col-sm-8 cakeContainer" role="main">

		<h1 id="ambienti">Quali sono gli ambienti di PortAlGas?</h1>
		
			<p>il Front End (Dove si fanno gli Ordini, è dove accedono gli Utenti): <a href="http://www.portalgas.it" target="_blank">http://www.portalgas.it</a></p>

			<p>il Front End link diretto: http://<nome gas>.portalgas.it</p>

			<p>il Back End (Dove si Gestisce il Gas e dove si gestiscono Ordini): <a href="http://www.portalgas.it/my" target="_blank">http://www.portalgas.it/my</a></p>

			<p>In Caso di dubbi o incertezze potete leggere il manuale: <a href="http://manuali.portalgas.it" target="_blank">http://manuali.portalgas.it</a></p>

			<p>Esiste un canale di help online per dubbi o emergenze su <b>Telegram</b> cercando Portalgas</p>

			<p>Se ci mettete Mi piace alla nostra pagina <b>FaceBook</b> ci date una mano oltre a restare aggiornati: <a href="https://www.facebook.com/Portalgas-677581532361100/" target="_blank">https://www.facebook.com/Portalgas-677581532361100/</a></p>
			
			<p>Se vi iscrivete al <b>Canale YouTube</b> ci sono i video istruttivi sul nostro nuovo canale YouTube: <a href="https://www.youtube.com/channel/UCo1XZkyDWhTW5Aaoo672HBA" target="_blank">https://www.youtube.com/channel/UCo1XZkyDWhTW5Aaoo672HBA</a>: Piano piano completeremo per tutti i ruoli e attività</p>
			
			<p>Per quanto riguarda eventuali dubbi su privacy ed altro sono qui riassunti: <a href="http://www.portalgas.it/12-portalgas/2-termini-di-utilizzo" target="_blank">http://www.portalgas.it/12-portalgas/2-termini-di-utilizzo</a></p>

		<h1 id="note-tecniche">Quali sono le caratteristiche tecniche di PortAlGas?</h1>

			<p>PortAlGas è installato sul Cloud VPS (Virtual Private Server) presso <a href="https://www.hetzner.com" target="_blank">hetzner</a></p>

			<p>Il sistema operativo è un Ubunbu 18.4 (4 VCPU - 8 GB RAM)</p>

			<p>Application Server Apache 2.x</p>

			<p>Database MariaDB 10.x.x</p>

			<p>Linguaggio di programmazione php 7.4.x</p>

			<p>Sistema di backup giornaliero</p>

			<p>Framework utilizzati: CMS Joomla 2.5.27, CakePhp 2.5.4, CakePhp 3.x.x, Jquery, JqueryUI, Vue Js, Bootstrap per il front-end</p>
			
			<p>Framework utilizzati per la versione app: Ionic, Angular Js</p>
			
		<h1 id="mail-da-portalgas">Quante Mail Arrivano da PortAlGas?</h1>

			<p>Le mail sono cumulative a parità di apertura e di chiusura.</p>

			<p>Una mail prima della consegna ricorda quali sono i produttori in consegna; certo che se si fa una consegna per produttore le mail si moltiplicano.</p>

			<p>E' possibile gestire l'invio delle mail
				<ul>
					<li>da parte del referente, per maggiori informazioni, clicca su <a href="/gestione_dei_produttori.php#modifica-di-un-produttore">Modifica di un produttore</a></li>
					<li>da parte del gasista, per maggiori informazioni, clicca su <a href="/front-end.php#gestione-mail-di-apertura-chiusura-ordini">Gestione mail di apertura/chiusura ordini</a></li>
				</ul>
			</p>

			<p>Le tipologie di mail sono
				<ul>
					<li>Ordini che si aprono oggi ...</li>
					<li>Ordini che si chiudono fa 3 gg...</li>
					<li>Ordini da ritirare alla consegna...</li>
				</ul>
			</p>
			
			<p>Ogni referente può inviare mail ai propri gasisti per  comunicazioni varie.</p>

			<p>Ci sono poi mail di controllo che vedranno solo i manager o i referenti ad esempio 
				<ul>
					<li>Nuovi Utenti che chiedono di iscriversi</li>
					<li>Un articolo sta raggiungendo il limite imposto (bancale)</li>
					<li>Un Ordine si è chiuso dopo l'ottimizzazione dei colli</li>
				</ul>
			</p>

		<h1 id="produttori">Produttori</h1>

		<h2 id="anagrafica">Anagrafica</h2>
		
		<p>E' normale che per alcuni produttori non condivisi (nel senso che li ho inseriti io e prima non c'erano) non riesco ad inserire un'immagine se non nel momento in cui li creo?</p>

		<p>E che in alcuni casi se io vado in modifica mi compare la scritta: Alcuni dati del produttore sono cambiati? Invia una mail ai 'manager dei produttori' per segnalare le variazioni</p>

		<p>Tutte le modifiche le puoi fare al primo inserimento, poi tutte le eventuali altre modifiche dovevano essere segnalate ai "Manager Produttori" ovvero "NOI", questo per controllare cosa è stato inserito i geocode etcc la cosa importante è cercare di inserire il produttore al COMPLETO se possibile. </p>

		<p>Per le parti mancanti si al momento MANDA tutte le variazioni ai Manager Produttori.</p>

		<h1 id="ordini">Ordini</h1>		
		
		<h2 id="aggiungere-un-messaggio-per-i-gasisti">Aggiungere un messaggio per i gasisti</h2>

		<p>Se durante la creazione di un ordine il referente ha la necessità di comunicare qualcosa ai proprio gasisti, di seguito il comportamento corretto.</p>
		
		<p>Esiste un campo "Note" (maggiori informazioni, clicca su <a href="/gestione_degli_ordini.php#il-tab-dei-dati-dell-ordine">Il tab dei dati dell'ordine</a>) che comparirà ai gasisti nel sito di portalgas.it nella voce di menù “Consegne”: il testo inserito nella nota è sempre in ROSSO e si tratta di testo <b>BREVE</b> (un massimo di 50 caratteri) che accompagnerà sempre l'Ordine.</p>
		
		<p><a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/ordini-tab-dati-ordine-campo-nota.jpg" /></a></p>
	
 		<p>Una segnalazione <b>lunga</b> è possibile inserirla nel tab successivo Invio Email (maggiori informazioni, clicca su <a href="/gestione_degli_ordini.php#il-tab-invio-mail">Il tab invio mail</a>).</p>
		
		<p>Il testo verrà incluso nella mail automatica di notifica di apertura ordine</p>

		<p>Per incollare il testo correttamente usa l'inconcina apposita cerchiata di rosso nello screenshot sottostante</p>
		
		<p><a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/ordini-tab-invio-mail1.png" /></a></p>
		
		<p>e otterrai </p>
		
		<p><a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/ordini-tab-invio-mail1.png" /></a></p>
		
		<h2 id="spese-di-trasporto-sconti-o-spese-generiche">Spese di trasporto, sconti o spese generiche</h2>
		
		<p>Dopo che un ordine si e' chiuso ed e' arrivata la fattura del fornitore, il referente deve inserire le spese di trasporto (se ci sono) e poi passare l'ordine al cassiere, giusto?</p>

		<p>Ho visto che solo dopo questo passaggio l'utente vede le spese di trasporto.</p>

		<p>Dato che nel nostro caso il cassiere e' il referente stesso, sara' poi lui a chiudere  definitivamente l'ordine, giusto?</p>

		<p>Quando si chiude un ordine arriva la merce è la fattura solo in quel momento si conosce l'importo esatto del trasporto  (soprattutto se il costo dipende dal peso ) e li si inserisce.</p>

		<p>Certo cambia leggermente i costi ma basta specificare che il trasporto non è compreso e che verrà aggiunto alla consegna.</p>

		<p>Quindi il Referente carica fattura dati di trasporto (deve selezionare la modalità di suddivisione dello stesso) e quindi passa al Cassiere.</p>

		<p>Il vostro caso è un pochino anomalo avendo tutti referenti cassieri cm si cambia cappello e come cassiere effettua la ricezione dl ordine da incassare le stampe eventuali ed incassa segnando gli importi incassati con tutti i conteggi effettuati.</p>

		<p>Quando tutti gli incassi saranno ricevuti ed inseriti l'ordine si chiude automaticamente</p>


		</div> <!-- col-sm-8 -->
		
		<div class="col-sm-3" role="complementary">

				<ul id="myScrollspy" class="nav nav-contenitore nav-stacked affix-top hidden-print hidden-xs hidden-sm" data-spy="affix" data-offset-top="125">
					<li class="active"><a href="#ambienti">Quali sono gli ambienti di PortAlGas?</a></li>
					<li><a href="#note-tecniche">Quali sono le caratteristiche tecniche di PortAlGas?</a></li>
					<li><a href="#mail-da-portalgas">Quante Mail Arrivano da PortAlGas?</a></li>
					<li><a href="#produttori">Produttori</a>
						<ul class="nav"> 
							<li><a href="#anagrafica">Anagrafica</a></li>
						</ul>
					</li>
					<li><a href="#ordini">Ordini</a>
						<ul class="nav"> 
							<li><a href="#aggiungere-un-messaggio-per-i-gasisti">Aggiungere un messaggio per i gasisti</a></li>
							<li><a href="#spese-di-trasporto-sconti-o-spese-generiche">Spese di trasporto, sconti o spese generiche</a></li>
						</ul>
					</li>
				</ul>

		
		</div> <!-- col-sm-3 -->
		
	
	</div>	  <!-- container -->
  

<?php require('_inc_footer.php');?>