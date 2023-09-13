<style>
.mail_no {
    border-left:5px solid red;
}
</style>    
<?php
echo '<h2 style="margin-top:25px;">I produttori del tuo GAS</h2>';

if(!empty($results)) {

	echo '<div class="table"><table class="table table-hover">';
	echo '<tr>';
	echo '<th>'.__('N').'</th>';
	echo '<th>Categoria</th>';
	echo '<th></th>';
	echo '<th>Ragione sociale</th>';
	echo '<th>Frequenza</th>';
	echo '<th>
            Mail all\'apertura dell\'ordine
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="order_open_all" name="order_open_all" value="1" />
                <label class="form-check-label">Sel./desel. tutti</label>
            </div>         
        </th>';
	echo '<th style="width:75px;"></th>';
	echo '<th>
            Mail alla chiusura dell\'ordine
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="order_close_all" name="order_close_all" value="1" />
                <label class="form-check-label">Sel./desel. tutti</label>
            </div>             
        </th>';
	echo '</tr>';
	
	$i=0;
	foreach ($results as $numResult => $result) {

		$i++;
		
		echo '<tr>';
		echo '<td>'.($i).'</td>';
		echo '<td>'.$result['CategoriesSupplier']['name'].'</td>';
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
		
                /*
                 * order_open
                 */
                if($result['SuppliersOrganization']['mail_order_open']=='N') {
                    echo '<td class="mail_no">';
                    echo 'Disabilitato del referente';
                    echo '</td>';                    
                }
                else {
                    echo '<td class="';
                    if($result['BookmarksMail']['order_open']=='N') echo "mail_no";
                    echo '">';
                    echo '<div class="checkbox">';
                    echo '<label><input type="checkbox" name="order_open" value="'.$result['SuppliersOrganization']['id'].'" ';
                    if($result['BookmarksMail']['order_open']=='Y') echo "checked"; 
                    echo '></label> ';
                    echo '</div>';
                    echo '</td>';                    
                }

                /*
                 * esito call ajax
                 */
                echo '<td>';
                echo '<img alt="" src="'.Configure::read('App.img.cake').'/blank32x32.png" id="esito_img-'.$result['SuppliersOrganization']['id'].'" />';
                echo '<span class="esito_ajax" id="esito_msg-'.$result['SuppliersOrganization']['id'].'"></span>';
                echo '</td>';
                
                /*
                 * order_close
                 */
                if($result['SuppliersOrganization']['mail_order_close']=='N') {
                    echo '<td class="mail_no">';
                    echo 'Disabilitato del referente';
                    echo '</td>';                    
                }
                else {
                    echo '<td class="';
                    if($result['BookmarksMail']['order_close']=='N') echo "mail_no";
                    echo '">';
                    echo '<div class="checkbox">';
                    echo '<label><input type="checkbox" name="order_close" value="'.$result['SuppliersOrganization']['id'].'" ';
                    if($result['BookmarksMail']['order_close']=='Y') echo "checked"; 
                    echo '></label> ';
                    echo '</div>';
                    echo '</td>';                    
                }                
    	
		echo '</tr>';		
	}
	echo '</table></div>';
}
else 
	echo '<div class="alert alert-info" role="alert"><a data-dismiss="alert" class="close" href="#">&times;</a><strong>Non ci sono ancora produttori associati al tuo GAS</strong></div>';
?>
<script>
function updateDb(supplier_organization_id, field, value) {
    /* console.log("Per il produttore "+supplier_organization_id+" "+field+" "+value); */
 
    $("#esito_img-" + supplier_organization_id).css('opacity' ,'1');
    $("#esito_msg-" + supplier_organization_id).css('opacity' ,'1');
    
    $.ajax({
            type: "GET",
            url: "/?option=com_cake&controller=Users&action=bookmarks_mails_update&supplier_organization_id="+supplier_organization_id+"&field="+field+"&value="+value+"&format=notmpl",
            data: "",
            success: function(response) {
                    $("#esito_img-" + supplier_organization_id).attr("src", app_img + "/actions/32x32/bookmark.png");
                    $("#esito_msg-" + supplier_organization_id).html("Salvato!");
                    $("#esito_img-" + supplier_organization_id).delay(1000).animate({
                        opacity: 0
                    }, 750);
                    $("#esito_msg-" + supplier_organization_id).delay(1000).animate({
                        opacity: 0
                    }, 750);
            },
            error:function (XMLHttpRequest, textStatus, errorThrown) {
                $('#esito_msg-'+supplier_organization_id).html(textStatus);
                $('#esito_img-'+supplier_organization_id).attr('src',app_img+'/blank32x32.png');
            }
    });

    return false;    
}

$(document).ready(function() {
    $("#order_open_all").click(function() {
        $("input[name='order_open']").each(function() {
            /*
            if($(this).prop('checked'))
                $(this).prop('checked', false);
            else  
                $(this).prop('checked', true);
            */
            $(this).trigger('click');
        });    
    });
    $("#order_close_all").click(function() {
        $("input[name='order_close']").each(function() {
            /*
            if($(this).prop('checked'))
                $(this).prop('checked', false);
            else  
                $(this).prop('checked', true);
            */
            $(this).trigger('click');
        });    
    });
    $("input[name='order_open']").click(function() {
        var value = 'Y';
        if(this.checked)
            value = 'Y';
        else
            value = 'N';
        
        var supplier_organization_id = $(this).val();

        updateDb(supplier_organization_id, 'order_open', value);
        
        if(value=='Y')
            $(this).parent().parent().parent().removeClass('mail_no');
        else
            $(this).parent().parent().parent().addClass('mail_no');
    });
    
    $("input[name='order_close']").click(function() {
        var value = 'Y';
        if(this.checked)
            value = 'Y';
        else
            value = 'N';
        
        var supplier_organization_id = $(this).val();
        
        updateDb(supplier_organization_id, 'order_close', value);
        
        if(value=='Y')
            $(this).parent().parent().parent().removeClass('mail_no');
        else
            $(this).parent().parent().parent().addClass('mail_no');        
    });    
});    
</script>