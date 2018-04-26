<?php

namespace App\Controllers\WebHooks;

use App\Controllers\Controller;
use DI\Container;
use GuzzleHttp\Client;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Validator\Validator;

class WebHooksYoutubeController extends Controller
{
	/**
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @param Container $container
	 * @param Client $client
	 * @param Logger $logger
	 * @return mixed
	 */
	public function newVideo(ServerRequestInterface $request, ResponseInterface $response, Container $container, Client $client, Logger $logger)
	{
		$apiKey = $container->get('google')['api_key'];
		$validator = new Validator($request->getParsedBody());
		$validator->required('url');
		$validator->notEmpty('url');
		if ($validator->isValid()) {
			$id = str_replace("https://youtu.be/", '', $validator->getValue('url'));
			$youTubeResponse = $client->get("https://www.googleapis.com/youtube/v3/videos?id={$id}&part=snippet%2CcontentDetails%2Cstatistics&key={$apiKey}");
			$youTubeResponseParsed = \json_decode($youTubeResponse->getBody()->getContents())->items[0];
			//send a log
			$logger->info("New video from \"{$youTubeResponseParsed->snippet->channelTitle}\" : {$validator->getValue('url')}");

			//create all the tags of youtube video
			foreach ($youTubeResponseParsed->snippet->tags as $tag) {
				$tagsCreateResponse = $client->post($container->get('wordpress')['endpoint'] . '/wp-json/wp/v2/tags', [
					'json' => [
						'name' => $tag
					],
					'http_errors' => false,
					'auth' => [
						$container->get('wordpress')['username'],
						$container->get('wordpress')['password'],
					]
				]);
				$tagsId = [];
				if ($tagsCreateResponse->getStatusCode() == 409) {
					array_push($tagsId, json_decode($tagsCreateResponse->getBody()->getContents())->data->term_id);
				} else {
					array_push($tagsId, json_decode($tagsCreateResponse->getBody()->getContents())->id);
				}
			}

			//create the featured media
			$wordPressMediaResponseParsed = json_decode($client->post($container->get('wordpress')['endpoint'] . '/wp-json/wp/v2/media', [
				'json' => [
					'status' => 'publish',
				]
			]));

			//create a wordpress post
			$wordPressResponseParsed = json_decode($client->post($container->get('wordpress')['endpoint'] . '/wp-json/wp/v2/posts', [
				'json' => [
					'title' => $youTubeResponseParsed->snippet->title,
					'content' => "<div style=\"width: 100%; height: 480px; overflow: hidden; position: relative; margin-bottom: 1em;\"><iframe frameborder=\"0\" scrolling=\"no\" seamless=\"seamless\" webkitallowfullscreen=\"webkitAllowFullScreen\" mozallowfullscreen=\"mozallowfullscreen\" allowfullscreen=\"allowfullscreen\" id=\"okplayer\" width=\"480\" height=\"270\" src=\"http://youtube.com/embed/{$id}\" style=\"width: 100%; height: 100%\"></iframe></div>   " . $youTubeResponseParsed->snippet->description,
					'author' => $container->get('wordpress')['author_id'],
					'status' => 'publish',
					'tags' => $tagsId,
					'format' => 'video',
					'featured_media' => $featuredMediaId
				],
 				'auth' => [
					$container->get('wordpress')['username'],
					$container->get('wordpress')['password'],
				]
			])->getBody()->getContents());

			return $response->withJson([
				'post' => $wordPressResponseParsed,
			]);

		} else {
			return $response->withJson([
				'success' => false,
				'errors' => $validator->getErrors()
			]);
		}
	}
}