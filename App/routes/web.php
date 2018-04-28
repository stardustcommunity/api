<?php
/*
|--------------------------------------------------------------------------
| Web routing
|--------------------------------------------------------------------------
|
| Register it all your normal routes
|
*/


$app->get('/', [\App\Controllers\PagesController::class, 'getHome'])->setName('home');
//$app->post('/webhooks/ifttt/new-video', [\App\Controllers\WebHooks\WebHooksIFTTTController::class, 'newVideo'])->setName('webhooks.youtube.new-video');
$app->post('/webhooks/youtube/new-video', [\App\Controllers\WebHooks\WebHooksYoutubeController::class, 'newVideo'])->add(\App\Middlewares\WebHookAuthMiddleware::class)->setName('webhooks.youtube.new-video');