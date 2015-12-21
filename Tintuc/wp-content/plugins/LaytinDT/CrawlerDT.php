<?php

/**
 * Plugin Name: Lấy Tin Điện Thoại
 * Plugin URI: https://www.google.com
 * Description: Ðây là plugin lấy tin tự động từ website khác không dùng rss.
 * Version: 1.0 
 * Author: Nhóm tin tức công nghệ
 * Author URI: https://www.google.com
 */
include_once('Preg_matchDT.php');


CrawlingDT::init();
CrawlingDT::setup();

class CrawlingDT {

    public static function init() {

        add_action('my_cron_job_dt', array(__CLASS__, 'do_cron_job_dt'));
        add_filter('cron_schedules', array(__CLASS__, 'custom_job_time_dt'));
    }

    public static function setup() {
        if (!wp_next_scheduled('my_cron_job_dt')) {
            wp_schedule_event(time(), 'do_cron_job_time_dt', 'my_cron_job_dt');
        }
    }

    public static function custom_job_time_dt($schedules) {
        $time = get_option('laytin_time');
        $interval = '';
        switch ($time) {
            case 'pre_minute':
                $interval = '60';
                break;
            case 'hourly':
                $interval = 'hourly';
                break;
            case 'thirty_minute':
                $interval = '1800';
                break;
            case 'twicedaily':
                $interval = 'twicedaily';
                break;
            case 'daily':
                $interval = 'daily';
                break;
        }
        $schedules['do_cron_job_time_dt'] = array(
            'interval' => $interval,
            'display' => 'Cron job DT'
        );
        return $schedules;
    }

    public static function do_cron_job_dt() {
        $enable = get_option('laytin_enable');
        if ($enable == "true") {
            CrawlingDT::getCrawler("Điện Thoại");
        }
    }

    public static function getCrawler($tags) {
        $title = '';
        $description = '';
        $img = '';
        global $wpdb;
        $result = $wpdb->get_row("SELECT * FROM linkcrawler WHERE id='1'");
        $pre=new Preg_matchDT();
        $data=  $pre->get_data($result);
        foreach ($data as $val) {
            $title=$val['title'];
            $img=$val['img'];
            $description=$val['content'];
            if (get_page_by_title(trim($title), OBJECT, 'post') == null && strlen(trim($title)) > 0 && strlen(trim($description)) > 0) {
                $post_id = wp_insert_post(array(
                    'post_title' => $title,
                    'post_content' => $description,
                    'post_status' => 'publish',
                    'post_author' => 1,
                    'post_category' => array((int)($result->iddanhmuc)),
                    'tags_input' => $tags
                ));
                CrawlingDT::set_image_thumbnail($post_id, $img);
            }
        }
        
    }


    public static function set_image_thumbnail($post_id, $image_url='') {
        if(!empty($image_url)){
        $upload_dir = wp_upload_dir();
        $image_data = file_get_contents($image_url);
        $filename = basename($image_url);
        if (wp_mkdir_p($upload_dir['path']))
            $file = $upload_dir['path'] . '/' . $filename;
        else
            $file = $upload_dir['basedir'] . '/' . $filename;
        file_put_contents($file, $image_data);

        $wp_filetype = wp_check_filetype($filename, null);
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => sanitize_file_name($filename),
            'post_content' => '',
            'post_status' => 'inherit'
        );
        $attach_id = wp_insert_attachment($attachment, $file, $post_id);
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attach_id, $file);
        wp_update_attachment_metadata($attach_id, $attach_data);

        set_post_thumbnail($post_id, $attach_id);
    }
    }

}

?>