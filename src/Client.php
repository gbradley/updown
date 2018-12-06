<?php

namespace GBradley\Updown;

use GBradley\Updown\Check;
use GBradley\Updown\ApiException;

class Client {

	const BASE_URL = 'https://updown.io/api/';
	
	protected $api_key;
	protected $default_token;

	public function __construct(string $api_key, string $default_token = null)
	{
		$this->api_key = $api_key;
		$this->default_token = $default_token;
	}

	/**
	 * Return an array of Checks, keyed by their token.
	 */
	public function checks() : array
	{
		$checks = [];
		foreach ($this->request('checks') as $data) {
			$checks[$data['token']] = new Check($data['token'], $this, $data);
		}
		return $checks;
	}

	/**
	 * Return a given Check, falling back to the default.
	 */
	public function check(string $token = null) : Check
	{
		return new Check(is_null($token) ? $this->default_token : $token, $this);
	}

	/**
	 * Create a new Check.
	 */
	public function create(string $url, array $data = []) : Check
	{
		$data = $this->request('checks', 'POST', ['url' => $url] + $data);
		return new Check($data['token'], $this, $data);
	}

	/**
	 * List the IP addresses of the nodes.
	 */
	public function ips($version = 4) : array
	{
		return $this->request('nodes/ipv' . $version);
	}

	/**
	 * Make a request to the API.
	 */
	public function request(string $endpoint, string $method = 'GET', array $params = []) : array
	{
		$curl = curl_init();
		curl_setopt_array($curl, [
			CURLOPT_CUSTOMREQUEST	=> $method,
			CURLOPT_RETURNTRANSFER	=> 1,
			CURLOPT_URL           	=> self::BASE_URL . $endpoint . (empty($params) ? '' : '?' . http_build_query($params)),
			CURLOPT_HTTPHEADER    	=> [
				'X-API-KEY: ' . $this->api_key
			]
		]);
		$response = curl_exec($curl);
		$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);

		// Check for well-formed JSON.
		$json = @json_decode($response, true);
		if ($json_error = json_last_error()) {
			throw new ApiException(sprintf("Updown returned a malformed JSON response (%s)", $json_error));
		}

		// Check for 200 OK or 201 Created.
		if (!in_array($http_code, [200, 201])) {
			throw new ApiException(sprintf("Updown returned a %s status code (%s)", $http_code, isset($json['error']) ? $json['error'] : 'no error provided'));
		}

		return $json;
	}

}