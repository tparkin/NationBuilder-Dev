<?php

	require('includes/oauth2/client.php');
	require('includes/oauth2/GrantType/IGrantType.php');
	require('includes/oauth2/GrantType/AuthorizationCode.php');


	class NationBuilderAPI {
		const CLIENT_ID = '<id>';
		const CLIENT_SECRET = '<secret>';
		const REDIRECT_URL = 'http://www.domain.com/oauth_callback';
		const NATION_BUILDER_SLUG = 'parkinwebdev';
		const AUTHORIZE_PATH = '/oauth/authorize';
		const ACCESS_TOKEN_PATH = '/oauth/token';

		private $client = null;

		public function __construct() {
			$this->client = new OAuth2\Client(CLIENT_ID, CLIENT_SECRET);
		}

		public function getAuthorizationUrl() {
			return $this->client->getAuthenticationUrl(
				$this->getNationBuilderUrl(AUTHORIZE_PATH), 
				REDIRECT_URL
			);
		}

		public function getAccessToken($code) {
			$params = array('code' => $code, 'redirect_uri' => REDIRECT_URL);
			$response = $this->client->getAccessToken($this->getNationBuilderUrl(ACCESS_TOKEN_PATH), 'authorization_code', $params);
			$token = $response['result']['access_token'];
			$this->setAccessToken($token);

			return $token;
		}

		public function setAccessToken($token) {
			$this->client->setAccessToken($token);
		}

		public function getPeople() {
			$response = $this->client->fetch($this->getNationBuilderUrl('/api/v1/people'));
			return $response;
		}

		public function addOrUpdateContact() {
			$this->addContact();
		}

		public function addContact($data) {
			$response = $this->client->fetch($this->getNationBuilderUrl('/api/v1/people'), $data, "POST");
			return $response;
		}

		public function createPerson($data) {
			$response = $this->client->fetch($this->getNationBuilderUrl('/api/v1/people'), $data, "POST");
			return $response;
		}

		public function updatePerson($id, $data) {
			$response = $this->client->fetch($this->getNationBuilderUrl('/api/v1/people/' . $id), $data, "PUT");
			return $response;
		}

		public function deletePerson($id) {
			$response = $this->client->fetch($this->getNationBuilderUrl('/api/v1/people/' . $id), array(), "DELETE");
			return $response;
		}

		private function getNationBuilderUrl($path) {
			return "https://" . NATION_BUILDER_SLUG . ".nationbuilder.com" . $path;
		}
	}

	$api = new NationBuilderAPI();
	
	// Create a person
	$data = array(
		'person' => array(
			'email' => 'bob@example.com',
			'last_name' => 'Smith',
			'first_name' => 'Bob',
			'sex' => 'M',
			'signup_type' => 0,
			'employer' => 'Dexter Labs',
			'party' => 'P',
			'registered_address' => array(
				'state' => 'TX',
				'country_code' => 'US'
			)
		)
	);
	$result = $api->createPerson($data);
	print_r($result);

	// Get the people
	$result = $api->getPeople();
	print_r($result);

	$person_id = $result['results'][0]['id'];

	// Update the person
	$data = array(
		'person' => array(
			'first_name' => "Joe",
			'email' => "johndoe@gmail.com",
			"phone" => "303-555-0841"
		)
	);
	$api->updatePerson($person_id, $data);

	// Get the people
	$result = $api->getPeople();
	print_r($result);

	// Delete the person
	$api->deletePerson($person_id);

	// Get the people
	$result = $api->getPeople();
	print_r($result);


	// Add Contact
	$data = array(
		'contact' => array(
			'sender_id' => 1058,
			'broadcaster_id' => 10,
			'status' => 'not_interested',
			'method' => 'door_knock',
			'type_id' => 5,
			'note' => 'He did not support the cause'
		)
	);
	$api->addContact($data);

?>
