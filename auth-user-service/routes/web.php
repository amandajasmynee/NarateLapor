<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use App\Http\Controllers\AuthController;

$router->get('/', function () {
    return response()->json(['message' => 'NarateLapor Auth Service is running.']);
});

$router->group([], function () use ($router) {

    // Public endpoints
    $router->post('/register', 'AuthController@register');
    $router->post('/login', 'AuthController@login');

    // Protected route with JWT auth
    $router->get('/me', [
        'middleware' => 'jwt.auth',
        function () {
            return response()->json(auth()->user());
        }
    ]);

    // Intern-only route
    $router->get('/intern-only', [
        'middleware' => ['jwt.auth', 'role:intern'],
        function () {
            return response()->json(['message' => 'Halo Intern!']);
        }
    ]);

    // Admin or Supervisor
    $router->get('/admin-or-supervisor', [
        'middleware' => ['jwt.auth', 'role:admin,supervisor'],
        function () {
            return response()->json(['message' => 'Admin atau Supervisor boleh masuk']);
        }
    ]);
});

$router->options('{any:.*}', function () {
    return response('OK', 200);
});
