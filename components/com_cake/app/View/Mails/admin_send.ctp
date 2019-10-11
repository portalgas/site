<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Mails'),array('controller'=>'Mails','action'=>'index'));
$this->Html->addCrumb(__('Send Mail'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>
<div class="mails">
	<h2 class="ico-mails">
		<?php echo __('Send Mail');?>
	<div class="actions-img">
	<ul>
		<li><?php echo $this->Html->link(__('List Mails'), array('action' => 'index'),array('class' => 'action actionConfig','title' => __('List Mails'))); ?></li>
	</ul>
	</div>
	</h2>
<?php 
echo $this->Form->create('Mail', ['id'=>'formGas','enctype' => 'multipart/form-data']);
echo '<fieldset>';
echo '<legend>'.__('Send Mail').'</legend>';
	
$i=0;
echo $this->Form->input('mittenti', ['options' => $mittenti, 'value' => Configure::read('Mail.no_reply_mail'), 'label'=>__('A chi rispondere'),'tabindex'=>($i+1)]);

echo $this->App->drawFormRadio('Mail','dest_options', ['options' => $dest_options, 'value'=>'USERS', 'name' => 'dest-options', 'label' => __('A chi inviarla'),'tabindex'=>($i+1)]);

echo $this->App->drawFormRadio('Mail','dest_options_qta', ['options' => $dest_options_qta, 'value'=>'ALL', 'name' => 'dest-options-qta', 'label' => __('A quanti'),'tabindex'=>($i+1)]);
		
/*
 * produttori
 */
echo '<div id="suppliersorganization" style="display:none;">';
$label = __('SuppliersOrganization').'&nbsp;('.count($ACLsuppliersOrganization).')';
echo $this->Form->input('supplier_organization', ['label' => $label,'options' => $ACLsuppliersOrganization,'escape' => false,'multiple' => true]);
echo '</div>';
				
/*
 * utenti dell'ordine
 */
echo '<div id="users_cart" style="display:none;">';
$label = "Utenti che hanno effettuato acquisti";
echo $this->Form->input('orders', ['label' => $label,'options' => $orders, 'empty' => Configure::read('option.empty'), 'escape' => false]);
echo '</div>';

echo '<div id="users_cart_articles_orders" style="display:none;background: transparent none repeat scroll 0px 0px;overflow-y: scroll;height: 300px;">';
echo '</div>';

/*
 * gruppi
 */
echo '<div id="userGroups" style="display:none;">';
$label = __('Groups').'&nbsp;('.count($userGroups).')';
echo $this->Form->input('usergroups', ['label' => $label,'options' => $userGroups,'escape' => false,'multiple' => true]);
echo '</div>';
		
/*
 * utenti
 */
echo '<div id="users" style="display:none;">';
$label = __('Users').'&nbsp;('.count($users).')';
echo '<label for="MailUser">'.$label.'</label> ';

echo $this->Form->select('master_user_id', $users, ['label' => $label, 'multiple' => true, 'size' =>10]);
echo $this->Form->select('user_id', [], ['multiple' => true, 'size' => 10, 'style' => 'min-width:300px']);					
echo $this->Form->hidden('user_ids', ['id' => 'user_ids','value' => '']);
echo '</div>';

/*
 * referenti
 */
echo '<div id="referenti" style="display:none;">';
$label = __('Referenti').'&nbsp;('.count($referenti).')';
echo '<label for="MailUser">'.$label.'</label> ';

echo $this->Form->select('master_referente_id', $referenti,['label' => $label, 'multiple' => true, 'size' =>10]);
echo $this->Form->select('referente_id', [], ['multiple' => true, 'size' => 10, 'style' => 'min-width:300px']);					
echo $this->Form->hidden('referente_ids', ['id' => 'referente_ids', 'value' => '']);
echo '</div>';

echo $this->Form->input('subject');

echo $this->Form->input('name', ['label' => 'Intestazione', 'value' => str_replace('<br />', '', $body_header), 'disabled' => 'true']);

echo '<div class="clearfix"></div>';
echo '<div class="input text"><label></label> ';
echo $body_header_mittente; 

echo $this->Form->textarea('body', ['rows' => '15', 'cols' => '75']);

echo '<div class="clearfix"></div>';
echo '<div class="input text"><label>Piè di pagina</label> ';

echo '<textarea cols="85%" rows="4" class="noeditor" disabled="true" id="body_footer_no_reply" style="display:inline;">'.str_replace('<br />', '', $body_footer_no_reply).'</textarea>';
echo '<textarea cols="85%" rows="4" class="noeditor" disabled="true" id="body_footer" style="display:none;">'.str_replace('<br />', '', $body_footer).'</textarea>';

echo '</div>';		

echo '<div class="clearfix"></div>';
echo $this->Form->input('Document.img1', ['label' => 'Allegato',
											'between' => '<br />',
											'type' => 'file']);	

echo '</fieldset>';

echo $this->Form->end(__('Send'));
		
echo '</div>';
?>
<script type="text/javascript">
function drawArticlesOrders(order_id) {
	/* console.log("order_id "+order_id); */
	if(order_id!=''){
		var url = "/administrator/index.php?option=com_cake&controller=Mails&action=ajax_users_cart_articles_orders&order_id="+order_id+"&format=notmpl";
		var idDivTarget = 'users_cart_articles_orders';
		$('#users_cart_articles_orders').show();
		ajaxCallBox(url, idDivTarget);
	}
	else {
		$('#users_cart_articles_orders').html("");
		$('#users_cart_articles_orders').hide();
	}
}

$(document).ready(function() {

	$('#MailMittenti').change(function() {
		var mittenti = $('#MailMittenti').val();	
		
		if(mittenti=='<?php echo Configure::read('Mail.no_reply_mail');?>') {
			$('#body_footer_no_reply').show();
			$('#body_footer').hide();
		}	
		else {
			$('#body_footer_no_reply').hide();
			$('#body_footer').show();
		}	
	});
	
	$('#MailOrders').change(function() {
		var order_id = $('#MailOrders').val();	
		drawArticlesOrders(order_id);
	});
	
	$('#MailMasterUserId').click(function() {
		$("#MailMasterUserId option:selected" ).each(function (){			
			$('#MailUserId').append($("<option></option>")
	         .attr("value",$(this).val())
	         .text($(this).text()));
	         
	         $(this).remove();
		});
	});
	
	$('#MailUserId').click(function() {
		$("#MailUserId option:selected" ).each(function (){			
			$('#MailMasterUserId').append($("<option></option>")
	         .attr("value",$(this).val())
	         .text($(this).text()));
	         
	         $(this).remove();
		});
	});
	
	$('#MailMasterReferenteId').click(function() {
		$("#MailMasterReferenteId option:selected" ).each(function (){			
			$('#MailReferenteId').append($("<option></option>")
	         .attr("value",$(this).val())
	         .text($(this).text()));
	         
	         $(this).remove();
		});
	});
	
	$('#MailReferenteId').click(function() {
		$("#MailReferenteId option:selected" ).each(function (){			
			$('#MailMasterReferenteId').append($("<option></option>")
	         .attr("value",$(this).val())
	         .text($(this).text()));
	         
	         $(this).remove();
		});
	});
	
	$("input[name='data[Mail][dest_options]']").change(function() {
		choiceDestOptions();
	});

	$("input[name='data[Mail][dest_options_qta]']").change(function() {
		choiceDestOptions();
	});
	
	choiceDestOptions();

	$('#formGas').submit(function() {

		var dest_options_qta = $("input[name='data[Mail][dest_options_qta]']:checked").val();
		var dest_options = $("input[name='data[Mail][dest_options]']:checked").val();

		if(dest_options_qta=='SOME') {
			
			var destinatariScelti = null;
			if(dest_options=='USERS') {
				var user_ids = '';
				$("#MailUserId option" ).each(function (){	
					user_ids +=  $(this).val()+',';
				});
				user_ids = user_ids.substring(0,user_ids.length-1);
				
				if(user_ids=='') {
					alert("Devi selezionare almeno un utente come destinatario");
					return false;
				}
				
				$('#user_ids').val(user_ids);			
			}
			else 
			if(dest_options=='REFERENTI') {
				var referente_ids = '';
				$("#MailReferenteId option" ).each(function (){	
					referente_ids +=  $(this).val()+',';
				});
				referente_ids = referente_ids.substring(0,referente_ids.length-1);
				
				if(referente_ids=='') {
					alert("Devi selezionare almeno un referente come destinatario");
					return false;
				}
				
				$('#referente_ids').val(referente_ids);	
			}
			else	
			if(dest_options=='SUPPLIERS') {
				destinatariScelti = $("#MailSupplierOrganization").val();
	
				if(destinatariScelti==null) {
					alert("Devi scegliere almeno un destinatario");
					return false;
				}			
			}			
		}
		else {

			if(dest_options=='USERS_CART') {
				var order_id = $('#MailOrders').val();
				if(order_id=='') {
					alert("Devi scegliere un ordine");
					return false;				
				}
				
				var article_order_key_selecteds = '';
				for(i = 0; i < $("input[name='data[Mail][article_order_key_selected]']:checked").length; i++) {
					var elem = $("input[name='data[Mail][article_order_key_selected]']:checked").eq(i);
					article_order_key_selecteds += $(elem).val()+'|';
				}
				
				$('#article_order_key_selecteds').val("");
				
				if(article_order_key_selecteds!='') {
					 article_order_key_selecteds = article_order_key_selecteds.substring(0,article_order_key_selecteds.length-1); 		
					$('#article_order_key_selecteds').val(article_order_key_selecteds);
				}	
				else {
					alert("Devi scegliere un articolo");
					return false;								
				}		
			}
		
		}
		
		var subject = $('#MailSubject').val();
		if(subject=="") {
			alert("Devi indicare l'oggetto della mail");
			return false;
		}
	
		var body = $('#MailBody').val();
		if(body=="") {
			alert("Devi indicare il testo della mail");
			return false;
		}
	
		if(!confirm("Verrà inviata la mail, attendere che venga terminata l'esecuzione")) 
			return false;
		
		$("input[type=submit]").attr('disabled', 'disabled');
		$("input[type=submit]").css('background-image', '-moz-linear-gradient(center top , #ccc, #dedede)');
		$("input[type=submit]").css('box-shadow', 'none');

		return true;
			
	});	
});

function choiceDestOptions() {
	var dest_options = $("input[name='data[Mail][dest_options]']:checked").val();
	var dest_options_qta = $("input[name='data[Mail][dest_options_qta]']:checked").val();

	$('#Maildest_options_qtaALL').attr('disabled',false);
	$('#Maildest_options_qtaSOME').attr('disabled',false);

	if(dest_options=='USERS_CART') {
		$('#users_cart').css('display','block');
		$('#users_cart_articles_orders').css('display','none');
		$('#users').css('display','none');
		$('#userGroups').css('display','none');
		$('#referenti').css('display','none');
		$('#suppliersorganization').css('display','none');
		
		$('#Maildest_options_qtaALL').prop("checked", true);
		$('#Maildest_options_qtaSOME').attr('disabled',true);
		
		var order_id = $('#MailOrders').val();	
		drawArticlesOrders(order_id);		
	}
	else
	if(dest_options_qta=='ALL') {
		$('#Maildest_options_qtaUSERS_CART').css('display','none');
	
		$('#users_cart').css('display','none');
		$('#users_cart_articles_orders').css('display','none');
		$('#users').css('display','none');
		$('#userGroups').css('display','none');
		$('#referenti').css('display','none');
		$('#suppliersorganization').css('display','none');
	}	
	else {
		if(dest_options=='USERS') {			
			$('#users_cart').css('display','none');
			$('#users_cart_articles_orders').css('display','none');
			$('#users').css('display','block');
			$('#userGroups').css('display','none');
			$('#referenti').css('display','none');
			$('#suppliersorganization').css('display','none');
			
			$('#Maildest_options_qtaSOME').attr('disabled',false);
		}
		else
		if(dest_options=='USERGROUPS') {			
			$('#users_cart').css('display','none');
			$('#users_cart_articles_orders').css('display','none');
			$('#users').css('display','none');
			$('#userGroups').css('display','block');
			$('#referenti').css('display','none');
			$('#suppliersorganization').css('display','none');
			
			$('#Maildest_options_qtaSOME').attr('disabled',false);
		}
		else	
		if(dest_options=='REFERENTI') {
			$('#users_cart').css('display','none');
			$('#users_cart_articles_orders').css('display','none');
			$('#users').css('display','none');
			$('#userGroups').css('display','none');
			$('#referenti').css('display','block');
			$('#suppliersorganization').css('display','none');
			
			$('#Maildest_options_qtaSOME').attr('disabled',false);
		}
		else	
		if(dest_options=='SUPPLIERS') {
			$('#users_cart').css('display','none');
			$('#users_cart_articles_orders').css('display','none');
			$('#users').css('display','none');
			$('#userGroups').css('display','none');
			$('#referenti').css('display','none');
			$('#suppliersorganization').css('display','block');
			
			$('#Maildest_options_qtaSOME').attr('disabled',false);
		}
	}
}
</script>