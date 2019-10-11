<div class="legenda">

<table cellpadding="0" cellspacing="0" >
	<tr>
		<td colspan="8"><h3>Stato elaborazione delle richieste di pagamento degli ordini</h3></td>
	</tr>
	<tr>
		<td title="<?php echo $this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_WAIT');?>" class="stato_wait"></td>
		<td width="30%">
			<?php echo $this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_WAIT');?>
		</td>
		<td title="<?php echo $this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_OPEN');?>" class="stato_open"></td>
		<td width="30%">
			<?php echo $this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_OPEN');?>
		</td>
		<td title="<?php echo $this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_CLOSE');?>" class="stato_close"></td>
		<td width="30%">
			<?php echo $this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_CLOSE');?>
		</td>
	</tr>
	<tr>
		<td></td>
		<td>La richiesta di pagamento non è ancora visibile agli utenti, il tesoriere può modificarla</td>
		<td></td>
		<td>La richiesta di pagamento è aperta: 
			<ul style="margin: 5px 0 0 0;">
				<li>viene inviata una mail agli utenti per avvisarli di effettuare il pagamento</li>
				<li>è ora visibile agli utenti</li>
			</ul>
		</td>
		<td></td>
		<td>
			Tutti pagamenti sono stati effettuati, la richiesta di pagamento è chiusa<br />
			Un Cron notturno cancellerà tutti i dati associati.
		</td>
	</tr>
</table>

</div>