<div class="docs">
	
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th colspan="2">Tipologia di documento</th>
		<th></th>
		<th>Formato pdf</th>
		<th>Formato excel</th>
	</tr>
	<tr>
		<td><a action="organizationsPayment" class="actionTrConfig openTrConfig" href="#" title="<?php echo __('Href_title_expand_config');?>"></a></td>
		<td>Stampa <b>dati pagamento</b> GAS</td>
		<td></td>
		<td></td>
		<td>
			<?php
			if(Configure::read('developer.mode'))
				echo 'No in developer mode';
			else
				echo '<a class="organizationsPayment" id="organizationsPayment-EXCEL" style="cursor:pointer;" rel="nofollow" title="stampa i dati di pagamento '.__('formatFilePdf').'"><img alt="EXCEL" src="'.Configure::read('App.img.cake').'/minetypes/32x32/vcalendar.png"></a>';
			?>
		</td>		
	</tr>
	<tr class="trConfig" id="trConfigId-organizationsPayment">
		<td></td>
		<td colspan="4" id="tdConfigId-organizationsPayment">
			
			<div class="left label" style="width:125px !important;">Opzioni stampa</div>
			<div class="left radio">
				<p>
					<?php
					$start = 2016;
					$end = date("Y");
					for($i=$start; $i<=$end; $i++)  
						$years[$i] = $i;
						
					echo $this->Form->input('years', array('label' => __('years'), 'id' => 'years', 'options' => $years,
											'default' => $end,'escape' => false));
					?>
				</p>						
			</div>	
						
		</td>
	</tr>		
	</table>
</div>


<script type="text/javascript">
jQuery(document).ready(function() {

	jQuery('.organizationsPayment').click(function() {	
		var id =  jQuery(this).attr('id');
		idArray = id.split('-');
		var action      = idArray[0];
		var doc_formato = idArray[1];
		
		var year = jQuery('#years').val();
		
		window.open('/administrator/index.php?option=com_cake&controller=ExportDocs&action='+action+'&year='+year+'&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');		
	});	
	
});
</script>