<?php

/** @var \Laravel\Lumen\Routing\Router $router */

$router->get('/', function () {
    return 'Review Rating Service is running!';
});

$router->group(['middleware' => ['jwt.parse', 'role:supervisor']], function () use ($router) {
    $router->post('/ratings', 'RatingController@store');
    $router->get('/ratings', 'RatingController@index');
    $router->get('/ratings/{report_id}', 'RatingController@show');
    $router->put('/ratings/{report_id}', 'RatingController@update');
    $router->delete('/ratings/{report_id}', 'RatingController@destroy');
});

$router->options('/{any:.*}', function () {
    return response('', 200);
});
