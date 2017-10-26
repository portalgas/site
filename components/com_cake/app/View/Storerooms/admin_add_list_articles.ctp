<?php
$i = 0;
if(!empty($results)) {
?>
	<div class="panel-group">
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
			foreach ($results as $i => $result):
			
				if($storeroom_id==$result['Storeroom']['id'])
					echo '<tr class="view" style="background-color:yellow;">';
				else	
					echo '<tr class="view">';
			?>
			
				<td><a action="articles-<?php echo $result['Article']['id']; ?>" class="actionTrView openTrView" href="#" title="<?php echo __('Href_title_expand');?>"></a></td>
				<td><?php echo ($i+1);?></td>
				<?php
				if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
					echo '<td>'.$result['Article']['codice'].'</td>';
				
				echo '<td>';
				if(!empty($result['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$result['Article']['organization_id'].DS.$result['Article']['img1'])) {
					echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$result['Article']['organization_id'].'/'.$result['Article']['img1'].'" />';
				}		
				echo '</td>';				
				?>
				<td><?php echo $result['Article']['name']; ?>&nbsp;
					 <?php if(!empty($result['Article']['nota'])) echo '<div class="small">'.strip_tags($result['Article']['nota']).'</div>'; ?>
				</td>
				<td><?php // Conf
					if($result['Article']['qta']>0)
						echo $this->App->getArticleConf($result['Article']['qta'], $this->App->traslateEnum($result['Article']['um']));?>
				</td>
				<td style="white-space: nowrap;"><?php echo $result['Storeroom']['prezzo'].'&nbsp;&euro;'; // Prezzo unità del prezzo in dispensa ?>
				</td>
				<td style="text-align:center;"><?php echo $result['Storeroom']['qta'];?></td>
				<td><?php echo $this->Form->input('qta',array('label'=>false,'name'=>'data[Storeroom]['.$result['Storeroom']['id'].'][Qta]','value' => '', 'class' => 'qta_storeroom', 'tabindex'=>($i+1)));?></td>
			</tr>
			<tr class="trView" id="trViewId-<?php echo $result['Article']['id'];?>">
				<td colspan="2"></td>
				<td colspan="<?php echo ($user->organization['Organization']['hasFieldArticleCodice']=='Y') ? '7' :'6';?>" id="tdViewId-<?php echo $result['Article']['id'];?>"></td>
			</tr>
		<?php 
		endforeach; 
			
		echo '</table></div>';
	}
	else   // 	if(!empty($results)
		echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "Per il produttore scelto non ci sono articoli nella dispensa"));
	?>
	
		  </div>
		</div>
	  </div>
	  <div class="panel panel-primary">
		<div class="panel-heading">
		  <h4 class="panel-title">
			<a data-toggle="collapse" data-parent="#accordion" href="#collapse2"><i class="fa fa-lg fa-plus" aria-hidden="true"></i> Elenco degli articoli attivi ancora da associare alla dispensa</a>
		  </h4>
		</div>
		<div id="collapse2" class="panel-collapse collapse">
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
			foreach ($articles as $ii => $article):
			?>
			<tr class="view">
				<td><?php echo ($ii+1);?></td>
				<?php
				if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
					echo '<td>'.$article['Article']['codice'].'</td>';
				
				
				echo '<td>';
				if(!empty($article['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$article['Article']['organization_id'].DS.$article['Article']['img1'])) {
					echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$article['Article']['organization_id'].'/'.$article['Article']['img1'].'" />';
				}		
				echo '</td>';				
				?>				
				<td><?php echo $article['Article']['name']; ?>&nbsp;
					 <?php if(!empty($article['Article']['nota'])) echo '<div class="small">'.strip_tags($article['Article']['nota']).'</div>'; ?>
				</td>
				<td><?php echo $this->App->getArticleConf($article['Article']['qta'], $this->App->traslateEnum($article['Article']['um']));?>
				</td>
				<td style="white-space: nowrap;"><?php echo $article['Article']['prezzo_e']; // Prezzo unità ?>
				</td>
				<td style="white-space: nowrap;"><?php // Prezzo/UM
					echo $this->App->getArticlePrezzoUM($article['Article']['prezzo'], $article['Article']['qta'], $article['Article']['um'], $article['Article']['um_riferimento']);?>
				</td>						
				<td><?php echo $this->Form->input('qta',array('label'=>false,'name'=>'data[Article]['.$article['Article']['id'].'][Qta]','value' => 0, 'class' => 'qta_article', 'tabindex'=>($i+1)));?></td>
			</tr>
		<?php endforeach; ?>
			</table></div>
		
		  </div>
		</div>
	  </div>
	</div> <!-- panel-group -->