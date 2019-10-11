<?php
echo $this->Form->input('user_id',array('empty' => Configure::read('option.empty'),
										'default' => 'ALL',
										'onChange' => 'javascript:choiceUser(this);'));
?>
<script type="text/javascript">
$(document).ready(function() {
	javascript:choiceUser(this);
});
</script>