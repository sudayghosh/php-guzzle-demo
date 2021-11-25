<?php
/*
plugin name: Fow File Extension Renderer
description: Show file extension by short code
author: SK Ghosh
author uri: http://facebook.com/sudayghosh
*/

$path = preg_replace('/wp-content.*$/', '', __DIR__);
require_once($path.'/wp-load.php');

require 'vendor/autoload.php';

function file_extension_renderer_content($atts, $content = null){
    $default = array(
        'id' => '-1',
    );
    $a = shortcode_atts($default, $atts);

    $id = $a['id'];
    global $wpdb;

    $sql = "SELECT ff.id as ext_id, ff.extension, fft.id AS file_types_id, fft.name as file_types_name, " .
            "fo.id as file_oss_id, fo.name as file_oss_name, fp.id as program_id, fp.name as program_name " .
            "FROM wp_fow_open_details AS fod INNER JOIN wp_fow_files AS ff ON fod.file_id = ff.id " .
            "INNER JOIN wp_fow_file_types AS fft ON fod.file_types_id = fft.id INNER JOIN wp_fow_oss AS fo ON fod.os_id = fo.id " .
            "INNER JOIN wp_fow_programs AS fp ON fod.program_id = fp.id where fod.file_id = " . $id . ";";

    $ext_id = 0;
    $file_types_id = 0;
    $file_oss_id = 0;
    $program_id = 0;
    $html = '<ul>';
    $rows = $wpdb->get_results($sql);
    foreach($rows as $row){  
        $tmp_ext_id = $row->ext_id;
        $tmp_file_types_id = $row->file_types_id;
        $tmp_file_oss_id = $row->file_oss_id;
        $tmp_program_id = $row->program_id;

        if($ext_id != $tmp_ext_id){
            $ext_id = $tmp_ext_id;
            $html = $html . '<li>' . $row->extension . '</li>';
            $html = $html . '<ul>';
        }
        if($file_types_id == 0){
            $file_types_id = $tmp_file_types_id;
            $html = $html . '<li>' . $row->file_types_name . '</li>';
            $html = $html . '<ul>';
        }
        if($file_types_id != $tmp_file_types_id){
            $html = $html . '</ul></ul>';
            $file_types_id = $tmp_file_types_id;
            $html = $html . '<li>' . $row->file_types_name . '</li>';
            $html = $html . '<ul>';
            $file_oss_id = 0;
        }
        if($file_oss_id == 0){
            $file_oss_id = $tmp_file_oss_id;
            $html = $html . '<li>' . $row->file_oss_name . '</li>';
            $html = $html . '<ul>';
        }
        if($file_oss_id != $tmp_file_oss_id){
            $html = $html . '</ul>';
            $file_oss_id = $tmp_file_oss_id;
            $html = $html . '<li>' . $row->file_oss_name . '</li>';
            $html = $html . '<ul>';
        }
        if($program_id != $tmp_program_id){
            $program_id = $tmp_program_id;
            $html = $html . '<li>' . $row->program_name . '</li>';
        }
        else {
            $html = $html . '</ul>';
        }
    }
    $html = $html . '</ul>';

    return $html;
}
add_shortcode('file_extension_renderer', 'file_extension_renderer_content'); 
