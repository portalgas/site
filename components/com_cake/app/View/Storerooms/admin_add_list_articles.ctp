<?php
$i = 0;

if(!empty($results)) {
?>
	<div class="box-open-close">
		<div class="barra-open-close close" id="articlesStoreroomsLabel">
			Elenco degli articoli gi&agrave; in dispensa
		</div>
	
		<div id="articlesStoreroomsList" style="display:none;">
		
			<table cellpadding="0" cellspacing="0">
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
					<th><?php echo __('Quantità<br />in dispensa');?></th>	
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
					echo '<img width="50" class="userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$result['Article']['organization_id'].'/'.$result['Article']['img1'].'" />';
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
				<td style="white-space: nowrap;"><?php echo $result['Storeroom']['prezzo'].' &euro;'; // Prezzo unità del prezzo in dispensa ?>
				</td>
				<td><?php echo $result['Storeroom']['qta'];?></td>
				<td><?php echo $this->Form->input('qta',array('label'=>false,'name'=>'data[Storeroom]['.$result['Storeroom']['id'].'][Qta]','value' => '', 'class' => 'qta_storeroom', 'size'=>3,'tabindex'=>($i+1)));?></td>
			</tr>
			<tr class="trView" id="trViewId-<?php echo $result['Article']['id'];?>">
				<td colspan="2"></td>
				<td colspan="<?php echo ($user->organization['Organization']['hasFieldArticleCodice']=='Y') ? '7' :'6';?>" id="tdViewId-<?php echo $result['Article']['id'];?>"></td>
			</tr>
		<?php endforeach; ?>
			</table>
		</div>
		
	</div>	
	<?php
	}
	else   // 	if(!empty($results)
		echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "Per il produttore scelto non ci sono articoli nella dispensa"));
	?>
	
	<div class="box-open-close">
		<div class="barra-open-close close" id="articlesLabel">
			Elenco degli articoli attivi ancora da associare alla dispensa
		</div>
			
		<div id="articlesList" style="display:none;">
		
			<table cellpadding="0" cellspacing="0">
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
					echo '<img width="50" class="userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$article['Article']['organization_id'].'/'.$article['Article']['img1'].'" />';
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
				<td><?php echo $this->Form->input('qta',array('label'=>false,'name'=>'data[Article]['.$article['Article']['id'].'][Qta]','value' => 0,'size'=>3,'class' => 'qta_article', 'tabindex'=>($i+1)));?></td>
			</tr>
		<?php endforeach; ?>
			</table>
		
		</div>

</div>

<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('#articlesStoreroomsLabel').click(function() {
		if(jQuery('#articlesStoreroomsList').css('display')=='none')  {
			jQuery('#articlesStoreroomsList').removeClass('close');
			jQuery('#articlesStoreroomsList').addClass('open');
			jQuery('#articlesStoreroomsList').show('slow');
		}	
		else {
			jQuery('#articlesStoreroomsList').removeClass('open');
			jQuery('#articlesStoreroomsList').addClass('close');
			jQuery('#articlesStoreroomsList').hide('slow');
		}
	});
	jQuery('#articlesLabel').click(function() {
		if(jQuery('#articlesList').css('display')=='none') {
			jQuery('#articlesLabel').removeClass('close');
			jQuery('#articlesLabel').addClass('open');
			jQuery('#articlesList').show('slow');
		}	
		else {
			jQuery('#articlesLabel').removeClass('open');
			jQuery('#articlesLabel').addClass('close');
			jQuery('#articlesList').hide('slow');
		}	
	});
});
<?php
if(!empty($results)) {
?>
jQuery(document).ready(function() {
	
	<?php
	/*
	* $storeroom_id valorizzati se chiamato da admin_index per modifica
	*/	
	if(!empty($storeroom_id)) {
	?>
		jQuery('#articlesStoreroomsLabel').removeClass('close');
		jQuery('#articlesStoreroomsLabel').addClass('open');
		jQuery('#articlesStoreroomsList').show('slow');
	<?php
	}
	else {
	?>
		jQuery('#articlesLabel').removeClass('close');
		jQuery('#articlesLabel').addClass('open');
		jQuery('#articlesList').show('slow');	
	<?php
	}
	?>	
	
});	
<?php
}
?>
</script>