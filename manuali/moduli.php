<?php require('_inc_header.php');?>
 
    <div class="container">
      	  

        <div class="col-sm-8 cakeContainer" role="main">


		<h1 id="modulo-per-la-gestione-della-notifica-per-il-cambio-della-consegna-all-ordine" class="page-header">Modulo per la “gestione della notifica per il cambio della consegna all'ordine”</h1>
		

<p>
<div class="table-responsive">
   <table class="table table-bordered">
		<tbody>
			<tr>
				<td class="action actionEdit"></td>
				<td>Modifica l'ordine</td>
			</tr>
		</tbody>
	</table>
</div>
</p>
		
<p>accedere a questo modulo quando</p>

					<ul>
						<li>si modifica un ordine esistente e viene modificata la consegna</li>
						<li>sono già stati effettuati acquisti da parte dei referenti</li>
					</ul>

<p>Il modulo è visibile ed accessibile solamente in alcuni “stati dell'ordine” (si veda il manuale “<a title="vai alla pagina del manuale" href="gestione_degli_ordini.php#il-ciclo-di-un-ordine-i-suoi-stati">La gestione degli ordini</a>”).</p>
<p>Qualora il consegna venisse modificata, PortAlGas permette di inviare una mail di notifica ai soli gasisti che hanno effettuati acquisti.</p>
<p>Se, invece, si volesse avvisare tutti i gasisti utilizzare il modulo per la “gestione della mail”.</p>
<p>Il sistema presenterà una tipologia di mail dando al referente la possibilità di modificarla.</p>
<p>Di seguito la videata che si presenterà al referente</p>

<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/modulo-notifica-cambio-consegna.png" class="img-responsive"></a>

<p>Dopo aver cliccato sul tasto “Invia la mail” attendere che la pagina si carichi completamente: l'operazione di invio mail potrebbe richiedere un po' di tempo.</p>




		<h1 id="modulo-per-la-gestione-della-notifica-per-la-sospensione-dell-ordine" class="page-header">Modulo per la “gestione della notifica per la sospensione dell'ordine”</h1>
		

<p>
<div class="table-responsive">
   <table class="table table-bordered">
		<tbody>
			<tr>
				<td class="action actionDelete"></td>
				<td>Cancella l'ordine</td>
			</tr>
		</tbody>
	</table>
</div>
</p>
		
<p>accedere a questo modulo quando</p>

					<ul>
						<li>si cancella un ordine esistente</li>
						<li>sono già stati effettuati acquisti da parte dei referenti</li>
					</ul>

<p>Il modulo è visibile ed accessibile solamente in alcuni “stati dell'ordine” (si veda il manuale “<a title="vai alla pagina del manuale" href="gestione_degli_ordini.php#il-ciclo-di-un-ordine-i-suoi-stati">La gestione degli ordini</a>”).</p>
<p>Qualora l'ordine venisse sospeso, PortAlGas permette di inviare una mail di notifica ai soli gasisti che hanno effettuati acquisti.</p>
<p>Se, invece, si volesse avvisare tutti i gasisti utilizzare il modulo per la “gestione della mail”.</p>
<p>Il sistema presenterà una tipologia di mail dando al referente la possibilità di modificarla.</p>
<p>Di seguito la videata che si presenterà al referente</p>

<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/modulo-notifica-sospensione-ordine.png" class="img-responsive"></a>

<p>Dopo aver cliccato sul tasto “Cancella definitivamente” attendere che la pagina si carichi completamente: l'operazione di invio mail potrebbe richiedere un po' di tempo.</p>





		
		<h1 id="modulo-per-la-gestione-degli-articoli-associati-all-ordine" class="page-header">Modulo per la “gestione degli articoli associati all'ordine”</h1>

<p>
<div class="table-responsive">
   <table class="table table-bordered">
		<tbody>
			<tr>
				<td class="action actionEdit"></td>
				<td>Modifica articoli associati all'ordine</td>
			</tr>
		</tbody>
	</table>
</div>
</p>

<p>Dopo aver selezionato un ordine, si può accedere a questo modulo cliccando su “Modifica articoli associati all'ordine”. Il modulo è visibile ed accessibile solamente in alcuni “stati dell'ordine” (si veda il manuale “<a href="http://manuali.portalgas.it/gestione_degli_ordini.php#il-ciclo-di-un-ordine-i-suoi-stati">La gestione degli ordini</a>”).</p>

<p>Il modulo presenta l'elenco</p>
					<ul>
						<li>Degli articoli associati all’ordine e i suoi eventuali acquisti (evidenziati in rosso)</li>
						<li>Degli articoli ancora da associare all’ordine</li>
					</ul>

<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/modulo-articoli-da-associare.png" class="img-responsive"></a>
		
<p>Cliccando su <div title="Clicca per maggior dettagli" href="#" class="action actionTrView"></div> visualizzerai gli eventuali acquisti</p>
		
<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/modulo-articoli-da-associare-dettaglio.png" class="img-responsive"></a>
		
<p>Cliccando su <div title="modifica" href="#" class="action actionEdit"></div> selezioni l'articolo che desideri modificare</p>

<p>Di seguito i parametri modificabili contestualmente all’ordine:</p>

<p>
 					<ul>
						<li>il prezzo</li>
						<li>il numero di pezzi in una confezione</li>
						<li>la quantità minima che un gasista può ordinare</li>
						<li>la quantità massima che un gasista può ordinare</li>
						<li>multipli di</li>
						<li>quantità minima rispetto a tutti gli acquisti</li>
						<li>quantità massima rispetto a tutti gli acquisti</li>
					</ul>
</p>

<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/articles-order-associa-articles-edit.png" class="img-responsive"></a>

<p>Modificando questi valori</p>

<p>
 					<ul>
						<li>verrà modificato <strong>solo</strong> in quest'ordine</li>
						<li><strong>non</strong> verrà modificato nell'anagrafica dell'articolo</li>
					</ul>
</p>
<p>Quindi, se desideri modificarlo per sempre, così da averlo per tutti i prossimi ordini, vai sull'anagrafica dell'articolo. Selezionando il Tab “Condizioni d'acquisto” troverai la seguente videata</p>

<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/articoli-tab-condizioni-aquisto.png" class="img-responsive"></a>

<p>Per maggior dettagli si veda il manuale “<a title="vai alla pagina del manuale" href="gestione_degli_articoli.php">La gestione degli articoli</a>”.

Il Referente potrà gestire solamente gli articoli dei produttori per i quali è abilitato.</p>

<p>Quando il referente crea un ordine di un produttore per il quale è abilitato, decide</p>

<ul>
	<li>quali articoli associare all'ordine</li>
	<li>quali condizioni d'acquisto (prezzo, numero di pezzi in una confezione, quantità minima, etc) devono avere gli articoli per quel determinato ordine.</li>
</ul>


		<h1 id="modulo-per-la-gestione-dei-colli" class="page-header">Modulo per la “gestione dei colli”</h1>

<p>
<div class="table-responsive">
   <table class="table table-bordered">
		<tbody>
			<tr>
				<td class="action actionValidate"></td>
				<td>Validazione dell'ordine</td>
			</tr>
		</tbody>
	</table>
</div>
</p>

<p>Dopo aver selezionato un ordine, si può accedere a questo modulo cliccando su “Validazione dell'ordine”.</p>

<p>Questo modulo è abilitato:</p>

<ul>
	<li>quando la data di chiusura dell'ordine è antecedente alla data odierna</li>
	<li>prima del giorno della consegna della merce ai gasisti</li>
	<li>se si hanno articoli che hanno il campo "numero pezzi in una confezione" maggiore di 1</li>
</ul>
 
<p>PortAlGas gestisce i colli in 3 diverse modalità:</p>

<ol>
	<li>Gestione da parte del referente</li>
	<li>Gestione con dispensa (solo se è attivo il modulo della dispensa)</li>
	<li>Riapertura dell’ordine</li>
</ol>

		<h2 id="gestione-da-parte-del-referente">Gestione da parte del referente</h2>
  
	<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/moduli-colli-referente.png" class="img-responsive"></a>

	<p>Il referente potrà variare le quantità fino a raggiungere il completamento del collo.</p>
	
		<h2 id="gestione-con-dispensa">Gestione con dispensa</h2>

	<p>Questa gestione è abilitata solo se è attivo il modulo della dispensa</p>

	<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/moduli-colli-dispensa.png" class="img-responsive"></a>

	<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/moduli-colli-dispensa-dettaglio.png" class="img-responsive"></a>

		<h2 id="riapertura-dell-ordine">Riapertura dell’ordine</h2>
		
<p>Il referente potrò riaprire l’ordine per permettere ai gasisti di completare i colli: solo gli articoli da completare saranno visibili ai gasisti</p>

<p>PortAlGas crea  e invia una mail segnalando gli articoli da completare.</p>

<p>Il referente potrà modificare il testo mail suggerito da PortAlGas</p>

<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/moduli-colli-riapertura-ordine.png" class="img-responsive"></a>

<p>Ai gasisti l’ordine riaperto comparirà così:</p>

<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/frontend-moduli-colli-riapertura-ordine.png" class="img-responsive"></a>
		
		<h1 id="modulo-per-la-gestione-degli-acquisti-nel-dettaglio" class="page-header">Modulo per la “gestione degli acquisti nel dettaglio”</h1>

<p>
<div class="table-responsive">
   <table class="table table-bordered">
		<tbody>
			<tr>
				<td class="action actionEditDbOne"></td>
				<td>Gestisci gli acquisti nel dettaglio</td>
			</tr>
		</tbody>
	</table>
</div>
</p>

<p>Dopo aver selezionato un ordine, si può accedere a questo modulo cliccando su “Gestisci gli acquisti nel dettaglio”. Il modulo è visibile ed accessibile solamente in alcuni “stati dell'ordine” (si veda il manuale “<a title="vai alla pagina del manuale" href="gestione_degli_ordini.php#il-ciclo-di-un-ordine-i-suoi-stati">La gestione degli ordini</a>”).</p>

 <p>A differenza dei 2 moduli:</p>

<ul>
	<li>modulo per la “gestione degli acquisti aggregati per importo”</li>
	<li>modulo per la “gestione degli acquisti suddividendo le quantità di ogni acquisto”</li>
</ul>

<p>non dev'essere impostato durante la creazione di un ordine, ma di default è presente per tutti gli ordini.</p>

<p>Con questo modulo possiamo visualizzare tutti gli acquisti effettuati dai gasisti</p>

<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/moduli-acquisti-dettaglio.png" class="img-responsive"></a>

<p>In questo modo possiamo  modificare, eliminare gli acquisti effettuati dai gasisti o crearne di nuovi. Tutte le eventuali modifiche saranno registrate come modifiche effettuate dal referente. Il referente potrà variare la quantità o impostare un nuovo importo utilizzando il campo denominato “importo forzato”.</p>

<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/moduli-acquisti-dettaglio-dettaglio-riga.png" class="img-responsive"></a>

<p>Per poter visualizzare il dettaglio di tutte le modifiche effettuate dal referente, utilizzare il report denominato “Documento con elenco diviso per utente con tutte le modifiche (per confrontare i dati dell'utente con le modifiche del referente)”</p>

<p>Possiamo filtrarli</p>

<ol>
	<li>Solo utenti con acquisti</li>
	<li>Tutti gli utenti, qualora volessimo aggiungere un nuovo utente</li>
	<li>Articoli raggruppati con il dettaglio degli utenti</li>
</ol>

<p>E visualizzare</p>

<ol>
	<li>Solo articoli acquistati</li>
	<li>Tutti gli articoli, qualora volessimo aggiungere articolo mai acquistato dall'utente selezionato</li>
</ol>
 
<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/moduli-acquisti-dettaglio-filtro.png" class="img-responsive"></a>
	




<h2 id="come-modificare-la-quantita-o-l-importo-di-un-articolo-ad-un-utente-che-ha-ordinato">Come modificare la quantità o l'importo di un articolo ad un utente che ha ordinato</h2>

<p>Di seguito i passi da seguire per <strong>modificare</strong> la quantità o l'importo di un articolo ad un utente che ha ordinato:</p>

<p>
<ul>
	<li>Scegliere l'ordine</li>
	<li>Accedere al <a href="#modulo-per-la-gestione-degli-acquisti-nel-dettaglio">Modulo per la "gestione degli acquisti nel dettaglio"</a></li>
	<li>Opzioni report: scegliere "Solo utenti con acquisti"</li>
	<li>Utenti: scegliere l'utente</li>
	<li>Opzioni articoli: scegliere "Solo articoli acquistati"</li>
</ul>
</p>

<p>	
<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/moduli-acquisti-modificare-articolo.png"></a>
</p>

<p>Si presenterà la lista di tutti gli articoli associati all'ordine acquistati dall'utente, andiamo sull'articolo che ci interessa e modifichiamo la quantità o l'importo</p>





<h2 id="come-associare-un-nuovo-articolo-ad-un-utente-che-non-ha-ordinato">Come associare un nuovo articolo ad un utente che ha ordinato</h2>

<p>Di seguito i passi da seguire per associare un <strong>nuovo</strong> articolo ad un utente che ha ordinato:</p>

<p>
<ul>
	<li>Scegliere l'ordine</li>
	<li>Accedere al <a href="#modulo-per-la-gestione-degli-acquisti-nel-dettaglio">Modulo per la "gestione degli acquisti nel dettaglio"</a></li>
	<li>Opzioni report: scegliere "Solo utenti con acquisti"</li>
	<li>Utenti: scegliere l'utente</li>
	<li>Opzioni articoli: scegliere "Tutti gli articoli"</li>
</ul>
</p>

<p>
<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/moduli-acquisti-aggiungere-articolo.png"></a>
</p>

<p>Si presenterà la lista di tutti gli articoli associati all'ordine, andiamo sull'articolo che ci interessa e impostiamo la quantità desiderata</p>




<h2 id="come-associare-un-articolo-ad-un-utente-che-non-ha-ordinato">Come associare un articolo ad un utente che non ha ordinato</h2>

<p>Di seguito i passi da seguire per associare un articolo ad un utente che <strong>non</strong> ha ordinato:</p>

<p>
<ul>
	<li>Scegliere l'ordine</li>
	<li>Accedere al <a href="#modulo-per-la-gestione-degli-acquisti-nel-dettaglio">Modulo per la "gestione degli acquisti nel dettaglio"</a></li>
	<li>Opzioni report: scegliere "Tutti gli utenti"</li>
	<li>Utenti: scegliere l'utente</li>
	<li>Opzioni articoli: scegliere "Tutti gli articoli"</li>
</ul>
</p>

<p>
<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/moduli-acquisti-aggiungere-articolo-nuovo-utente.png"></a>
</p>

<p>Si presenterà la lista di tutti gli articoli associati all'ordine, andiamo sull'articolo che ci interessa e impostiamo la quantità desiderata</p>





		
		<h1 id="modulo-per-la-gestione-degli-acquisti-aggregati-per-importo" class="page-header">Modulo per la “gestione degli acquisti aggregati per importo”</h1>
	
<p>
<div class="table-responsive">
   <table class="table table-bordered">
		<tbody>
			<tr>
				<td class="action actionEditDbGroupByUsers"></td>
				<td>Gestisci gli acquisti aggregati per l'importo degli utenti</td>
			</tr>
		</tbody>
	</table>
</div>
</p>
		
<p>Dopo aver selezionato un ordine, si può accedere a questo modulo cliccando su “Gestisci gli acquisti aggregati per l'importo degli utenti”. Il modulo è visibile ed accessibile solamente in alcuni “stati dell'ordine” (si veda il manuale “<a title="vai alla pagina del manuale" href="gestione_degli_ordini.php#il-ciclo-di-un-ordine-i-suoi-stati">La gestione degli ordini</a>”).</p>

<p>Per utilizzare il modulo bisogna averlo attivato durante la creazione dell'ordine o successivamente modificando la sua anagrafica.</p>

<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/ordini-tab-gestione-dopo-la-consegna-gest-acquisti.png" class="img-responsive"></a>

<p>Settandolo escluderemo il modulo per la “gestione degli acquisti suddividendo le quantità di ogni acquisto”, mentre avremo sempre a disposizione il modulo per la “gestione degli acquisti nel dettaglio”</p>

<p>Con questo modulo aggreghiamo gli importi degli acquisti di ogni gasista: è da utilizzare quando al referente interessa sapere qual'è l'importo totale che ogni utente deve pagare.</p>

<p>Per esempio</p>

<div class="table-responsive">
   <table class="table table-bordered">
		<thead>
			<tr>
				<th>Gasista</th>
				<th>Importo</th>
				<th>articolo acquistato</th>
				</tr>
		</thead>
		<tbody>
			<tr>
				<td>Rossi</td>
				<td>Articolo A importo 10,00 &euro;</td>
				<td>17,50 &euro;</td>
			</tr>
			<tr>
				<td></td>
				<td>Articolo B importo 5,00 &euro;</td>
				<td></td>
			</tr>
			<tr>
				<td></td>
				<td>Articolo C importo 2,50 &euro;</td>
				<td></td>
			</tr>
			<tr>
				<td>Verdi</td>
				<td>Articolo A importo 20,00 &euro;</td>
				<td>27,00 &euro;</td>
			</tr>
			<tr>
				<td></td>
				<td>Articolo B importo 7,00 &euro;</td>
				<td></td>
			</tr>
		</tbody>
	</table>
</div>

		<h1 id="modulo-per-la-gestione-degli-acquisti-suddividendo-le-quantita-di-ogni-acquisto" class="page-header">Modulo per la “gestione degli acquisti suddividendo le quantità di ogni acquisto”</h1>

<p>
<div class="table-responsive">
   <table class="table table-bordered">
		<tbody>
			<tr>
				<td class="action actionEditDbSplit"></td>
				<td>Gestisci gli acquisti dividendo le quantità di ogni acquisto</td>
			</tr>
		</tbody>
	</table>
</div>
</p>

<p>Dopo aver selezionato un ordine, si può accedere a questo modulo cliccando su “Gestisci gli acquisti dividendo le quantità di ogni acquisto”. Il modulo è visibile ed accessibile solamente in alcuni “stati dell'ordine” (si veda il manuale “<a title="vai alla pagina del manuale" href="gestione_degli_ordini.php#il-ciclo-di-un-ordine-i-suoi-stati">La gestione degli ordini</a>”).</p>

<p>Per utilizzare il modulo bisogna averlo attivato durante la creazione dell'ordine o successivamente modificando la sua anagrafica.</p>

<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/ordini-tab-gestione-dopo-la-consegna-gest-acquisti.png" class="img-responsive"></a>

<p>Settandolo escluderemo il modulo per la “gestione degli acquisti aggregati per importo”, mentre avremo sempre a disposizione il modulo per la “gestione degli acquisti nel dettaglio”.</p>

<p>Con questo modulo suddividiamo le quantità degli acquisti di ogni gasista: è da utilizzare quando il referente è a conoscenza degli importi dei singoli articoli e deve ricalcolare  l'importo da pagare per ogni utente. Indicando i singoli importi ci penserà PortAlGas a calcolare i totali</p>

<p>Per esempio</p>

<div class="table-responsive">
   <table class="table table-bordered">
		<thead>
			<tr>
				<th>Gasista</th>
				<th>Quantità articolo acquistato</th>
				<th>Importo gestito</th>
				</tr>
		</thead>
		<tbody>
			<tr>
				<td>Rossi</td>
				<td>Articolo A, quantità 3</td>
				<td>Articolo A, quantità 1</td>
			</tr>
			<tr>
				<td></td>
				<td></td>
				<td>Articolo A, quantità 1</td>
			</tr>
			<tr>
				<td></td>
				<td></td>
				<td>Articolo A, quantità 1</td>
			</tr>
			<tr>
				<td></td>
				<td>Articolo B, quantità 1</td>
				<td>Articolo B, quantità 1</td>
			</tr>
		</tbody>
	</table>
</div>
		
		<h1 id="modulo-per-la-gestione-del-trasporto" class="page-header">Modulo per la “gestione del trasporto”</h1>

<p>
<div class="table-responsive">
   <table class="table table-bordered">
		<tbody>
			<tr>
				<td class="action actionTrasport"></td>
				<td>Gestisci il trasporto</td>
			</tr>
		</tbody>
	</table>
</div>
</p>
		
<p>Dopo aver selezionato un ordine, si può accedere a questo modulo cliccando su “Gestisci il trasporto”. Il modulo è visibile ed accessibile solamente in alcuni “stati dell'ordine” (si veda il manuale “<a title="vai alla pagina del manuale" href="gestione_degli_ordini.php#il-ciclo-di-un-ordine-i-suoi-stati">La gestione degli ordini</a>”).</p>

<p>Questo modulo è abilitato:</p>

<ul>
	<li>quando l'ordine è chiuso e dopo che la consegna è stata effettuata</li>
	<li>se si è creato un ordine con l'opzione "gestisci la spese di trasporto"</li>
</ul>

<p>Di seguito la videata del Tab dell'ordine con la possibilità di scegliere se gestire o no un trasporto</p>

<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/ordini-tab-gestione-dopo-la-consegna.png" class="img-responsive"></a>

<p>una volta impostato l’importo del trasporto si dovrà decidere come suddividerlo, secondo una delle seguenti modalità:</p>

<ol>
	<li>Divido il trasporto in base al quantitativo acquistato</li>
	<li>Divido il trasporto in base al peso di ogni acquisto</li>
	<li>Divido il trasporto per ogni utente</li>
</ol>

<p>L'opzione “Divido il trasporto in base al peso di ogni acquisto” sarà visibile solamente se tutti gli articoli associati all'ordine avranno indicato il peso appartenenti alla medesima famiglia di unità di misura.</p>

<p>Per esempio se tutti i pesi sono in</p>

<ul>
	<li>Kg, Hg, Gr oppure</li>
	<li>Lt, Dl Ml oppure</li>
	<li>Pezzo</li>
</ul>

<p>
<div class="table-responsive">
   <table class="table table-bordered">
		<thead>
			<tr>
				<th>Gasista</th>
				<th>Ha ordinato</th>
				<th>Con l'importo</th>
				<th>Gestito la somma degli importi</th>
				<th>Importo trasporto</th>
				</tr>
		</thead>
		<tbody>
			<tr>
				<td>Rossi</td>
				<td>2 orate</td>
				<td>10,00 &euro;</td>
				<td rowspan="2">10 + 5 = 15 &euro;</td>
				<td rowspan="2">4 &euro;</td>
			</tr>
			<tr>
				<td></td>
				<td>1 branzino</td>
				<td>5 &euro;</td>
			</tr>
		</tbody>
	</table>
</div>
</p>

<p>L'importo aggregato per il gasista Rossi sarà di 15 + 4 = 19 &euro;<p>

<p>Se ho effettuato delle modifiche con il modulo per la “gestione degli acquisti aggregati per importo” e dopo utilizzo il modulo per la “gestione del trasporto”, mi troverò in quest'ultimo modulo le modifiche effettuate.</p>

<p>Ma se dovessi ritornare sul modulo per la “gestione degli acquisti aggregati per importo” ed effettuare nuove modifiche queste non sarebbero recepite dal modulo per la “gestione del trasporto”. Se si volesse far recepire al modulo per la “gestione del trasporto” le modifiche effettuate si deve cancellare l'importo del trasporto e far rieseguire i calcoli a PortAlGas.</p>

<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/moduli-trasporto-cancella.png" class="img-responsive"></a>


		<h2 id="modulo-trasporto-con-modulo-la-gestione-degli-acquisti-aggregati-importo">Utilizzo del modulo per la gestione del trasporto con il modulo la gestione degli acquisti aggregati per importo</h2>
	
<p>Qualora un ordine dovesse essere configurato per gestire i moduli</p>

<ul>
	<li>del trasporto</li>
	<li>degli acquisti aggregati per importo</li>
</ul>

<p>Si presenterà, nell'anagrafica dell'ordine come sotto illustrato</p>

<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/moduli-conflitto-acquisti-dettaglio.png" class="img-responsive"></a>

<p>Bisogna tener conto che i 3 algoritmi che suddividono l'importo del trasporto per ogni utente aggregano gli importi dei singoli utenti, così come il modulo per la “gestione degli acquisti aggregati per importo”.</p>

<p>Quindi se si è già utilizzato il modulo per la “gestione degli acquisti aggregati per importo”,  PortAlGas utilizzerà gli importi già aggregati dal modulo.</p>

<p>Inoltre, l'importo del trasporto calcolato per ogni utente sarà aggiunto all'importo aggregato.</p>
 
<p>Di seguito un esempio:</p>

<p>Supponendo che</p>

<ul>
	<li>l'importo totale del trasporto  è di 40 €</li>
	<li>viene utilizzato l'algoritmo “Divido il trasporto per ogni utente”</li>
	<li>ci sono 10 utenti e quindi ad utente viene 4 €</li>
</ul>
	
		<h1 id="modulo-per-la-gestione-del-costo-aggiuntivo" class="page-header">Modulo per la “gestione del costo aggiuntivo”</h1>

<p>
<div class="table-responsive">
   <table class="table table-bordered">
		<tbody>
			<tr>
				<td class="action actionCostMore"></td>
				<td>Gestisci un costo aggiuntivo</td>
			</tr>
		</tbody>
	</table>
</div>
</p>
		
<p>Dopo aver selezionato un ordine, si può accedere a questo modulo cliccando su “Gestisci un costo aggiuntivo”. Il modulo è visibile ed accessibile solamente in alcuni “stati dell'ordine” (si veda il manuale “<a title="vai alla pagina del manuale" href="gestione_degli_ordini.php#il-ciclo-di-un-ordine-i-suoi-stati">La gestione degli ordini</a>”).</p>

<p>Questo modulo è abilitato:</p>

<ul>
	<li>quando l'ordine è chiuso e dopo che la consegna è stata effettuata</li>
	<li>se si è creato un ordine con l'opzione "gestisci il costo aggiuntivo"</li>
</ul>

<p>Di seguito la videata del Tab dell'ordine con la possibilità di scegliere se gestire o no un costo aggiuntivo</p>

<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/moduli-conflitto-acquisti-dettaglio.png" class="img-responsive"></a>

<p>una volta impostato l’importo del costo aggiuntivo si dovrà decidere come suddividerlo, secondo una delle seguenti modalità:</p>

<ol>
	<li>Divido il costo aggiuntivo in base al quantitativo acquistato</li>
	<li>Divido il costo aggiuntivo in base al peso di ogni acquisto</li>
	<li>Divido il costo aggiuntivo per ogni utente</li>
</ol>

<p>L'opzione “Divido il costo aggiuntivo in base al peso di ogni acquisto” sarà visibile solamente se tutti gli articoli associati all'ordine avranno indicato il peso appartenenti alla medesima famiglia di unità di misura.</p>

<p>Per esempio se tutti i pesi sono in</p>

<ul>
	<li>Kg, Hg, Gr oppure</li>
	<li>Lt, Dl Ml oppure</li>
	<li>Pezzo</li>
</ul>

		
		<h1 id="modulo-per-la-gestione-dello-sconto" class="page-header">Modulo per la “gestione dello sconto”</h1>
	
<p>
<div class="table-responsive">
   <table class="table table-bordered">
		<tbody>
			<tr>
				<td class="action actionCostLess"></td>
				<td>Gestisci lo sconto</td>
			</tr>
		</tbody>
	</table>
</div>
</p>
		
<p>Dopo aver selezionato un ordine, si può accedere a questo modulo cliccando su “Gestisci lo sconto”. Il modulo è visibile ed accessibile solamente in alcuni “stati dell'ordine” (si veda il manuale “<a title="vai alla pagina del manuale" href="gestione_degli_ordini.php#il-ciclo-di-un-ordine-i-suoi-stati">La gestione degli ordini</a>”).</p>

<p>Questo modulo è abilitato:</p>

<ul>
	<li>quando l'ordine è chiuso e dopo che la consegna è stata effettuata</li>
	<li>se si è creato un ordine con l'opzione "gestisci lo sconto"</li>
</ul>

<p>Di seguito la videata del Tab dell'ordine con la possibilità di scegliere se gestire o no uno sconto</p>

<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/moduli-conflitto-acquisti-dettaglio.png" class="img-responsive"></a>

<p>una volta impostato l’importo dello sconto si dovrà decidere come suddividerlo, secondo una delle seguenti modalità:</p>

<ol>
	<li>Applica lo sconto  in base al quantitativo acquistato</li>
	<li>Applica lo sconto in base al peso di ogni acquisto</li>
	<li>Applica lo sconto per ogni utente</li>
</ol>

<p>L'opzione “Divido lo sconto in base al peso di ogni acquisto” sarà visibile solamente se tutti gli articoli associati all'ordine avranno indicato il peso appartenenti alla medesima famiglia di unità di misura.</p>

<p>Per esempio se tutti i pesi sono in</p>

<ul>
	<li>Kg, Hg, Gr oppure</li>
	<li>Lt, Dl Ml oppure</li>
	<li>Pezzo</li>
</ul>

		
		<h1 id="modulo-per-la-gestione-della-cassa" class="page-header">Modulo per la “gestione della cassa e del cassiere”</h1>
		
		<p><a href="gestione_del_cassiere.php">Clicca qui per maggiori informazioni</a></li>
		
		
		
		<h1 id="modulo-per-la-gestione-delle-stampe" class="page-header">Modulo per la “gestione delle stampe”</h1>

<p>
<div class="table-responsive">
   <table class="table table-bordered">
		<tbody>
			<tr>
				<td class="action actionPrinter"></td>
				<td>Stampa l'ordine</td>
			</tr>
		</tbody>
	</table>
</div>
</p>
		
<p>Dopo aver selezionato un ordine, si può accedere a questo modulo cliccando su “Stampa l'ordine”.</p>

<p>Con questo modulo potrai esportare i dati del report i formato PDF, CVS ed EXCEL</p>

<p>Di seguito i diversi report di stampa disponibili</p>


<p>
<ul>
	<li>Documento con elenco diviso per utente con tutte le modifiche (per confrontare i dati dell'utente con le modifiche del referente)</li>
	<li>Documento con elenco diviso per utente (per pagamento dell'utente)</li>
	<li>Documento con elenco diviso per utente in formato etichetta (per la consegna)</li>
	<li>Documento con articoli raggruppati (per il produttore)</li>
	<li>Documento con articoli raggruppati con il dettaglio degli utenti</li>
</ul>
</p>

<p>Selezionando la tipologia di report, si presenteranno alcune opzioni del report, per esempio</p>

<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/moduli-stampa.png" class="img-responsive"></a>

<p>Altre stampe in PortAlGas si trovano</p>
<p>
<ul>
	<li>BackOffice, cliccando su Utility => Stampa documenti</li>
	<li>FrontEnd, cliccando su Stampe</li>
</ul>
</p>
<p>accedendo alle pagine sopraindicate, si troveranno le seguenti stampe</p>
<p>
<ul>
	<li>Tutti gli acquisti della consegna</li>  
	<li>Tutti gli utenti che saranno presenti alla consegna</li>  
	<li>Tutti gli articoli del produttore</li>  
	<li>Tutti gli articoli dell'ordine</li>  
	<li>Anagrafica di tutti gli utenti</li>  
	<li>I referenti dei produttori</li>  
	<li>Il carrello dell'utente scelto tra la lista presentata in una determinata consegna (questa stampa è abilitata solo per gli utenti con ruolo di “manager del GAS”)</li>  
	<li>La richiesta di pagamento di un utente scelto tra la lista presentata (questa stampa è abilitata solo per 
		<ul>  
			<li>i GAS configurati con la gestione del pagamento dopo la consegna.</li>  
			<li>gli utenti con ruolo di “manager del GAS” o ruolo di “Tesoriere”</li>  
		</ul>
	</li>  
</ul>
</p>

<p>
<div class="table-responsive">
   <table class="table table-bordered">
		<tbody>
			<tr>
				<td class="action actionTrConfig"></td>
				<td>Cliccando sull'icona si visualizzano, dove presenti, le opzioni di stampa</td>
			</tr>
		</tbody>
	</table>
</div>
</p>

<div class="alert alert-info" role="alert">
	<strong>Nota</strong>: nelle stampe, il simbolo * indica che l'importo è stato modificato dal referente: potrebbe dipendere, per esempio, dalla somma di importi aggregati tra loro o dal calcolo del trasporto 
</div>

    
		<h1 id="modulo-per-la-gestione-dell-invio-delle-mail" class="page-header">Modulo per la “gestione dell'invio delle mail”</h1>

<p>
<div class="table-responsive">
   <table class="table table-bordered">
		<tbody>
			<tr>
				<td class="action actionJContent"></td>
				<td>Invio mail</td>
			</tr>
		</tbody>
	</table>
</div>
</p>
    
<p>Accedendo al modulo dal menù Utility => Mail si potrà così inviare le mail</p>

<p>Queste le opzioni del modulo:</p>

<p>Scegli a chi è il mittente:</p>

<ul>
	<li>no-reply@portalgas.it</li>
	<li>il tuo indirizzo mail</li>
</ul>
 
<p>Scegli a chi inviare la mail, i destinatari</p>

<p>
<ul>
	<li>agli utenti (i gasisti)
		<ul>
			<li>a tutti</li>
			<li>ad alcuni
				<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/moduli-mail-a-utenti.png" class="img-responsive"></a>
		 	</li>
		</ul>
	</li>
	<li>ai referenti
		<ul>
			<li>a tutti</li>
			<li>ad alcuni</li>
		</ul>
	</li>
	<li>ai produttori
		<ul>
			<li>a tutti</li>
			<li>ad alcuni</li>
		</ul>
	</li>
	<li>agli utenti che hanno effettuato acquisti ad un certo ordine
		<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/moduli-mail-a-utenti-ordini.png" class="img-responsive"></a>
	</li>
</ul>    
</p>

<div role="alert" class="alert alert-info">
	<strong>Nota: </strong> se hai problemi ad inviarele mail da PortAlGas <a href="problemi.php#non-riesco-ad-inviare-le-mail-da-portalgas">leggi qui</a>
</div>	

							
		</div> <!-- col-sm-8 -->
		
		<div class="col-sm-3" role="complementary">

				<ul id="myScrollspy" class="nav nav-contenitore nav-stacked hidden-print hidden-xs hidden-sm" data-spy="affix" data-offset-top="125">
					<li class="active"><a href="#modulo-per-la-gestione-della-notifica-per-il-cambio-della-consegna-all-ordine">Modulo per la “gestione della notifica per il cambio della consegna all'ordine”</a></li>
					<li><a href="#modulo-per-la-gestione-della-notifica-per-la-sospensione-dell-ordine">Modulo per la “gestione della notifica per la sospensione dell'ordine”</a></li>
					<li><a href="#modulo-per-la-gestione-degli-articoli-associati-all-ordine">Modulo per la “gestione degli articoli associati all'ordine”</a></li>
					<li><a href="#modulo-per-la-gestione-dei-colli">Modulo per la “gestione dei colli”</a>
						<ul class="nav">
							<li><a href="#gestione-da-parte-del-referente">Gestione da parte del referente</a></li>
							<li><a href="#gestione-con-dispensa">Gestione con dispensa</a></li>
							<li><a href="#riapertura-dell-ordine">Riapertura dell’ordine</a></li>							
						</ul>					
					</li>
					<li><a href="#modulo-per-la-gestione-degli-acquisti-nel-dettaglio">Modulo per la “gestione degli acquisti nel dettaglio”</a>
						<ul class="nav">
							<li><a href="#come-modificare-la-quantita-o-l-importo-di-un-articolo-ad-un-utente-che-ha-ordinato">Come modificare la quantità o l'importo di un articolo ad un utente che ha ordinato</a></li>
							<li><a href="#come-associare-un-nuovo-articolo-ad-un-utente-che-non-ha-ordinato">Come associare un nuovo articolo ad un utente che ha ordinato</a></li>
							<li><a href="#come-associare-un-articolo-ad-un-utente-che-non-ha-ordinato">Come associare un articolo ad un utente che non ha ordinato</a></li>
						</ul>					
					</li>
					<li><a href="#modulo-per-la-gestione-degli-acquisti-aggregati-per-importo">Modulo per la “gestione degli acquisti aggregati per importo”</a></li>
					<li><a href="#modulo-per-la-gestione-degli-acquisti-suddividendo-le-quantita-di-ogni-acquisto">Modulo per la “gestione degli acquisti suddividendo le quantità di ogni acquisto”</a></li>
					<li><a href="#modulo-per-la-gestione-del-trasporto">Modulo per la “gestione del trasporto”</a>
						<ul class="nav">
							<li><a href="#modulo-trasporto-con-modulo-la-gestione-degli-acquisti-aggregati-importo">Utilizzo del modulo per la gestione del trasporto con il modulo la gestione degli acquisti aggregati per importo</a></li>							
						</ul>					
					</li>					
					<li><a href="#modulo-per-la-gestione-del-costo-aggiuntivo">Modulo per la “gestione del costo aggiuntivo”</a></li>
					<li><a href="#modulo-per-la-gestione-dello-sconto">Modulo per la “gestione dello sconto”</a></li>
					<li><a href="#modulo-per-la-gestione-della-cassa">Modulo per la “gestione della cassa”</a></li>
					<li><a href="#modulo-per-la-gestione-delle-stampe">Modulo per la “gestione delle stampe”</a></li>
					<li><a href="#modulo-per-la-gestione-dell-invio-delle-mail">Modulo per la “gestione dell'invio delle mail”</a></li>
				</ul>
		
		
		</div> <!-- col-sm-3 -->
		
	
	</div>	  <!-- container -->
  

<?php require('_inc_footer.php');?>