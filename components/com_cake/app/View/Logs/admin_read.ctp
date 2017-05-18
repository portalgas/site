<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('Logs'), array('controller' => 'Logs', 'action' => 'index'));
$this->Html->addCrumb(__('Read Log'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>
<div class="logs">
	<h2 class="ico-config">
		<?php echo __('Read Log');?>
	</h2>
</div>

<div class=""logs" form">
	
	<?php 
	if(!empty($file)) {
		$file_info = $file->info();
	
		echo $this->Form->create('Cron');?>
		<fieldset>
		
			<div class="input text"><label for="">Nome file</label><?php echo $file_info['filename'];?></div>
			<div class="input text"><label for="">Grandezza file</label><?php echo $this->App->formatSizeUnits($file_info['filesize']);?></div>
			<div class="input text"><label for="">Mime</label><?php echo $file_info['mime'];?></div>
		
		</fieldset>
		<?php echo $this->Form->end();?>
					
		<?php 
		echo "<pre>";
		print_r($contents);
		echo "</pre>";
	}
	else
		echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "File inesistente"));
		?>
</div>

<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Logs'), array('action' => 'index'),array('class'=>'action actionReload'));?></li>
	</ul>
</div>