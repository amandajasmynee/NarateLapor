<?php

/** @var \Laravel\Lumen\Routing\Router $router */

$router->get('/', function () use ($router) {
    return $router->app->version();
});

// SUPERVISOR ROUTES - harus ditaruh duluan
$router->group(['middleware' => ['jwt.parse', 'role:supervisor']], function () use ($router) {
    $router->get('/reports/all', 'ReportController@all'); // lihat semua laporan intern
    $router->patch('/reports/{id}/review', 'ReportController@review');
});

// INTERN ROUTES
$router->group(['middleware' => ['jwt.parse', 'role:intern']], function () use ($router) {
    $router->get('/reports', 'ReportController@index'); // lihat semua laporan sendiri
    $router->post('/reports', 'ReportController@store'); // buat laporan
    $router->put('/reports/{id}', 'ReportController@update'); // update title/status
    $router->delete('/reports/{id}', 'ReportController@destroy'); // hapus laporan
    $router->patch('/reports/{id}/publish', 'ReportController@publish'); // submit laporan
    $router->get('/reports/{id}', 'ReportController@show'); // lihat detail laporan sendiri
});

$router->options('/{any:.*}', function () {
    return response('', 200);
});


