# Updown

A PHP library for the [updown.io](https://updown.io) website monitoring API. Create, inspect, modify and delete checks, with optional Laravel integration.

	$check = $updown->create('https://mysite.com', [
		'alias' 	=> 'MySite',
		'period'	=> 60,
	]);
	
	...
	
	$check->delete();
	
	
- [Requirements](#requirements)
- [Installation](#installation)
- [Setup](#setup)
- [Usage](#usage)
- [Working with Laravel](#working-with-laravel)

## Requirements

Updown requires PHP 7.1 or above.

## Installation

Install with Composer:

	$ composer require gbradley/updown
	
## Setup

Configure an instance of the updown client by passing in your API key. If the running application is checked by updown.io, you can provide its token in the second argument.

	use GBradley\Updown\Client;
	...
	$updown = new Client('my-api-key', 'my-app-token');
	// do stuff

## Usage

The Client is used to fetch and obtain Check instances, which allow you to inspect, modify and delete a single check. If the library encounters a problem, a `GBradley\Updown\ApiException` will be thrown, providing error information from the API response where possible.

### Client

- [checks()](#checks)
- [create()](#create)
- [check()](#check)
- [ips()](#ips)

#### checks()

Returns an array of `Gbradley\Updown\Check`s, keyed by their token.

	$checks = $updown->checks();
	$checks[0]->alias;			// 'MySite'
	
#### create()

Creates a new check. Pass the URL to be checked as the first argument; you can provide any of the [documented options](https://updown.io/api#rest) in an array as the second argument.

	$check = $updown->create('https://yoursite.com', [
		'alias'	=> 'YourSite',
	]);
	$check->token;				// 'abzy'
	
#### check()

Retrieve a Check object matching the provided token. If none is provided, the configured app token is used instead.

	$check = $updown->check('abzy');
	$check->alias;				// 'YourSite'
	
#### ips()

Retrieve a list of all the IPs used to perform checks. You may specify the IP version in the first argument; defaults to 4.

### Check

- [getters & setters](#getters--setters)
- [save()](#save)
- [enable()](#enable)
- [disable()](#disable)
- [downtimes()](#downtimes)
- [metrics()](#metrics)
- [delete()](#delete)

#### Getters & setters

To get a Check's data, just access the relevant property:

	$check->alias;				// 'MySite'

To set data on a Check, assign the value to the property:

	$check->alias = 'MySite';
	
**Note that changes via setters will not be applied until you call save().**

#### save()

Applies any changes made to the check. You may provide an additional array of changes to this method.

	$check->alias = 'MySite';
	$check->save([
		'period'	=> 120,
	]);

#### enable()

Enables the check.

#### disable()

Disables the check.

#### downtimes()

Returns an array of downtime information for the check; you can provide any of the [documented options](https://updown.io/api#rest) in an array as the second argument.

#### metrics()

Returns an array of metrics for the check; you can provide any of the [documented options](https://updown.io/api#rest) in an array as the second argument.

#### delete()

Deletes the check.

## Working with Laravel

If you use Laravel, the package can assist you by auto-resolving a client, disabling checks while in maintenance mode, and listening for events via webhooks.

- [Config](#config)
- [Service Provider](#service-provider)
- [Maintenance mode](#maintenance-mode)
- [Webhooks](#webhooks)

### Config

Start by publishing the config file:

	$ php artisan vendor:publish --Provider=Gbradley\Updown\Laravel\ServiceProvider
	
By default, `config/updown.php` attempts to read `UPDOWN_API_KEY` and `UPDOWN_APP_TOKEN` from your `.env` file.

### Service Provider

With Laravel 5.5 or later, the ServiceProvider will be automatically detected. You can now resolve a configured singleton directly from the container or via dependency injection.

	use GBradley\Updown\Client;
	...
	public function handle(Client $updown)
	{
		// do stuff
	}
	
### Maintenance mode

If your application is checked by updown.io, you'll want to avoid running that check during mainenance. Provided your app token is set, the package will automatically disable the check when you run the `artisan down` command, and enable it again after `artisan up`.

If you wish to disable this behaviour, you can do so in the config file.

### Webhooks

To respond to updown.io's webhooks, enable webhooks in `config/updown.php`. The webhook URI is set to a sensible default, but you may change it if you wish. If you use config and / or route caching, you should refresh these caches now.

Next, go to your [updown.io settings](https://updown.io/settings/edit) and add the webhook using its full address. Your application will now begin to accept webhook requests, so all that's left is to handle the relevent events.

#### Listeners

To listen for the events, [create and register a Listener](https://laravel.com/docs/5.7/events#registering-events-and-listeners) for the `GBradley\Updown\Laravel\Events\Down` & `GBradley\Updown\Laravel\Events\Up` events. 

    protected $listen = [
        'GBradley\Updown\Laravel\Events\Down' => ['App\Listeners\Down'],
        'GBradley\Updown\Laravel\Events\Up' => ['App\Listeners\Up'],
    ];
    
When updown.io sends a request to your webhook, the appropriate listener will receive an event with `check` and `downtime` properties.

    public function handle(Down $event)
    {
    	$evnt->check->alias;	// 'MySite'
    }
    
#### Broadcasting
    
If you wish to [broadcast](https://laravel.com/docs/5.7/broadcasting) the `Up` & `Down` events, perhaps to your front-end, you may subclass them and specify the custom classes in `config/updown.php`.

	use GBradley\Updown\Laravel\Events\Down as BaseDown;
	use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
	
	class Down extends BaseDown implements ShouldBroadcast
	{
	    ...
	}
	
You may now follow the standard Laravel procedure for broadcasting to your channels.