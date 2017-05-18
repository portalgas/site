<script>
var delivery_id = 24;
var order_id = 9;
var user_id = 34;
var articlesOptions = 'options-users-cart';
var order_by = 'articles_asc';

AjaxCallToArticlesResult(delivery_id, order_id, user_id, articlesOptions, order_by);

function AjaxCallToArticlesResult(delivery_id, order_id, user_id, articlesOptions, order_by) {
	var url = "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_management_carts_users&delivery_id="+delivery_id+"&order_id="+order_id+"&user_id="+user_id+"&articlesOptions="+articlesOptions+"&order_by="+order_by+"&format=notmpl";
	var idDivTarget = 'articles-result';
	ajaxCallBox(url, idDivTarget);
}
</script>
<?php 
echo '<div id="articles-result">';
echo '</div>';
?>