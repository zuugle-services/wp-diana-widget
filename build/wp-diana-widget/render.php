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
	if ( in_array("clientID", $attributes, true) && in_array("clientSecret", $attributes, true) ) {
		$api_token = wp_diana_widget_get_api_token($attributes["clientID"], $attributes["clientSecret"]);
	} else {
		$api_token = wp_diana_widget_get_api_token();
	}

    if ( is_wp_error( $api_token ) ) {
        // Display an error message to admins/editors
        if ( current_user_can( 'edit_posts' ) ) {
            return sprintf(
                '<div %1$s><p class="wp-block-wp-diana-widget-error">%2$s: %3$s</p></div>',
                get_block_wrapper_attributes(),
                esc_html__( 'Diana Widget Error', 'wp-diana-widget' ),
                esc_html( $api_token->get_error_message() )
            );
        }
        // For public users show a friendly message
        error_log('Diana Widget: Failed to render due to API token error - ' . $api_token->get_error_message());
		$friendly_message = esc_html__( 'The widget is currently unavailable. Please try again later.', 'wp-diana-widget' );
        return sprintf(
			'<div %1$s><p class="wp-block-wp-diana-widget-error">%2$s</p></div>',
			get_block_wrapper_attributes(),
			$friendly_message
		);
    }

	$multiday = false;
	if (intval($attributes['multiday']) == 1 || $attributes['multiday']) {
		$multiday = true;
	}

	foreach ($attributes as $key => $value) {
		if ($value == '') {
			$attributes[$key] = null;
		}
	}

	if ($attributes["overrideUserStartLocation"] == null) {
		$attributes["overrideUserStartLocationType"] = null;
	}

	if ($attributes["activityDurationDaysFixed"] == 0) {
		$attributes["activityDurationDaysFixed"] = null;
	}

    // Prepare the widget configuration
    $widget_config = array(
		// Required
        'activityName'                     => $attributes['activityName'] ?? 'Default Activity',
        'activityType'                     => $attributes['activityType'] ?? 'Default Type',
        'activityStartLocation'            => $attributes['activityStartLocation'] ?? '',
        'activityStartLocationType'        => $attributes['activityStartLocationType'] ?? 'coordinates',
        'activityEndLocation'              => $attributes['activityEndLocation'] ?? '',
        'activityEndLocationType'          => $attributes['activityEndLocationType'] ?? 'coordinates',
        'activityEarliestStartTime'        => $attributes['activityEarliestStartTime'] ?? '09:00',
        'activityLatestStartTime'          => $attributes['activityLatestStartTime'] ?? '17:00',
        'activityEarliestEndTime'          => $attributes['activityEarliestEndTime'] ?? '10:00',
        'activityLatestEndTime'            => $attributes['activityLatestEndTime'] ?? '18:00',
        'activityDurationMinutes'          => $attributes['activityDurationMinutes'] ?? '120',
        'timezone'                         => $attributes['timezone'] ?? 'Europe/Vienna',
		// Optional
		'activityStartLocationDisplayName' => $attributes['activityStartLocationDisplayName'] ?? null,
		'activityEndLocationDisplayName'   => $attributes['activityEndLocationDisplayName'] ?? null,
        'activityStartTimeLabel'           => $attributes['activityStartTimeLabel'] ?? null,
        'activityEndTimeLabel'             => $attributes['activityEndTimeLabel'] ?? null,
        'apiBaseUrl'                       => $attributes['apiBaseUrl'] ?? 'https://api.zuugle-services.net',
        'language'                         => $attributes['language'] ?? 'EN',
		'overrideUserStartLocation'		   => $attributes['overrideUserStartLocation'] ?? null,
		'overrideUserStartLocationType'    => $attributes['overrideUserStartLocationType'] ?? null,
		'displayStartDate' 				   => $attributes['displayStartDate'] ?? null,
		'displayEndDate'				   => $attributes['displayEndDate'] ?? null,
		'destinationInputName'		       => $attributes['destinationInputName'] ?? null,
		// Multiday
		'multiday'                         => $multiday,
		'overrideActivityStartDate'        => $attributes['overrideActivityStartDate'] ?? null,
		'overrideActivityEndDate'          => $attributes['overrideActivityEndDate'] ?? null,
		'activityDurationDaysFixed'        => $attributes['activityDurationDaysFixed'] ?? null,
		// Generated token
		'apiToken'                         => $api_token, // Securely fetched token
    );

    // Filter out null values to avoid them in the JS object if not explicitly set
    $widget_config = array_filter( $widget_config, function( $value ) {
        return ! is_null( $value );
    } );

	// Convert the configuration array values to strings, except for booleans
	foreach ( $widget_config as $key => $value ) {
		if ( is_bool( $value ) ) {
			// Keep boolean values as is
			$widget_config[ $key ] = $value;
		} else {
			$widget_config[ $key ] = (string) $value;
		}
	}

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
    $wrapper_attributes = get_block_wrapper_attributes();

	// add max-height to widget container style (widget container id)
	$widgetContainerStyle = sprintf(
		'style="max-height: %s;"',
		$container_max_height
	);


    return sprintf(
        '<div %1$s><div id="%2$s" %3$s></div></div>',
		$wrapper_attributes,
        esc_attr( $widget_container_id ),
		$widgetContainerStyle
    );
}

?>
