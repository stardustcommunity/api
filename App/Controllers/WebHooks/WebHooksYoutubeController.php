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
		if(is_a($request->getParsedBody(), \SimpleXMLElement::class)){
			$id = $request->getParsedBody()->entry->id;
			$youTubeResponse = $client->get("https://www.googleapis.com/youtube/v3/videos?id={$id}&part=snippet%2CcontentDetails%2Cstatistics&key={$this->container->get('google')['api_key']}");
			$youTubeResponseParsed = \json_decode($youTubeResponse->getBody()->getContents());
			if (isset($youTubeResponseParsed->items[0])) {
				//send a log
				$logger->info("New video from \"{$youTubeResponseParsed->items[0]->snippet->channelTitle}\" : https://youtu.be/{$id}");

				return $response->withJson([
					'success' => true
				]);
			}else{
				return $response->withStatus(404)->withJson([
					'success' => false,
					'errors' => [
						[
							'code' => 'unknown_youtube_id',
							'message' => 'Unknown youtube id'
						]
					]
				]);
			}
		}else{
			return $response->withStatus(400)->withJson([
				'success' => false,
				'errors' => [
					[
						'code' => 'invalid_body',
						'message' => 'Body must be an xml object'
					]
				]
			]);
		}
	}
}