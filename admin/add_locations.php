<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

add_action( 'init', 'mm_post_types' );
function mm_post_types() {

  $labels = array(
    'name'                => __( 'Map Me', 'map-me' ),
    'singular_name'       => __( 'Map Me', 'map-me' ),
    'add_new'             => __( 'Add New Location', 'map-me' ),
    'add_new_item'        => __( 'Add New Location', 'map-me' ),
    'edit_item'           => __( 'Edit Location', 'map-me' ),
    'new_item'            => __( 'New Location', 'map-me' ),
    'all_items'           => __( 'Locations', 'map-me' ),
    'view_item'           => __( 'View Location', 'map-me' ),
    'search_items'        => __( 'Search Locations', 'map-me' ),
    'not_found'           => __( 'No Location found', 'map-me' ),
    'not_found_in_trash'  => __( 'No Location found in Trash', 'map-me' ),
    'menu_name'           => __( 'Map Me', 'map-me' ),
  );

  $supports = array( 'title' );

  $args = array(
    'labels'              => $labels,
    'show_ui'             => true,
    'show_in_menu'        => 'mm_menu_page',
    'query_var'           => false,
    'rewrite'             => array( 'slug' => 'mm' ),
    'capability_type'     => 'post',
    'supports'            => $supports,
  );

  register_post_type( 'mm', $args );
}


//manage the columns of the `page` post type
function manage_columns_for_mm( $columns ) {
  //add new columns
  $columns['country'] = __( 'Country', 'map-me' );
  $columns['city'] = __( 'City', 'map-me' );
  $columns['featured'] = __( 'Featured', 'map-me' );
  return $columns;
}
add_action( 'manage_mm_posts_columns', 'manage_columns_for_mm' );

//Populate custom columns for "page" post type
function mm_populate_page_columns( $column, $post_id ) {

  $location_data = get_post_meta( $post_id, 'mm_location_data', true );

  //page content column
  echo $column == 'country' ? @$location_data['address']['country'] : null;
  echo $column == 'city' ? @$location_data['address']['city'] : null;
  echo $column == 'featured' ? @$location_data['featured'] : null;

}
add_action( 'manage_mm_posts_custom_column', 'mm_populate_page_columns', 10, 2 );


//sort columns
// add_filter( 'manage_edit-mm_sortable_columns', 'mm_table_sorting' );
// function mm_table_sorting( $columns ) {
//   $columns['country'] = 'country';
//   $columns['city'] = 'city';
//   $columns['featured'] = 'featured';
//   return $columns;
// }

// add_filter( 'request', 'mm_country_column_orderby' );
// function mm_country_column_orderby( $vars ) {
//   if ( isset( $vars['orderby'] ) && 'country' == $vars['orderby'] ) {
//     $vars = array_merge( $vars, array(
//         'meta_key' => 'mm_country',
//         'orderby' => 'meta_value'
//       ) );
//   }
//   return $vars;
// }
// add_filter( 'request', 'mm_city_column_orderby' );
// function mm_city_column_orderby( $vars ) {
//   if ( isset( $vars['orderby'] ) && 'city' == $vars['orderby'] ) {
//     $vars = array_merge( $vars, array(
//         'meta_key' => 'mm_city',
//         'orderby' => 'meta_value'
//       ) );
//   }
//   return $vars;
// }
// add_filter( 'request', 'mm_featured_column_orderby' );
// function mm_featured_column_orderby( $vars ) {
//   if ( isset( $vars['orderby'] ) && 'featured' == $vars['orderby'] ) {
//     $vars = array_merge( $vars, array(
//         'meta_key' => 'mm_featured',
//         'orderby' => 'meta_value'
//       ) );
//   }
//   return $vars;
// }




/* Meta Boxes */
function location_data_box( $object ) {
  wp_nonce_field( basename( __FILE__ ), 'meta-box-nonce1' );

  // Location Data
  $ld = get_post_meta( $object->ID, 'mm_location_data', true );

  $latitude  = isset( $ld['coordinates']['latitude'] ) ? $ld['coordinates']['latitude'] : null;
  $longitude = isset( $ld['coordinates']['longitude'] ) ? $ld['coordinates']['longitude'] : null;
  $manual    = isset($ld['coordinates']['manual']) ? $ld['coordinates']['manual'] : null;

  $address = isset($ld['address']['address']) ? $ld['address']['address'] : null;
  $city    = isset($ld['address']['city']) ? $ld['address']['city'] : null;
  $zip     = isset($ld['address']['zip']) ? $ld['address']['zip'] : null;
  $country = isset($ld['address']['country']) ? $ld['address']['country'] : null;
  $url     = isset($ld['url']) ? $ld['url'] : null;

  $description = isset($ld['description']) ? $ld['description'] : null;

  $show_address       = isset($ld['show']['address']) ? $ld['show']['address'] : false;
  $show_zip           = isset($ld['show']['zip']) ? $ld['show']['zip'] : false;
  $show_city          = isset($ld['show']['city']) ? $ld['show']['city'] : false;
  $show_country       = isset($ld['show']['country']) ? $ld['show']['country'] : false;
  $show_description   = isset($ld['show']['description']) ? $ld['show']['description'] : false;
  $show_url           = isset($ld['show']['url']) ? $ld['show']['url'] : false;

  $error = isset($ld['error']) ? $ld['error'] : null;
  $error_seen = get_option('mm_error_seen');
?>

    <?php if ($error && !$error_seen) : ?>
      <div class="mm_error_message"><?php echo $error; ?></div>
      <?php update_option('mm_error_seen', true); ?>
    <?php endif; ?>

    <table id="mm_location">
      <tbody>
      <tr>
        <th colspan="2"><?php _e("Location Info: ", 'map-me' ); ?></th>
        <th><?php _e("Show in info window: ", 'map-me'); ?></th>
      </tr>
      <tr>
          <td><strong><?php _e("Address: ", 'map-me' ); ?></strong></td>
          <td><input type="text" name="ld[address][address]" value="<?php echo $address; ?>" /></td>
          <td><input type="checkbox" id="mm_show_address" name="ld[show][address]" value="true" <?php echo $show_address ? 'checked' : null ?> /></td>
        </tr>
      <tr>
        <tr>
          <td><strong><?php _e("City: ", 'map-me' ); ?></strong></td>
          <td><input type="text" name="ld[address][city]" value="<?php echo $city; ?>" /></td>
          <td><input type="checkbox" id="mm_show_city" name="ld[show][city]" value="true" <?php echo $show_city ? 'checked' : null ?> /></td>
        </tr>
        <tr>
          <td><strong><?php _e("Zip Code: ", 'map-me' ); ?></strong></td>
          <td><input type="number" name="ld[address][zip]" value="<?php echo $zip; ?>" /></td>
          <td><input type="checkbox" id="mm_show_zip" name="ld[show][zip]" value="true" <?php echo $show_zip ? 'checked' : null ?> /></td>
        </tr>
        <tr>
          <td><strong><?php _e("Country: ", 'map-me' ); ?></strong></td>
          <td><input type="text" name="ld[address][country]" value="<?php echo $country; ?>" /></td>
          <td><input type="checkbox" id="mm_show_country" name="ld[show][country]" value="true" <?php echo $show_country ? 'checked' : null ?> /></td>
        </tr>
        <tr>
          <td><strong><?php _e("Website: ", 'map-me' ); ?></strong></td>
          <td><input type="url" name="ld[url]" value="<?php echo $url; ?>" /></td>
          <td><input type="checkbox" id="mm_show_url" name="ld[show][url]" value="true" <?php echo $show_url ? 'checked' : null ?> /></td>
        </tr>

        <tr class="mm_devider"></tr>

        <tr class="left">
          <td><strong><?php _e("Manually add <br>coordinates", 'map-me'); ?></strong></td>
          <td><input type="checkbox" id="mm_add_coordinates" name="ld[coordinates][manual]" value="true" <?php echo $manual ? 'checked' : null ?> /> <small>(not recommended)</small></td>
        </tr>

        <tr>
          <td><strong><?php _e("Latitude: ", 'map-me' ); ?></strong></td>
          <td><input type="text" id="mm_latitude" name="ld[coordinates][latitude]" value="<?php echo $latitude; ?>" <?php echo !$manual ? 'disabled class="disabled"' : null; ?> /></td>
        </tr>
        <tr>
          <td><strong><?php _e("Longitude: ", 'map-me' ); ?></strong></td>
          <td><input type="text" id="mm_longitude" name="ld[coordinates][longitude]" value="<?php echo $longitude; ?>" <?php echo !$manual ? 'disabled class="disabled"' : null; ?> /></td>
        </tr>

        <tr class="mm_devider"></tr>

        <tr>
          <td><strong><?php _e("Short Description: ", 'map-me' ); ?></td></strong></td>
          <td><textarea name="ld[description]" rows="7" maxlength="255"><?php echo $description; ?></textarea></td>
          <td><input type="checkbox" id="mm_show_description" name="ld[show][description]" value="true" <?php echo $show_description ? 'checked' : null ?> /></td>
        </tr>
      </tbody>
    </table>

<?php
}

function marker_data_box($object) {
  wp_nonce_field(basename(__FILE__), 'meta-box-nonce2');

  // Location Data
  $ld = get_post_meta($object->ID, 'mm_location_data', true);

  $featured    = isset($ld['featured']) ? $ld['featured'] : null;
  $animation   = isset($ld['animation']) ? $ld['animation'] : null;
  $info_window = isset($ld['info_window']) ? $ld['info_window'] : false;

  $show = $featured ? 'block' : 'none';

?>

<table id="mm_location_settings">
    <tbody>
      <tr>
        <td><strong><?php _e("Featured on Map: ", 'map-me' ); ?></strong></td>
        <td><input type="checkbox" id="mm_featured" name="ld[featured]" value="true" <?php echo $featured ? 'checked' : null ?> /></td>
      </tr>
      <tr class="mm_animations" style="display:<?php echo $show; ?>;">
        <td>
          <strong><?php _e("Marker Animation: ", 'map-me' ); ?></strong>
        </td>
        <td>
          <input type="radio" name="ld[animation]" id="bounce" value="BOUNCE" <?php echo $animation === 'BOUNCE' ? 'checked' : null ?> />
          <label for="bounce">Bounce: </label>
          </br>
          <input type="radio" name="ld[animation]" id="drop" value="DROP" <?php echo $animation === 'DROP' ? 'checked' : null ?> />
          <label for="drop">Drop: </label>
          </td>

        </tr>
      <tr>
      <tr>
        <td><strong><?php _e("Show Info Window: ", 'map-me'); ?></strong></td>
        <td><input type="checkbox" id="mm_info_window" name="ld[info_window]" value="true" <?php echo $info_window ? 'checked' : null ?> /></td>
      </tr>
   </tbody>
</table>

<?php
}

function marker_icon_data_box($object) {
  wp_nonce_field(basename(__FILE__), 'meta-box-nonce3');

  // Location Data
  $ld = get_post_meta($object->ID, 'mm_location_data', true);

  $icon = isset($ld['icon']) ? $ld['icon'] : null;

  $directory = icons_dir_path();
  $folder    = icons_path();

  $icons_standard = glob($directory . "standard/*.png");
  $icons_other    = glob($directory . "*.png");
?>

<div id="mm_icon_set">

    <h3>Standard Icons</h3>
<?php
  foreach ($icons_standard as $filename) :
    $location_icon = $folder.'standard/'.basename($filename); ?>

        <span class="mm_icon_group<?php echo $icon === $location_icon ? ' icon_checked' : null; ?>">
          <input type="radio" name="ld[icon]" class="mm_icon_group_radio" value="<?php echo $location_icon; ?>" <?php echo $icon === $location_icon ? 'checked' : null; ?> />
          <img src="<?php echo $location_icon; ?>" class="mm_icon" />
        </span>

    <?php endforeach; ?>


    <h3>Other Icons</h3>
<?php
  foreach ($icons_other as $filename) :
    $location_icon = $folder.basename($filename); ?>

      <span class="mm_icon_group<?php echo $icon === $location_icon ? ' icon_checked' : null; ?>">
        <input type="radio" name="ld[icon]" class="mm_icon_group_radio" value="<?php echo $location_icon; ?>" <?php echo $icon === $location_icon ? 'checked' : null; ?> />
        <img src="<?php echo $location_icon; ?>" class="mm_icon" />
      </span>

      <?php endforeach; ?>

</div>

<?php
}

function mm_add_settings_box() {
  add_meta_box('location_box', 'Location', 'location_data_box', 'mm', 'normal', 'high', null);
  add_meta_box('marker_settings_box', 'Marker Settings', 'marker_data_box', 'mm', 'normal', 'high', null);
  add_meta_box('marker_icon_box', 'Choose icon', 'marker_icon_data_box', 'mm', 'normal', 'high', null);
}

add_action('add_meta_boxes', 'mm_add_settings_box');


function mm_save_settings_box($post_id, $post, $update) {
  if ( (!isset($_POST['meta-box-nonce1']) || !wp_verify_nonce($_POST['meta-box-nonce1'], basename(__FILE__))) &&
       (!isset($_POST['meta-box-nonce2']) || !wp_verify_nonce($_POST['meta-box-nonce2'], basename(__FILE__))) &&
       (!isset($_POST['meta-box-nonce3']) || !wp_verify_nonce($_POST['meta-box-nonce3'], basename(__FILE__))) )
  {
    return $post_id;
  }

  if (!current_user_can('edit_post', $post_id)) return $post_id;

  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post_id;

  if ('mm' !== $post->post_type) return $post_id;


  $title = $post->post_title;

  // Location Data
  $ld = isset($_POST['ld']) ? $_POST['ld'] : null;

  $error     = null;
  $latitude  = null;
  $longitude = null;
  $manual    = isset($ld['coordinates']['manual']) ? $ld['coordinates']['manual'] : null;

  if ($manual) {

    $latitude  = isset( $ld['coordinates']['latitude'] ) ? $ld['coordinates']['latitude'] : null;
    $longitude = isset( $ld['coordinates']['longitude'] ) ? $ld['coordinates']['longitude'] : null;

  } else {

    $map_settings = get_option('mm_plugin_settings');
    //Main api key
    $mm_api_key_1 = isset($map_settings['api_key']) ? $map_settings['api_key'] : null;
    //Use geo api key for geocoding if available
    $mm_api_key_2 = isset($map_settings['api_key_2']) ? $map_settings['api_key_2'] : null;

    $mm_api_key   = trim($mm_api_key_2) !== '' ? $mm_api_key_2 : $mm_api_key_1;


    $full_address   = isset($ld['address']) ? implode(',', array_filter($ld['address'])) : null;

    $geo_response   = geocode($full_address, $mm_api_key);

    $error = isset($geo_response['error']) ? $geo_response['error'] : null;

    if ($error) update_option('mm_error_seen', false);

    if (isset($geo_response) && !$error) {
      $latitude  = isset($geo_response['latitude']) ? $geo_response['latitude'] : null;
      $longitude = isset($geo_response['longitude']) ? $geo_response['longitude'] : null;
    }

  }

  $address = isset($ld['address']['address']) ? $ld['address']['address'] : null;
  $city    = isset($ld['address']['city']) ? $ld['address']['city'] : null;
  $zip     = isset($ld['address']['zip']) ? $ld['address']['zip'] : null;
  $country = isset($ld['address']['country']) ? $ld['address']['country'] : null;
  $url     = isset($ld['url']) ? $ld['url'] : null;

  $description = isset($ld['description']) ? $ld['description'] : null;

  $featured    = isset($ld['featured']) ? filter_var($ld['featured'], FILTER_VALIDATE_BOOLEAN) : false;
  $animation   = isset($ld['animation']) ? $ld['animation'] : null;

  $animation = $featured && !$animation ? 'BOUNCE' : $animation;

  $info_window = isset($ld['info_window']) ? filter_var($ld['info_window'], FILTER_VALIDATE_BOOLEAN) : false;

  $icon = isset($ld['icon']) ? $ld['icon'] : null;

  $show_address       = isset($ld['show']['address']) ? filter_var($ld['show']['address'], FILTER_VALIDATE_BOOLEAN) : false;
  $show_zip           = isset($ld['show']['zip']) ? filter_var($ld['show']['zip'], FILTER_VALIDATE_BOOLEAN) : false;
  $show_city          = isset($ld['show']['city']) ? filter_var($ld['show']['city'], FILTER_VALIDATE_BOOLEAN) : false;
  $show_country       = isset($ld['show']['country']) ? filter_var($ld['show']['country'], FILTER_VALIDATE_BOOLEAN) : false;
  $show_description   = isset($ld['show']['description']) ? filter_var($ld['show']['description'], FILTER_VALIDATE_BOOLEAN) : false;
  $show_url           = isset($ld['show']['url']) ? filter_var($ld['show']['url'], FILTER_VALIDATE_BOOLEAN) : false;


  $location_data = [
    'title'       => $title,
    'coordinates'   => [
        'latitude'      => $latitude,
        'longitude'     => $longitude,
        'manual'        => $manual,
    ],
    'address'     => [
        'address'     => $address,
        'city'        => $city,
        'zip'         => $zip,
        'country'     => $country,
    ],
    'url'          => $url,
    'featured'     => $featured,
    'description'  => $description,
    'icon'         => $icon,
    'animation'    => $animation,
    'info_window'  => $info_window,
    'show'          => [
        'address'      => $show_address,
        'zip'          => $show_zip,
        'city'         => $show_city,
        'country'      => $show_country,
        'description'  => $show_description,
        'url'          => $show_url
    ],
    'error'          => $error
  ];

  update_post_meta($post->ID, 'mm_location_data', $location_data);

}

add_action('save_post', 'mm_save_settings_box', 10, 3);

?>
