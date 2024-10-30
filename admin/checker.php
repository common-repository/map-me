<?php

function mm_checker() {

	$locations_formated = get_option( 'mm_map_locations_formated' );

	if ( $locations_formated ) return;

	global $post;

	$args = array(
		'post_type'           => 'mm',
		'post_status'         => 'publish',
		'posts_per_page'      => -1
	);

	$locations = new WP_Query( $args );

	if ( $locations->have_posts() ) {
		while ( $locations->have_posts() ) {
			$locations->the_post();

			$location_data = (get_post_meta($post->ID, 'mm_location_data', true) || false);

			// Location already formatted, skip this iteration
			if ($location_data) continue;


			$title 			  = get_the_title();
			$latitude 		  = get_post_meta($post->ID, 'mm_latitude', true);
			$longitude 		  = get_post_meta($post->ID, 'mm_longitude', true);
			$manual 		  = get_post_meta($post->ID, "mm_add_coordinates", true);
			$address 		  = get_post_meta($post->ID, "mm_address", true);
			$city 			  = get_post_meta($post->ID, "mm_city", true);
			$zip 			  = get_post_meta($post->ID, "mm_zip", true);
			$country 		  = get_post_meta($post->ID, "mm_country", true);
			$url 			  = get_post_meta($post->ID, "mm_url", true);
			$featured 		  = get_post_meta($post->ID, "mm_featured", true);
			$description 	  = get_post_meta($post->ID, "mm_description", true);
			$icon 			  = get_post_meta($post->ID, "mm_icon", true);
			$animation 		  = get_post_meta($post->ID, "mm_featured_animation", true);
			$info_window 	  = get_post_meta($post->ID, 'mm_info_window', true);
			$show_address 	  = get_post_meta($post->ID, 'mm_show_address', true);
			$show_zip 		  = get_post_meta($post->ID, 'mm_show_zip', true);
			$show_city 		  = get_post_meta($post->ID, 'mm_show_city', true);
			$show_country 	  = get_post_meta($post->ID, 'mm_show_country', true);
			$show_description = get_post_meta($post->ID, 'mm_show_description', true);
			$show_url 		  = get_post_meta($post->ID, 'mm_show_url', true);

			$manual 		  = $manual === 'yes' ? true : false;
			$featured 		  = $featured === 'yes' ? true : false;
			$info_window 	  = $info_window === 'show' ? true : false;
			$show_address 	  = $show_address === 'yes' ? true : false;
			$show_zip 		  = $show_zip === 'yes' ? true : false;
			$show_city 		  = $show_city === 'yes' ? true : false;
			$show_country 	  = $show_country === 'yes' ? true : false;
			$show_description = $show_description === 'yes' ? true : false;
			$show_url 		  = $show_url === 'yes' ? true : false;



			$location_data = [
				'title' 			=> $title,
				'coordinates'		=> [
					'latitude' 			=> $longitude,//FIX - Update inverted values (old mistake)
					'longitude' 		=> $latitude,//FIX - Update inverted values (old mistake)
					'manual'			=> $manual,
				],
				'address'			=> [
					'address'			=> $address,
					'city'				=> $city,
					'zip'				=> $zip,
					'country'			=> $country,
				],
				'url'				=> $url,
				'featured'			=> $featured,
				'description'		=> $description,
				'icon'				=> $icon,
				'animation'			=> $animation,
				'info_window'		=> $info_window,
				'show'				=> [
					'address'		=> $show_address,
					'zip'			=> $show_zip,
					'city'			=> $show_city,
					'country'		=> $show_country,
					'description'	=> $show_description,
					'url'			=> $show_url
				],
				'error'				=> null
			];


			update_post_meta($post->ID, 'mm_location_data', $location_data);

		}

		wp_reset_postdata();
	}

	$old_latitude = get_option('mm_center_latitude');
	$old_longitude = get_option('mm_center_longitude');

	//FIX - Update inverted values
	update_option('mm_center_latitude', $old_longitude);
	update_option('mm_center_longitude', $old_latitude);

	update_option('mm_map_locations_formated', true);

}

add_action('init', 'mm_checker');
