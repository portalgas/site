<?php 
$this->App->d($results);

if(!empty($results)) {
	if(!empty($results['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$results['Supplier']['img1']))
		echo ' <img width="100" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$results['Supplier']['img1'].'" alt="'.$results['SuppliersOrganization']['name'].'" />';

		if(isset($results['SuppliersOrganization']['owner_articles'])) {
			echo '<div class="input text ">';
			echo '<label>'.__('organization_owner_articles').'</label> ';						
			switch ($results['SuppliersOrganization']['owner_articles']) {
				case 'SUPPLIER':
					echo $this->App->traslateEnum('ProdGasSupplier'.$results['SuppliersOrganization']['owner_articles']);
				break;
				case 'REFERENT':
					echo $this->App->traslateEnum('ProdGasSupplier'.$results['SuppliersOrganization']['owner_articles']);
				break;
				case 'DES':
					echo $this->App->traslateEnum('ProdGasSupplier'.$results['SuppliersOrganization']['owner_articles']);
				break;
			}
			echo '</div>';
		}
		
        /*
         * per Order::admin_add / admin_edit gestisco il tab delle mail
         */
		if($msgAlert) { 
			if($results['SuppliersOrganization']['mail_order_open']=='Y') {
				echo '<script type="text/javascript">';
				echo 'mail_order_open = true;';
				echo '$("#mail_order_open_Y").show();';
				echo '$("#mail_order_open_N").hide();';
				echo '</script>';
			}
			else
			if($results['SuppliersOrganization']['mail_order_open']=='N') {
				echo '<script type="text/javascript">';
				echo 'mail_order_open = false;';
				echo '$("#mail_order_open_Y").hide();';
				echo '$("#mail_order_open_N").show();';
				echo '</script>';            
			}
				
			if($results['SuppliersOrganization']['mail_order_close']=='Y') {
				echo '<script type="text/javascript">';
				echo '$("#mail_order_close_Y").show();';
				echo '$("#mail_order_close_N").hide();';
				echo '</script>';
			}
			else
			if($results['SuppliersOrganization']['mail_order_close']=='N') {
				echo '<script type="text/javascript">';
				echo '$("#mail_order_close_Y").hide();';
				echo '$("#mail_order_close_N").show();';
				echo '</script>';            
			}
		} // end if($msgAlert)
}


/* 
 * potrebbe essere un ordine DES
 */
if($msgAlert && (!empty($msgOrderDes) && ($isOwnGasTitolareDes || $isTitolareDes || $isReferenteDes || $isSuperReferenteDes))) {
?>
<div class="modal fade" id="dialog-confirm-order-des" role="dialog">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Attenzione</h4>
			</div>
			<div class="modal-body">
				<div id="" title="">
				  	<p style="font-size:16px;"><?php echo $msgOrderDes;?></p>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-sm btn-primary" data-dismiss="modal"><?php echo __('Close');?></button>
			</div>
		</div>
	</div>		
</div>	

<script type="text/javascript">
  $( function() {
	$( "#dialog-confirm-order-des" ).modal();
  } );
</script>
<?php 
}
?>