<div class="organizations_pays">
	<h2 class="ico-organizations">
		<?php echo __('Organizations');?>
		<div class="actions-img">
			<ul>
				<li><?php echo $this->Html->link(__('List Organizations'), array('controller' => 'Organizations', 'action' => 'index'),array('class' => 'action actionReload','title' => __('List Organizations'))); ?></li>
			</ul>
		</div>
	</h2>
	
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th rowspan="2"><?php echo __('Id');?></th>
			<th rowspan="2" colspan="4"><?php echo __('Name');?></th>	
			<th style="text-align:center;" colspan="3">Pagamento</th>
			<th rowspan="2" style="text-align:center;"><?php echo __('Year');?></th>
			<th rowspan="2" style="text-align:center;">Produttori</th>
			<th rowspan="2" style="text-align:center;">Articoli</th>
			<th rowspan="2" style="text-align:center;">Ordini effettuati</th>
			<th rowspan="2" style="text-align:center;">Utenti attivi</th>
			<th rowspan="2" style="text-align:center;">Utenti default *</th>
			<th rowspan="2"><?php echo __('Importo');?></th>
			<th rowspan="2"></th>			
	</tr>
	<tr>
			<th><?php echo __('Riferimenti');?></th>
			<th><?php echo __('Localit&agrave;');?></th>
			<th><?php echo __('Coordinate');?></th>
	</tr>
	<?php	
	$tot_users = 0;
	$tot_suppliers_organizations = 0;
	$tot_articles = 0;
	$tot_orders = 0;
	$tot_importo = 0;
	$year_old=0;
	foreach ($results as $result):
	
		if(isset($result['OrganizationsPay']['id'])) {
		
			/*
 			 * totali
			 */
			if($year_old>0 && ($year_old != $result['OrganizationsPay']['year'])) {
				echo '<tr class="trGroup" valign="top">';
				echo '<th style="text-align:center;" colspan="10"><h1>'.$result['OrganizationsPay']['year'].'</h1></th>';		
				echo '<th style="text-align:center;';
				if($tot_suppliers_organizations==0) echo 'background-color:red;color:#fff;';
				echo '">';
				echo number_format($tot_suppliers_organizations,0,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'</th>';

				echo '<th style="text-align:center;';
				if($tot_articles==0) echo 'background-color:red;color:#fff;';
				echo '">';
				echo number_format($tot_articles,0,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'</th>';	

				echo '<th style="text-align:center;';
				if($tot_orders==0) echo 'background-color:red;color:#fff;';
				echo '">';
				echo number_format($tot_orders,0,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
				echo '</th>';

				echo '<th style="text-align:center;';
				if($tot_users==0) echo 'background-color:red;color:#fff;';
				echo '">';
				echo number_format($tot_users,0,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'</th>';	

				echo '<th>'.number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</th>';
				echo '<th></th>';

				echo '</tr>';	
							
				$tot_users = 0;
				$tot_suppliers_organizations = 0;
				$tot_articles = 0;
				$tot_orders = 0;
				$tot_importo = 0;					
			}

		
			echo '<tr valign="top">';
			echo '<td>';
			if($result['OrganizationsPay']['id']!=0)
				echo $result['OrganizationsPay']['organization_id']; 
			echo '</td>';
			
			echo '<td>';
			echo ' <img width="50" class="userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$result['Organization']['img1'].'" alt="'.$result['Organization']['name'].'" /> ';	
			echo '</td>';

			echo '<td>';
			echo $result['Organization']['name'].' ('.$result['Organization']['id'].')'; 
				if(!empty($result['Organization']['j_seo'])) echo '<div class="small">SEO '.$result['Organization']['j_seo'].'</div>';
			echo '</td>';
			
			echo '<td>';
			echo $result['OrganizationsPay']['beneficiario_pay'];
			echo '</td>';
			
			echo '<td>';
			echo $result['OrganizationsPay']['type_pay'];
			echo '</td>';

			if($result['OrganizationsPay']['id']!=0) {
				echo '<td></td>';
				echo '<td></td>';
				echo '<td></td>'; 
			}
			else {
				echo '<td>'; 
						if(!empty($result['Organization']['payMail'])) echo 'Mail '.h($result['Organization']['payMail']).'<br />';
						if(!empty($result['Organization']['payContatto'])) echo 'Contatto '.h($result['Organization']['payContatto']).'<br />';
						if(!empty($result['Organization']['payIntestatario'])) echo 'Intestatario '.h($result['Organization']['payIntestatario']).'<br />';
				echo '</td>';
				echo '<td>';
					   if(!empty($result['Organization']['payIndirizzo'])) echo $result['Organization']['payIndirizzo'].'&nbsp;';
					   if(!empty($result['Organization']['payCitta'])) echo $result['Organization']['payCitta'].'&nbsp;';
					   if(!empty($result['Organization']['payCap'])) echo $result['Organization']['payCap'].'&nbsp;';
					   if(!empty($result['Organization']['payProv'])) echo '('.h($result['Organization']['payProv']).')'; 
				echo '</td>';
				echo '<td>'; 
						if(!empty($result['Organization']['payCf'])) echo 'CF '.h($result['Organization']['payCf']).'<br />';
						if(!empty($result['Organization']['payPiva'])) echo 'P.iva '.h($result['Organization']['payPiva']);
				echo '</td>';			
			}
					
			echo '<td style="text-align:center;">';
			if(empty($result['OrganizationsPay']['year']))
				$result['OrganizationsPay']['year'] = '-';
			echo $result['OrganizationsPay']['year']; 
			echo '</td>';
			
			echo '<td style="text-align:center;';
			if($result['OrganizationsPay']['tot_suppliers_organizations']==0) echo 'background-color:red;color:#fff;';
			echo '">';
			echo number_format($result['OrganizationsPay']['tot_suppliers_organizations'],0,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')); 
			echo '</td>';	
			
			echo '<td style="text-align:center;';
			if($result['OrganizationsPay']['tot_articles']==0) echo 'background-color:red;color:#fff;';
			echo '">';
			echo number_format($result['OrganizationsPay']['tot_articles'],0,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')); 
			echo '</td>';	
						
			echo '<td style="text-align:center;';
			if($result['OrganizationsPay']['tot_orders']==0) echo 'background-color:red;color:#fff;';
			echo '">';
			echo number_format($result['OrganizationsPay']['tot_orders'],0,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')); 
			echo '</td>';	
			
			echo '<td style="text-align:center;';
			if($result['OrganizationsPay']['tot_users']==0) echo 'background-color:red;color:#fff;';
			echo '">';
			echo number_format($result['OrganizationsPay']['tot_users'],0,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
			echo '</td>';	

			echo '<td style="text-align:center;">'.$result['OrganizationsPay']['users_default'].'</td>';
			
			/*
			 * calcolare 
			 */
			echo '<td>';
			if($result['OrganizationsPay']['importo']==0 && $result['OrganizationsPay']['id']==0) 
				$result['OrganizationsPay']['importo'] = (Configure::read('costToUser') * (float)$result['OrganizationsPay']['tot_users']);
			
			if($result['OrganizationsPay']['importo'] > Configure::read('OrganizationPayImportMax'))
				$result['OrganizationsPay']['importo'] = Configure::read('OrganizationPayImportMax');
				
			echo number_format($result['OrganizationsPay']['importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
			if($result['OrganizationsPay']['importo'] == Configure::read('OrganizationPayImportMax')) echo ' <span>(max)</span>';
			echo '</td>';

			/*
			 * doc 
			 */
			echo '<td>';
			if($result['OrganizationsPay']['id']!=0) {
				if(file_exists(Configure::read('App.root').Configure::read('App.doc.upload.organizations.pays').DS.$result['OrganizationsPay']['year'].DS.$result['OrganizationsPay']['organization_id'].'.pdf')) {
					echo '<a target="_blank" href="'.Configure::read('App.server').Configure::read('App.web.doc.upload.organizations.pays').'/'.$result['OrganizationsPay']['year'].'/'.$result['OrganizationsPay']['organization_id'].'.pdf">';
					echo '<img alt="PDF" src="'.Configure::read('App.img.cake').'/minetypes/32x32/pdf.png"></a>';
				}
			}				
			echo '</td>';
		
		echo '</tr>';
		
		//if($result['OrganizationsPay']['id']==0) {
			$tot_users += $result['OrganizationsPay']['tot_users'];
			$tot_suppliers_organizations += $result['OrganizationsPay']['tot_suppliers_organizations'];
			$tot_articles += $result['OrganizationsPay']['tot_articles'];
			$tot_orders += $result['OrganizationsPay']['tot_orders'];
			$tot_importo += $result['OrganizationsPay']['importo'];
		//}	
		
		$year_old = $result['OrganizationsPay']['year'];
		
	} // end if(isset($result['OrganizationsPay']['id']))	
endforeach; 
	
/*
 * totali
 */
echo '<tr class="trGroup" valign="top">';
echo '<td></td>';
echo '<td></td>';
echo '<td></td>';
echo '<td></td>';
echo '<td></td>';
echo '<td></td>';
echo '<td></td>';
echo '<td></td>';
echo '<td></td>';		
echo '<th style="text-align:center;">'.$tot_suppliers_organizations.'</th>';	
echo '<th style="text-align:center;">'.number_format($tot_articles,0,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'</th>';
echo '<th style="text-align:center;">'.number_format($tot_orders,0,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'</th>';
echo '<th style="text-align:center;">'.number_format($tot_users,0,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'</th>';
echo '<th style="text-align:center;"></th>';
echo '<th>'.number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</th>';
echo '<th></th>';
echo '</tr>';		
	
echo '</table>';
echo '</div>';


echo '<div class="legenda legenda-ico-info">';
echo '(*) Dal totale utenti già sottratti';
echo '<ul>';
echo '<li>Assistente PortAlGas</li>';
echo '<li>eventuale Dispensa PortAlGas</li>';
echo '</ul>';
echo '</div>';