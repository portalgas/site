<?php require('_inc_header.php');?>
 
    <div class="container">
      

        <div class="col-sm-8 cakeContainer" role="main">

		<h1 id="gestione-cart">Gestione degli acquisti</h1>
		
            <div role="alert" class="alert alert-info">
				<strong>Nota: </strong> il referente di un ordine potrà gestirne (modificare, cancellare o aggiungere) gli acquisti <b>solo</b> 
                quando l'ordine sarà <b>chiuso</b> (lo stato dell'ordine sarà "In carico al referente prima della consegna") e quindi quando i gasisti non potranno più effettuare gli acquisti.
			</div>	

			<p>Accedere all'area di amministrazione (backoffice) https://www.portalgas.it/my e autenticarsi con le proprie credenziali</p>

			<p>Accedete tramite il menù superiore al modulo <b>Ordini</b>: Referenti => Ordini</p>

			<p>
				<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/use-case-cart-update/01.png" class="img-responsive"></a>
			</p>

			<p>Si avrà la lista di tutti gli ordini del proprio G.A.S.</p>
			<p>In corrispondenza dall'ordine con il quale vogliamo lavorare cliccare sull'icona del "<b>menù</b>"</p>

			<p>
				<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/use-case-cart-update/02.png" class="img-responsive"></a>
			</p>

            <p>Si visualizzeranno tutte le voci di menù attive i base allo stato dell'ordine</p>
            <p>Cliccate sulla voce di menù <b>Gestisci gli acquisti nel dettaglio</b></p>

			<p>
				<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/use-case-cart-update/03.png" class="img-responsive"></a>
			</p>

            <h1 id="gestione-modulo">Modulo "Gestisci gli acquisti dell'ordine nel dettaglio"</h1>

            <p>Si accederà al modulo "Gestisci gli acquisti dell'ordine nel dettaglio"</p>
            <p>Cliccare su</p>
			<p>
				<ul>
					<li><b>Opzioni report</b>: Tutti gli utenti </li>
					<li><b>Utente</b>: scegliere il gasista</li>
					<li><b>Opzioni articoli</b>: Tutti gli articoli</li>
				</ul>
			</p>

			<p>In corrispondenza dell'articolo cliccare sul tasto +</p>
			<p>raggiunta la quantità desiderata, cliccare sull'icona del dischetto (salva)</p>

			<p>
				<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/use-case-cart-update/04.png" class="img-responsive"></a>
			</p>

            <p>Maggior dettagli sul modulo "Gestisci gli acquisti dell'ordine nel dettaglio" <a href="moduli.php#modulo-per-la-gestione-degli-acquisti-nel-dettaglio">leggi qui</a></p>


        <h1 id="gestione-multiple">Gestione delle quantità multiple</h1>

        <p>Se i nostri articoli sono in confezioni di 12 colli possiamo impostare le quantità multiple, così cliccando sul tasto + la quantità sarà incrementata con multipli di 12</p>
        
        <p>Torniamo sull'elenco degli ordini</p>
        <p>In corrispondenza dall'ordine con il quale vogliamo lavorare cliccare sull'icona del "<b>menù</b>"</p>
        <p>Si visualizzeranno tutte le voci di menù attive i base allo stato dell'ordine</p>
        <p>Cliccate sulla voce di menù <b>Modifica gli articoli associati</b></p>	

            <p>
				<a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/use-case-cart-update/05.png" class="img-responsive"></a>
			</p>

            <p>In corrispondenza dell'articolo interessato, cliccare sul icona di <b>modifica</b></p>	

            <p>
                <a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/use-case-cart-update/06.png" class="img-responsive"></a>
            </p>

            <h1 id="gestione-articoli">Modulo per la "gestione degli articoli associati all'ordine"</h1>
			
            <p>Accederemo al modulo della <b>gestione degli articoli associati all'ordine</b></p>
            <p>impostiamo il campo <b>Multipli di</b> con il valore desiderato (nell'esempio 12)</p>

            <p>
                <a data-target="#modalImg" data-toggle="modal" href="" class="img_orig" title="clicca per ingrandire l'immagine"><img src="images/use-case-cart-update/07.png" class="img-responsive"></a>
            </p>

            <p>ora aumentando la quantità degli acquisti, incremeteremo la quantità con multipli di 12 (12, 24, 36...)</p>

            <p>Maggior dettagli sul modulo "gestione degli articoli associati all'ordine" <a href="moduli.php#modulo-per-la-gestione-degli-articoli-associati-all-ordine">leggi qui</a></p>
 
            <div role="alert" class="alert alert-info">
				<strong>Nota: </strong> se impostiamo il campo <b>Num di pezzi in una confezione</b> possiamo gestire i <b>colli</b>,
                maggior dettagli sul modulo "gestione dei colli" <a href="moduli.php#modulo-per-la-gestione-dei-colli">leggi qui</a> 
			</div>	           


		</div> <!-- col-sm-8 -->
		
		<div class="col-sm-3" role="complementary">

				<ul id="myScrollspy" class="nav nav-contenitore nav-stacked affix-top hidden-print hidden-xs hidden-sm" data-spy="affix" data-offset-top="125">
					<li><a href="use-case-cart-update.php">Gestione del trasporto</a></li>
					<li><a href="use-case-cart-update.php">Ordine D.E.S. (condiviso)</a></li>
					<li class="active"><a href="use-case-cart-update.php">Gestire gli acquisti</a></li>
					<li><a href="/use-case.php#gestione-degli-articoli-per-gli-ordini-settimanali">Gestione degli articoli per gli ordini settimanali</a></li>
				</ul>
 
		
		</div> <!-- col-sm-3 -->
		
	
	</div>	  <!-- container -->
  

<?php require('_inc_footer.php');?>