<?php
/*
 * Media Directories AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Februar 2023
 */
$intro=file_get_contents(__DIR__.'/../README.md');
echo substr($intro,strpos($intro,'<div>'));
?>