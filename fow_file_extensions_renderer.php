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
            "fo.id as os_id, fo.name as file_oss_name, fp.id as program_id, fp.name as program_name " .
            ", CONCAT(fft.id,fo.id) as file_type_os_id ".
            "FROM " . $table_prefix . "fow_open_details AS fod INNER JOIN " . $table_prefix . "fow_files AS ff ON fod.file_id = ff.id " .
            "INNER JOIN " . $table_prefix . "fow_file_types AS fft ON fod.file_types_id = fft.id INNER JOIN " . $table_prefix . "fow_oss AS fo ON fod.os_id = fo.id " .
            "INNER JOIN " . $table_prefix . "fow_programs AS fp ON fod.program_id = fp.id where fod.file_id = " . $id . ";";

    // echo $sql;
    
    $html = '';
    $rows = $wpdb->get_results($sql);
    
    $current_file_type_id = 0;    
    $current_file_type_os_id = 0;    
    $is_ul_started = false;    

    foreach($rows as $row){  
        if ($current_file_type_id != $row->file_types_id){
            if ($is_ul_started) $html .= '</ul>';
            
            $is_ul_started = false;

            $html .= '<h2>.' . strtoupper( $row->extension ) . ' <i>File Type</i> - <b>' . $row->file_types_name . '</b></h2>';               
            $current_file_type_id = $row->file_types_id;
                
        }        
        if ($current_file_type_os_id != $row->file_type_os_id){
            if ($is_ul_started) $html .= '</ul>';
            
            $is_ul_started = true;

            $html .= '<h3>In <b>' . $row->file_oss_name . '</b> .'. strtoupper( $row->extension ) . ' ' . $file_open_with_txt . '</h3><ul>';
            $current_file_type_os_id = $row->file_type_os_id;
        }

        $html .= '<li>' . $row->program_name . '</li>';
       
    }
    /*

    $ext_id = 0;
    $file_types_id = 0;
    $os_id = 0;
    $program_id = 0;
    $html .= '<br><br>========================<br>';

    foreach($rows as $row){  

        $tmp_ext_id = $row->ext_id;
        $tmp_file_types_id = $row->file_types_id;
        $tmp_file_oss_id = $row->os_id;
        $tmp_program_id = $row->program_id;

        if($ext_id != $tmp_ext_id){
            $ext_id = $tmp_ext_id;
        }
        if($file_types_id == 0){
            $file_types_id = $tmp_file_types_id;            
            $html .= '<h2>.' . strtoupper( $row->extension ) . ' <i>File Type</i> - <b>' . $row->file_types_name . '</b></h2>';
        }
        if($file_types_id != $tmp_file_types_id){            
            $file_types_id = $tmp_file_types_id;            
            $html .= '<h2>.' . strtoupper( $row->extension ) . ' <i>File Type</i> - <b>' . $row->file_types_name . '</b></h2>';            
            $os_id = 0;
        }
        if($os_id == 0){
            $os_id = $tmp_file_oss_id;
            $html = $html . '<h3>In <b>' . $row->file_oss_name . '</b> .'. strtoupper( $row->extension ) . ' ' . $file_open_with_txt . '</h3>';
        }

        if($os_id != $tmp_file_oss_id){
            $os_id = $tmp_file_oss_id;
            $html = $html . '<h3>In <b>' . $row->file_oss_name . '</b> .'. strtoupper( $row->extension ) . ' ' . $file_open_with_txt . '</h3>';
            //$html .= '<ul>';
        }
        if($program_id != $tmp_program_id){
            $program_id = $tmp_program_id;            
            $html = $html . '<div>' . $row->program_name . '</div>';
        }
    }*/
    return $html;
}
add_shortcode('file_extension_renderer', 'file_extension_renderer_content'); 
