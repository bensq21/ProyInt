<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SedeController;
use App\Http\Controllers\TutorController;
use App\Http\Controllers\AlumnoController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});*/


Route::post('login', [AuthController::class, 'authenticate']);
Route::post('register', [AuthController::class, 'register']);


Route::get('tutores', [TutorController::class, 'index']);
Route::get('sedes', [SedeController::class, 'index']);

Route::group(['middleware' => ['jwt.verify']], function () {
    //Todo lo que este dentro de este grupo requiere verificación de usuario.
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('user', [AuthController::class, 'getUser']);

    Route::put('user/update', [AuthController::class, 'update']);
    Route::delete('user/delete', [AuthController::class, 'destroy']);

    Route::get('alumnos', [AlumnoController::class, 'alumnos']);

    Route::get('candidaturas', [AlumnoController::class, 'candidaturas']);
    Route::post('candidaturas/create', [AlumnoController::class, 'store']);
    Route::put('candidaturas/update/{id}', [AlumnoController::class, 'update']);
    Route::delete('candidatura/delete/{id}', [AlumnoController::class, 'destroy']);

    Route::post('sede/create', [SedeController::class, 'store']);
    Route::put('sede/update/{id}', [SedeController::class, 'update']);
    Route::delete('sede/delete/{id}', [SedeController::class, 'destroy']);


    Route::post('tutor/create', [TutorController::class, 'store']);
    Route::put('tutor/update/{id}', [TutorController::class, 'update']);
    Route::delete('tutor/delete/{id}', [TutorController::class, 'destroy']);
});