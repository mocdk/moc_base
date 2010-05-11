<?php

########################################################################
# Extension Manager/Repository config file for ext "moc_base".
#
# Auto generated 11-05-2010 10:27
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'MOC base library',
	'description' => 'Library for MOC base classes.',
	'category' => 'MOC_BASE',
	'author' => 'Christian Winther',
	'author_email' => 'cwin@mocsystems.com',
	'shy' => '',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => 'MOC Systems',
	'version' => '1.2.1',
	'constraints' => array(
		'depends' => array(
		),
		'conflicts' => array(
		),
		'suggests' => array(
			'moc_dblib' => '',
			'moc_formlib' => '',
		),
	),
	'suggests' => array(
	),
	'_md5_values_when_last_written' => 'a:72:{s:9:"ChangeLog";s:4:"d41d";s:12:"ext_icon.gif";s:4:"ed95";s:17:"ext_localconf.php";s:4:"3ba8";s:16:"etc/doxygen.conf";s:4:"701b";s:11:"lib/MOC.php";s:4:"6c8c";s:17:"lib/bootstrap.php";s:4:"5776";s:22:"lib/MOC/Annotation.php";s:4:"469f";s:17:"lib/MOC/Array.php";s:4:"34b0";s:20:"lib/MOC/Autoload.php";s:4:"a777";s:25:"lib/MOC/Configuration.php";s:4:"dc5a";s:14:"lib/MOC/DB.php";s:4:"ad7d";s:16:"lib/MOC/Date.php";s:4:"5c2b";s:15:"lib/MOC/EID.php";s:4:"15a3";s:17:"lib/MOC/Event.php";s:4:"6c37";s:21:"lib/MOC/Exception.php";s:4:"8eed";s:21:"lib/MOC/Inflector.php";s:4:"a28f";s:16:"lib/MOC/JSON.php";s:4:"3eee";s:15:"lib/MOC/Log.php";s:4:"b5d9";s:16:"lib/MOC/Misc.php";s:4:"c8a4";s:14:"lib/MOC/Pi.php";s:4:"1d20";s:20:"lib/MOC/Registry.php";s:4:"a699";s:20:"lib/MOC/Sanitize.php";s:4:"e81e";s:18:"lib/MOC/String.php";s:4:"25d1";s:15:"lib/MOC/Uri.php";s:4:"4831";s:20:"lib/MOC/Validate.php";s:4:"3261";s:24:"lib/MOC/Api/Abstract.php";s:4:"8dbf";s:26:"lib/MOC/Api/Annotation.php";s:4:"db4f";s:25:"lib/MOC/Api/Exception.php";s:4:"13b0";s:23:"lib/MOC/Api/Service.php";s:4:"ceb6";s:32:"lib/MOC/Api/Annotation/Alias.php";s:4:"aedb";s:30:"lib/MOC/Api/Annotation/Api.php";s:4:"bb44";s:32:"lib/MOC/Api/Loader/Exception.php";s:4:"7ac4";s:29:"lib/MOC/Api/Loader/Prefix.php";s:4:"8d06";s:31:"lib/MOC/Api/Loader/Registry.php";s:4:"13c2";s:34:"lib/MOC/Api/Service/Annotation.php";s:4:"727a";s:33:"lib/MOC/Api/Service/Exception.php";s:4:"b938";s:33:"lib/MOC/Api/Service/Interface.php";s:4:"9de9";s:30:"lib/MOC/Autoload/Exception.php";s:4:"5cb6";s:30:"lib/MOC/Autoload/Interface.php";s:4:"9f1a";s:25:"lib/MOC/Autoload/Pear.php";s:4:"b94c";s:35:"lib/MOC/Configuration/Exception.php";s:4:"a715";s:29:"lib/MOC/Configuration/Ini.php";s:4:"761e";s:24:"lib/MOC/DB/Exception.php";s:4:"6148";s:28:"lib/MOC/Event/Dispatcher.php";s:4:"52a2";s:25:"lib/MOC/Log/Exception.php";s:4:"2205";s:32:"lib/MOC/Log/Adapter/Abstract.php";s:4:"c1e4";s:31:"lib/MOC/Log/Adapter/Console.php";s:4:"5247";s:33:"lib/MOC/Log/Adapter/Interface.php";s:4:"9981";s:21:"lib/MOC/XML/Error.php";s:4:"8bc0";s:20:"lib/MOC/XML/Node.php";s:4:"45c0";s:19:"lib/Zend/Config.php";s:4:"3144";s:22:"lib/Zend/Exception.php";s:4:"fcbd";s:29:"lib/Zend/Config/Exception.php";s:4:"a4ff";s:23:"lib/Zend/Config/Ini.php";s:4:"00a4";s:26:"lib/Zend/Config/Writer.php";s:4:"36d4";s:23:"lib/Zend/Config/Xml.php";s:4:"fa47";s:32:"lib/Zend/Config/Writer/Array.php";s:4:"b276";s:30:"lib/Zend/Config/Writer/Ini.php";s:4:"5d0c";s:30:"lib/Zend/Config/Writer/Xml.php";s:4:"fb41";s:27:"lib/Zend/Console/Getopt.php";s:4:"a1fe";s:37:"lib/Zend/Console/Getopt/Exception.php";s:4:"79eb";s:20:"lib/addendum/LICENSE";s:4:"9d14";s:28:"lib/addendum/annotations.php";s:4:"3585";s:46:"lib/addendum/annotations/annotation_parser.php";s:4:"76cb";s:40:"lib/addendum/annotations/doc_comment.php";s:4:"89dd";s:50:"lib/addendum/annotations/tests/acceptance_test.php";s:4:"cfc1";s:48:"lib/addendum/annotations/tests/addendum_test.php";s:4:"1369";s:44:"lib/addendum/annotations/tests/all_tests.php";s:4:"5212";s:57:"lib/addendum/annotations/tests/annotation_parser_test.php";s:4:"2ad8";s:50:"lib/addendum/annotations/tests/annotation_test.php";s:4:"fbee";s:62:"lib/addendum/annotations/tests/constrained_annotation_test.php";s:4:"cf2f";s:51:"lib/addendum/annotations/tests/doc_comment_test.php";s:4:"8a7f";}',
);

?>