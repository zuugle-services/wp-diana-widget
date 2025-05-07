<?php
// This file is generated. Do not modify it manually.
return array(
	'wp-diana-widget' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'wp-diana-widget/wp-diana-widget',
		'version' => '1.0.0',
		'title' => 'Diana Activity Widget',
		'category' => 'widgets',
		'icon' => 'location-alt',
		'description' => 'Integrates the Diana Activity Widget to plan transit for activities.',
		'keywords' => array(
			'travel',
			'transit',
			'activity',
			'planning',
			'diana'
		),
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'attributes' => array(
			'align' => array(
				'type' => 'string',
				'default' => ''
			),
			'activityName' => array(
				'type' => 'string',
				'default' => 'My Awesome Activity'
			),
			'activityType' => array(
				'type' => 'string',
				'default' => 'General'
			),
			'activityStartLocation' => array(
				'type' => 'string',
				'default' => '47.7262, 13.0421'
			),
			'activityStartLocationType' => array(
				'type' => 'string',
				'enum' => array(
					'coordinates',
					'address',
					'station'
				),
				'default' => 'coordinates'
			),
			'activityStartLocationDisplayName' => array(
				'type' => 'string',
				'default' => ''
			),
			'activityEndLocation' => array(
				'type' => 'string',
				'default' => '47.7048, 13.0387'
			),
			'activityEndLocationType' => array(
				'type' => 'string',
				'enum' => array(
					'coordinates',
					'address',
					'station'
				),
				'default' => 'coordinates'
			),
			'activityEndLocationDisplayName' => array(
				'type' => 'string',
				'default' => ''
			),
			'activityEarliestStartTime' => array(
				'type' => 'string',
				'default' => '09:00:00'
			),
			'activityLatestStartTime' => array(
				'type' => 'string',
				'default' => '17:00:00'
			),
			'activityEarliestEndTime' => array(
				'type' => 'string',
				'default' => '10:00:00'
			),
			'activityLatestEndTime' => array(
				'type' => 'string',
				'default' => '18:00:00'
			),
			'activityDurationMinutes' => array(
				'type' => 'string',
				'default' => '120'
			),
			'timezone' => array(
				'type' => 'string',
				'default' => 'Europe/Vienna'
			),
			'activityStartTimeLabel' => array(
				'type' => 'string',
				'default' => ''
			),
			'activityEndTimeLabel' => array(
				'type' => 'string',
				'default' => ''
			),
			'language' => array(
				'type' => 'string',
				'enum' => array(
					'EN',
					'DE'
				),
				'default' => 'EN'
			),
			'apiBaseUrl' => array(
				'type' => 'string',
				'default' => 'https://api.zuugle-services.net'
			),
			'containerMaxHeight' => array(
				'type' => 'string',
				'default' => '600px'
			)
		),
		'textdomain' => 'wp-diana-widget',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'viewScript' => 'file:./view.js'
	)
);
