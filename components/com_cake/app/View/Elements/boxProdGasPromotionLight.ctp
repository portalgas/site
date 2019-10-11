<?php
echo '<div class="table-responsive"><table class="table table-hover">';
echo '<tr>';
/*
echo '	<td>';
if(!empty($results['ProdGasPromotion']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.prod_gas_promotions').DS.$results['ProdGasPromotion']['supplier_id'].DS.$results['ProdGasPromotion']['img1'])) {
	echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.prod_gas_promotions').'/'.$results['ProdGasPromotion']['supplier_id'].'/'.$results['ProdGasPromotion']['img1'].'" />';
}			
echo '</td>';
*/
echo '	<td>';
echo $results['ProdGasPromotion']['name'];
echo '</td>';	
echo '	<td><b>'.__('DataInizio').'</b><br />';
echo $this->Time->i18nFormat($results['ProdGasPromotion']['data_inizio'],"%A %e %B %Y");
echo '	</td>';
echo '	<td><b>'.__('DataFine').'</b><br /> ';
echo $this->Time->i18nFormat($results['ProdGasPromotion']['data_fine'],"%A %e %B %Y");
echo '	</td>';
echo '	<td><b>'.__('Importo_scontato').'</b><br />';
echo '<span style="text-decoration: line-through;">'.$results['ProdGasPromotion']['importo_originale_e'].'</span><br />'.$results['ProdGasPromotion']['importo_scontato_e'];
echo '	</td>';
echo '</tr>';
echo '</table></div>';
?>