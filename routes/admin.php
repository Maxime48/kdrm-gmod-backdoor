<?php

use App\Http\Controllers\kermini\adminLogic;
use App\Http\Controllers\kermini\special\IpBlocker;
use App\Http\Controllers\userModify;
use App\Http\Controllers\usersController;
use App\Http\Controllers\usersModActions;
use App\Models\servers;
use App\Models\User;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'admin'], function(){

    //Dashboard Admin
    Route::get('/', function () {
        $userCount = User::count();
        $server_count = servers::count();
        return view('admindashboard', compact(
            'userCount',
            'server_count'
        ));
    })->name('AdminDashboard');

    //Page with all users | 1: all | 2:Search and pageid
    //rework to consider for new page system, forgot if this was added
    Route::get('/users', [usersController::class, 'all'])->name('users');
    Route::get('/users/{id}', [usersController::class, 'all'])->name('userspage');

    //User Profile | admin side
    Route::get('/user/{id}', [usersController::class, 'show'])->name('user');

    //Get the logs, shows page 0 by default
    Route::get('/logs/{pageid?}', [adminLogic::class, 'getLogs'])->name('adminLogs');

    //Get the server list
    Route::get('/servers/{pageid?}', [adminLogic::class, 'serverList'])->name('serverList');

    //Get all the images
    Route::get('/images/{pageid?}', [adminLogic::class, 'allImages'])->name('AdminImages');

    Route::group(['prefix' => 'payloads', 'middleware' => ['AdminLevel2']], function(){
        //Get all the payloads
        Route::get('/u/{pageid?}', [adminLogic::class, 'allPayloads'])->name('AllPayloads');

        Route::group(['prefix' => 'global'], function(){
            //Get all global payloads
            Route::get('/all/{pageid?}', [adminLogic::class, 'GlobalPayloads'])->name('GlobalPayloads');

                //Create a global payload
                Route::get('/create', [adminLogic::class, 'CreateGlobalPayload'])->name('CreateGlobalPayload'); //Shows creation page
                Route::post('/create/post', [adminLogic::class, 'CreateGlobalPayloadPost'])->name('CreateGlobalPayloadPost'); //recaptcha implemented

                //Edit a global payload
                Route::group(['prefix' => 'edit'], function(){
                    Route::get('/{payloadid}', [adminLogic::class, 'editGlobalPayload'])->name('editGlobalPayload');
                    Route::post('/post/', [adminLogic::class, 'editGlobalPayloadPost'])->name('editGlobalPayloadPost'); //recaptcha needed
                });

                //Delete a global payload
                Route::get('/delete/{payloadid}', [adminLogic::class, 'deleteGlobalPayload'])->name('deleteGlobalPayload');
        });
    });

    //Admin actions
    Route::prefix('useractions')->group(function () {
        Route::get('/ban/{id}', [usersModActions::class, 'ban'])->name('ban');
        Route::get('/unban/{id}', [usersModActions::class, 'unban'])->name('unban');
        Route::get('/promote/{id}', [usersModActions::class, 'promote'])->name('promote');
        Route::get('/promotedown/{id}', [usersModActions::class, 'promotedown'])->name('promotedown');

        //modify user's profile
       Route::prefix('modify')->group(function () {
            Route::get('/{id}', [userModify::class, 'changeMode'])->name('changeMode');
            Route::post('/post/{id}', [userModify::class, 'adminModify'])->name('adminModify'); //recaptcha implemented
       });

    });

    Route::group(['prefix' => 'block'], function(){
        Route::get('/all/{pageid?}', [IpBlocker::class, 'AdminBlockedIps'])->name('AdminBlockedIps');

        Route::post('/new', [IpBlocker::class, 'AdminPostNew'])->name('AdminPostNew');

        Route::get('/edit/{restriction}', [IpBlocker::class, 'AdminEditRestriction'])->name('AdminEditRestriction');
        //Route::post('/edit/{restriction}', [IpBlocker::class, 'AdminEditRestrictionPost'])->name('AdminEditRestrictionPost');

        //Route::get('/delete/{restriction}', [IpBlocker::class, 'AdminDeleteRestriction'])->name('AdminDeleteRestriction');
    });
});

require __DIR__.'/auth.php';
