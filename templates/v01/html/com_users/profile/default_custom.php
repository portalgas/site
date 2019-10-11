<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */
defined('_JEXEC') or die;

JLoader::register('JHtmlUsers', JPATH_COMPONENT . '/helpers/html/users.php');
JHtml::register('users.spacer', array('JHtmlUsers', 'spacer'));

$fieldsets = $this->form->getFieldsets();
if (isset($fieldsets['core']))   unset($fieldsets['core']);
if (isset($fieldsets['params'])) unset($fieldsets['params']);

foreach ($fieldsets as $group => $fieldset): // Iterate through the form fieldsets
	$fields = $this->form->getFieldset($group);
	if (count($fields)):
?>
<fieldset id="users-profile-custom" class="users-profile-custom-<?php echo $group;?>">
	<?php if (isset($fieldset->label)):// If the fieldset has a label set, display it as the legend.?>
	<legend><?php echo JText::_($fieldset->label); ?></legend>
	<?php endif;?>
	<dl>
	<?php foreach ($fields as $field):
		if (!$field->hidden) :
		
		 /*
		 * campi da escludere
		 */
          if($field->name!='jform[profile][level]' && 
			 $field->name!='jform[profile][hasArticlesOrder]' &&
			 $field->name!='jform[profile][codice]' &&
			 $field->name!='jform[profile][phone2]' &&
			 $field->name!='jform[profile][aboutme]'  &&
			$field->name!='jform[profile][dataRichEnter]' &&
			$field->name!='jform[profile][dataEnter]' &&
			$field->name!='jform[profile][numDeliberaEnter]' &&
			$field->name!='jform[profile][dataDeliberaEnter]' &&
			$field->name!='jform[profile][dataRichExit]' &&
			$field->name!='jform[profile][motivoRichExit]' &&
			$field->name!='jform[profile][dataExit]' &&
			$field->name!='jform[profile][numDeliberaExit]' &&
			$field->name!='jform[profile][dataDeliberaExit]' &&
			$field->name!='jform[profile][dataRestituzCassa]' &&
			$field->name!='jform[profile][notaRestituzCassa]') {
		?>

		<div class="form-group">
		  <label class="control-label col-xs-3"><?php echo $field->title; ?></label>
			<div class="col-xs-9">	
				<?php if (JHtml::isRegistered('users.'.$field->id)):?>
					<?php echo JHtml::_('users.'.$field->id, $field->value);?>
				<?php elseif (JHtml::isRegistered('users.'.$field->fieldname)):?>
					<?php echo JHtml::_('users.'.$field->fieldname, $field->value);?>
				<?php elseif (JHtml::isRegistered('users.'.$field->type)):?>
					<?php echo JHtml::_('users.'.$field->type, $field->value);?>
				<?php else:?>
					<?php echo JHtml::_('users.value', $field->value);?>
				<?php endif;?>
			</div>
		</div>	
	
		<?php
		}  // end campi da escludere
		endif;?>
	<?php endforeach;?>
	</dl>
</fieldset>
	<?php endif;?>
<?php endforeach;?>
