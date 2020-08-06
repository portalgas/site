<?php require('_inc_header.php');?>
 
    <div class="container">
      

	  

        <div class="col-sm-8 cakeContainer" role="main">

		
			<h1 id="introduzione" class="page-header">Introduzione</h1>

				<p>Il produttore dev'essere associato ad uno o più GAS che aderiscono al progetto PortAlGas</p>
				<p>Il produttore ha un proprio account con il quale potrà accedere al back-office <a href="https://www.portalgas.it/my" target="_blank">https://www.portalgas.it/my</a></p>
				<p>Una volta autenticato, si troverà le seguenti voci di menù.</p>
					<ul>
						<li>Gestione articoli</li>
						<li>Elenco GAS associati</li>
						<li>Gestione promozioni</li>
					</ul>
				</p>
				
				<h1 id="gestione-articoli" class="page-header">Gestione articoli</h1>
				
				<p>Il produttore dovrà crearsi un proprio listino degli articoli, saranno questi che potrà:</p>
					<ul>
						<li>condividere con i GAS per poi tenerli sincronizzati</li>
						<li>sceglierli per creare le promozioni</li>
					</ul>
				</p>
    
				<h1 id="elenco-gas-associati" class="page-header">Elenco GAS associati</h1>

				<p>Elenco dei GAS al quale il produttore è associato. Per ogni GAS viene visualizzata l'abilitazione che il GAS concede al produttore, abilitazione che gli permetterà</p>
				<p>
					<ul>
						<li>visualizzare gli ordini a lui associato <b>senza</b> visualizzare i nomi dei gasisti</li>
						<li>visualizzare gli ordini a lui associato visualizzando anche i nomi dei gasisti</li>
						<li>sincronizzazione dei listino: far gestire il proprio listino al produttore</li>
					</ul>
				</p>
				
				<p>
					<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/elenco-gas.jpg" /></a>
				</p>
				
				<div class="alert alert-info" role="alert">
					Maggiori informazioni sul referente e la gestione dei premessi del produttore, clicca su <a href="http://manuali.portalgas.it/gestione_dei_produttori.php#il-tab-permessi-del-produttore">il tab permessi del produttore</a></p>
				</div>
				
				<h2 id="sincronizzazione-articoli" class="page-header">Sincronizzazione articoli</h2>
   
   				<p>Un pò di teoria... un GAS che demanda la gestione degli articoli al produttore ha</p>
   				<p>
   				<ul>
   					<li>una copia del listino degli articoli sul proprio GAS</li>
					<li>per ogni ordine che si apre si crea un copia dell'articolo (copia che si porta dietro per esempio il prezzo così e varia il prezzo dell'articolo potrebbe nn variare per quel determinato ordine)</li>
				</ul>
				</p>
				<p>Quindi, quando un produttore varia l'anagrafica di un proprio articolo può scegliere se sincronizzare (copiare i dati)</p>
   				<p>
   				<ul>
   					<li>con l'articolo di un GAS</li>
					<li>con un articolo di un GAS legato ad un ordine.</li>
				</ul>
				</p>
				<p>Bisogna fare attenzione a sincronizzare i dati di un articolo di un GAS legato ad un ordine: gli ordini che si possono scegliere possono essere</p>
   				<p><ul>
   					<li>aperti: i gasisti stanno effetuando acquisti</li>
    				<li>in carico al referente prima della consegna: i gasisti non possono più effetuando acquisti ma il referente può ancora apportare modifiche</li>
    				<li>in carico al referente dopo della consegna: i gasisti non possono più effetuando acquisti ma il referente può ancora apportare modifiche</li>
				</ul>
				</p>
				<p>Queste operazioni possono essere effettuare nel backoffice in diversi contesti che però fanno le medesime cose:</p>
   				<p><ul>
   					<li>contestualmente quando si modifica un articolo al tab "sincronizzazione" può decidere se sincronizzare i dati:
   						<ul>
		   					<li>con l'articolo di un GAS</li>
		   					<li>con un articolo di un GAS legato ad un ordine.</li>
		   				</ul>
		   			</li>	
   					<li>dall'apposito menù Articoli -> Sincronizza i tuoi articoli con quelli dei GAS</li>
   					<li>dall'apposito menù Articoli -> Sincronizza i tuoi articoli con quelli ordinati dei GAS</li>
   				</ul>
   				</p>
				<p>Una volta scelto il GAS al quale si desidera sincronizzare il proprio listino con quello del GAS, si presenterà l'elenco degli articoli del produttore e del GAS.</p>
				<p>Per ogni articolo si possono avere diverse casistiche e quindi differenti azioni</p>
				<p>Di seguito le diverse casistiche:</p>
				

					<div class="table-responsive">
					   <table class="table table-bordered">
							<thead>	
								 <tr>
								  <th>Articolo produttore</th>
								  <th>Articolo GAS</th>
								  <th>Nota</th>
								  <th>Azioni</th>
								 </tr>
							</thead>
							<tbody>  
								 <tr>
									<td>Presente</td>
									<td>Non presente</td>
									<td>L'articolo del produttore non è presente tra gli articoli del GAS</td>
									<td>
										<div style="padding-left:45px;width: 80%;" class="action actionAdd">Inserisci</div>
									</td>
								 </tr>
								 <tr>
									<td>Presente</td>
									<td>Presente</td>
									<td>L'articolo del produttore è presente tra gli articoli del GAS</td>
									<td>
										<div style="padding-left:45px;width: 80%;" class="action actionSyncronize">Aggiorna i dati dell'articolo</div>
									</td>
								 </tr>
								 <tr>
									<td>Presente</td>
									<td>Presente</td>
									<td>L'articolo del produttore è presente tra gli articoli del GAS</td>
									<td>
										<div style="padding-left:45px;width: 80%;" class="action actionOnOff">Rendi l'articolo non più presente tra quelli che il G.A.S. potrà ordinare</div>
									</td>
								 </tr>
								 <tr>
									<td>Non presente</td>
									<td>Presente,<br />acquistato</td>
									<td>L'articolo del GAS non è presente tra gli articoli del produttore, ma risulta <b>acquistato</b> da alcuni gasisti</td>
									<td>
										<div style="padding-left:45px;width: 80%;" class="action actionOnOff">Rendi l'articolo non più presente tra quelli che il G.A.S. potrà ordinare</div>
									</td>
								 </tr>
								 <tr>
									<td>Non presente</td>
									<td>Presente,<br />acquistato</td>
									<td>L'articolo del GAS non è presente tra gli articoli del produttore, e <b>non</b> risulta <b>acquistato</b> da alcuni gasisti</td>
									<td>
										<div style="padding-left:45px;width: 80%;" class="action actionDelete">Elimina l'articolo definitivamente</div>
									</td>
								 </tr>		
						   </tbody>
						</table>
					</div>  

				<h1 id="promozioni" class="page-header">Promozioni</h1>
  
				<p>In fase di sviluppo</p>

				
		</div> <!-- col-sm-8 -->
		
		<div class="col-sm-3" role="complementary">

				<ul id="myScrollspy" class="nav nav-contenitore nav-stacked affix-top hidden-print hidden-xs hidden-sm" data-spy="affix" data-offset-top="125">
					<li class="active"><a href="#introduzione">Introduzione</a></li>
					<li><a href="#gestione-articoli">Gestione articoli</a>
					<li><a href="#elenco-gas-associati">Elenco GAS associati</a>
						<ul class="nav">
							<li><a href="#sincronizzazione-articoli">Sincronizzazione articoli</a></li>					
						</ul>					
					</li>
					<li><a href="#promozioni">Promozioni</a></li>
				</ul>
		
		
		</div> <!-- col-sm-3 -->
	
	
	</div>	  <!-- container -->
  

<?php require('_inc_footer.php');?>