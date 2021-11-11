<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Mails'),array('controller'=>'Mails','action'=>'root_index'));
$this->Html->addCrumb(__('Send Mail'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>
<div class="mails">
	<h2 class="ico-mails">
		<?php echo __('Send Mail');?>
	<div class="actions-img">
	<ul>
		<li><?php echo $this->Html->link(__('List Mails'), array('action' => 'prod_gas_supplier_index'),array('class' => 'action actionConfig','title' => __('List Mails'))); ?></li>
	</ul>
	</div>
	</h2>


<?php echo $this->Form->create('Mail',array('id'=>'formGas','enctype' => 'multipart/form-data'));?>

	<fieldset>
		<legend><?php echo __('Send Mail'); ?></legend>
	<?php
		$i=0;
		echo $this->Form->input('mittenti', array('options' => $mittenti, 'value' => Configure::read('Mail.no_reply_mail'), 'label'=>__('A chi rispondere'),'tabindex'=>($i+1)));

		/*
		 * organizations
		 */		
		echo $this->Form->drawFormCheckbox('Mail','organizations',array('options' => $organizationResults, 'selected'=> '', 'name' => 'organizationResults', 'label' => __('G.A.S.'),'tabindex'=>($i+1)));
	
		echo $this->Form->drawFormCheckbox('Mail','dest_options',array('options' => $dest_options, 'selected'=> '', 'name' => 'dest-options', 'label' => __('A chi inviarla'),'tabindex'=>($i+1)));
				
		echo $this->Form->input('subject');
		
		echo '<div class="clearfix"></div>';
		echo '<div class="input text"><label></label> ';
		echo $body_header_mittente; 
		
		echo $this->Form->textarea('body', array('rows' => '15', 'cols' => '75'));
		
		echo '<div class="clearfix"></div>';
		echo '<div class="input text"><label>Piè di pagina</label> ';
		
		echo '</div>';		
		
		echo '<div class="clearfix"></div>';
		echo $this->Form->input('Document.img1', array(
													'label' => 'Allegato',
												    'between' => '<br />',
												    'type' => 'file'
												));	
		
		echo '</fieldset>';
		
		echo $this->Form->end(__('Send'));
		?>
</div>

<script type="text/javascript">
$(document).ready(function() {

	$('#formGas').submit(function() {

  		if($("input[name='data[Mail][organizations]']:checked").length==0) {
			alert("Devi indicare almeno un GAS al quale inviare la mail");
			return false;  		
  		}

  		if($("input[name='data[Mail][dest_options]']:checked").length==0) {
			alert("Devi indicare a chi inviare la mail");
			return false;  		
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
</script>