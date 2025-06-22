<?php
/**
 * Plugin Name:       WP Diana Widget
 * Plugin URI:        https://www.zuugle-services.com
 * Description:       WP Diana Widget is a trip-planning block that lets users schedule transport to and from activities with time constraints like start time, end time, and duration.
 * Version:           1.0.0
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            Simon Heppner
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-diana-widget
 *
 * @package WpDianaWidget
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

define('WP_DIANA_WIDGET_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_DIANA_WIDGET_BUILD_DIR', WP_DIANA_WIDGET_PLUGIN_DIR . 'build/wp-diana-widget/'); // Define path to block's build assets
define('WP_DIANA_WIDGET_CDN_URL', 'https://diana.zuugle-services.net/dist/DianaWidget.bundle.js');
define('WP_DIANA_WIDGET_TOKEN_ENDPOINT', 'https://api.zuugle-services.net/o/token/');

/**
 * Registers the block.
 */
function wp_diana_widget_block_init()
{
	$block_json_path = WP_DIANA_WIDGET_BUILD_DIR . 'block.json';
	// Path to the render.php file that was copied into the build directory by wp-scripts
	$render_php_path = WP_DIANA_WIDGET_BUILD_DIR . 'render.php';

	if (!file_exists($block_json_path)) {
		error_log('DEBUG Diana Widget Plugin: ERROR - block.json not found at ' . $block_json_path . '. Block NOT registered.');
		return;
	}

	// Manually include the render.php file.
	if (file_exists($render_php_path)) {
		require_once $render_php_path;
	} else {
		error_log('DEBUG Diana Widget Plugin: ERROR - render.php not found at ' . $render_php_path . '. Frontend rendering will fail.');
	}

	// Register the block type using its block.json file.
	if (function_exists('wp_diana_widget_wp_diana_widget_render_callback')) {
		register_block_type($block_json_path, array(
			'render_callback' => 'wp_diana_widget_wp_diana_widget_render_callback',
		));
	} else {
		error_log('DEBUG Diana Widget Plugin: ERROR - wp_diana_widget_wp_diana_widget_render_callback function not found after attempting to include render.php. Registering block without server-side render function.');
		register_block_type($block_json_path);
	}
}

add_action('init', 'wp_diana_widget_block_init');

// --- Settings Page and API Token Functions ---

/**
 * Add settings page for API credentials.
 */
function wp_diana_widget_add_admin_menu()
{
	add_options_page(
		__('Diana Widget Settings', 'wp-diana-widget'),
		__('Diana Widget', 'wp-diana-widget'),
		'manage_options',
		'wp_diana_widget_settings',
		'wp_diana_widget_settings_page_html'
	);
}

add_action('admin_menu', 'wp_diana_widget_add_admin_menu');

/**
 * Register plugin settings.
 */
function wp_diana_widget_settings_init()
{
	register_setting('wp_diana_widget_settings_group', 'wp_diana_widget_client_id', [
		'type' => 'string',
		'sanitize_callback' => 'sanitize_text_field',
		'default' => '',
	]);
	register_setting('wp_diana_widget_settings_group', 'wp_diana_widget_client_secret', [
		'type' => 'string',
		'sanitize_callback' => 'sanitize_text_field',
		'default' => '',
	]);

	add_settings_section(
		'wp_diana_widget_api_settings_section',
		__('API Credentials', 'wp-diana-widget'),
		null,
		'wp_diana_widget_settings_page'
	);

	add_settings_field(
		'wp_diana_widget_client_id_field',
		__('Client ID', 'wp-diana-widget'),
		'wp_diana_widget_client_id_field_html',
		'wp_diana_widget_settings_page',
		'wp_diana_widget_api_settings_section'
	);

	add_settings_field(
		'wp_diana_widget_client_secret_field',
		__('Client Secret', 'wp-diana-widget'),
		'wp_diana_widget_client_secret_field_html',
		'wp_diana_widget_settings_page',
		'wp_diana_widget_api_settings_section'
	);
}

add_action('admin_init', 'wp_diana_widget_settings_init');

/**
 * HTML for Client ID field.
 */
function wp_diana_widget_client_id_field_html()
{
	$client_id = get_option('wp_diana_widget_client_id');
	?>
	<input type="text" name="wp_diana_widget_client_id" id="wp_diana_widget_client_id"
		   value="<?php echo esc_attr($client_id); ?>" class="regular-text">
	<p class="description"><?php esc_html_e('Enter your Diana Widget Client ID.', 'wp-diana-widget'); ?></p>
	<?php
}

/**
 * HTML for Client Secret field.
 */
function wp_diana_widget_client_secret_field_html()
{
	$client_secret = get_option('wp_diana_widget_client_secret');
	?>
	<input type="password" name="wp_diana_widget_client_secret" id="wp_diana_widget_client_secret"
		   value="<?php echo esc_attr($client_secret); ?>" class="regular-text">
	<p class="description"><?php esc_html_e('Enter your Diana Widget Client Secret. This will be stored securely.', 'wp-diana-widget'); ?></p>
	<?php
}

/**
 * HTML for the settings page.
 */
function wp_diana_widget_settings_page_html()
{
	if (!current_user_can('manage_options')) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
		<form action="options.php" method="post">
			<?php
			settings_fields('wp_diana_widget_settings_group');
			do_settings_sections('wp_diana_widget_settings_page');
			submit_button(__('Save Settings', 'wp-diana-widget'));
			?>
		</form>
	</div>
	<?php
}

/**
 * Fetches the API token from Zuugle Services.
 */
function wp_diana_widget_get_api_token($client_id = null, $client_secret = null)
{
	$cached_token = get_transient('wp_diana_widget_api_token');
	if (false !== $cached_token) {
		return $cached_token;
	}

	if (!is_null($client_id) && !is_null($client_secret)) {
		$client_id = sanitize_text_field($client_id);
		$client_secret = sanitize_text_field($client_secret);
	} else {
		$client_id = get_option('wp_diana_widget_client_id');
		$client_secret = get_option('wp_diana_widget_client_secret');
	}

	if (empty($client_id) || empty($client_secret)) {
		error_log('DEBUG Diana Widget Plugin PHP: Client ID or Secret MISSING in settings.');
		return new WP_Error('missing_credentials', __('Client ID or Client Secret is not configured in WordPress settings (Settings > Diana Widget).', 'wp-diana-widget'));
	}

	$response = wp_remote_post(
		WP_DIANA_WIDGET_TOKEN_ENDPOINT,
		array(
			'method' => 'POST',
			'timeout' => 45,
			'body' => array(
				'grant_type' => 'client_credentials',
				'client_id' => $client_id,
				'client_secret' => $client_secret,
			),
		)
	);

	if (is_wp_error($response)) {
		error_log('DEBUG Diana Widget Plugin PHP: API token request failed (wp_remote_post error). WP_Error: ' . $response->get_error_message());
		return $response;
	}

	$body = wp_remote_retrieve_body($response);
	$data = json_decode($body, true);
	$response_code = wp_remote_retrieve_response_code($response);

	if ($response_code !== 200 || empty($data['access_token'])) {
		$error_message = isset($data['error_description']) ? $data['error_description'] : (isset($data['error']) ? $data['error'] : 'Unknown error during token retrieval from API.');
		error_log('DEBUG Diana Widget Plugin PHP: API token retrieval failed. Response code: ' . $response_code . ' Body: ' . $body);
		return new WP_Error('token_retrieval_failed', __('Failed to retrieve API token: ', 'wp-diana-widget') . $error_message);
	}

	$token = sanitize_text_field($data['access_token']);
	$expires_in = isset($data['expires_in']) ? intval($data['expires_in']) : 3600;
	set_transient('wp_diana_widget_api_token', $token, max(60, $expires_in - 60));
	return $token;
}

/**
 * Generates the HTML for the Diana Activity Widget block programmatically.
 *
 * @param array $attributes An associative array of block attributes.
 * Example:
 *
 *     $attributes = [
 *         'activityName'                     => 'Museum Visit',
 *         'activityType'                     => 'Exhibition',
 *         'activityStartLocation'            => '48.2082,16.3738', // Lat,Lon or Address string
 *         'activityStartLocationType'        => 'coordinates',    // 'coordinates' or 'address'
 *         'activityEndLocation'              => '48.2180,16.3580', // Lat,Lon or Address string
 *         'activityEndLocationType'          => 'coordinates',    // 'coordinates' or 'address'
 *         'activityEarliestStartTime'        => '10:00',
 *         'activityLatestStartTime'          => '16:00',
 *         'activityEarliestEndTime'          => '12:00',
 *         'activityLatestEndTime'            => '20:00',
 *         'activityDurationMinutes'          => '120',
 *         'activityStartLocationDisplayName' => 'City Center',
 *         'activityEndLocationDisplayName'   => 'Art Museum',
 *         'timezone'                         => 'Europe/Vienna', // Timezone in which the config times are given
 *         'activityStartTimeLabel'           => 'Arrive by',
 *         'activityEndTimeLabel'             => 'Depart by',
 *         'apiBaseUrl'                       => 'https://api.zuugle-services.net',
 *         'language'                         => 'EN',
 *         'overrideUserStartLocation'        => 'Wien, Stephansplatz',
 *         'overrideUserStartLocationType'    => 'address',
 *         'displayStartDate'                 => null,
 *         'displayEndDate'                   => null,
 *         'destinationInputName'             => 'Destination Input Placeholder',
 *         'containerMaxHeight'               => '700px',
 *         // Multiday parameters
 *         'multiday'                         => false,
 *         'overrideActivityStartDate'        => "2025-05-20", // Can also be used for single-day date
 *         'overrideActivityEndDate'          => "2025-05-25",
 *         'activityDurationDaysFixed'        => 2,
 *         // Caching & Sharing parameters
 *         'cacheUserStartLocation'           => true,
 *         'userStartLocationCacheTTLMinutes' => 15,
 *         'share'                            => true,
 *         'allowShareView'                   => true,
 *         'shareURLPrefix'                   => '',
 *
 *         // Optionally, if you need to override credentials on a per-instance basis
 *         // (though typically these are set globally in plugin settings):
 *         // 'clientID' => 'your_specific_client_id_for_this_instance',
 *         // 'clientSecret' => 'your_specific_client_secret_for_this_instance',
 *
 *         // For detailed documentation visit the GitHub zuugle-services/DianaWidget Repository README.
 * ]
 *
 * @return
 */
function get_diana_widget_html($attributes = [])
{
	if (!is_array($attributes)) {
		$attributes = [];
	}

	$block_name = 'wp-diana-widget/wp-diana-widget';

	// For programmatic use, if a widgetId isn't provided, generate one to prevent potential conflicts.
	// Note: For caching to work, a STABLE ID must be passed in the attributes.
	if (!isset($attributes['widgetId'])) {
		$attributes['widgetId'] = 'prog-' . sanitize_key(uniqid());
	}
	$containerId = 'dianaWidgetContainer-' . sanitize_key($attributes['widgetId']);

	$block_markup = sprintf(
		'<!-- wp:%s %s /-->',
		esc_attr($block_name),
		empty($attributes) ? '' : wp_json_encode($attributes)
	);

	return [
		'html' => do_blocks($block_markup),
		'containerId' => $containerId
	];
}
