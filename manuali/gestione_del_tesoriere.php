<?php require('_inc_header.php');?>
 
    <div class="container">
      

	  



	
	  
	
							


        <div class="col-sm-8 cakeContainer" role="main">



<h1 class="page-header" id="la-gestione-del-tesoriere-su-portalgas">La gestione del Tesoriere su PortAlGas</h1>

<p>PortAlGas permette di gestire il Tesoriere attraverso il menù "Tesoriere", che è così composto:</p>
<p>Solamente se il G.A.S. configurati per la gestione con "pagamento <strong>dopo</strong> la consegna", sono disponibili le seguenti voci:</p>
<ul>
	<li>Prendi in carico ordini</li>
	<li>Gestione ordini in elaborazione</li>
	<li>Gestione del pagamento degli ordini</li>
</ul>

<p>Per tutti i G.A.S. sono disponibili le seguenti voci:</p> 
<p>
<ul>
	<li>Pagamento Produttori storico</li>
	<li>Pagamento Produttori</li>
</ul>	
</p>

<h1 class="page-header" id="il-tesoriere-e-il-ciclo-di-un-ordine">Il Tesoriere e il ciclo di un ordine</h1>

<p>Gli ordini hanno degli "stati dell'ordine" che influiscono sulle azioni che gli attori di PortAlGas (gasisti registrati, referenti, cassiere o tesoriere) possono compiere.</p>
<p>Per i G.A.S. configurati per la gestione con "pagamento <strong>dopo</strong> la consegna" i referenti gestiranno i seguenti stati dell'ordine</p>
<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/ordini-stati-pagamento-dopo-consegna.png"></a>
<p>Per maggior dettagli si veda il manuale su "<a href="gestione_degli_ordini.php#il-ciclo-di-un-ordine-i-suoi-stati" title="vai alla pagina del manuale">Il ciclo di un ordine: i suoi stati</a>"</p>
<p>Come si può vedere ci sono "stati dell'ordine" durante i quali possono agire solamente</p>
<ul>
	<li>gli utenti</li>
	<li>i referenti dei produttori</li>
	<li>il tesoriere</li>
</ul>	

<p>Prendiamo in considerazione solamente gli "stati dell'ordine" che interessano sia i referenti ce i tesorieri</p>
<div class="table-responsive">
   <table class="table table-bordered">
        <thead>
		 <tr>
		  <th colspan="2">Stato dell'ordine</th>
		  <th colspan="2">Referente</th>
		  <th colspan="2">Tesoriere</th>
         </tr>
        </thead>
        <tbody>
		 <tr>
			<td class="orderStatoPROCESSED-POST-DELIVERY" style="width: 32px;"></td>
			<td>In carico al referente dopo la consegna</td>
			<td class="action actionFromRefToTes"></td>
			<td>Passa l'ordine al tesoriere</td>
			<td></td>
			<td></td>
		  </tr>
		 <tr>
			<td class="orderStatoWAIT-PROCESSED-TESORIERE" style="width: 32px;"></td>
			<td>In attesa che il tesoriere lo prenda in carico</td>
			<td class="action actionFromTesToRef"></td>
			<td>Riporta l'ordine al referente</td>
			<td class="action actionFromRefToTes"></td>
			<td>Prendi in carico l'ordine</td>
		  </tr>
		 <tr>
			<td class="orderStatoPROCESSED-TESORIERE" style="width: 32px;"></td>
			<td>In carico al Tesoriere</td>
			<td></td>
			<td></td>
			<td></td>
			<td>
				<p>Passa l'ordine allo stato</p>
				<ul>
					<li>In attesa che il tesoriere lo prenda in carico</li>
					<li>Possibilità di chiederne il pagamento</li>
				</ul>
			</td>
		  </tr>
		 <tr>
			<td class="orderStatoTO-PAYMENT" style="width: 32px;"></td>
			<td>Possibilità di chiederne il pagamento</td>
			<td></td>
			<td></td>
			<td class="action actionPay"></td>
			<td>Richiedi il pagamento</td>
		  </tr>
       </tbody>
    </table>
</div>



<h1 class="page-header" id="prendere-in-carico-gli-ordini">Prendere in carico gli ordini</h1>

<p>Cliccando su questa voce di menù si accede al modulo per prendere in carico gli ordini che sono nel seguente "stato dell'ordine":</p>

<div class="table-responsive">
   <table class="table table-bordered">
        <tbody>
		 <tr>
			<td class="orderStatoWAIT-PROCESSED-TESORIERE" style="width: 32px;"></td>
			<td>In attesa che il tesoriere lo prenda in carico</td>
		  </tr>
       </tbody>
    </table>
</div>

<p>Scegliendo una consegna</p>

<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/tesoriere-ordini-wait-processed-filtra-consegna.png" class="img-responsive"></a>

<p>si presenteranno tutti gli ordini associati</p>

<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/tesoriere-ordini-wait-processed-tesoriere.png" class="img-responsive"></a>

<p>Per ogni riga, corrispodente ad un ordine, è visibile, se presente</p>
<p>
<ul>
	<li>la fattura</li>
	<li>l'importo della fattura</li>
	<li>l'importo dell'ordine</li>
	<li>l'eventuale nota del referente</li>
</ul>
</p>

<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/tesoriere-dettaglio-fattura.png" class="img-responsive"></a>

<p>Checcando gli ordini interessati, il Tesoriere potrà effettuare le seguenti operazioni</p>

<div class="table-responsive">
   <table class="table table-bordered">
        <thead>
		 <tr>
		  <th>Azione</th>
		  <th>Nota</th>
         </tr>
        </thead>   
        <tbody>
		 <tr>
			<td>Prendi in carico gli ordini</td>
			<td>Il referente non potrà più modificarlo</td>
		  </tr>
       </tbody>
    </table>
</div>



<h1 class="page-header" id="la-gestione-ordini-in-elaborazione">La gestione ordini in elaborazione</h1>

<p>Cliccando su questa voce di menù si accede al modulo per gestire gli ordini che sono stati presi in carico e che attendono di essere elaborati dal Tesoriere, quindi degli ordini che sono nel seguente "stato dell'ordine":</p>

<div class="table-responsive">
   <table class="table table-bordered">
        <tbody>
		 <tr>
			<td class="orderStatoPROCESSED-TESORIERE" style="width: 32px;"></td>
			<td>In carico al tesoriere</td>
		  </tr>
       </tbody>
    </table>
</div>

<p>Scegliendo una consegna</p>

<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/tesoriere-ordini-processed-filtra-consegna.png" class="img-responsive"></a>

<p>si presenteranno tutti gli ordini associati</p>

<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/tesoriere-ordini-processed-tesoriere.png" class="img-responsive"></a>

<p>Per ogni riga, corrispodente ad un ordine, è visibile, se presente</p>
<p>
<ul>
	<li>la fattura</li>
	<li>l'importo della fattura</li>
	<li>l'importo dell'ordine</li>
	<li>l'eventuale nota del referente</li>
</ul>
</p>

<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/tesoriere-dettaglio-fattura.png" class="img-responsive"></a>

<p>Checcando gli ordini interessati, il Tesoriere potrà effettuare le seguenti operazioni</p>

<div class="table-responsive">
   <table class="table table-bordered">
        <thead>
		 <tr>
		  <th>Porta l'ordine allo stato</th>
		  <th>Nota</th>
         </tr>
        </thead>   
        <tbody>
		 <tr>
			<td>In attesa che il tesoriere lo prenda in carico</td>
			<td>L'ordine viene restituito al referente</td>
		  </tr>
		 <tr>
			<td>Possibilità di chiederne il pagamento</td>
			<td>L'ordine è valido e si potrà richiederne il pagamento</td>
		  </tr>
       </tbody>
    </table>
</div>


<h1 class="page-header" id="la-gestione-del-pagamento-degli-ordini">La gestione del pagamento degli ordini</h1>

<p>Cliccando su questa voce di menù si accede al modulo per gestire il pagamento degli ordini, quindi degli ordini che sono nel seguente "stato dell'ordine":</p>

<div class="table-responsive">
   <table class="table table-bordered">
        <tbody>
		 <tr>
			<td class="orderStatoTO-PAYMENT" style="width: 32px;"></td>
			<td>Possibilità di chiederne il pagamento</td>
		  </tr>
       </tbody>
    </table>
</div>

<p>Ad una richiesta di pagamento si possono associare:</p>
<ul>
	<li>N. ordini (che siano nello "stato d'ordine" di "Possibilità di chiederne il pagamento")</li>
	<li>N. voci di spesa generici da associare a tutti o alcuni utenti</li>
</ul>

<p>Le richieste di pagamento possono avere i seguenti stati:</p>
<div class="table-responsive">
   <table class="table table-bordered">
        <thead>
		 <tr>
		  <th colspan="2">Stato della richiesta</th>
		  <th>Nota</th>
		  <th>Azione<br />da parte di</th>
         </tr>
        </thead>   
        <tbody>
		 <tr>
			<td class="stato_t" title="In lavorazione" style="width: 30px;"></td>
			<td>In lavorazione</td>
			<td>La richiesta di pagamento non è ancora visibile agli utenti, il tesoriere può modificarla</td>
			<td>Tesoriere</td>
		  </tr>
		 <tr>
			<td class="stato_open" title="Aperta per richiedere il pagamento"></td>
			<td>Aperta per richiedere il pagamento</td>
			<td>
				La richiesta di pagamento è aperta: 
				<ul>
					<li>viene inviata una mail agli utenti per avvisarli di effettuare il pagamento</li>
					<li>è ora visibile agli utenti</li>
				</ul>			
			</td>
			<td>Tesoriere</td>
		  </tr>
		 <tr>
			<td class="stato_close" title="Chiuso"></td>
			<td>Chiuso</td>
			<td>
				Tutti pagamenti sono stati effettuati, la richiesta di pagamento è chiusa
			</td>
			<td>PortAlGas</td>
		  </tr>
       </tbody>
    </table>
</div>



<h2 id="elenco-delle-richiesta-di-pagamento">Elenco delle richiesta di pagamento</h2>	

<p>Cliccando su questa voce di menù si accede al modulo per gestire il pagamento degli ordini e si presenterà l'elenco delle richieste di pagamento</p>

<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/tesoriere-rich-pagamento-elenco.png" class="img-responsive"></a>

<p>da qui sono presenti 3 icone che permettono di eseguire 3 differenti azioni, di seguito illustrare</p>

<div class="table-responsive">
   <table class="table table-bordered">
		<thead>
			<tr>
				<th>Icona</th>
				<th>Azione</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="action actionOpen"></td>
				<td>Modifico lo stato della richiesta di pagamento</td>
			</tr>
			<tr>
				<td class="action actionEdit"></td>
				<td>Modifico la richiesta di pagamento
					<ul>
						<li>Aggiungo / Cancello gli ordini</li>
						<li>Aggiungo / Cancello le voci di spesa generici</li>
					</ul>
				</td>
			</tr>
			<tr>
				<td class="action actionExcel"></td>
				<td>Esporta la richiesta di pagamento in formato excel</td>
			</tr>
			<tr>
				<td class="action actionDelete"></td>
				<td>Cancello, dopo un ulteriore conferma, la richiesta di pagamento</td>
			</tr>
		</tbody>
	</table>
</div>


<h2 id="nuova-richiesta-di-pagamento">Nuova richiesta di pagamento</h2>

	<p>Clicchiamo su "Nuova richiesta di pagamento", si accederà alla maschera per gestire le richieste di pagamento.</p>

	<p>Ad una richiesta di pagamento si possono associare:</p>
	<ul>
		<li>N. ordini (che siano nello "stato d'ordine" di "Possibilità di chiederne il pagamento")</li>
		<li>N. voci di spesa generici da associare a tutti o alcuni utenti</li>
	</ul>

	<p>La nuova richiesta sarà nello stato "in lavorazione"</p>




<h2 id="richiesta-di-pagamento-in-lavorazione">Richiesta di pagamento in lavorazione</h2>	

<div class="table-responsive">
   <table class="table table-bordered">
        <thead>
		 <tr>
		  <th colspan="2">Stato della richiesta</th>
		  <th>Nota</th>
		  <th>Azione<br />da parte di</th>
         </tr>
        </thead>   
        <tbody>
		 <tr>
			<td class="stato_t" title="In lavorazione" style="width: 30px;"></td>
			<td>In lavorazione</td>
			<td>La richiesta di pagamento non è ancora visibile agli utenti, il tesoriere può modificarla</td>
			<td>Tesoriere</td>
		  </tr>
       </tbody>
    </table>
</div>


<p>In questa fase il tesoriere potrà associare:</p>
<ul>
	<li>N. ordini (che siano nello "stato d'ordine" di "Possibilità di chiederne il pagamento")</li>
	<li>N. voci di spesa generici da associare a tutti o alcuni utenti</li>
</ul>

<p>Si presentarà la seguente videata: l'elenco degli utenti ai quali arriverà la richiesta di pagamento</p>

<p><a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/tesoriere-rich-pag-stato-lavorazione.jpg" class="img-responsive"></a></p>

<p>
PortAlGas presenterà, per ogni utente 
<ul>
	<li><b>Importo dovuto</b>: l'importo calcolato come somma di tutti gli acquisti dell'utente per gli ordini e le voci di spesa inclusi nella richiesta di pagamento</li> 
	<li><b>Importo richiesto</b>: l'importo che sarà realmente richiesto una volta che la richiesta di pagamementi è conclusa</li>
</ul>
</p>

<p>PortAlGas presenterà i 2 importi con il medesimo valore e il tesoriere potrebbe
<ul>
	<li>lasciare invariati gli importi (Importo dovuto = Importo richiesto)</li> 
	<li>inserire un importo diverso: la differenza andrà in cassa</li> 
	<li>qualora l'utente avesse un debito o un credito verso la cassa, fleggare "includi cassa" e "Importo richiesto" sarà ricalcolato rispetto al valore presente in cassa,</li>
</ul>
</p>

<p>Facciamo un esempio con i dati riportati nell'immagine precedente:</p>

<div class="panel panel-default">
 <div class="panel-heading">
  <h3 class="panel-title">001 Rossi Marco ha un credito (colore verde) verso la cassa di 12 € e dovrebbe 1,40 € (Importo dovuto) per la richiesta di pagamento. Di default "Importo richiesto" è di 1,40 €.</h3>
 </div>
 <div class="panel-body">
	<ul>
		<li>Se il tesoriere <b>non include la cassa</b>, a 001 Rossi Marco arriverà una richiesta di pagamento di 1,40 €</li> 
		<li>Se il tesoriere <b>include la cassa</b>, a 001 Rossi Marco arriverà una richiesta di pagamento di 0 € e il suo credito verso la cassa passerà (*) a 10,60 €</li> 
	</ul>
 </div>
</div>

<div class="panel panel-default">
 <div class="panel-heading">
  <h3 class="panel-title">003 Verdi Mario non ha debiti o crediti verso la cassa. Dovrebbe 1,10 € (Importo dovuto) per la richiesta di pagamento. Di default "Importo richiesto" è di 1,10 €.</h3>
 </div>
 <div class="panel-body">
	<ul>
		<li>Se il tesoriere <b>non modifica</b> l'"Importo richiesto", a 003 Verdi Mario arriverà una richiesta di pagamento di 1,10 €</li> 
		<li>Se il tesoriere <b>modifica</b> l'"Importo richiesto" valorizzandolo a 1,00 €, a 003 Verdi Mario arriverà una richiesta di pagamento di 1,00 € e verrà creato un debito (colore rosso) di -0,10 € verso la cassa (*)</li> 
	</ul>
 </div>
</div>

<div class="alert alert-info" role="alert">
	<strong>*</strong> i debiti o credito verso la cassa saranno contabilizzato solamente quando una richiesta di pagamento passa allo stato <b>pagato</b>, si veda la sezione successiva
</div>






<h2 id="richiesta-di-pagamento-in-stato-aperta-per-richiederne-il-pagamento">Richiesta di pagamento in stato "Aperta per richiedere il pagamento"</h2>

<div class="table-responsive">
   <table class="table table-bordered">
        <thead>
		 <tr>
		  <th colspan="2">Stato della richiesta</th>
		  <th>Nota</th>
		  <th>Azione<br />da parte di</th>
         </tr>
        </thead>   
        <tbody>
		 <tr>
			<td class="stato_open" title="Aperta per richiedere il pagamento"></td>
			<td>Aperta per richiedere il pagamento</td>
			<td>
				La richiesta di pagamento è aperta: 
				<ul>
					<li>viene inviata una mail agli utenti per avvisarli di effettuare il pagamento</li>
					<li>è ora visibile agli utenti</li>
				</ul>			
			</td>
			<td>Tesoriere</td>
		  </tr>
       </tbody>
    </table>
</div>


<p>In questa fase il tesoriere potrà, per ogni richiesta di pagamento, settare lo stato in
<ul>
	<li>pagato (verde)</li>
	<li>sollecito uno (giallo)</li>
	<li>sollecito due (giallo)</li>
	<li>sopseso (rosso)</li>
</ul>	


<p>Si presentarà la seguente videata: l'elenco degli utenti ai quali è arrivata la richiesta di pagamento</p>

<p><a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/tesoriere-rich-pag-stato-aperta.jpg" class="img-responsive"></a></p>

<p>Qualora l'importo dovuto e l'importo richiesto fossero diversi, la differenza anche in cassa (crerando un credito o un debito) al momento che la richiesta di pagamento passerà allo stato "pagato"</p>


<div class="alert alert-info" role="alert">
	<strong>Nota:</strong> se si inserisce un "importo pagato" diverso dell'importo richiesto, la differenza non viene inserita automaticamente in cassa, questa operazione dev'essere effettuata manualmente dal cassiere
</div>


<h2 id="la-richiesta-di-pagamento-al-gasista">La richiesta di pagamento al gasista</h2>

	<p>Ecco come appare la richiesta di un pagamento ad un gasista una volta che si è loggato su http://www.portalgas.it</p>

	<p><a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/frontend-rich-pagamento.png" class="img-responsive"></a></p>




<h2 id="chiusura-della-richiesta-di-pagamento">Chiusura della richiesta di pagamento</h2>

	<div class="table-responsive">
	   <table class="table table-bordered">
			<thead>
			 <tr>
			  <th colspan="2">Stato della richiesta</th>
			  <th>Nota</th>
			  <th>Azione<br />da parte di</th>
			 </tr>
			</thead>   
			<tbody>
			 <tr>
				<td class="stato_close" title="Chiuso"></td>
				<td>Chiuso</td>
				<td>
					Tutti pagamenti sono stati effettuati, la richiesta di pagamento è chiusa
				</td>
				<td>PortAlGas</td>
			  </tr>
		   </tbody>
		</table>
	</div>

	<p>Ogni notte, PortAlGas provvederà a chiudere tutte le richieste di pagamento dove ogni utente ha lo stato di</p>
	<ul>
		<li>pagato</li>
		<li>sospeso</li>
	</ul>
		
	<p>Se i pagamenti sono stati effettuati, la riga dell'utente sarà colorata di verde come si può vedere nell'immagine sottostante</p>
	 
	<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/tesoriere-utente-pagato.png" class="img-responsive"></a>

<h1 class="page-header" id="il-pagamento-dei-produttori">Il pagamento dei Produttori</h1>
	
<p>Clicchiamo su "Pagamento produttori", si accederà alla maschera per gestire del pagamento dei produttori.</p>


<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/tesoriere-pagamento-produttori.png" class="img-responsive"></a>

					
		</div> <!-- col-sm-8 -->
		
		<div class="col-sm-3" role="complementary">

				<ul id="myScrollspy" class="nav nav-contenitore nav-stacked affix-top hidden-print hidden-xs hidden-sm" data-spy="affix" data-offset-top="125">
					<li class="active"><a href="#la-gestione-del-tesoriere-su-portalgas">La gestione del Tesoriere su PortAlGas</a></li>
					<li><a href="#il-tesoriere-e-il-ciclo-di-un-ordine">Il Tesoriere e il ciclo di un ordine</a></li>
					<li><a href="#prendere-in-carico-gli-ordini">Prendere in carico gli ordini</a></li>
					<li><a href="#la-gestione-ordini-in-elaborazione">La gestione ordini in elaborazione</a></li>
					<li><a href="#la-gestione-del-pagamento-degli-ordini">La gestione del pagamento degli ordini</a>
					<ul class="nav">
						<li><a href="#elenco-delle-richiesta-di-pagamento">Elenco delle richiesta di pagamento</a></li>
						<li><a href="#nuova-richiesta-di-pagamento">Nuova richiesta di pagamento</a>
						<li><a href="#richiesta-di-pagamento-in-lavorazione">Richiesta di pagamento in lavorazione</a></li>
						<li><a href="#richiesta-di-pagamento-in-stato-aperta-per-richiederne-il-pagamento">Richiesta di pagamento in stato aperta per richiederne il pagamento</a></li>
						<li><a href="#la-richiesta-di-pagamento-al-gasista">La richiesta di pagamento al gasista</a>
						<li><a href="#chiusura-della-richiesta-di-pagamento">Chiusura della richiesta di pagamento</a></li>						
					</ul>
					</li>
					<li><a href="#il-pagamento-dei-produttori">Il pagamento dei Produttori</a></li>
					<li><a href="use-case.php#ordine-dal-referente-al-tesoriere">Caso d'uso: ordine dal referente al tesoriere</a></li>
				</ul>
		
		
		</div> <!-- col-sm-3 -->
	
	
	</div>	  <!-- container -->
  

<?php require('_inc_footer.php');?>