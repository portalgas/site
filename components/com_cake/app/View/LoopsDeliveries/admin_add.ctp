<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List LoopsDeliveries'), array('controller' => 'LoopsDeliveries', 'action' => 'index'));
$this->Html->addCrumb(__('Add LoopsDelivery'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>
<div class="loopsDeliveries form">
<?php echo $this->Form->create('LoopsDelivery', array('id'=>'formGas'));?>
	<fieldset>
		<legend><?php echo __('Add LoopsDelivery'); ?></legend>
		
		
		<?php 
		if(isset($data_master) &&isset($data_copy)) {
			echo $this->Html->css('fullcalendar-min');
			echo $this->Html->script('fullcalendar/moment.min');
			echo $this->Html->script('fullcalendar/fullcalendar.min');
			echo $this->Html->script('fullcalendar/lang-all');
		?>
			<script type="text/javascript">
			jQuery(document).ready(function() {
			
				var currentLangCode = 'it';
			
				jQuery('#calendar_master').fullCalendar({
					header: false,
					theme: false,
					lang: currentLangCode,
					editable: false,
					events: [
						{
							id: <?php echo $data_master;?>,
							title: false,
							start: new Date(<?php echo $data_master_value;?>),
							url: false,
							allDay: false,
							backgroundColor: 'green',
						}
					]
				});

				jQuery('#calendar_copy').fullCalendar({
					header: false,
					theme: false,
					lang: currentLangCode,
					editable: false,
					events: [
						{
							id: <?php echo $data_copy;?>,
							title: false,
							start: new Date(<?php echo $data_copy_value;?>),
							url: false,
							allDay: true,
						}
					]							
				});		

				jQuery('#calendar_master').fullCalendar( 'gotoDate', '<?php echo $data_master;?>' );	
				jQuery('#calendar_copy').fullCalendar( 'gotoDate', '<?php echo $data_copy;?>' );

				jQuery('.fc-time').css('display', 'none');
				jQuery('.fc-title').css('display', 'none');
				
			});
			</script>
			
			<div style="clear: none;float:left;width:45%;">
				<h3>Giorno di partenza per calcolare la ricorsione:<br /><?php echo $this->Time->i18nFormat($data_master,"%A %e %B %Y");?></h3>
				<div id="calendar_master"></div>
			</div>
			<div style="clear: none;float:left;width:45%;">
				<h3>Nuova Consegna:<br /><?php echo $this->Time->i18nFormat($data_copy,"%A %e %B %Y");?></h3>
				<div id="calendar_copy"></div>
			</div>		
			<?php 
		}
		?>
	
	<div style="clear: both;float:none;"></div>
	<h3>Alla data di</h3>	
	<?php	
	echo $this->Form->input('data_master',array('label' => false, 'type' => 'text','size'=>'30','value' => $this->Time->i18nFormat($this->Form->value('LoopsDelivery.data_master_db'),"%A, %e %B %Y")));
	
	echo $this->Ajax->datepicker('LoopsDeliveryDataMaster',array('dateFormat' => 'DD, d MM yy','altField' => '#LoopsDeliveryDataMasterDb', 'altFormat' => 'yy-mm-dd'));
	echo '<input type="hidden" id="LoopsDeliveryDataMasterDb" name="data[LoopsDelivery][data_master_db]" value="'.$this->Form->value('LoopsDelivery.data_master_db').'" />';
	?>

	 <h3>verrà creata una nuova consegna con i seguenti criteri di ricorrenza</h3>

	 <div style="float:left;width:25%;clear: none;">
		 <p>
			<input type="radio" value="WEEK" name="data[LoopsDelivery][type]" <?php echo (($this->request->data['LoopsDelivery']['type']=='WEEK') ? "checked" : "");?> /> Settimanale
		</p>
		<p>
			<input type="radio" value="MONTH" name="data[LoopsDelivery][type]"  <?php echo (($this->request->data['LoopsDelivery']['type']=='MONTH') ? "checked" : "");?> /> Mensile
		</p>
	</div>
	
	<div style="float:right;width:70%;clear: none;">

					<div id="type_week" style="display:none;">
						ricorre ogni <input type="text" value="<?php echo $this->request->data['LoopsDelivery']['week_every_week'];?>" class="noWidth" name="data[LoopsDelivery][week_every_week]" /> settimana/e
					</div>	
					
					<div id="type_month" style="display:none;">
						<p>
						
							<table>
								<tr>
									<td><input type="radio" value="MONTH1" name="data[LoopsDelivery][type_month]" <?php echo (($this->request->data['LoopsDelivery']['type_month']=='MONTH1') ? "checked" : "");?> /> Giorno</td>
									<td><input class="type_month1 noWidth" type="text" value="<?php echo $this->request->data['LoopsDelivery']['month1_day'];?>" name="data[LoopsDelivery][month1_day]" /> ogni <input class="type_month1 noWidth" type="text" value="<?php echo $this->request->data['LoopsDelivery']['month1_every_month'];?>" name="data[LoopsDelivery][month1_every_month]" /> mese/i</td>
								</tr>
							</table>
							
						</p>
						<p>
						
								<table>
									<tr>
										<td colspan="2"><input type="radio" value="MONTH2" name="data[LoopsDelivery][type_month]" <?php echo (($this->request->data['LoopsDelivery']['type_month']=='MONTH2') ? "checked" : "");?> /> Ogni</td>
									</tr>
									<tr>
										<td></td>
										<td>
											<select class="type_month2" name="data[LoopsDelivery][month2_every_type]">
												<option value="FIRST" <?php echo (($this->request->data['LoopsDelivery']['month2_every_type']=='FIRST') ? "selected" : "");?> ><?php echo $this->App->traslateEnum('FIRST');?></option>
												<option value="SECOND" <?php echo (($this->request->data['LoopsDelivery']['month2_every_type']=='SECOND') ? "selected" : "");?> ><?php echo $this->App->traslateEnum('SECOND');?></option>
												<option value="THIRD" <?php echo (($this->request->data['LoopsDelivery']['month2_every_type']=='THIRD') ? "selected" : "");?> ><?php echo $this->App->traslateEnum('THIRD');?></option>
												<option value="FOURTH" <?php echo (($this->request->data['LoopsDelivery']['month2_every_type']=='FOURTH') ? "selected" : "");?> ><?php echo $this->App->traslateEnum('FOURTH');?></option>
												<option value="LAST" <?php echo (($this->request->data['LoopsDelivery']['month2_every_type']=='LAST') ? "selected" : "");?> ><?php echo $this->App->traslateEnum('LAST');?></option>
											</select>						
										</td>
									</tr>
									<tr>
										<td></td>
									<td>
											<ul>
												<li><input class="type_month2" type="checkbox" value="MON" name="data[LoopsDelivery][month2_day_week]" <?php echo (($this->request->data['LoopsDelivery']['month2_day_week']=='MON') ? "checked" : "");?> /><?php echo $this->App->traslateEnum('MON');?></option>
												<li><input class="type_month2" type="checkbox" value="TUE" name="data[LoopsDelivery][month2_day_week]" <?php echo (($this->request->data['LoopsDelivery']['month2_day_week']=='TUE') ? "checked" : "");?>  /><?php echo $this->App->traslateEnum('TUE');?></option>
												<li><input class="type_month2" type="checkbox" value="WED" name="data[LoopsDelivery][month2_day_week]" <?php echo (($this->request->data['LoopsDelivery']['month2_day_week']=='WED') ? "checked" : "");?>  /><?php echo $this->App->traslateEnum('WED');?></option>
												<li><input class="type_month2" type="checkbox" value="THU" name="data[LoopsDelivery][month2_day_week]" <?php echo (($this->request->data['LoopsDelivery']['month2_day_week']=='THU') ? "checked" : "");?>  /><?php echo $this->App->traslateEnum('THU');?></option>
												<li><input class="type_month2" type="checkbox" value="FRI" name="data[LoopsDelivery][month2_day_week]" <?php echo (($this->request->data['LoopsDelivery']['month2_day_week']=='FRI') ? "checked" : "");?>  /><?php echo $this->App->traslateEnum('FRI');?></option>
												<li><input class="type_month2" type="checkbox" value="SAT" name="data[LoopsDelivery][month2_day_week]" <?php echo (($this->request->data['LoopsDelivery']['month2_day_week']=='SAT') ? "checked" : "");?>  /><?php echo $this->App->traslateEnum('SAT');?></option>
												<li><input class="type_month2" type="checkbox" value="SUN" name="data[LoopsDelivery][month2_day_week]" <?php echo (($this->request->data['LoopsDelivery']['month2_day_week']=='SUN') ? "checked" : "");?>  /><?php echo $this->App->traslateEnum('SUN');?></option>
											</ul>
															
										</td>
									</tr>
									<tr>
										<td></td>
										<td>
											ogni <input class="type_month2 noWidth" type="text" value="<?php echo $this->request->data['LoopsDelivery']['month2_every_month'];?>" name="data[LoopsDelivery][month2_every_month]" /> mese/i					
										</td>
									</tr>
								</table>
						</p>
					</div>		
	</div>

	<?php 	
		echo $this->Html->div('clearfix','');

		echo '<h3>con i parametri</h3>';
		
		echo $this->Ajax->autoComplete('luogo',
									  Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Ajax&action=autoCompleteDeliveries_luogo&format=notmpl',
									  array('label' => 'Luogo Consegna','div' => 'required'));
	
		echo $this->Form->input('orario_da', array('type' => 'time','selected' => $orario_da,'timeFormat'=>'24','interval' => 15));
		echo $this->Form->input('orario_a',  array('type' => 'time','selected' => $orario_a, 'timeFormat'=>'24','interval' => 15));

		echo $this->Form->input('nota');
	
		echo $this->Form->input('nota_evidenza',array('options' => $nota_evidenza,
													'id' => 'DeliveryNotaEvidenza',
													'value' => $this->Form->value('LoopsDelivery.nota_evidenza'),
													'label' => 'Nota evidenza',
													'after'=>'<div id="DeliveryNotaEvidenzaImg" style="float:right;height:18px;width:400px;" class=""></div>'));
			
		echo $this->Html->div('clearfix','');	
		
		echo $this->App->drawFormRadio('LoopsDelivery','flag_send_mail',array('options' => $flag_send_mail, 'value'=> $flag_send_mailDefault, 'label'=> "Notifico con una mail a chi crea la ricorsione", 'required'=>'required'));
		
	?>	
	
	</fieldset>
<?php 
echo $this->Form->hidden('action_post',array('id' => 'action_post','value' => 'action_preview'));

echo $this->Form->submit(__('Salvo la nuova consegna'),array('id' => 'action_submit', 'div'=> 'submitMultiple', 'style' => 'display:none;'));
echo $this->Form->submit(__('Preview della nuova consegna'),array('id' => 'action_preview', 'div'=> 'submitMultiple','class' => 'buttonBlu'));

echo $this->Form->end();
?>
	
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List LoopsDeliveries'), array('action' => 'index'),array('class'=>'action actionReload'));?></li>
	</ul>
</div>

<script type="text/javascript">
jQuery(document).ready(function() {

	jQuery('.type_month1').attr('disabled',true);
	jQuery('.type_month1').css('opacity','0.3');
	
	jQuery('.type_month2').attr('disabled',true);
	jQuery('.type_month2').css('opacity','0.3');
	
	jQuery("input[name='data[LoopsDelivery][type]']").change(function() {
		var type = jQuery("input[name='data[LoopsDelivery][type]']:checked").val();

		if(type=='WEEK') {
			jQuery('#type_week').show();
			jQuery('#type_month').hide();
		}
		else
		if(type=='MONTH') {
			jQuery('#type_week').hide();
			jQuery('#type_month').show();		
		}			
	});
				
	jQuery("input[name='data[LoopsDelivery][type_month]']").change(function() {
		var type_month = jQuery("input[name='data[LoopsDelivery][type_month]']:checked").val();

		if(type_month=='MONTH1') {
			jQuery('.type_month1').attr('disabled',false);
			jQuery('.type_month1').css('opacity','1');
			
			jQuery('.type_month2').attr('disabled',true);
			jQuery('.type_month2').css('opacity','0.3');
		}
		else
		if(type_month=='MONTH2') {
			jQuery('.type_month1').attr('disabled',true);
			jQuery('.type_month1').css('opacity','0.3');
			
			jQuery('.type_month2').attr('disabled',false);
			jQuery('.type_month2').css('opacity','1');		
		}			
	});

	<?php 
	if($this->request->data['LoopsDelivery']['type']=='WEEK') {
	?>
		jQuery('#type_week').show();
		jQuery('#type_month').hide();
	<?php 
	}else 
	if($this->request->data['LoopsDelivery']['type']=='MONTH') {	
	?>
		jQuery('#type_week').hide();
		jQuery('#type_month').show();
	<?php 
	}
	?>

	<?php 
	if($this->request->data['LoopsDelivery']['type_month']=='MONTH1') {
	?>
		jQuery('.type_month1').attr('disabled',false);
		jQuery('.type_month1').css('opacity','1');
		
		jQuery('.type_month2').attr('disabled',true);
		jQuery('.type_month2').css('opacity','0.3');
	<?php 
	} else {
	?>
		jQuery('.type_month1').attr('disabled',true);
		jQuery('.type_month1').css('opacity','0.3');
		
		jQuery('.type_month2').attr('disabled',false);
		jQuery('.type_month2').css('opacity','1');	
	<?php 
	}
	?>

	jQuery('#action_submit').click(function() {	
		jQuery('#action_post').val('action_submit');
	});
	jQuery('#action_preview').click(function() {	
		jQuery('#action_post').val('action_preview');
	});
	
	jQuery('#formGas').submit(function() {
			return ctrlValidateForm();
	});	

	<?php 
	if($action_submit=='hidden') 
		echo "jQuery('#action_submit').hide();";
	else 
		echo "jQuery('#action_submit').show();";
	?>		


	jQuery('#DeliveryNotaEvidenzaImg').addClass("nota_evidenza_<?php echo strtolower($this->Form->value('LoopsDelivery.nota_evidenza'));?>");
	
	jQuery('#DeliveryNotaEvidenza').change(function() {
		var deliveryNotaEvidenza = jQuery(this).val();
		setNotaEvidenza(deliveryNotaEvidenza);
	});
	
	<?php
	if(!empty($nota_evidenzaDefault)) 
		echo 'setNotaEvidenza(\''.$nota_evidenzaDefault.'\');';
	?>
});

function ctrlValidateForm() {
	var type = jQuery("input[name='data[LoopsDelivery][type]']:checked").val();
	if(type==undefined) {
		alert("Indica la tipologia di ricorsione: settimanale o mensile?");
		return false;
	}

	if(type=="WEEK") {
		var week_every_week = jQuery("input[name='data[LoopsDelivery][week_every_week]']").val();
		if(week_every_week=="") {
			alert("Indica ogni quanto ricorre.");
			jQuery("input[name='data[LoopsDelivery][week_every_week]']").focus();
			return false;
		}	
		if(!isNumber(week_every_week)) {
			alert("Il valore che indica ogni quanto ricorre dev'essere un valore numerico.");
			jQuery("input[name='data[LoopsDelivery][week_every_week]']").focus();
			return false;
		}		
	}
	else
	if(type=="MONTH") {
		var type_month = jQuery("input[name='data[LoopsDelivery][type_month]']:checked").val();
		if(type_month==undefined) {
			alert("Indica la tipologia di ricorsione mensile");
			return false;
		}

		if(type=="MONTH1") {
			var month1_day = jQuery("input[name='data[LoopsDelivery][month1_day]']").val();
			if(month1_day=="") {
				alert("Indica ogni quanto deve ricorrere.");
				jQuery("input[name='data[LoopsDelivery][month1_day]']").focus();
				return false;
			}	
			if(!isNumber(month1_day)) {
				alert("Il valore che indica ogni quanto ricorre dev'essere un valore numerico.");
				jQuery("input[name='data[LoopsDelivery][month1_day]']").focus();
				return false;
			}
						
			var month1_every_month = jQuery("input[name='data[LoopsDelivery][month1_every_month]']").val();
			if(month1_every_month=="") {
				alert("Indica quanti mesi deve ricorrere.");
				jQuery("input[name='data[LoopsDelivery][month1_every_month]']").focus();
				return false;
			}	
			if(!isNumber(month1_every_month)) {
				alert("Il valore che indica ogni quanti mese dev'essere un valore numerico.");
				jQuery("input[name='data[LoopsDelivery][month1_every_month]']").focus();
				return false;
			}			
		}
		else
		if(type=="MONTH2") {
			var month2_every_type = jQuery("input[name='data[LoopsDelivery][month2_every_type]']:selected").val();
			if(month2_every_type==undefined) {
				alert("Indica ogni quanto deve ricorrere.");
				return false;
			}

			var month2_day_week = jQuery("input[name='data[LoopsDelivery][month2_day_week]']:checked").val();
			if(month2_day_week==undefined) {
				alert("Indica ogni quanto deve ricorrere.");
				return false;
			}

			var month2_every_month = jQuery("input[name='data[LoopsDelivery][month2_every_month]']").val();
			if(month2_every_month=="") {
				alert("Indica quanti mesi deve ricorrere.");
				jQuery("input[name='data[LoopsDelivery][month2_every_month]']").focus();
				return false;
			}	
			if(!isNumber(month2_every_month)) {
				alert("Il valore che indica ogni quanti mese dev'essere un valore numerico.");
				jQuery("input[name='data[LoopsDelivery][month2_every_month]']").focus();
				return false;
			}			
		}
	}
	
	var luogo = jQuery("input[name='data[LoopsDelivery][luogo]']").val();
	if(luogo=="") {
		alert("Indica il luogo della consegna.");
		jQuery("input[name='data[LoopsDelivery][luogo]']").focus();
		return false;
	}
		
	return true;
}
function setNotaEvidenza(deliveryNotaEvidenza) {
	jQuery('#DeliveryNotaEvidenzaImg').removeClass();
	jQuery('#DeliveryNotaEvidenzaImg').addClass("nota_evidenza_"+deliveryNotaEvidenza.toLowerCase());
}
</script>