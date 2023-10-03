<?php

use Illuminate\Http\Request;

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

// Route::middleware('auth:api')->get('/crm', function (Request $request) {
//     return $request->user();
// });


Route::group(['prefix' => 'crm'], function () {

    Route::post('/login', 'LoginController@login')->withOutMiddleware([usertoken::class, checkuser::class]);
    Route::get('/logout', 'LoginController@logout');
    Route::post('/user/add', 'UserController@store');
    Route::post('/user/update', 'UserController@store');
    Route::get('/user/activeLists', 'UserController@getActiveUserList');
    Route::get('/user/getTrainerLists', 'UserController@getTrainersList');
    Route::get('/user/getReferralLists', 'UserController@getReferralList');
    Route::post('/batch/create', 'BatchController@store');
    Route::get('/batch/list', 'BatchController@getBatchList');
    Route::post('/course/create', 'CourseController@store');
    Route::get('/course/list', 'CourseController@getCourseList');
    Route::post('/students/add', 'StudentController@store');
    Route::post('/students/update', 'StudentController@update');
    Route::get('/students/list', 'StudentController@getStudentsList');
    Route::get('/user/getMyStudents', 'UserController@getMyStudentLists');
    Route::get('/user/getMyReferrals', 'UserController@getMyReferredStudentsLists');
    Route::get('/students/{id}', 'StudentController@getStudentDataById');
    Route::get('/batch/{id}', 'BatchController@getBatchById');
    Route::get('/course/{id}', 'CourseController@getCourseById');
    Route::get('/user/{uuid}', 'UserController@getUsersById');
    Route::get('/paymentmethod/list', 'UserController@getPaymentMethodList');
});

Route::resource('/crm/user', 'UserController');
