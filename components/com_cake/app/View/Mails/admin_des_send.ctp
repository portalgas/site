<?php
$this->App->d($results);
					
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Mails'),array('controller'=>'Mails','action'=>'des_index'));
$this->Html->addCrumb(__('Send Mail'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>
<div class="mails">
	<h2 class="ico-mails">
		<?php echo __('Send Mail');?>
	<div class="actions-img">
	<ul>
		<li><?php echo $this->Html->link(__('List Mails'), array('action' => 'des_index'),array('class' => 'action actionConfig','title' => __('List Mails'))); ?></li>
	</ul>
	</div>
	</h2>


<?php echo $this->Form->create('Mail',array('id'=>'formGas','enctype' => 'multipart/form-data'));?>

	<fieldset>
		<legend><?php echo __('Send Mail'); ?></legend>
		
	<?php
		/* 
		 *   loop DesSupplier
		 */
		if(!empty($results)) {

			echo '<table cellpadding="0" cellspacing="0">';
			echo '<tr>';
			echo '<th>'.__('N').'</th>';
			echo '<th></th>';
			echo '<th colspan="5">'.__('DesSuppliers').'</th>';
			echo '</tr>';

			foreach ($results as $numResult => $result) {

				echo '<tr class="view-2">';
				echo '<td>'.($numResult+1).'</td>';
				echo '<td><input type="radio" name="data[DesSupplier][id]" value="'.$result['DesSupplier']['id'].'" /></td>';
				
				echo '<td>';
				if(!empty($result['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$result['Supplier']['img1']))
					echo '<img width="50" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$result['Supplier']['img1'].'" />';	
				echo '</td>';			
				echo '<td>'.$result['Supplier']['name'];
				if(!empty($result['Supplier']['descrizione']))
					echo ' - '.$result['Supplier']['descrizione'];
				echo '</td>';
				echo '<td></td>';
				echo '<td></td>';
				echo '<td></td>';
				echo '</tr>';
				
				echo '<tr class="view">';
				echo '<td></td>';
				echo '<td colspan="6" class="details_users" id="details_users-'.$result['DesSupplier']['id'].'"></td>';
				echo '</tr>';
				
			} // loop DesSupplier
			echo '</table>';
				
		echo $this->Form->input('subject');
		
		echo '<div class="clearfix"></div>';
		echo '<div class="input text"><label></label> ';
		echo $body_header_mittente; 
		
		echo $this->Form->textarea('body', array('rows' => '15', 'cols' => '75'));
		echo '<div class="clearfix"></div>';
		
		echo '<div class="input text"><label>Piè di pagina</label> ';
		
		echo '<textarea cols="85%" rows="4" class="noeditor" disabled="true" id="body_footer" style="display:inline;">'.str_replace('<br />', '', $body_footer).'</textarea>';
		
		echo '</div>';		
		
		echo '<div class="clearfix"></div>';
		echo $this->Form->input('Document.img1', array(
													'label' => 'Allegato',
												    'between' => '<br />',
												    'type' => 'file'
												));	
		
		echo '</fieldset>';
		
		echo $this->Form->end(__('Send'));
		} 
		else
			echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFound', 'msg' => "Non sei abilitato su alcun produttore!"));
		?>
</div>

<script type="text/javascript">
$(document).ready(function() {
	$("input[name='data[DesSupplier][id]']").change(function() {	
		
		var des_supplier_id = $(this).val();

		$('.details_users').html("");
		$('.details_users').css('display', 'none');
				
		$('#details_users-'+des_supplier_id).css('min-height', '50px');
		$('#details_users-'+des_supplier_id).css('display', 'table-cell');
		$('#details_users-'+des_supplier_id).css('background', 'url("<?php echo Configure::read('App.server').Configure::read('App.img.cake');?>/ajax-loader.gif") no-repeat scroll center 0 transparent');
		
		$.ajax({
			type: "GET",
			url: "/administrator/index.php?option=com_cake&controller=Mails&action=des_send_details_users&des_supplier_id="+des_supplier_id+"&format=notmpl",
			data: "",
			success: function(response) {
				$('#details_users-'+des_supplier_id).css('background', 'none repeat scroll 0 0 transparent');
				 $('#details_users-'+des_supplier_id).html(response);
			},
			error:function (XMLHttpRequest, textStatus, errorThrown) {
				$('#details_users-'+des_supplier_id).css('background', 'none repeat scroll 0 0 transparent');
				$('#details_users-'+des_supplier_id).html(textStatus);
			}
		});
			
		return false;
	});

	$('#formGas').submit(function() {

		var des_supplier_id = $("input[name='data[DesSupplier][id]']:checked").val();
		if(des_supplier_id=='' || des_supplier_id==undefined) {
			alert("Devi indicare il produttore al quale sono associati i referenti");
			return false;
		}
		
		var subject = $('#MailSubject').val();
		if(subject=="")  {
			alert("Devi indicare l'oggetto della mail");
			return false;
		}
	
		var body = $('#MailBody').val();
		if(body=="") {
			alert("Devi indicare il testo della mail");
			return false;
		}
	
		alert("Verrà inviata la mail, attendere che venga terminata l'esecuzione");
	
		$("input[type=submit]").attr('disabled', 'disabled');
		$("input[type=submit]").css('background-image', '-moz-linear-gradient(center top , #ccc, #dedede)');
		$("input[type=submit]").css('box-shadow', 'none');

		return true;
	});	
});
</script>