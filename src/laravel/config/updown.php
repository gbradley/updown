<?php

return [

	/**
	 * The read/write API key for your Updown.io account.
	 */
	'api_key'			=> env('UPDOWN_API_KEY'),

	/**
	 * If this application is checked by updown.io, add its token here.
	 */
	'app_token'			=> env('UPDOWN_APP_TOKEN'),

	/**
	 * Maintenance configuration.
	 */
	'maintenance' 		=> [

		// Automatically listen for maintenance mode and enable/disable checks as appropriate. Requires an app_token to be set.
		'listen'		=> true,

	],

	/**
	 * Webhook configuration.
	 */
	'webhook'			=> [

		/**
	 	 * Determines whether to enable webhooks.
	 	 */
		'enabled'				=> env('UPDOWN_WEBHOOKS_ENABLED', false),

		/**
		 * The uri for accepting incoming webhooks.
		 */
		'uri'					=> '/webhooks/updown',

		/**
		 * Map events reeived by the webhook to the class that handles them.
		 */
		'events'				=> [
			'check.up' 				=> \GBradley\Updown\Laravel\Events\Up::class,
			'check.down' 			=> \GBradley\Updown\Laravel\Events\Down::class,
		]

	],

];