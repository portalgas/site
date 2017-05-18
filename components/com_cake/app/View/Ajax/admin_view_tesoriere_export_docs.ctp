<script type="text/javascript">
var delivery_id = <?php echo $delivery_id;?>;
var order_id    = <?php echo $order_id;?>;
var doc_options = 'to-users';

a = 'Y';  /* jQuery("input[name='user_phone1']:checked").val(); */
b = 'Y';  /* jQuery("input[name='user_email1']:checked").val(); */
c = 'N';  /* jQuery("input[name='user_address1']:checked").val(); */
d = 'Y';  /*  jQuery("input[name='totale_per_utente']:checked").val(); */
<?php 
/* jQuery("input[name='trasportAndCost2']:checked").val(); */
if(($results['Order']['hasTrasport']=='Y' && $results['Order']['trasport']!='0.00') || 
	($results['Order']['hasCostMore']=='Y' && $results['Order']['cost_more']!='0.00') ||
	($results['Order']['hasCostLess']=='Y' && $results['Order']['cost_less']!='0.00'))  
	echo "e = 'Y';";
else 
	echo "e = 'N';";
?>
f = 'N';  /* jQuery("input[name='user_avatar1']:checked").val() */
g = 'Y';  /* jQuery("input[name='dettaglio_per_utente']:checked").val();  */
h = 'N';  /* jQuery("input[name='note1']:checked").val();	*/
var url = '/administrator/index.php?option=com_cake&controller=ExportDocs&action=exportToReferent&delivery_id='+delivery_id+'&order_id='+order_id+'&doc_options='+doc_options+'&doc_formato=PREVIEW&a='+a+'&b='+b+'&c='+c+'&d='+d+'&e='+e+'&f='+f+'&g='+g+'&h='+h+'&format=notmpl';
var idDivTarget = 'tdViewId-'+order_id;
ajaxCallBox(url, idDivTarget);	
</script>