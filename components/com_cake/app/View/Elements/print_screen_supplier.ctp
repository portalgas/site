<?php
$msg_print_screen_supplier_article_ctrl = '<p>Il testo che hai inserito ha <b>poche righe</b>: questo dovrebbe comporre l\'articolo per descrivere l\'attività del produttore:<br />non è obbligatorio che il produttore abbia un articolo associato.<br /><br />Se hai una semplice descrizione del produttore <b>non</b> comporre questo campo.<br />Consulta il <b>sito</b> del produttore per avere materiale da pubblicare!</p><p>Di seguito come apparirà ai gasisti l\'articolo di un produttore</p><p><img src=\"'.Configure::read('App.img.cake').'/print_screen_supplier_article.jpg\" title=\"\" border=\"0\" /></p>';
?>
<div class="modal fade" id="modalSupplierPrintScreen" role="dialog">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title"></h4>
			</div>
			<div class="modal-body">
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-sm btn-warning" data-dismiss="modal"><?php echo __('Close');?></button>
			</div>
		</div>
	</div>		
</div>		

<script type="text/javascript">
function gestioneModalSupplier(id) {
		var mymodal = $('#modalSupplierPrintScreen');

		switch(id) {
			case "print_screen_supplier_nota":
				mymodal.find('.modal-title').html("Ecco come apparirà la descrizione inserita");
				mymodal.find('.modal-body').html('<img src="<?php echo Configure::read('App.img.cake');?>/print_screen_supplier_nota.jpg" class="img-responsive-disabled" />');
				break;
			case "print_screen_supplier_article":
				mymodal.find('.modal-title').html("Ecco come apparirà il testo inserito per la scheda del produttore");
				mymodal.find('.modal-body').html('<img src="<?php echo Configure::read('App.img.cake');?>/print_screen_supplier_article.jpg" class="img-responsive-disabled" />');
				break;
			case "print_screen_supplier_article_ctrl":
				mymodal.find('.modal-title').html("Scheda del produttore");
				mymodal.find('.modal-body').html("<?php echo $msg_print_screen_supplier_article_ctrl;?>");
				break;
		}
		
		mymodal.modal('show');
}
		
$(document).ready(function() {
	$('.print_screen').click(function() {
		var id = $(this).attr('id');
		gestioneModalSupplier(id);
	});
});
</script>