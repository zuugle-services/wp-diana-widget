<?php
/**
 * Block render callback.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block default content.
 * @param WP_Block $block      Block instance.
 * @return string Rendered block HTML.
 */
function wp_diana_widget_wp_diana_widget_render_callback( $attributes, $content, $block ) {
    // Fetch the API token
    $api_token = wp_diana_widget_get_api_token();

    if ( is_wp_error( $api_token ) ) {
        // Optionally, display an error message to admins/editors
        if ( current_user_can( 'edit_posts' ) ) {
            return sprintf(
                '<div %1$s><p class="wp-block-wp-diana-widget-error">%2$s: %3$s</p></div>',
                get_block_wrapper_attributes(),
                esc_html__( 'Diana Widget Error', 'wp-diana-widget' ),
                esc_html( $api_token->get_error_message() )
            );
        }
        // For public users, you might want to show nothing or a generic message
        error_log('Diana Widget: Failed to render due to API token error - ' . $api_token->get_error_message());
        return ''; // Or a user-friendly message
    }

    // Prepare the widget configuration
    $widget_config = array(
        'activityName'                     => $attributes['activityName'] ?? 'Default Activity',
        'activityType'                     => $attributes['activityType'] ?? 'Default Type',
        'activityStartLocation'            => $attributes['activityStartLocation'] ?? '',
        'activityStartLocationType'        => $attributes['activityStartLocationType'] ?? 'coordinates',
        'activityStartLocationDisplayName' => $attributes['activityStartLocationDisplayName'] ?? null,
        'activityEndLocation'              => $attributes['activityEndLocation'] ?? '',
        'activityEndLocationType'          => $attributes['activityEndLocationType'] ?? 'coordinates',
        'activityEndLocationDisplayName'   => $attributes['activityEndLocationDisplayName'] ?? null,
        'activityEarliestStartTime'        => $attributes['activityEarliestStartTime'] ?? '09:00',
        'activityLatestStartTime'          => $attributes['activityLatestStartTime'] ?? '17:00',
        'activityEarliestEndTime'          => $attributes['activityEarliestEndTime'] ?? '10:00',
        'activityLatestEndTime'            => $attributes['activityLatestEndTime'] ?? '18:00',
        'activityDurationMinutes'          => $attributes['activityDurationMinutes'] ?? '120',
        'timezone'                         => $attributes['timezone'] ?? 'Europe/Vienna',
        'activityStartTimeLabel'           => $attributes['activityStartTimeLabel'] ?? null,
        'activityEndTimeLabel'             => $attributes['activityEndTimeLabel'] ?? null,
        'apiBaseUrl'                       => $attributes['apiBaseUrl'] ?? 'https://api.zuugle-services.net',
        'language'                         => $attributes['language'] ?? 'EN',
        'apiToken'                         => $api_token, // Securely fetched token
    );

    // Filter out null values to avoid them in the JS object if not explicitly set
    $widget_config = array_filter( $widget_config, function( $value ) {
        return ! is_null( $value );
    } );

    $config_json = wp_json_encode( $widget_config );
    $widget_container_id = 'dianaWidgetContainer-' . uniqid(); // Unique ID for multiple widgets on a page
    $container_max_height = !empty($attributes['containerMaxHeight']) ? esc_attr($attributes['containerMaxHeight']) : 'none';

    // Prepare inline script to set the configuration
    // This script MUST be printed before the external widget script.
    $inline_script = sprintf(
        "window.dianaActivityConfigs = window.dianaActivityConfigs || {}; window.dianaActivityConfigs['%s'] = %s;",
        $widget_container_id,
        $config_json
    );
     // Add another script to initialize the widget for this specific container
    $init_script = sprintf(
        "document.addEventListener('DOMContentLoaded', function() {
            if (typeof window.DianaWidget !== 'undefined' && window.dianaActivityConfigs && window.dianaActivityConfigs['%s']) {
                new window.DianaWidget(window.dianaActivityConfigs['%s'], '%s');
            } else {
                // Fallback or retry mechanism if DianaWidget or config is not ready
                var attempts = 0;
                var intervalId = setInterval(function() {
                    attempts++;
                    if (typeof window.DianaWidget !== 'undefined' && window.dianaActivityConfigs && window.dianaActivityConfigs['%s']) {
                        clearInterval(intervalId);
                        new window.DianaWidget(window.dianaActivityConfigs['%s'], '%s');
                    } else if (attempts > 20) { // Stop after 10 seconds (20 * 500ms)
                        clearInterval(intervalId);
                        console.error('DianaWidget or its config for %s not available after several attempts.');
                    }
                }, 500);
            }
        });",
        $widget_container_id, // for the check
        $widget_container_id, // for the config access
        $widget_container_id, // for the container ID argument
        $widget_container_id, // for the check in interval
        $widget_container_id, // for the config access in interval
        $widget_container_id, // for the container ID argument in interval
        $widget_container_id  // for the error message
    );


    // Enqueue the external widget script if not already done
    // Use a unique handle to ensure it's only added once per page load, even with multiple blocks.
    if ( ! wp_script_is( 'diana-widget-external-script', 'enqueued' ) ) {
        wp_enqueue_script(
            'diana-widget-external-script',
            WP_DIANA_WIDGET_CDN_URL,
            array(), // Dependencies
            null,    // Version (null for CDN to avoid query strings if not needed)
            true     // In footer
        );
    }

    // Add the inline configuration script.
    // It's crucial this runs before the external script if the external script auto-initializes.
    // If DianaWidget constructor needs to be called explicitly, view.js or this inline script can do it.
    // The `true` for $in_footer in wp_enqueue_script for the external script means our inline script
    // needs to also be effectively "before" it. Using wp_add_inline_script with the handle of the
    // external script and 'before' position is a good way.
    // However, the widget expects window.dianaActivityConfig.
    // A better approach is to ensure the config is available when the view.js runs or when the widget itself runs.

    wp_add_inline_script( 'diana-widget-external-script', $inline_script, 'before' );
    wp_add_inline_script( 'diana-widget-external-script', $init_script, 'after' ); // Run init after main script

    // Output the container div
    // The `get_block_wrapper_attributes()` adds necessary classes like alignment.
    $wrapper_attributes = get_block_wrapper_attributes(
        array( 'style' => 'max-height:' . $container_max_height . ';' )
    );

    return sprintf(
        '<div %1$s><div id="%2$s"></div></div>',
        $wrapper_attributes,
        esc_attr( $widget_container_id )
    );
}

// The block registration in wp_diana_widget_block_init() handles associating this render callback
// with the block defined in block.json, specifically via the "render": "file:./render.php" property.
// WordPress automatically calls this function when the block is rendered on the frontend.
// To make this function callable by `register_block_type` when "render" is a file path,
// it should not be inside another function or hook that hasn't run yet by the time `register_block_type` is processed.
// The current structure where `wp_diana_widget_block_init` calls `register_block_type` which points to this file is correct.
// This file itself doesn't need to call `register_block_type`.

// The README for the widget.js states:
// Place a div with the ID dianaWidgetContainer where you want the widget to render:
// <div id="dianaWidgetContainer"></div>
// The constructor of DianaWidget is: constructor(config = {}, containerId = "dianaWidgetContainer")
// So we need to pass the unique container ID to the constructor.
// And the config should be passed directly, not globally as window.dianaActivityConfig if we initialize per instance.

// The original widget.js has: this.container = document.getElementById("dianaWidgetContainer");
// This needs to be changed in the widget.js or the WordPress plugin needs to adapt.
// For now, let's assume we can modify the widget initialization call to pass the container ID.
// The provided widget.js constructor is `constructor(config = {})` and it hardcodes `dianaWidgetContainer`.
// This means the `render.php` needs to ensure that the `div` it creates has the ID `dianaWidgetContainer`.
// If multiple blocks are on the page, this will cause issues.
// The widget.js needs to be adapted to accept a container ID.
// Let's assume for now the widget.js is updated to:
// `export default class DianaWidget { constructor(config = {}, containerId = "dianaWidgetContainer") { ... this.container = document.getElementById(containerId); ... } }`
// And the initialization in `index.html` (and thus our `view.js` or inline script) would be `new DianaWidget(config, specificContainerId);`

// Given the provided widget.js, it looks for a single `dianaWidgetContainer`.
// If we can't change widget.js, this plugin can only support one instance of the widget per page.
// Let's proceed with the assumption that widget.js *can* be adapted or that the user understands this limitation.
// The current render.php generates unique IDs and tries to make it work for multiple instances.
// This requires the widget to be initialized with `new DianaWidget(config, unique_id_for_this_instance)`.
// The `view.js` or an inline script in `render.php` will handle this.
?>
