<?php

namespace GBradley\Updown;

use GBradley\Updown\Client;

class Check {

	protected $token;
	protected $client;
	protected $data;
	protected $updates;

	public function __construct(string $token, Client $client, array $data = null)
	{
		$this->token = $token;
		$this->client = $client;
		$this->data = $data;
		$this->updates = [];
	}

	/**
	 * Enable the check.
	 */
	public function enable()
	{
		$this->data = $this->client->request('checks/' . $this->token, 'PUT', ['enabled' => true]);
	}

	/**
	 * Disable the check.
	 */
	public function disable()
	{
		$this->data = $this->client->request('checks/' . $this->token, 'PUT', ['enabled' => false]);
	}

	/**
	 * Return the check's downtimes.
	 */
	public function downtimes(array $options = []) : array
	{
		return $this->client->request('checks/' . $this->token . '/downtimes', 'GET', $options);
	}

	/**
	 * Return the check's metrics.
	 */
	public function metrics(array $options = []) : array
	{
		return $this->client->request('checks/' . $this->token . '/metrics', 'GET', $options);
	}

	/**
	 * Save any provided or pending updates.
	 */
	public function save(array $updates = [])
	{
		$updates = $updates + $this->updates;
		if (count($updates)) {
			$this->data = $this->client->request('checks/' . $this->token, 'PUT', $updates);
			$this->updates = [];
		}
	}

	/**
	 * Delete the check.
	 */
	public function delete() : bool
	{
		return $this->client->request('checks/' . $this->token, 'DELETE')['deleted'];
	}

	/**
	 * Return all data for the check.
	 */
	public function data() : array
	{
		return $this->fetch();
	}

	/**
	 * Magic method for getting a named attribute. The method will avoid an HTTP request if possible.
	 */
	public function __get($name) {
		if (array_key_exists($name, $this->updates)) {
			$value = $this->updates[$name];
		} else {
			$value = $this->fetch()[$name];
		}
		return $value;
	}

	/**
	 * Magic method for setting a named attribute.
	 */
	public function __set($name, $value) {
		$this->updates[$name] = $value;
	}

	/**
	 * Fetch the check data.
	 */
	protected function fetch() : array
	{
		if (is_null($this->data)) {
			$this->data = $this->client->request('checks/' . $this->token);
		}
		return $this->data;
	}
}