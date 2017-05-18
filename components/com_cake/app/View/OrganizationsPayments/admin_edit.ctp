<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('View Organization'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="organizations">';

echo $this->Form->create('OrganizationsPayment',array('id' => 'formGas'));
?>
	<fieldset>
		<legend><?php echo __('View Organization'); ?></legend>
		
         <div class="tabs">
             <ul>
                 <li><a href="#tabs-0"><span><?php echo __('Dati per il pagamento'); ?></span></a></li>
                 <li><a href="#tabs-1"><span><?php echo __('Dati anagrafici'); ?></span></a></li>
                 <li><a href="#tabs-2"><span><?php echo __('Bilancio PortAlGas'); ?></span></a></li>
                 <li><a href="#tabs-3"><span><?php echo __('Users'); ?></span></a></li>
             </ul>
			<div id="tabs-0">
				<?php
					echo $this->Form->input('payContatto', array('id' => 'payContatto', 'label' => __('payContatto')));
					echo $this->Form->input('payMail', array('id' => 'payMail', 'label' => __('payMail')));
					echo $this->Form->input('payIntestatario', array('id' => 'payIntestatario', 'label' => __('payIntestatario')));
					echo $this->Form->input('payIndirizzo', array('id' => 'payIndirizzo', 'label' => __('payIndirizzo')));
					echo $this->Form->input('payCap', array('id' => 'payCap', 'label' => __('payCap')));
					echo $this->Form->input('payCitta', array('id' => 'payCitta', 'label' => __('payCitta')));
					echo $this->Form->input('payProv', array('id' => 'payProv', 'label' => __('payProv')));
					echo $this->Form->input('payCf', array('id' => 'payCf', 'label' => __('payCf')));
					echo $this->Form->input('payPiva', array('id' => 'payPiva', 'label' => __('payPiva')));
				?>		
			</div>			 
            <div id="tabs-1">		
				<?php
					echo $this->Form->input('indirizzo', array('id' => __('indirizzo')));
					echo $this->Form->input('telefono');
					echo $this->Form->input('telefono2');
					echo $this->Form->input('mail', array('id' => __('mail')));
					echo $this->Form->input('www2', array('label' => 'Www'));

					echo '<hr />';
				
					echo $this->Form->input('cf');
					echo $this->Form->input('piva');
					echo $this->Form->input('banca');				
					echo $this->Form->input('banca_iban');
				?>
			</div>
			<div id="tabs-2">
				<?php
				echo $table_plan->introtext;
				?>
			</div>
			<div id="tabs-3">

								
						<table cellpadding="0" cellspacing="0">
						<tr>
								<th><?php echo __('N');?></th>
								<th>Codice</th>
								<th></th>
								<th><?php echo __('Nominativo');?></th>
								<th><?php echo __('Username');?></th>
								<th><?php echo __('Mail');?></th>
								<th><?php echo __('registerDate', __('Registrato il'));?></th>
								<th><?php echo __('lastvisitDate', __('Ultima visita'));?></th>								
								<th><?php echo __('stato',__('Stato'));?></th>
						<?php
						echo '</tr>';
						
						foreach ($this->request->data['User'] as $i => $result):
				
							if(!empty($result['lastvisitDate']) && $result['lastvisitDate']!='0000-00-00 00:00:00') 
								$lastvisitDate = $this->Time->i18nFormat($result['lastvisitDate'],"%e %b %Y");
							else 
								$lastvisitDate = "";
							?>
						<tr class="view">
							<td><?php echo ($i+1);?></td>
							<td><?php echo $result['Profile']['codice']; ?></td>
							<td><?php echo $this->App->drawUserAvatar($user, $result['id'], $result); ?></td>
							<td><?php echo $result['name']; ?></td>
							<td><?php echo $result['username']; ?></td>
							<td><?php  	
								if(!empty($result['email'])) echo '<a title="'.__('Email send').'" target="_blank" href="mailto:'.$result['email'].'">'.$result['email'].'</a><br />';
							echo '</td>';
							?>
							<td><?php echo $this->Time->i18nFormat($result['registerDate'],"%e %b %Y");?></td>
							<td><?php echo $lastvisitDate;?></td>
							<td title="<?php echo __('toolTipStato');?>" class="stato_<?php echo $this->App->traslateEnum($result['block']); ?>"></td>		
						</tr>
					<?php endforeach; ?>
						</table>			
			
			
			</div>
		</div>
	</fieldset>
<?php 
echo $this->Form->end(__('Submit'));
?>
</div>

<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery(function() {
		jQuery( ".tabs" ).tabs({
			event: "click"
		});
	});

	jQuery('#formGas').submit(function() {
		
		var payContatto = jQuery('#payContatto').val();
		if(payContatto=='') {
			jQuery('.tabs').tabs('option', 'active',0);
			alert("Devi indicare il Nominativo per il pagamento");
			jQuery('#payContatto').focus();
			return false;
		}		
		var payMail = jQuery('#payMail').val();
		if(payMail=='') {
			jQuery('.tabs').tabs('option', 'active',0);
			alert("Devi indicare la Mail al quale sarà inviato il documento di pagamento");
			jQuery('#payMail').focus();
			return false;
		}
		var payIntestatario = jQuery('#payIntestatario').val();
		if(payIntestatario=='') {
			jQuery('.tabs').tabs('option', 'active',0);
			alert("Devi indicare l'Intestatario del documento di pagamento");
			jQuery('#payIntestatario').focus();
			return false;
		}
		var payIndirizzo = jQuery('#payIndirizzo').val();
		if(payIndirizzo=='') {
			jQuery('.tabs').tabs('option', 'active',0);
			alert("Devi indicare l'Indirizzo del documento di pagamento");
			jQuery('#payIndirizzo').focus();
			return false;
		}
		var payCap = jQuery('#payCap').val();
		if(payCap=='') {
			jQuery('.tabs').tabs('option', 'active',0);
			alert("Devi indicare il CAP");
			jQuery('#payCap').focus();
			return false;
		}
		var payCitta = jQuery('#payCitta').val();
		if(payCitta=='') {
			jQuery('.tabs').tabs('option', 'active',0);
			alert("Devi indicare la città");
			jQuery('#payCitta').focus();
			return false;
		}	
		var payProv = jQuery('#payProv').val();
		if(payProv=='') {
			jQuery('.tabs').tabs('option', 'active',0);
			alert("Devi indicare la provincia");
			jQuery('#payProv').focus();
			return false;
		}	
		var payCf = jQuery('#payCf').val();
		if(payCf=='') {
			jQuery('.tabs').tabs('option', 'active',0);
			alert("Devi indicare il Codice Fiscale");
			jQuery('#payCf').focus();
			return false;
		}	
		var indirizzo = jQuery('#indirizzo').val();
		if(indirizzo=='') {
			jQuery('.tabs').tabs('option', 'active',1);
			alert("Devi indicare l'indirizzo del proprio G.A.S.");
			jQuery('#indirizzo').focus();
			return false;
		}	
		var mail = jQuery('#mail').val();
		if(mail=='') {
			jQuery('.tabs').tabs('option', 'active',1);
			alert("Devi indicare la mail del proprio G.A.S.");
			jQuery('#mail').focus();
			return false;
		}	
			
		return true;
	});	
});
</script>