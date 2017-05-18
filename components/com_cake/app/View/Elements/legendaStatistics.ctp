<div class="legenda">
	Qui troverete i dati relativi 
	<ul>
		<li>alle <b>consegne</b> con data chiusura anteriore a <?php echo Configure::read('GGArchiveStatics');?> giorni</li>
		<?php
		if($user->organization['Organization']['payToDelivery']=='POST' || $user->organization['Organization']['payToDelivery']=='ON-POST') 
			echo '<li>alle <b>richieste di pagamento</b> con data anteriore a '.Configure::read('GGArchiveStatics').' giorni</li>';
		?>
	</ul>
</div>