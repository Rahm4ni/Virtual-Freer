<?
/*
  Virtual Freer
  http://freer.ir/virtual

  Copyright (c) 2011 Mohammad Hossein Beyram, freer.ir

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v3 (http://www.gnu.org/licenses/gpl-3.0.html)
  as published by the Free Software Foundation.
*/
//-------------------------------------------- Start Smarty Engin
define('SITE_ROOT', dirname(dirname(__FILE__)));
define('PRESENTATION_DIR', SITE_ROOT . '/templates/');
define('BUSINESS_DIR', SITE_ROOT . '/business/');
define('SMARTY_DIR', SITE_ROOT . '/include/libs/smarty/');
define('TEMPLATE_DIR', PRESENTATION_DIR . 'templates');
define('COMPILE_DIR', ($config['smarty']['compile_dir'] != '') ? $config['smarty']['compile_dir'] : PRESENTATION_DIR . 'templates_c');
define('CONFIG_DIR', SITE_ROOT . '/include/smartyConfigs');
//------------------- Ready to use
require_once SITE_ROOT . '/include/libs/startSmarty.php';
$smarty = new startSmarty();
