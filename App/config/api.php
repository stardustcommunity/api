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
	]
];