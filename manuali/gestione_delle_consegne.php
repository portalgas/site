<?php require('_inc_header.php');?>
 
    <div class="container">
      

	  

        <div class="col-sm-8 cakeContainer" role="main">

		<h1 id="la-gestione-delle-consegne-in-portalgas" class="page-header">La gestione delle consegne in PortAlGas</h1>
		
			<p>PortAlGas permette di gestire le consegne attraverso il menù superiore, che è così composto:</p>
			<p>Solamente per gli utenti con il ruolo di "<strong>Manager consegne</strong>", sono disponibili le seguenti voci:</p>
			<ul>
				<li>Consegne: elenco di tutte le consegne aperte</li>
				<li>Consegne storiche: elenco di tutte le consegne chiuse</li>
				<li>Ricorsione delle consegne</li>
			</ul>

			<p>Per tutti gli utenti che <strong>non</strong> hanno il ruolo di "Manager consegne" è disponibile la seguente voce:</p> 
			<p>
			<ul>
				<li>Consegne: elenco in sola <strong>lettura</strong> di tutte le consegne aperte</li>
			</ul>	
			</p>

			<a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/consegne-elenco.png" /></a>
	
	
			<h1 id="invio-della-mail-ai-gasisti-il-giorno-precedente-la-consegna">Invio della mail ai gasisti il giorno precedente la consegna</h1>
			
					<p>Un giorno prima della consegna sarà inviata una mail a tutti i gasisti che hanno effetuato acquisti con</p>
					<ul>
						<li>l'elenco di tutti i produttori presenti alla consegna</li>
						<li>il link alla pagina di PortAlGas con il carrello dell'utente per quella determinata consegna</li>
					</ul>

					<p>Di seguito un esempio di mail</p>
				
					<p><a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/consegne-notifica-mail.png" /></a></p>
				
					<p> 	
					<div role="alert" class="alert alert-info">
						<strong>Nota: </strong> se hai problemi a ricevere la mail <a href="problemi.php#non-ricevo-le-mail-di-portalgas">leggi qui</a>
					</div>	
					</p>	

			
			<h1 id="gestione-delle-consegne-ricorsive">Gestione delle consegne ricorsive</h1>
			
				<p>PortAlGas permette di impostare delle regole per la creazione in automatico delle consegne.</p>
				
				<p>Se si vuole utilizzare questa funzionalità, sarà sufficiente creare una consegna ricorsiva con questi parametri:</p>
									
					<ul>
						<li>Una data di consegna: alla scadenza sarà creata la nuova consegna con le regole sotto impostate</li>
						<li>Le regole di ricorsioni: si legga sotto</li>
						<li>I dati della nuova consegna: sono i dati, per esempio il luogo di consegna, che saranno riportati sulla nuova consegna</li>
						<li>Flag per ricevere una mail alla creazione della consegna: serve come memorandum per evitare che una consegna ricorsiva venga dimenticata da chi l'ha creata!</li>
					</ul>
					
				<p>Le regole di ricorsioni possono essere di 2 tipologie</p>
													
				<ul>
					<li>Settimanali
						<p><a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/consegne-add-ricorrenza-settimanale.jpg" /></a></p>
					</li>
					<li>Mensili
						<p><a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/consegne-add-ricorrenza-mensile.jpg" /></a></p>
					</li>
				</ul>				
						
				<h2 id="gestione-delle-consegne-ricorsive-esempio">Esempio</h2>		
				<p>Per semplificare, facciamo un esempio!</p>
				
				<p>
					Abbiamo un produttore che ogni settimana, il mercoledì, ci consegna la propria merce.
					<ul>
						<li>Creo una nuova "consegna ricorsiva"</li>
						<li>Valorizzo il campo "Alla data di" con il prossimo mercoledì (per esempio mercoledì 6 maggio): sarà il giorno dal quale PortAlGas inizierà il conteggio per creare la nuova consegna (mercoledì 13 maggio)</li>
						<li>Imposto come criteri di ricorsione
							<ul>
								<li>Settimanale</li>
								<li>ricorre ogni <b>1</b> settimana/e</li>
							</ul>							
						</li>
						<li>Valorizzo i campi anagrafici (luogo della consegna, eventuali note) che saranno riportati nella nuova consegna</li>
					</ul>	
				</p>
				
				<p>Cliccando su "preview" saranno visualizzati 2 calendari</p>

					<ul>
						<li>Uno con la data di partenza del conteggio ricorsivo</li>
						<li>Uno con la data della nuova consegna</li>
					</ul>	

					<p><a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/consegne-add-ricorrenza-preview.jpg" /></a></p>

				<p>Se tutto corretto, salva</p>
				
					<p><a title="clicca per ingrandire l'immagine" class="img_orig" href="" data-toggle="modal" data-target="#modalImg"><img class="img-responsive" src="images/consegne-ricorsive.jpg" /></a></p>
				
				<p>Il giorno mercoledì 6 maggio sarà creata una nuova consegna con data mercoledì 13 maggio, così per ogni settimana</p>
				
				<p>Qualora <b>esistesse già</b> una consegna per quel giorno, verrà comunque creata la consegna; PortAlGas permette di gestire più consegne nella medesima data perchè il GAS potrebbe avere diversi punti di distribuzione.</p>
				
				<p>Qualora il GAS fosse integrato con <b>GCalendar</b>, la consegna sarà notificata con GCalendar. (si veda il manuale su <a title="vai alla pagina del manuale" href="social-integration.php#integrare-gcalendar-a-portalgas">Integrare GCalendar a PortAlGas</a>)</p>	
									
				<div class="alert alert-info" role="alert">
					<strong>Nota: </strong> La consegna ricorsiva con data "mercoledì 6 maggio" non sarà creata, ma sarà la data di partenza per creare le consegne successive: esse saranno invece create e quindi disponibili a tutti i referenti nell'elenco delle consegne
						<p>Si è creata una regola di ricorsione che partità "mercoledì 6 maggio" e creerà la sua prima consegna mercoledì 13 maggio e così ogni settimana</p>
				</div>
				<h2 id="gestione-delle-consegne-ricorsive-salta-una-consegna">Salta una consegna</h2>		
				
				<p>Se, creata la consegna ricorsiva di esempio, dovesse capitare che per una settimana la consegna non verrà effettuata oppura cambia di data, cosa fare?</p>

				<p>Andate in modifica della vostra consegna ricorsiva e modificate la data di apertura della nuova consegna: PortAlGas creerà la consegna con la nuova data ma per la consegna successiva manterrà il criterio di ricorsione, quindi per esempio</p>
				
					<ul>
						<li>Data di quando creare la consegna. mercoledì 6 maggio</li>
						<li>Data della nuova consegna: mercoledì 13 maggio</li>
						<li>La consegna ancora successiva sarebbe: mercoledì 13 maggio</li>
						<li>Modifico la data da mercoledì 13 maggio e giovedì 14 maggio</li>
						<li>Mercoledì 6 maggio sarà creata la nuova consegna con data giovedì 14 maggio e la successiva rimarrà mercoledì 20 maggio</li>
					</ul>				
				
				<br />
				
		</div> <!-- col-sm-8 -->
		
		<div class="col-sm-3" role="complementary">

				<ul id="myScrollspy" class="nav nav-contenitore nav-stacked affix-top hidden-print hidden-xs hidden-sm" data-spy="affix" data-offset-top="125">
					<li class="active"><a href="#la-gestione-delle-consegne-in-portalgas">La gestione delle consegne in PortAlGas</a></li>
					<li><a href="#invio-della-mail-ai-gasisti-il-giorno-precedente-la-consegna">Invio della mail ai gasisti il giorno precedente la consegna</a></li>
					<li><a href="#gestione-delle-consegne-ricorsive">Gestione delle consegne ricorsive</a>
						<ul>
							<li><a href="#gestione-delle-consegne-ricorsive-esempio">Esempio</a></li>
							<li><a href="#gestione-delle-consegne-ricorsive-salta-una-consegna">Salta una consegna</a></li>
						</ul>
					</li>
				</ul>
		
		
		</div> <!-- col-sm-3 -->
		
		
	</div>	  <!-- container -->
  

<?php require('_inc_footer.php');?>