<?php
$this->App->d($results);

$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('ProdGasSupplier home'),array('controller' => 'ProdGasSuppliers', 'action' => 'index'));
$this->Html->addCrumb(__('ProdGasMonitoringArticles'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));


echo '<div class="organizations">';

if(count($results)>0) {
	?>
	<div class="table-responsive"><table class="table">
	<tr>
		<th><?php echo __('N');?></th>
		<th colspan="2"><?php echo __('Name');?></th>
		<th style="width:5px;"></th>
		<th><?php echo __('ProdGasSupplierArticlesOrganization');?></th>
		<th style="width:5px;"></th>
		<th><?php echo __('ProdGasSupplierArticlesOrdersOrganization');?></th>
	</tr>
	<?php
	foreach ($results as $organizationResult) {

		echo '<tr>';	
		echo '<td class="trGroup" colspan="7">'.$organizationResult['Organization']['name'].'</td>';	
		echo '</tr>';
		
		unset($organizationResult['Organization']);

		if(!empty($organizationResult)) {
			$i=0;
			foreach($organizationResult as  $result) {
				
				if(isset($result['ProdGasArticle'])) {
					$i++;
					
					$result = $result['ProdGasArticle'];
					/*
					echo "<pre>";
					print_r($result);
					echo "</pre>";
					*/
					
					echo '<tr class="view">';
					
					echo '<td>'.$i.'</td>';
	
					echo '<td>';
					if(!empty($result['ProdGasArticle']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.prod_gas_article').DS.$result['ProdGasArticle']['organization_id'].DS.$result['ProdGasArticle']['img1'])) {
						echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.prod_gas_article').'/'.$result['ProdGasArticle']['organization_id'].'/'.$result['ProdGasArticle']['img1'].'" />';	
					}
					echo '</td>';
					echo '<td>';
					echo $result['ProdGasArticle']['name'];
					echo '</td>';	
					echo '<td style="';
					if(isset($result['Article']))
						echo 'background-color:red;';
					else {
						if(!$result['article_found'])
							echo 'background-color:yellow;';
						else
							echo 'background-color:green;';
					}
					echo '"></td>';
					echo '<td>';
					if(isset($result['Article']['Diff'])) {
						foreach($result['Article']['Diff'] as $field => $value) {
							echo '<b>'.__(ucfirst($field)).'</b> '.$value.'<br />';
						}
					}
					else {
						if(!$result['article_found']) {
							echo "articolo non presente!";
						}
					}	
					echo '</td>';
					
					if(isset($result['ArticlesOrder'])) {
					
						echo '<td style="background-color:red;"></td>';	
										
						echo '<td>';	
						echo '<div class="table-responsive"><table class="table">';
						foreach($result['ArticlesOrder'] as $articlesOrderResult) {
							echo '<tr>';
							echo '<td>';
							echo $articlesOrderResult['Delivery']['luogoData'];
							echo '</td>';
							echo '<td>';
							foreach($articlesOrderResult['Diff'] as $field => $value) {
								echo '<b>'.__(ucfirst($field)).'</b> '.$value.'<br />';
							}	
							echo '</td>';
							echo '</tr>';					
						}
						echo '</table></div>';
						echo '</td>';					
					}
					else {
						if($result['articles_order_found'])
							echo '<td style="background-color:red;"></td>';	
						else 
							echo '<td></td>';
						echo '<td></td>';	
					}
					echo '</tr>';
				} // end if/isset($result['ProdGasArticle'])
			} // loop foreach($organizationResult as $numResult => $result) 
			
			if($i==0) {
				echo '<tr>';	
				echo '<td colspan="7">';
				echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "Tutti gli articoli sono sincronizzati con quelli del GAS"));			
				echo '</td>';	
				echo '</tr>';			
			}
			
		} // if(!empty($organizationResult)) 
		else {
			echo '<tr>';	
			echo '<td colspan="7">';
			echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "Tutti gli articoli sono sincronizzati con quelli del GAS"));			
			echo '</td>';	
			echo '</tr>';	
		}
	} // loop Organizations
	
	echo '</table></div>';			
} 
else // if(count($articlesResults)>0)
	echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "Non ci sono articoli del produttore"));
echo '</div>';
?>	