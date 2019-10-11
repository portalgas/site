<?php require('_inc_header.php');?>
 
    <div class="container">
      

	  

        <div class="col-sm-8 cakeContainer" role="main">


<h1 id="gli-articoli-su-portalgas" class="page-header">Gli articoli su PortAlGas</h1>

<p>Ogni referente potrà gestire solamente gli articoli dei produttori per i quali è abilitato.</p>
<p>Quando il referente crea un ordine, decide</p>
<ul>
	<li>quali articoli associare all'ordine</li>
	<li>imposta alcuni valori degli articoli scelti per quel determinato ordine</li>
</ul>
<p>PortAlGas permette di gestire gli articoli dei produttori attraverso il menù "Articoli", che è così composto:</p>
<ul>
	<li>Articoli, per gestire la loro anagrafica</li>
	<li>Modifica rapida degli articoli</li>
	<li>Stampa articoli</li>
	<li>Gestisci categorie</li>
	<li>Modifica prezzi</li>
	<li>Modifica prezzi in %</li>
	<li>Modifica prezzo degli articolo associati agli ordini</li>
	<li>Importa articoli</li>
</ul>
<h1 id="articoli-per-gestire-la-loro-anagrafica" class="page-header">Articoli, per gestire la loro anagrafica</h1>

<h2 id="ricerca-degli-articoli" class="page-header">Ricerca degli articoli</h2>

<p>Clicchiamo su Articoli, si presenterà l’elenco degli articoli dei produttori di cui sono referente.</p>

<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/articoli-filtri-ricerca.png" /></a>

<p>Vengono presentati i seguenti filtri di ricerca:</p>

<ul>
	<li>Articoli associati ad un ordine</li>
	<li>Articoli associati ad un produttore</li>
	<li>Articoli associati ad una categoria</li>
	<li>Articoli con un determinato nome</li>
	<li>Articoli che appartiene ad una delle seguenti categorie:
		<ul>
			<li>Biologico</li>
			<li>Biodinamico</li>
			<li>Vegetariano</li>
			<li>Vegano</li>
			<li>Celiaco</li>
		</ul>
	</li>
	<li>Articoli associati all’unità di misura
		<ul>
			<li>KG</li>
			<li>HG</li>
			<li>GR</li>
			<li>PZ</li>
			<li>LT</li>
			<li>ML</li>
			<li>MM</li>
		</ul>	
	</li>
	<li>Articoli visibili o non visibili</li>
</ul>
						
						
<h2 id="anagrafica-degli-articoli" class="page-header">Anagrafica degli articoli</h2>

<p>Di seguito i campi da compilare per creare l'anagrafica di un articolo , organizzati i 4 differenti tabs:</p>

		<ol>
			<li>Dati articolo</li>
			<li>Prezzo</li>
			<li>Condizioni d'acquisto</li>
			<li>Immagine</li>
		</ol>


<h3 id="il-tab-dei-dati-dell-articolo">Il Tab dei dati dell’articolo</h3>

<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/articoli-form.png" /></a>

<h3 id="il-tab-del-prezzo">Il Tab del prezzo</h3>

<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/articoli-tab-prezzo.png" /></a>

<p>
<strong>Nota</strong>: Nel caso in cui le confezioni siano minori di 1 Kg (ad Es. 500 Grammi o 5 Hg meglio usare 0.5 Kg)  è meglio inserirle come frazioni dello stesso 
ovvero 0,50 Kg Unità di Misura Kg con il prezzo relativo agli stessi, in questo caso il prezzo al Kg verrà calcolato automaticamente, vedasi l'esempio in figura 
</p>

<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/articoli-tab-prezzo-um.png" /></a>

<h3 id="il-tab-delle-condizioni-d-acquisto">Il Tab delle condizioni d’acquisto</h3>

<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/articoli-tab-condizioni-aquisto.png" /></a>

<p>I valori del tab "Condizioni d’acquisto" saranno i valori di default presentati quando si assocerà l’articolo ad un ordine.
Questi potranno essere modificati per quel determinato ordine senza modificarli nella sua anagrafica</p>

<p>Questi sono:</p>

<p>
<ul>
	<li>Quantità minima che un gasista può acquistare: di default 1.</li>
	<li>Quantità massima che un gasista può acquistare: di default 0 per indicare che non ci sono limiti.</li>
	<li>Pezzi di una confezione (i colli): indica di quanti elementi è composto un eventuale collo. Di default 1: se è valorizzato a 1 l'articolo non fa parte di un collo.
		<p>I gasisti acquistano gli articoli e non i singoli colli.</p>
		<p>Questi servono solo al referente per sapere se è stata raggiunta la quantità necessaria per completare i colli.</p>
		<p>All'apertura dell'ordine, PortAlGas presenterà un report per monitorare gli eventuali acquisti se alcuni articoli presentano i valori:</p>
		<ul>
			<li>Pezzi confezione maggiore di 1</li>
			<li>Quantità minima rispetto a tutti gli acquisti maggiore di 0</li>
			<li>Quantità massima rispetto a tutti gli acquisti maggiore di 0</li>
		</ul>		
	</li>
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

<p>Di seguito la videata che si presenta quando si crea un ordine. Nell'associazione degli articoli ad un ordine vengono presentati i campi del tab "Condizioni d’acquisto" così da poterli eventualmente modificare per quel determinato ordine</p>

<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/articles-order-add-articles-fields.png" /></a>

<h3 id="il-tab-dell-immagine">Il Tab dell’immagine</h3>

<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/articoli-tab-immagine.png" /></a>

<p>Nel "Tab Immagine" si può uplodare un immagine con estensione .jpg, .jpeg, .gif, .png da associare all'articolo</p>

<h1 id="modifica-rapida-degli-articoli" class="page-header">Modifica rapida degli articoli</h1>

<div role="alert" class="alert alert-info">
	<strong>Nota: </strong> Questo modulo è attivo solo per i referenti che hanno impostato nel loro profilo "Gestisci gli articoli associati all'ordine" a <strong>si</strong>
</div>

<p>Si può accedere al modulo</p>
<p>
<ul>
	<li>dal menù superiore</li>
	<li>dall'elenco degli articoli, solo <b>dopo aver filtrato</b> per un produttore</li>
</ul>
</p>

<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/articoli-modifica-rapida-link.png" /></a>


<p>Cliccando su questa voce di menù si accede al modulo per modificare rapidamente alcuni valori degli articoli.</p>

<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/articoli-modifica-rapida.png" /></a>

<p>Filtrando per il produttore si presenterà la lista di tutti gli articoli a lui associati e si potranno modificare i seguenti campi:</p>

<ul>
	<li>Codice (se il G.A.S. è abilitato a gestirlo)</li>
	<li>Nome del prodotto</li>
	<li>Confezione</li>
	<li>Prezzo</li>
</ul>

<p>E' anche possibile selezionare più articoli ed eliminarli</p>

<h1 id="stampa-articoli" class="page-header">Stampa articoli</h1>

<p>Cliccando su questa voce di menù si accede al modulo per stampare gli articoli nei formati:</p>

<ul>
	<li>pdf</li>
	<li>csv</li>
	<li>excel</li>
</ul>

<h1 id="gestisci-categorie" class="page-header">Gestisci categorie</h1>

<p>Cliccando su questa voce di menù si accede al modulo per gestire in modo massivo le categorie degli articoli.</p>

<p>Filtrando per il produttore si presenterà la lista di tutti gli articoli a lui associati. Selezionando una delle categorie indicate la si potrà associare a tutti gli articoli scelti.</p>

<h1 id="modifica-prezzi" class="page-header">Modifica prezzi</h1>

<p>Cliccando su questa voce di menù si accede al modulo per gestire in modo rapido i prezzi degli articoli.</p>

<p>Filtrando per il produttore si presenterà la lista di tutti gli articoli a lui associati con la possibilità di aggiornare ogni singolo prezzo.</p>

<p>Una volta impostati i nuovi prezzi si potrà salvare in 2 distinte modalità:</p>

<div class="table-responsive">
   <table class="table table-bordered">
        <thead>	
 <tr>
  <th>Azione</th>
  <th>Aggiornamento<br />prezzo anagrafica articolo</th>
  <th>Aggiornamento<br />prezzo articolo associati agli ordini</th>
 </tr>
        </thead>
        <tbody>  
 <tr>
 	<td>Aggiorna i prezzi degli articoli</td>
 	<td>Si</td>
 	<td>No</td>
 </tr>	
 <tr>
 	<td>Aggiorna i prezzi degli articoli e anche agli articolo associati agli ordini</td>
 	<td>Si</td>
 	<td>No</td>
 </tr>	
       </tbody>
    </table>
</div>  


<div role="alert" class="alert alert-info">
	<strong>Nota: </strong> Se il G.A.S. o l'utente non ha abilitato la "gestione degli articolo associati all'ordine" si presenterà solamente il tasto "Aggiorna i prezzi degli articoli" e in automatico saranno aggiornati anche i prezzi degli articoli associati agli ordini.
</div>


<h1 id="modifica-prezzi-in-percentuale" class="page-header">Modifica prezzi in %</h1>

<div role="alert" class="alert alert-danger">
	<strong>Modulo non ancora implementato</strong>
</div>

<h1 id="modifica-prezzo-degli-articolo-associati-agli-ordini" class="page-header">Modifica prezzo degli articolo associati agli ordini</h1>

<p>Cliccando su questa voce di menù si accede al modulo per ricercare gli articolo associati ad un determinato ordine.</p>

<p>Filtrando per la consegna e per l'ordine si accede all'elenco degli articoli associati all'ordine.</p>

<p>Modificando i valori sotto indicati</p>

<ul>
	<li>saranno modificati solo nell'ambito dell'ordine</li>
	<li>non saranno modificati nell'anagrafica dell'articolo</li>
</ul>

<h1 id="importa-articoli" class="page-header">Importa articoli</h1>

<p>Cliccando su questa voce di menù, raggiungibile dal percorso Home => Referenti => Articoli => importa articoli, si accede al modulo per importare con un file con estensione csv gli articoli in PortAlGas.</p>

<p>Di seguito, come si presenta la maschera iniziale</p>

<p>
<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/articoli-importa.png" class="img-responsive"></a>
</p>

<p>Il file dev'essere formattato nel modo che segue</p>

<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/articoli-importazione-formato.png" /></a>

<p>I valori consentiti per i campi

		<ul>
			<li>"Prezzo": valore numerico con 2 decimali (esempio 1,00)</li>
			<li>"Quantità": valore numerico con 2 decimali (esempio 1,00)</li>
			<li>"Unità di misura": Pz, Gr, Hg, Kg, Ml, Dl, Lt</li>
			<li>"Unità di misura di riferimento": Pz, Gr, Hg, Kg, Ml, Dl, Lt</li>
			<li>"Bio": Y se è biologico, N se non è biologico</li>
		</ul>
</p>

<div class="alert alert-info" role="alert">
	<strong>Nota: </strong> per evitare problemi di carico su PortAlGas, sono consentiti file con un massimo di <strong>80</strong> righe.
</div>


<h1 id="associamo-gli-articoli-all-ordine" class="page-header">Associamo gli articoli all’ordine</h1>

<p>Ora possiamo scegliere quali articoli, tra quelli del produttore scelto, associare all’ordine.</p>

<p>In questa fase possiamo settare alcuni valori degli articoli.</p>

<p>
<ul>
	<li>il prezzo</li>
	<li>il numero di pezzi in una confezione</li>
	<li>la quantità minima che un gasista può ordinare</li>
	<li>la quantità massima che un gasista può ordinare</li>
	<li>multipli di</li>
	<li>la quantità minima che si può ordinare rispetto al totale degli acquisti</li>
	<li>la quantità massima che si può ordinare rispetto al totale degli acquisti</li>
</ul>
</p>

<p>Di default questi campi hanno il valore preso dall’anagrafica degli articoli</p>

<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/articles-order-add-articles-fields.png" /></a>

<div role="alert" class="alert alert-info">
	<strong>Nota: </strong> in questo elenco verranno presentati solo gli articoli con i campi
	<ul>
		<li>"Stato" a <span style="color:green">Si</span></li>
		<li>"Presente tra gli articoli da ordinare" a <span style="color:green">Si</span></li>
	</ul>
	<p>Maggiori informazioni, clicca su <a href="/gestione_degli_articoli.php#gestione-visibilita-degli-articoli">Gestione visibilità degli articoli</a></p>
</div>

<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/articles-order-associa-articles.png" /></a>

<p>Cliccando su <a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/ico-view-more.png" /></a> </p>
<p>visualizzerai gli eventuali acquisti</p>

<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/articles-order-associa-articles-details.png" /></a>

<p>Cliccando su <a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/ico-edit.png" /></a> </p>
<p>selezioni l'articolo che desideri modificare, di seguito i parametri modificabili contestualmente all’ordine:</p>


<ul>
	<li>il prezzo</li>
	<li>il numero di pezzi in una confezione</li>
	<li>la quantità minima che un gasista può ordinare</li>
	<li>la quantità massima che un gasista può ordinare</li>
	<li>multipli di</li>
	<li>la quantità minima che si può ordinare rispetto al totale degli acquisti</li>
	<li>la quantità massima che si può ordinare rispetto al totale degli acquisti</li>
</ul>

<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/articles-order-associa-articles-edit.png" /></a>


<p>Questi valori</p>

<ul>
	<li>verranno modificati solo nell'associazione con quest'ordine</li>
	<li><strong>non</strong> verranno modificati nell'anagrafica dell'articolo</li>
</ul>

<p>se invece vuoi modificarlo per sempre, così da averlo per tutti i prossimi ordini, vai sull'anagrafica dell'articolo.</p>
				




<h1 id="gestione-visibilita-degli-articoli" class="page-header">Gestione visibilità degli articoli</h1>

<p>Gli articoli presentano 2 differenti campi che ne gestiscono la visibilità, il campo
<ul>	
	<li>"Presente tra gli articoli da ordinare"</li>
	<li>"Stato"</li>
</ul>
</p>

<p>Il campo "<b>Presente tra gli articoli da ordinare</b>" gestisce la visibilità degli articoli da associare ad un ordine: quando si crea un ordine si presenterà un elenco con i possibili articoli da associare</p>

<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/articles-orders-associa.png" /></a>

<p>Può essere utile per un referente settare il valore del campo "Presente tra gli articoli da ordinare" per alcuni articoli, magari stagionali, che non si desidera avere nell'elenco tra quelli da associare ad un ordine.</p>

<p>Il valore del campo "Presente tra gli articoli da ordinare" non modifica la visibiltà degli articoli già associati ad ordini esistenti.</p>

<p>Il campo "<b>Stato</b>" gestisce la visibilità degli articoli in maniera più generale; il suo valore influenza 
<ul>
	<li>la visibilità degli articoli da associare,</li>
	<li>la visibilità di articoli già associati ad ordini esistenti</li>
</ul>
</p>

<p>Andando a modificare l'anagrafica di un articolo, il campo "Stato"
<ul>
	<li>può essere impostato a <span style="color:green">Si</span> o <span style="color:red">No</span> se
		<ul>
			<li>l'articolo non è associato ad alcun ordine</li>
			<li>l'articolo è associato ad un ordine ma non sono ancora stati effettuati acquisti; valorizzando il campo Stato a <span style="color:red">No</span>, l'articolo non sarà più acquistabile</li>
		</ul>
	<li>
	<li>è già impostato a <span style="color:green">Si</span> e non si può modificare se
		<ul>
			<li>l'articolo è associato ad un ordine e sono già stati effettuati degli acquisti. <br />Il controllo dell'associazione di un articolo con gli ordini vale <b>per tutti gli ordini</b>, aperti o chiusi: questo perchè un articolo acquistato non potrà essere impostato con Stato a <span style="color:red">No</span> perchè nno sarebbe più visibile per eventuali pagamenti da parte del Cassiere o Tesoriere. Si dovrà attendere che l'ordine venga archiviato nelle statistiche per poter impostare il campo stato a <span style="color:red">No</span>.</li>
		</ul>
	</li>
</ul>
</p>
<p>
In base alla condizione dell'articolo, associato ad un ordine o acquistato, si presenterà una nota in corrispondenza al campo "Stato".
</p>

<p>
Se si desidera eliminare un articolo ad un ordine aperto clicca su , clicca su <a href="/gestione_degli_articoli.php#associamo-gli-articoli-all-ordine">Associamo gli articoli all’ordine</a>
</p>
				
		</div> <!-- col-sm-8 -->
		
		<div class="col-sm-3" role="complementary">

				<ul id="myScrollspy" class="nav nav-contenitore nav-stacked affix-top hidden-print hidden-xs hidden-sm" data-spy="affix" data-offset-top="125">
					<li class="active"><a href="#gli-articoli-su-portalgas">Gli articoli su PortAlGas</a></li>
					<li><a href="#articoli-per-gestire-la-loro-anagrafica">Articoli, per gestire la loro anagrafica</a>
						<ul class="nav">
							<li><a href="#ricerca-degli-articoli">Ricerca degli articoli</a></li>
							<li><a href="#anagrafica-degli-articoli">Anagrafica degli articoli</a>
								<ul class="nav">
									<li><a href="#il-tab-dei-dati-dell-articolo">Il Tab dei dati dell’articolo</a></li>
									<li><a href="#il-tab-del-prezzo">Il Tab del prezzo</a></li>
									<li><a href="#il-tab-delle-condizioni-d-acquisto">Il Tab delle condizioni d’acquisto</a></li>
									<li><a href="#il-tab-dell-immagine">Il Tab dell’immagine</a></li>
								</ul>
							</li>							
						</ul>					
					</li>
					<li><a href="#modifica-rapida-degli-articoli">Modifica rapida degli articoli</a></li>
					<li><a href="#stampa-articoli">Stampa articoli</a></li>
					<li><a href="#gestisci-categorie">Gestisci categorie</a></li>
					<li><a href="#modifica-prezzi">Modifica prezzi</a></li>
					<li><a href="#modifica-prezzi-in-percentuale">Modifica prezzi in %</a></li>
					<li><a href="#modifica-prezzo-degli-articolo-associati-agli-ordini">Modifica prezzo degli articolo associati agli ordini</a></li>
					<li><a href="#importa-articoli">Importa articoli</a></li>
					<li><a href="#associamo-gli-articoli-all-ordine">Associamo gli articoli all’ordine</a></li>
					<li><a href="#gestione-visibilita-degli-articoli">Gestione visibilità degli articoli</a></li>
				</ul>
		
		
		</div> <!-- col-sm-3 -->
	
	
	</div>	  <!-- container -->
  

<?php require('_inc_footer.php');?>