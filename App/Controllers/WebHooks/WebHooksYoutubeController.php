<?php

namespace App\Controllers\WebHooks;

use App\Controllers\Controller;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Monolog\Logger;

class WebHooksYoutubeController extends Controller
{
	/**
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @param Logger $logger
	 * @param Client $client
	 * @return mixed
	 */
	public function newVideo(ServerRequestInterface $request, ResponseInterface $response, Logger $logger, Client $client)
	{
		$id = $request->getParsedBody()->entry->id;
		$youTubeResponse = $client->get("https://www.googleapis.com/youtube/v3/videos?id={$id}&part=snippet%2CcontentDetails%2Cstatistics&key={$this->container->get('google')['api_key']}");

		$youTubeResponseParsed = \json_decode($youTubeResponse->getBody()->getContents())->items[0];
		//send a log
		$logger->info("New video from \"{$youTubeResponseParsed->snippet->channelTitle}\" : https://youtu.be/{$id}");
//		echo $request->getParsedBody()->entry->id;

		return $response->withJson([
			'success' => true
		]);
	}
}