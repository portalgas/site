<?php require('_inc_header.php');?>
 
    <div class="container">
      

	  

        <div class="col-sm-8 cakeContainer" role="main">

		<h1 id="requisiti-per-aprire-un-ordine-condiviso">Requisiti per aprire un ordine condiviso</h1>

			
			<p>Di seguito i requisiti per aprire un <b>ordine condiviso</b> da altri GAS facenti parte del medesimo D.E.S.</p>
			<ul>
			<li>associare uno dei <b>produttori</b> presenti nell'archivio di PortAlGas al D.E.S.</li>
			<li>verificare che altri GAS abbiamo il produttore associato al proprio GAS</li>
			<li>per poter aprire e gestire un ordine condiviso bisogna avere il <b>ruolo</b> di "<b>Titolare del produttore</b>" associato al produttore interessato: in automatico il proprio GAS diventerà anch'esso titolare del produttore.</li>
			</ul>
			</p>

		<h1 id="gestione-ruoli-des">Gestione dei ruoli</h1>

		<p>Il D.E.S. di PortAlGas gestisce i seguenti ruoli
			<ul>
			<li>Manager del D.E.S.</li>
			<li>Titolare ordini condivisi</li>
			<li>Referente D.E.S. degli ordini</li>
			<li>Super-Referente ordini condivisi</li>
			</ul>
		</p>
			
		<p>
			Il <b>Manager del D.E.S.</b> gestisce ruoli rispetto agli utenti del proprio GAS
		</p>
		<p>
			Il <b>Titolare ordini condivisi</b> è il titolare di un produttore associato al D.E.S. e può aprire e gestire un ordine condiviso.<br />
			Quando sarà creato l'ordine condiviso, potrà scegliere quali <b>articoli associare</b> all'ordine e metterli a disposizione ai G.A.S. asosciati al D.E.S. 
		</p>
		<p>
			Il <b>Referente D.E.S. degli ordini</b> &egrave; abilitato a visualizzare gli ordini dei G.A.S.: potr&agrave; visualizzare i report con i dettagli degli acquisti	
		</p>
		<p>
		<div role="alert" class="alert alert-info">
			<strong>Nota: </strong> un produttore potrà avere solamente uno o più utenti titolari appartenenti al <b>medesimo GAS</b>. 
			<br />Per esempio, qualora si desiderasse che il titolare di un produttore passi dal GAS Uno al GAS Due, si dovrà
				<ul>
					<li>Il manager del GAS Uno dovrà eliminare prima i titolari del GAS Uno</li>
					<li>Il manager del GAS Due dovrà aggiungere i titolari del GAS Due</li>
				</ul>
		</div>
		</p>		
		<p>
			Il <b>Referente ordini condivisi</b> gestisce l'ordine condiviso del proprio GAS dei produttori ai quali è associto.<br />
			Quando creerà l'ordine per il proprio G.A.S. si troverà già gli <b>articoli associati</b> dal <b>titolare</b> dell'ordine.  
		</p>
		<p>
			Il <b>Super-Referente ordini condivisi</b> gestisce l'ordine condiviso del proprio GAS di tutti i produttori
		</p>
					
		<p>
		<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/des-ruoli.jpg" /></a>
		</p>

		<p>Un esempio
			<ul>
			<li>Il "Manager del D.E.S." che appartiene al <b>GAS Uno</b> associa al produttore "Fattoria Felice" un suo gasista con il ruolo di <b>Titolare</b></li>
			<li>Il "Manager del D.E.S." che appartiene al <b>GAS Due</b> associa al produttore "Fattoria Felice" un suo gasista con il ruolo di <b>Referente</b></li>
			<li>Il "Manager del D.E.S." che appartiene al <b>GAS Due</b> associa al produttore "Fattoria Felice" un suo gasista con il ruolo di <b>Super-Referente</b></li>
			<li>Il "Titolare degli ordini condivisi" del <b>GAS Uno</b> è titolare del produttore "Fattoria Felice" e <b>crea un ordine condiso</b> per quel produttore</li>
			<li>Il "Referente degli ordini condivisi" del <b>GAS Uno</b>, referente del produttore "Fattoria Felice" <b>crea un ordine del proprio GAS</b> associato all'ordine condiviso appena creato</li>
			<li>Il "Referente degli ordini condivisi" del <b>GAS Due</b>, referente del produttore "Fattoria Felice" <b>crea un ordine del proprio GAS</b> associato all'ordine condiviso appena creato</li>
			<li>Il "Super-Referente degli ordini condivisi" del <b>GAS Due</b>, monitorizza l'ordine del proprio GAS associato all'ordine condiviso appena creato</li>
			</ul>
		</p>
		
		<p>
		 Non esiste un ruolo che centralizzi alcune funzionalità del DES, ma <b>ogni Manager del D.E.S.</b> è responsabile e può gestire solamente gli utenti che appartengono al proprio GAS.
		 </p>
		 
		<h1 id="produttori">I produttori</h1>

		<p>Con il modulo "produttori" si potranno associare al D.E.S. i produttori inseriti nella banca dati condivisa di PortAlGas</p>

		<p>
		<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/des-produttori.jpg" /></a>
		</p>
		
		<p>Questo modulo può essere gestito solo dagli utenti con ruolo <b>Manager del D.E.S.</b></p>
		<p>
		<div role="alert" class="alert alert-info">
			<strong>Nota: </strong> tutti i G.A.S. associati al D.E.S. devono avere i produttori associati al proprio G.A.S.
		</div>
		</p>
		
		<p>Il G.A.S. titolare di un produttore D.E.S. <b>deve</b> aver associato ad esso anche gli articoli: questi saranno condivisi con gli altri G.A.S.</p>
		<p>Il G.A.S. <b>non</b> titolare di un produttore D.E.S. possono anche non aver associato ad esso gli articoli perchè utilizzeranno quell del G.A.S. titolare di un produttore D.E.S.</p>
			
		<h1 id="aprire-un-ordine-condiviso">Aprire un ordine condiviso</h1>

			<p>A questo punto si può aprire un ordine condiviso cliccando su "Crea un nuovo ordine condiviso"</p>
			<p>Questo modulo può essere gestito solo dagli utenti con ruolo <b>Titolare ordini condivisi</b></p>

			<p>
			<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/des-ordine-add.jpg" /></a>
			</p>
			
			<p>Si aprirà il modulo per inserire l'anagrafica di un ordine condiviso, di seguito i campi da valorizzare</p>
			<ul>
			<li>Produttore condiviso</li>
			<li>Luogo: indicare il luogo della consegna, informazione utile ai referente del D.E.S. ma non sarà visualizzata dai gasisti dei diversi GAS; per loro rimarrà valida la data di consegna dell'ordine del proprio GAS</li>
			<li>Per tutti i GAS si chiuderà: gli ordini dei GAS associati non potrà avere una data di chiusura ordine maggiore della data qui inserita</li>
			<li>Nota: l'eventuale nota sarà comunicata ai G.A.S. quando visualizzeranno il proprio ordine</li>
			<li>Ha le spese di <b>trasporto</b>: l'eventuale spesa di trasporto sarà comunicata ai G.A.S. che potranno già configurare il proprio ordine con le spese di trasporto</li>
			<li>Gestisco un <b>costo aggiuntivo</b>: l'eventuale spesa aggiuntive sarà comunicata ai G.A.S. che potranno già configurare il proprio ordine con le spese aggiuntive</li>
			<li>Gestisco uno <b>sconto</b>: l'eventuale sconto sarà comunicato ai G.A.S. che potranno già configurare il proprio ordine con lo sconto</li>
			</ul>
			
			<p>
				<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/des-order-msg-spese-eventuali.jpg" /></a>
			</p>
						
			<p>Ora, entrando nel modulo dell'ordine condiviso, il titolare potrà creare l'ordine del proprio GAS e associargli gli articoli.</p>
			<p>Il <b>primo ordine</b> dovrà essere creato dal titolare del produttore perché solo lui gestirà gli articoli associarti all'ordine.</p>
			<p>Gli altri GAS dovranno solo creare l'ordine e si troveranno già gli articoli associati senza alcuna possibilità di modificarli.</p>
			
		<h1 id="articoli-nell-ordine-condiviso">Articoli in ordine condiviso</h1>

			
		<p>Un ordine condiviso avrà associato i soli articoli del G.A.S. titolare di un produttore D.E.S.</p>
		<p>Solo il titolare di un produttore D.E.S. potrà gestirne l'anagrafica modificando, per esempio, i prezzi, le descrizioni etc.</p>
		<p>Tutte le modifche che effettuerà si ripercuoteranno anche sui G.A.S. associati; quindi se dovesse <b>cancellare l'associazione</b> di un articolo associato ad un ordine:
		<ul>
			<li>lo cancellare anche dagli altri G.A.S.</li>
			<li>cancellare tutti gli eventuali <b>acquisti</b> effettuati da tutti i G.A.S.</li>
		</ul>
		</p>
		
				
				
		<h1 id="monitoraggio-di-un-ordine-condiviso">Monitoraggio di un ordine Condiviso</h1>

		<p>Il Titolare Ordine Condiviso può monitorare l&#39;andamento dell&#39;ordine Des cliccando su AZIONI del Proprio GAS</p>
		<p>
			<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/des-ordini.png" /></a>
		</p>
		<p>Comparirà (SOLO A LUI in Verde ) un elenco più dettagliato con le azioni possibili per l&#39;ordine Condiviso </p>
		<p>
			<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/des-menu.png" /></a>
		</p>
		<p>Cliccando su Stampa Ordine Condiviso</p>

		<p>Sarà possibile selezionare la modalità di stampa desiderata</p>
		<p>
			<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/des-esporta-doc.png" /></a>
		</p>
		<p>Ovviamente occorre attendere la chiusura di tutti gli ordini per avere la stampa con i dati finali.</p>
		
		<h1 id="gli-stati-di-un-ordine-condiviso">Gli stati di un ordine condiviso</h1>
		
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
						<td class="orderStatoOPEN" style="width: 32px;"></td>
						<td>Ordine aperto</td>
						<td>
							Ordine aperto, gli utenti possono effettuare acquisti
						</td>
					</tr>
					<tr>
						<td class="orderStatoBEFORE-TRASMISSION" style="width: 32px;"></td>
						<td>Da trasmettere al produttore</td>
						<td>
							Ordine da trasmettere al produttori: tutti i GAS possono effettuare modifiche
						</td>
					</tr>
					<tr>
						<td class="orderStatoPOST-TRASMISSION" style="width: 32px;"></td>
						<td>Trasmesso al produttore</td>
						<td>
							Ordine trasmesso al produttori: i GAS non possono più effettuare modifiche. Solo il Titolare può effetuare modifiche
						</td>
					</tr>
					<tr>
						<td class="orderStatoREFERENT-WORKING" style="width: 32px;"></td>
						<td>In carico ai referenti dei GAS</td>
						<td>
							L'ordine può nuovamente essere modificato dai referenti dei singoli GAS: potranno vedere e far quadrare le eventuali modifiche comunicate dal Titolare
						</td>
					</tr>
					<tr>
						<td class="orderStatoCLOSE" style="width: 32px;"></td>
						<td>Ordine chiuso</td>
						<td>
							l'ordine non può più essere modificato
						</td>
					</tr>
				</tbody>
			</table>
		</div>
			
			<p>Una volta che è scaduta la data di chiusura dell'ordine condiviso, l'ordine passerà allo stato "Da trasmettere al produttore": 
			<ul>
			<li>i <b>gassisti</b> non potranno più effettuare acquisti</li>
			<li>i <b>referenti</b> potranno effettuare eventuali modifiche sugli acquisiti</li>
			<li>il <b>titolare</b> potrà modificare gli articoli associati all'ordine e passare l'ordine allo stato "Trasmesso al produttore"</li>
			</ul>
			</p>
			<p>Quando un ordine si trova nello stato "Trasmesso al produttore"</p>
			<ul>
			<li>i <b>gassisti</b> non potranno più effettuare acquisti</li>
			<li>i <b>referenti</b> non potranno effettuare modifiche sugli acquisiti</li>
			<li>il <b>titolare</b> potrà modificare gestire i dati aggregati degli acquisti dei singoli GAS così da trasmetterli al produttore</li>
			</ul>
		
		<h1 id="ciclo-di-un-ordine-condiviso">Ciclo di un ordine condiviso</h1>
		
			<p>Riportiamo qui un esempio per spiegare meglio il ciclo di un ordine condiviso</p>
			<p>Ipotiziamo il seguente D.E.S. denominato "D.E.S. Italia", composto da 3 GAS:
			<ul>
				<li>Gas Roma</li>
				<li>Gas Venezia</li>
				<li>Gas Bologna</li>
			</ul>   

			<h3 id="ordine-da-aprire">Ordine da aprire</h3>
			<ul>
				<li>Gas Roma: Il "titolare" del produttore "La Cascina Felice" apre un ordine D.E.S. e imposta una <b>data di chiusura</b> affinchè tutti gli ordini dei GAS associati non abbiano una data di chiusura posteriore.</li>
				<li>Gas Venezia e Gas Bologna: <b>non possono</b> ancora aprire un ordine condiviso con il produttore "La Cascina Felice".</li>
				<li>Gas Roma: essendo il "titolare" del produttore "La Cascina Felice" dal modulo degli ordini D.E.S. apre un ordine con il produttore "La Cascina Felice"; scegli la <b>data di consegna</b> per il prprio GAS, imposta i suoi parametri e scegli quali <b>articoli associare</b> dall'elenco degli articoli del produttore.</li>
				<li>Gas Venezia e Gas Bologna: possono aprire dal modulo degli ordini D.E.S. apre un ordine con il produttore "La Cascina Felice"; scegli la <b>data di consegna</b> per il prprio GAS, imposta i suoi parametri ma <b>non potranno</b> quali <b>articoli associare</b>: visualizzeranno quelli del listino del "titolare" del produttore.</li>
			</ul>
			
			<h3 id="ordine-aperto">Ordine in "Ordine aperto"</h3>

					<div class="table-responsive">
		   <table class="table table-bordered">
				<tbody>
					<tr>
						<td class="orderStatoOPEN" style="width: 32px;"></td>
						<td>Ordine aperto</td>
						<td>
							Ordine aperto, gli utenti possono effettuare acquisti
						</td>
					</tr>
				</tbody>
			</table>
		</div>
			
			<p>Tutti i 3 ordini sono aperti e i gasisti possono <b>effettuare i loro acquisti</b>, ognuno nella pagina del proprio G.A.S.</p>
			<p>Pian piano ogni ordine si chiudono, tutti prima della data di chiusura fissata dal "titolare" del produttore: ora tutti gli ordini sono nello stato "in carico al referente prima della consegna".</p>

			<h3 id="ordine-da-trasmettere-al-produttore">Ordine in "Da trasmettere al produttore"</h3>
			
			
		<div class="table-responsive">
		   <table class="table table-bordered">
				<tbody>
					<tr>
						<td class="orderStatoBEFORE-TRASMISSION" style="width: 32px;"></td>
						<td>Da trasmettere al produttore</td>
						<td>
							Ordine da trasmettere al produttori: tutti i GAS possono effettuare modifiche
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		
			<p>Quando la <b>data di chiusura dell'ordine condiviso</b> impostata dal "titolare" del produttore "La Cascina Felice" sar&agrave; uguale al giorno corrente, PortAlGas porter&agrave; l'ordine condisivo dallo stato "Ordine aperto" allo stato "Da trasmettere al produttore"</p>
			<p>I <b>referenti</b> dei diversi GAS potranno effettuare le eventuali <b>modifiche</b> sui propri ordini.</p>

			<h3 id="ordine-trasmesso-al-produttore">Ordine in "Trasmesso al produttore"</h3>
			
		<div class="table-responsive">
		   <table class="table table-bordered">
				<tbody>
					<tr>
						<td class="orderStatoPOST-TRASMISSION" style="width: 32px;"></td>
						<td>Trasmesso al produttore</td>
						<td>
							Ordine trasmesso al produttori <b>da parte del titolare</b>: i GAS non possono più effettuare modifiche. Solo il Titolare può effetuare modifiche
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		
			<p>Quando il "titolare" del produttore "La Cascina Felice" lo riterr&agrave; opportuno, potr&agrave; portare l'ordine condiviso allo stato "Trasmesso al produttore". In questa fase i <b>referenti</b> dei diversi GAS <b>non possono più effettuare modifiche</b> sui propri ordini.</p>
			<p>Solo il "titolare" del produttore potr&agrave; effetuare modifiche sull'ordine condiviso.</p>
			<p>In questa fase la merce del produttore potrebbe gi&agrave; essere arrivate e potrebbo esserci <b>modifiche sull'importo finale</b> dell'ordine dovuto, per esempio:</p>
			<ul>
				<li>alcuni <b>articoli non sono arrivati</b>: l'importo finale sar&agrave; inferiore e dovr&agrave; variare anche quello dei diversi GAS</li>
				<li>&egrave; confermato l'<b>importo del trasporto</b> che sar&agrave; da suddividere tra i diversi GAS</li>
				<li>alcuni articoli hanno avuto una <b>variazione di prezzo</b>: l'importo finale varier&agrave; e dovr&agrave; variare anche quello dei diversi GAS</li>
			</ul>
			
			<h3 id="ordine-trasporto">Importo del trasporto da applicare all'importo finale</h3>
			
			<p>Immaginiamo il caso in cui &egrave; confermato l'importo del trasporto</p>
			<p>Il "titolare" del produttore potr&agrave; accedere al modulo "<b>Gestisci gli acquisti aggregati per GAS</b>"</p>
			<p>Qui visualizzer&agrave; gli <b>importi totali aggregati</b> per GAS, per esempio</p>											

			<p>Nella tabella sottostante, la colonna "Importo originale" mostra la somma di tutti gli acquisti per ogni GAS</p>

		<div class="table-responsive">
		   <table class="table table-bordered">
				<thead>		
				<tr>
					<th>GAS</th>
					<th>Importo originale</th>
					<th>% rispetto al totale</th>
					<th>Importo modificato</th>
					<th>Differenza</th>
				</tr>
				</thead>
				<tbody>					
				<tr>
					<td>Gas Roma</td>
					<td>77,88 €</td>
					<td>40 %</td>
					<td>77,88 €</td>
					<td>0</td>
				</tr>
				<tr>
					<td>Gas Venezia</td>
					<td>33,48 €</td>
					<td>17 %</td>
					<td>33,48 €</td>
					<td>0</td>
				</tr>
				<tr>
					<td>Gas Bologna</td>
					<td>83,70 €</td>
					<td>43 %</td>
					<td>83,70 €</td>
					<td>0</td>
				</tr>
				</tbody>					
			</table>
		</div>


			<p>Supponiamo che l'importo del trasporto comunicato dal produttore al "titolare" del produttore sia di 50 € e di volerlo <b>suddividere in base alle percentuale</b> sull'importo totale che &egrave; di 195,06 €.</p>
			<p>Verr&agrave; così suddiviso</p> 
		<div class="table-responsive">
		   <table class="table table-bordered">
				<thead>	
				<tr>
					<th>GAS</th>
					<th>Importo originale</th>
					<th>% rispetto al totale</th>
					<th>Importo trasporto</th>
				</tr>
				</thead>
				<tbody>					
				<tr>
					<td>Gas Roma</td>
					<td>77,88 €</td>
					<td>40 %</td>
					<td>20 €</td>

				</tr>
				<tr>
					<td>Gas Venezia</td>
					<td>33,48 €</td>
					<td>17 %</td>
					<td>8,50 €</td>
				</tr>
				<tr>
					<td>Gas Bologna</td>
					<td>83,70 €</td>
					<td>43 %</td>
					<td>21,50 €</td>
				</tr>
				</tbody>					
			</table>
		</div>

			<p>Il "titolare" del produttore potr&agrave; modificare la colonna "<b>Importo modificato</b>" per suddividere per ogni GAS l'importo del trasporto e aggiungere eventualmente una <b>nota</b></p>

			<p>
		<div class="table-responsive">
		   <table class="table table-bordered">
				<thead>	
				<tr>
					<th>GAS</th>
					<th>Importo originale</th>
					<th>% rispetto al totale</th>
					<th>Importo modificato</th>
					<th>Differenza</th>
					<th>Nota</th>
				</tr>
				</thead>
				<tbody>					
				<tr>
					<td>Gas Roma</td>
					<td>77,88 €</td>
					<td>40 %</td>
					<td>97,88 €</td>
					<td>20,00 €</td>
					<td>Spese di trasporto</td>
				</tr>
				<tr>
					<td>Gas Venezia</td>
					<td>33,48 €</td>
					<td>17 %</td>
					<td>41,98 €</td>
					<td>8,50 €</td>
					<td>Spese di trasporto</td>
				</tr>
				<tr>
					<td>Gas Bologna</td>
					<td>83,70 €</td>
					<td>43 %</td>
					<td>105,20 €</td>
					<td>21,50 €</td>
					<td>Spese di trasporto</td>
				</tr>
				</tbody>					
			</table>
		</div>
			</p>

			<p>
			<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/des_gestisci_dati_aggregati.jpg" /></a>
			</p>

			
			 
			<h3 id="ordine-in-carico-ai-referenti">Ordine in "In carico ai referenti dei GAS"</h3>
			
		<div class="table-responsive">
		   <table class="table table-bordered">
				<tbody>
					<tr>
						<td class="orderStatoREFERENT-WORKING" style="width: 32px;"></td>
						<td>In carico ai referenti dei GAS</td>
						<td>
							L'ordine può nuovamente essere modificato dai referenti dei singoli GAS: potranno vedere e far quadrare le eventuali modifiche comunicate dal Titolare
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		
			<p>Quando il "titolare" del produttore "La Cascina Felice" lo riterr&agrave; opportuno, potr&agrave; portare l'ordine condiviso allo stato "In carico ai referenti dei GAS".</p>
			<p>In questa fase i referenti dei diversi GAS potranno nuovamente modificare i propri ordini.</p>
			<p>Soprattutto potranno vedere e <b>far quadrare</b> le eventuali modifiche comunicate dal "titolare" del produttore.</p>

			<p>Ecco come si presenta il proprio ordine ad un G.A.S.</p>
			<p>
			<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/des-order-msg-gas-non-titolare.jpg" /></a>
			</p>
			
			<p>Ogni referente dovr&agrave; gestire il <b>trasporto</b> per il proprio GAS, suddividendolo come ritiene più opportuno, affinch&egrave; il proprio importo totale dell'ordine corrisponda a quello impostato dal "titolare" del produttore, per esempio</p>
			<ul>
				<li>il Gas Roma, che doveva 77,88 €, dovr&agrave; 97,88 € al D.E.S.: i 20,00 € richiesti in più dovr&agrave; gestirli come "importo del trasporto"</li>
				<li>il Gas Venezia, che doveva 33,48 €, dovr&agrave; 41,98 € al D.E.S.: i 8,50 € richiesti in più dovr&agrave; gestirli come "importo del trasporto"</li>
				<li>il Gas Bologna, che doveva 83,70 €, dovr&agrave; 105,20 € al D.E.S.: i 21,50 € richiesti in più dovr&agrave; gestirli come "importo del trasporto"</li>
			</ul>
			
			<h3 id="ordine-chiuso">Ordine in "Ordine Chiuso"</h3>
			
		<div class="table-responsive">
		   <table class="table table-bordered">
				<tbody>
					<tr>
						<td class="orderStatoCLOSE" style="width: 32px;"></td>
						<td>Ordine chiuso</td>
						<td>
							l'ordine non può più essere modificato
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		
			<p>Quando tutti gli ordini dei singoli GAS risulteranno chiusi, PortAlGas chiuder&agrave; automaticamente anche l'ordine condiviso.</p>

		
		
		
		<h1 id="dati-aggregati-degli-acquisti-dei-singoli-GAS">Dati aggregati degli acquisti dei singoli GAS</h1>

			<p>Gestendo i dati aggregati degli acquisti dei singoli GAS, si visualizzerà il totale che ogni singolo GAS dovrà pagare al D.E.S.</p>
			<p>Tale importo potrà essere modificato dal titolare e ogni referente di ogni GAS vedrà la differenza che dovrà far quadrare all'interno del proprio ordine.</p>

			<p>Ecco come si presenta ad un G.A.S. che <b>non</b> &egrave; titolare, il proprio ordine</p>
			<p>
			<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/des-order-msg-gas-non-titolare.jpg" /></a>
			</p>
							
		</div> <!-- col-sm-8 -->


				
		<div class="col-sm-3" role="complementary">

				<ul id="myScrollspy" class="nav nav-contenitore nav-stacked affix-top hidden-print hidden-xs hidden-sm" data-spy="affix" data-offset-top="125">
					<li class="active"><a href="#requisiti-per-aprire-un-ordine-condiviso">Requisiti per aprire un ordine condiviso</a></li>					
					<li><a href="#gestione-ruoli-des">Gestione dei ruoli</a></li>
					<li><a href="#produttori">I produttori</a></li>
					<li><a href="#aprire-un-ordine-condiviso">Aprire un ordine condiviso</a></li>
					<li><a href="#articoli-nell-ordine-condiviso">Articoli in ordine condiviso</a></li>
					<li><a href="#monitoraggio-di-un-ordine-condiviso">Monitoraggio di un ordine Condiviso</a></li>
					<li><a href="#gli-stati-di-un-ordine-condiviso">Gli stati di un ordine condiviso</a></li>

					<li><a href="#ciclo-di-un-ordine-condiviso">Ciclo di un ordine condiviso</a>
						<ul class="nav">
							<li><a href="#ordine-da-aprire">Ordine da aprire</a></li>
							<li><a href="#ordine-aperto">Ordine in "Ordine aperto"</a></li>
							<li><a href="#ordine-da-trasmettere-al-produttore">Ordine in "Da trasmettere al produttore"</a></li>
							<li><a href="#ordine-trasmesso-al-produttore">Ordine in "Trasmesso al produttore"</a></li>
							<li><a href="#ordine-trasporto">Importo del trasporto da applicare all'importo finale</a></li>
							<li><a href="#ordine-in-carico-ai-referenti">Ordine in "In carico ai referenti dei GAS"</a></li>
							<li><a href="#ordine-chiuso">Ordine in "Ordine Chiuso"</a></li>
						</ul>
					</li>					
					<li><a href="#dati-aggregati-degli-acquisti-dei-singoli-GAS">Dati aggregati degli acquisti dei singoli GAS</a></li>
				</ul>
		
		
		</div> <!-- col-sm-3 -->
		
	
	</div>	  <!-- container -->
  

<?php require('_inc_footer.php');?>