<?php
/*
 * View/FullCalendar/index.ctp
 * CakePHP Full Calendar Plugin
 *
 * Copyright (c) 2010 Silas Montgomery
 * http://silasmontgomery.com
 *
 * Licensed under MIT
 * http://www.opensource.org/licenses/mit-license.php
 */
?>
<script type="text/javascript">
var plgFcRoot = "?option=com_cake&controller=Events&action=";
</script>
<?php
echo $this->Html->script(array('/js/fullcalendarEvents/jquery-1.5.min', 
							   '/js/fullcalendarEvents/jquery-ui-1.8.9.custom.min', 
							   '/js/fullcalendarEvents/fullcalendar.min', 
							   '/js/fullcalendarEvents/jquery.qtip-1.0.0-rc3.min', 
							   '/js/fullcalendarEvents/ready'), array('inline' => 'false'));
echo $this->Html->css('/css/fullcalendar', null, array('inline' => false));
?>

<div class="events form">
	<h2 class="ico-wait">
		<?php echo __('Events');?>
		<div class="actions-img">
			<ul>
				<li><?php echo $this->Html->link(__('List Events'), array('controller' => 'Events', 'action' => 'index'),array('class' => 'action actionReload','title' => __('List Events'))); ?></li>
			</ul>
		</div>
	</h2>
		

	<div id="calendar"></div>

</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Events'), array('controller' => 'Events', 'action' => 'index'),array('class'=>'action actionReload'));?></li>
		<li><?php echo $this->Html->link(__('Add Event'), array('controller' => 'Events', 'action' => 'add'),array('class'=>'action actionAdd'));?></li>
	</ul>
</div>
