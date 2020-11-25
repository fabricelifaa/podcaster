<?php

class Podcaster
{
    private static $post_type_n = 'f_podcator';
    static $post_type_name = "Podcastor";
    static $post_type_single = "Podcast";
    static $post_type_plural = "Podcasts";
    private static $podcaster_meta = 'podcastor_media_url';
    private static $podcaster_meta_type = 'podcastor_media_type';
    private static $podcaster_meta_ext = 'podcastor_media_extension';
    private static $podcaster_meta_caption = 'podcator_caption';
    private static $podcast_view_meta = 'podcast_view';


    function init()
    {
        add_action('wp_ajax_podcastor', array('Podcaster', "ajax"));
        add_action('wp_ajax_nopriv_podcastor', array('Podcaster', "ajax"));
        add_action('admin_enqueue_scripts', array('Podcaster', 'add_podcastor_admin_custom_files'));
        add_action('wp_enqueue_scripts', array('Podcaster', 'add_podcastor_custom_file'));
        add_filter('manage_f_podcator_posts_columns', array('Podcaster', 'set_podcastor_columns'));
        add_filter('manage_f_podcator_posts_custom_column', array('Podcaster', 'set_podcastor_column_data'), 10, 2);
        add_shortcode("podcastor_code", array('Podcaster', "generate_podcastor"));
        add_action('add_meta_boxes', array('Podcaster', "register_meta_boxes"), 100);
        self::register_the_post_type();
    }

    static function register_the_post_type()
    {
        $labels = array(
            'name' => self::$post_type_plural,
            'singular_name' => self::$post_type_single,
            'menu_name' => self::$post_type_name,
            'name_admin_bar' => self::$post_type_single,
            'add_new' => 'Add cast',
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_icon' => "dashicons-album",
            "supports" => array('title', 'editor', "thumbnail"),
            'query_var' => true,
            'capability_type' => 'post',
            'hierarchical' => true,
        );
        flush_rewrite_rules();
        register_post_type(self::$post_type_n, $args);
        self::register_taxonomies();
    }

    static function register_taxonomies()
    {

        register_taxonomy(
            'podcastor_label',
            array(
                self::$post_type_n
            ),
            array(
                'label' => __("Label"),
                'hierarchical' => true,
                'show_admin_column' => true
            )
        );

        register_taxonomy(
            'podcastor_cat',
            array(
                self::$post_type_n
            ),
            array(
                'label' => __("Categories"),
                'hierarchical' => true,
                'show_admin_column' => true
            )
        );
    }

    function ajax()
    {
        if (isset($_POST["action"])) {
            $request = $_POST;
        } else {
            if (isset($_GET["action"])) {
                $request = $_GET;
            }
        }

        switch ($request["action_type"]) {
            case "update_media_link":
                if (!isset($request['post_id']) || !isset($request['media_link'])) {
                    echo 'failed';
                    exit();
                }
                $post_id = (int) sanitize_text_field($request['post_id']);
                update_post_meta($post_id, self::$podcaster_meta, esc_url_raw($request['media_link']));
                update_post_meta($post_id, self::$podcaster_meta_caption, esc_url_raw($request['caption_url']));
                update_post_meta($post_id, self::$podcaster_meta_type, sanitize_text_field($request['media_type']));
                update_post_meta($post_id, self::$podcaster_meta_ext, sanitize_text_field($request['media_ext']));
                echo json_encode(array('status' => true));
                exit();
                break;

            case "save_view":
                if (!session_id()) {
                    session_start();
                }

                if (!isset($request["post"])) {
                    echo json_encode(array("status" => false, "message" => "Error"));
                    exit();
                }
                $post_id = (int)sanitize_text_field($request["post"]);

                if (isset($_SESSION['podcast_viewed']) && in_array($post_id, $_SESSION['podcast_viewed'])) {
                    echo json_encode(array("status" => true, "message" => "podcast already viewed"));
                    exit();
                }

                self::setActualiteViews($post_id);
                $_SESSION['podcast_viewed'][]= $post_id;
                echo json_encode(array("status" => true, "message" => "podcast saved"));
                exit();
            break;

            default:
                echo 'failed';
                exit();
                break;
        }
        exit();
    }

    static function getActualiteViews($postID){
        $count_key = self::$podcast_view_meta;;
        $count = get_post_meta($postID, $count_key, true);
        if($count==''){
            delete_post_meta($postID, $count_key);
            add_post_meta($postID, $count_key, '0');
            return "0 View";
        }
        return $count.' View(s)';
    }

    static function setActualiteViews($postID) {
        $count_key = self::$podcast_view_meta;
        $count = get_post_meta($postID, $count_key, true);
        if($count==''){
            $count = 0;
            delete_post_meta($postID, $count_key);
            add_post_meta($postID, $count_key, '0');
        }else{
            $count++;
            update_post_meta($postID, $count_key, $count);
        }
    }

    function set_podcastor_columns($columns)
    {
        $columns['shortcode'] = __('Shortcode', 'f_podcator');

        return $columns;
    }

    function set_podcastor_column_data($column, $post_id)
    {
        switch ($column) {
            case "shortcode":
                $shortcode = '[podcastor_code podcast=' . $post_id . ']';
?>
                <div class='shortcode-container'>
                    <input class='sampleLink' style='border: 0px; padding: 0px; margin: 0px; position: absolute; left: -9999px; top: 0px;' value='<?php echo $shortcode; ?>' />
                    <a href='#' class='copy btn'>Copy</a>
                </div>
        <?php
                break;
        }
    }

    function add_podcastor_admin_custom_files()
    {
        wp_enqueue_script("podcastor-admin-scripts", PODCASTOR__PLUGIN_URI . "/public/js/podcastor-admin-scripts.js", array('jquery'));
        wp_enqueue_style('podcastor-style', PODCASTOR__PLUGIN_URI . '/public/css/podcastor-style.css');
    }

    function add_podcastor_custom_file()
    {
        wp_register_script("plyr", PODCASTOR__PLUGIN_URI . '/public/js/plyr.min.js', array(), '', true);
        wp_enqueue_script("plyr");
        wp_register_script("podcastor", PODCASTOR__PLUGIN_URI . '/public/js/podcastor.js', array('jquery'), '', false);
        wp_enqueue_script("podcastor");
        wp_enqueue_style('plyr', PODCASTOR__PLUGIN_URI . '/public/css/plyr.css');
        wp_localize_script('plyr', 'Podcastor', array('ajax_url' => admin_url("admin-ajax.php")));
    }

    function register_meta_boxes()
    {
        // add_meta_box('boutique-order-status', "Etats", array($this, 'order_status_content'), $this->post_type_name, 'side');
        add_meta_box('podcastor-meta-box', "Podcastor", array('Podcaster', "podcastor_metabox_content"), self::$post_type_n);
    }

    static function empty_content($str)
    {
        return trim(str_replace('&nbsp;', '', strip_tags($str))) == '';
    }

    function podcastor_metabox_content($post)
    {
        ?>
        <div class="wrap">
            <div class="col">
                <label for="">
                    <input type="radio" name="podcastor-media-type" value="audio" checked>
                    <span>Audio</span>
                </label>
                <label for="">
                    <input type="radio" name="podcastor-media-type" value="video">
                    <span>Video</span>
                </label>
            </div>

            <div class="col">
                <label for="">Media link <span style="color: red; font-weight: bold;">*</span></label><br>
                <input type="url" id="podcator-media-url" name="podcator-media-url" placeholder="Your media link" value="<?php echo get_post_meta($post->ID, self::$podcaster_meta, true); ?>">
            </div>

            <div class="d-none col" style="margin-top: 15px;" id="caption-container">
                <label for="">Caption link</label><br>
                <input type="url" id="podcastor-caption" name="podcator-caption" placeholder="Your caption link" value="">
            </div>
        </div>

        <?php
    }

    function generate_podcastor($atts)
    {
        if (isset($atts['podcast'])) {
            $postcast = (int) sanitize_text_field($atts['podcast']);
        } else {
            return Podcastor_error::get_error_message();
        }
        $url =  get_post_meta($postcast, self::$podcaster_meta, true);
        $type =  get_post_meta($postcast, self::$podcaster_meta_type, true);
        $ext =  get_post_meta($postcast, self::$podcaster_meta_ext, true);
        if (empty($url)) {
            return Podcastor_error::get_error_message('Error with shortcode try to copy again and retry.');
        }
        ob_start();
        if (!self::empty_content(get_the_content(null, false, $postcast))) {
            get_the_content(null, false, $postcast);
        }

        if ($type == "audio") {
        ?>
            <audio id="podcaster-player" controls data-post="<?php echo $postcast; ?>">
                <source src="<?php echo $url; ?>" type="<?php echo $type . "/" . $ext; ?>" />
            </audio>
        <?php
        } elseif ($type == "video") {
            if (get_post_meta($postcast, self::$podcaster_meta_caption, true)) {
                $caption =  get_post_meta($postcast, self::$podcaster_meta_caption, true);
            }

        ?>
            <video id="podcaster-player" playsinline controls data-poster="" data-post="<?php echo $postcast; ?>">
                <source src="<?php echo $url; ?>" type="<?php echo $type . "/" . $ext; ?>" />

                <!-- Captions are optional -->
                <?php if (isset($caption)) {
                ?>
                    <track kind="captions" label="English captions" src="$caption" srclang="en" default />
                <?php
                } ?>
            </video>

<?php
        }
        return ob_get_clean();
    }
}
