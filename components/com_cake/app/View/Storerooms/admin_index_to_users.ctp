<?php
$this->App->d($results);
$this->App->d($FilterStoreroomGroupBy);

$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('Storeroom'), array('controller' => 'Storerooms', 'action' => 'index'));
$this->Html->addCrumb('Cosa è stato acquistato');
echo $this->Html->getCrumbList(array('class'=>'crumbs'));  

echo '<div class="storerooms">';

echo '<h2 class="ico-storerooms">';
echo __('Storeroom');
echo '</h2>';

echo $this->Form->create('FilterStoreroom',array('id'=>'formGasFilter','type'=>'get'));
echo '<fieldset class="filter">';
echo '<legend>'.__('Filter Storeroom').'</legend>';
?>
			<div class="table-responsive"><table class="table">
				<tr>
					<td>
						<?php echo $this->Form->input('delivery_id',array('label' => false,'options' => $deliveries,'empty' => 'Filtra per consegne','name'=>'FilterStoreroomDeliveryId','default'=>$FilterStoreroomDeliveryId,'escape' => false)); ?>
					</td>
					<td>
						<div class="input">
							<label for="FilterStoreroomGroupBy">Raggruppa per</label>
								<input type="radio" name="FilterStoreroomGroupBy" id="FilterStoreroomGroupBySUPPLIERS" value="SUPPLIERS" <?php if ($FilterStoreroomGroupBy=='SUPPLIERS') echo 'checked="checked"';?> /> 
							<label style="width:80px !important;margin-left:10px;" for="FilterGroupBySUPPLIERS">Produttori</label>
								<input type="radio" name="FilterStoreroomGroupBy" id="FilterStoreroomGroupByUSERS" value="USERS" <?php if ($FilterStoreroomGroupBy=='USERS') echo 'checked="checked"';?> />
							<label style="width:80px !important;margin-left:10px;" for="FilterStoreroomGroupByUSERS">Utenti</label>
						</div>
					</td>
					<td>
						<?php echo $this->Form->reset('Reset', array('value' => 'Reimposta','class' => 'reset')); ?>
					</td>
					<td>
						<?php echo $this->Form->end(array('label' => __('Filter'), 'class' => 'filter', 'div' => array('class' => 'submit filter', 'style' => 'display:none'))); ?>
					</td>
				</tr>	
			</table>
		</fieldset>					

<?php	
if(!empty($results)) {
	if($FilterStoreroomGroupBy=='SUPPLIERS') {
		$delivery_id_old = 0;
		$supplier_organization_id_old = 0;
		$count=0;
		foreach ($results as $i => $result): 
	
			if($result['Storeroom']['delivery_id']!=$delivery_id_old) {
				$count=1;
				if($i>0) echo '</table>';
			
				echo "<h2>".__('Delivery')." ";
				if($result['Delivery']['sys']=='N')
					echo $result['Delivery']['luogoData'];
				else 
					echo $result['Delivery']['luogo'];
				echo '</h2>';
				
				if($result['Delivery']['isToStoreroom']=='N')
						echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "La consegna non è abilitata per gestire la dispensa!")); 
			
				echo '<div class="table-responsive"><table class="table table-hover">';
				echo '<thead>';
				echo '<tr>';
				echo '	<th>'.__('N').'</th>';
				echo '	<th>Utente</th>';
				echo '	<th>'.__('Name').'</th>';
				echo '	<th>'.__('Conf').'</th>';
				echo '	<th>'.__('PrezzoUnita').'</th>';
				echo '	<th>'.__('Prezzo/UM').'</th>';
				echo '	<th style="text-align:center;">'.__('StoreroomArticleJustBooked').'</th>';
				echo '	<th>'.__('Importo').'</th>';
				echo '	<th>'.__('Created').'</th>';
				echo '	<th class="actions">'.__('Actions').'</th>';
				echo '</tr>';
				echo '</thead>';

			}
			else 
				$count++;
	

			if($result['Article']['supplier_organization_id']!=$supplier_organization_id_old) {
				echo '<tr>';
				echo '<td colspan="10" class="trGroup">'.__('Supplier').': '.$result['SuppliersOrganization']['SuppliersOrganization']['name'];
				echo '</td>';
				echo '</tr>';
			}
		
		echo '<tr>';
			echo '<td>'.($count).'</td>';
			echo '<td>'.$result['User']['name'].'</td>';
			echo '<td>'; 
				if($result['Storeroom']['stato']=='LOCK') echo '<span class="stato_lock"></span> ';
				echo $result['Storeroom']['name'].'</td>';
			echo '<td>'.$this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']).'</td>';
			echo '<td>'.$result['Storeroom']['prezzo_e'].'</td>';
			echo '<td>'.$this->App->getArticlePrezzoUM($result['Article']['prezzo'], $result['Article']['qta'], $result['Article']['um'], $result['Article']['um_riferimento']).'</td>';
	
			echo '<td style="text-align:center;">';
			if($result['Storeroom']['qta']==1)
				echo $result['Storeroom']['qta'];
			else
				echo $result['Storeroom']['qta'];
			echo '</td>';

			echo '<td>'.$this->App->getArticleImporto($result['Storeroom']['prezzo'], $result['Storeroom']['qta']).'</td>';
			echo '<td style="white-space: nowrap;">'.$this->App->formatDateCreatedModifier($result['Storeroom']['created']).'</td>';
			echo '<td class="actions-table-img">';
			if($result['Delivery']['isToStoreroomPay']=='Y') {
				echo '<span class="label label-info">'.__('StoreroomArticleInRequestPaymentShort').'</span>';
			}
			else {
				if($result['SuppliersOrganization']['IsReferente']=='Y' || $isUserCurrentStoreroom) 
					/*
					 * edit riporta l'artixcolo in dispensa come delete
					 * echo $this->Html->link(null, array('action' => 'edit', $result['Storeroom']['id']),array('class' => 'action actionEdit','title' => __('Edit')));
					*/
					 echo $this->Html->link(__('CartsToStoreroom'), ['action' => 'index_to_users', $result['Storeroom']['id']], 
								['confirm' => "Sei sicuro di voler rimettere in dispensa l'articolo associato ora ad un gasista?", 'class' => 'btn btn-primary', 'title' => __('CartsToStoreroom')]);			
			}
			echo '</td>';
		echo '</tr>';
	
		$delivery_id_old=$result['Storeroom']['delivery_id'];
		$supplier_organization_id_old=$result['Article']['supplier_organization_id'];
		endforeach; 
		
		echo '</table></div>';

	}
	else 
	if($FilterStoreroomGroupBy=='USERS') {
		$delivery_id_old = 0;
		$user_id_old = 0;
		$count=0;
		foreach ($results as $i => $result): 
	
			if($result['Storeroom']['delivery_id']!=$delivery_id_old) {
				$count=1;
				if($i>0) echo '</table>';
				
				echo "<h2>".__('Delivery')." ";
				if($result['Delivery']['sys']=='N')
					echo $result['Delivery']['luogoData'];
				else
					echo $result['Delivery']['luogo'];
				echo '</h2>';
				
				if($result['Delivery']['isToStoreroom']=='N')
						echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "La consegna non è abilitata per gestire la dispensa!")); 

				echo '<div class="table-responsive"><table class="table table-hover">';
				echo '<thead>';
				echo '<tr>';
				echo '<th>'.__('N').'</th>';
				echo '<th>'.__('Supplier').'</th>';
				echo '<th>'.__('Name').'</th>';
				echo '<th>'.__('Conf').'</th>';
				echo '<th>'.__('PrezzoUnita').'</th>';
				echo '<th>'.__('Prezzo/UM').'</th>';
				echo '<th>'.__('qta').'</th>';
				echo '<th>'.__('Importo').'</th>';
				echo '<th>'.__('Created').'</th>';
				echo '<th class="actions">'.__('Actions').'</th>';
				echo '</tr>';
				echo '<thead>';
			
			}
			else 
				$count++;
	
		
			if($result['Storeroom']['user_id']!=$user_id_old) {
				echo '<tr>';
				echo '<td colspan="10" class="trGroup">Utente: '.$result['User']['name'];
				echo '</td>';
				echo '</tr>';
			}
			echo '<tr>';
			echo '<td>'.($count).'</td>';
			echo '<td>';
			echo $result['SuppliersOrganization']['SuppliersOrganization']['name'].'</td>';
			echo '<td>';
				if($result['Storeroom']['stato']=='LOCK') echo '<span class="stato_lock"></span> ';
				echo $result['Storeroom']['name'].'</td>';
			echo '<td>'.$this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']).'</td>';
			echo '<td>'.$result['Storeroom']['prezzo_e'].'</td>';
			echo '<td>'.$this->App->getArticlePrezzoUM($result['Article']['prezzo'], $result['Article']['qta'], $result['Article']['um'], $result['Article']['um_riferimento']).'</td>';
			
			echo '<td>';
			if($result['Storeroom']['qta']==1)
				echo $result['Storeroom']['qta'].' acquistato';
			else
				echo $result['Storeroom']['qta'].' acquistati';
			echo '</td>';
		
			echo '<td>'.$this->App->getArticleImporto($result['Storeroom']['prezzo'], $result['Storeroom']['qta']).'</td>';
			echo '<td style="white-space: nowrap;">'.$this->App->formatDateCreatedModifier($result['Storeroom']['created']).'</td>';
			echo '<td class="actions-table-img">';

			/*
			 * edit riporta l'artixcolo in dispensa come delete
			 * echo $this->Html->link(null, array('action' => 'edit', $result['Storeroom']['id']),array('class' => 'action actionEdit','title' => __('Edit')));
			*/
			 echo $this->Html->link(__('CartsToStoreroom'), ['action' => 'index_to_users', $result['Storeroom']['id']], 
						['confirm' => "Sei sicuro di voler rimettere in dispensa l'articolo associato ora ad un gasista?", 'class' => 'btn btn-primary', 'title' => __('CartsToStoreroom')]);	
			echo '</td>';
		echo '</tr>';

		$delivery_id_old=$result['Storeroom']['delivery_id'];
		$user_id_old=$result['Storeroom']['user_id'];
		endforeach;
		echo '</table></div>';	

	}
} //  if(empty($results ))
else 
	echo $this->element('boxMsg', ['class_msg' => 'message', 'msg' => "Non ci sono ancora articoli associati alla dispensa"]);	
echo '</div>';
?>