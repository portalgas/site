<?php 
/*
echo "<pre>";
print_r($results);
echo "</pre>";
*/
if(!empty($results)) {
	if(!empty($results['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$results['Supplier']['img1']))
		echo ' <img width="100" class="userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$results['Supplier']['img1'].'" alt="'.$results['SuppliersOrganization']['name'].'" /> ';

        /*
         * per Order::admin_add / admin_edit gestisco il tab delle mail
         */
        if($results['SuppliersOrganization']['mail_order_open']=='Y') {
            echo '<script type="text/javascript">';
            echo 'mail_order_open = true;';
            echo 'jQuery("#mail_order_open_Y").show();';
            echo 'jQuery("#mail_order_open_N").hide();';
            echo '</script>';
        }
        else
        if($results['SuppliersOrganization']['mail_order_open']=='N') {
            echo '<script type="text/javascript">';
            echo 'mail_order_open = false;';
            echo 'jQuery("#mail_order_open_Y").hide();';
            echo 'jQuery("#mail_order_open_N").show();';
            echo '</script>';            
        }
            
        if($results['SuppliersOrganization']['mail_order_close']=='Y') {
            echo '<script type="text/javascript">';
            echo 'jQuery("#mail_order_close_Y").show();';
            echo 'jQuery("#mail_order_close_N").hide();';
            echo '</script>';
        }
        else
        if($results['SuppliersOrganization']['mail_order_close']=='N') {
            echo '<script type="text/javascript">';
            echo 'jQuery("#mail_order_close_Y").hide();';
            echo 'jQuery("#mail_order_close_N").show();';
            echo '</script>';            
        }
}


/* 
 * potrebbe essere un ordine DES
 */
if(!empty($msgOrderDes) && ($isOwnGasTitolareDes || $isTitolareDes || $isReferenteDes || $isSuperReferenteDes)) {
?>
<div id="dialog-confirm-order-des" title="Attenzione">
  <p style="font-size:16px;"><?php echo $msgOrderDes;?></p>
</div>

		<script type="text/javascript">
		  jQuery( function() {
		    jQuery( "#dialog-confirm-order-des" ).dialog({
		      resizable: false,
		      autoOpen: true,
		      height: "auto",
		      width: 500,
		      modal: true,
		      buttons: {
		        Ok: function() {
		          jQuery( this ).dialog( "close" );
		        }
		      }
		    });
		  } );
		</script>
<?php 
}
?>