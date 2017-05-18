<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List ProdGroups'), array('controller' => 'ProdGroups', 'action' => 'index'));
$this->Html->addCrumb(__('List ProdUsersGroups'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>
<div class="prod_users_groups">
	<h2 class="ico-users">
		<?php echo __('Prod UsersGroups');?> <?php echo $prodGroup['ProdGroup']['name'];?>
		<div class="actions-img">
			<ul>
				<li><?php echo $this->Html->link(__('Add ProdUsersGroups').' '.$prodGroup['ProdGroup']['name'], array('action' => 'add', $prodGroup['ProdGroup']['id']), array('class' => 'action actionAdd','title' => __('Add ProdUsersGroups'))); ?></li>
			</ul>
		</div>
	</h2>

	<?php
	if(!empty($prodUsersGroups)) {
		echo '<ul id="sortable">';
			
			foreach ($prodUsersGroups as $numResult => $prodUsersGroup) { 
				
				echo '<li class="ui-state-default"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>';
				echo '	<div class="cbp_tmicon" id="'.$prodUsersGroup['User']['id'].'">'.($numResult+1).'</div>';
				echo 		$prodUsersGroup['User']['label'].' ('.$prodUsersGroup['ProdUsersGroup']['sort'].')';
		
				echo $this->Html->link(null, array('action' => 'delete', $prodUsersGroup['ProdUsersGroup']['id']),array('class' => 'action actionDelete','title' => __('Delete')));
		
				echo '<div class="esito" id="esito_'.$prodUsersGroup['User']['id'].'">';
				echo '</li>';
			}
		
		echo '</ul>';
	}
	else
		echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFonud'));
	
echo '</div>';
?>
	
<style type="text/css">
#sortable { list-style-type: none; margin: 0; padding: 0; width: 60%; }
#sortable li { margin: 0 3px 3px 3px; padding: 0.4em; padding-left: 1.5em; font-size: 1.4em; height: 18px; }
#sortable li span { position: absolute; margin-left: -1.3em; }
  
#sortable {
   list-style-type: none;
   margin: 0;
   padding: 0;
}

#sortable li {
    cursor: s-resize;
}
#sortable li {
    border: 1px solid #CCCCCC;
    border-radius: 3px;
    display: block;
    height: auto;
    margin: 15px 5px;
    padding: 5px;
}
#sortable li {
    background: none repeat scroll 0 0 #F8F8F8;
}
#sortable li {
    font-size: 11px;
    margin: 0 3px 3px;
    padding: 0.4em 0.4em 0.4em 1.5em;
    width: 550px; 
}
#sortable li .cbp_tmicon {
    background: none repeat scroll 0 0 #F8F8F8;
    border-radius: 50%;
    box-shadow: 0 0 0 4px #CCCCCC;
    color: #777777;
    font-size: 12px;
    font-style: normal;
    font-variant: normal;
    font-weight: bold;
    height: 18px;
    line-height: 20px;
    position: relative;
    right: -548px;
    text-align: center;
    text-transform: none;
    top: 5px;
    width: 18px;
}
</style>

<script type="text/javascript">     
 jQuery(document).ready(function() {
	var original_sort;

	jQuery( "#sortable" ).sortable({
	    start: function(e, ui) {
	    	original_sort = ui.item.index(); 
		},
	    update: function(event, ui) {
	        var new_sort = ui.item.index(); 				        
	        
	        var item = jQuery(ui.item);
	        var user_id = jQuery(item).find('.cbp_tmicon').attr('id');
	        
	        var prod_group_id = 1;
	        
	        
	        var url = '/administrator/index.php?option=com_cake&controller=ProdUsersGroups&action=sort_users&prod_group_id='+prod_group_id+'&original_sort='+original_sort+'&new_sort='+new_sort+'&format=notmpl';
	        
			
			$.ajax({
				type: "GET",
				async:false,
				url: url,
				data: "",
				success: function(response) {
					jQuery('#esito_'+user_id).css('display', 'block');
					jQuery('#esito_'+user_id).html("ok");
					jQuery('#esito_'+user_id).fadeOut(3000);
					
					ricalcola_contatore();
				},
				error: function(response){
					jQuery('#esito_'+user_id).html("error!");
				}
			});	
	    }
	});
/*	jQuery( "#sortable").disableSelection(); */
	jQuery( "#sortable").sortable({ cursor: "move" });
	jQuery( "#sortable").sortable({ placeholder: "placeholder" });
});
 
function ricalcola_contatore() {
	jQuery('.cbp_tmicon').each(function( index ) {
		var count = (index+1);
		jQuery(this).html(count);
	});
}
</script>