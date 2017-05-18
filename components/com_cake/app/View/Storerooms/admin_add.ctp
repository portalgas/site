<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('Storeroom'), array('controller' => 'Storerooms', 'action' => 'index'));
$this->Html->addCrumb(__('Add Storeroom'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>
<script type="text/javascript">
function add_list_articles() {
	var supplier_organization_id = jQuery("#supplier_organization_id").val();
	var storeroom_id = jQuery("#storeroom_id").val();

	if(supplier_organization_id=="") {
		jQuery('#articles-result').css('display', 'none');
		jQuery('#articles-result').css('background', 'none repeat scroll 0 0 transparent');
		jQuery('#articles-result').html("");
		return false;	
	}

	jQuery('#articles-result').css('display', 'block');
	jQuery('#articles-result').css('background', 'url("<?php echo Configure::read('App.server').Configure::read('App.img.cake');?>/ajax-loader.gif") no-repeat scroll center 0 transparent');

	jQuery.ajax({
		type: "get", 
		url: "/administrator/index.php?option=com_cake&controller=Storerooms&action=add_list_articles&supplier_organization_id="+supplier_organization_id+"&storeroom_id="+storeroom_id+"&format=notmpl",
		data: "",  
		success: function(response) {
			jQuery('#articles-result').css('background', 'none repeat scroll 0 0 transparent');
			jQuery('#articles-result').html(response);
		},
		error:function (XMLHttpRequest, textStatus, errorThrown) {
			jQuery('#articles-result').css('background', 'none repeat scroll 0 0 transparent');
			jQuery('#articles-result').html(textStatus);
		}
	});
	return false;
}
</script>
<div class="storerooms form" style="min-height:450px;">
		<?php 
		echo $this->Form->create('Storeroom',array('id' => 'formGas'));
		echo '<fieldset>';
		echo '<legend>'.__('Add Storeroom').'</legend>';
		
		$options = array('id' => 'supplier_organization_id',
						 'name'=>'supplier_organization_id',
						 'onChange' => 'javascript:add_list_articles(this);',
						 'empty'=> Configure::read('option.empty'),
						 'options' => $suppliersOrganization);
		if(count($suppliersOrganization) > Configure::read('HtmlSelectWithSearchNum')) 
			$options += array('class'=> 'selectpicker', 'data-live-search' => true); 

		/*
		* $storeroom_id / $supplier_organization_id valorizzati se chiamato da admin_index per modifica
		*/
		if(!empty($supplier_organization_id))
			$options += array('default'=> $supplier_organization_id);
		
		echo $this->Form->input('supplier_organization_id',$options);	
		
		
		echo '<div id="articles-result" style="display:none;"></div>';
		
		/*
		* $storeroom_id / $supplier_organization_id valorizzati se chiamato da admin_index per modifica
		*/		
		echo '<input type="hidden" value="'.$storeroom_id.'" id="storeroom_id" name="storeroom_id" />';
		echo $this->Form->end(__('Submit'));
		?>

	</fieldset>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Storeroom'), array('action' => 'index'),array('class'=>'action actionReload'));?></li>
	</ul>
</div>

<script type="text/javascript">
jQuery(document).ready(function() {

	<?php
	/*
	* $storeroom_id / $supplier_organization_id valorizzati se chiamato da admin_index per modifica
	*/	
	if(!empty($supplier_organization_id)) {
	?>
		add_list_articles();
	<?php
	}
	?>
	
	jQuery('#formGas').submit(function() {

		var continua = true;
		
		/*
		 * ciclo per gli articoli gia' inseriti in dispensa 
		 */
		 var count = 0;
		jQuery(".qta_storeroom").each(function () {
			var qta_storeroom = jQuery(this).val(); 
		
			if(continua && qta_storeroom!='' && !isNumber(qta_storeroom)) {
				alert("Devi indicare la quantità da modificare in dispensa con un valore numerico");
				jQuery(this).focus();
				continua = false;
			}
				
			if(continua && qta_storeroom!='') count++;
		});
			
				
		
		
		/*
		 * ciclo per gli articoli ancora da inserire in dispensa 
		 */
		 if(continua) {
			jQuery(".qta_article").each(function () {
				var qta_article = jQuery(this).val(); 
			
				if(continua && !isNumber(qta_article)) {
					alert("Devi indicare la quantità da associare alla dispensa con un valore numerico");
					jQuery(this).focus();
					continua = false;
				}
					
				if(continua && qta_article > 0) count++;
			});
		}
					
		if(continua && count==0) {
			alert("Devi valorizzare almeno una quantità di un articolo");
			continua = false;		
		}	

		if(!continua)
			return false;
		else	
			return true;
	});
});
</script>