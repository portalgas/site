<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Events'), array('controller' => 'Events', 'action' => 'index'));
$this->Html->addCrumb(__('Add Event'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>
<div class="events form">
<?php echo $this->Form->create('Event', array('id' => 'formGas'));?>
	<fieldset>
 		<legend><?php __('Add Event'); ?></legend>
	<?php
		echo $this->Form->input('event_type_id', array('label' => __('EventsType')));
		echo $this->Form->input('title');

		$options = array(
				 'data-placeholder' => 'Scegli un responsabile',
				 'label' => __('EventUser'), 
				 'options' => $usersRespResults, 
				 'required' => 'false',
				 'empty' => Configure::read('option.empty'));
		if(count($usersRespResults) > Configure::read('HtmlSelectWithSearchNum')) 
			$options += array('class'=> 'selectpicker', 'data-live-search' => true); 
		echo $this->Form->input('user_id', $options);
	
		echo $this->Form->input('nota', array('label' => __('EventNota')));
		echo $this->Form->input('start', array('label' => __('EventStart'), 'minYear' => (date(Y)-2), 'maxYear' => (date(Y)+2)));
		echo $this->Form->input('end', array('label' => __('Eventend'), 'minYear' => (date(Y)-2), 'maxYear' => (date(Y)+2)));
		echo $this->Form->input('date_alert_mail', array('label' => __('EventDateAlertMail'), 'required' => false, 'empty' => true, 'minYear' => (date(Y)-2), 'maxYear' => (date(Y)+2)));
		echo $this->Form->input('date_alert_fe', array('label' => __('EventDateAlertFE'), 'required' => false, 'empty' => true, 'minYear' => (date(Y)-2), 'maxYear' => (date(Y)+2)));
		
		echo $this->App->drawFormRadio('Event','isVisibleFrontEnd',array('options' => $isVisibleFrontEnd, 'value'=> 'Y', 'label'=>__('isVisibleFrontEnd'), 'required'=>'required',
																'after'=>$this->App->drawTooltip(null,__('toolTipIsVisibleFrontEnd'),$type='HELP')));				
		
		/*
		echo $this->Form->input('all_day', array('checked' => 'checked'));
		echo $this->Form->input('status', array('options' => array(
					'Scheduled' => 'Scheduled','Confirmed' => 'Confirmed','In Progress' => 'In Progress',
					'Rescheduled' => 'Rescheduled','Completed' => 'Completed'
				)
			)
		);
		*/
		
	/*
	 * utenti ancora da associare
	 */
	echo '<div>';
	echo '<label for="User">'.__('Users').'</label>';

	echo $this->Form->select('master_user_id', $usersResults, array('label' => __('Users'), 'id' => 'master_user_id', 'multiple' => true, 'size' =>10));
	echo $this->Form->select('event_user_id', $eventUsersResults, array('id' => 'event_user_id', 'multiple' => true, 'size' => 10, 'style' => 'min-width:300px'));
	echo $this->Form->hidden('event_user_ids', array('id' => 'event_user_ids','value' => ''));
	echo '</div>';
	?>

	<script type="text/javascript">
	$(document).ready(function() {

		$('#master_user_id').click(function() {
			$("#master_user_id option:selected" ).each(function (){			
				$('#event_user_id').append($("<option></option>")
				 .attr("value",$(this).val())
				 .text($(this).text()));
				 
				 $(this).remove();
			});
		});
		
		$('#event_user_id').click(function() {
			$("#event_user_id option:selected" ).each(function (){			
				$('#master_user_id').append($("<option></option>")
				 .attr("value",$(this).val())
				 .text($(this).text()));
				 
				 $(this).remove();
			});
		});
		
		jQuery('#formGas').submit(function() {

			var event_user_ids = '';
			jQuery("#event_user_id option").each(function (){	
				event_user_ids +=  jQuery(this).val()+',';
			});
			event_user_ids = event_user_ids.substring(0,event_user_ids.length-1);

			if(event_user_ids=='') {
				alert("Devi selezionare almeno un utente da associare all'attività");
				return false;
			}

			jQuery("#event_user_ids" ).val(event_user_ids);
			
			return true;
		});		
	});
	</script>		
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Events'), array('action' => 'index'),array('class'=>'action actionReload'));?></li>
		<?php /* echo '<li>'.$this->Html->link(__('View Calendar'), array('controller' => 'FullCalendar', 'action' => 'index'),array('class'=>'action actionDeliveryCalendar')).'</li>'; */ ?>
	</ul>
</div>
