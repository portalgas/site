<table>	
	<tr>
		<th></th>
		<th>Ruolo</th>
		<th>Descrizione</th>
		<th>Abilitazione del modulo</th>
		<th>Gruppo Joomla</th>
	</tr>			
	<tr id="tr_group_id_manager">
		<td><input type="radio" name="group_id_manager" value="Y" checked="checked" /><label for="" style="width:80px !important;margin-left:10px;">Si</label></td>
		<td>Manager</td>
		<td><?php echo __('HasUserGroupsManager');?></td>
		<td></td>
		<td>gasManager (<?php echo Configure::read('group_id_manager');?>)</td>
	</tr>
	<tr id="tr_group_id_manager_delivery">
		<td><input type="radio" name="group_id_manager_delivery" value="Y" checked="checked" /><label for="" style="width:80px !important;margin-left:10px;">Si</label></td>
		<td>Manager Consegne</td>
		<td>Gestisce le <b>consegne</b></td>
		<td></td>
		<td>gasManagerDelivery (<?php echo Configure::read('group_id_manager_delivery');?>)</td>
	</tr>			
	<tr id="tr_group_id_referent">
		<td><input type="radio" name="group_id_referent" value="Y" checked="checked" /><label for="" style="width:80px !important;margin-left:10px;">Si</label></td>
		<td>Referente</td>
		<td>Referente dei produttori associati</td>
		<td></td>
		<td>gasReferente (<?php echo Configure::read('group_id_referent');?>)</td>
	</tr>
	<tr id="tr_group_id_super_referent">
		<td><input type="radio" name="group_id_super_referent" value="Y" checked="checked" /><label for="" style="width:80px !important;margin-left:10px;">Si</label></td>
		<td>Super-Referente</td>
		<td>Può accedere a tutti i produttori</td>
		<td></td>
		<td>gasSuperReferente (<?php echo Configure::read('group_id_super_referent');?>)</td>
	</tr>		
	<tr id="tr_group_id_cassiere">
		<td>
			<?php 
			echo $this->App->drawFormRadio('Organization','hasUserGroupsCassiere',array('options' => $hasUserGroupsCassiere, 'value' => $this->request->data['Organization']['hasUserGroupsCassiere'], 'label' => false, 'required'=> false, 'label_style' => 'width:20px !important;margin-left:1px;'));
			?>
		</td>
		<td>Cassiere</td>
		<td><?php echo __('toolTipHasUserGroupsCassiere');?></td>
		<td>Pagamento <b>alla</b> consegna</td>
		<td>gasCassiere (<?php echo Configure::read('group_id_cassiere');?>)</td>
	</tr>					
	<tr id="tr_group_id_referent_tesoriere">
		<td>
			<?php 
			echo $this->App->drawFormRadio('Organization','hasUserGroupsReferentTesoriere',array('options' => $hasUserGroupsReferentTesoriere, 'value' => $this->request->data['Organization']['hasUserGroupsReferentTesoriere'], 'label' => false, 'required' => false, 'label_style' => 'width:20px !important;margin-left:1px;'));
			?>
		</td>
		<td>Referente-Tesoriere</td>
		<td><?php echo __('toolTipHasUserGroupsReferentTesoriere');?></td>
		<td>Pagamento con richiesta <b>dopo</b> la consegna</td>
		<td>gasReferentTesoriere (<?php echo Configure::read('group_id_referent_tesoriere');?>)</td>
	</tr>
	<tr id="tr_group_id_tesoriere">
		<td>
			<?php 
			echo $this->App->drawFormRadio('Organization','hasUserGroupsTesoriere',array('options' => $hasUserGroupsTesoriere, 'value' => $this->request->data['Organization']['hasUserGroupsTesoriere'], 'label' => false, 'required' => false, 'label_style' => 'width:20px !important;margin-left:1px;'));
			?>						
		</td>
		<td>Tesoriere</td>
		<td><?php echo __('toolTipHasUserGroupsTesoriere');?></td>
		<td>Gestore dei pagamenti ai <b>fornitori</b></td>
		<td>gasTesoriere (<?php echo Configure::read('group_id_tesoriere');?>)</td>
	</tr>
	<tr id="tr_group_id_storeroom">
		<td>
			<?php 
			echo $this->App->drawFormRadio('Organization','hasUserGroupsStoreroom',array('options' => $hasUserGroupsStoreroom, 'value' => $this->request->data['Organization']['hasUserGroupsStoreroom'], 'label' => false, 'required' => false, 'label_style' => 'width:20px !important;margin-left:1px;'));
			?>	
		</td>		
		<td>Dispensa</td>
		<td>Gestore della <b>dispensa</b></td>
		<td>Dispensa</td>
		<td>gasDispensa (<?php echo Configure::read('group_id_storeroom');?>9)</td>
	</tr>
	<tr>
		<td><input type="radio" name="group_id_user" value="Y" checked="checked" /><label for="" style="width:80px !important;margin-left:10px;">Si</label></td>
		<td>Utenti</td>
		<td>Utente registrato</td>
		<td>Lato Front-end</td>
		<td>Registered (<?php echo Configure::read('group_id_user');?>)</td>
	</tr>
</table>