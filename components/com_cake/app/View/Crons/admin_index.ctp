<div class="crons">
	<h2 class="ico-config">
		<?php echo __('Cron');?>
	</h2>
</div>

<div class="crons form">

	<table cellpadding="0" cellspacing="0">
	<tr>
		<th><?php echo __('N');?></th>
		<th><?php echo __('Category');?></th>
		<th><?php echo __('Name');?></th>
		<th><?php echo __('nota');?></th>
		<th>Eseguito</th>
		<th><?php echo __('method');?></th>
		<th><?php echo __('Stato');?></th>
		<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php foreach ($crons as $numCrons => $cron) {?>
		<tr>
			<td><?php echo ($numCrons+1);?></td>
			<td><?php echo $cron['category'];?></td>
			<td><?php echo $cron['name'];?></td>
			<td><?php echo $cron['nota'];?></td>
			<td><?php echo $cron['execute'];?></td>
			<td><?php echo $cron['method'];?></td>
			<td title="<?php echo __('toolTipStato');?>" class="stato_<?php echo $this->App->traslateEnum($cron['stato']); ?>"></td>
			<td class="actions-table-img">
				<?php 
				/*
				 * escludo i cron senza logs (ex mail.sh)
				 */
				if(strpos($cron['method'],'.sh')===false) {
					$fileLog = date('Ymd').'_'.$cron['method'].'.log';
					echo $this->Html->link(null, array('action' => 'read', $fileLog),array('class' => 'action actionLog blank','title' => __('Read'))); 
					if($cron['category']=='DES')
						echo $this->Html->link(null, array('action' => 'execute_des', $cron['method']),array('class' => 'action actionRun blank','title' => __('Execute'))); 
					else
						echo $this->Html->link(null, array('action' => 'execute', $cron['method']),array('class' => 'action actionRun blank','title' => __('Execute'))); 
				}	
				?>
			</td>
		</tr>	
	<?php }?>
	</table>

</div>

<div class="actions">
	<h3><?php echo __('Directory'); ?></h3>
	<ul>
		<li>Backup del codice <?php echo $this->App->formatSizeUnits($dir_size_backup);?></li>
		<li>Dump del database <?php echo $this->App->formatSizeUnits($dir_size_dump);?></li>
		<li>Logs dei cron <?php echo $this->App->formatSizeUnits($dir_size_log);?></li>
	</ul>
	<p>
	 file più vecchi di <?php echo Configure::read('GGLogDelete');?> sono cancellati dal Cron::filesystemLogDelete()
	</p>
</div>
