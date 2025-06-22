<?php
/**
 * Block render callback.
 *
 * @param array $attributes Block attributes.
 * @param string $content Block default content.
 * @param WP_Block $block Block instance.
 * @return string Rendered block HTML.
 */
function wp_diana_widget_wp_diana_widget_render_callback($attributes, $content, $block)
{
	// Fetch the API token
	if (in_array("clientID", $attributes, true) && in_array("clientSecret", $attributes, true)) {
		$api_token = wp_diana_widget_get_api_token($attributes["clientID"], $attributes["clientSecret"]);
	} else {
		$api_token = wp_diana_widget_get_api_token();
	}

	if (is_wp_error($api_token)) {
		// Display an error message to admins/editors
		if (current_user_can('edit_posts')) {
			return sprintf(
				'<div %1$s><p class="wp-block-wp-diana-widget-error">%2$s: %3$s</p></div>',
				get_block_wrapper_attributes(),
				esc_html__('Diana Widget Error', 'wp-diana-widget'),
				esc_html($api_token->get_error_message())
			);
		}
		// For public users show a friendly message
		$friendly_message = esc_html__('The widget is currently unavailable. Please try again later.', 'wp-diana-widget');
		return sprintf(
			'<div %1$s><p class="wp-block-wp-diana-widget-error">%2$s</p></div>',
			get_block_wrapper_attributes(),
			$friendly_message
		);
	}

	// Sanitize boolean attributes
	$multiday = !empty($attributes['multiday']);
	$cacheUserStartLocation = isset($attributes['cacheUserStartLocation']) ? (bool)$attributes['cacheUserStartLocation'] : true;
	$share = isset($attributes['share']) ? (bool)$attributes['share'] : true;
	$allowShareView = isset($attributes['allowShareView']) ? (bool)$attributes['allowShareView'] : true;

	foreach ($attributes as $key => $value) {
		if ($value === '') {
			$attributes[$key] = null;
		}
	}

	if (isset($attributes["activityDurationDaysFixed"]) && intval($attributes["activityDurationDaysFixed"]) === 0) {
		$attributes["activityDurationDaysFixed"] = null;
	}

	if (empty($attributes["overrideUserStartLocation"])) {
		$attributes["overrideUserStartLocationType"] = null;
	}


	// Prepare the widget configuration
	$widget_config = array(
		// Required
		'activityName' => $attributes['activityName'] ?? 'Default Activity',
		'activityType' => $attributes['activityType'] ?? 'Default Type',
		'activityStartLocation' => $attributes['activityStartLocation'] ?? '',
		'activityStartLocationType' => $attributes['activityStartLocationType'] ?? 'coordinates',
		'activityEndLocation' => $attributes['activityEndLocation'] ?? '',
		'activityEndLocationType' => $attributes['activityEndLocationType'] ?? 'coordinates',
		'activityEarliestStartTime' => $attributes['activityEarliestStartTime'] ?? '09:00',
		'activityLatestStartTime' => $attributes['activityLatestStartTime'] ?? '17:00',
		'activityEarliestEndTime' => $attributes['activityEarliestEndTime'] ?? '10:00',
		'activityLatestEndTime' => $attributes['activityLatestEndTime'] ?? '18:00',
		'activityDurationMinutes' => $attributes['activityDurationMinutes'] ?? '120',
		'timezone' => $attributes['timezone'] ?? 'Europe/Vienna',
		// Optional
		'activityStartLocationDisplayName' => $attributes['activityStartLocationDisplayName'],
		'activityEndLocationDisplayName' => $attributes['activityEndLocationDisplayName'],
		'activityStartTimeLabel' => $attributes['activityStartTimeLabel'],
		'activityEndTimeLabel' => $attributes['activityEndTimeLabel'],
		'apiBaseUrl' => $attributes['apiBaseUrl'] ?? 'https://api.zuugle-services.net',
		'language' => $attributes['language'] ?? 'EN',
		'overrideUserStartLocation' => $attributes['overrideUserStartLocation'],
		'overrideUserStartLocationType' => $attributes['overrideUserStartLocationType'],
		'displayStartDate' => $attributes['displayStartDate'],
		'displayEndDate' => $attributes['displayEndDate'],
		'destinationInputName' => $attributes['destinationInputName'],
		// Multiday
		'multiday' => $multiday,
		'overrideActivityStartDate' => $attributes['overrideActivityStartDate'],
		'overrideActivityEndDate' => $attributes['overrideActivityEndDate'],
		'activityDurationDaysFixed' => isset($attributes['activityDurationDaysFixed']) ? intval($attributes['activityDurationDaysFixed']) : null,
		// Caching & Sharing
		'cacheUserStartLocation' => $cacheUserStartLocation,
		'userStartLocationCacheTTLMinutes' => isset($attributes['userStartLocationCacheTTLMinutes']) ? intval($attributes['userStartLocationCacheTTLMinutes']) : 15,
		'share' => $share,
		'allowShareView' => $allowShareView,
		'shareURLPrefix' => $attributes['shareURLPrefix'],
		// Generated token
		'apiToken' => $api_token,
	);

	// Filter out null values to avoid them in the JS object if not explicitly set
	$widget_config = array_filter($widget_config, function ($value) {
		return !is_null($value);
	});

	$config_json = wp_json_encode($widget_config);

	// Use the persistent widgetId from attributes for a stable container ID.
	// Fallback to uniqid for older blocks that don't have this attribute yet.
	$widget_id = $attributes['widgetId'] ?? null;
	if (empty($widget_id)) {
		$widget_id = 'rand_' . uniqid();
	}
	$widget_container_id = 'dianaWidgetContainer-' . $widget_id;
	$container_max_height = !empty($attributes['containerMaxHeight']) ? esc_attr($attributes['containerMaxHeight']) : 'none';

	$inline_script = sprintf(
		"window.dianaActivityConfigs = window.dianaActivityConfigs || {}; window.dianaActivityConfigs['%s'] = %s;",
		$widget_container_id,
		$config_json
	);

	$init_script = sprintf(
		"document.addEventListener('DOMContentLoaded', function() {
            if (typeof window.DianaWidget !== 'undefined' && window.dianaActivityConfigs && window.dianaActivityConfigs['%1\$s']) {
                new window.DianaWidget(window.dianaActivityConfigs['%1\$s'], '%1\$s');
            } else {
                var attempts = 0;
                var intervalId = setInterval(function() {
                    attempts++;
                    if (typeof window.DianaWidget !== 'undefined' && window.dianaActivityConfigs && window.dianaActivityConfigs['%1\$s']) {
                        clearInterval(intervalId);
                        new window.DianaWidget(window.dianaActivityConfigs['%1\$s'], '%1\$s');
                    } else if (attempts > 20) {
                        clearInterval(intervalId);
                        console.error('DianaWidget or its config for %1\$s not available after several attempts.');
                    }
                }, 500);
            }
        });",
		$widget_container_id
	);

	if (!wp_script_is('diana-widget-external-script', 'enqueued')) {
		// --- CHANGE START ---
		// Define a version for your script to ensure proper cache busting.
		$script_version = defined('WP_DIANA_WIDGET_VERSION') ? WP_DIANA_WIDGET_VERSION : '1.0.0';

		wp_enqueue_script(
			'diana-widget-external-script',
			WP_DIANA_WIDGET_CDN_URL,
			array(),
			$script_version, // Replaced `false` with an explicit version number.
			true
		);
		// --- CHANGE END ---
	}

	wp_add_inline_script('diana-widget-external-script', $inline_script, 'before');
	wp_add_inline_script('diana-widget-external-script', $init_script, 'after');

	$wrapper_attributes = get_block_wrapper_attributes();

	$widgetContainerStyle = sprintf(
		'style="max-height: %s;"',
		$container_max_height
	);


	return sprintf(
		'<div %1$s><div id="%2$s" %3$s></div></div>',
		$wrapper_attributes,
		esc_attr($widget_container_id),
		$widgetContainerStyle
	);
}
