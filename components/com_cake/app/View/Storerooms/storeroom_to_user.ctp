<div class="storeroom">
<?php echo $this->Form->create('Storeroom',array('id'=>'ajaxForm'));?>
	<fieldset>
		<legend><?php echo __('Add Storeroom to User'); ?></legend>

	<?php
	echo $this->Form->input('supplier',array('value' => $this->data['SuppliersOrganization']['name'], 'disabled' => 'true'));
	
	echo $this->Form->input('name',array('disabled' => 'true'));

	echo $this->Form->input('delivery_id',array('empty' => Configure::read('option.empty')));
	
	echo '<div class="input select">';

	echo "\r\n";
	echo '<table style="width: 77%; float: right;">';
	echo '<tr>';
	echo '<th>'.__('Conf').'</th>';
	echo '<th>'.__('PrezzoUnita').'</th>';
	echo '<th>'.__('Prezzo/UM').'</th>';
	echo '<th>'.__('Importo').'</th>';
	echo '</tr>';

	echo "\r\n";
	echo '<tr>';
	echo "\r\n";
	echo '<td>';
	echo $this->App->getArticleConf($this->data['Article']['qta'], $this->data['Article']['um']);
	echo '</td>';
	
	echo "\r\n";
	echo '<td>';
	echo $this->data['Storeroom']['prezzo_e'];
	echo '</td>';
	
	echo "\r\n";
	echo '<td>';
	echo $this->App->getArticlePrezzoUM($this->data['Storeroom']['prezzo'], $this->data['Article']['qta'], $this->data['Article']['um'], $this->data['Article']['um_riferimento']);
	echo '</td>';
	
	echo "\r\n";
	echo '<td>';
	
	$options['label'] = false; 
	$options['style'] = 'width:75px'; 
	$options['value'] = '0.00'; 
	$options['after'] = ' <span style="font-size:14px;">&euro;</span>'; 
	echo $this->Form->input('prezzoNew',$options);
	echo '</td>';
	echo '</tr>';
	echo '</table>';
	echo "\r\n";

	echo '</div>';

	echo $this->Form->input('qta', array('empty' => Configure::read('option.empty'), 
										 'label' => __('qta'),
										 'id' => 'qta',
										 'type' => 'select', 
										 'options' => array_combine(range(1, $this->data['Storeroom']['qta']),range(1, $this->data['Storeroom']['qta'])),
										 'selected'=>'',
										 'onChange' => 'javascript:setImportoAndQtaRestore(this);'));	

	echo "<span style='float: right;font-size: 15px;'>";
	echo "Rimane in dispensa la seguente quantit&agrave;:&nbsp;<span id='qtaRestore' class='qtaUno'>0</span></span>";

	echo $this->Form->hidden('id');	 	
	echo $this->Form->hidden('Prezzo');	
	echo $this->Form->hidden('order_id',array('value'=>0));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>

<script type="text/javascript">
function setImportoAndQtaRestore() {
	setImporto();
	setQtaRestore();
}

function setImporto() {
	var prezzo = '<?php echo $this->data['Storeroom']['prezzo']?>';
	var qta = jQuery("#qta").val();	
	
	prezzoNew = number_format(prezzo*qta,2,',','.');
	jQuery('#StoreroomPrezzoNew').val(prezzoNew);
}

function setQtaRestore() {
	var qta = jQuery("#qta").val();
	if(qta=="") qtaSelezionata = 0;
	else qtaSelezionata =qta;

	var qtaRestore = (parseInt(<?php echo $this->data['Storeroom']['qta'];?>) - parseInt(qtaSelezionata));
   jQuery("#qtaRestore").html(qtaRestore);	
}

jQuery(document).ready(function() {

	  setQtaRestore();

	  jQuery("#ajaxForm").submit(function() {

		var storeroomId = jQuery('#StoreroomId').val();
	    var deliveryId = jQuery("#StoreroomDeliveryId").val();
	    var qta = jQuery("#qta").val();
	    
	    if(deliveryId=="") {
		    alert("Devi indicare la consegna durante la quale ritirerai il prodotto");
		    return false;
		}
	    if(qta=="") {
		    alert("Devi indicare la quantità");
		    return false;
		}
		
	    jQuery.ajax({
	      type: "POST",
	      url: "/?option=com_cake&controller=Storerooms&action=storeroomToUser&id="+storeroomId+"&format=notmplt",
	      data: "id=" + storeroomId + "&delivery_id=" + deliveryId + "&qta=" + qta,
	      dataType: "html",
	      success: function(msg)
	      {
	    	    jQuery('#ajaxContent').animate({opacity:0});
	    	    var url = "/home-cavagnetta/storeroom?esito=OK&format=notmpl";
	    		jQuery('#ajaxContent').load(url);
	    		jQuery('#ajaxContent').animate({opacity:1},1500);
	      },
	      error: function()
	      {
	        alert("Chiamata fallita, si prega di riprovare...");
	      }
	    });

	    return false;
	  });
});
</script>
