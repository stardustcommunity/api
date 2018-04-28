<?php
namespace App\Middlewares;
use DI\Container;
use ParagonIE\ConstantTime\Encoding;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class WebHookAuthMiddleware {
	/**
	 * @var Container
	 */
	private $container;

	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
	{
		if ($request->hasHeader('X-Hub-Signature')){
			$tokenHashed = 'sha1=' . sha1($this->container->get('webhook')['token']);
			if ($request->getHeader('X-Hub-Signature')[0] == $tokenHashed){
				return $next($request, $response);
			}else{
				return $response->withStatus(401)->withJson([
					'success' => false,
					'errors' => [
						[
							'code' => 'auth_invalid',
							'message' => 'Invalid authentication'
						]
					]
				]);
			}
		}else{
			return $response->withStatus(401)->withJson([
				'success' => false,
				'errors' => [
					[
						'code' => 'auth_required',
						'message' => 'You must provide a authentication'
					]
				]
			]);
		}
	}
}