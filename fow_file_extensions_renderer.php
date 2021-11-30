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
    $file_open_with_txt = 'File Open With';
    $default = array(
        'id' => '-1',
    );
    $a = shortcode_atts($default, $atts);

    $id = $a['id'];
    global $wpdb;
    $table_prefix = $wpdb->prefix;

    $sql = "SELECT ff.id as ext_id, ff.extension, fft.id AS file_types_id, fft.name as file_types_name, " .
            "fo.id as file_oss_id, fo.name as file_oss_name, fp.id as program_id, fp.name as program_name " .
            "FROM " . $table_prefix . "fow_open_details AS fod INNER JOIN " . $table_prefix . "fow_files AS ff ON fod.file_id = ff.id " .
            "INNER JOIN " . $table_prefix . "fow_file_types AS fft ON fod.file_types_id = fft.id INNER JOIN " . $table_prefix . "fow_oss AS fo ON fod.os_id = fo.id " .
            "INNER JOIN " . $table_prefix . "fow_programs AS fp ON fod.program_id = fp.id where fod.file_id = " . $id . ";";

    $ext_id = 0;
    $file_types_id = 0;
    $file_oss_id = 0;
    $program_id = 0;
    $html = '';
    $rows = $wpdb->get_results($sql);
    foreach($rows as $row){  
        $tmp_ext_id = $row->ext_id;
        $tmp_file_types_id = $row->file_types_id;
        $tmp_file_oss_id = $row->file_oss_id;
        $tmp_program_id = $row->program_id;

        if($ext_id != $tmp_ext_id){
            $ext_id = $tmp_ext_id;
        }
        if($file_types_id == 0){
            $file_types_id = $tmp_file_types_id;
            $html = $html . '<h2>.' . strtoupper( $row->extension ) . ' ' . $row->file_types_name . '</h2>';
        }
        if($file_types_id != $tmp_file_types_id){
            $file_types_id = $tmp_file_types_id;
            $html = $html . '<h2>.' . strtoupper( $row->extension ) . ' ' . $row->file_types_name . '</h2>';
            $file_oss_id = 0;
        }
        if($file_oss_id == 0){
            $file_oss_id = $tmp_file_oss_id;
            $html = $html . '<h3>In ' . $row->file_oss_name . ' .'. strtoupper( $row->extension ) . ' ' . $file_open_with_txt . '</h3>';
        }
        if($file_oss_id != $tmp_file_oss_id){
            $file_oss_id = $tmp_file_oss_id;
            $html = $html . '<h3>In ' . $row->file_oss_name . ' .'. strtoupper( $row->extension ) . ' ' . $file_open_with_txt . '</h3>';
        }
        if($program_id != $tmp_program_id){
            $program_id = $tmp_program_id;
            $html = $html . '<div>' . $row->program_name . '</div>';
        }
    }
    return $html;
}
add_shortcode('file_extension_renderer', 'file_extension_renderer_content'); 
