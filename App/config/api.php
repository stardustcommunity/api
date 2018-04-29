<?php
return [
	'google' => [
		'api_key' => getenv('GOOGLE_API_KEY')
	],
	'wordpress' => [
		'endpoint' => getenv('WORDPRESS_ENDPOINT'),
		'username' => getenv('WORDPRESS_USERNAME'),
		'password' => getenv('WORDPRESS_PASSWORD'),
		'author_id' => getenv('WORDPRESS_DEFAULT_AUTHOR_ID')
	],
	"webhook" => [
		'token' => getenv('WEBHOOK_TOKEN')
	],
	"radionomy" => [
		'radio_uid' => "f23a1d32-55ac-4a84-b224-7dd213d42814"
	]
];