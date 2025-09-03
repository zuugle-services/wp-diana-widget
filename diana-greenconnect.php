<?php
/**
 * Plugin Name:       Diana GreenConnect
 * Plugin URI:        https://zuugle-services.com/en/diana-widget/
 * Description:       Diana GreenConnect is a trip-planning block that lets users schedule transport to and from activities with time constraints like start time, end time, and duration.
 * Version:           1.0.6
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
define('DIANA_GREENCONNECT_PLUGIN_URL', plugin_dir_url(__FILE__));
const DIANA_GREENCONNECT_VERSION = '1.0.6';
const DIANA_GREENCONNECT_BUILD_DIR = DIANA_GREENCONNECT_PLUGIN_DIR . 'build/diana-greenconnect/'; // Define path to block's build assets
const DIANA_GREENCONNECT_CDN_URL = 'https://diana.zuugle-services.net/dist/DianaWidget.bundle.js';
const DIANA_GREENCONNECT_TOKEN_ENDPOINT = 'https://api.zuugle-services.net/o/token/';

/**
 * Registers the block.
 */
function DIANA_GREENCONNECT_block_init()
{
	$block_json_path = DIANA_GREENCONNECT_BUILD_DIR . 'block.json';
	$render_php_path = DIANA_GREENCONNECT_BUILD_DIR . 'render.php';

	if (!file_exists($block_json_path)) {
		return;
	}

	// Manually include the render.php file.
	if (file_exists($render_php_path)) {
		require_once $render_php_path;
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

/**
 * Add settings link on plugin page.
 */
function DIANA_GREENCONNECT_add_settings_link($links)
{
	$settings_link = '<a href="' . admin_url('options-general.php?page=DIANA_GREENCONNECT_settings') . '">' . __('Settings', 'diana-greenconnect') . '</a>';
	array_unshift($links, $settings_link);
	return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'DIANA_GREENCONNECT_add_settings_link');


/**
 * Helper function to get block attributes from block.json.
 * Caches the result in a static variable for performance.
 *
 * @return array The block attributes.
 */
function diana_greenconnect_get_block_attributes()
{
	static $attributes = null;
	if ($attributes === null) {
		$block_json_path = DIANA_GREENCONNECT_BUILD_DIR . 'block.json';
		if (file_exists($block_json_path)) {
			$json = file_get_contents($block_json_path);
			$block_data = json_decode($json, true);
			$attributes = isset($block_data['attributes']) ? $block_data['attributes'] : [];
		} else {
			$attributes = []; // Fallback
		}
	}
	return $attributes;
}

/**
 * Handles the [diana_greenconnect_widget] shortcode.
 *
 * @param array $atts Shortcode attributes.
 * @return string The rendered HTML for the widget.
 */
function DIANA_GREENCONNECT_shortcode_handler($atts)
{
	$block_attributes_spec = diana_greenconnect_get_block_attributes();
	$defaults = [];
	foreach ($block_attributes_spec as $key => $value) {
		$defaults[strtolower($key)] = isset($value['default']) ? $value['default'] : null;
	}

	// Normalize attribute keys to lowercase for shortcode_atts
	$atts = shortcode_atts($defaults, array_change_key_case((array)$atts, CASE_LOWER), 'diana_greenconnect_widget');

	// Map lowercase attributes back to their original camelCase format for the block renderer
	$final_attributes = [];
	foreach ($atts as $lower_key => $value) {
		foreach ($block_attributes_spec as $camel_key => $spec) {
			if (strtolower($camel_key) === $lower_key) {
				$type = isset($spec['type']) ? $spec['type'] : 'string';
				if ($type === 'boolean') {
					$final_attributes[$camel_key] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
				} elseif ($type === 'integer') {
					$final_attributes[$camel_key] = intval($value);
				} else {
					$final_attributes[$camel_key] = $value;
				}
				break;
			}
		}
	}

	$widget_info = diana_greenconnect_get_block_html($final_attributes);
	return $widget_info['html'];
}
add_shortcode('diana_greenconnect_widget', 'DIANA_GREENCONNECT_shortcode_handler');


/**
 * Add settings page for API credentials.
 */
function DIANA_GREENCONNECT_add_admin_menu()
{
	$hook = add_options_page(
		__('Diana GreenConnect Settings', 'diana-greenconnect'),
		__('Diana GreenConnect', 'diana-greenconnect'),
		'manage_options',
		'DIANA_GREENCONNECT_settings',
		'DIANA_GREENCONNECT_settings_page_html'
	);
	add_action("admin_print_scripts-{$hook}", 'DIANA_GREENCONNECT_admin_scripts');
}

add_action('admin_menu', 'DIANA_GREENCONNECT_add_admin_menu');

/**
 * Enqueue admin scripts for the settings page.
 */
function DIANA_GREENCONNECT_admin_scripts()
{
	wp_enqueue_script(
		'diana-greenconnect-admin',
		DIANA_GREENCONNECT_PLUGIN_URL . 'diana-greenconnect-admin.js',
		array('jquery'),
		DIANA_GREENCONNECT_VERSION,
		true
	);
	wp_localize_script('diana-greenconnect-admin', 'diana_greenconnect_ajax', array(
		'ajax_url' => admin_url('admin-ajax.php'),
		'nonce' => wp_create_nonce('diana-greenconnect-verify-nonce'),
		'testing_text' => __('Testing...', 'diana-greenconnect'),
	));
}

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
		.diana-settings-box {
			background-color: #f7f9fc;
			border: 1px solid #e0e0e0;
			border-radius: 8px;
			padding: 25px 30px;
			margin: 20px 0;
			box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
		}

		.diana-promo-box {
			border-left: 5px solid #0a1f4a;
		}

		.diana-promo-box h2, .diana-docs-box h2 {
			font-size: 1.3em;
			margin-top: 0;
			margin-bottom: 10px;
			color: #0a1f4a;
			font-weight: 700;
		}

		.diana-promo-box p, .diana-docs-box p {
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
			background-color: #7d87ff;
			border-color: #7d87ff;
			transform: translateY(-1px);
		}

		#diana-verification-result {
			margin-top: 10px;
			padding: 10px 15px;
			border-radius: 4px;
			display: none;
		}

		#diana-verification-result.success {
			background-color: #d4edda;
			border-left: 5px solid #28a745;
			color: #155724;
		}

		#diana-verification-result.error {
			background-color: #f8d7da;
			border-left: 5px solid #dc3545;
			color: #721c24;
		}

		.diana-docs-box {
			border-left: 5px solid #7d87ff;
		}

		.diana-docs-box pre {
			background-color: #1e293b;
			color: #e2e8f0;
			padding: 15px 20px;
			border-radius: 6px;
			white-space: pre-wrap;
			word-break: break-all;
			font-size: 13px;
			line-height: 1.6;
		}

		.diana-docs-box code {
			font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, Courier, monospace;
		}

	</style>
	<div class="wrap">
		<h1><?php echo esc_html(get_admin_page_title()); ?></h1>

		<?php if (empty($client_id) || empty($client_secret)) : ?>
			<div class="diana-settings-box diana-promo-box">
				<h2><?php esc_html_e('Get Your Free API Credentials', 'diana-greenconnect'); ?></h2>
				<p><?php esc_html_e('To use the Diana GreenConnect widget, you need a Client ID and Client Secret. Register on our developer dashboard to create an application and get your credentials instantly.', 'diana-greenconnect'); ?></p>
				<a href="https://zuugle-services.com/en/diana-dashboard/" target="_blank"
				   class="button button-primary"><?php esc_html_e('Register on zuugle-services.com', 'diana-greenconnect'); ?></a>
			</div>
		<?php endif; ?>

		<form id="diana-settings-form" action="options.php" method="post">
			<?php
			settings_fields('DIANA_GREENCONNECT_settings_group');
			do_settings_sections('DIANA_GREENCONNECT_settings_page');
			?>
			<div>
				<?php submit_button(__('Save Settings', 'diana-greenconnect'), 'primary', 'submit', false); ?>
				<button type="button" id="diana-verify-credentials" class="button button-secondary" style="margin-left: 10px;"><?php esc_html_e('Test Connection', 'diana-greenconnect'); ?></button>
			</div>
			<div id="diana-verification-result"></div>
		</form>

		<div class="diana-settings-box diana-docs-box">
			<h2><?php esc_html_e('Shortcode Documentation', 'diana-greenconnect'); ?></h2>
			<p><?php esc_html_e('Use the shortcode to display the widget anywhere on your site, including in the Classic Editor, page builders, or text widgets. All block attributes are supported. Convert attribute names to all lowercase, for example, `activityName` becomes `activityname`.', 'diana-greenconnect'); ?></p>
			<p><strong><?php esc_html_e('Example:', 'diana-greenconnect'); ?></strong></p>
			<pre><code>[diana_greenconnect_widget
    widgetid="unique-event-123"
    activityname="Hiking Trip to the Alps"
    activitydurationminutes="240"
    activitystartlocation="47.422, 10.984"
    activitystartlocationtype="coordinates"
    activityendlocation="Alpine Peak"
    activityendlocationtype="address"
]</code></pre>
			<p><em><?php esc_html_e('Note: Providing a unique `widgetid` is required for the start location caching feature to work correctly with shortcodes.', 'diana-greenconnect'); ?></em></p>
		</div>

	</div>
	<?php
}

/**
 * AJAX handler for verifying credentials.
 */
function DIANA_GREENCONNECT_verify_credentials_ajax()
{
	check_ajax_referer('diana-greenconnect-verify-nonce', 'nonce');

	$client_id = isset($_POST['client_id']) ? sanitize_text_field(wp_unslash($_POST['client_id'])) : '';
	$client_secret = isset($_POST['client_secret']) ? sanitize_text_field(wp_unslash($_POST['client_secret'])) : '';

	if (empty($client_id) || empty($client_secret)) {
		wp_send_json_error(__('Please enter both Client ID and Client Secret.', 'diana-greenconnect'));
		return;
	}

	// Use a separate function to test token retrieval without caching
	$result = DIANA_GREENCONNECT_test_api_token($client_id, $client_secret);

	if (is_wp_error($result)) {
		wp_send_json_error($result->get_error_message());
	} else {
		wp_send_json_success(__('Connection successful! Your credentials are valid.', 'diana-greenconnect'));
	}
}
add_action('wp_ajax_diana_verify_credentials', 'DIANA_GREENCONNECT_verify_credentials_ajax');

/**
 * Tests API token retrieval without caching.
 */
function DIANA_GREENCONNECT_test_api_token($client_id, $client_secret)
{
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
		$error_message = isset($data['error_description']) ? $data['error_description'] : (isset($data['error']) ? $data['error'] : __('Unknown error during token retrieval from API.', 'diana-greenconnect'));
		return new WP_Error('token_retrieval_failed', __('Failed to retrieve API token: ', 'diana-greenconnect') . $error_message);
	}

	return true;
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

	$result = DIANA_GREENCONNECT_test_api_token($client_id, $client_secret);
	if (is_wp_error($result)) {
		return $result;
	}

	// Since test was successful, we can assume the main token retrieval will be too.
	// This is a simplified approach. A more robust one might reuse the token from the test call.
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
