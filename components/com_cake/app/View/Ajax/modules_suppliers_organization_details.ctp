<div class="gas_modules">
<?php
 if (!empty($results)):?>	
	<h3>Dati produttore</h3>
	<ul class="list">
		<li><?php echo $results['SuppliersOrganization']['name'];?>/<?php echo $results['Supplier']['descrizione'];?></li>
		<li><?php 
			if(!empty($results['Supplier']['indirizzo'])) echo $results['Supplier']['indirizzo'].'&nbsp;';
			if(!empty($results['Supplier']['localita']))  echo $results['Supplier']['localita'].'&nbsp;';
			if(!empty($results['Supplier']['provincia'])) echo '('.$results['Supplier']['provincia'].')&nbsp;';
			if(!empty($results['Supplier']['cap'])) echo $results['Supplier']['cap'].'&nbsp;';
			?>
		</li>
		<li><?php 
			if(!empty($results['Supplier']['telefono'])) echo $results['Supplier']['telefono'].'&nbsp;';
			if(!empty($results['Supplier']['mail']))  echo '<a class="link_mailto" title="'.__('Email send').'" target="_blank" href="mailto:'.$this->App->getPublicMail($user,$results['Supplier']['mail']).'"></a>&nbsp;';
			if(!empty($results['Supplier']['www']))  echo '<a class="link_www" target="_blank" href="'.$this->App->traslateWww($results['Supplier']['www']).'"><img alt="Vai al sito del produttore" src="'.Configure::read('App.img.cake').'/icons/16x16/world_link.png" /></a>&nbsp;';
			?>
		</li>
	</ul>

	<?php 
	if(!empty($results['SuppliersOrganizationsReferent'])) { ?>
	<h3>Referenti</h3>
		<ul class="list">
			<?php 
			foreach($results['SuppliersOrganizationsReferent'] as $referent) {
				echo '<li>';
				echo $referent['User']['name'].'&nbsp;';
				if(!empty($referent['User']['email']))  echo '<a class="link_mailto" target="_blank" href="mailto:'.$this->App->getPublicMail($user,$referent['User']['email']).'"></a>&nbsp;';
				echo '</li>';			
			}
			?>
		</ul>
	<?php 
	}
	?>


	<h3>Consegne</h3>
	<?php 
	if(!empty($results['Order'])) { ?>
		<ul class="list">
			<?php 
			foreach($results['Order'] as $order) {
				if(!empty($order['Delivery']['data']) && $order['Delivery']['data']!=Configure::read('DB.field.date.empty')) 
					$data = $this->Time->i18nFormat($order['Delivery']['data'],"%A %e %B %Y");
				else
					$data = "";
				if(!empty($order['data_inizio']) && $order['data_inizio']!=Configure::read('DB.field.date.empty')) 
					$data_inizio = $this->Time->i18nFormat($order['data_inizio'],"%e %B");
				else
					$data_inizio = "";
					
				if(!empty($order['data_fine']) && $order['data_fine']!=Configure::read('DB.field.date.empty')) 
					$data_fine = $this->Time->i18nFormat($order['data_fine'],"%e %B");
				else
					$data_fine = "";
				
				echo '<li>';
				echo $data.'&nbsp;';
				echo '  <span class="consegna hasTip" title="'.$order['Delivery']['luogo'];
				if(!empty($order['Delivery']['nota'])) echo ' - Nota: '.$order['Delivery']['nota'];
				echo '"><img alt="Note" src="'.Configure::read('App.img.cake').'/icons/16x16/magnifier_zoom_in.png" /></span><br />';
				
				if($order['state_code']=='OPEN') {
					if($order['dayDiffToDateFine'] >= Configure::read('GGOrderCloseNext')) {
						$str .= '<span style="background-color:#999999;color:yellow;">Si sta chiudendo! ';
						if($order['dayDiffToDateFine']==0) $str .= 'oggi';
						else $str .= 'Tra&nbsp;'.(-1 * $order['dayDiffToDateFine']).'&nbsp;gg';
						$str .= '</span>';
					}
					else
						echo '<span style="color:green;">Aperto</span>, dal '.$data_inizio.' al '.$data_fine;							
				}	
				else
				if($order['state_code']=='OPEN-NEXT')
					echo '<span style="color:#000000;">Aprira&grave; il '.$data_inizio.'</span>';				
				if($order['state_code']>'CLOSE')
					echo '<span style="color:red;">Chiuso</span>';
				
				echo '</li>';			
			}
			?>		
	<?php 
	}
	else { ?>
		<ul class="list">
			<li>Non ci sono consegne aperte</li>
		</ul>
	<?php
	}
	?>
<?php endif; ?>
</div>