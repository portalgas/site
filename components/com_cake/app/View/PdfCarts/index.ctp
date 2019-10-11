<?php 
echo '<div class="pdf-carts" id="ajaxContent">';

echo '<div class="well">'; 
echo '<form role="select" class="navbar-form" accept-charset="utf-8" method="get" id="pdfForm" action="'.$_SERVER['REQUEST_URI'].'">';
echo '<fieldset class="filter">'; 

echo '<div class="row">'; 
echo '<div class="col-md-6">';  
$options =  array('name' => 'supplier_organization_id', 'label' => false,
						'options' => $supplier_organizations, 
						'default' => $supplier_organization_id,
						'class'=> 'selectpicker chosen-select form-control', 'style' => 'width:350px;', 
						'data-live-search' => 'true');
echo $this->Form->input('supplier_organization_id',$options);
echo '</div>';  
echo '<div class="col-md-3">';          
	$options =  array('name' => 'year_id', 'label' => false,
						'options' => $years,
						'default' => $year_id,
						'class' => 'selectpicker form-control');
echo $this->Form->input('year_id',$options);
echo '</div>';  
echo '<div class="col-md-3">';          
echo $this->Form->button('Filtra', array('type' => 'submit', 'class' => 'btn btn-primary'));
echo '</div>';  
echo '</div>';  
echo '</fieldset>';
echo $this->Form->end(); 
echo '</div>';
         
$this->App->d($results);

if(!empty($results)) {
	
	echo '<div class="table">';
	echo '<table class="table table-hover">';
	
	$totStatOrders = 0;
	$statImportoTot = 0;
	foreach ($results as $result) {

		echo '<thead>';
		echo '<tr>';
		echo '<th style="background-color:#337ab7;color:#fff;">'.__('Delivery').'</th>';
		echo '<th colspan="6" style="background-color:#337ab7;color:#fff;">'.$result['StatDelivery']['luogoData'].'</th>';
		echo '</tr>';
		echo '</thead>';

		foreach($result['StatOrder'] as $statOrderResult) {
		
			$order_data_inizio = $this->Time->i18nFormat($statOrderResult['data_inizio'],"%e %B %Y"); 
			$order_data_fine = $this->Time->i18nFormat($statOrderResult['data_fine'],"%e %B %Y"); 
		
			echo '</tbody>';
			
			/*
			 * per ora non previsto se no troppi risultati
			 */
			if(empty($supplier_organization_id)) {
				echo '<tr>';
				echo '<td><b>'.__('Supplier Organization').'</b></td>';
				echo '<td>';
				if(!empty($statOrderResult['supplier_img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$statOrderResult['supplier_img1']))
					echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$statOrderResult['supplier_img1'].'" />';
				echo ' '.$statOrderResult['supplier_organization_name'];
				echo '</td>';
				echo '<td><b>'.__('Order').'</b></td>';	
				echo '<td colspan="2">'.__('DataInizioPast').' '.$order_data_inizio.'</td>';
				echo '<td colspan="2">'.__('DataFinePast').' '.$order_data_fine.'</td>';
				echo '</tr>';		
			}
			else {
				echo '<tr>';
				echo '<td colspan="3"><b>'.__('Order').'</b></td>';	
				echo '<td colspan="2">'.__('DataInizioPast').' '.$order_data_inizio.'</td>';
				echo '<td colspan="2">'.__('DataFinePast').' '.$order_data_fine.'</td>';
				echo '</tr>';				
			}

			echo '<tr>';
			echo '<th></th>';
			echo '<th>'.__('Article').'</th>';
			echo '<th style="text-align:center;">'.__('qta').'</th>';
			echo '<th style="text-align:center;">'.__('Package').'</th>';
			echo '<th style="text-align:center;">'.__('PrezzoUnita').'</th>';
			echo '<th style="text-align:center;">'.__('Prezzo/UM').'</th>';
			echo '<th style="text-align:center;">'.__('Importo').'</th>';
			echo '</tr>';
			
			$statCartImportoTot = 0; 
			foreach($statOrderResult['StatCart'] as $statCartResult) {
			
				$statCartImportoTot += $statCartResult['StatCart']['importo'];
				$statCartImporto = number_format($statCartResult['StatCart']['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
			
				echo '<tr>';
				echo '<td></td>';
				echo '<td>'.$statCartResult['StatArticlesOrder']['name'].'</td>';
				echo '<td style="text-align:center;">'.$statCartResult['StatCart']['qta'].'</td>';				
				echo '<td style="text-align:center;">'.$this->App->getArticleConf($statCartResult['StatArticlesOrder']['qta'], $statCartResult['StatArticlesOrder']['um']).'</td>';
				echo '<td style="text-align:center;">'.$statCartResult['StatArticlesOrder']['prezzo_e'].'</td>';
				echo '<td style="text-align:center;">'.$this->App->getArticlePrezzoUM($statCartResult['StatArticlesOrder']['prezzo'], $statCartResult['StatArticlesOrder']['qta'], $statCartResult['StatArticlesOrder']['um'], $statCartResult['StatArticlesOrder']['um_riferimento']).'</td>';
				echo '<td style="text-align:center;">'.$statCartImporto.'</td>';
				echo '</tr>';
			
			} // end loop StatCart

			$statImportoTot += $statCartImportoTot;
			$statCartImportoTot = number_format($statCartImportoTot,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
			
			echo '<tr>';
			echo '<th></th>';
			echo '<th></th>';
			echo '<th></th>';
			echo '<th></th>';
			echo '<th></th>';
			echo '<th></th>';
			echo '<th style="text-align:center;">'.$statCartImportoTot.'</th>';
			echo '</tr>';
			echo '</tbody>';
			
			$totStatOrders++;
			
		} // end loop StatOrder
	} // end loop StatDelivery

	if($totStatOrders>1) {
		$statImportoTot = number_format($statImportoTot,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
				
		echo '<tr>';
		echo '<th></th>';
		echo '<th></th>';
		echo '<th></th>';
		echo '<th></th>';
		echo '<th></th>';
		echo '<th></th>';
		echo '<th style="text-align:center;">'.$statImportoTot.'</th>';
		echo '</tr>';
	}
	
	echo '</table></div>';
	
	}
	else {
		if(empty($supplier_organization_id) || empty($year_id)) 
			$msg = "Valorizza i filtri di ricerca";
		else
			$msg = "Non ci sono acquisti archiviati";
		echo $this->element('boxMsgFrontEnd',array('class_msg' => 'notice', 'msg' => $msg));	
	}
echo '</div>';