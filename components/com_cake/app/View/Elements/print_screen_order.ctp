<div class="modal fade" id="modalOrderPrintScreen" role="dialog">
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
$(document).ready(function() {
	$('.print_screen').click(function() {
		var id = jQuery(this).attr('id');
		
		var mymodal = $('#modalOrderPrintScreen');

		switch(id) {
			case "print_screen_mail_open_testo":
				mymodal.find('.modal-dialog').removeClass("modal-lg").addClass("modal-md");
				mymodal.find('.modal-title').html("Ecco come apparirà il testo inserito");
				mymodal.find('.modal-body').html('<img src="<?php echo Configure::read('App.img.cake');?>/print_screen_mail_open_testo.jpg" title="" border="0" />');
				break;
			case "print_screen_order_nota":
				mymodal.find('.modal-dialog').removeClass("modal-md").addClass("modal-lg");
				mymodal.find('.modal-title').html("Ecco come apparirà il testo inserito");
				mymodal.find('.modal-body').html('<img src="<?php echo Configure::read('App.img.cake');?>/print_screen_order_nota.jpg" title="" border="0" />');
				break;
			case "print_screen_type_draw_simple":
				mymodal.find('.modal-dialog').removeClass("modal-md").addClass("modal-lg");
				mymodal.find('.modal-title').html("Ecco come apparirà agli utenti");
				mymodal.find('.modal-body').html('<img src="<?php echo Configure::read('App.img.cake');?>/print_screen_type_draw_simple.jpg" title="" border="0" />');
				break;
			case "print_screen_type_draw_complete":
				mymodal.find('.modal-dialog').removeClass("modal-md").addClass("modal-lg");
				mymodal.find('.modal-title').html("Ecco come apparirà agli utenti");
				mymodal.find('.modal-body').html('<img src="<?php echo Configure::read('App.img.cake');?>/print_screen_type_draw_complete.jpg" title="" border="0" />');
				break;
		}
		
		mymodal.modal('show');		
	});
});
</script>