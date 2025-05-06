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

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'WP_DIANA_WIDGET_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_DIANA_WIDGET_CDN_URL', 'https://diana.zuugle-services.net/dist/dianaWidget.bundle.js' );
define( 'WP_DIANA_WIDGET_TOKEN_ENDPOINT', 'https://api.zuugle-services.net/oauth/token' ); // Example, replace with actual endpoint

/**
 * Registers the block using a `blocks-manifest.php` file.
 */
function wp_diana_widget_block_init() {
	if ( function_exists( 'wp_register_block_types_from_metadata_collection' ) ) {
		wp_register_block_types_from_metadata_collection( WP_DIANA_WIDGET_PLUGIN_DIR . 'build', WP_DIANA_WIDGET_PLUGIN_DIR . 'build/blocks-manifest.php' );
	} elseif ( function_exists( 'wp_register_block_metadata_collection' ) ) {
		wp_register_block_metadata_collection( WP_DIANA_WIDGET_PLUGIN_DIR . 'build', WP_DIANA_WIDGET_PLUGIN_DIR . 'build/blocks-manifest.php' );
		$manifest_data = require WP_DIANA_WIDGET_PLUGIN_DIR . 'build/blocks-manifest.php';
		foreach ( array_keys( $manifest_data ) as $block_type ) {
			register_block_type( WP_DIANA_WIDGET_PLUGIN_DIR . "build/{$block_type}" );
		}
	} else {
        // Fallback for older WordPress versions if necessary, though 6.7 is required.
        // This part of your original code handles registration for < 6.7 if the above functions don't exist.
        // However, your plugin requires 6.7, so this might be redundant unless supporting < 6.7 without manifest.
        $block_json_file = WP_DIANA_WIDGET_PLUGIN_DIR . 'build/wp-diana-widget/block.json';
        if ( file_exists( $block_json_file ) ) {
            register_block_type( $block_json_file );
        }
    }
}
add_action( 'init', 'wp_diana_widget_block_init' );

/**
 * Add settings page for API credentials.
 */
function wp_diana_widget_add_admin_menu() {
    add_options_page(
        __( 'Diana Widget Settings', 'wp-diana-widget' ),
        __( 'Diana Widget', 'wp-diana-widget' ),
        'manage_options',
        'wp_diana_widget_settings',
        'wp_diana_widget_settings_page_html'
    );
}
add_action( 'admin_menu', 'wp_diana_widget_add_admin_menu' );

/**
 * Register plugin settings.
 */
function wp_diana_widget_settings_init() {
    register_setting( 'wp_diana_widget_settings_group', 'wp_diana_widget_client_id', [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '',
    ]);
    register_setting( 'wp_diana_widget_settings_group', 'wp_diana_widget_client_secret', [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field', // Consider a more robust sanitization if needed, but it's a secret.
        'default' => '',
    ]);

    add_settings_section(
        'wp_diana_widget_api_settings_section',
        __( 'API Credentials', 'wp-diana-widget' ),
        null, // No callback needed for section description
        'wp_diana_widget_settings_page' // Page slug
    );

    add_settings_field(
        'wp_diana_widget_client_id_field',
        __( 'Client ID', 'wp-diana-widget' ),
        'wp_diana_widget_client_id_field_html',
        'wp_diana_widget_settings_page', // Page slug
        'wp_diana_widget_api_settings_section' // Section ID
    );

    add_settings_field(
        'wp_diana_widget_client_secret_field',
        __( 'Client Secret', 'wp-diana-widget' ),
        'wp_diana_widget_client_secret_field_html',
        'wp_diana_widget_settings_page', // Page slug
        'wp_diana_widget_api_settings_section' // Section ID
    );
}
add_action( 'admin_init', 'wp_diana_widget_settings_init' );

/**
 * HTML for Client ID field.
 */
function wp_diana_widget_client_id_field_html() {
    $client_id = get_option( 'wp_diana_widget_client_id' );
    ?>
    <input type="text" name="wp_diana_widget_client_id" id="wp_diana_widget_client_id" value="<?php echo esc_attr( $client_id ); ?>" class="regular-text">
    <p class="description"><?php esc_html_e( 'Enter your Diana Widget Client ID.', 'wp-diana-widget' ); ?></p>
    <?php
}

/**
 * HTML for Client Secret field.
 */
function wp_diana_widget_client_secret_field_html() {
    $client_secret = get_option( 'wp_diana_widget_client_secret' );
    ?>
    <input type="password" name="wp_diana_widget_client_secret" id="wp_diana_widget_client_secret" value="<?php echo esc_attr( $client_secret ); ?>" class="regular-text">
    <p class="description"><?php esc_html_e( 'Enter your Diana Widget Client Secret. This will be stored securely.', 'wp-diana-widget' ); ?></p>
    <?php
}

/**
 * HTML for the settings page.
 */
function wp_diana_widget_settings_page_html() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields( 'wp_diana_widget_settings_group' ); // Nonce, action, option_page fields
            do_settings_sections( 'wp_diana_widget_settings_page' ); // Page slug
            submit_button( __( 'Save Settings', 'wp-diana-widget' ) );
            ?>
        </form>
    </div>
    <?php
}

/**
 * Fetches the API token from Zuugle Services.
 * Implements caching using WordPress Transients.
 *
 * @return string|WP_Error The API token or a WP_Error on failure.
 */
function wp_diana_widget_get_api_token() {
    $cached_token = get_transient( 'wp_diana_widget_api_token' );
    if ( false !== $cached_token ) {
        return $cached_token;
    }

    $client_id = get_option( 'wp_diana_widget_client_id' );
    $client_secret = get_option( 'wp_diana_widget_client_secret' );

    if ( empty( $client_id ) || empty( $client_secret ) ) {
        return new WP_Error( 'missing_credentials', __( 'Client ID or Client Secret is not configured.', 'wp-diana-widget' ) );
    }

    $response = wp_remote_post(
        WP_DIANA_WIDGET_TOKEN_ENDPOINT,
        array(
            'method'    => 'POST',
            'timeout'   => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking'  => true,
            'headers'   => array(
                'Content-Type' => 'application/x-www-form-urlencoded',
            ),
            'body'      => array(
                'grant_type'    => 'client_credentials',
                'client_id'     => $client_id,
                'client_secret' => $client_secret,
            ),
            'cookies'   => array(),
        )
    );

    if ( is_wp_error( $response ) ) {
        error_log( 'Diana Widget: API token request failed. WP_Error: ' . $response->get_error_message() );
        return $response;
    }

    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );

    if ( wp_remote_retrieve_response_code( $response ) !== 200 || empty( $data['access_token'] ) ) {
        $error_message = isset($data['error_description']) ? $data['error_description'] : (isset($data['error']) ? $data['error'] : 'Unknown error during token retrieval.');
        error_log( 'Diana Widget: API token retrieval failed. Response code: ' . wp_remote_retrieve_response_code( $response ) . ' Body: ' . $body );
        return new WP_Error( 'token_retrieval_failed', __( 'Failed to retrieve API token: ', 'wp-diana-widget' ) . $error_message );
    }

    $token = sanitize_text_field( $data['access_token'] );
    $expires_in = isset( $data['expires_in'] ) ? intval( $data['expires_in'] ) : 3600; // Default to 1 hour

    // Cache the token for its validity period (minus a small buffer, e.g., 60 seconds)
    set_transient( 'wp_diana_widget_api_token', $token, max( 60, $expires_in - 60 ) );

    return $token;
}

/**
 * Enqueue scripts and styles for the block.
 * This function is typically handled by block.json `viewScript` and `style` properties for the frontend,
 * and `editorScript` and `editorStyle` for the editor.
 * However, we might need to enqueue the external widget script manually in render.php or ensure it's handled.
 * For now, render.php will handle the external script.
 */

// No direct enqueues here for the block's view script, as render.php will handle it.
// The block editor scripts are handled by block.json.

?>
