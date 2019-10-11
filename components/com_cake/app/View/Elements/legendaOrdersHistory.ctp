<div class="legenda">
	PortAlGas cancellerà tutte
	<ul>
		<li>le <b>consegne</b> con data chiusura anteriore a <?php echo Configure::read('GGArchiveStatics');?> giorni</li>
		<?php
		if($user->organization['Template']['payToDelivery']=='POST' || $user->organization['Template']['payToDelivery']=='ON-POST') 
			echo '<li>alle <b>richieste di pagamento</b> con data anteriore a '.Configure::read('GGArchiveStatics').' giorni</li>';
		?>		
	</ul>
	ed effettuerà 
	<ul>
		<li>cancellazione Carrello / Ordini / Consegne</li>
		<li>dati archiviati in Statistiche</li>
	</ul>
</div>