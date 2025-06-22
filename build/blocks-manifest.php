<?php
// This file is generated. Do not modify it manually.
return array(
	'diana-greenconnect' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'diana-greenconnect/diana-greenconnect',
		'version' => '1.0.0',
		'title' => 'Diana GreenConnect Widget',
		'category' => 'widgets',
		'icon' => 'location-alt',
		'description' => 'Integrates the Diana GreenConnect Widget to plan transit for activities.',
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
			'activityEarliestStartTime' => array(
				'type' => 'string',
				'default' => '05:00:00'
			),
			'activityLatestStartTime' => array(
				'type' => 'string',
				'default' => '14:00:00'
			),
			'activityEarliestEndTime' => array(
				'type' => 'string',
				'default' => '12:00:00'
			),
			'activityLatestEndTime' => array(
				'type' => 'string',
				'default' => '20:00:00'
			),
			'activityDurationMinutes' => array(
				'type' => 'string',
				'default' => '120'
			),
			'activityStartLocationDisplayName' => array(
				'type' => 'string',
				'default' => ''
			),
			'activityEndLocationDisplayName' => array(
				'type' => 'string',
				'default' => ''
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
			'apiBaseUrl' => array(
				'type' => 'string',
				'default' => 'https://api.zuugle-services.net'
			),
			'language' => array(
				'type' => 'string',
				'enum' => array(
					'EN',
					'DE'
				),
				'default' => 'EN'
			),
			'overrideUserStartLocation' => array(
				'type' => 'string',
				'default' => ''
			),
			'overrideUserStartLocationType' => array(
				'type' => 'string',
				'enum' => array(
					'coordinates',
					'address',
					'station'
				),
				'default' => 'address'
			),
			'displayStartDate' => array(
				'type' => 'string',
				'default' => ''
			),
			'displayEndDate' => array(
				'type' => 'string',
				'default' => ''
			),
			'destinationInputName' => array(
				'type' => 'string',
				'default' => ''
			),
			'containerMaxHeight' => array(
				'type' => 'string',
				'default' => '600px'
			),
			'multiday' => array(
				'type' => 'boolean',
				'default' => false
			),
			'overrideActivityStartDate' => array(
				'type' => 'string',
				'default' => ''
			),
			'overrideActivityEndDate' => array(
				'type' => 'string',
				'default' => ''
			),
			'activityDurationDaysFixed' => array(
				'type' => 'integer',
				'default' => 0
			),
			'cacheUserStartLocation' => array(
				'type' => 'boolean',
				'default' => true
			),
			'userStartLocationCacheTTLMinutes' => array(
				'type' => 'integer',
				'default' => 15
			),
			'share' => array(
				'type' => 'boolean',
				'default' => true
			),
			'allowShareView' => array(
				'type' => 'boolean',
				'default' => true
			),
			'shareURLPrefix' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'textdomain' => 'diana-greenconnect',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'viewScript' => 'file:./view.js'
	)
);
