<?php
echo '<div class="panel-group">';

if(empty($results) && empty($articles)) {
	if($isSupplierOrganizationDesTitolare)
		echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "Per il produttore scelto non ci sono articoli!"));
	else {
		$msg = "Gli articoli del produttore scelto appartengono a \"".$ownOrganizationResults['OwnOrganization']['name']."\" che fa parte del DES \"".$ownOrganizationResults['De']['name']."\"";
		echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => $msg));
	}	
}	
else {
$i = 0;
if(!empty($results)) {
?>
	  <div class="panel panel-primary">
		<div class="panel-heading">
		  <h4 class="panel-title">
			<a data-toggle="collapse" data-parent="#accordion" href="#collapse1"><i class="fa fa-lg fa-minus" aria-hidden="true"></i> Elenco degli articoli gi&agrave; in dispensa (<?php echo count($results);?>)</a>
		  </h4>
		</div>
		<div id="collapse1" class="panel-collapse collapse in">
		  <div class="panel-body">
		  
		 
		
			<div class="table-responsive"><table class="table table-hover">
			<tr>
					<th></th>
					<th><?php echo __('N');?></th>
					<?php
					if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
						echo '<th>'.__('codice').'</th>';
					?>
					<th colspan="2">Nome prodotto</th>
					<th><?php echo __('Conf.');?></th>
					<th><?php echo __('Prezzo<br />unità');?></th>
					<th style="text-align:center;"><?php echo __('Quantità<br />in dispensa');?></th>	
					<th><?php echo __('Modifica<br />quantità<br />in dispensa');?></th>			
			</tr>
			<?php
			foreach ($results as $i => $result) {
			
				if($storeroom_id==$result['Storeroom']['id'])
					echo '<tr class="view" style="background-color:yellow;">';
				else	
					echo '<tr class="view">';
			
			
				echo '<td><a action="articles-'.$result['Article']['organization_id'].'_'.$result['Article']['id'].'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a></td>';
				echo '<td>'.($i+1).'</td>';
				
				if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
					echo '<td>'.$result['Article']['codice'].'</td>';
				
				echo '<td>';
				if(!empty($result['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$result['Article']['organization_id'].DS.$result['Article']['img1'])) {
					echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$result['Article']['organization_id'].'/'.$result['Article']['img1'].'" />';
				}		
				echo '</td>';				
				echo '<td>'.$result['Article']['name'].'&nbsp;';
				if(!empty($result['Article']['nota'])) echo '<div class="small">'.strip_tags($result['Article']['nota']).'</div>';
				echo '</td>';
				echo '<td>'; // Conf
				if($result['Article']['qta']>0)
					echo $this->App->getArticleConf($result['Article']['qta'], $this->App->traslateEnum($result['Article']['um']));
				echo '</td>';
				echo '<td style="white-space: nowrap;">'.$result['Storeroom']['prezzo'].'&nbsp;&euro;'; // Prezzo unità del prezzo in dispensa 
				echo '</td>';
				echo '<td style="text-align:center;">'.$result['Storeroom']['qta'].'</td>';
				echo '<td>'.$this->Form->input('qta', ['label' => false, 'name' => 'data[Storeroom]['.$result['Storeroom']['id'].'][qta]', 'value' => '', 'class' => 'qta_storeroom', 'tabindex'=>($i+1)]).'</td>';
			echo '</tr>';
			echo '<tr class="trView" id="trViewId-'.$result['Article']['organization_id'].'_'.$result['Article']['id'].'">';
			echo '<td colspan="2"></td>';
			$colspan = ($user->organization['Organization']['hasFieldArticleCodice']=='Y') ? '7' :'6';
			echo '<td colspan="'.$colspan.'" id="tdViewId-'.$result['Article']['organization_id'].'_'.$result['Article']['id'].'"></td>';
			echo '</tr>';
		}
			
		echo '</table></div>';
	?>
	
		  </div> <!-- panel-body -->
		</div> <!-- collapse1 -->
	  </div> <!-- panel panel-primary -->
<?php 
	}
	else   // 	if(!empty($results)
		echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "Per il produttore scelto non ci sono articoli nella dispensa"));

	if(!empty($articles)) {
?>	  
	  <div class="panel panel-primary">
		<div class="panel-heading">
		  <h4 class="panel-title">
			<a data-toggle="collapse" data-parent="#accordion" href="#collapse2"><i class="fa fa-lg fa-plus" aria-hidden="true"></i> Elenco degli articoli attivi ancora da associare alla dispensa</a>
		  </h4>
		</div>
		<div id="collapse2" class="panel-collapse collapse <?php echo (!empty($results)?'': 'in');?>">
		  <div class="panel-body">

		
			<div class="table-responsive"><table class="table table-hover">
			<tr>
				<th><?php echo __('N');?></th>
				<?php
				if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
					echo '<th>'.__('codice').'</th>';				
				?>
				<th colspan="2">Nome prodotto</th>
				<th><?php echo __('Conf.');?></th>
				<th><?php echo __('Prezzo<br />unità');?></th>
				<th><?php echo __('Prezzo/UM');?></th>
				<th><?php echo __('Quantità<br />da inserire<br />in dispensa');?></th>			
			</tr>
			<?php
			foreach ($articles as $ii => $article) {
			
				echo '<tr class="view">';
				echo '<td>'.($ii+1).'</td>';
				
				if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
					echo '<td>'.$article['Article']['codice'].'</td>';
				
				
				echo '<td>';
				if(!empty($article['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$article['Article']['organization_id'].DS.$article['Article']['img1'])) {
					echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$article['Article']['organization_id'].'/'.$article['Article']['img1'].'" />';
				}		
				echo '</td>';				
				echo '<td>'.$article['Article']['name'].'&nbsp;';
				if(!empty($article['Article']['nota'])) echo '<div class="small">'.strip_tags($article['Article']['nota']).'</div>';
				echo '</td>';
				echo '<td>'.$this->App->getArticleConf($article['Article']['qta'], $this->App->traslateEnum($article['Article']['um']));
				echo '</td>';
				echo '<td style="white-space: nowrap;">'.$article['Article']['prezzo_e']; // Prezzo unità 
				echo '</td>';
				echo '<td style="white-space: nowrap;">'; // Prezzo/UM
				echo $this->App->getArticlePrezzoUM($article['Article']['prezzo'], $article['Article']['qta'], $article['Article']['um'], $article['Article']['um_riferimento']);
				echo '</td>';
				echo '<td>'.$this->Form->input('qta', ['label' => false, 'name' => 'data[Article]['.$article['Article']['organization_id'].'-'.$article['Article']['id'].'][qta]', 'value' => 0, 'class' => 'qta_article', 'tabindex'=>($i+1)]).'</td>';
				echo '</tr>';
			} 
			?>
			</table></div>
		
		  </div>
		</div>
	  </div> <!-- panel panel-primary -->
	  <?php
		}
		else   // 	if(!empty($articles)
			echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "Per il produttore scelto non ci sono articoli da inserire in dispensa"));

	echo '</div> <!-- panel-group -->';
}