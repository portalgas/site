<?php
if(isset($order['Order']) && $order['Order']['order_type_id']==Configure::read('Order.type.gas_groups')) {
	$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
	$this->Html->addCrumb(__('List Orders'), Configure::read('Neo.portalgas.url').'admin/orders/index/'.$order['Order']['order_type_id']);
	$this->Html->addCrumb(__('Order home'), Configure::read('Neo.portalgas.url').'admin/orders/home/'.$order['Order']['order_type_id'].'/'.$order['Order']['id']);
	$this->Html->addCrumb(__('Validation Carts'),array('controller'=>'Carts','action'=>'validationCarts', null, 'order_id='.$order_id));	
	$this->Html->addCrumb(__('Validation Carts Edit'));
}
else {
	$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
	$this->Html->addCrumb(__('List Orders'),array('controller' => 'Orders', 'action' => 'index'));
	if(isset($order_id) && !empty($order_id))
		$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$order_id));
	$this->Html->addCrumb(__('Validation Carts'),array('controller'=>'Carts','action'=>'validationCarts', null, 'order_id='.$order_id));	
	$this->Html->addCrumb(__('Validation Carts Edit'));	
}
echo $this->Html->getCrumbList(['class'=>'crumbs']);
?>
<h2 class="ico-validation-carts">
	<?php echo __('Validation Carts');?>
	<div class="actions-img">
		<ul>
			<li><?php echo $this->Html->link(__('Order home'), array('controller' => 'Orders', 'action' => 'home', null, 'order_id='.$order_id),array('class' => 'action actionWorkflow','title' => __('Order home'))); ?></li>
		</ul>
	</div>
</h2>

<div class="contentMenuLaterale">
<?php echo $this->Form->create('Cart',array('id' => 'formGas'));
?>
	<fieldset>

	<table cellpadding="0" cellspacing="0">
		<tr>
			<th colspan="3"><?php echo __('Name');?></th>
			<th><?php echo __('PrezzoUnita');?></th>
			<th><?php echo __('pezzi_confezione');?></th>
			<th>Quantità<br />ordinata</th>
			<th>
				<span style="float:left;">Differenza<br />da ordinare</span>
				<span style="float:right;"><?php echo $this->Tabs->drawTooltip('Importo forzato',__('toolTipQtaDifferenza'),$type='WARNING',$pos='LEFT');?></span>
			</th>
			<th>Importo<br />da pagare</th>
		</tr>

		<tr class="view">
			<td>
			<?php 
			if(!empty($articlesOrdesResults['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$articlesOrdesResults['Article']['organization_id'].DS.$articlesOrdesResults['Article']['img1'])) {
				echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$articlesOrdesResults['Article']['organization_id'].'/'.$articlesOrdesResults['Article']['img1'].'" />';			
			}		
			?>	
			</td>
			<td colspan="2"><?php echo $articlesOrdesResults['ArticlesOrder']['name'];?></td>
			<td style="text-align:center;"><?php echo $articlesOrdesResults['ArticlesOrder']['prezzo_e'];?></td>
			<td style="text-align:center;"><?php echo $articlesOrdesResults['ArticlesOrder']['pezzi_confezione'];?></td>
			<td style="text-align:center;font-weight: bold;">
				<div id="qtaTot">
				<?php echo $articlesOrdesResults['ArticlesOrder']['qta_cart'];?>
				</div>
			</td>
			<td>
				<div id="qtaDiff" class="qtaEvidenza"><?php echo $articlesOrdesResults['ArticlesOrder']['differenza_da_ordinare'];?></div></td>
			<td style="text-align:center;"><?php echo number_format($articlesOrdesResults['ArticlesOrder']['differenza_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));?>&nbsp;&euro;</td>
		</tr>
		<tr>
			<td style="width:50px;"></td>
			<td colspan="7">
			

				<table cellpadding = "0" cellspacing = "0">
					<tr>
						<th style="height:10px;width:30px;" rowspan="2"><?php echo __('N');?></th>
						<th style="height:10px;" rowspan="2"><?php echo __('User');?></th>
						<th style="text-align:center;width:50px;height:10px;border-bottom:none;border-left:1px solid #CCCCCC;"><?php echo __('qta');?></th>
						<th style="text-align:center;width:100px;height:10px;border-bottom:none;border-right:1px solid #CCCCCC;"><?php echo __('Importo');?></th>
						<th colspan="2" style="text-align:center;width:150px;height:10px;border-bottom:none;">Quantità e importi totali</th>
						<th style="height: 10px;width:150px;" rowspan="2"></th>
						<th style="height: 10px;" rowspan="2"><?php echo __('qta');?></th>
						<th style="height: 10px;" rowspan="2"><?php echo __('Importo');?></th>
						<th style="height: 10px;" rowspan="2"><?php echo __('Stato');?></th>
						<th style="height: 10px;" rowspan="2"><?php echo __('Acquistato il'); ?></th>
					</tr>	
					<tr>
						<th style="text-align:center;height:10px;border-left:1px solid #CCCCCC;border-right:1px solid #CCCCCC;" colspan="2">dell'utente</th>
						<th style="text-align:center;height:10px;border-right:1px solid #CCCCCC;" colspan="2">modificati dal referente</th>
					</tr>
			
				<?php 
					foreach($results as $numResult => $result) {
			
						$rowId = $result['Cart']['order_id'].'_'.$result['Cart']['article_organization_id'].'_'.$result['Cart']['article_id'].'_'.$result['Cart']['user_id'];
						
						echo "\r\n";
						echo '<tr class="rowEcomm">';
						echo '<td>'.((int)$numResult+1).'</td>';
						echo '<td>'.$result['User']['name'].'</td>';
						echo '<td style="text-align:center;">';
						if($result['Cart']['qta']>0)
							echo $result['Cart']['qta'];
						else
							echo '-';
						echo '</td>';
							
						echo "\r\n";
						echo '<td style="text-align:center;">';
						if($result['Cart']['qta']>0)
							echo $this->App->getArticleImporto($result['ArticlesOrder']['prezzo'], $result['Cart']['qta']);
						else
							echo '-';
						echo '</td>';
						echo '<td style="text-align:center;">';
						if($result['Cart']['qta_forzato']>0)
							echo $result['Cart']['qta_forzato'];
						else
							echo '-';
						echo '</td>';
						echo '<td style="text-align:center;">';
						if($result['Cart']['importo_forzato']>0)
							echo $result['Cart']['importo_forzato'].'&nbsp;&euro;';
						else
							echo '-';
						echo '</td>';
							
						echo '<td style="text-align:center;">';
						echo '<div id="buttonPiuMeno-'.$rowId.'" class="buttonPiuMeno" style="display:none;">';
						echo '<img alt="compra in +" src="'.Configure::read('App.img.cake').'/button-piu.png" class="buttonCarrello buttonPiu" />';
						echo "\n";
						echo '<img alt="compra in -" src="'.Configure::read('App.img.cake').'/button-meno.png" class="buttonCarrello buttonMeno" />';						
						echo '</td>';
						
						// Quantita' forzato impostata dal referente
						if($result['Cart']['qta_forzato']==0)
							$qta_forzato = $result['Cart']['qta'];
						else
							$qta_forzato = $result['Cart']['qta_forzato'];
						if($qta_forzato>0) $classQta = "qtaUno";
						else $classQta = "qtaZero";
						
						echo '<td style="text-align:center;">';
						echo '<div id="qta-'.$rowId.'" class="'.$classQta.' qta">'.$qta_forzato.'</div>';
						echo '</td>';

						
						if($result['Cart']['importo_forzato']==0) {
							if($result['Cart']['qta_forzato']>0)
								$importo = number_format(($result['Cart']['qta_forzato'] * $result['ArticlesOrder']['prezzo']),2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
							else
								$importo = number_format(($result['Cart']['qta'] * $result['ArticlesOrder']['prezzo']),2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
						}
						else
							$importo = number_format($result['Cart']['importo_forzato'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
						
						echo '<td style="text-align:center;">';
						echo '<div id="prezzoNew-'.$rowId.'" class="prezzoNew">';
						echo $importo.'&nbsp;&euro;';
						echo '<div></td>';
						
						echo "\r\n";
						echo '<td ';
						echo 'title="'.$this->App->traslateArticlesOrderStato($result).'" ';			
						echo ' class="stato_'.strtolower($result['ArticlesOrder']['stato']).'">';
						echo '</td>';
												
						echo '<td style="white-space: nowrap;">'.$this->App->formatDateCreatedModifier($result['Cart']['date']);
						
						/*
						 *	parametri per il ctrl js
						*/
						echo '<input class="debug" type="hidden" value="'.$result['ArticlesOrder']['prezzo'].'" id="prezzo-'.$rowId.'" />';
						echo '<input class="debug" type="hidden" value="'.$result['ArticlesOrder']['prezzo'].'" id="prezzoNew-'.$rowId.'" />';
						
						echo '<input class="debug" type="hidden" value="'.$result['ArticlesOrder']['pezzi_confezione'].'" id="pezzi_confezione-'.$rowId.'" />';
						echo '<input class="debug" type="hidden" value="'.$result['ArticlesOrder']['stato'].'" id="articleOrder_stato-'.$rowId.'" />';
						echo '<input class="debug" type="hidden" value="'.$result['ArticlesOrder']['qta_massima_order'].'" id="qta_massima_order-'.$rowId.'" />';
						echo '<input class="debug" type="hidden" value="'.$result['ArticlesOrder']['qta_cart'].'" id="qta_cart-'.$rowId.'" />';
						
						if($result['Cart']['qta_forzato']==0)  // e' la prima volta che da backOffice faccio una modifica
							$qta_prima_modifica = $result['Cart']['qta'];
						else
							$qta_prima_modifica = $result['Cart']['qta_forzato'];
						
						echo '<input class="debug" type="hidden" name="data[Cart]['.$rowId.'][qta_prima_modifica]" value="'.$qta_prima_modifica.'" id="qta_prima_modifica-'.$rowId.'" />';
						
						/*
						 *	parametri da salvare sul DB
						*/
						echo '<input class="debug inputToAnnullare" type="hidden" name="data[Cart]['.$rowId.'][qta]" value="'.$qta_forzato.'" id="qta_new-'.$rowId.'" />';
						echo '<input class="debug" type="hidden" name="data[Cart]['.$rowId.'][user_id]" value="'.$result['Cart']['user_id'].'" />';

						echo '</td>';	
						echo '</tr>';
						
					} // end foreach($results as $numResult => $result) 
					
									
					/*
					 * gestione utenti nuovi
					 */					 
					 $rowId = $result['Cart']['order_id'].'_'.$result['Cart']['article_organization_id'].'_'.$result['Cart']['article_id'].'_0';
					 $qta=0;
					 $classQta = "qtaZero";
					 $importo = 0;						

					echo '<tr class="rowEcomm">';
					
					 if(!empty($users)) {
						echo '<td></td>';
						echo '<td>';
						echo $this->Form->input('user_id',array('id' => 'adduser_id', 'empty' => Configure::read('option.empty'), 'required' => false, 'value' => $users, 'label' => false));
						echo '</td>';
						echo '<td></td>';
						echo '<td></td>';
						echo '<td></td>';
						echo '<td></td>';

						echo '<td style="text-align:center;">';
						echo '<div id="buttonPiuMeno-'.$rowId.'" class="buttonPiuMeno" style="display:none;">';
						echo '<img alt="compra in +" src="'.Configure::read('App.img.cake').'/button-piu.png" class="buttonCarrello buttonPiu" />';
						echo "\n";
						echo '<img alt="compra in -" src="'.Configure::read('App.img.cake').'/button-meno.png" class="buttonCarrello buttonMeno" />';						
						echo '</td>';
						
						echo '<td style="text-align:center;">';
						echo '<div id="qta-'.$rowId.'" class="'.$classQta.' qta">';
						if($qta>0) echo $qta;
						echo '</div>';
						echo '</td>';
						
						echo '<td style="text-align:center;">';
						echo '<div id="prezzoNew-'.$rowId.'" class="prezzoNew">';
						if($importo>0) echo $importo.'&nbsp;&euro;';
						echo '<div></td>';
						
						echo '<td></td>';
					}
					else
						echo '<td colspan="10"></td>';

					echo '<td>';
					
					/*
					 *	parametri per il ctrl js
					*/
					echo '<input class="debug" type="hidden" value="'.$result['ArticlesOrder']['prezzo'].'" id="prezzo-'.$rowId.'" />';
					echo '<input class="debug" type="hidden" value="'.$result['ArticlesOrder']['prezzo'].'" id="prezzoNew-'.$rowId.'" />';
					
					echo '<input class="debug" type="hidden" value="'.$result['ArticlesOrder']['pezzi_confezione'].'" id="pezzi_confezione-'.$rowId.'" />';
					echo '<input class="debug" type="hidden" value="'.$result['ArticlesOrder']['stato'].'" id="articleOrder_stato-'.$rowId.'" />';
					echo '<input class="debug" type="hidden" value="'.$result['ArticlesOrder']['qta_massima_order'].'" id="qta_massima_order-'.$rowId.'" />';
					echo '<input class="debug" type="hidden" value="'.$result['ArticlesOrder']['qta_cart'].'" id="qta_cart-'.$rowId.'" />';
					
					/*
					 *	parametri da salvare sul DB
					*/
					echo '<input class="debug inputToAnnullare" type="hidden" name="data[Cart]['.$rowId.'][qta]" value="'.$qta.'" id="qta_new-'.$rowId.'" />';
				
					echo '</td>';					
					echo '</tr>';
					 
					/*
					 * annulla ordine
					 */
					echo '<tr>';
					echo '<td colspan="2" class="submitMultiple"><input type="submit" value="Annulla acquisti per questo articolo: setta tutte le quantità a zero" class="buttonBlu" id="action_annulla"></td>';
					echo '<td colspan="9"></td>';					
					echo '</tr>';
				
				echo '</table>';
			
			echo '</td>';
		echo '</tr>';
	echo '</table>';
	
	echo '</fieldset>';
		
	echo $this->Form->hidden('order_id', array('value' => $result['Cart']['order_id']));
	echo $this->Form->hidden('article_organization_id', array('value' => $result['Cart']['article_organization_id']));
	echo $this->Form->hidden('article_id', array('value' => $result['Cart']['article_id']));
	
	echo $this->Form->end(array('label' => __('Submit'), 'div' => array('class' => 'submit', 'style' => 'display:block'))); 
	
echo '</div>';

$options = [];
echo $this->MenuOrders->drawWrapper($order_id, $options);
?>

<script type="text/javascript">
var numRow = "";
$(document).ready(function() {

	$("#action_annulla").click(function () {
		$(".inputToAnnullare").val('0');
		$(".qta").html('0').addClass('qtaZero').removeClass('qtaUno');
		$(".prezzoNew").html('0,00&nbsp;&euro;');	
		
		return false;
	});
		
	$(".rowEcomm").each(function () {
	   /* 
		* rowEcomm
		*/
		$(this).mouseenter(function() {
			 $(this).find('.buttonPiuMeno').css('display','inline');
		});
		$(this).mouseleave(function() {
			 $(this).find('.buttonPiuMeno').css('display','none');
		});

	   /*
		* buttonPiu
		*/
		$(this).find('.buttonPiu').click(function () {
			ecommRowsButtonPiu(this);
		});

	   /* 
		* buttonMeno
		*/
		$(this).find('.buttonMeno').click(function() {
			ecommRowsButtonMeno(this);
		});
	});
	
	$('.submit').click(function() {
		//return false;
	});	
});


function ecommRowsButtonPiu(obj) { 			

	/* get id della TD  <td id="xxx-1">  */
	var idRow = $(obj).parent().attr('id');
	numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
	
	var qta = $('#qta-'+numRow).html(); 
	if(qta=="") qta = 0;
	qta++;
	
	if(!validitationQta(numRow, qta)) return false;
	
	var prezzo = $('#prezzo-'+numRow).val(); 
	
	prezzoNew = number_format((prezzo*qta),2,',','.'); 
	$('#prezzoNew-'+numRow).html(prezzoNew+'&nbsp;&euro;');
	
	$('#qta-'+numRow).html(qta);
	$('#qta-'+numRow).removeClass("qtaZero");
	$('#qta-'+numRow).addClass("qtaUno");
	
	var qtaTot = $('#qtaTot').html();
	qtaTot++; 
	$('#qtaTot').html(qtaTot);	
	
	var pezzi_confezione = $('#pezzi_confezione-'+numRow).val();
	differenza_da_ordinare = (qtaTot % pezzi_confezione);
	var qtaDiff = (pezzi_confezione - differenza_da_ordinare);
	if(qtaDiff==pezzi_confezione) qtaDiff = 0;
	$('#qtaDiff').html(qtaDiff);
	
	if(qtaDiff==0) {
		$('#qtaDiff').removeClass("qtaEvidenza");
		$('#qtaDiff').addClass("qtaUno");
		
		/* $('.submit').show('low');   /* rendo visibile il tasto submit del filtro */ 
	}
	else {
		$('#qtaDiff').removeClass("qtaUno");
		$('#qtaDiff').addClass("qtaEvidenza");	

		/* $('.submit').hide(); */
	}
	
	$('#qta_new-'+numRow).val(qta); /* qta che passo in POST per salvare sul DB */
}

function ecommRowsButtonMeno(obj) { 			

	/* get id della TD  <td id="xxx-1">  */
	var idRow = $(obj).parent().attr('id');
	numRow = idRow.substring(idRow.indexOf('-')+1,idRow.lenght);
	
	var qta = $('#qta-'+numRow).html(); 
	if(qta==0) return false;
	qta--;
	
	if(!validitationQta(numRow, qta)) return false;
	
	var prezzo = $('#prezzo-'+numRow).val();
	prezzoNew = number_format((prezzo*qta),2,',','.'); 
	$('#prezzoNew-'+numRow).html(prezzoNew+'&nbsp;&euro;');
	
	$('#qta-'+numRow).html(qta);
	if(qta==0) {
		$('#qta-'+numRow).removeClass("qtaUno");
		$('#qta-'+numRow).addClass("qtaZero");
	}
	
	var qtaTot = $('#qtaTot').html();
	qtaTot--; 
	$('#qtaTot').html(qtaTot);
	
	var pezzi_confezione = $('#pezzi_confezione-'+numRow).val();
	differenza_da_ordinare = (qtaTot % pezzi_confezione);
	var qtaDiff = (pezzi_confezione - differenza_da_ordinare);
	if(qtaDiff==pezzi_confezione) qtaDiff = 0;
	$('#qtaDiff').html(qtaDiff);
	$('#qta-'+numRow).html(qta);
	
	if(qtaDiff==0) {
		$('#qtaDiff').removeClass("qtaEvidenza");
		$('#qtaDiff').addClass("qtaUno");
		
		/* $('.submit').show('low');   /* rendo visibile il tasto submit del filtro */ 
	}
	else {
		$('#qtaDiff').removeClass("qtaUno");
		$('#qtaDiff').addClass("qtaEvidenza");
		
		/* $('.submit').hide(); */	
	}
	
	$('#qta_new-'+numRow).val(qta); /* qta che passo in POST per salvare sul DB */
}
</script>

<style>
.buttonCarrello {
    float: left;
    margin: 0 0 0 5px;
    padding: 0;
}
</style>