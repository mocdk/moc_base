<?php
// Initialize MOC base if it's loaded
if (t3lib_extMgm::isLoaded($_EXTKEY)) {
    require t3lib_extMgm::extPath($_EXTKEY) . 'lib' . DIRECTORY_SEPARATOR . 'bootstrap.php';
}
?>