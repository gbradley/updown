<?php

if (config('updown.webhook.enabled')) {
	Route::post(config('updown.webhook.uri'), '\GBradley\Updown\Laravel\WebhookController@receive');
}