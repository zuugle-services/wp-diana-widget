/**
 * Use this file for JavaScript code that you want to run in the front-end
 * on posts/pages that contain this block.
 *
 * When this file is defined as the value of the `viewScriptModule` property
 * in `block.json` it will be enqueued on the front end of the site.
 */

/* global DianaWidget, dianaActivityConfigs */ // Inform linters about global variables

// The actual initialization logic is now primarily handled by inline scripts in render.php
// This file is kept for potential future use or if a module-based approach is preferred for initialization.
// For now, it can remain minimal or be used for polyfills if DianaWidget requires them.

console.log('WP Diana Widget view.js loaded.');

// Example of how you might re-trigger initialization if needed,
// though the inline script in render.php should handle the primary load.
// This is more of a fallback or for dynamic content loading scenarios.
// document.addEventListener('DOMContentLoaded', function () {
//     const widgetContainers = document.querySelectorAll('[id^="dianaWidgetContainer-"]');
//     widgetContainers.forEach(container => {
//         const containerId = container.id;
//         // Check if widget is already initialized for this container
//         // This check is simplistic; a more robust check might involve checking a data attribute
//         // or if the container has child elements populated by the widget.
//         if (container.children.length === 0 && typeof DianaWidget !== 'undefined' && window.dianaActivityConfigs && window.dianaActivityConfigs[containerId]) {
//             console.log(`DianaWidget: Attempting to initialize for ${containerId} from view.js`);
//             try {
//                 new DianaWidget(window.dianaActivityConfigs[containerId], containerId);
//             } catch (e) {
//                 console.error(`DianaWidget: Error initializing widget for ${containerId} from view.js:`, e);
//             }
//         }
//     });
// });

// Note: The widget.js itself is loaded from a CDN.
// The `render.php` file now includes inline scripts to:
// 1. Define `window.dianaActivityConfigs[containerId]` with the specific configuration.
// 2. Call `new DianaWidget(config, containerId)` after the main DianaWidget script is loaded.
// This `view.js` file, if enqueued as `viewScriptModule`, will run after the DOM is loaded.
// The inline scripts in `render.php` are designed to handle the initialization.
