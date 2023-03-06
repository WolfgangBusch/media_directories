<?php
/*
 * Media Directories AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version März 2023
 */
require_once __DIR__.'/lib/class.media_directories.php';
require_once __DIR__.'/lib/class.media_install.php';
require_once __DIR__.'/lib/class.media_plugins.php';
require_once __DIR__.'/lib/class.media_pages.php';
#
# --- Stylesheet und Scripts auch im Backend einbinden
$my_package=$this->getPackageId();
$file=rex_url::addonAssets($my_package).$my_package.'.css';
rex_view::addCssFile($file);
$file=rex_url::addonAssets($my_package).$my_package.'.js';
rex_view::addJsFile($file);
?>
