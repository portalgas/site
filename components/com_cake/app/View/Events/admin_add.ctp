<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Events'), array('controller' => 'Events', 'action' => 'index'));
$this->Html->addCrumb(__('Add Event'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="events form">';
echo $this->Form->create('Event', array('id' => 'formGas'));
echo '<fieldset>';
echo '<legend>'.__('Add Event').'</legend>';

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

		echo '<div class="row">';
		echo $this->App->drawDateTime('Event', 'start', __('EventStart'), $this->request->data['Event']['start']);
		echo '</div>';		
		echo '<div class="row">';
		echo $this->App->drawDateTime('Event', 'end', __('Eventend'), $this->request->data['Event']['end']);
		echo '</div>';
		
		echo $this->App->drawDate('Event', 'date_alert_mail', __('EventDateAlertMail'), $this->request->data['Event']['date_alert_mail']);
		echo $this->App->drawDate('Event', 'date_alert_fe', __('EventDateAlertFE'), $this->request->data['Event']['date_alert_fe']);
		
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
	echo '<div class="row"><div class="col-md-12">';
	echo '<label for="User">'.__('Users').'</label> ';

	echo $this->Form->select('master_user_id', $usersResults, array('label' => __('Users'), 'id' => 'master_user_id', 'multiple' => true, 'size' =>10));
	echo $this->Form->select('event_user_id', $eventUsersResults, array('id' => 'event_user_id', 'multiple' => true, 'size' => 10, 'style' => 'min-width:300px'));
	echo $this->Form->hidden('event_user_ids', array('id' => 'event_user_ids','value' => ''));
	echo '</div></div>';
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
		
		$('#formGas').submit(function() {

			var event_user_ids = '';
			$("#event_user_id option").each(function (){	
				event_user_ids +=  $(this).val()+',';
			});
			event_user_ids = event_user_ids.substring(0,event_user_ids.length-1);

			if(event_user_ids=='') {
				alert("Devi selezionare almeno un utente da associare all'attività");
				return false;
			}

			$("#event_user_ids" ).val(event_user_ids);
			
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
