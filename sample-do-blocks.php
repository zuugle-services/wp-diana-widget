<?php
/**
 * Generates the HTML for the Diana Activity Widget block programmatically.
 *
 * This function uses do_blocks() to render the block based on the provided attributes.
 * It allows embedding the Diana Widget anywhere in your PHP code, such as in a
 * theme's functions.php, a custom plugin, or a template file.
 *
 * Make sure the 'wp-diana-widget/wp-diana-widget' block is registered (i.e., the
 * WP Diana Widget plugin is active) for this function to work correctly.
 * The render_callback (render.php) for the block will handle API token fetching
 * and script enqueuing.
 *
 * @param array $attributes An associative array of block attributes.
 * These attributes correspond to those defined in the block's
 * `block.json` and used in its `render.php` callback.
 * Example:
 * [
 * 'activityName'                     => 'Museum Visit',
 * 'activityType'                     => 'Exhibition',
 * 'activityStartLocation'            => '48.2082,16.3738', // Lat,Lon or Address string
 * 'activityStartLocationType'        => 'coordinates',    // 'coordinates' or 'address'
 * 'activityStartLocationDisplayName' => 'City Center',
 * 'activityEndLocation'              => '48.2180,16.3580', // Lat,Lon or Address string
 * 'activityEndLocationType'          => 'coordinates',    // 'coordinates' or 'address'
 * 'activityEndLocationDisplayName'   => 'Art Museum',
 * 'activityEarliestStartTime'        => '10:00',
 * 'activityLatestStartTime'          => '11:00',
 * 'activityEarliestEndTime'          => '14:00',
 * 'activityLatestEndTime'            => '15:00',
 * 'activityDurationMinutes'          => '120',
 * 'timezone'                         => 'Europe/Vienna',
 * 'activityStartTimeLabel'           => 'Arrive by',
 * 'activityEndTimeLabel'             => 'Depart by',
 * 'apiBaseUrl'                       => 'https://api.zuugle-services.net',
 * 'language'                         => 'EN',
 * 'containerMaxHeight'               => '700px',
 * 'overrideUserStartLocation'        => 'Wien, Stephansplatz',
 * 'overrideUserStartLocationType'    => 'address',
 * 'displayStartDate'                 => null,
 * 'displayEndDate'                   => null,
 * 'destinationInputName'             => 'Destination Input Placeholder',
 * // Optionally, if you need to override credentials on a per-instance basis
 * // (though typically these are set globally in plugin settings):
 * // 'clientID' => 'your_specific_client_id_for_this_instance',
 * // 'clientSecret' => 'your_specific_client_secret_for_this_instance',
 * ]
 * @return string The rendered HTML of the Diana Activity Widget block. Returns an empty
 * string or error message if the block cannot be rendered.
 */
function get_diana_widget_html( $attributes = [] ) {
	$block_name = 'wp-diana-widget/wp-diana-widget';

	$block_markup = sprintf(
		'<!-- wp:%s %s /-->',
		esc_attr( $block_name ),
		empty( $attributes ) ? '' : wp_json_encode( $attributes )
	);

	return do_blocks( $block_markup );
}
?>
