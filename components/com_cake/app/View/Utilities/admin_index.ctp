<div class="utilities">

	<h2 class="ico-users">
		<?php echo __('Utilities');?>
	</h2>

</div>

<?php
echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFonud', 'msg' => Configure::read('sys_function_not_implement')));
?>