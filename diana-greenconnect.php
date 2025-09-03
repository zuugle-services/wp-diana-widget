<?php
/**
 * Plugin Name:       Diana GreenConnect
 * Plugin URI:        https://zuugle-services.com/diana/
 * Description:       Diana GreenConnect is a trip-planning block that lets users schedule transport to and from activities with time constraints like start time, end time, and duration.
 * Version:           1.0.4
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            zuugleservices
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       diana-greenconnect
 *
 * @package WpDianaWidget
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

define('DIANA_GREENCONNECT_PLUGIN_DIR', plugin_dir_path(__FILE__));
const DIANA_GREENCONNECT_VERSION = '1.0.4';
const DIANA_GREENCONNECT_BUILD_DIR = DIANA_GREENCONNECT_PLUGIN_DIR . 'build/diana-greenconnect/'; // Define path to block's build assets
const DIANA_GREENCONNECT_CDN_URL = 'https://diana.zuugle-services.net/dist/DianaWidget.bundle.js';
const DIANA_GREENCONNECT_TOKEN_ENDPOINT = 'https://api.zuugle-services.net/o/token/';

/**
 * Registers the block.
 */
function DIANA_GREENCONNECT_block_init()
{
	$block_json_path = DIANA_GREENCONNECT_BUILD_DIR . 'block.json';
	// Path to the render.php file that was copied into the build directory by wp-scripts
	$render_php_path = DIANA_GREENCONNECT_BUILD_DIR . 'render.php';

	if (!file_exists($block_json_path)) {
		return;
	}

	// Manually include the render.php file.
	if (file_exists($render_php_path)) {
		require_once $render_php_path;
	} else {
	}

	// Register the block type using its block.json file.
	if (function_exists('DIANA_GREENCONNECT_DIANA_GREENCONNECT_render_callback')) {
		register_block_type($block_json_path, array(
			'render_callback' => 'DIANA_GREENCONNECT_DIANA_GREENCONNECT_render_callback',
		));
	} else {
		register_block_type($block_json_path);
	}
}

add_action('init', 'DIANA_GREENCONNECT_block_init');

// --- Settings Page and API Token Functions ---

/**
 * Add settings page for API credentials.
 */
function DIANA_GREENCONNECT_add_admin_menu()
{
	add_options_page(
		__('Diana GreenConnect Settings', 'diana-greenconnect'),
		__('Diana GreenConnect', 'diana-greenconnect'),
		'manage_options',
		'DIANA_GREENCONNECT_settings',
		'DIANA_GREENCONNECT_settings_page_html'
	);
}

add_action('admin_menu', 'DIANA_GREENCONNECT_add_admin_menu');

/**
 * Register plugin settings.
 */
function DIANA_GREENCONNECT_settings_init()
{
	register_setting('DIANA_GREENCONNECT_settings_group', 'DIANA_GREENCONNECT_client_id', [
		'type' => 'string',
		'sanitize_callback' => 'sanitize_text_field',
		'default' => '',
	]);
	register_setting('DIANA_GREENCONNECT_settings_group', 'DIANA_GREENCONNECT_client_secret', [
		'type' => 'string',
		'sanitize_callback' => 'sanitize_text_field',
		'default' => '',
	]);

	add_settings_section(
		'DIANA_GREENCONNECT_api_settings_section',
		__('API Credentials', 'diana-greenconnect'),
		null,
		'DIANA_GREENCONNECT_settings_page'
	);

	add_settings_field(
		'DIANA_GREENCONNECT_client_id_field',
		__('Client ID', 'diana-greenconnect'),
		'DIANA_GREENCONNECT_client_id_field_html',
		'DIANA_GREENCONNECT_settings_page',
		'DIANA_GREENCONNECT_api_settings_section'
	);

	add_settings_field(
		'DIANA_GREENCONNECT_client_secret_field',
		__('Client Secret', 'diana-greenconnect'),
		'DIANA_GREENCONNECT_client_secret_field_html',
		'DIANA_GREENCONNECT_settings_page',
		'DIANA_GREENCONNECT_api_settings_section'
	);
}

add_action('admin_init', 'DIANA_GREENCONNECT_settings_init');

/**
 * HTML for Client ID field.
 */
function DIANA_GREENCONNECT_client_id_field_html()
{
	$client_id = get_option('DIANA_GREENCONNECT_client_id');
	?>
	<input type="text" name="DIANA_GREENCONNECT_client_id" id="DIANA_GREENCONNECT_client_id"
		   value="<?php echo esc_attr($client_id); ?>" class="regular-text">
	<p class="description"><?php esc_html_e('Enter your Diana GreenConnect Widget Client ID.', 'diana-greenconnect'); ?></p>
	<?php
}

/**
 * HTML for Client Secret field.
 */
function DIANA_GREENCONNECT_client_secret_field_html()
{
	$client_secret = get_option('DIANA_GREENCONNECT_client_secret');
	?>
	<input type="password" name="DIANA_GREENCONNECT_client_secret" id="DIANA_GREENCONNECT_client_secret"
		   value="<?php echo esc_attr($client_secret); ?>" class="regular-text">
	<p class="description"><?php esc_html_e('Enter your Diana GreenConnect Widget Client Secret. This will be stored securely.', 'diana-greenconnect'); ?></p>
	<?php
}

/**
 * HTML for the settings page.
 */
function DIANA_GREENCONNECT_settings_page_html()
{
	if (!current_user_can('manage_options')) {
		return;
	}

	$client_id = get_option('DIANA_GREENCONNECT_client_id');
	$client_secret = get_option('DIANA_GREENCONNECT_client_secret');
	?>
	<style>
		.diana-promo-box {
			background-color: #f7f9fc;
			border: 1px solid #e0e0e0;
			border-left: 5px solid #0a1f4a;
			border-radius: 8px;
			padding: 25px 30px;
			margin: 20px 0;
			box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
		}

		.diana-promo-box h2 {
			font-size: 1.3em;
			margin-top: 0;
			margin-bottom: 10px;
			color: #0a1f4a;
			font-weight: 700;
		}

		.diana-promo-box p {
			font-size: 14px;
			margin-bottom: 20px;
			line-height: 1.6;
			color: #5a7170;
		}

		.diana-promo-box .button-primary {
			background-color: #c0fbd5;
			border-color: #c0fbd5;
			color: #0a1f4a;
			box-shadow: none;
			text-shadow: none;
			font-weight: bold;
			padding: 8px 16px;
			border-radius: 6px;
			transition: background-color 0.2s ease, transform 0.2s ease;
		}

		.diana-promo-box .button-primary:hover {
			background-color: #b3f2c5;
			border-color: #b3f2c5;
			transform: translateY(-1px);
		}
	</style>
	<div class="wrap">
		<h1><?php echo esc_html(get_admin_page_title()); ?></h1>

		<?php if (empty($client_id) || empty($client_secret)) : ?>
			<div class="diana-promo-box">
				<h2><?php esc_html_e('Get Your Free API Credentials', 'diana-greenconnect'); ?></h2>
				<p><?php esc_html_e('To use the Diana GreenConnect widget, you need a Client ID and Client Secret. Register on our developer dashboard to create an application and get your credentials instantly.', 'diana-greenconnect'); ?></p>
				<a href="https://zuugle-services.com/en/diana-dashboard/" target="_blank"
				   class="button button-primary"><?php esc_html_e('Register on zuugle-services.com', 'diana-greenconnect'); ?></a>
			</div>
		<?php endif; ?>

		<form action="options.php" method="post">
			<?php
			settings_fields('DIANA_GREENCONNECT_settings_group');
			do_settings_sections('DIANA_GREENCONNECT_settings_page');
			submit_button(__('Save Settings', 'diana-greenconnect'));
			?>
		</form>
	</div>
	<?php
}

/**
 * Fetches the API token from Zuugle Services.
 */
function DIANA_GREENCONNECT_get_api_token($client_id = null, $client_secret = null)
{
	$cached_token = get_transient('DIANA_GREENCONNECT_api_token');
	if (false !== $cached_token) {
		return $cached_token;
	}

	if (!is_null($client_id) && !is_null($client_secret)) {
		$client_id = sanitize_text_field($client_id);
		$client_secret = sanitize_text_field($client_secret);
	} else {
		$client_id = get_option('DIANA_GREENCONNECT_client_id');
		$client_secret = get_option('DIANA_GREENCONNECT_client_secret');
	}

	if (empty($client_id) || empty($client_secret)) {
		return new WP_Error('missing_credentials', __('Client ID or Client Secret is not configured in WordPress settings (Settings > Diana GreenConnect).', 'diana-greenconnect'));
	}

	$response = wp_remote_post(
		DIANA_GREENCONNECT_TOKEN_ENDPOINT,
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
		return $response;
	}

	$body = wp_remote_retrieve_body($response);
	$data = json_decode($body, true);
	$response_code = wp_remote_retrieve_response_code($response);

	if ($response_code !== 200 || empty($data['access_token'])) {
		$error_message = isset($data['error_description']) ? $data['error_description'] : (isset($data['error']) ? $data['error'] : 'Unknown error during token retrieval from API.');
		return new WP_Error('token_retrieval_failed', __('Failed to retrieve API token: ', 'diana-greenconnect') . $error_message);
	}

	$token = sanitize_text_field($data['access_token']);
	$expires_in = isset($data['expires_in']) ? intval($data['expires_in']) : 3600;
	set_transient('DIANA_GREENCONNECT_api_token', $token, max(60, $expires_in - 60));
	return $token;
}

/**
 * Generates the HTML for the Diana GreenConnect Widget block programmatically.
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
function diana_greenconnect_get_block_html($attributes = [])
{
	if (!is_array($attributes)) {
		$attributes = [];
	}

	$block_name = 'diana-greenconnect/diana-greenconnect';

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
