<?php
/*
Plugin Name: Map Me
Plugin URI:
Description: Google Maps Plugin. Easy, fast and efficient way to embed google map into your site.
Author: Devnet
Version: 2.0.3
Author URI: https://devnet.hr
Text Domain: map-me
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

function icons_path()
{
    $folder = plugin_dir_url(__FILE__) . 'assets/icons/';
    return $folder;
}
function icons_dir_path()
{
    $folder = plugin_dir_path(__FILE__) . 'assets/icons/';
    return $folder;
}
include 'admin/geocode.php';
include 'admin/plugin_menu_page.php';
include 'admin/add_locations.php';
include 'admin/help_menu_page.php';
include 'admin/checker.php';

function mm_styles_and_scripts()
{

    $options = get_option('mm_plugin_settings');
    $mm_api_key = isset($options['api_key']) ? $options['api_key'] : null;

    if ($mm_api_key !== null) {
        $map_url = '//maps.googleapis.com/maps/api/js?key=' . $mm_api_key;
    } else {
        $map_url = '//maps.googleapis.com/maps/api/js';
    }

    wp_register_script('maps-api', $map_url, true);
    wp_register_script('init-script', plugins_url('/assets/js/init.js', __FILE__), array('jquery'), '1.0', true);
    wp_register_script('map-styles', plugins_url('/assets/js/map_styles.js', __FILE__), array('jquery'), '1.0', true);
    wp_register_style('mm_styles', plugins_url('/assets/css/mm_styles.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'mm_styles_and_scripts');

function mm_location_styles()
{
    global $post_type;
    if ('mm' == $post_type) {
        wp_enqueue_style('mm_custom_styles', plugins_url('/assets/css/mm_custom_styles.css', __FILE__));
        wp_enqueue_script('mm_custom_script', plugins_url('/assets/js/mm_custom_script.js', __FILE__), array('jquery'), '1.0', true);
    }
}
add_action('admin_print_scripts-post-new.php', 'mm_location_styles', 11);
add_action('admin_print_scripts-post.php', 'mm_location_styles', 11);

add_filter('plugin_action_links', 'mm_add_action_plugin', 10, 5);
function mm_add_action_plugin($actions, $plugin_file)
{
    static $plugin;

    if (!isset($plugin)) {
        $plugin = plugin_basename(__FILE__);
    }

    if ($plugin == $plugin_file) {

        $settings = array('settings' => '<a href="admin.php?page=mm_plugin_settings">' . __('Settings', 'General') . '</a>');

        $actions = array_merge($settings, $actions);
    }

    return $actions;
}

function mm_map_data()
{

    wp_enqueue_style('mm_styles');
    wp_enqueue_script('map-styles');
    wp_enqueue_script('maps-api');
    wp_enqueue_script('init-script');

    $map_settings = get_option('mm_plugin_settings');

    //Main api key
    $mm_api_key_1 = isset($map_settings['api_key']) ? $map_settings['api_key'] : null;
    //Use geo api key for geocoding if available
    $mm_api_key_2 = isset($map_settings['api_key_2']) ? $map_settings['api_key_2'] : null;
    $mm_api_key = trim($mm_api_key_2) !== '' ? $mm_api_key_2 : $mm_api_key_1;

    $scroll = isset($map_settings['scroll']) ? true : false;
    $controls = isset($map_settings['controls']) ? true : false;
    $map_type = isset($map_settings['map_type']) ? $map_settings['map_type'] : null;

    $map_options = [
        'zoom' => $map_settings['zoom'],
        'scroll' => $scroll,
        'controls' => $controls,
        'style' => $map_settings['styles'],
        'type' => $map_type,
    ];

    $locations = [];

    global $post;

    $args = array(
        'post_type' => 'mm',
        'post_status' => 'publish',
        'posts_per_page' => -1,
    );
    $loop = new WP_Query($args);

    if ($loop->have_posts()) :

        while ($loop->have_posts()) : $loop->the_post();

            // Location Data
            $ld = get_post_meta($post->ID, 'mm_location_data', true);

            $title = isset($ld['title']) ? $ld['title'] : null;

            $latitude = isset($ld['coordinates']['latitude']) ? $ld['coordinates']['latitude'] : null;
            $longitude = isset($ld['coordinates']['longitude']) ? $ld['coordinates']['longitude'] : null;
            $featured = isset($ld['featured']) ? $ld['featured'] : null;
            $animation = isset($ld['animation']) ? $ld['animation'] : null;
            $icon = isset($ld['icon']) ? $ld['icon'] : null;

            $address = isset($ld['address']['address']) ? $ld['address']['address'] : null;
            $city = isset($ld['address']['city']) ? $ld['address']['city'] : null;
            $zip = isset($ld['address']['zip']) ? $ld['address']['zip'] : null;
            $country = isset($ld['address']['country']) ? $ld['address']['country'] : null;
            $url = isset($ld['url']) ? $ld['url'] : null;
            $description = isset($ld['description']) ? $ld['description'] : null;

            $info_window = isset($ld['info_window']) ? $ld['info_window'] : null;

            $show_address = isset($ld['show']['address']) ? $ld['show']['address'] : null;
            $show_city = isset($ld['show']['city']) ? $ld['show']['city'] : null;
            $show_zip = isset($ld['show']['zip']) ? $ld['show']['zip'] : null;
            $show_country = isset($ld['show']['country']) ? $ld['show']['country'] : null;
            $show_description = isset($ld['show']['description']) ? $ld['show']['description'] : null;
            $show_url = isset($ld['show']['url']) ? $ld['show']['url'] : null;

            $info_window_data = '';
            $info_window_data .= '<div class="mm_info_window">';
            $info_window_data .= '<div><h5>' . $title . '</h5></div>';

            if ($address && $show_address) {
                $info_window_data .= '<div>' . $address . '</div>';
            }
            if ($zip && $show_zip) {
                $info_window_data .= '<div>' . $zip . '</div>';
            }
            if ($city && $show_city) {
                $info_window_data .= '<div>' . $city . '</div>';
            }
            if ($country && $show_country) {
                $info_window_data .= '<div>' . $country . '</div>';
            }
            if ($description && $show_description) {
                $info_window_data .= '<div> <strong>' . $description . '</strong> </div>';
            }
            if ($url && $show_url) {
                $info_window_data .= '<div><a href="' . $url . '" class="mm_location_url" target="_blank">Website</a></div>';
            }

            $info_window_data .= '</div>';

            $locations[] = [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'featured' => $featured,
                'icon' => $icon,
                'animation' => $animation,
                'info_window' => $info_window,
                'info_window_data' => $info_window_data,
            ];

        endwhile;

        wp_reset_postdata();

    endif;

    $option_check = get_option('mm_plugin_center_check');

    if (
        $option_check['city'] != $map_settings['city'] ||
        $option_check['zip'] != $map_settings['zip'] ||
        $option_check['country'] != $map_settings['country'] ||
        $option_check['address'] != $map_settings['address']
    ) {

        $full_address = $map_settings['zip'] . ', ' . $map_settings['city'] . ', ' . $map_settings['country'] . ', ' . $map_settings['address'];

        $geo_response = geocode($full_address, $mm_api_key);

        update_option('mm_center_latitude', $geo_response['latitude']);
        update_option('mm_center_longitude', $geo_response['longitude']);

        $option_check['zip'] = $map_settings['zip'];
        $option_check['city'] = $map_settings['city'];
        $option_check['country'] = $map_settings['country'];
        $option_check['address'] = $map_settings['address'];

        update_option('mm_plugin_center_check', $option_check);

        $center_at = [
            'latitude' => $geo_response['latitude'],
            'longitude' => $geo_response['longitude'],
        ];
    } else {

        $center_at = [
            'latitude' => get_option('mm_center_latitude'),
            'longitude' => get_option('mm_center_longitude'),
        ];
    }

    $data = [
        'locations' => json_encode($locations),
        'options' => json_encode($map_options),
        'center' => json_encode($center_at),
        'other' => null,
        'height' => $map_settings['height'],
    ];

    return $data;
}

function mm_map_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'id' => null,
    ), $atts, 'mm_map');

    $data = mm_map_data();

    $locations = isset($data['locations']) ? $data['locations'] : null;
    $options = isset($data['options']) ? $data['options'] : null;
    $center = isset($data['center']) ? $data['center'] : null;
    $other = isset($data['other']) ? $data['other'] : null;
    $height = isset($data['height']) ? $data['height'] : null;

    ob_start();
?>
<script>
var map_data = {
    locations: <?php echo $locations; ?>,
    options: <?php echo $options; ?>,
    center: <?php echo $center; ?>,
    other: null,
};
</script>
<div id="googleMap" style="width:100%;height:<?php echo $height; ?>px;"></div>

<?php
    return ob_get_clean();
}

add_shortcode('mm_map', 'mm_map_shortcode');