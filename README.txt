=== Diana GreenConnect ===
Contributors: zuugleservices
Tags: travel, widget, transit, planning, diana
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Stable Tag: 1.0.2
Tested up to: 6.8

Diana GreenConnect is a trip-planning block that lets users schedule trips to and from activities with constraints like start/end time and duration.

== Description ==
Diana GreenConnect is a WordPress plugin that provides a Gutenberg block to seamlessly integrate the powerful [DianaWidget Activity Transit Planner](https://github.com/zuugle-services/DianaWidget) into your WordPress pages and posts. It allows users to plan public transport to and from activities with specific time constraints like start times, end times, and duration.

This plugin acts as a wrapper for the [DianaWidget](https://github.com/zuugle-services/DianaWidget), a sophisticated JavaScript library designed for activity-based transit planning. By using the "Diana GreenConnect Widget" block, you can easily embed this functionality into your content, allowing your site visitors to:

* Find transit connections to a predefined activity.
* Specify their starting location with autocomplete suggestions.
* Select a date for their travel using an adaptive calendar.
* View connection results with clear timelines and transport modes.

The plugin handles secure API authentication with Zuugle Services by allowing administrators to store Client ID and Client Secret in WordPress settings. These credentials are then used server-side to fetch an API token, which is passed to the widget. Each block instance can be configured with unique activity details directly within the WordPress editor.

## Features

* **Gutenberg Block Integration:** Easily add the Diana GreenConnect Widget to any page or post using a dedicated block.
* **Secure API Credential Management:** Store your Zuugle Services Client ID and Client Secret securely in WordPress settings. API tokens are fetched and handled server-side.
* **Customizable Activity Parameters:** For each block instance, you can configure:
	* Activity Name and Type
	* Start and End Locations (coordinates or address)
	* Display Names for locations
	* Earliest and Latest Start/End Times for the activity
	* Activity Duration
	* Timezone and Language
	* Custom labels for start/end times
	* Multiday and date override settings
* **Dynamic Widget Loading:** The widget script is loaded from the official CDN.
* **Stable Instance Caching:** Automatically generates a stable ID for each block to enable persistent user-input caching (e.g., the user's starting address).
* **Sharing:** Allows users to share their planned journey via a unique link.
* **Multiple Widget Instances:** Supports multiple Diana GreenConnect blocks on a single page, each with its own configuration.
* **Responsive Design:** Leverages the responsive capabilities of the core DianaWidget.

## About the DianaWidget JavaScript Library

The core functionality is provided by the `DianaWidget`, a standalone JavaScript library. For more detailed information about the widget's features, its own configuration options (which this plugin exposes), styling, and architecture, please refer to its GitHub repository:
[https://github.com/zuugle-services/DianaWidget](https://github.com/zuugle-services/DianaWidget)

== Installation ==

1.  **Download:** Download the plugin ZIP file from the latest release, or optionally a past version.
2.  **Upload:** In your WordPress admin panel, go to `Plugins` > `Add New` > `Upload Plugin`. Choose the ZIP file and click `Install Now`.
3.  **Activate:** Activate the plugin through the `Plugins` menu in WordPress.
4.  **Configure Credentials:**
	* Navigate to `Settings` > `Diana GreenConnect` in your WordPress admin area.
	* Enter your `Client ID` and `Client Secret` provided by Zuugle Services.
	* Save the settings.

## How to Use

1.  **Add the Block:**
	* Open a page or post in the WordPress block editor.
	* Click the `+` icon to add a new block.
	* Search for "Diana GreenConnect Widget" and select it.
2.  **Configure the Block:**
	* With the block selected, use the Inspector Controls (sidebar on the right) to set the specific details for the activity you want users to plan travel to. This includes activity name, location, times, duration, etc.
3.  **Save and View:** Save your page/post. The Diana GreenConnect Block will appear on the frontend, configured with the details you provided. For any existing blocks from older versions, be sure to "Update" the page to generate the stable ID needed for caching.

## Programmatic Usage

You can also render the Diana GreenConnect Widget block programmatically within your PHP code (e.g., in your theme's `functions.php`, a custom plugin, or a template file) using WordPress's `do_blocks()` function. This is useful if you need to embed the widget in locations not directly editable with the block editor.

There's a helper function `diana_greenconnect_get_block_html()` integrated in the plugin that you can use to generate the HTML for the widget. This function takes an array of attributes and returns the HTML for the Diana GreenConnect block.

**Example of how to use this function:**

`php
<?php
$my_widget_attributes = [
    \'widgetId\'                         => \'main-sidebar-hiking-widget\',

    // Required
    \'activityName\'                     => \'Marktschellenberger Eishöhle im Untersberg\',
    \'activityType\'                     => \'Hiking\',
    \'activityStartLocation\'            => \'47.72620173410345, 13.042174020936743\',
    \'activityStartLocationType\'        => \'coordinates\',
    \'activityEndLocation\'              => \'47.70487271915757, 13.038710343883247\',
    \'activityEndLocationType\'          => \'coordinates\',
    \'activityEarliestStartTime\'        => \'08:00:00\',
    \'activityLatestStartTime\'          => \'14:00:00\',
    \'activityEarliestEndTime\'          => \'10:00:00\',
    \'activityLatestEndTime\'            => \'20:00:00\',
    \'activityDurationMinutes\'          => \'300\',

    // Optional
    \'activityStartLocationDisplayName\' => \'Untersbergbahn Talstation\',
    \'activityEndLocationDisplayName\'   => \'Eishöhle, Marktschellenberg\',
    \'timezone\'                         => \'Europe/Vienna\', // Set timezone in which all config times are given
    \'activityStartTimeLabel\'           => \'Beginn\',
    \'activityEndTimeLabel\'             => \'Ende\',
    \'apiBaseUrl\'                       => \'https://api.zuugle-services.net\',
    \'language\'                         => \'EN\', // Currently supported: EN, DE
    \'overrideUserStartLocation\'        => \'Wien, Stephansplatz\',
    \'overrideUserStartLocationType\'    => \'address\',
    \'displayStartDate\'                 => null,
    \'displayEndDate\'                   => null,
    \'destinationInputName\'             => \'Destination Input Placeholder\',
    \'containerMaxHeight\'               => \'650px\',

    // Multiday parameters
    \'multiday\'                         => false,
    \'overrideActivityStartDate\'        => \"2025-05-20\", // Can also be used for single-day date
    \'overrideActivityEndDate\'          => \"2025-05-25\",
    \'activityDurationDaysFixed\'        => 2,

    // Caching & Sharing parameters
    \'cacheUserStartLocation\'           => true,
    \'userStartLocationCacheTTLMinutes\' => 15,
    \'share\'                            => true,
    \'allowShareView\'                   => true,
    \'shareURLPrefix\'                   => \'\',

    // ClientID and ClientSecret are typically managed by the plugin\'s settings page.
    // Only include them here if you need to override for a specific instance.
    // \'clientID\' => \'your_client_id_override\',
    // \'clientSecret\' => \'your_client_secret_override\',
];

$widget_info = diana_greenconnect_get_block_html( $my_widget_attributes );

// Output the HTML (e.g., in a template file or via a shortcode)
echo $widget_info['html'];
?>
`

When using `diana_greenconnect_get_block_html()`, providing a stable `widgetId` is crucial for the start location caching feature to work correctly. The plugin cannot automatically determine a stable ID for programmatically rendered blocks, so you must define it yourself.

== Frequently Asked Questions ==

= Where do I get a Client ID and Client Secret? =

You need to apply for access and obtain these credentials from [Zuugle Services](https://www.zuugle-services.com) as described in the [DianaWidget security process](https://github.com/zuugle-services/DianaWidget#apply-for-access--security-process).

= The user's start location isn't being saved/cached. Why? =

This happens if the widget doesn't have a stable ID. The caching feature relies on a persistent, unique ID for each widget instance.
    * **For Blocks in the Editor:** This is handled automatically. If you have blocks created with an older version of the plugin, simply open the page in the editor and click "Update". This will save the new stable ID for the block.
    * **For Programmatic Usage:** When using the `diana_greenconnect_get_block_html()` function, you **must** manually provide a unique and unchanging `widgetId` string in the attributes array. See the example under "Programmatic Usage".

= Can I customize the appearance of the widget? =

The DianaWidget itself supports theming via CSS custom properties. You can add custom CSS to your WordPress theme to override these variables. See the [DianaWidget styling documentation](https://github.com/zuugle-services/DianaWidget#styling--theming) for more details. This plugin also provides a "Widget Container Max Height" setting in the block editor.

== Screenshots ==

1. This screen shot description corresponds to screenshot-1.png. This screen shot represents a preview header of what the initial Widget Page would look like.

== Changelog ==

**1.0.2**
* Fix code according to WordPress plugin review
* Rename Settings page to only 'Diana GreenConnect', without 'Widget'

**1.0.1**
* Allow integer values for activityDurationMinutes in attributes

**1.0.0**
* Initial release with Gutenberg block.
* Settings page for API Client ID and Secret.
* Server-side API token fetching and caching.
* Comprehensive block attributes for widget configuration.
* Support for multiple widget instances.

== External Services ==

This plugin connects to external services provided by Zuugle Services GmbH to provide its transit planning functionality.

* **DianaWidget JavaScript Library**: The core functionality of this plugin is provided by the `DianaWidget`, a JavaScript library that is loaded from a Content Delivery Network (CDN) at `https://diana.zuugle-services.net/dist/DianaWidget.bundle.js`. This script is loaded on pages where the Diana GreenConnect block is used to display the transit planning widget.
* **Zuugle Services API**: To function, the widget needs to communicate with the Zuugle Services API, which has its main endpoint at `https://api.zuugle-services.net`. This plugin securely handles the authentication with this API.

    * **What data is sent and when**:
        * To obtain an access token, the plugin sends a `Client ID` and `Client Secret` (which are configured in the plugin’s settings) to the token endpoint at `https://api.zuugle-services.net/o/token/`. This happens on the server-side when a user visits a page containing the widget and a valid token is not already cached. No user data is sent in this step.
        * When a user interacts with the widget to find transit connections, the widget sends the user-specified starting location, the pre-defined activity location, and the selected date and time to the Zuugle Services API to retrieve transit information.
    * **Terms and Privacy**:
        * The use of these external services is subject to the provider’s terms and policies. You can find them here:
            * **Terms of Service:** [Terms of Service](https://zuugle-services.com/agb/)
            * **Privacy Policy:** [Privacy Policy](https://zuugle-services.com/datenschutzerklaerung/)
