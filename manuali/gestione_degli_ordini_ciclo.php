<?php require('_inc_header.php');?>
  
    <div class="container">
      

	  



	
	  
	
							


        <div class="col-sm-8 cakeContainer" role="main">


	
	<h1 id="il-ciclo-di-un-ordine-i-suoi-stati">Il ciclo di un ordine: i suoi stati</h1>
			
<p>Gli ordini hanno degli "stati dell'ordine" che influiscono sulle azioni che gli attori di PortAlGas (gasisti registrati, referenti, cassiere o tesoriere) possono compiere.</p>

<p>Se il G.A.S. è configurato</p>

<ul>
	<li>per la gestione con "pagamento <strong>dopo</strong> la consegna" i referenti gestiranno i seguenti stati dell'ordine <br />
		<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/ordini-stati-pagamento-dopo-consegna.png" class="img-responsive"></a>
	</li>
	<li>per la gestione con "pagamento <strong>alla</strong> consegna" i referenti gestiranno i seguenti stati dell'ordine  <br />
		<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/ordini-stati-pagamento-alla-consegna.png" class="img-responsive"></a>
	</li>
</ul>	

<p>
<div class="table-responsive">
   <table class="table table-bordered">
		<thead>
			<tr>
				<th>Pagamento <strong>dopo</strong> la consegna</th>
				<th>Pagamento <strong>alla</strong> consegna</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>Ordine incompleto</td>
				<td>Ordine incompleto</td>
			</tr>
			<tr>
				<td>Prossima apertura</td>
				<td>Prossima apertura</td>
			</tr>
			<tr>
				<td>Aperto</td>
				<td>Aperto</td>
			</tr>
			<tr>
				<td>In carico al referente prima della consegna</td>
				<td>In carico al referente prima della consegna</td>
			</tr>
			<tr>
				<td>In carico al referente dopo la consegna</td>
				<td>In carico al referente con la merce arrivata</td>
			</tr>
			<tr>
				<td>In attesa che il tesoriere lo prenda in carico</td>
				<td>In carico al cassiere durante la consegna</td>
			</tr>
			<tr>
				<td>In carico al tesoriere</td>
				<td>Ordine chiuso</td>
			</tr>
			<tr>
				<td>Possibilità di richiederne il pagamento</td>
				<td></td>
			</tr>
			<tr>
				<td>Ordine chiuso</td>
				<td></td>
			</tr>
		</tbody>
	</table>
</div> 
</p>
					
	<h2 id="ordine-incompleto">Ordine incompleto</h2>

<div class="table-responsive">
   <table class="table table-bordered">
		<thead>
			<tr>
				<th colspan="2">Icona</th>
				<th>Nota</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td style="width: 32px;" class="orderStatoCREATE-INCOMPLETE"></td>
				<td>Ordine incompleto</td>
				<td>
					Si è in questo stato quando è viene creato un ordine ma non ci sono articoli <strong>associati</strong>
				</td>
			</tr>
		</tbody>
	</table>
</div> 

<p>Di seguito le azioni permesse al referente durante questa fase</p>

<div class="table-responsive">
   <table class="table table-bordered">
		<thead>
			<tr>
				<th>Icona</th>
				<th>Azione</th>
				<th>Modulo *</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="action actionEdit"></td>
				<td>Modificare i dati anagrafici dell'ordine (per esempio la data di chiusura dell'ordine)</td>
				<td><a title="vai alla pagina del manuale" href="moduli.php#modulo-per-la-gestione-della-notifica-per-il-cambio-della-consegna-all-ordine">Notifica per il cambio della consegna</a></td>
			</tr>
			<tr>
				<td class="action actionEditCart"></td>
				<td>L'ordine non ha ancora articoli associati!</td>
				<td><a title="vai alla pagina del manuale" href="moduli.php#modulo-per-la-gestione-degli-articoli-associati-all-ordine">Articoli associati all'ordine</a></td>
			</tr>
			<tr>
				<td class="action actionDelete"></td>
				<td>Cancellare l'ordine e gli eventuali acquisti</td>
				<td><a title="vai alla pagina del manuale" href="moduli.php#modulo-per-la-gestione-della-notifica-per-la-sospensione-dell-ordine">Notifica per la sospensione  dell'ordine</a></td>
			</tr>
			<tr>
				<td class="action actionBackup"></td>
				<td>Ripristino dati dell'ordine</td>
				<td></td>
			</tr>
		</tbody>
	</table>
</div>

<div class="alert alert-info" role="alert">
	<strong>*</strong> si veda il manuale sui moduli di PortAlGas
</div>

<h3 id="ripristino-dati-dell-ordine">Ripristino dati dell'ordine</h3>

<p>
	PortAlGas effettua ogni notte un backup completo di tutti i dati.<br /> 
	Con questo modulo si permette di copiare i dati del backup del giorno precedente riguardanti 
	<ul>
		<li>gli articoli associati all'ordine</li>
		<li>gli eventuali acqusiti</li>
	</ul>
</p>

<p>	
  Qualora ci fossero dati, verrà presentata la lista degli articoli associato all'ordine evidenziando il rosso gli acquisti<br /> 
  Cliccando su "Ripristina i dati" verranno copiati i dati del backup della notte precedente sull'ordine corrente 
</p>

<p>	
	<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/ordini-backup.jpg" class="img-responsive"></a>
</p>


						
	<h2 id="prossima-apertura">Prossima apertura</h2>
	
<div class="table-responsive">
   <table class="table table-bordered">
		<thead>
			<tr>
				<th colspan="2">Icona</th>
				<th>Nota</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td style="width: 32px;" class="orderStatoOPEN-NEXT"></td>
				<td>Prossima apertura</td>
				<td>
					Si è in questo stato quando la data di apertura dell'ordine è <strong>posteriore</strong> alla data odierna: in questa
					stato dell'ordine
					<ul>
						<li>il referente può modificare la sua anagrafica e gli articoli associati</li>
						<li>i gasisti lo vedono nell'elenco delle consegne</li>
						<li>i gasisti non possono effettuare acquisti</li>
					</ul>
				</td>
			</tr>
		</tbody>
	</table>
</div> 

	
	<h2 id="ordine-aperto">Ordine aperto</h2>
	
<div class="table-responsive">
   <table class="table table-bordered">
		<thead>
			<tr>
				<th colspan="2">Icona</th>
				<th>Nota</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td style="width: 32px;" class="orderStatoOPEN"></td>
				<td>Aperto</td>
				<td>
					Si è in questo stato quando la data di apertura dell'ordine è uguale o <strong>antecedente</strong> alla data odierna: in questa stato dell'ordine
					<ul>
						<li>il referente può modificare la sua anagrafica e gli articoli associati</li>
						<li>il referente non può modificare gli acquisti ma solamente monitorarli</li>
						<li>i gasisti lo vedono nell'elenco delle consegne</li>
						<li>i gasisti possono effettuare acquisti</li>
					</ul>
				</td>
			</tr>
		</tbody>
	</table>
</div>

<p>Di seguito le azioni permesse al referente durante questa fase</p>

<div class="table-responsive">
   <table class="table table-bordered">
		<thead>
			<tr>
				<th>Icona</th>
				<th>Azione</th>
				<th>Modulo *</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="action actionEdit"></td>
				<td>Modificare i dati anagrafici dell'ordine (per esempio la data di chiusura dell'ordine)</td>
				<td><a title="vai alla pagina del manuale" href="moduli.php#modulo-per-la-gestione-della-notifica-per-il-cambio-della-consegna-all-ordine">Notifica per il cambio della consegna</a></td>
			</tr>
			<tr>
				<td class="action actionEditCart"></td>
				<td>Modificare l'anagrafica degli articoli associati</td>
				<td><a title="vai alla pagina del manuale" href="moduli.php#modulo-per-la-gestione-degli-articoli-associati-all-ordine">Articoli associati all'ordine</a></td>
			</tr>
			<tr>
				<td class="action actionDelete"></td>
				<td>Cancellare l'ordine e gli eventuali acquisti</td>
				<td><a title="vai alla pagina del manuale" href="moduli.php#modulo-per-la-gestione-della-notifica-per-la-sospensione-dell-ordine">Notifica per la sospensione  dell'ordine</a></td>
			</tr>
			<tr>
				<td class="action actionEditDbOne"></td>
				<td>Visualizzare gli acquisti</td>
				<td><a title="vai alla pagina del manuale" href="moduli.php#gestione-con-dispensa">Gestione degli acquisti nel dettaglio</a></td>
			</tr>
			<tr>
				<td class="action actionPrinter"></td>
				<td>Stampare i diversi report di stampa</td>
				<td><a title="vai alla pagina del manuale" href="moduli.php#modulo-per-la-gestione-delle-stampe">Gestione delle stampe</a></td>
			</tr>
		</tbody>
	</table>
</div>

<div class="alert alert-info" role="alert">
	<strong>*</strong> si veda il manuale sui moduli di PortAlGas
</div>


	
	<h2 id="in-carico-al-referente-prima-della-consegna">In carico al referente prima della consegna</h2>

<div class="table-responsive">
   <table class="table table-bordered">
		<thead>
			<tr>
				<th colspan="2">Icona</th>
				<th>Nota</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td style="width: 32px;" class="orderStatoPROCESSED-BEFORE-DELIVERY"></td>
				<td>In carico al referente prima della consegna</td>
				<td>
					Si è in questo stato quando la data di chiusura dell'ordine è <strong>posteriore</strong> alla data odierna: in questa stato dell'ordine
					<ul>
						<li>il referente può modificare la sua anagrafica e gli articoli associati</li>
						<li>il referente può modificare gli acquisti così da inviare al produttore un report con tutte le eventuali modifiche che possono capitare dopo la data di chiusura di un ordine</li>
						<li>i gasisti lo vedono nell'elenco delle consegne </li>
						<li>i gasisti <strong>non</strong> possono effettuare acquisti</li>
					</ul>
				</td>
			</tr>
		</tbody>
	</table>
</div>


	<h3 id="gestione-con-pagamento-dopo-la-consegna">Gestione con “pagamento dopo la consegna”</h3>
	
Se il G.A.S. è configurato per la gestione con "pagamento <strong>dopo</strong> la consegna", il referente avrà a disposizione le seguenti azioni


<div class="table-responsive">
   <table class="table table-bordered">
		<thead>
			<tr>
				<th>Icona</th>
				<th>Azione</th>
				<th>Modulo *</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="action actionEdit"></td>
				<td>Modificare i dati anagrafici dell'ordine (per esempio la data di chiusura dell'ordine)</td>
				<td><a title="vai alla pagina del manuale" href="moduli.php#modulo-per-la-gestione-della-notifica-per-il-cambio-della-consegna-all-ordine">Notifica per il cambio della consegna</a></td>
			</tr>
			<tr>
				<td class="action actionEditCart"></td>
				<td>Modificare l'anagrafica degli articoli associati</td>
				<td><a title="vai alla pagina del manuale" href="moduli.php#modulo-per-la-gestione-degli-articoli-associati-all-ordine">Articoli associati all'ordine</a></td>
			</tr>
			<tr>
				<td class="action actionDelete"></td>
				<td>Cancellare l'ordine e gli eventuali acquisti</td>
				<td><a title="vai alla pagina del manuale" href="moduli.php#modulo-per-la-gestione-della-notifica-per-la-sospensione-dell-ordine">Notifica per la sospensione  dell'ordine</a></td>
			</tr>
			<tr>
				<td class="action actionEditDbOne"></td>
				<td>Gestire gli acquisti, in modo puntuale</td>
				<td><a title="vai alla pagina del manuale" href="moduli.php#gestione-con-dispensa">Gestione degli acquisti nel dettaglio</a></td>
			</tr>
			<tr>
				<td class="action actionValidate"></td>
				<td>Gestione dei colli</td>
				<td><a title="vai alla pagina del manuale" href="moduli.php#modulo-per-la-gestione-dei-colli">Gestione dei colli</a></td>
			</tr>
			<tr>
				<td class="action actionPrinter"></td>
				<td>Stampare i diversi report di stampa</td>
				<td><a title="vai alla pagina del manuale" href="moduli.php#modulo-per-la-gestione-delle-stampe">Gestione delle stampe</a></td>
			</tr>
		</tbody>
	</table>
</div>
<div class="alert alert-info" role="alert">
	<strong>*</strong> si veda il manuale sui moduli di PortAlGas
</div>

	
	<h3 id="gestione-con-pagamento-alla-consegna">Gestione con “pagamento alla consegna”</h3>


Se il G.A.S. è configurato per la gestione con "pagamento <strong>alla</strong> la consegna", il referente avrà a disposizione le seguenti azioni 


<div class="table-responsive">
   <table class="table table-bordered">
		<thead>
			<tr>
				<th>Icona</th>
				<th>Azione</th>
				<th>Modulo *</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="action actionEdit"></td>
				<td>Modificare i dati anagrafici dell'ordine (per esempio la data di chiusura dell'ordine)</td>
				<td><a title="vai alla pagina del manuale" href="moduli.php#modulo-per-la-gestione-della-notifica-per-il-cambio-della-consegna-all-ordine">Notifica per il cambio della consegna</a></td>
			</tr>
			<tr>
				<td class="action actionEditCart"></td>
				<td>Modificare l'anagrafica degli articoli associati</td>
				<td><a title="vai alla pagina del manuale" href="moduli.php#modulo-per-la-gestione-degli-articoli-associati-all-ordine">Articoli associati all'ordine</a></td>
			</tr>
			<tr>
				<td class="action actionDelete"></td>
				<td>Cancellare l'ordine e gli eventuali acquisti</td>
				<td><a title="vai alla pagina del manuale" href="moduli.php#modulo-per-la-gestione-della-notifica-per-la-sospensione-dell-ordine">Notifica per la sospensione  dell'ordine</a></td>				
			</tr>
			<tr>
				<td class="action actionEditDbOne"></td>
				<td>Gestire gli acquisti, in modo puntuale</td>
				<td><a title="vai alla pagina del manuale" href="moduli.php#gestione-con-dispensa">Gestione degli acquisti nel dettaglio</a></td>
			</tr>
			<tr>
				<td class="action actionIncomingOrder"></td>
				<td>Merce arrivata: l'ordine passerà allo stato "in carico al referente con la merce arrivata"</td>
				<td></td>
			</tr>
			<tr>
				<td class="action actionPrinter"></td>
				<td>Stampare i diversi report di stampa</td>
				<td><a title="vai alla pagina del manuale" href="moduli.php#modulo-per-la-gestione-delle-stampe">Gestione delle stampe</a></td>
			</tr>
		</tbody>
	</table>
</div>
<div class="alert alert-info" role="alert">
	<strong>*</strong> si veda il manuale sui moduli di PortAlGas
</div>

	<h2 id="in-carico-al-referente-dopo-la-consegna">In carico al referente dopo la consegna</h2>
	
<div class="table-responsive">
   <table class="table table-bordered">
		<thead>
			<tr>
				<th>Pagamento <strong>dopo</strong> la consegna</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>Ordine incompleto</td>
			</tr>
			<tr>
				<td>Prossima apertura</td>
			</tr>
			<tr>
				<td>Aperto</td>
			</tr>
			<tr>
				<td>In carico al referente prima della consegna</td>
			</tr>
			<tr>
				<td><strong>In carico al referente dopo la consegna</strong></td>
			</tr>
			<tr>
				<td>In attesa che il tesoriere lo prenda in carico</td>
			</tr>
			<tr>
				<td>In carico al tesoriere</td>
			</tr>
			<tr>
				<td>Possibilità di richiederne il pagamento</td>
			</tr>
			<tr>
				<td>Ordine chiuso</td>
			</tr>
		</tbody>
	</table>
</div> 


<p>Se il G.A.S. è configurato per la gestione con "pagamento <strong>dopo</strong> la consegna", questo stato dell'ordine verrà gestito come segue:</p>
<p>Si è in questo stato dell'ordine quando</p>

<ul>
	<li>la data di chiusura dell'ordine è antecedente o uguale alla data odierna</li>
	<li>la data della consegna della merce ai gasisti è antecedente alla data odierna</li>
</ul>

<p>Di seguito le azioni permesse al referente durante questa fase</p>

<div class="table-responsive">
   <table class="table table-bordered">
		<thead>
			<tr>
				<th>Icona</th>
				<th>Azione</th>
				<th>Modulo *</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="action actionEdit"></td>
				<td>Modificare i dati anagrafici dell'ordine (per esempio la data di chiusura dell'ordine)</td>
				<td><a title="vai alla pagina del manuale" href="moduli.php#modulo-per-la-gestione-della-notifica-per-il-cambio-della-consegna-all-ordine">Notifica per il cambio della consegna</a></td>
			</tr>
			<tr>
				<td class="action actionEditCart"></td>
				<td>Modificare l'anagrafica degli articoli associati</td>
				<td><a title="vai alla pagina del manuale" href="moduli.php#modulo-per-la-gestione-degli-articoli-associati-all-ordine">Articoli associati all'ordine</a></td>
			</tr>
			<tr>
				<td class="action actionDelete"></td>
				<td>Cancellare l'ordine e gli eventuali acquisti</td>
				<td><a title="vai alla pagina del manuale" href="moduli.php#modulo-per-la-gestione-della-notifica-per-la-sospensione-dell-ordine">Notifica per la sospensione  dell'ordine</a></td>
			</tr>
			<tr>
				<td class="action actionEditDbOne"></td>
				<td>Gestire gli acquisti, in modo puntuale</td>
				<td><a title="vai alla pagina del manuale" href="moduli.php#gestione-con-dispensa">Gestione degli acquisti nel dettaglio</a></td>
			</tr>
			<tr>
				<td class="action actionEditDbGroupByUsers"></td>
				<td>Gestire gli acquisti aggregando gli importi degli acquisti per ogni gasista, qualora si volesse gestire solo un importo totale per ogni gasista</td>
				<td><a title="vai alla pagina del manuale" href="moduli.php#modulo-per-la-gestione-degli-acquisti-aggregati-per-importo">Gestione degli acquisti aggregati per importo</a></td>
			</tr>
			<tr>
				<td class="action actionEditDbSplit"></td>
				<td>Gestire gli acquisti suddividendo le quantità degli acquisti per ogni gasista, qualora si volesse gestire l'importo di ogni singola quantità acquistata per ogni gasista</td>
				<td><a title="vai alla pagina del manuale" href="moduli.php#modulo-per-la-gestione-degli-acquisti-suddividendo-le-quantita-di-ogni-acquisto">Gestione degli acquisti suddividendo le quantità di ogni acquisto</a></td>
			</tr>
			<tr>
				<td class="action actionTrasport"></td>
				<td>Gestione le spese di trasporto</td>
				<td><a title="vai alla pagina del manuale" href="moduli.php#modulo-per-la-gestione-del-trasporto">Gestione le spese di trasporto</a></td>
			</tr>
			<tr>
				<td class="action actionCostMore"></td>
				<td>Gestione le spese di aggiuntive</td>
				<td><a title="vai alla pagina del manuale" href="moduli.php#modulo-per-la-gestione-del-costo-aggiuntivo">Gestione le spese di aggiuntive</a></td>
			</tr>
			<tr>
				<td class="action actionCostLess"></td>
				<td>Gestione dello sconto</td>
				<td><a title="vai alla pagina del manuale" href="moduli.php#modulo-per-la-gestione-dello-sconto">Gestione dello sconto</a></td>
			</tr>
			<tr>
				<td class="action actionPrinter"></td>
				<td>Stampare i diversi report di stampa</td>
				<td><a title="vai alla pagina del manuale" href="moduli.php#modulo-per-la-gestione-delle-stampe">Gestione delle stampe</a></td>
			</tr>
			<tr>
				<td class="action actionMail"></td>
				<td>Invia mail al produttore</td>
				<td></td>
			</tr>
			<tr>
				<td class="action actionFromRefToTes"></td>
				<td>Passa l'ordine al tesoriere</td>
				<td></td>
			</tr>
		</tbody>
	</table>
</div>
<div class="alert alert-info" role="alert">
	<strong>*</strong> si veda il manuale sui moduli di PortAlGas
</div>


<p>Cliccando su "Passa l'ordine al tesoriere" si accederà al modulo per trasmettere l'ordine al tesoriere che potrà così:</p>
<p>
<ul>
	<li>richiedere il pagamento ai gasisti</li>
	<li>pagare i produttori</li>
</ul>
</p>

<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/ordini-passa-tesoriere.png" class="img-responsive"></a>

<p>Passando l'ordine al tesoriere, l'ordine assumerà lo stato di "In attesa che il tesoriere lo prenda in carico". Il referente potrà sempre riportarlo allo stato "In carico al referente dopo la consegna" per modificarlo fino a quando non sarà in "In carico al tesoriere": da quel momento il referente non potrà più apporre modifiche.Il modulo sarà composto dai seguenti campi</p>

<div class="table-responsive">
   <table class="table table-bordered">
		<thead>
			<tr>
				<th>Campi</th>
				<th>Note</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>Fattura per il tesoriere</td>
				<td>
					E' possibile uplodare un file, contenente la fattura, con estensione:
					<ul>
						<li>.pdf</li>
						<li>.zip</li>
						<li>.jpg</li>
						<li>.jpeg</li>
						<li>.gif</li>
						<li>.png</li>
					</ul>				
					<p>Qualora si dovessero allegare <strong>più file</strong> da trasmettere al tesoriere si potrà fare un file zip dei diversi documenti e uplodarli.</p>
					<p>Se si effettua l'upload più volte, il file verrà sovrascritto</p>
				</td>
			</tr>
			<tr>
				<td>Importo della fattura</td>
				<td>Indicare l'importo della fattura</td>
			</tr>
			<tr>
				<td>Importo totale dell'ordine</td>
				<td>Questo campo è valorizzato da PortAlGas e indica la somma di tutti gli importi dell'ordine</td>
			</tr>
			<tr>
				<td>Differenza</td>
				<td>Questo campo è calcolato da PortAlGas ed è la differenza tra l'importo totale dell'ordine e l'importo della fattura</td>
			</tr>
			<tr>
				<td>Nota per il tesoriere</td>
				<td>Indicare un eventuale testo da trasmettere al tesoriere</td>
			</tr>
		</tbody>
	</table>
</div>

<p>Di seguito come apparirà ai tesorieri l'ordine appena trasmesso</p>

<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/ordine-passato-tesoriere.png" class="img-responsive"></a>
	
	<h2 id="in-carico-al-referente-con-la-merce-arrivata">In carico al referente con la merce arrivata</h2>
	
<div class="table-responsive">
   <table class="table table-bordered">
		<thead>
			<tr>
				<th>Pagamento <strong>alla</strong> consegna</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>Ordine incompleto</td>
			</tr>		
			<tr>
				<td>Prossima apertura</td>
			</tr>
			<tr>
				<td>Aperto</td>
			</tr>
			<tr>
				<td>In carico al referente prima della consegna</td>
			</tr>
			<tr>
				<td><strong>In carico al referente con la merce arrivata</strong></td>
			</tr>
			<tr>
				<td>In carico al cassiere durante la consegna</td>
			</tr>
			<tr>
				<td>Ordine chiuso</td>
			</tr>
		</tbody>
	</table>
</div> 

<p>Questo stato dell'ordine è gestito solo per i G.A.S. configurati per la gestione con "pagamento <strong>alla</strong> la consegna".</p> 

<p>Di seguito le azioni permesse al referente durante questa fase</p>

<div class="table-responsive">
   <table class="table table-bordered">
		<thead>
			<tr>
				<th>Icona</th>
				<th>Azione</th>
				<th>Modulo *</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="action actionEdit"></td>
				<td>Modificare i dati anagrafici dell'ordine (per esempio la data di chiusura dell'ordine)</td>
				<td><a title="vai alla pagina del manuale" href="moduli.php#modulo-per-la-gestione-della-notifica-per-il-cambio-della-consegna-all-ordine">Notifica per il cambio della consegna</a></td>
			</tr>
			<tr>
				<td class="action actionEditCart"></td>
				<td>Modificare l'anagrafica degli articoli associati</td>
				<td><a title="vai alla pagina del manuale" href="moduli.php#modulo-per-la-gestione-degli-articoli-associati-all-ordine">Articoli associati all'ordine</a></td>
			</tr>
			<tr>
				<td class="action actionDelete"></td>
				<td>Cancellare l'ordine e gli eventuali acquisti</td>
				<td><a title="vai alla pagina del manuale" href="moduli.php#modulo-per-la-gestione-della-notifica-per-la-sospensione-dell-ordine">Notifica per la sospensione  dell'ordine</a></td>
			</tr>
			<tr>
				<td class="action actionEditDbOne"></td>
				<td>Gestire gli acquisti, in modo puntuale</td>
				<td><a title="vai alla pagina del manuale" href="moduli.php#gestione-con-dispensa">Gestione degli acquisti nel dettaglio</a></td>
			</tr>
			<tr>
				<td class="action actionEditDbGroupByUsers"></td>
				<td>Gestire gli acquisti aggregando gli importi degli acquisti per ogni gasista, qualora si volesse gestire solo un importo totale per ogni gasista</td>
				<td><a title="vai alla pagina del manuale" href="moduli.php#modulo-per-la-gestione-degli-acquisti-aggregati-per-importo">Gestione degli acquisti aggregati per importo</a></td>
			</tr>
			<tr>
				<td class="action actionEditDbSplit"></td>
				<td>Gestire gli acquisti suddividendo le quantità degli acquisti per ogni gasista, qualora si volesse gestire l'importo di ogni singola quantità acquistata per ogni gasista</td>
				<td><a title="vai alla pagina del manuale" href="moduli.php#modulo-per-la-gestione-degli-acquisti-suddividendo-le-quantita-di-ogni-acquisto">Gestione degli acquisti suddividendo le quantità di ogni acquisto</a></td>
			</tr>
			<tr>
				<td class="action actionTrasport"></td>
				<td>Gestione le spese di trasporto</td>
				<td><a title="vai alla pagina del manuale" href="moduli.php#modulo-per-la-gestione-del-trasporto">Gestione le spese di trasporto</a></td>
			</tr>
			<tr>
				<td class="action actionCostMore"></td>
				<td>Gestione le spese di aggiuntive</td>
				<td><a title="vai alla pagina del manuale" href="moduli.php#modulo-per-la-gestione-del-costo-aggiuntivo">Gestione le spese di aggiuntive</a></td>
			</tr>
			<tr>
				<td class="action actionCostLess"></td>
				<td>Gestione dello sconto</td>
				<td><a title="vai alla pagina del manuale" href="moduli.php#modulo-per-la-gestione-dello-sconto">Gestione dello sconto</a></td>
			</tr>
			<tr>
				<td class="action actionPrinter"></td>
				<td>Stampare i diversi report di stampa</td>
				<td><a title="vai alla pagina del manuale" href="moduli.php#modulo-per-la-gestione-delle-stampe">Gestione delle stampe</a></td>
			</tr>
			<tr>
				<td class="action actionPay"></td>
				<td>Passa l'ordine al Cassiere per il pagamento</td>
				<td></td>
			</tr>
			<tr>
				<td class="action actionNotIncomingOrder"></td>
				<td>Merce non arrivata: l'ordine ritorna allo stato "in carico al referente prima della consegna".</td>
				<td></td>
			</tr>
			<tr>
				<td class="action actionClose"></td>
				<td>Chiudi ordine: l'ordine non viene passato al cassiere o al tesoriere per gestirne il pagamento.</td>
				<td></td>
			</tr>
		</tbody>
	</table>
</div>
<div class="alert alert-info" role="alert">
	<strong>*</strong> si veda il manuale sui <a href="moduli.php">moduli</a> di PortAlGas
</div>

	
	<h2 id="ordine-chiuso">Ordine chiuso</h2>


<div class="table-responsive">
   <table class="table table-bordered">
		<thead>
			<tr>
				<th colspan="2">Icona</th>
				<th>Nota</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td style="width: 32px;" class="orderStatoCLOSE"></td>
				<td>Chiuso</td>
				<td>
					Si è in questo stato quando il referente ha effettuato tutte le operazioni sull'ordine
				</td>
			</tr>
		</tbody>
	</table>
</div> 

<p>Di seguito le azioni permesse al referente durante questa fase</p>

<div class="table-responsive">
   <table class="table table-bordered">
		<thead>
			<tr>
				<th>Icona</th>
				<th>Azione</th>
				<th>Modulo *</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="action actionView"></td>
				<td>Visualizza l'ordine</td>
				<td></td>
			</tr>
			<tr>
				<td class="action actionPay"></td>
				<td>Richiesta di pagamento: questa funzionalità è disponibile solo per i G.A.S. configurati per la gestione con "pagamento <strong>dopo</strong> la consegna"</td>
				<td></td>
			</tr>
			<tr>
				<td class="action actionPrinter"></td>
				<td>Stampare i diversi report di stampa</td>
				<td><a title="vai alla pagina del manuale" href="moduli.php#modulo-per-la-gestione-delle-stampe">Gestione delle stampe</a></td>
			</tr>
		</tbody>
	</table>
</div>
<div class="alert alert-info" role="alert">
	<strong>*</strong> si veda il manuale sui moduli di PortAlGas
</div>


	<h2 id="cassiere-consegna-da-chiudere">Chiusura delle consegne</h2>

	<p>Se il G.A.S. è configurato per la gestione con "pagamento alla consegna", il cassiere dovrà gestire la <strong>chiusura ella consegna</strong>:</p>

<p>quando tutti gli ordini associati ad una consegna sono stai pagati da tutti i gasisti, il cassiere dovrà accedere al modulo per gestire lo "stato della consegna" cliccando su Cassiere -> Gestisci le consegne</p>

<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/cassiere-ordini-chiudere.png"></a>

<p>e cliccando <div class="action actionOpen"></div> su chiudere la consegna</p>

<p>PortAlGas segnala con un messaggio la presenza di questi ordini</p>

<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/ordini-cassiere-msg-chiudere.png"></a>


				
	
					
		</div> <!-- col-sm-8 -->
		
		<div class="col-sm-3" role="complementary">
				
				<ul id="myScrollspy" class="nav nav-contenitore nav-stacked affix-top hidden-print hidden-xs hidden-sm" data-spy="affix" data-offset-top="125">
					<li><a href="/gestione_degli_ordini.php#gli-ordini-su-portalgas">Gli ordini su PortAlGas</a></li>
					<li class="active"><a href="#il-ciclo-di-un-ordine-i-suoi-stati">Il ciclo di un ordine: i suoi stati</a>
					<ul class="nav">
					<li><a href="#ordine-incompleto">Ordine incompleto</a>
						<ul class="nav">
							<li><a href="#ripristino-dati-dell-ordine">Ripristino dati dell'ordine</a></li>
						</ul>						
					</li>
					<li><a href="#prossima-apertura">Prossima apertura</a></li>
					<li><a href="#ordine-aperto">Ordine aperto</a></li>
					<li><a href="#in-carico-al-referente-prima-della-consegna">In carico al referente prima della consegna</a>
						<ul class="nav">
							<li><a href="#gestione-con-pagamento-dopo-la-consegna">Gestione con “pagamento dopo la consegna”</a></li>
							<li><a href="#gestione-con-pagamento-alla-consegna">Gestione con “pagamento alla consegna”</a></li>
						</ul>
					</li>
					<li><a href="#in-carico-al-referente-dopo-la-consegna">In carico al referente dopo la consegna</a></li>
					<li><a href="#in-carico-al-referente-con-la-merce-arrivata">In carico al referente con la merce arrivata</a></li>
					<li><a href="#ordine-chiuso">Ordine chiuso</a>
						<ul class="nav">
							<li><a href="#cassiere-consegna-da-chiudere">Chiusura delle consegne</a></li>
						</ul>					
					</li>
					</ul>
					</li>
				</ul>
		
		
		</div> <!-- col-sm-3 -->
	
	</div>	  <!-- container -->
  
<?php require('_inc_footer.php');?>
