<?php

/** @var \Laravel\Lumen\Routing\Router $router */
use Illuminate\Support\Facades\App;


/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'member'], function () use ($router) {
    # Auth
    $router->post('/register', 'Member\AuthController@register');
    $router->post('/login', 'Member\AuthController@login');
    $router->get('/logout', 'Member\AuthController@logout');

    # Verification
    $router->post('/request_email_verification', 'Member\AccountController@request_email_verification');
    // TODO Change Email set STATUS back to NOT VERIFIED

    # Microservice
    // TODO Send Email INQUIRY From email_outbox

    # Open
    // TODO Verify email address from Email Link
});

$router->get('/key', 'ExampleController@generateKey');
// $router->post('foo', 'ExampleController@generateKey');

// $router->get('admin/home', ['middleware' => 'age', function () {
//     return 'Old Enough';
// }]);
// $router->get('fail', function () {
//     return 'Not Mature';
// });

// $router->get('/profile', ['as' => 'profile', 'uses' => 'ExampleController@getProfile']);
// $router->get('/profile/action', ['as' => 'profile.action', 'uses' => 'ExampleController@getProfileAction']);

// $router->get('foo/bar', 'ExampleController@fooBar');

// $router->post('user', 'ExampleController@getUser');
// $router->post('response', 'ExampleController@response');
