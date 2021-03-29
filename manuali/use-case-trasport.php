<?php require('_inc_header.php');?>
 
    <div class="container">
      

        <div class="col-sm-8 cakeContainer" role="main">

		<h1 id="gestione-degli-articoli-per-gli-ordini-settimanali">Gestione del trasporto (o costo aggiuntivo o sconto)</h1>
		
			<p>Se devo gestire il trasporto, oppure un costo aggiuntivo o uno sconto devo eseguire i seguenti passi:</p>

			<p>andate in <b>modifica</b> dell'ordine</p>

			<p>
				<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/use-case-trasport/01.png" class="img-responsive"></a>
			</p>

			<p>Clicco sul tab "Dopo l'arrivo della merce", fleggo a Si sulla voce "Ha le spese di trasporto" (o "Gestisco un costo aggiuntivo" o "Gestisco uno sconto")</p>

			<p>
				<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/use-case-trasport/02.png" class="img-responsive"></a>
			</p>

			<p>Quando si desidera gestire il trasporto, cambiate lo <b>stato</b> dell'ordine in "Merce arrivata": i calcoli sul trasporto vengono fatti in base agli acquisti effettuati dai gasisti e finchè non arriva la merce non ho la conferma che quello ordinato è arrivato</p>

			<p>
				<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/use-case-trasport/03.png" class="img-responsive"></a>
			</p>

			<p>Comparirà la voce di menù "<b>Getione del trasporto</b>", clicco ed accedo al modulo del trasporto</p>

			<p>
				<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/use-case-trasport/04.png" class="img-responsive"></a>
			</p>

			<p>imposto l'<b>importo</b> (se è in percentuale rispetto al totale, ottengo il totale importo dalle stampe relative e lo calcolo)</p>

			<p>scelgo tra le 3 tipologie</p>

			<p>
				<ul>
					<li>Divido il trasporto in base all'<b>importo</b> di ogni utente</li>
					<li>Divido il trasporto in base al <b>peso</b> di ogni acquisto</li>
					<li>Divido il trasporto per ogni <b>utente</b></li>
				</ul>
			</p>

			<p>Maggiori informazioni, clicca su <a href="/moduli.php#modulo-per-la-gestione-del-trasporto">Modulo per la “gestione del trasporto”</a></p>

			<p>
				<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/use-case-trasport/05.png" class="img-responsive"></a>
			</p>
		
			<p><b>Memo</b>: poichè i calcoli sul trasporto vengono fatti <b>in base agli acquisti effettuati</b> dai gasisti, se calcolo già il trasporto per i gasisti ma poi <b>successivamente</b>:
			</p>
            
  			<p>
				<ul>
					<li><b>cancello</b> un articolo acquistato dall'ordine (con la voce di menù "Modifica gli articoli associati")</li>
					<li>modifico la <b>quantità</b> acquistata da un gasista (con la voce di menù "Gestisci gli acquisti nel dettaglio")</li>
					<li>modifico l'<b>importo finale</b> degli acquisti di un gasista (con la voce di menù "Gestisci gli acquisti nel dettaglio")</li>
				</ul>
			</p>
			        
			<p>dovrò tornare sul modulo del trasporto e <b>ricalcolarlo</b></p>

		</div> <!-- col-sm-8 -->
		
		<div class="col-sm-3" role="complementary">

				<ul id="myScrollspy" class="nav nav-contenitore nav-stacked affix-top hidden-print hidden-xs hidden-sm" data-spy="affix" data-offset-top="125">
					<li class="active"><a href="use-case-trasport.php">Gestione del trasporto</a></li>
					<li><a href="/use-case.php#gestione-degli-articoli-per-gli-ordini-settimanali">Gestione degli articoli per gli ordini settimanali</a></li>
				</ul>
 
		
		</div> <!-- col-sm-3 -->
		
	
	</div>	  <!-- container -->
  

<?php require('_inc_footer.php');?>