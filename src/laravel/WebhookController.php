<?php

namespace GBradley\Updown\Laravel;

use App\Http\Controllers\Controller;
use GBradley\Updown\Client;
use GBradley\Updown\Check;
use GBradley\Updown\Laravel\Events\Up;
use GBradley\Updown\Laravel\Events\Down;
use Illuminate\Http\Request;

class WebhookController extends Controller {

	protected $updown;

	public function __construct(Client $updown) {
		$this->updown = $updown;
	}
	
	/**
	 * Receieve a webhook request.
	 */
	public function receive(Request $request) {

		$events = config('updown.webhook.events');

		foreach ($request->input() as $payload) {
			if (is_array($payload) && isset($payload['event'])) {
				$type = $payload['event'];
				if (isset($events[$type])) {

					// Create the Check instance.
					$check = new Check($payload['check']['token'], $this->updown, $payload['check']);

					// Get the event class.
					$cls = $events[$type];

					// Create the event instance and dispatch it along with the check and its downtime data.
					event(new $cls($check, $payload['downtime']));
				}
			}
		}

		return response('OK', 200);
	}

}