<?php
define('MOC_BASE_LIB_DIR', dirname(__FILE__));
define('MOC_BASE_SITE_ID', join(DIRECTORY_SEPARATOR, array_slice(explode(DIRECTORY_SEPARATOR, MOC_BASE_LIB_DIR), 0, -5)));

require_once MOC_BASE_LIB_DIR . DIRECTORY_SEPARATOR . 'MOC' . DIRECTORY_SEPARATOR . 'Autoload' . DIRECTORY_SEPARATOR . 'Exception.php';
require_once MOC_BASE_LIB_DIR . DIRECTORY_SEPARATOR . 'MOC' . DIRECTORY_SEPARATOR . 'Autoload' . DIRECTORY_SEPARATOR . 'Interface.php';
require_once MOC_BASE_LIB_DIR . DIRECTORY_SEPARATOR . 'MOC' . DIRECTORY_SEPARATOR . 'Autoload' . DIRECTORY_SEPARATOR . 'Pear.php';
require_once MOC_BASE_LIB_DIR . DIRECTORY_SEPARATOR . 'MOC' . DIRECTORY_SEPARATOR . 'Autoload.php';

// Register MOC_Autoload as a spl_autoload_register handler
spl_autoload_register(array('MOC_Autoload', 'includeClass'));

// Make sure to add this path to autoload scan list
MOC_Autoload::addPath(MOC_BASE_LIB_DIR . DIRECTORY_SEPARATOR, new MOC_Autoload_Pear());