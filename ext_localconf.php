<?php
// Initialize MOC base if it's loaded
if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded($_EXTKEY)) {
    require \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'lib' . DIRECTORY_SEPARATOR . 'bootstrap.php';
}
?>