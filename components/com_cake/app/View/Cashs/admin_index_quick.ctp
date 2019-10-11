<?php
$this->App->d($results);
?>
  <div class="cashs">
	<h2 class="ico-cashs">
		<?php echo __('Cash');?>
	</h2>
	
	<?php
	if(!empty($results)) {
		?>		
		<div class="table-responsive"><table class="table table-hover">
		<tr>
				<th colspan="2"><?php echo __('N');?></th>
				<th colspan="2"><?php echo __('Name');?></th>
				<th colspan="3"><?php echo __('CashSaldo');?></th>
				<th>Sottrai importo</th>
                <th>Aggiungi importo</th>
				<th><?php echo __('Nota');?></th>
		</tr>
		<?php
		echo $this->Form->create('Cash',array('id' => 'formGas'));
		echo '<fieldset>';
	
		$i=0;
		$tot_importo=0;
		foreach ($results as $numResult => $result):
			
				echo '<tr>';
				echo '<td>';
				if(!empty($result['Cash']['id']))	
					echo '<a action="cashes_histories-'.$result['Cash']['id'].'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a>';
				echo '</td>';
				
				    
				echo '<td>'.($numResult + 1).'</td>';
					
				echo '<td>'.$this->App->drawUserAvatar($user, $result['User']['id'], $result['User']).'</td>';
				echo '<td>';
				echo $result['User']['name'];
				if(!empty($result['User']['email']))	
					echo '<br /><a title="'.__('Email send').'" target="_blank" href="mailto:'.$result['User']['email'].'">'.$result['User']['email'].'</a>';				
				echo '</td>';
				
				echo '<td id="'.$result['User']['id'].'-color" style="width:10px;background-color:';
				if($result['Cash']['importo']=='0.00') echo '#fff';
				else
				if($result['Cash']['importo']<0) echo 'red';
				else
				if($result['Cash']['importo']>0) echo 'green';
				echo '"></td>';
				
				echo '<td style="min-width: 150px;white-space: nowrap;">';
				echo $this->Form->input('importo', array('id' => $result['User']['id'].'-importo', 'class' => 'importoSubmit activeUpdate double', 'value' => $result['Cash']['importo_'], 'type' => 'text', 'label' => false, 'after' => '&nbsp;&euro;', 'style' => 'display:inline', 'tabindex' => ($i+1)));
				echo '</td>';
				
				echo '<td>';
				echo '<img alt="" src="'.Configure::read('App.img.cake').'/blank32x32.png" id="submitEcomm-'.$result['User']['id'].'" class="buttonCarrello submitEcomm" />';
				echo '<div id="msgEcomm-'.$result['User']['id'].'" class="msgEcomm"></div>';
				echo '</td>';
		

				echo '<td style="white-space: nowrap;">';
				echo $this->Form->input('importo_subtract', array('id' => $result['User']['id'].'-importo-subtract', 'class' => 'activeUpdateSubtract double', 'value' => '0,00', 'type' => 'text', 'label' => false, 'after' => '&nbsp;&euro;', 'style' => 'display:inline', 'tabindex' => ($i+1)));
				echo '</td>';
        
				echo '<td style="white-space: nowrap;">';
				echo $this->Form->input('importo_add', array('id' => $result['User']['id'].'-importo-add', 'class' => 'activeUpdateAdd double', 'value' => '0,00', 'type' => 'text', 'label' => false, 'after' => '&nbsp;&euro;', 'style' => 'display:inline', 'tabindex' => ($i+1)));
				echo '</td>';
        
				echo '<td>';
				echo $this->Form->input('nota', array('id' => $result['User']['id'].'-nota', 'value' => $result['Cash']['nota'], 'class' => 'noeditor', 'type' => 'textarea', 'label' => false, 'rows' => '2')); // lo tolgo dalla nota se no scatta sempre l'evento onfocus
				echo '</td>';			
			echo '</tr>';

			if(!empty($result['Cash']['id'])) {
				echo '<tr class="trView" id="trViewId-'.$result['Cash']['id'].'">';
				echo '	<td colspan="2"></td>'; 
				echo '	<td colspan="8" id="tdViewId-'.$result['Cash']['id'].'"></td>';
				echo '</tr>';
			}
						
			$tot_importo += $result['Cash']['importo'];
			$i++;
		endforeach; 
	
		echo $this->Form->end();	
		
		/*
		 * totale cassa
		 */
		echo '</tr>';
		echo '<td></td>';
		echo '<td></td>';	
		echo '<td></td>';
		echo '<td></td>';
		echo '<td style="text-align:right;font-weight: bold;">Totale</td>';
		echo '<td id="importo_totale_color" style="width:10px;background-color:#fff"></td>';
		echo '<td>';
		echo '<span id="importo_totale"></span>';
		echo '</td>';
		echo '<td></td>';
		echo '<td></td>';
		echo '<td></td>';
		echo '<td></td>';
		echo '<td></td>';
		echo '</tr>';	
		
		echo '</table></div>';
		
		echo $this->element('legendaCash');
	} 
	else 
		echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFound', 'msg' => "Non ci sono utenti attivi!"));
		
echo '</div>';
?>
<script type="text/javascript">
function settaColorRow(user_id) {
	var importo = $('#'+user_id+'-importo').val();
	importo = numberToJs(importo);
	
	if(importo < 0)
		$('#'+user_id+'-color').css('background-color', 'red');
	else
	if(importo > 0)
		$('#'+user_id+'-color').css('background-color', 'green');
	else
		$('#'+user_id+'-color').css('background-color', '#fff');
}
function settaImportoTotale() {

	var importo_totale = 0;
	$('.importoSubmit').each(function( index ) {
		var importo = $(this).val();
		importo = numberToJs(importo);

		importo_totale = (parseFloat(importo_totale) + parseFloat(importo));
		/* console.log(importo_totale + "+" + importo + " = "+ importo_totale); */
    });
    /* 
	   console.log("----------------------");
       console.log(importo_totale);
	*/
	if(importo_totale < 0)
		$('#importo_totale_color').css('background-color', 'red');
	else
	if(importo_totale > 0)
		$('#importo_totale_color').css('background-color', 'green');
	else
		$('#importo_totale_color').css('background-color', '#fff');
		
	importo_totale = number_format(importo_totale,2,',','.');
	/*console.log(importo_totale);*/
	
	$('#importo_totale').html(importo_totale+'&nbsp;&euro;');
}

function callUpdateImporto(user_id, importo) {
	
	/* console.log("callUpdateImporto - importo "+importo); */
	
	if(importo=='' || importo==undefined) { /* || importo=='0,00' || importo=='0.00' || importo=='0') { permetto di portare il saldo a ZERO */
		return;
	}
	
	$("#submitEcomm-" + user_id).animate({opacity: 1});
	

    var url = '';
    url = "/administrator/index.php?option=com_cake&controller=Cashs&action=index_quick_update&format=notmpl";

    $.ajax({
        type: "POST",
        url: url,
        data: "user_id="+user_id+"&value="+encodeURIComponent(importo),
        success: function(response){
            $("#submitEcomm-" + user_id).attr("src", app_img + "/actions/32x32/bookmark.png");
            $("#msgEcomm-" + user_id).html("Salvato!");
			$('#'+user_id+"-importo-subtract").val('0,00');
			$('#'+user_id+"-importo-add").val('0,00');			
            $("#submitEcomm-" + user_id).delay(1000).animate({
                opacity: 0
            }, 1500);
            $("#msgEcomm-" + user_id).delay(1000).animate({
                opacity: 0
            }, 1500);
        },
        error:function (XMLHttpRequest, textStatus, errorThrown) {
             $('#msgEcomm-'+user_id).html(textStatus);
             $('#submitEcomm-'+user_id).attr('src',app_img+'/blank32x32.png');
			 $('#'+user_id+"-importo-subtract").val('0,00');
			 $('#'+user_id+"-importo-add").val('0,00');
        }
    });

    settaColorRow(user_id);
    settaImportoTotale();	        
}

$(document).ready(function() {

	settaImportoTotale();
	
	$('.double').focusout(function() {validateNumberField(this, 'importo');});

	$(".activeUpdate").each(function () {
		$(this).change(function() {
			/* get id da id="id-field-table"  */
			var idRow = $(this).attr('id');
			
			var user_id = idRow.substring(0,idRow.indexOf('-'));
			var importo =  $(this).val();
			
            callUpdateImporto(user_id, importo);
            return false;
		});
	});
	
	$('.activeUpdateSubtract').change(function() {
        /* get id da id="id-field-table"  */
        var idRow = $(this).attr('id');

        var user_id = idRow.substring(0,idRow.indexOf('-'));
        var value =  $(this).val();
		if(value=='' || value=='0' || value=='00' || value=='0.0' || value=='0.00' || value=='0,00' || value=='0,0') {
			alert("Inserisci un importo diverso da zero!");
			return;	
		}
		/*console.log('activeUpdateSubtract '+value);*/
        value = numberToJs(value);
		/*console.log('activeUpdateAdd numberToJs '+value);*/

        var importo_cassa_orig = $('#'+user_id+'-importo').val();
        /*console.log(importo_cassa_orig);*/
        importo_cassa_orig = numberToJs(importo_cassa_orig);
        
        var importo_cassa_new = (parseFloat(importo_cassa_orig) - parseFloat(value));
        importo_cassa_new = number_format(importo_cassa_new,2,',','.');
        $('#'+user_id+'-importo').val(importo_cassa_new);
        
        callUpdateImporto(user_id, importo_cassa_new);
        return false;        
    });
    	
	$('.activeUpdateAdd').change(function() {
        /* get id da id="id-field-table"  */
        var idRow = $(this).attr('id');

        var user_id = idRow.substring(0,idRow.indexOf('-'));
        var value =  $(this).val();
		if(value=='' || value=='0' || value=='00' || value=='0.0' || value=='0.00' || value=='0,00' || value=='0,0'){
			alert("Inserisci un importo diverso da zero!");
			return;	
		}
		/*console.log('activeUpdateAdd '+value);*/
        value = numberToJs(value);
		/*console.log('activeUpdateAdd numberToJs '+value);*/
		
        var importo_cassa_orig = $('#'+user_id+'-importo').val();
		importo_cassa_orig = numberToJs(importo_cassa_orig);
        
        var importo_cassa_new = (parseFloat(importo_cassa_orig) + parseFloat(value));
        importo_cassa_new = number_format(importo_cassa_new,2,',','.');
        $('#'+user_id+'-importo').val(importo_cassa_new);
        
        callUpdateImporto(user_id, importo_cassa_new);
        return false;           
    });
    
	$('.importoSubmit').change(function() {

		var importo = $(this).val();

		if(importo=='' || importo==undefined) { /* || importo=='0,00' || importo=='0.00' || importo=='0') { permetto di portare il saldo a ZERO */ 
			alert("Devi indicare l'importo da associare all'utente");
			$(this).val("0,00");
			$(this).focus();
			return false;
		}	
	});
	
	$('.noeditor').focusout(function() {

		var nota = $(this).val();

		if(nota!='') {

	        var idRow = $(this).attr('id');
	        var user_id = idRow.substring(0,idRow.indexOf('-'));
	        
			$("#submitEcomm-" + user_id).animate({opacity: 1});
				
		    var url = '';
		    url = "/administrator/index.php?option=com_cake&controller=Cashs&action=index_quick_update_nota&format=notmpl";
		
		    $.ajax({
		        type: "POST",
		        url: url,
		        data: "user_id="+user_id+"&value="+encodeURIComponent(nota),
		        success: function(response){
		            $("#submitEcomm-" + user_id).attr("src", app_img + "/actions/32x32/bookmark.png");
		            $("#msgEcomm-" + user_id).html("Salvato!");
		            $("#submitEcomm-" + user_id).delay(1000).animate({
		                opacity: 0
		            }, 1500);
		            $("#msgEcomm-" + user_id).delay(1000).animate({
		                opacity: 0
		            }, 1500);
		        },
		        error:function (XMLHttpRequest, textStatus, errorThrown) {
		             $('#msgEcomm-'+user_id).html(textStatus);
		             $('#submitEcomm-'+user_id).attr('src',app_img+'/blank32x32.png');
		        }
		    });
		
		    settaColorRow(user_id);

		}	
	});
});		
</script>