--
-- Database query file
-- For uninstallation
--
-- @package         Content Templater
-- @version         4.6.3
--
-- @author          Peter van Westen <peter@nonumber.nl>
-- @link            http://www.nonumber.nl
-- @copyright       Copyright © 2013 NoNumber All Rights Reserved
-- @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
--

DELETE FROM `#__extensions`
WHERE `type` = 'plugin' AND `element` = 'contenttemplater';
