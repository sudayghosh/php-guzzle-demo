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
    return 'Follow us on '.$a['id'];
}
add_shortcode('file_extension_renderer', 'file_extension_renderer_content'); 
