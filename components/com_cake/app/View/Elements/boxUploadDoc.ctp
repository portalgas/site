<?php
if(!isset($type)) // TESORIERE / CASSIERE
	$type = 'TESORIERE';

echo $this->Form->create('Order', ['id' => 'formGas', 'enctype' => 'multipart/form-data']);
echo '<fieldset>';

if(isset($msg) && !empty($msg))
	echo $this->element('boxMsg', array('msg' => $msg));

$i=0;
echo "\r\n";
echo '<div class="table-responsive"><table class="table">';
echo '<tr>';
echo '<td>';
if($type=='TESORIERE')
	echo __('Tesoriere Doc1');
else
	echo __('Cassiere Doc1');	
echo '</td>';
echo '<td>';
echo $this->Form->input('Order.tesoriere_doc1', array(
		'between' => '<br />',
		'type' => 'file',
		'label' => false, 'tabindex'=>($i+1)
));

if(isset($file1)) {
	echo '<div class="input">';

	$ico = $this->App->drawDocumentIco($file1->name);
	echo '<a href="'.Configure::read('App.server').Configure::read('App.web.doc.upload.tesoriere').'/'.$user->organization['Organization']['id'].'/'.$file1->name.'" target="_blank"><img style="cursor:pointer;" src="'.$ico.'" title="'.$file1->name.'" /></a>';

	echo '&nbsp;&nbsp;&nbsp;'.$this->App->formatBytes($file1->size());
	echo '</div>';
	
	/*
	 * non permetto la cancellazione se Organization.hasFieldFatturaRequired=='Y'
	 *
	 * non la permetto mai perche' non e' gestita correttamente
	 if($user->organization['Organization']['hasFieldFatturaRequired']=='N') {
		echo $this->Form->checkbox('file1_delete', array('label' => 'Cancella documento', 'value' => 'Y'));
		echo $this->Form->label('Cancella documento');
	 }
	 */
}
	
echo $this->element('legendaTesoriereDoc1');

echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td>'.__('Tesoriere fattura importo').'</td>';
echo '<td style="white-space: nowrap;padding-right: 25px;">';
echo $this->Form->input('tesoriere_fattura_importo',array('type' => 'text','label' => false, 'id' => 'tesoriere_fattura_importo', 'tabindex'=>($i+1), 'after'=>'&nbsp;&euro;','class'=>'double', 'style'=>'display:inline', 'value' => number_format($this->Form->value('Order.tesoriere_fattura_importo'),2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'))));
echo '</td>';
echo '</tr>';
echo '<tr>';
echo '<td>'.__('Importo totale ordine').'</td>';
echo '<td>';
echo number_format($importo_totale,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).' '.'&euro;';
echo $this->Form->input('importo_totale',array('type' => 'hidden', 'id' => 'importo_totale', 'value'=> $importo_totale));
echo '</td>';
echo '</tr>';
echo '<td>'.__('Delta').'</td>';
echo '<td style="white-space: nowrap;" class="differenza_result"><span id="differenza" style="display:inline">0,00</span>&nbsp;&euro;</td>';
echo '</tr>';
echo '</table></div>';

if($type=='TESORIERE')
	$label = __('Tesoriere Nota');
else
	$label = __('Cassiere Nota');

echo $this->Form->input('tesoriere_nota',array('tabindex'=>($i+1), 'required' => 'false', 'label' => $label));

echo '</fieldset>';

echo $this->Form->hidden('order_id', ['value' => $order_id]);
echo $this->Form->submit(__('Submit'), array('type' => 'submit', 'class' => 'afterDisabled'));

echo $this->Form->end();
?>

<script type="text/javascript">
var debug = false;

function setta_differenza() {

	var tesoriere_fattura_importo = $('#tesoriere_fattura_importo').val();
	var importo_totale = $('#importo_totale').val();

	if(debug) {
		console.log('tesoriere_fattura_importo '+tesoriere_fattura_importo);
		console.log('importo_totale '+importo_totale);
	}
	
	/* 
	tesoriere_fattura_importo: campo inserito dall'utente, lo converto 
	importo_totale: campo non inserito dall'utente, lo converto
	*/
	tesoriere_fattura_importo = numberToJs(tesoriere_fattura_importo);
	//importo_totale = numberToJs(importo_totale);

	if(debug) {
		console.log('tesoriere_fattura_importo '+tesoriere_fattura_importo);
		console.log('importo_totale '+importo_totale);
	}
	
	var differenza = (parseFloat(importo_totale) - parseFloat(tesoriere_fattura_importo));
	if(debug) {
		console.log('differenza '+differenza);
	}	
	if(differenza >= 0 && differenza!=importo_totale) {
		$('.differenza_result').css('background-color','green');
		$('.differenza_result').css('color','#fff');
	}
	else {
		$('.differenza_result').css('background-color','red');
		$('.differenza_result').css('color','#000');
	}	

	differenza = number_format(differenza,2,',','.');  /* in 1.000,50 */
	$('#differenza').html(differenza);
}

var sumbitJustSend = false;

$(document).ready(function() {

	setta_differenza();
	
	$('#tesoriere_fattura_importo').change(function() {
		setta_differenza();
	});	

	$('#formGas').submit(function() {

		if(sumbitJustSend) 
			return false;
		
		<?php
		if($user->organization['Organization']['hasFieldFatturaRequired']=='Y' && !isset($file1)) {
		?>
			var doc1 = $('#OrderTesoriereDoc1').val();
			if(doc1=='' || doc1==undefined) {
				alert("Devi uplodare la fattura per il cassiere/tesoriere");
				$(this).focus();
				return false;
			}	
			
			var tesoriere_fattura_importo = $('#tesoriere_fattura_importo').val();
			if(tesoriere_fattura_importo=='' || tesoriere_fattura_importo==undefined) {
				alert("Devi indicare l'importo della fattura");
				$(this).val("0,00");
				$(this).focus();
				return false;
			}	
			
			if(tesoriere_fattura_importo=='0,00') {
				alert("L'importo della fattura dev'essere indicato con un valore maggior di 0");
				$(this).focus();
				return false;
			}
		<?php		
		}
		?>

		var doc1 = $('#OrderTesoriereDoc1').val().split('\\').pop();
		if(doc1!='') {
			  pattern = /^([a-zA-Z0-9\.\_\-\s\à\è\é\ì\ò\ù])+$/;
			  if(!checkPatternChars('File',doc1,pattern,false))
			  {
					return false;
			  }
		}
				
		var differenza_alert = 50;
		var tesoriere_fattura_importo = $('#tesoriere_fattura_importo').val();
		var importo_totale = $('#importo_totale').val();
		tesoriere_fattura_importo = numberToJs(tesoriere_fattura_importo);
		
		var differenza = (parseFloat(importo_totale) - parseFloat(tesoriere_fattura_importo));
		if(debug) {
			console.log('tesoriere_fattura_importo '+tesoriere_fattura_importo);
			console.log('importo_totale '+importo_totale);
			console.log('differenza '+differenza);
		}

		if((  (differenza < 0) && ((differenza* -1) > differenza_alert)) || 
		   (  (differenza > 0) && (differenza > differenza_alert)) )  {
			if(!confirm("La differenza tra l'importo della fattura e l'importo totale dell'ordine è superiore ai "+differenza_alert+" euro, sei sicuro che desideri continuare?"))
				return false;
		}	
		
		$('input[type="submit"].afterDisabled').val("Elaborazione in corso.. attendere...").unbind().css('cursor','default');
		sumbitJustSend = true;
	
		return true;
	});
});
</script>