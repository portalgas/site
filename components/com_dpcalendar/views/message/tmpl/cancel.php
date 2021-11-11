<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2014 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$this->loadHelper('message');
if (empty($this->item->canceltext))
{
	$this->item->canceltext = JComponentHelper::getParams('com_dpcalendar')->get('canceltext', null);
}
// Translate message
$message = DPCalendarHelperMessage::processLanguage($this->item->canceltext);

// Parse merge tags
$message = DPCalendarHelperMessage::processPaymentTags($message, $this->item);

// Process content plugins
$message = JHTML::_('content.prepare', $message);
?>

<h1 class="componentheading">
	<?php echo $this->escape(JText::_('COM_DPCALENDAR_MESSAGE_SORRY')) ?>
</h1>

<?php echo JHTML::_('content.prepare', $message) ?>

<div class="dpcalendar-goback">
	<p><a href="<?php echo $this->return_page ?>"><?php echo JText::_('COM_DPCALENDAR_MESSAGE_BACK') ?></a></p>
</div>
