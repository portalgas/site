<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Mails'),array('controller'=>'Mails','action'=>'root_index'));
$this->Html->addCrumb(__('Send Mail'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="mails">';
echo '<h2 class="ico-mails">';
echo __('Send Mail');
echo '<div class="actions-img">';
echo '<ul>';
echo '<li>'.$this->Html->link(__('List Mails'), ['action' => 'root_index'], ['class' => 'action actionConfig','title' => __('List Mails')]).'</li>';
echo '</ul>';
echo '</div>';
echo '</h2>';


echo $this->Form->create('Mail', ['id'=>'formGas','enctype' => 'multipart/form-data']);

echo '<fieldset>';
echo '<legend>'.__('Send Mail').'</legend>';
	
		$i=0;
		echo $this->Form->input('mittenti', array('options' => $mittenti, 'value' => Configure::read('Mail.no_reply_mail'), 'label'=>__('A chi rispondere'),'tabindex'=>($i+1)));

		echo $this->App->drawFormRadio('Mail','dest_options',array('options' => $dest_options, 'value'=>'SUPPLIERS', 'name' => 'dest-options', 'label' => __('A chi inviarla'),'tabindex'=>($i+1)));
		
		echo '<div id="dest_options_qta_supplier">';
		echo $this->App->drawFormRadio('Mail','dest_options_qta_supplier',array('options' => $dest_options_qta_supplier, 'value'=>'ALL', 'name' => 'dest-options-qta-supplier', 'label' => __('A quanti'),'tabindex'=>($i+1)));
		echo '</div>';

		echo '<div id="dest_options_qta_gas">';
		echo $this->App->drawFormRadio('Mail','dest_options_qta_gas',array('options' => $dest_options_qta_gas, 'value'=>'ALL', 'name' => 'dest-options-qta-gas', 'label' => __('A quanti'),'tabindex'=>($i+1)));
		echo '</div>';
	
		/*
		 * organizations GAS
		 */
		echo '<div id="gas" style="display:block;">';
		$label = __('GasOrganizations').' '.__('GasOrganizations').'&nbsp;('.count($organizationResults).')';
		echo $this->Form->input('gas',array('label' => $label,'options' => $organizationResults,'escape' => false,'multiple' => true));
		echo '</div>';
		
		/*
		 * organizations PRODGAS
		 */		
		echo '<div id="prodgas" style="display:block;">';
		$label = __('ProdGasOrganization').' '.__('ProdGasOrganizations').'&nbsp;('.count($organizationProdGasResults).')';
		echo $this->Form->input('prodgas',array('label' => $label, 'options' => $organizationProdGasResults,'escape' => false,'multiple' => true));
		echo '</div>';
		
		/*
		 * produttori
		 */
		echo '<div id="suppliersorganization" style="display:block;">';
		$label = __('SuppliersOrganization').'&nbsp;('.count($ACLsuppliersOrganization).')';
		echo $this->Form->input('suppliers',array('label' => $label,'options' => $ACLsuppliersOrganization,'escape' => false,'multiple' => true));
		echo '</div>';

        /*
         * single
         */
        echo '<div id="single" style="display:block;">';
        echo $this->Form->input('single', ['type' => 'email', 'label' => __('Email')]);
        echo '</div>';

		echo $this->Form->input('subject');
		
		echo '<div class="clearfix"></div>';
		echo '<div class="input text"><label></label> ';
		echo $body_header_mittente; 
		
		echo $this->Form->textarea('body', array('rows' => '15', 'cols' => '75'));
		
		echo '<div class="clearfix"></div>';
		echo '<div class="input text"><label>Piè di pagina</label> ';
		
		echo '<textarea cols="85%" rows="4" class="noeditor" disabled="true" id="body_footer_no_reply" style="display:inline;">'.str_replace('<br />', '', $body_footer_no_reply).'</textarea>';
		
		echo '</div>';		
		
		echo '<div class="clearfix"></div>';
		echo $this->Form->input('Document.img1', ['label' => 'Allegato',
												'between' => '<br />',
												'type' => 'file']);	
		
		echo '</fieldset>';
		
		echo $this->Form->end(__('Send'));
		?>
</div>


<script type="text/javascript">
$(document).ready(function() {

	$('#MailMittenti').change(function() {
		var mittenti = $('#MailMittenti').val();	
		
		if(mittenti=='<?php echo Configure::read('Mail.no_reply_mail');?>') {
			$('#body_footer_no_reply').show();
			$('#body_footer').hide();
		}	
		else {
			$('#body_footer_no_reply').hide();
			$('#body_footer').show();
		}	
	});
	
	$("input[name='data[Mail][dest_options]']").change(function() {
		choiceDestOptions();
	});

	$("input[name='data[Mail][dest_options_qta_supplier]']").change(function() {
		choiceDestOptions();
	});
	
	$("input[name='data[Mail][dest_options_qta_gas]']").change(function() {
		choiceDestOptions();
	});
	
	choiceDestOptions();

	$('#formGas').submit(function() {

		if(dest_options=='GAS' || dest_options=='PRODGAS') {
			var dest_options_qta_gas = $("input[name='data[Mail][dest_options_qta_gas]']:checked").val();
		}
		else	
		if(dest_options=='SUPPLIERS') {
			var dest_options_qta_supplier = $("input[name='data[Mail][dest_options_qta_supplier]']:checked").val();
			if(dest_options_qta_supplier=='SOME') {
				var dest_options = $("input[name='data[Mail][dest_options]']:checked").val();
				
				var destinatariScelti = null;
				destinatariScelti = $("#MailSuppliers").val();
		
				if(destinatariScelti==null) {
					alert("Devi scegliere almeno un destinatario");
					return false;
				}			
			}
		
		}

		var subject = $('#MailSubject').val();
		if(subject=="") {
			alert("Devi indicare il soggetto della mail");
			return false;
		}
	
		var body = $('#MailBody').val();
		if(body=="") {
			alert("Devi indicare il testo della mail");
			return false;
		}
	
		alert("Verrà inviata la mail, attendere che venga terminata l'esecuzione");
	
		$("input[type=submit]").attr('disabled', 'disabled');
		$("input[type=submit]").css('background-image', '-moz-linear-gradient(center top , #ccc, #dedede)');
		$("input[type=submit]").css('box-shadow', 'none');

		return true;
	});	
});

function choiceDestOptions() {
	var dest_options = $("input[name='data[Mail][dest_options]']:checked").val();
	var dest_options_qta_supplier = $("input[name='data[Mail][dest_options_qta_supplier]']:checked").val();
	var dest_options_qta_gas = $("input[name='data[Mail][dest_options_qta_gas]']:checked").val();

	$('#dest_options_qta_supplier').hide();
    $('#dest_options_qta_gas').hide();
	$('#Maildest_options_qta_supplierALL').attr('disabled',false);
	$('#Maildest_options_qta_supplierSOME').attr('disabled',false);
	$('#Maildest_options_qta_gasALL').attr('disabled',false);
	$('#Maildest_options_qta_gasSOME').attr('disabled',false);

	if(dest_options=='GAS') {
		$('#gas').css('display','block');
		$('#prodgas').css('display','none');
		$('#suppliersorganization').css('display','none');
        $('#single').css('display','none');
		
		$('#dest_options_qta_supplier').hide();
		$('#dest_options_qta_gas').show();			
		$('#Maildest_options_qta_gasSOME').attr('disabled',false);
		
		if(dest_options_qta_gas=='ALL') {
			$('#gas').css('display','none');
			$('#prodgas').css('display','none');
			$('#suppliersorganization').css('display','none');
            $('#single').css('display','none');
		}			
	}
	else	
	if(dest_options=='PRODGAS') {
		$('#prodgas').css('display','block');
		$('#gas').css('display','none');
		$('#suppliersorganization').css('display','none');
        $('#single').css('display','none');
		
		$('#dest_options_qta_supplier').hide();
		$('#dest_options_qta_gas').show();			
		$('#Maildest_options_qta_gasSOME').attr('disabled',false);
		
		if(dest_options_qta_gas=='ALL') {
			$('#gas').css('display','none');
			$('#prodgas').css('display','none');
			$('#suppliersorganization').css('display','none');
            $('#single').css('display','none');
		}			
	}
    else
    if(dest_options=='SUPPLIERS') {
        $('#gas').css('display','none');
        $('#prodgas').css('display','none');
        $('#suppliersorganization').css('display','block');
        $('#single').css('display','none');

        $('#dest_options_qta_supplier').show();
        $('#dest_options_qta_gas').hide();
        $('#Maildest_options_qta_supplierSOME').attr('disabled',false);

        if(dest_options_qta_supplier=='ALL') {
            $('#gas').css('display','none');
            $('#prodgas').css('display','none');
            $('#suppliersorganization').css('display','none');
            $('#single').css('display','none');
        }
    }
    else
    if(dest_options=='SINGLE') {
        $('#gas').css('display','none');
        $('#prodgas').css('display','none');
        $('#suppliersorganization').css('display','none');
        $('#single').css('display','block');

        $('#dest_options_qta_supplier').show();
        $('#dest_options_qta_gas').hide();
        $('#Maildest_options_qta_supplierSOME').attr('disabled',false);

        $('#dest_options_qta_supplier').css('display','none');
    }
}
</script>