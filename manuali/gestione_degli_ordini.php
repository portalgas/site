<?php require('_inc_header.php');?>
  
    <div class="container">
      

	  



	
	  
	
							


        <div class="col-sm-8 cakeContainer" role="main">

	<h1 id="gli-ordini-su-portalgas" class="page-header">Gli ordini su PortAlGas</h1>
			
	<p>Ogni referente potrà gestire solamente gli ordini dei produttori per i quali è abilitato.</p>
	<p>Gli ordini hanno degli “stati dell'ordine” che influiscono sulle azioni che gli attori di PortAlGas (gasisti registrati, referenti, cassiere o tesoriere) possono compiere.</p>
		
	<h1 id="creazione-di-un-ordine">Creazione di un ordine</h1>
	
	<p>Clicchiamo su Ordini -> Crea un nuovo ordine</p>
	
	<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/ordini-header-index.png" /></a>
					
	<p>si presenterà la maschera di inserimento di un nuovo ordine, organizzata in 6 differenti tabs:</p>

	<ol> 
		<li>Dati dell'ordine</li>
		<li>Invio mail</li>
		<li>Visualizzazione per gli utenti</li>
		<li>Gestione dopo la consegna o Gestione dopo l'arrivo della merce</li>
		<li>Referenti</li>
		<li>Fattura</li>
	</ol>

	<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/ordini-tabs.png" /></a>

	<h2 id="il-tab-dei-dati-dell-ordine">Il Tab dei "Dati dell'ordine"</h2>
	
	<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/ordini-tab-dati-ordine.jpg" /></a>

	<p>La maschera è composta dai seguenti campi</p>

<div class="table-responsive">
   <table class="table table-bordered">
        <thead>	
 <tr>
  <th>Campo</th>
  <th>Nota</th>
 </tr>
        </thead>
        <tbody> 
 <tr>
 	<td>Il produttore</td>
 	<td></td>
 </tr>	
 <tr>
 	<td>La consegna</td>
 	<td>
 hai 3 possibilità:
<ul>
	<li>scegliere una consegna esistente</li>
	<li>o
		<ul>
			<li>se sei gestore delle consegne, creare una nuova consegna.</li>
			<li>se NON sei gestore delle consegne, inviare una mail ai gestori delle consegne per richiederne una</li>
		</ul>
	</li>
	<li>creare una consegna con DATA da DEFINIRE</li>
</ul>	
 	</td>
 </tr>	
 <tr>
 	<td>La data di inizio dell'ordine</td>
 	<td>Indica da quando i gasisti potranno fare i loro acquisti</td>
 </tr>
 <tr>
 	<td>La data di chiusura dell'ordine</td>
 	<td>Indica fino a quando i gasisti potranno fare i loro acquisti</td>
 </tr>	
 <tr>
 	<td>Nota</td>
 	<td>
 	Testo che comparirà ai gasisti nel sito di portalgas.it nella voce di menù “Consegne”: il testo inserito nella nota è sempre in ROSSO e si tratta di testo BREVE (un massimo di 50 caratteri) che accompagnerà sempre l'Ordine.
 	<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/ordini-tab-dati-ordine-campo-nota.jpg" /></a>
	
	<p>Se devi inserire un testo molto lungo <a href="faq.php#aggiungere-un-messaggio-per-i-gasisti">leggi qui</a></p>
 	</td>
 </tr>	
       </tbody>
    </table>
</div>  

<div role="alert" class="alert alert-info">
	<strong>Nota: </strong> la gestione delle consegne è un compito del manager delle consegne del G.A.S.
</div>	
	
	<h2 id="il-tab-invio-mail">Il Tab "Invio mail"</h2>
	
	<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/ordini-tab-invio-mail.png" /></a>
	
	<p>La maschera è composta dai seguenti campi</p>

<p>
<div class="table-responsive">
   <table class="table table-bordered">
        <thead>
 <tr>
  <th>Campo</th>
  <th>Nota</th>
 </tr>
       </thead>
        <tbody> 
 <tr>
 	<td>Testo aggiuntivo della mail all'apertura dell'ordine</td>
 	<td>
 	Inserire in questo campo l'eventuale testo da aggiunge al corpo della mail
 	</td>
 </tr>		
       </tbody>
    </table>
</div>  
</p>	


<p>Per incollare il testo correttamente usa l'inconcina apposita cerchiata di rosso nello screenshot sottostante</p>

<p><a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/ordini-tab-invio-mail1.png" /></a></p>

<p>e otterrai </p>

<p><a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/ordini-tab-invio-mail1.png" /></a></p>
		
		
<p>Ecco come comparirà la nota nel corpo della mail che verrà inviata ai gasisti</p>

<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/ordini-tab-invio-mail-nota.jpg" /></a>

<p> 	
<div role="alert" class="alert alert-info">
	<strong>Nota: </strong> se hai problemi a ricevere la mail <a href="problemi.php#non-ricevo-le-mail-di-portalgas">leggi qui</a>
</div>	
</p>
 	
<p>
	Il testo aggiuntivo della mail all'apertura dell'ordine sarà anche visibile ai gasisti loggati sul front-end (www.portalgas.it) nel "Tab consegne" cliccando sull'icona <img src="http://www.portalgas.it/images/cake/apps/32x32/messenger.png" /> evidenziata nell'immagine sottostante
</p>	
<p>
	<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/front-end-testo-mail.png" /></a>
</p>
	
	<h2 id="il-tab-visualizzazione-per-gli-utenti">Il Tab Visualizzazione "Per gli utenti"</h2>
	
	<p>Gestisce la visualizzazione per i gasisti al momento che dovranno effettuare gli acquisti.</p>

	<p>Sono infatti previste 2 distinte modalità:</p>
	<ol>
		<li>modalità senza le immagini degli articoli</li>
		<li>modalità con le immagini degli articoli</li>
	</ol>
	<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/ordini-tab-visualizzazione-per-gli-utenti.jpg" /></a>
 	 
	<p>Quando si crea un ordine non si può scegliere se potrà essere visualizzato in modalità con o senza immagini.</p>
	<p>Solo dopo che sono stati associati gli articoli, PortAlGas controlla quanti articoli associati hanno immagini: se almeno <b>80%</b> degli articoli associati presentano immagini, l'ordine sarà in modalità con le immagini.</p>
	<p>Il referente potrà comunque andare in modifica dell'ordine e setta la modalità che preferisce.</p>

	<h2 id="il-tab-gestione-durante-l-ordine">Il Tab Gestione "Durante l'ordine"</h2>
	
	<p>Qualora fosse necessario è possibile impostare 2 limiti sull'ordine</p>
	<ul>
		<li>un limite sulla quantità totale dell'ordine</li>
		<li>un limite sull'importo totale dell'ordine</li>
	</ul>	
	
	<p>Raggiunto tale limite</p>
	<p>
	<ul>
		<li>verrà inviata una mail ai referenti dell'ordine</li>
		<li>verrà chiuso automaticamente l'ordine così da non permettere ai gasisti di effettuare ordini</li>
	</ul>
	</p>
	
	<p>Lasciare i valori a 0 se non si vuole impostare alcun limite.</p>
	
	
	<p>Se si imposta un limite sulla quantità totale dell'ordine si dovrà scegliere l'unità di misura di riferimento tra le seguenti</p>
	<ul>
		<li>KG, prenderà in considerazione anche gli Hg, Gr</li>
		<li>LT, prenderà in considerazione anche gli Hl, Ml</li>
		<li>Pezzi</li>
	</ul>
	
	<p>Quindi, per esempio, se si dovesse scegliere KG, sarà calcolata la quantità totale solo degli acquisti di articoli che hanno come unità di misura KG, HG e GR.</p>
	
	<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/ordini-tab-gestione-durante-l-ordine.png" /></a>
	
	<h2 id="il-tab-gestione-dopo-la-consegna-o-gestione-dopo-l-arrivo-della-merce">Il Tab Gestione "Dopo la consegna o dopo l'arrivo della merce"</h2>
	
	Se il G.A.S. è configurato
	<ul>
		<li>per la gestione con “pagamento <strong>dopo</strong> la consegna” il Tab avrà come titolo "Dopo la consegna"</li>
		<li>per la gestione con “pagamento <strong>alla</strong> consegna” il Tab avrà come titolo "Dopo l'arrivo della merce"</li>
	</ul>
	<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/ordini-tab-gestione-dopo-la-consegna.png" /></a>
	
	<p>La maschera è composta dai seguenti campi</p>
<div class="table-responsive">
   <table class="table table-bordered">
        <thead>
 <tr>
  <th>Campo</th>
  <th>Nota</th>
            </tr>
        </thead>
        <tbody>
 <tr>
 	<td>Gestione degli acquisti</td>
 	<td>
 	Permette di gestire l'ordine con 2 distinte modalità:
	<ol>
		<li>Gestisci gli acquisti aggregati per l'importo degli utenti</li>
		<li>Gestisci gli acquisti dividendo le quantità di ogni acquisto</li>
	</ol>
	Per entrambe le modalità si veda il manuale sui <a title="vai alla pagina del manuale" href="moduli.php">moduli di PortAlGas</a></td>
 </tr>		
 <tr>
 	<td>Spese di trasporto</td>
 	<td>Successivamente si potrà gestire la spesa di trasporto attraverso il “modulo del trasporto” (si veda il manuale sui <a title="vai alla pagina del manuale" href="moduli.php#modulo-per-la-gestione-del-trasporto">moduli di PortAlGas)</td>
 </tr>		
       </tbody>
    </table>
</div>  
  
	<h2 id="il-tab-referenti">Il Tab "Referenti"</h2>
	<p>Il tab referenti presenta l'elenco dei referenti associati al produttore dell'ordine. Il Tab comparirà solo dopo la creazione dell'ordine</p>
	<h2 id="il-tab-fattura">Il Tab "Fattura"</h2>
	
	Questo tab sarà presente solo per i G.A.S. Configurati per la gestione con “pagamento dopo la consegna

ll tab fattura presenta le seguenti informazioni:
<ul>
	<li>la fattura uplodate dal referente</li>
	<li>L'importo della fattura</li>
	<li>L'Importo totale dell'ordine</li>
	<li>La differenza tra l'importo della fattura e l'Importo totale dell'ordine</li>
	<li>La nota del referente quando ha trasmesso l'ordine al tesoriere</li>
	<li>Stato del pagamento</li>
</ul>
											
	<h1 id="associazione-degli-articoli-del-produttore-all-ordine">Associazione degli articoli del produttore all'ordine</h1>
	
	<p>Ora possiamo scegliere quali articoli, tra quelli del produttore scelto, associare all'ordine.</p>
	<p>In questa fase possiamo settare alcuni valori degli articoli che saranno validi solo per l'ordine in questione</p>				
		
	<ul>
		<li>il Prezzo</li>
		<li>il numero di pezzi in una confezione</li>
		<li>la quantità minima che un gasista può ordinare</li>
		<li>la quantità massima che un gasista può ordinare</li>
		<li>multipli di</li>
		<li>quantità minima rispetto a tutti gli acquisti</li>
		<li>quantità massima rispetto a tutti gli acquisti</li>
	</ul>
			
	<p>Di default questi campi hanno il valore preso dall'anagrafica degli articoli.</p>
	<p>Di seguito il tab “Condizioni d'acquisto” dell'anagrafica degli articoli dove si valorizzano questi valori:</p>
	
	<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/articoli-tab-condizioni-aquisto.png" /></a>
	
	<p>e sotto gli stessi campi come vengono presentati durante l'associazione degli articoli ad un determinato ordine. Se si desidera si possono modificare i valori, se no si prendono quelli valorizzati nell'anagrafica dell'articolo.</p>
	
	Questi sono:
<p>
	<ul>
		<li>Quantità minima che un gasista può acquistare: di default 1.</li>
		<li>Quantità massima che un gasista può acquistare: di default 0 per indicare che non ci sono limiti.</li>
		<li>Pezzi di una confezione (i colli): indica di quanti elementi è composto un eventuale collo. Di default 1: se è valorizzato a 1 l'articolo non fa parte di un collo.</li>
		<li>Multipli di: di default 1.</li>
		<li>Quantità minima rispetto a tutti gli acquisti: : di default 0 per indicare che non ci sono limiti.</li>
		<li>Quantità massima rispetto a tutti gli acquisti: di default 0 per indicare che non ci sono limiti. se indicato un valore maggiore di zero, quando verrà raggiunta tale quantità:
			<ul>
				<li>l'acquisto sull'articolo sarà bloccato</li>
				<li>PortAlGas invierà una mail ai referenti</li>
			</ul>
		</li>
	</ul>	
</p>

<div role="alert" class="alert alert-info">
	<strong>Nota: </strong> in questo elenco verranno presentati solo gli articoli con i campi
	<ul>
		<li>"Stato" a <span style="color:green">Si</font></li>
		<li>"Presente tra gli articoli da ordinare" a <span style="color:green">Si</font></li>
	</ul>
	<p>Maggiori informazioni, clicca su <a href="/gestione_degli_articoli.php#gestione-visibilita-degli-articoli">Gestione visibilità degli articoli</a></p>
</div>



<h2 id="associazione-degli-articoli-dell-ordine-precedente">Associazione degli articoli dell'ordine precedente</h2>

<p>Qualora si volesse creare un ordine associato ad produttore ed esistesse già un suo ordine precedente, il sistema presenterà la possibilità di associare gli articoli del precedente ordine a quello che si desidera creare.</p>

<p>Si avrà sempre la possibilità di modificare gli articoli associati una volta salvati</p>

<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/associazione-degli-articoli-all-ordine.png" /></a>
		

		
	<h1 id="invio-della-mail-ai-gasisti-per-notificare-l-apertura-e-la-chiusura-dell-ordine">Invio della mail ai gasisti per notificare l'apertura e la chiusura dell'ordine</h1>
	
	<p>Un giorno prima dell'apertura dell'ordine sarà inviata una mail a tutti i gasisti per comunicarne l'apertura.</p>
			<ul>
				<li>Se indichi il giorno di apertura dell'ordine uguale al giorno odierno la mail a tutti i gasisti sarà inviata <strong>domani</strong>.</li>
				<li>Se indichi il giorno di apertura dell'ordine precedente al giorno odierno <strong>non</strong> sarà inviata alcuna mail.</li>
			</ul>
 
	<p>Di seguito un esempio di mail inviata ai gasisti per notificare l'apertura dell'ordine riportando a nota che il referente ha inserito durante la creazione dell'ordine</p>

	<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/ordini-tab-invio-mail-nota.jpg" /></a>
	
	<p>Tre giorni prima della <strong>chiusura</strong> dell'ordine sarà inviata una mail a tutti i gasisti per comunicarne la chiusura.</p>
	

<p> 	
<div role="alert" class="alert alert-info">
	<strong>Nota: </strong> se hai problemi a ricevere la mail <a href="problemi.php#non-ricevo-le-mail-di-portalgas">leggi qui</a>
</div>	
</p>	
	
	<h1 id="ecco-creato-l-ordine">Ecco creato l'ordine!</h1>
	
	<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/ordini-elenco.jpg" /></a>
	<p>In questa fase l'ordine potrà avere i seguenti “stati dell'ordine”:</p>

<div class="table-responsive">
   <table class="table table-bordered">
        <thead>
 <tr>
  <th colspan="2">Icona</th>
  <th>Se da data di apertura dell'ordine è…</th>
            </tr>
        </thead>
        <tbody>
 <tr>
 	<td class="orderStatoOPEN-NEXT" style="width: 32px;"></td>
 	<td>Prossima apertura</td>
 	<td>posteriore alla data odierna</td>
 </tr>		
  <tr>
 	<td class="orderStatoOPEN"></td>
 	<td>Aperto</td>
 	<td>Anteriore alla data odierna</td>
 </tr>		
        </tbody>
    </table>
</div> 

	<p>Oppure</p>

<div class="table-responsive">
   <table class="table table-bordered">
        <thead>
 <tr>
  <th colspan="2">Icona</th>
  <th>Se da data di apertura dell'ordine è…</th>
 </tr>
         </thead>
        <tbody>
 <tr>
 	<td class="orderStatoCREATE-INCOMPLETE" style="width: 32px;"></td>
 	<td>Creato ma incompleto</td>
 	<td>Hai creato l'odine ma non hai ancora associato gli articoli,<br />
	clicca su <div class="action actionEditCart"></div> e completa l'ordine</td>
 </tr>		
        </tbody>
    </table>
</div> 
	
		
	<h1 id="monitoriamo-il-nostro-ordine">Monitoriamo il nostro ordine</h1>
	
	<p>Cliccando su Home dell'area di backoffice, il referente, avrà a disposizione 3 report per monitorare l'ordine:</p>
		<ol>
			<li>report con gli articoli aggregati (se gli articoli associati all'ordine gestiscono la quantità massima o i colli saranno visualizzati 2 colonne apposite con i rispettivi valori)</li>
			<li>report con gli articoli aggregati con il dettaglio dei gasisti</li>
			<li>report con gli articoli divisi per gasista</li>
		</ol>

		<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/home-ordini-monitor.gif" class="img-responsive"></a>

		<p>Cliccare su <div class="action actionWorkflow"></div> per accede alla Home dell'ordine</p>
		<p>Cliccare su <div class="action actionMenu"></div> per aprire il menù dell'ordine</p>
			
		
	<h1 id="il-front-end-l-ordine-da-parte-dei-gasisti">Il Front-end: l'ordine da parte dei gasisti</h1>

	<p>Per visualizzare come si presenta ad un gasista l'ordine appena creato <a href="front-end.php#la-voce-di-menu-consegne">leggi qui</a></p>
	


	<h1 id="monitorare-alcuni-ordini">Monitorare alcuni ordini</h1>
				
<p>Questo modulo è disponibile solo per gli utenti che fanno parte del gruppo "Super-Referente".</p>
Per accedere al modulo:
<ul>
	<li>Cliccando dal menù su Referenti => Ordini => Monitoraggio Ordini</li>
	<li>Dalla home cliccare su <img border="0" title="ci sono prodotti ordinati" src="http://www.portalgas.it/manuali/images/ico-monitorare-ordini.png" /></li>
</ul>

Qui si visualizzeranno gli ordini che il Super-Referente ha scelto di monitorare. Avrà a disposizione 3 differenti report:
<ol>
	<li>report con gli articoli aggregati (se gli articoli associati all'ordine gestiscono la quantità massima o i colli saranno visualizzati 2 colonne apposite con i rispettivi valori)</li>
	<li>report con gli articoli aggregati con il dettaglio dei gasisti</li>
	<li>report con gli articoli divisi per gasista</li>
</ol>

<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/monitorare-ordini.gif" class="img-responsive"></a>

<p>Cliccare su <div class="action actionWorkflow"></div> per accede alla Home dell'ordine</p>
<p>Cliccare su <div class="action actionMenu"></div> per aprire il menù dell'ordine</p>

<p>Cliccare si "Gestisci il monitoraggio degli ordini" si potranno scegliere quali ordini monitorare o quali eliminare dall'elenco di quelli da monitorare.</p>

<p>Quando l'ordine assumerà lo stato di "chiuso" verrà automaticamente eliminato tra quelli da monitorare.</p>

<p>E' una funzionalità che potrebbe servire qualora il SuperReferente volesse monitorare l'operato di un referente poco pratico di PortAlGas o di un ordine particolarmente difficile.</p>		
		
					
					
		</div> <!-- col-sm-8 -->
		
		<div class="col-sm-3" role="complementary">
				
				<ul id="myScrollspy" class="nav nav-contenitore nav-stacked affix-top hidden-print hidden-xs hidden-sm" data-spy="affix" data-offset-top="125">
					<li class="active"><a href="#gli-ordini-su-portalgas">Gli ordini su PortAlGas</a></li>
					<li><a href="#creazione-di-un-ordine">Creazione di un ordine</a>
						<ul class="nav">
					<li><a href="#il-tab-dei-dati-dell-ordine">Il Tab dei "dati dell'ordine"</a></li>
					<li><a href="#il-tab-invio-mail">Il Tab "Invio mail"</a></li>
					<li><a href="#il-tab-visualizzazione-per-gli-utenti">Il Tab visualizzazione "Per gli utenti"</a></li>
					<li><a href="#il-tab-gestione-durante-l-ordine">Il Tab gestione "Durante l'ordine"</a></li>
					<li><a href="#il-tab-gestione-dopo-la-consegna-o-gestione-dopo-l-arrivo-della-merce">Il Tab gestione "Dopo la consegna o Dopo l'arrivo della merce"</a></li>
					<li><a href="#il-tab-referenti">Il Tab "Referenti"</a></li>
					<li><a href="#il-tab-fattura">Il Tab "Fattura"</a></li>
						</ul>
					</li>	
					<li><a href="#associazione-degli-articoli-del-produttore-all-ordine">Associazione degli articoli del produttore all'ordine</a>
						<ul  class="nav">
							<li><a href="#associazione-degli-articoli-dell-ordine-precedente">Associazione degli articoli dell ordine precedente</a></li>
						</ul>
					</li>
					<li><a href="#invio-della-mail-ai-gasisti-per-notificare-l-apertura-e-la-chiusura-dell-ordine">Invio della mail ai gasisti per notificare l'apertura e la chiusura dell'ordine</a></li>
					<li><a href="#ecco-creato-l-ordine">Ecco creato l'ordine!</a></li>
					<li><a href="#monitoriamo-il-nostro-ordine">Monitoriamo il nostro ordine</a></li>
					<li><a href="#il-front-end-l-ordine-da-parte-dei-gasisti">Il Front-end: l'ordine da parte dei gasisti</a></li>
					<li><a href="#monitorare-alcuni-ordini">Monitorare alcuni ordini</a></li>
					<li><a href="/gestione_degli_ordini_ciclo.php#il-ciclo-di-un-ordine-i-suoi-stati">Il ciclo di un ordine: i suoi stati</a>
				</ul>
		
		
		</div> <!-- col-sm-3 -->
	
	</div>	  <!-- container -->
  
<?php require('_inc_footer.php');?>
