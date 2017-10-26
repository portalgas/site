<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Orders'),array('controller' => 'Orders', 'action' => 'index'));
if(isset($order_id) && !empty($order_id))
	$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$order_id));
$this->Html->addCrumb(__('Storeroom'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
/*
echo "<pre>";
print_r($results);
echo "</pre>";
*/
?>
<h2 class="ico-storeroom">
	<?php echo __('Storeroom');?>
</h2>

<div class="contentMenuLaterale">

<div class="legenda legenda-ico-info" style="float:none;">
Gli articoli associati all'utente "dispensa" sono stati inseriti in dispensa alla chiusura della consegna
</div>

<?php
include(Configure::read('App.root').Configure::read('App.component.base').DS.'View/ArticlesOrders/box_order_detail.ctp');
?>

<div class="table-responsive"><table class="table table-hover table-striped">
	<thead>
	<tr>
		<th><?php echo __('N');?></th>
		<th colspan="2"><?php echo __('Name');?></th>
		<th><?php echo __('Conf');?></th>
		<th><?php echo __('PrezzoUnita');?></th>
		<th><?php echo __('Prezzo/UM');?></th>
		<th><?php echo __('qta');?></th>
		<th><?php echo __('Importo');?></th>
		<th>Già in dispensa</th>
	</tr>
	</thead>
	<?php
	echo '<tbody>';
	
	$tot_qta = 0;
	$tot_importo = 0;
	foreach ($results as $numResult => $result): 

		if($result['Cart']['qta']==0)
			$qta = $result['Cart']['qta_forzato'];
		else
			$qta = $result['Cart']['qta'];
		

		echo '<tr>';
		echo '<td>'.($numResult+1).'</td>';
		echo '<td>';
		if(!empty($result['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$result['Article']['organization_id'].DS.$result['Article']['img1'])) {
			echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$result['Article']['organization_id'].'/'.$result['Article']['img1'].'" />';
		}		
		echo '</td>';		
		echo '<td>'.$result['ArticlesOrder']['name'].'</td>';
		echo '<td>'.$this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']).'</td>';
		echo '<td>'.$result['ArticlesOrder']['prezzo_e'].'</td>';
		echo '<td>'.$this->App->getArticlePrezzoUM($result['ArticlesOrder']['prezzo'], $result['Article']['qta'], $result['Article']['um'], $result['Article']['um_riferimento']).'</td>';
		echo '<td>'.$qta.'</td>';
		echo '<td>'.$this->App->getArticleImporto($result['ArticlesOrder']['prezzo'], $qta).'</td>';
		echo '<td title="'.__('Già in dispensa').'" class="stato_'.$this->App->traslateEnum($result['Cart']['inStoreroom']).'"></td>';
		echo '</tr>';
		
		$tot_qta = ($tot_qta + $qta);
		$tot_importo = ($tot_importo + ($result['ArticlesOrder']['prezzo'] * $qta));
	
	endforeach;
	
	echo '</tbody>';
	echo '</footer>';
	echo '<tr>';
	echo '<th></th>';
	echo '<th></th>';
	echo '<th></th>';
	echo '<th></th>';
	echo '<th></th>';
	echo '<th></th>';
	echo '<th>'.$tot_qta.'</th>';
	echo '<th>'.number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</th>';
	echo '<th></th>';
	echo '</tr>';	
	echo '</tbody>';
	echo '</table></div>';	

echo '</div>';

$options = [];
echo $this->MenuOrders->drawWrapper($order_id, $options);
?>