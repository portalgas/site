<?php
$url = Configure::read('App.server')."/home-".$user->organization['Organization']['j_seo']."/dispensa-".$user->organization['Organization']['j_seo']."?esito=OK&format=notmpl";

echo '<div class="storeroom">';

echo $this->Form->create('Storeroom', ['id'=>'ajaxForm']);
echo '<fieldset>';
echo '<legend>'.__('Add Storeroom to User').'</legend>';

	echo $this->Form->input('supplier',array('value' => $this->data['SuppliersOrganization']['name'], 'disabled' => 'true'));
	
	echo $this->Form->input('name',array('disabled' => 'true'));

	echo $this->Form->input('delivery_id', ['empty' => Configure::read('option.empty')]);
	
	echo '<div class="input select">';

	echo "\r\n";
	echo '<table style="width: 77%; float: right;">';
	echo '<thead>';
	echo '<tr>';
	echo '<th>'.__('Conf').'</th>';
	echo '<th>'.__('PrezzoUnita').'</th>';
	echo '<th>'.__('Prezzo/UM').'</th>';
	echo '<th>'.__('Importo').'</th>';
	echo '<th style="padding-left:25px;">'.__('qta').'</th>';
	echo '</thead>';
	echo '</tr>';

	echo "\r\n";
	echo '<tr>';
	echo '<tbody>';
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
	echo '<td style="white-space: nowrap;">';
	$options = []; 
	$options['label'] = false; 
	$options['disabled'] = true; 
	$options['style'] = 'display:inline;'; 
	$options['value'] = $this->data['Storeroom']['prezzo_']; 
	$options['after'] = ' <span style="font-size:14px;">&euro;</span>'; 
	// debug($options);
	echo $this->Form->input('prezzoNew', $options);
	echo '</td>';
	
	echo '<td style="padding-left:25px;">';
	echo $this->Form->input('qta', ['empty' => Configure::read('option.empty'), 
									 'label' => false,
									 'id' => 'qta',
									 'type' => 'select', 
									 'options' => array_combine(range(1, $this->data['Storeroom']['qta']),range(1, $this->data['Storeroom']['qta'])),
									 'default'=> $this->data['Storeroom']['qta'],
									 'onChange' => 'javascript:setImportoAndQtaRestore(this);']);	
	echo '</td>';
	echo '</tr>';
	echo '</tbody>';
	echo '</table>';
	echo "\r\n";

	echo '</div>';



	echo "<span style='float: right;font-size: 15px;'>";
	echo "Rimane in dispensa la seguente quantit&agrave;:&nbsp;<span id='qtaRestore' class='qtaUno'>0</span></span>";

	echo $this->Form->hidden('id');	 	
	echo $this->Form->hidden('Prezzo');	
	echo $this->Form->hidden('order_id', ['value' => 0]);
	
	echo '</fieldset>';
	
	echo '<button type="submit" class="btn btn-success"><span>'.__('Submit').'</span></button>';

    echo $this->Form->end();
echo '</div>';
?>

<script type="text/javascript">
function setImportoAndQtaRestore() {
	setImporto();
	setQtaRestore();
}

function setImporto() {
	var prezzo = '<?php echo $this->data['Storeroom']['prezzo']?>';
	var qta = $("#qta").val();	
	
	prezzoNew = number_format(prezzo*qta,2,',','.');
	$('#StoreroomPrezzoNew').val(prezzoNew);
}

function setQtaRestore() {
	var qta = $("#qta").val();
	if(qta=="") qtaSelezionata = 0;
	else qtaSelezionata =qta;

	var qtaRestore = (parseInt(<?php echo $this->data['Storeroom']['qta'];?>) - parseInt(qtaSelezionata));
   $("#qtaRestore").html(qtaRestore);	
}

$(document).ready(function() {

	  setQtaRestore();
	  setImporto();
	  
	  $("#ajaxForm").submit(function() {

		var storeroomId = $('#StoreroomId').val();
	    var deliveryId = $("#StoreroomDeliveryId").val();
	    var qta = $("#qta").val();
	    
	    if(deliveryId=="") {
		    alert("Devi indicare la consegna durante la quale ritirerai il prodotto");
		    return false;
		}
	    if(qta=="") {
		    alert("Devi indicare la quantità");
		    return false;
		}
		
	    $.ajax({
	      type: "POST",
	      url: "/?option=com_cake&controller=Storerooms&action=storeroomToUser&id="+storeroomId+"&format=notmplt",
	      data: "id=" + storeroomId + "&delivery_id=" + deliveryId + "&qta=" + qta,
	      dataType: "html",
	      success: function(msg)
	      {
	    	    $('#ajaxContent').animate({opacity:0});
	    	    var url = "<?php echo $url;?>";
	    		$('#ajaxContent').load(url);
	    		$('#ajaxContent').animate({opacity:1},1500);
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
