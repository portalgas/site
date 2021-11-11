<?php
/**
 * @version   1.13 July 2, 2012
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2012 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

require_once(dirname(__FILE__) . '/RokMenuTheme.php');

if (!class_exists('AbstractRokMenuTheme')) {

    abstract class AbstractRokMenuTheme implements RokMenuTheme {
        /**
         * @var array
         */
        protected $defaults = array();

        /**
         * @return array
         */
        public function getDefaults() {
            return $this->defaults;
        }
    }
}
