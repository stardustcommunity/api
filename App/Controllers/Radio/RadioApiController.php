<?php

namespace App\Controllers\Radio;

use App\Controllers\Controller;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use DusanKasan\Knapsack\Tests\Helpers\Car;
use GuzzleHttp\Client;
use Laravie\Parser\Xml\Document;
use Laravie\Parser\Xml\Reader;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RadioApiController extends Controller
{
	public function getInfo(ServerRequestInterface $request, ResponseInterface $response, Client $client)
	{
		try {
			$radionomyResponse = $client->get(
				"http://api.radionomy.com/currentsong.cfm?radiouid={$this->container->get('radionomy')['radio_uid']}&type=xml&cover=yes&defaultcover=yes&size=90&dynamicconf=yes",
				[
					'timeout' => 24
				]
			);
		} catch (\Exception $e) {
			return $response->withStatus(400)->withJson([
				'success' => false,
				'errors' => [
					[
						'code' => $e->getCode(),
						'message' => $e->getMessage()
					]
				]
			]);
		}
		$xml = (new Reader(new Document()))->extract($radionomyResponse->getBody()->getContents());
		$track = $xml->parse([
			'radionomy_id' => ['uses' => 'track.uniqueid'],
			'play_duration' => ['uses' => 'track.playduration'],
			'start_time' => ['uses' => 'track.starttime'],
			'cover' => ['uses' => 'track.cover'],
			'title' => ['uses' => 'track.title'],
			'artists' => ['uses' => 'track.artists'],
		]);
		/*$track = [
			'radionomy_id' => "4526670054",
			'play_duration' => "281999",
			'start_time' => "2018-04-29 11:08:55.967",
			'cover' => "http://i3.radionomy.com/tracks/90/1ea66ffc-cf09-42c5-98c3-04008d2d5478?radiouid=f23a1d32-55ac-4a84-b224-7dd213d42814",
			'title' => "Un titre",
			'artists' => "Des artistes",
		];*/
		$endTime = Carbon::parse($track['start_time'], 'Europe/Paris')->addSeconds($track['play_duration'] / 1000);

		return $response->withJson([
			'success' => true,
			'data' => [
				'track' => $track,
				'time' => [
					'end_at' => $endTime->toDateTimeString(),
					'end_in' => [
						'milliseconds' => $endTime->diffInSeconds() * 1000,
						'seconds' => $endTime->diffInSeconds(),
						'human' => $endTime->diffForHumans(),
					]
				]
			]
		]);
	}
}