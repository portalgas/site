<?php
echo '<div id="ajaxContent">';
echo '<div id="tabsDelivery">';
if(empty($results))
	echo '<div class="tab-content deliveries">';
	foreach($results as $numTabs => $result)
		echo '<div class="tab-pane deliveries" id="tabs-'.$numTabs.'"></div>';
	echo '</div>';
	?>
		jQuery('#tabs a').click(function (e) {
			e.preventDefault()
		  	jQuery(this).tab('show')
		})
				
			echo "jQuery('#tabs a:first').tab('show');";
			echo "\r\n";
			echo 'drawDelivery(\''.$results[0]['Delivery']['data'].'\', 0)';
		}
		jQuery('#tabs-'+numTabs).html('');
		jQuery('#tabs-'+numTabs).css('min-height','100px');	
</div>
</div>