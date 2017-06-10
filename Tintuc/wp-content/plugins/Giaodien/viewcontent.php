<?php

include_once('Crawler.php');
define('WP_FOLDER_NAME', 'Tintuc');
require_once( $_SERVER['DOCUMENT_ROOT'] . '/' . WP_FOLDER_NAME . '/wp-config.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/' . WP_FOLDER_NAME . '/wp-includes/wp-db.php' );

$url = $_REQUEST['url'];
$noidung = get_option('laytin_content');
$loaibo = get_option('laytin_loaibo');
$crawling = new crawling();
if ($noidung) {
    echo ($crawling->get_content($url, $noidung,$loaibo));
}