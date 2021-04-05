<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Cargando Clases
use \App\Http\Middleware\ApiAuthMiddleware;

//RUTAS DE PRUEBA
Route::get('/', function () {
    return view('welcome');
});


//Ejemplo de rutas con parametros opcionales( nombre de variable + ?)
Route::get('/prueba/{nombre?}', function($nombre = null){
    $texto = '<h2>Texto desde una rutas</h2>';
    $texto .= 'Nombre: '.$nombre;
    
    return view('prueba', array(
        'texto'=> $texto
    ));
});

Route::get('/animales', 'App\Http\Controllers\PruebasController@index');
Route::get('/test-orm', 'App\Http\Controllers\PruebasController@testOrm');

//RUTAS DEL API
    /*METODOS HTTP Comunes
    
    *   GET: Conseguir datos o recursos
    *   POST: Guardar datos o recursos o hacer logica desde un formularios
    *   PUT: Actualizar datos o recursos
    *   DELETE: Eliminar datos o recursos

    */

    //RUTAS DE PRUEBA
    //Route::get('/post/pruebas', 'App\Http\Controllers\PostController@pruebas');
    //Route::get('/usuario/pruebas', 'App\Http\Controllers\UserController@pruebas');
    //Route::get('/category/pruebas', 'App\Http\Controllers\CategoryController@pruebas');

    //RUTAS DE CONTROLADOR DE USUARIOS
    Route::post('/api/register','App\Http\Controllers\UserController@register');
    Route::post('/api/login','App\Http\Controllers\UserController@login');
    Route::put('/api/user/update','App\Http\Controllers\UserController@update');
    Route::post('/api/user/upload', 'App\Http\Controllers\UserController@upload')->middleware(ApiAuthMiddleware::class);
    Route::get('/api/user/avatar/{filename}','App\Http\Controllers\UserController@getImage');
    Route::get('/api/user/detail/{id}','App\Http\Controllers\UserController@detail');

    //RUTAS DEL CONTROLADOR DE CATEGORIAS
    Route::resource('/api/category', 'App\Http\Controllers\CategoryController');

    //Rutas del controlador de Entradas
    Route::resource('/api/post', 'App\Http\Controllers\PostController');
    Route::post('/api/post/upload', 'App\Http\Controllers\PostController@upload');
    Route::get('/api/post/image/{filename}', 'App\Http\Controllers\PostController@getImage');
    Route::get('/api/post/category/{id}', 'App\Http\Controllers\PostController@getPostsByCategory');
    Route::get('/api/post/user/{id}', 'App\Http\Controllers\PostController@getPostsByUser');