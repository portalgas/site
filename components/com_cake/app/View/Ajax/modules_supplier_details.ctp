<div class="gas_modules">
<?php
 if (!empty($results)):?>	
	<h3>Dati produttore</h3>
	<ul class="list-unstyled">
		<li><?php echo $results['Supplier']['name'];
			if(!empty($results['Supplier']['descrizione'])) 
				echo '/'.$results['Supplier']['descrizione'];?>
		</li>
		<li>Categoria: <?php echo $results['CategoriesSupplier']['name'];?></li>
		<li><?php 
			if(!empty($results['Supplier']['indirizzo'])) echo $results['Supplier']['indirizzo'].'&nbsp;';
			if(!empty($results['Supplier']['localita']))  echo $results['Supplier']['localita'].'&nbsp;';
			if(!empty($results['Supplier']['provincia'])) echo '('.$results['Supplier']['provincia'].')&nbsp;';
			if(!empty($results['Supplier']['cap'])) echo $results['Supplier']['cap'].'&nbsp;';
			?>
		</li>
		<li><?php 
			if(!empty($results['Supplier']['telefono'])) echo $results['Supplier']['telefono'].'&nbsp;';
			if(!empty($results['Supplier']['mail']))  echo '<a title="'.__('Email send').'" target="_blank" href="mailto:'.$this->App->getPublicMail($user,$results['Supplier']['mail']).'"><i class="fa fa-envelope-o"></i></a>&nbsp;';
			if(!empty($results['Supplier']['www']))  echo '<a title="Vai al sito del produttore" target="_blank" href="'.$this->App->traslateWww($results['Supplier']['www']).'"><i class="fa fa-link"></i></a>&nbsp;';
			?>
		</li>
	</ul>

	<?php 
	if(!empty($results['Organization'])) { ?>
	<h3>Rifornisce i G.A.S.</h3>
		<ul class="list-unstyled">
			<?php 
			foreach($results['Organization'] as $organization) {
				echo '<li>';
				echo '<a href="'.$this->App->traslateWww($organization['www']).'">';
				echo ' <img width="50" class="userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$organization['img1'].'" alt="'.$organization['name'].'" /> ';
				echo $organization['name'].'&nbsp;('.$organization['provincia'].')';
				echo '</a>';
				echo '</li>';			
			}
			?>
		</ul>
	<?php 
	}
	?>
<?php endif; ?>
</div>