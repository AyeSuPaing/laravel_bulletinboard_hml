<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\AuthController;

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/resetPasswordMail', [AuthController::class, 'resetPasswordMail']);
Route::post('/auth/resetPassword', [AuthController::class, 'resetPassword']);
Route::middleware(['auth_api'])->group(function () {
    Route::post('/auth/changePassword', [AuthController::class, 'changePassword']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    // admin permission only
    Route::middleware(['admin_api'])->group(function () {
        // user management
        Route::get('/user/list', [UserController::class, 'getUserList']);
        Route::get('/user/{id}', [UserController::class, 'getUser']);
        Route::post('/user/create', [UserController::class, 'createUser']);
        Route::post('/user/edit/{id}', [UserController::class, 'updateUser']);
        Route::post('/users/unlock', [UserController::class, 'unlockUsers']);
        Route::post('/users/delete', [UserController::class, 'deleteUsers']);
    });
    // profile
    Route::get('/userProfile', [UserController::class, 'profile']);
    Route::post('/userProfile/edit', [UserController::class, 'updateProfile']);
    // post management
    Route::get('/post/list', [PostController::class, 'getPostList']);
    Route::get('/post/{id}', [PostController::class, 'getPost']);
    Route::post('/post/create', [PostController::class, 'createPost']);
    Route::post('/post/edit/{id}', [PostController::class, 'updatePost'])->middleware('user_post_api');
    Route::post('/posts/upload', [PostController::class, 'uploadPosts']);
    Route::post('/posts/download', [PostController::class, 'downloadPosts']);
    Route::post('/posts/template/download', [PostController::class, 'downloadTemplate']);
    Route::post('/posts/delete', [PostController::class, 'deletePosts'])->middleware('user_post_api');
    // session
    Route::get('/session', [AuthController::class, 'getAuthSession']);
    // get table configs
    Route::get('/tableConfigs', [AuthController::class, 'getTableConfigs']);
});
