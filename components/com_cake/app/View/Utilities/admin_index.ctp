<div class="utilities">

	<h2 class="ico-users">
		<?php echo __('Utilities');?>
	</h2>

</div>

<?php
echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFound', 'msg' => Configure::read('sys_function_not_implement')));
?>