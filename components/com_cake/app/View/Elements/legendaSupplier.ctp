<div class="legenda">

<table cellpadding="0" cellspacing="0">
	<tr>
		<td colspan="7"><h3>Stato dei produttori</h3></td>
	</tr>
	<tr>
		<td style="width:20px"></td>
		<td class="stato_y" style="width:20px"></td>
		<td width="33%">
			<?php echo $this->App->traslateEnum('Y');?>
		</td>
		<td class="stato_n" style="width:20px"></td>
		<td width="33%">
			<?php echo $this->App->traslateEnum('N');?>
		</td>
		<td class="stato_lock" style="width:20px"></td>
		<td width="33%">
			<?php echo $this->App->traslateEnum('T');?>
		</td>
	</tr>
	<tr>
		<td></td>
		<td></td>
		<td>Il produttore sarà visibile nella lista per essere scelto dalle diverse organizzazioni</td>
		<td></td>
		<td>Il produttore non sarà visibile nella lista per essere scelto dalle diverse organizzazioni</td>
		<td></td>
		<td>Il produttore è stato creato da un organizzazione e necessita di una validazione</td>
	</tr>	
</table>

</div>