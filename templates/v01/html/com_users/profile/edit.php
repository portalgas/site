<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
//load user_profile plugin language
$lang = JFactory::getLanguage();
$lang->load( 'plg_user_profile', JPATH_ADMINISTRATOR );
?>

<h2>Modifica il tuo profilo</h2>

<div class="profile-edit<?php echo $this->pageclass_sfx?>">
<?php if ($this->params->get('show_page_heading')) : ?>
	<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
<?php endif; ?>

<div class="container">
<div class="col-xs-2"></div>	
<div class="col-xs-8">	
<form class="form-horizontal"id="member-profile" action="<?php echo JRoute::_('index.php?option=com_users&task=profile.save'); ?>" method="post" class="form-validate" enctype="multipart/form-data">
<?php foreach ($this->form->getFieldsets() as $group => $fieldset):// Iterate through the form fieldsets and display each one.?>
	<?php $fields = $this->form->getFieldset($group);?>
	<?php if (count($fields)):?>
	<fieldset>
		<?php if (isset($fieldset->label)):// If the fieldset has a label set, display it as the legend.?>
		<legend><?php echo JText::_($fieldset->label); ?></legend>
		<?php endif;?>
		
			
			<?php foreach ($this->form->getFieldset($fieldset->name) as $name => $field): ?>

			<?php endforeach; ?>

			
		
		<?php foreach ($fields as $field):// Iterate through the fields in the set and display them.?>
			<?php if ($field->hidden):// If the field is hidden, just display the input.?>
				<?php echo $field->input;?>
			<?php else:
			
				 /*
				 * campi da escludere
				 */
		          if($field->name!='jform[params][editor]' && 
					 $field->name!='jform[params][timezone]' &&
					 $field->name!='jform[params][language]' &&
					 $field->name!='jform[params][admin_style]' &&
					 $field->name!='jform[params][admin_language]'&&
					 $field->name!='jform[params][helpsite]' &&

					$field->name!='jform[profile][level]' &&
					$field->name!='jform[profile][hasArticlesOrder]' &&
					$field->name!='jform[profile][codice]' &&
					$field->name!='jform[profile][phone2]' &&
					$field->name!='jform[profile][aboutme]' &&
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
						<label for="<?php echo $field->id; ?>" class="control-label col-xs-3"><?php echo $field->label; ?> 
							<?php if (!$field->required && $field->type!='Spacer' && $field->name!='jform[username]'): ?>
								<span class="optional"><?php echo JText::_('COM_USERS_OPTIONAL'); ?></span>
							<?php endif; ?>					
						</label>
						<div class="col-xs-9">			
							<?php echo $field->input; ?>
						</div>
					</div>
			<?php 
			} // end campi da escludere
			else {
				echo '<input type="hidden" value="'.$field->value.'" name="'.$field->name.'" />';
			}
			endif;?>
		<?php endforeach;?>
		
	</fieldset>
	<?php endif;?>
<?php endforeach;?>

		<div class="content-btn pull-right">
			<button type="submit" class="validate btn btn-success"><span><?php echo JText::_('JSUBMIT'); ?></span></button>
			<?php echo JText::_('COM_USERS_OR'); ?>
			<a href="<?php echo JRoute::_(''); ?>" title="<?php echo JText::_('JCANCEL'); ?>"><?php echo JText::_('JCANCEL'); ?></a>

			<input type="hidden" name="option" value="com_users" />
			<input type="hidden" name="task" value="profile.save" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
	</div>
	<div class="col-xs-2"></div>
	</div> <!-- class="container" -->	
	
</div>
