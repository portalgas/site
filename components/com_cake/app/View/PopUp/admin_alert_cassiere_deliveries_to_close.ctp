<?php
echo $this->Html->css('popupSlider-min');
?>
<div class="cakeContainer">		
	<div class="cassiere">
		<h2 class="ico-users"><?php echo __('Cassiere');?></h2>
	</div>
		
	Ci sono delle <b>consegne</b> scadute (con la data precedente alla data odierna) <br />
	che il Cassiere potrà chiudere se tutti i gasisti hanno effettuato i <b>pagamenti</b> degli ordini associati.<br /><br />

	Se tutti i gasisti hanno effettuato i pagamenti degli ordini associati:
	<ul class="menuLateraleItems">
		<li><span class="popupNum">1</span> <span class="popupVoceMenu bgLeft"> Vai alla voce di menù "Cassiere" => "Gestisci le consegne"</span></li>
		<li><span class="popupNum">2</span> <span class="popupVoceMenu bgLeft"> Ora sei al modulo per modificare lo stato delle consegne</span></li>
		<li><span class="popupNum">3</span> <span class="popupVoceMenu bgLeft actionOpen">Cambia lo stato della consegna da "Aperto" a "Chiuso"</span></li>
	</ul>			
					
	<?php 
	echo $this->Form->create('PopUp',array('id'=>'formGas'));
	echo $this->Form->submit(__('Vai al modulo per modificare lo stato delle consegne'),array('id' => 'redirect', 'div'=> 'submitMultiple left'));
	echo $this->Form->end();
	?>
		
	<div class="clearfix"></div>
</div>		
	
<script type="text/javascript">
$(document).ready(function() {
	$('#redirect').click(function() {
		window.location.replace('<?php echo Configure::read('App.server');?>/administrator/index.php?option=com_cake&controller=Cassiere&action=home');
		return false;
	});
});
</script>			