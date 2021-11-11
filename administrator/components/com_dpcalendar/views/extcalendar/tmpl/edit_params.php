<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2007 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$fieldSets = $this->form->getFieldsets();
foreach ($fieldSets as $name => $fieldSet)
{
	if ($name != 'params')
	{
		continue;
	}
	?>
	<div class="tab-pane" id="params-<?php echo $name;?>">
	<?php
	if (isset($fieldSet->description) && trim($fieldSet->description))
	{
		echo '<p class="alert alert-info">' . $this->escape(JText::_($fieldSet->description)) . '</p>';
	}
	?>
			<?php foreach ($this->form->getFieldset($name) as $field)
			{
				if (strtolower($field->__get('type')) == 'hidden')
				{
					echo $field->input;
				}
				else
				{?>
				<div class="control-group">
					<div class="control-label"><?php echo $field->label; ?></div>
					<div class="controls"><?php echo $field->input; ?></div>
				</div>
			<?php
				}
			} ?>
	</div>
<?php
}
