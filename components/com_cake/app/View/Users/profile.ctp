<?php
if (isset($user->organization['Organization']))
    $j_seo = $user->organization['Organization']['j_seo'];
else
    $j_seo = '';
?>

<form class="form-horizontal" id="member-profile" action="" method="post" class="form-validate" enctype="multipart/form-data">
<fieldset>

<div class="container">
<div class="col-sm-12 col-md-6 col-xs-6">

	<h2>Il tuo profilo</h2>

	<div class="form-group">
	  <label class="control-label col-xs-3">Nome:</label>
		<div class="col-xs-9">
			<?php echo $results['User']['name'];?>
		</div>
	</div>
		
	<div class="form-group">
	  <label class="control-label col-xs-3">Nome utente:</label>
		<div class="col-xs-9">
			<?php echo $results['User']['username'];?>
		</div>
	</div>
	
	<div class="form-group">
	  <label class="control-label col-xs-3">Indirizzo email:</label>
		<div class="col-xs-9">
			<?php echo $results['User']['email'];?>
		</div>
	</div>
	
	<div class="form-group">
	  <label class="control-label col-xs-3">Indirizzo:</label>
		<div class="col-xs-9">
			<?php echo $results['Profile']['address'];?>
		</div>
	</div>

	<div class="form-group">
	  <label class="control-label col-xs-3">Città:</label>
		<div class="col-xs-9">
			<?php echo $results['Profile']['city'];?>
		</div>
	</div>

	<div class="form-group">
	  <label class="control-label col-xs-3">Provincia:</label>
		<div class="col-xs-9">
			<?php echo $results['Profile']['region'];?>
		</div>
	</div>

	<div class="form-group">
	  <label class="control-label col-xs-3">Paese:</label>
		<div class="col-xs-9">
				<?php echo $results['Profile']['country'];?>
		</div>
	</div>

	<div class="form-group">
	  <label class="control-label col-xs-3">CAP:</label>
		<div class="col-xs-9">
			<?php echo $results['Profile']['postal_code'];?>
		</div>
	</div>

	<div class="form-group">
	  <label class="control-label col-xs-3">Cellulare:</label>
		<div class="col-xs-9">
			<?php echo $results['Profile']['phone'];?>
		</div>
	</div>

	<div class="form-group">
	  <label class="control-label col-xs-3">Altro telefono:</label>
		<div class="col-xs-9">
			<?php echo $results['Profile']['phone2'];?>
		</div>
	</div>

	<?php
	if(!isset($results['Profile']['satispay']) || empty($results['Profile']['satispay']))
		$satispay = 'No';
	else 
	if($results['Profile']['satispay']=='Y')	
		$satispay = 'Si';
	else
		$satispay = 'No';
	
	$satispay_phone = '';
	if(isset($results['Profile']['satispay']) && $results['Profile']['satispay']=='Y') {
		if(isset($results['Profile']['satispay_phone']) && empty($results['Profile']['satispay_phone']))
			$satispay_phone = $results['Profile']['satispay_phone'];
	}
	?>
	<div class="form-group">
	  <label class="control-label col-xs-3">Hai Satispay: <img src="/images/satispay.png" style="width:75px;" /></label>
		<div class="col-xs-9">
			<?php echo $satispay;?>
		</div>
	</div>
	
	<?php
	if(!empty($satispay_phone)) {
	?>
		<div class="form-group">
		  <label class="control-label col-xs-3">Altro telefono:</label>
			<div class="col-xs-9">
				<?php echo $results['Profile']['phone2'];?>
			</div>
		</div>
	<?php
	}
	?>
	
	<div class="content-btn">
		<a class="validate btn btn-success pull-right" href="<?php echo JRoute::_('index.php?option=com_users&task=profile.edit&user_id='.(int) $results['User']['id']);?>">
			Modifica i tuoi dati</a>
	</div>
	
</div>
<div class="col-sm-12 col-md-6 col-xs-6">
	
	<div class="col-sm-12 col-md-12 col-xs-12" style="margin-bottom:10px">
	
		<h2>La tua immagine del profilo</h2>
	
		<div class="form-group">
		  <label class="control-label col-xs-3"></label>
			<div class="col-xs-9">
				<?php echo $this->App->drawUserAvatar($user, $results['User']['id'], $results['User']);?>
			</div>
		</div>
		
		<div class="form-group">
		  <label class="control-label col-xs-3">Inserisci una nuova immagine del profilo</label>
			<div class="col-xs-9">
				<?php 
					echo $this->Form->input('Document.file1', array(
						'between' => '<br />',
						'type' => 'file',
						 'label' => false 
					));			
				?>
			</div>
		</div>
							
		<div class="content-btn">
			<button type="submit" class="validate btn btn-success pull-right"><span>Invia immagine</span></button>
			<?php echo JHtml::_('form.token'); ?>
		</div>

	</div>
	
	
	<div class="clearfix"></div>
	
	
	<div class="col-sm-6 col-md-6 col-xs-6">

		<div class="service-item">
			<span class="fa-stack fa-4x">
				<i class="fa fa-circle fa-stack-2x"></i>
				<i class="fa fa-cubes fa-stack-1x text-primary"></i>
			</span>
			<p class="title">Articoli preferiti</p>
			<div class="description-wrap">
				<div class="service-description">
					<div class="m-arrow-wrap"><div class="m-arrow"></div></div>
					<p>Gestisci i tuoi articoli preferiti, verranno caricati in automatico all'apertura dell'ordine.<br /><b>Modulo in sviluppo</b></p>
					<div class="clearfix"></div>
				</div>
			</div>
		</div>
	
	</div>
	<div class="col-sm-6 col-md-6 col-xs-6">
	
		<a href="/home-<?php echo $j_seo;?>/bookmarks-mails">
		<div class="service-item">
			<span class="fa-stack fa-4x">
				<i class="fa fa-circle fa-stack-2x"></i>
				<i class="fa fa-envelope-o fa-stack-1x text-primary"></i>
			</span>
			<p class="title">Personalizza le Mail</p>
			<div class="description-wrap">
				<div class="service-description">
					<div class="m-arrow-wrap"><div class="m-arrow"></div></div>
					<p>Gestisci le mail che PortAlGas ti invia, scegli da quale produttore desideri ricevere la mail di notifica di apertura o chiusura di un ordine.</p>
					<div class="clearfix"></div>
				</div>
			</div>
		</div>
		</a>
	
	</div>
		
</div>
</div>

<div class="container">
<?php
echo '<h2 style="margin-top:25px;">I produttori di cui sei referente</h2>';

if(!empty($results['SuppliersOrganization'])) {

	echo '<div class="table"><table class="table table-hover">';
	echo '<tr>';
	echo '<th>'.__('N').'</th>';
	echo '<th></th>';
	echo '<th>Ragione sociale</th>';
	echo '<th>Frequenza</th>';
	echo '<th>Localit&agrave;</th>';
	echo '<th>Contatti</th>';
	echo '<th>'.__('Supplier').'</th>';
	echo '</tr>';
	
	
	foreach ($results['SuppliersOrganization'] as $numResult => $result) {

		echo '<tr>';
		echo '<td>'.((int)$numResult+1).'</td>';
		echo '<td>';
		if(!empty($result['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$result['Supplier']['img1']))
			echo ' <img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$result['Supplier']['img1'].'" alt="'.$result['SupplierOrganization']['name'].'" /> ';		
		echo '</td>';		
		echo '<td>';
		echo $result['SuppliersOrganization']['name'];
		if(!empty($result['Supplier']['telefono'])) echo '<br /><small>'.$result['Supplier']['descrizione'].'</small>';
		echo '</td>';
		
		echo '<td>';
		echo $result['SuppliersOrganization']['frequenza'];
		echo '</td>';
		
		echo '<td>';
		if(!empty($result['Supplier']['indirizzo'])) echo $result['Supplier']['indirizzo'].'<br />';
		if(!empty($result['Supplier']['localita'])) echo $result['Supplier']['localita'].'<br />';
		if(!empty($result['Supplier']['cap'])) echo $result['Supplier']['cap'].'<br />';
		if(!empty($result['Supplier']['provincia'])) echo '('.$result['Supplier']['provincia'].')<br />';		
		echo '</td>';
		
		echo '<td>';
		if(!empty($result['Supplier']['telefono'])) echo $result['Supplier']['telefono'].'<br />';
		if(!empty($result['Supplier']['telefono2'])) echo $result['Supplier']['telefono2'].'<br />';
		if(!empty($result['Supplier']['mail'])) echo '<a title="'.__('Email send').'" target="_blank" href="mailto:'.$result['Supplier']['mail'].'">'.$result['Supplier']['mail'].'</a><br />';
		if(!empty($result['Supplier']['www'])) echo '<a href="'.$this->App->traslateWww($result['Supplier']['www']).'" target="_blank">'.$result['Supplier']['www'].'</a><br />';
		echo '</td>';
		
		echo '<td>';
		if($result['SuppliersOrganizationsReferent']['type']=='REFERENTE')
			echo '<img style="margin-right:5px;" alt="'.$this->App->traslateEnum($result['SuppliersOrganizationsReferent']['type']).'" src="'.Configure::read('App.img.cake').'/icons/16x16/user.png" />';
		else
		if($result['SuppliersOrganizationsReferent']['type']=='COREFERENTE')
			echo '<img style="margin-right:5px;" alt="'.$this->App->traslateEnum($result['SuppliersOrganizationsReferent']['type']).'" src="'.Configure::read('App.img.cake').'/icons/16x16/user_add.png" />';
		echo '</td>';
		echo '</tr>';		
	}
	echo '</table></div>';
}
else 
	echo '<div class="alert alert-info" role="alert"><a data-dismiss="alert" class="close" href="#">×</a><strong>Non sei referente di alcun produttore</strong></div>';

echo '</fieldset>';
echo '	</form>';
?>
</div>

<script type="text/javascript">
$(document).ready(function() {

	$('#formGas').submit(function() {

		var doc1 = $('#DocumentImg1').val();
		if(doc1=='' || doc1==undefined) {
			alert("Devi scelgliere dal tuo PC un immagine da uplodare");
			$(this).focus();
			return false;
		}	
		
		return true;
	});
});
</script>