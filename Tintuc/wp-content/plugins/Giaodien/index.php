<?php

/**
 * Plugin Name: Giao diện
 * Plugin URI: http://google.com
 * Description: Ðây là giao diện plugin lấy tin tự động từ website khác không dùng rss.
 * Version: 1.0 
 * Author: Nhóm Tin Công Nghệ
 * Author URI: http://google.com
 */
include_once('Crawler.php');

function laytin_create_menu() {
    add_menu_page('Crawler', 'Crawler Settings', 'administrator', __FILE__, 'laytin_settings_page', plugins_url('/images/icon.png', __FILE__), 1);
}

add_action('admin_menu', 'laytin_create_menu');

function laytin_scripts() {
    wp_enqueue_script('laytin_script', plugins_url('/js/script.js', __FILE__), array('jquery'));
    wp_localize_script('laytin_script', 'laytinAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
    wp_enqueue_script('jquery');
}

add_action('init', 'laytin_scripts');

function laytin_settings_page() {
    //wp_dropdown_categories('id=danhmuc');
    include_once('templates/mainSetting.php');
}

/* Cap nhat plugin setting
 * 
 */
add_action("wp_ajax_laytin_update_plugin_setting", "laytin_update_plugin_setting");

function laytin_update_plugin_setting() {
    $time = $_POST['time'];
    $enable = $_POST['enable'];
    //cap nhat bang WP options table, su dung get_option('option name') de lay lai gia tri.
    if (update_option('laytin_time', $time) || update_option('laytin_enable', $enable)) {
        echo 1;
        die();
    } 
    if (!update_option('laytin_time', $time) && !update_option('laytin_enable', $enable)) {
        echo 0;
        die();
    } 
    
}

/* Lay noi dung cho link
 * 
 */
add_action("wp_ajax_laytin_get_link_content", "laytin_get_link_content");

function laytin_get_link_content() {
    global $wpdb;
    $id = $_POST['id'];
    $link = $wpdb->get_row("SELECT * FROM linkcrawler WHERE link = $id");
    include_once('templates/linkContent.php');
    die();
}

/* Lay noi dung cho dataView
 * 
 */
add_action("wp_ajax_laytin_data_view", "laytin_data_view");

function laytin_data_view() {
    global $wpdb;
    $arr['id'] = $_POST['id'];
    $arr['host'] = $_POST['host'];
    $arr['bieuthuc'] = $_POST['bieuthuc'];
    $arr['link'] = $_POST['link'];
    $arr['title'] = $_POST['title'];
    $arr['img'] = $_POST['img'];
    $content = $_POST['content'];
    $loaibo = $_POST['loaibo'];
    update_option('laytin_content',$content);
    update_option('laytin_loaibo',$loaibo);
    $data = crawling::get_data($arr);

    $d1 = '<table><thead><th>IMAGE</th><th>TITLE</th><th>CONTENT</th></thead>';
    $d2 = '';
    foreach ($data as $e) {
        $d2 = $d2 . '<tr>
                <td><img style="width: 200px;height=100px;" src="' . $e['img'] . '"/></td>
                <td><h3>' . $e['title'] . '</h3></td>
                <td><a target="_blank" href="' . get_home_url() . '/wp-content/plugins/Giaodien/viewcontent.php?&url=' . $e['link'] . '">View Content</a></td>
                </tr>';
    }
    $d3 = '</table>';
    echo $d1 . '' . $d2 . '' . $d3;
}

add_action("wp_ajax_save_crawler", "save_crawler");

function save_crawler() {
    global $wpdb;
    $id = $_POST['id'];
    $iddanhmuc = $_POST['iddanhmuc'];
    $host = $_POST['host'];
    $bieuthuc = $_POST['bieuthuc'];
    $url = $_POST['link'];
    $tieude = $_POST['title'];
    $hinhanh = $_POST['img'];
    $noidung = $_POST['content'];
    $loaibo = $_POST['loaibo'];
    $sql = "UPDATE linkcrawler SET iddanhmuc='" . $iddanhmuc . "',host='" . $host . "',bieuthuc='" . $bieuthuc . "',url='" . $url . "',"
            . "tieude='" . $tieude . "',hinhanh='" . $hinhanh . "',noidung='" . $noidung . "',loaibo='".$loaibo."' WHERE link ='" . $id . "'";
    $result = $wpdb->query($sql);
    echo $result ;
}
