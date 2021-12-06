<?php
/*
plugin name: Fow File Extensions
description: insert file extensions data from textbox into database
author: SK Ghosh
author uri: http://facebook.com/sudayghosh
*/

$path = preg_replace('/wp-content.*$/', '', __DIR__);
require_once($path.'/wp-load.php');

require 'vendor/autoload.php';

class FileExtension {
    public function __construct(){
        add_action( 'admin_menu', array( &$this, 'my_admin_menu') );
    }

    function my_admin_menu() {
        add_menu_page(
            __( 'File Extension', 'my-textdomain' ),
            __( 'File Extension', 'my-textdomain' ),
            'manage_options',
            'file-extension',
            array( &$this, 'file_extension_page_contents' ),
            'dashicons-schedule',
            3
        );
    }

    function file_extension_page_contents() {

        if(isset($_POST['execute'])){
            global $wpdb;
            $table_prefix = $wpdb->prefix;

            $urls = $_POST['urls'];
            $split_urls = explode("\n", $urls);

            foreach ($split_urls as $url) {
                //echo $url;
                //echo '<br>';

               
                $httpClient = new \GuzzleHttp\Client();
                $response = $httpClient->get(trim($url));
                $htmlString = (string) $response->getBody();
                //print $htmlString;
                //add this line to suppress any warnings
                libxml_use_internal_errors(true);
                $doc = new DOMDocument();
                $doc->loadHTML($htmlString);               
                $xpath = new DOMXPath($doc);

                $extensions = $xpath->query('//div[@id="left"]//article//h1/text()');
                // print_r ($extensions);
                // print_r ('<br>');
                foreach ($extensions as $ext) {
                    $extension = $ext->textContent.PHP_EOL;
                    $extension = strtolower( str_replace( ".", "", trim( $extension ) ) );
                    // print_r ($extension);
                    // print_r ('<br>');

                    $ext_id = $this->get_file_id($extension);
                    // print_r ($ext_id);
                    // print_r ('<br>');

                    if ( $ext_id == -1 ) {
                        $has_details_ext = false;

                        $wpdb->insert( $wpdb->prefix . 'fow_files', array('extension'=>$extension, 'icon'=>'') );
                        $ext_id = $wpdb->insert_id;

                        // print_r ($ext_id);
                        // print_r ('<br>');

                        $sections = $xpath->query('//div[@id="left"]//article//section');
                        // print_r ($sections);
                        // print_r ('<br>');
                        
                        foreach ($sections as $section) {
                            $section_id = $section->getAttribute('id');
                            // print_r ($section_id);
                            // print_r ('<br>');
                            $file_types = $xpath->query('//section[@id="'.$section_id.'"]//div[@class="entryHeader"]//h2[@class="title"]/text()');
                            // print_r ($file_types);
                            // print_r ('<br>');
                            foreach ($file_types as $type) {
                                $file_type = trim($type->textContent.PHP_EOL);

                                $file_type_id = $this->get_file_type_id($file_type);
                                if ( $file_type_id == -1 ) {
                                    $wpdb->insert( $table_prefix . 'fow_file_types', array('name'=>$file_type, 'icon'=>'') );
                                    $file_type_id = $wpdb->insert_id;
                                }

                                $platform_wrapper = $xpath->query('//section[@id="'.$section_id.'"]//div//div[@class="programs"]//div[@class="platformwrapper"]');
                                foreach ($platform_wrapper as $p) {

                                    $platform = trim( $xpath->query('div[@class="platform"]/text()', $p)[0]->textContent.PHP_EOL );
                                    // print_r ($platform);
                                    // print_r ('-platform-<br>');
                                    $platform_id = $this->get_oss_id($platform);
                                    // print_r ($platform_id);
                                    // print_r ('-platform_id-<br>');
                                    if ( $platform_id == -1 ) {
                                        $wpdb->insert( $table_prefix . 'fow_oss', array('name'=>$platform, 'icon'=>'') );
                                        $platform_id = $wpdb->insert_id;
                                    }

                                    // print_r ($p);
                                    // print_r ('-p-<br>');

                                    $apps = $xpath->query('div[@class="apps"]//div[@class="app"]//div/div//a[1]/text()', $p);
                                    // print_r ($apps);
                                    // print_r ('-apps-<br>');
                                    
                                    foreach ($apps as $a) {
                                        $app = trim( $a->textContent.PHP_EOL );
                                        // print_r ($app);
                                        // print_r ('-app-<br>');
                                        $program_id = $this->get_program_id($app);
                                        // print_r ($program_id);
                                        // print_r ('<br>');
                                        if ( $program_id == -1 ) {
                                            $wpdb->insert( $table_prefix . 'fow_programs', array('name'=>$app, 'icon'=>'') );
                                            $program_id = $wpdb->insert_id;
                                        }

                                        $wpdb->insert( $table_prefix . 'fow_open_details', array('file_id'=>$ext_id, 'file_types_id'=>$file_type_id, 'os_id'=>$platform_id, 'program_id'=>$program_id));
                                        $has_details_ext = true;
                                    }
                                }
                            }
                        }
                        if($has_details_ext == true){
                            $short_code = '[file_extension_renderer id="' . $ext_id .'"]';
                            $this->create_post($extension, $short_code);
                        }
                    }
                }/* */
            }
            ?>
            <label><br>Executed successfully</label>
            <?php            
        }
        ?>

        <h1>
            <?php esc_html_e( 'Welcome to file extension.', 'my-plugin-textdomain' ); ?>
        </h1>
        <form action="" method="post">
            <label>Urls (Enter url with space):</label>
            <textarea id="urls" name="urls" rows="4" cols="50"></textarea>
            <br />
            <input type="submit" name="execute" value="Execute" />
        </form>
        <?php
    }

    function create_post($title, $content = null){
        $file_open_with_txt = 'File Open With';
        global $user_ID;
        $new_post = array(
        'post_title' => '.' . strtoupper( $title ) . ' ' . $file_open_with_txt,
        'post_name' => $title,
        'post_content' => $content,
        'post_status' => 'publish',
        'post_date' => date('Y-m-d H:i:s'),
        'post_author' => $user_ID,
        'post_type' => 'post',
        'post_category' => array(0)
        );
        $post_id = wp_insert_post($new_post);
    }

    function get_file_id($name){
        global $wpdb;
        $id  = -1;

        $sql = "SELECT id FROM " . $wpdb->prefix . "fow_files WHERE extension = '$name' LIMIT 1";
        $row = $wpdb->get_row( $sql );
        if (empty($row)) {
            return $id;
        }
        $id = $row->id;
        return $id;
    }

    function get_file_type_id($name){
        global $wpdb;
        $id  = -1;

        $sql = "SELECT id FROM " . $wpdb->prefix . "fow_file_types WHERE name = '$name' LIMIT 1";
        $row = $wpdb->get_row( $sql );
        if (empty($row)) {
            return $id;
        }
        $id = $row->id;
        return $id;
    }

    function get_oss_id($name){
        global $wpdb;
        $id  = -1;
        $sql = "SELECT id FROM " . $wpdb->prefix . "fow_oss WHERE name = '$name' LIMIT 1";
        $row = $wpdb->get_row( $sql );
        if (empty($row)) {
            return $id;
        }
        $id = $row->id;
        return $id;
    }

    function get_program_id($name){
        global $wpdb;
        $id  = -1;
        $sql = "SELECT id FROM " . $wpdb->prefix . "fow_programs WHERE name = '$name' LIMIT 1";
        $row = $wpdb->get_row( $sql );
        if (empty($row)) {
            return $id;
        }
        $id = $row->id;
        return $id;
    }
}

$new_user = new FileExtension();
