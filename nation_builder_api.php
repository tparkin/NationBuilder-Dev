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
		private $accessToken = null;

		public function __construct() {
			$this->client = new OAuth2\Client(self::CLIENT_ID, self::CLIENT_SECRET);
		}

		public function getAuthorizationUrl() {
			return $this->client->getAuthenticationUrl(
				$this->getNationBuilderUrl(self::AUTHORIZE_PATH), 
				self::REDIRECT_URL
			);
		}

		public function getAccessToken($code) {
			$params = array('code' => $code, 'redirect_uri' => self::REDIRECT_URL);
			$response = $this->client->getAccessToken($this->getNationBuilderUrl(self::ACCESS_TOKEN_PATH), 'authorization_code', $params);
			$token = $response['result']['access_token'];
			$this->setAccessToken($token);

			return $token;
		}

		public function setAccessToken($token) {
			//$this->client->setAccessToken($token);
			$this->accessToken = $token;
		}

		public function getPeople() {
			$response = $this->getJSON($this->getNationBuilderUrl('/api/v1/people'));
			return $this->processResponse($response);
		}

		public function addOrUpdateContact() {
			$this->addContact();
		}

		public function addContact($id, $data) {
			$response = $this->getJSON($this->getNationBuilderUrl('/api/v1/people/' . $id . '/contacts'), $data, "POST");
			return $this->processResponse($response);
		}

		public function createPerson($data) {
			$response = $this->postJSON($this->getNationBuilderUrl('/api/v1/people'), json_encode($data), "POST");
			return $this->processResponse($response);
		}

		public function updatePerson($id, $data) {
			$response = $this->postJSON($this->getNationBuilderUrl('/api/v1/people/' . $id), json_encode($data), "PUT");
			return $this->processResponse($response);
		}

		public function deletePerson($id) {
			$response = $this->postJSON($this->getNationBuilderUrl('/api/v1/people/' . $id), "", "DELETE");
			return $this->processResponse($response);
		}

		private function getNationBuilderUrl($path) {
			return "https://" . self::NATION_BUILDER_SLUG . ".nationbuilder.com" . $path;
		}

		private function processResponse($response) {
			if($response['code'] >= 200 && $response['code'] < 400) {
				return $response['result'];
			} else {
				echo "Error processing request: " . print_r($response, true);
			}
		}

		private function getJSON($uri) {
			return $this->postJSON($uri, "", "GET");
		}

		private function postJSON($uri, $json, $method = "POST") {
			return $this->client->fetch($this->appendAccessToken($uri), $json, $method, array("Accept" => "application/json", "Content-Type" => "application/json"));
		}

		private function appendAccessToken($uri) {
			return $uri . "?access_token=" . $this->accessToken;
		}
	}

	// Helper method to output people
	function outputPeople($result) {
		echo "========== THE PEOPLE ==========\r\n";
		foreach($result['results'] as $person) {
			echo "Person:\r\n";
			echo "\tID:\t" . $person['id'] . "\r\n";
			echo "\tEmail:\t" . $person['email'] . "\r\n";
			echo "\tName:\t" . $person['first_name'] . " " . $person['last_name'] . "\r\n";
			echo "----------\r\n\r\n";
		}
	}

	$api = new NationBuilderAPI();
	// Use test token for demo purposes
	$api->setAccessToken('090b5a68fe41cdb1e292269f391eb81eb75d086eee3672168cd32d37a1e101b5');

	echo "Here are our initial people...\r\n";

	// Get the people
	$result = $api->getPeople();
	outputPeople($result);
	
	// Create a person
	$data = array(
		'person' => array(
			'email' => 'bob' . time() . '@example.com',
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
	echo "Creating person Bob Smith...\r\n";
	$result = $api->createPerson($data);

	// Get the people
	$result = $api->getPeople();
	outputPeople($result);

	$person_id = $result['results'][0]['id'];

	// Update the person
	$data = array(
		'person' => array(
			'first_name' => "Joe",
			'email' => "johndoe" . time() . "@gmail.com",
			"phone" => "303-555-0841"
		)
	);
	echo "Renaming Bob Smith to Joe Smith with email " . $data['person']['email'] . "...\r\n";
	$api->updatePerson($person_id, $data);

	// Get the people
	$result = $api->getPeople();
	outputPeople($result);

	// Delete the people
	echo "Deleting all but first two people...\r\n";
	for($x = 0; $x < sizeof($result['results']); $x++) {
		$user_id = $result['results'][$x]['id'];
		if($user_id > 2) {
			$api->deletePerson($user_id);
		}
	}

	// Get the people
	$result = $api->getPeople();
	outputPeople($result);

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

	// Add a contact for our user
	echo "Adding a contact to our user...\r\n";
	$result = $api->addContact(2, $data);
	echo "Success! Contact added.\r\n";


?>
