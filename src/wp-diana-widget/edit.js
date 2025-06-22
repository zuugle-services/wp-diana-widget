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

	const locationTypeOptions = [
		{label: __('Coordinates (lat,lon)', 'wp-diana-widget'), value: 'coordinates'},
		{label: __('Address', 'wp-diana-widget'), value: 'address'},
		{label: __('Station ID', 'wp-diana-widget'), value: 'station'},
	];

	const languageOptions = [
		{label: __('English', 'wp-diana-widget'), value: 'EN'},
		{label: __('German', 'wp-diana-widget'), value: 'DE'},
	];

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Activity Details', 'wp-diana-widget')} initialOpen={true}>
					<TextControl
						label={__('Activity Name', 'wp-diana-widget')}
						value={activityName}
						onChange={(val) => setAttributes({activityName: val})}
						help={__('e.g., Hiking Trip, Museum Visit', 'wp-diana-widget')}
					/>
					<TextControl
						label={__('Activity Type', 'wp-diana-widget')}
						value={activityType}
						onChange={(val) => setAttributes({activityType: val})}
						help={__('Internal type identifier, e.g., Hiking, Sightseeing', 'wp-diana-widget')}
					/>
					<TextControl
						label={__('Activity Duration (minutes)', 'wp-diana-widget')}
						type="number"
						value={activityDurationMinutes}
						onChange={(val) => setAttributes({activityDurationMinutes: val})}
						help={__('e.g., 120 for 2 hours', 'wp-diana-widget')}
					/>
				</PanelBody>

				<PanelBody title={__('Start Location', 'wp-diana-widget')} initialOpen={false}>
					<TextareaControl
						label={__('Start Location (Coordinates or Address)', 'wp-diana-widget')}
						value={activityStartLocation}
						onChange={(val) => setAttributes({activityStartLocation: val})}
						help={__('Enter coordinates as "lat,lon" or a full address.', 'wp-diana-widget')}
					/>
					<SelectControl
						label={__('Start Location Type', 'wp-diana-widget')}
						value={activityStartLocationType}
						options={locationTypeOptions}
						onChange={(val) => setAttributes({activityStartLocationType: val})}
					/>
					<TextControl
						label={__('Start Location Display Name (Optional)', 'wp-diana-widget')}
						value={activityStartLocationDisplayName}
						onChange={(val) => setAttributes({activityStartLocationDisplayName: val})}
						help={__('Custom name to display for the start location.', 'wp-diana-widget')}
					/>
				</PanelBody>

				<PanelBody title={__('End Location', 'wp-diana-widget')} initialOpen={false}>
					<TextareaControl
						label={__('End Location (Coordinates or Address)', 'wp-diana-widget')}
						value={activityEndLocation}
						onChange={(val) => setAttributes({activityEndLocation: val})}
						help={__('Enter coordinates as "lat,lon" or a full address.', 'wp-diana-widget')}
					/>
					<SelectControl
						label={__('End Location Type', 'wp-diana-widget')}
						value={activityEndLocationType}
						options={locationTypeOptions}
						onChange={(val) => setAttributes({activityEndLocationType: val})}
					/>
					<TextControl
						label={__('End Location Display Name (Optional)', 'wp-diana-widget')}
						value={activityEndLocationDisplayName}
						onChange={(val) => setAttributes({activityEndLocationDisplayName: val})}
						help={__('Custom name to display for the end location.', 'wp-diana-widget')}
					/>
				</PanelBody>

				<PanelBody title={__('Activity Time Constraints', 'wp-diana-widget')} initialOpen={false}>
					<TextControl
						label={__('Earliest Start Time (HH:MM or HH:MM:SS)', 'wp-diana-widget')}
						value={activityEarliestStartTime}
						onChange={(val) => setAttributes({activityEarliestStartTime: val})}
						placeholder="09:00:00"
					/>
					<TextControl
						label={__('Latest Start Time (HH:MM or HH:MM:SS)', 'wp-diana-widget')}
						value={activityLatestStartTime}
						onChange={(val) => setAttributes({activityLatestStartTime: val})}
						placeholder="17:00:00"
					/>
					<TextControl
						label={__('Earliest End Time (HH:MM or HH:MM:SS)', 'wp-diana-widget')}
						value={activityEarliestEndTime}
						onChange={(val) => setAttributes({activityEarliestEndTime: val})}
						placeholder="10:00:00"
					/>
					<TextControl
						label={__('Latest End Time (HH:MM or HH:MM:SS)', 'wp-diana-widget')}
						value={activityLatestEndTime}
						onChange={(val) => setAttributes({activityLatestEndTime: val})}
						placeholder="18:00:00"
					/>
				</PanelBody>

				<PanelBody title={__('Localization & Display', 'wp-diana-widget')} initialOpen={false}>
					<TextControl
						label={__('Timezone', 'wp-diana-widget')}
						value={timezone}
						onChange={(val) => setAttributes({timezone: val})}
						help={
							<>
								{__('IANA timezone name, e.g., Europe/Vienna. ', 'wp-diana-widget')}
								<ExternalLink href="https://en.wikipedia.org/wiki/List_of_tz_database_time_zones">
									{__('List of timezones', 'wp-diana-widget')}
								</ExternalLink>
							</>
						}
						placeholder="Europe/Vienna"
					/>
					<SelectControl
						label={__('Language', 'wp-diana-widget')}
						value={language}
						options={languageOptions}
						onChange={(val) => setAttributes({language: val})}
					/>
					<TextControl
						label={__('Activity Start Time Label (Optional)', 'wp-diana-widget')}
						value={activityStartTimeLabel}
						onChange={(val) => setAttributes({activityStartTimeLabel: val})}
						help={__('Custom label for "Activity Start".', 'wp-diana-widget')}
					/>
					<TextControl
						label={__('Activity End Time Label (Optional)', 'wp-diana-widget')}
						value={activityEndTimeLabel}
						onChange={(val) => setAttributes({activityEndTimeLabel: val})}
						help={__('Custom label for "Activity End".', 'wp-diana-widget')}
					/>
					<TextControl
						label={__('Destination Input Name (Optional)', 'wp-diana-widget')}
						value={destinationInputName}
						onChange={(val) => setAttributes({destinationInputName: val})}
						help={__('Placeholder for the disabled input field for the destination.', 'wp-diana-widget')}
					/>
					<TextControl
						label={__('Display Start Date (Optional)', 'wp-diana-widget')}
						value={displayStartDate}
						onChange={(val) => setAttributes({displayStartDate: val})}
						help={__('Start date (YYYY-MM-DD) for widget visibility. If set, the widget will only display on or after this date.', 'wp-diana-widget')}
						placeholder="YYYY-MM-DD"
					/>
					<TextControl
						label={__('Display End Date (Optional)', 'wp-diana-widget')}
						value={displayEndDate}
						onChange={(val) => setAttributes({displayEndDate: val})}
						help={__('End date (YYYY-MM-DD) for widget visibility. If set, the widget will only display on or before this date.', 'wp-diana-widget')}
						placeholder="YYYY-MM-DD"
					/>
				</PanelBody>

				<PanelBody title={__('Multiday', 'wp-diana-widget')} initialOpen={false}>
					<ToggleControl
						label={__('Enable Multiday Mode', 'wp-diana-widget')}
						checked={multiday}
						onChange={(val) => setAttributes({multiday: val})}
						help={__('Allows selecting a date range for the activity.', 'wp-diana-widget')}
					/>
					<TextControl
						label={__('Override Activity Start Date (Optional)', 'wp-diana-widget')}
						value={overrideActivityStartDate}
						onChange={(val) => setAttributes({overrideActivityStartDate: val})}
						help={__('Override activity start date with this date (YYYY-MM-DD). Can also be used for single-day activities.', 'wp-diana-widget')}
						placeholder="YYYY-MM-DD"
					/>
					<TextControl
						label={__('Override Activity End Date (Optional)', 'wp-diana-widget')}
						value={overrideActivityEndDate}
						onChange={(val) => setAttributes({overrideActivityEndDate: val})}
						help={__('Override activity end date with this date (YYYY-MM-DD).', 'wp-diana-widget')}
						placeholder="YYYY-MM-DD"
						disabled={!multiday}
					/>
					<TextControl
						label={__('Fixed Activity Duration (Days)', 'wp-diana-widget')}
						type="number"
						value={activityDurationDaysFixed}
						onChange={(val) => setAttributes({activityDurationDaysFixed: parseInt(val, 10) || 0})}
						help={__('Set a fixed duration in days for the activity date range picker.', 'wp-diana-widget')}
						disabled={!multiday}
					/>
				</PanelBody>

				<PanelBody title={__('Caching & Sharing', 'wp-diana-widget')} initialOpen={false}>
					<ToggleControl
						label={__('Enable User Start Location Caching', 'wp-diana-widget')}
						checked={cacheUserStartLocation}
						onChange={(val) => setAttributes({cacheUserStartLocation: val})}
						help={__('Cache the user\'s last entered start location in their browser.', 'wp-diana-widget')}
					/>
					<TextControl
						label={__('Cache TTL (minutes)', 'wp-diana-widget')}
						type="number"
						value={userStartLocationCacheTTLMinutes}
						onChange={(val) => setAttributes({userStartLocationCacheTTLMinutes: parseInt(val, 10) || 15})}
						help={__('How long to cache the location. Default: 15.', 'wp-diana-widget')}
						disabled={!cacheUserStartLocation}
					/>
					<ToggleControl
						label={__('Enable Share Button', 'wp-diana-widget')}
						checked={share}
						onChange={(val) => setAttributes({share: val})}
						help={__('Show a share button in the widget menu.', 'wp-diana-widget')}
					/>
					<ToggleControl
						label={__('Allow Share View', 'wp-diana-widget')}
						checked={allowShareView}
						onChange={(val) => setAttributes({allowShareView: val})}
						help={__('Allow the widget to be displayed in a read-only "share view" via a URL parameter.', 'wp-diana-widget')}
						disabled={!share}
					/>
					<TextControl
						label={__('Share URL Prefix (Optional)', 'wp-diana-widget')}
						value={shareURLPrefix}
						onChange={(val) => setAttributes({shareURLPrefix: val})}
						help={__('Base URL for share links. Defaults to the current page URL.', 'wp-diana-widget')}
						disabled={!share}
					/>
				</PanelBody>

				<PanelBody title={__('Advanced & Styling', 'wp-diana-widget')} initialOpen={false}>
					<TextControl
						label={__('API Base URL', 'wp-diana-widget')}
						value={apiBaseUrl}
						onChange={(val) => setAttributes({apiBaseUrl: val})}
						help={__('Default: https://api.zuugle-services.net', 'wp-diana-widget')}
					/>
					<TextControl
						label={__('Widget Container Max Height', 'wp-diana-widget')}
						value={containerMaxHeight}
						onChange={(val) => setAttributes({containerMaxHeight: val})}
						help={__('e.g., 600px, 80vh, none. Default: 600px', 'wp-diana-widget')}
						placeholder="600px"
					/>
					<TextControl
						label={__('Override User Start Location', 'wp-diana-widget')}
						value={overrideUserStartLocation}
						onChange={(val) => setAttributes({overrideUserStartLocation: val})}
						help={__('Permanently set the user\'s starting point.', 'wp-diana-widget')}
					/>
					<SelectControl
						label={__('Override User Start Location Type', 'wp-diana-widget')}
						value={overrideUserStartLocationType}
						options={locationTypeOptions}
						onChange={(val) => setAttributes({overrideUserStartLocationType: val})}
					/>
				</PanelBody>
			</InspectorControls>
			<div {...blockProps}>
				<div className="wp-diana-widget-editor-preview">
					<h4>{__('Diana Activity Widget', 'wp-diana-widget')}</h4>
					<p>
						{__('Activity:', 'wp-diana-widget')}{' '}
						{activityName || __('Not set', 'wp-diana-widget')}
					</p>
					<p>
						{__(
							'This block will display the Diana Activity Widget on the frontend. Configure API credentials under Settings > Diana Widget.',
							'wp-diana-widget'
						)}
					</p>
					<p>
						<em>{__('Frontend preview may differ.', 'wp-diana-widget')}</em>
					</p>
				</div>
			</div>
		</>
	);
}
