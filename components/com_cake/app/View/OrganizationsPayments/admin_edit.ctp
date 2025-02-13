<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('View Organization'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="organizations">';

echo $this->Form->create('OrganizationsPayment',array('id' => 'formGas'));

echo '<fieldset>';
echo '<legend>'.__('View Organization').'</legend>';
 
echo '<div class="tabs">';
echo '<ul class="nav nav-tabs">'; // nav-tabs nav-pills
echo '<li class="active"><a href="#tabs-0" data-toggle="tab">'.__('Payment profile').'</a></li>';
echo '<li><a href="#tabs-gas-config" data-toggle="tab">'.__('GasConfigure').'</a></li>';
echo '<li><a href="#tabs-1" data-toggle="tab">'.__('User profile').'</a></li>';
echo '<li><a href="#tabs-2" data-toggle="tab">'.__('PortAlGasBilancio').'</a></li>';
echo '<li><a href="#tabs-3" data-toggle="tab">'.__('Users').'</a></li>';
echo '</ul>';

echo '<div class="tab-content">';

echo '<div class="tab-pane fade active in" id="tabs-0">';

// pdf
if(!empty($pdf_url)) {
	echo '<p>
	<a href="'.$pdf_url.'" target="_blank" title="scarica documento di spesa">
		<img alt="PDF" src="/images/cake/minetypes/32x32/pdf.png"></a>
	Scarica	'.$pdf_label.'
		</p>';
}

echo $this->Form->input('payContatto', array('id' => 'payContatto', 'label' => __('payContatto')));
echo $this->Form->input('payMail', array('id' => 'payMail', 'label' => __('payMail')));
echo $this->Form->input('payIntestatario', array('id' => 'payIntestatario', 'label' => __('payIntestatario')));
echo $this->Form->input('payIndirizzo', array('id' => 'payIndirizzo', 'label' => __('payIndirizzo')));
echo $this->Form->input('payCap', array('id' => 'payCap', 'label' => __('payCap')));
echo $this->Form->input('payCitta', array('id' => 'payCitta', 'label' => __('payCitta')));
echo $this->Form->input('payProv', array('id' => 'payProv', 'label' => __('payProv')));
echo $this->Form->input('payCf', array('id' => 'payCf', 'label' => __('payCf')));
echo $this->Form->input('payPiva', array('id' => 'payPiva', 'label' => __('payPiva')));
echo $this->Form->input('payType', array('id' => 'payType', 'label' => __('payType'), 'options' => ['RICEVUTA', 'RITENUTA']));

echo $this->element('boxMsg', array('class_msg' => 'message', 'msg' => "Se avete bisogno di una ritenuta di pagamento, l'anno successivo dovrete versare tramite F24 la ritenuta del 20%"));
echo '</div>'; 

echo '<div class="tab-pane fade" id="tabs-gas-config">';
echo '<div class="table-responsive"><table class="table table-hover">';
echo '<tr>';
echo '<th>'.__('PayToDelivery').'</th>';
echo '<th>'.__('OrdersCycleLifeToCLOSE').'</th>';
echo '</tr>';				
foreach($templateResults as $templateResult) {

	if($this->request->data['Template']['id']==$templateResult['Template']['id'])
		$css = 'background-color:yellow';
	else	
		$css = '';
		 
	echo '<tr style="'.$css.'">';
	echo '<td>'.__('PayToDelivery-'.$templateResult['Template']['payToDelivery']).'</td>';
	echo '<td>'.$templateResult['Template']['descri_order_cycle_life'].'</td>';
	echo '</tr>';	
}
echo '</table>';
echo '</div>';
echo '</div>';

echo '<div class="tab-pane fade" id="tabs-1">';
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
echo '</div>'; 
echo '<div class="tab-pane fade" id="tabs-2">';
echo $table_plan->intro_text;
echo '</div>'; 
echo '<div class="tab-pane fade" id="tabs-3">';
?>
				
		<div class="table-responsive"><table class="table table-hover">
		<tr>
				<th><?php echo __('N');?></th>
				<th><?php echo __('Code');?></th>
				<th></th>
				<th><?php echo __('Nominative');?></th>
				<th><?php echo __('Username');?></th>
				<th><?php echo __('Mail');?></th>
				<th><?php echo __('registerDate', __('registerDate'));?></th>
				<th><?php echo __('lastvisitDate', __('LastvisitDateo'));?></th>								
				<th><?php echo __('stato',__('Stato'));?></th>
		<?php
		echo '</tr>';
		
		foreach ($this->request->data['User'] as $numResult => $result) {

			if(!empty($result['lastvisitDate']) && $result['lastvisitDate']!=Configure::read('DB.field.datetime.empty')) 
				$lastvisitDate = $this->Time->i18nFormat($result['lastvisitDate'],"%e %b %Y");
			else 
				$lastvisitDate = "";
			
			echo '<tr class="view">';
			?>
			<td><?php echo ((int)$numResult+1);?></td>
			<td><?php echo $result['Profile']['codice']; ?></td>
			<td><?php echo $this->App->drawUserAvatar($user, $result['id'], $result); ?></td>
			<td><?php echo $result['name']; ?></td>
			<td><?php echo $result['username']; ?></td>
			<td><?php  	
				if(!empty($result['email'])) echo '<a title="'.__('Email send').'" target="_blank" href="mailto:'.$result['email'].'">'.$result['email'].'</a><br />';
			echo '</td>';
			echo '<td>'.$this->Time->i18nFormat($result['registerDate'],"%e %b %Y").'</td>';
			echo '<td>'.$lastvisitDate.'</td>';
			echo '<td title="'.__('toolTipStato').'" class="stato_'.$this->App->traslateEnum($result['block']).'"></td>';
			echo '</tr>';
	} // end loop
echo '</table></div>';
		
echo '</div>'; 
echo '</div>'; // tabs
echo '</div>'; // tab-content
echo '</fieldset>'; 
echo $this->Form->end(__('Submit'));
echo '</div>'; 
?>

<script type="text/javascript">
$(document).ready(function() {

	$('#formGas').submit(function() {
		
		var payContatto = $('#payContatto').val();
		if(payContatto=='') {
			$('.nav-tabs a[href="#tabs-0"]').tab('show');
			alert("Devi indicare il Nominativo per il pagamento");
			$('#payContatto').focus();
			return false;
		}		
		var payMail = $('#payMail').val();
		if(payMail=='') {
			$('.nav-tabs a[href="#tabs-0"]').tab('show');
			alert("Devi indicare la Mail al quale sarà inviato il documento di pagamento");
			$('#payMail').focus();
			return false;
		}
		var payIntestatario = $('#payIntestatario').val();
		if(payIntestatario=='') {
			$('.nav-tabs a[href="#tabs-0"]').tab('show');
			alert("Devi indicare l'Intestatario del documento di pagamento");
			$('#payIntestatario').focus();
			return false;
		}
		var payIndirizzo = $('#payIndirizzo').val();
		if(payIndirizzo=='') {
			$('.nav-tabs a[href="#tabs-0"]').tab('show');
			alert("Devi indicare l'Indirizzo del documento di pagamento");
			$('#payIndirizzo').focus();
			return false;
		}
		var payCap = $('#payCap').val();
		if(payCap=='') {
			$('.nav-tabs a[href="#tabs-0"]').tab('show');
			alert("Devi indicare il CAP");
			$('#payCap').focus();
			return false;
		}
		var payCitta = $('#payCitta').val();
		if(payCitta=='') {
			$('.nav-tabs a[href="#tabs-0"]').tab('show');
			alert("Devi indicare la città");
			$('#payCitta').focus();
			return false;
		}	
		var payProv = $('#payProv').val();
		if(payProv=='') {
			$('.nav-tabs a[href="#tabs-0"]').tab('show');
			alert("Devi indicare la provincia");
			$('#payProv').focus();
			return false;
		}	
		var payCf = $('#payCf').val();
		if(payCf=='') {
			$('.nav-tabs a[href="#tabs-0"]').tab('show');
			alert("Devi indicare il Codice Fiscale");
			$('#payCf').focus();
			return false;
		}	
		var indirizzo = $('#indirizzo').val();
		if(indirizzo=='') {
			$('.nav-tabs a[href="#tabs-1"]').tab('show');
			alert("Devi indicare l'indirizzo del proprio G.A.S.");
			$('#indirizzo').focus();
			return false;
		}	
		var mail = $('#mail').val();
		if(mail=='') {
			$('.nav-tabs a[href="#tabs-1"]').tab('show');
			alert("Devi indicare la mail del proprio G.A.S.");
			$('#mail').focus();
			return false;
		}	
			
		return true;
	});	
});
</script>