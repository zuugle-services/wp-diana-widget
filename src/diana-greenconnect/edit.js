/**
 * WordPress dependencies
 */
import {__} from '@wordpress/i18n';
import {InspectorControls, useBlockProps} from '@wordpress/block-editor';
import {
	ExternalLink,
	PanelBody,
	SelectControl,
	TextareaControl,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import {useEffect} from '@wordpress/element';

/**
 * Local dependencies
 */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the editor.
 * This represents what the editor will render when the block is used.
 */
export default function Edit({attributes, setAttributes}) {
	const blockProps = useBlockProps();

	const {
		// Internal
		widgetId,
		// Required
		activityName,
		activityType,
		activityStartLocation,
		activityStartLocationType,
		activityEndLocation,
		activityEndLocationType,
		activityEarliestStartTime,
		activityLatestStartTime,
		activityEarliestEndTime,
		activityLatestEndTime,
		activityDurationMinutes,
		// Optional
		activityStartLocationDisplayName,
		activityEndLocationDisplayName,
		timezone,
		activityStartTimeLabel,
		activityEndTimeLabel,
		apiBaseUrl,
		language,
		overrideUserStartLocation,
		overrideUserStartLocationType,
		displayStartDate,
		displayEndDate,
		destinationInputName,
		containerMaxHeight,
		// Multiday
		multiday,
		overrideActivityStartDate,
		overrideActivityEndDate,
		activityDurationDaysFixed,
		// Caching & Sharing
		cacheUserStartLocation,
		userStartLocationCacheTTLMinutes,
		share,
		allowShareView,
		shareURLPrefix,
	} = attributes;

	// Effect to generate a unique, persistent ID for the block instance.
	useEffect(() => {
		if (!widgetId) {
			// A simple unique ID generator
			const newId = 'diana-block-' + Math.random().toString(36).substring(2, 11);
			setAttributes({widgetId: newId});
		}
	}, [widgetId, setAttributes]);

	const locationTypeOptions = [
		{label: __('Coordinates (lat,lon)', 'diana-greenconnect'), value: 'coordinates'},
		{label: __('Address', 'diana-greenconnect'), value: 'address'},
		{label: __('Station ID', 'diana-greenconnect'), value: 'station'},
	];

	const languageOptions = [
		{label: __('English', 'diana-greenconnect'), value: 'EN'},
		{label: __('German', 'diana-greenconnect'), value: 'DE'},
	];

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Activity Details', 'diana-greenconnect')} initialOpen={true}>
					<TextControl
						label={__('Activity Name', 'diana-greenconnect')}
						value={activityName}
						onChange={(val) => setAttributes({activityName: val})}
						help={__('e.g., Hiking Trip, Museum Visit', 'diana-greenconnect')}
					/>
					<TextControl
						label={__('Activity Type', 'diana-greenconnect')}
						value={activityType}
						onChange={(val) => setAttributes({activityType: val})}
						help={__('Internal type identifier, e.g., Hiking, Sightseeing', 'diana-greenconnect')}
					/>
					<TextControl
						label={__('Activity Duration (minutes)', 'diana-greenconnect')}
						type="number"
						value={activityDurationMinutes}
						onChange={(val) => setAttributes({activityDurationMinutes: parseInt(val, 10)})}
						help={__('e.g., 120 for 2 hours', 'diana-greenconnect')}
					/>
				</PanelBody>

				<PanelBody title={__('Start Location', 'diana-greenconnect')} initialOpen={false}>
					<TextareaControl
						label={__('Start Location (Coordinates or Address)', 'diana-greenconnect')}
						value={activityStartLocation}
						onChange={(val) => setAttributes({activityStartLocation: val})}
						help={__('Enter coordinates as "lat,lon" or a full address.', 'diana-greenconnect')}
					/>
					<SelectControl
						label={__('Start Location Type', 'diana-greenconnect')}
						value={activityStartLocationType}
						options={locationTypeOptions}
						onChange={(val) => setAttributes({activityStartLocationType: val})}
					/>
					<TextControl
						label={__('Start Location Display Name (Optional)', 'diana-greenconnect')}
						value={activityStartLocationDisplayName}
						onChange={(val) => setAttributes({activityStartLocationDisplayName: val})}
						help={__('Custom name to display for the start location.', 'diana-greenconnect')}
					/>
				</PanelBody>

				<PanelBody title={__('End Location', 'diana-greenconnect')} initialOpen={false}>
					<TextareaControl
						label={__('End Location (Coordinates or Address)', 'diana-greenconnect')}
						value={activityEndLocation}
						onChange={(val) => setAttributes({activityEndLocation: val})}
						help={__('Enter coordinates as "lat,lon" or a full address.', 'diana-greenconnect')}
					/>
					<SelectControl
						label={__('End Location Type', 'diana-greenconnect')}
						value={activityEndLocationType}
						options={locationTypeOptions}
						onChange={(val) => setAttributes({activityEndLocationType: val})}
					/>
					<TextControl
						label={__('End Location Display Name (Optional)', 'diana-greenconnect')}
						value={activityEndLocationDisplayName}
						onChange={(val) => setAttributes({activityEndLocationDisplayName: val})}
						help={__('Custom name to display for the end location.', 'diana-greenconnect')}
					/>
				</PanelBody>

				<PanelBody title={__('Activity Time Constraints', 'diana-greenconnect')} initialOpen={false}>
					<TextControl
						label={__('Earliest Start Time (HH:MM or HH:MM:SS)', 'diana-greenconnect')}
						value={activityEarliestStartTime}
						onChange={(val) => setAttributes({activityEarliestStartTime: val})}
						placeholder="09:00:00"
					/>
					<TextControl
						label={__('Latest Start Time (HH:MM or HH:MM:SS)', 'diana-greenconnect')}
						value={activityLatestStartTime}
						onChange={(val) => setAttributes({activityLatestStartTime: val})}
						placeholder="17:00:00"
					/>
					<TextControl
						label={__('Earliest End Time (HH:MM or HH:MM:SS)', 'diana-greenconnect')}
						value={activityEarliestEndTime}
						onChange={(val) => setAttributes({activityEarliestEndTime: val})}
						placeholder="10:00:00"
					/>
					<TextControl
						label={__('Latest End Time (HH:MM or HH:MM:SS)', 'diana-greenconnect')}
						value={activityLatestEndTime}
						onChange={(val) => setAttributes({activityLatestEndTime: val})}
						placeholder="18:00:00"
					/>
				</PanelBody>

				<PanelBody title={__('Localization & Display', 'diana-greenconnect')} initialOpen={false}>
					<TextControl
						label={__('Timezone', 'diana-greenconnect')}
						value={timezone}
						onChange={(val) => setAttributes({timezone: val})}
						help={
							<>
								{__('IANA timezone name, e.g., Europe/Vienna. ', 'diana-greenconnect')}
								<ExternalLink href="https://en.wikipedia.org/wiki/List_of_tz_database_time_zones">
									{__('List of timezones', 'diana-greenconnect')}
								</ExternalLink>
							</>
						}
						placeholder="Europe/Vienna"
					/>
					<SelectControl
						label={__('Language', 'diana-greenconnect')}
						value={language}
						options={languageOptions}
						onChange={(val) => setAttributes({language: val})}
					/>
					<TextControl
						label={__('Activity Start Time Label (Optional)', 'diana-greenconnect')}
						value={activityStartTimeLabel}
						onChange={(val) => setAttributes({activityStartTimeLabel: val})}
						help={__('Custom label for "Activity Start".', 'diana-greenconnect')}
					/>
					<TextControl
						label={__('Activity End Time Label (Optional)', 'diana-greenconnect')}
						value={activityEndTimeLabel}
						onChange={(val) => setAttributes({activityEndTimeLabel: val})}
						help={__('Custom label for "Activity End".', 'diana-greenconnect')}
					/>
					<TextControl
						label={__('Destination Input Name (Optional)', 'diana-greenconnect')}
						value={destinationInputName}
						onChange={(val) => setAttributes({destinationInputName: val})}
						help={__('Placeholder for the disabled input field for the destination.', 'diana-greenconnect')}
					/>
					<TextControl
						label={__('Display Start Date (Optional)', 'diana-greenconnect')}
						value={displayStartDate}
						onChange={(val) => setAttributes({displayStartDate: val})}
						help={__('Start date (YYYY-MM-DD) for widget visibility. If set, the widget will only display on or after this date.', 'diana-greenconnect')}
						placeholder="YYYY-MM-DD"
					/>
					<TextControl
						label={__('Display End Date (Optional)', 'diana-greenconnect')}
						value={displayEndDate}
						onChange={(val) => setAttributes({displayEndDate: val})}
						help={__('End date (YYYY-MM-DD) for widget visibility. If set, the widget will only display on or before this date.', 'diana-greenconnect')}
						placeholder="YYYY-MM-DD"
					/>
				</PanelBody>

				<PanelBody title={__('Multiday', 'diana-greenconnect')} initialOpen={false}>
					<ToggleControl
						label={__('Enable Multiday Mode', 'diana-greenconnect')}
						checked={multiday}
						onChange={(val) => setAttributes({multiday: val})}
						help={__('Allows selecting a date range for the activity.', 'diana-greenconnect')}
					/>
					<TextControl
						label={__('Override Activity Start Date (Optional)', 'diana-greenconnect')}
						value={overrideActivityStartDate}
						onChange={(val) => setAttributes({overrideActivityStartDate: val})}
						help={__('Override activity start date with this date (YYYY-MM-DD). Can also be used for single-day activities.', 'diana-greenconnect')}
						placeholder="YYYY-MM-DD"
					/>
					<TextControl
						label={__('Override Activity End Date (Optional)', 'diana-greenconnect')}
						value={overrideActivityEndDate}
						onChange={(val) => setAttributes({overrideActivityEndDate: val})}
						help={__('Override activity end date with this date (YYYY-MM-DD).', 'diana-greenconnect')}
						placeholder="YYYY-MM-DD"
						disabled={!multiday}
					/>
					<TextControl
						label={__('Fixed Activity Duration (Days)', 'diana-greenconnect')}
						type="number"
						value={activityDurationDaysFixed}
						onChange={(val) => setAttributes({activityDurationDaysFixed: parseInt(val, 10) || 0})}
						help={__('Set a fixed duration in days for the activity date range picker.', 'diana-greenconnect')}
						disabled={!multiday}
					/>
				</PanelBody>

				<PanelBody title={__('Caching & Sharing', 'diana-greenconnect')} initialOpen={false}>
					<ToggleControl
						label={__('Enable User Start Location Caching', 'diana-greenconnect')}
						checked={cacheUserStartLocation}
						onChange={(val) => setAttributes({cacheUserStartLocation: val})}
						help={__('Cache the user\'s last entered start location in their browser.', 'diana-greenconnect')}
					/>
					<TextControl
						label={__('Cache TTL (minutes)', 'diana-greenconnect')}
						type="number"
						value={userStartLocationCacheTTLMinutes}
						onChange={(val) => setAttributes({userStartLocationCacheTTLMinutes: parseInt(val, 10) || 15})}
						help={__('How long to cache the location. Default: 15.', 'diana-greenconnect')}
						disabled={!cacheUserStartLocation}
					/>
					<ToggleControl
						label={__('Enable Share Button', 'diana-greenconnect')}
						checked={share}
						onChange={(val) => setAttributes({share: val})}
						help={__('Show a share button in the widget menu.', 'diana-greenconnect')}
					/>
					<ToggleControl
						label={__('Allow Share View', 'diana-greenconnect')}
						checked={allowShareView}
						onChange={(val) => setAttributes({allowShareView: val})}
						help={__('Allow the widget to be displayed in a read-only "share view" via a URL parameter.', 'diana-greenconnect')}
						disabled={!share}
					/>
					<TextControl
						label={__('Share URL Prefix (Optional)', 'diana-greenconnect')}
						value={shareURLPrefix}
						onChange={(val) => setAttributes({shareURLPrefix: val})}
						help={__('Base URL for share links. Defaults to the current page URL.', 'diana-greenconnect')}
						disabled={!share}
					/>
				</PanelBody>

				<PanelBody title={__('Advanced & Styling', 'diana-greenconnect')} initialOpen={false}>
					<TextControl
						label={__('API Base URL', 'diana-greenconnect')}
						value={apiBaseUrl}
						onChange={(val) => setAttributes({apiBaseUrl: val})}
						help={__('Default: https://api.zuugle-services.net', 'diana-greenconnect')}
					/>
					<TextControl
						label={__('Widget Container Max Height', 'diana-greenconnect')}
						value={containerMaxHeight}
						onChange={(val) => setAttributes({containerMaxHeight: val})}
						help={__('e.g., 620px, 80vh, none. Default: 620px', 'diana-greenconnect')}
						placeholder="620px"
					/>
					<TextControl
						label={__('Override User Start Location', 'diana-greenconnect')}
						value={overrideUserStartLocation}
						onChange={(val) => setAttributes({overrideUserStartLocation: val})}
						help={__('Permanently set the user\'s starting point.', 'diana-greenconnect')}
					/>
					<SelectControl
						label={__('Override User Start Location Type', 'diana-greenconnect')}
						value={overrideUserStartLocationType}
						options={locationTypeOptions}
						onChange={(val) => setAttributes({overrideUserStartLocationType: val})}
					/>
				</PanelBody>
			</InspectorControls>
			<div {...blockProps}>
				<div className="diana-greenconnect-editor-preview">
					<h4>{__('Diana GreenConnect Widget', 'diana-greenconnect')}</h4>
					<p>
						{__('Activity:', 'diana-greenconnect')}{' '}
						{activityName || __('Not set', 'diana-greenconnect')}
					</p>
					<p>
						{__(
							'This block will display the Diana GreenConnect Widget on the frontend. Configure API credentials under Settings > Diana GreenConnect Widget.',
							'diana-greenconnect'
						)}
					</p>
					<p>
						<em>{__('Frontend preview may differ.', 'diana-greenconnect')}</em>
					</p>
				</div>
			</div>
		</>
	);
}
