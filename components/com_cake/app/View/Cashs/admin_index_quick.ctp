<?php
/*
  echo "<pre>";
  print_r($results);
  echo "</pre>";
*/
?>
  <div class="cashs">
	<h2 class="ico-cashs">
		<?php echo __('Cash');?>
	</h2>
	
	<?php
	if(!empty($results)) {
		?>		
		<table cellpadding="0" cellspacing="0">
		<tr>
				<th colspan="2"><?php echo __('N');?></th>
				<th colspan="2"><?php echo __('Name');?></th>
				<th><?php echo __('Mail');?></th>
				<th colspan="3"><?php echo __('CashSaldo');?></th>
				<th>Sottrai importo</th>
                <th>Aggiungi importo</th>
				<th><?php echo __('nota');?></th>
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
				echo '<td>'.$result['User']['name'].'</td>';
				
				echo '<td>';
				if(!empty($result['User']['email']))	
					echo ' <a title="'.__('Email send').'" target="_blank" href="mailto:'.$result['User']['email'].'">'.$result['User']['email'].'</a>';
				echo '</td>';
				
				echo '<td id="'.$result['User']['id'].'-color" style="width:10px;background-color:';
				if($result['Cash']['importo']=='0.00') echo '#fff';
				else
				if($result['Cash']['importo']<0) echo 'red';
				else
				if($result['Cash']['importo']>0) echo 'green';
				echo '"></td>';
				
				echo '<td>';
				echo $this->Form->input('importo', array('id' => $result['User']['id'].'-importo', 'class' => 'importoSubmit activeUpdate noWidth double', 'value' => $result['Cash']['importo_'], 'type' => 'text', 'label' => false, 'after' => '&nbsp;&euro;', 'size' => '8', 'tabindex' => ($i+1)));
				echo '</td>';
				
				echo '<td>';
				echo '<img alt="" src="'.Configure::read('App.img.cake').'/blank32x32.png" id="submitEcomm-'.$result['User']['id'].'" class="buttonCarrello submitEcomm" />';
				echo '<div id="msgEcomm-'.$result['User']['id'].'" class="msgEcomm"></div>';
				echo '</td>';
		

				echo '<td>';
				echo $this->Form->input('importo_subtract', array('id' => $result['User']['id'].'-importo-subtract', 'class' => 'activeUpdateSubtract noWidth double', 'value' => '0,00', 'type' => 'text', 'label' => false, 'after' => '&nbsp;&euro;', 'size' => '8', 'tabindex' => ($i+1)));
				echo '</td>';
        
				echo '<td>';
				echo $this->Form->input('importo_add', array('id' => $result['User']['id'].'-importo-add', 'class' => 'activeUpdateAdd noWidth double', 'value' => '0,00', 'type' => 'text', 'label' => false, 'after' => '&nbsp;&euro;', 'size' => '8', 'tabindex' => ($i+1)));
				echo '</td>';
        
				echo '<td>';
				echo $this->Form->input('nota', array('id' => $result['User']['id'].'-nota', 'value' => $result['Cash']['nota'], 'class' => 'noeditor', 'type' => 'textarea', 'label' => false, 'rows' => '2', 'tabindex' => ($i+1)));
				echo '</td>';			
			echo '</tr>';

			if(!empty($result['Cash']['id'])) {
				echo '<tr class="trView" id="trViewId-'.$result['Cash']['id'].'">';
				echo '	<td colspan="2"></td>'; 
				echo '	<td colspan="10" id="tdViewId-'.$result['Cash']['id'].'"></td>';
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
		
		echo '</table>';
		
		echo $this->element('legendaCash');
	} 
	else 
		echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFonud', 'msg' => "Non ci sono utenti attivi!"));
		
echo '</div>';
?>
<script type="text/javascript">
function settaColorRow(user_id) {
	var importo = jQuery('#'+user_id+'-importo').val();
	importo = numberToJs(importo);
	
	if(importo < 0)
		jQuery('#'+user_id+'-color').css('background-color', 'red');
	else
	if(importo > 0)
		jQuery('#'+user_id+'-color').css('background-color', 'green');
	else
		jQuery('#'+user_id+'-color').css('background-color', '#fff');
}
function settaImportoTotale() {

	var importo_totale = 0;
	jQuery('.importoSubmit').each(function( index ) {
		var importo = jQuery(this).val();
		importo = numberToJs(importo);

		importo_totale = (parseFloat(importo_totale) + parseFloat(importo));
		/* console.log(importo_totale + "+" + importo + " = "+ importo_totale); */
    });
    /* 
	   console.log("----------------------");
       console.log(importo_totale);
	*/
	if(importo_totale < 0)
		jQuery('#importo_totale_color').css('background-color', 'red');
	else
	if(importo_totale > 0)
		jQuery('#importo_totale_color').css('background-color', 'green');
	else
		jQuery('#importo_totale_color').css('background-color', '#fff');
		
	importo_totale = number_format(importo_totale,2,',','.');
	/*console.log(importo_totale);*/
	
	jQuery('#importo_totale').html(importo_totale+' &euro;');
}

function callUpdateImporto(user_id, importo) {

	if(importo=='' || importo==undefined || importo=='0,00' || importo=='0.00' || importo=='0') {
		return;
	}
	
	jQuery("#submitEcomm-" + user_id).animate({opacity: 1});
	

    var url = '';
    url = "/administrator/index.php?option=com_cake&controller=Cashs&action=index_quick_update&format=notmpl";

    jQuery.ajax({
        type: "POST",
        url: url,
        data: "user_id="+user_id+"&value="+encodeURIComponent(importo),
        success: function(response){
            jQuery("#submitEcomm-" + user_id).attr("src", app_img + "/actions/32x32/bookmark.png");
            jQuery("#msgEcomm-" + user_id).html("Salvato!");
            jQuery("#submitEcomm-" + user_id).delay(1000).animate({
                opacity: 0
            }, 1500);
            jQuery("#msgEcomm-" + user_id).delay(1000).animate({
                opacity: 0
            }, 1500);
        },
        error:function (XMLHttpRequest, textStatus, errorThrown) {
             jQuery('#msgEcomm-'+user_id).html(textStatus);
             jQuery('#submitEcomm-'+user_id).attr('src',app_img+'/blank32x32.png');
        }
    });

    settaColorRow(user_id);
    settaImportoTotale();	        
}

jQuery(document).ready(function() {

	settaImportoTotale();
	
	jQuery('.double').focusout(function() {validateNumberField(this, 'importo');});

	jQuery(".activeUpdate").each(function () {
		jQuery(this).change(function() {
			/* get id da id="id-field-table"  */
			var idRow = jQuery(this).attr('id');
			
			var user_id = idRow.substring(0,idRow.indexOf('-'));
			var importo =  jQuery(this).val();
			
            callUpdateImporto(user_id, importo);
            return false;
		});
	});
	
	jQuery('.activeUpdateSubtract').change(function() {
        /* get id da id="id-field-table"  */
        var idRow = jQuery(this).attr('id');

        var user_id = idRow.substring(0,idRow.indexOf('-'));
        var value =  jQuery(this).val();
        var importo_cassa_orig = jQuery('#'+user_id+'-importo').val();
        /*console.log(importo_cassa_orig);*/
        value = numberToJs(value);
        importo_cassa_orig = numberToJs(importo_cassa_orig);
        
        var importo_cassa_new = (parseFloat(importo_cassa_orig) - parseFloat(value));
        importo_cassa_new = number_format(importo_cassa_new,2,',','.');
        jQuery('#'+user_id+'-importo').val(importo_cassa_new);
        
        callUpdateImporto(user_id, importo_cassa_new);
        return false;        
    });
    	
	jQuery('.activeUpdateAdd').change(function() {
        /* get id da id="id-field-table"  */
        var idRow = jQuery(this).attr('id');

        var user_id = idRow.substring(0,idRow.indexOf('-'));
        var value =  jQuery(this).val();
        var importo_cassa_orig = jQuery('#'+user_id+'-importo').val();
        
        value = numberToJs(value);
        importo_cassa_orig = numberToJs(importo_cassa_orig);
        
        var importo_cassa_new = (parseFloat(importo_cassa_orig) + parseFloat(value));
        importo_cassa_new = number_format(importo_cassa_new,2,',','.');
        jQuery('#'+user_id+'-importo').val(importo_cassa_new);
        
        callUpdateImporto(user_id, importo_cassa_new);
        return false;           
    });
    
	jQuery('.importoSubmit').change(function() {

		var importo = jQuery(this).val();

		if(importo=='' || importo==undefined || importo=='0,00' || importo=='0.00' || importo=='0') {
			alert("Devi indicare l'importo da associare all'utente");
			jQuery(this).val("0,00");
			jQuery(this).focus();
			return false;
		}	
	});
	
	jQuery('.noeditor').focusout(function() {

		var nota = jQuery(this).val();

		if(nota!='') {

	        var idRow = jQuery(this).attr('id');
	        var user_id = idRow.substring(0,idRow.indexOf('-'));
	        
			jQuery("#submitEcomm-" + user_id).animate({opacity: 1});
				
		    var url = '';
		    url = "/administrator/index.php?option=com_cake&controller=Cashs&action=index_quick_update_nota&format=notmpl";
		
		    jQuery.ajax({
		        type: "POST",
		        url: url,
		        data: "user_id="+user_id+"&value="+encodeURIComponent(nota),
		        success: function(response){
		            jQuery("#submitEcomm-" + user_id).attr("src", app_img + "/actions/32x32/bookmark.png");
		            jQuery("#msgEcomm-" + user_id).html("Salvato!");
		            jQuery("#submitEcomm-" + user_id).delay(1000).animate({
		                opacity: 0
		            }, 1500);
		            jQuery("#msgEcomm-" + user_id).delay(1000).animate({
		                opacity: 0
		            }, 1500);
		        },
		        error:function (XMLHttpRequest, textStatus, errorThrown) {
		             jQuery('#msgEcomm-'+user_id).html(textStatus);
		             jQuery('#submitEcomm-'+user_id).attr('src',app_img+'/blank32x32.png');
		        }
		    });
		
		    settaColorRow(user_id);

		}	
	});
});		
</script>