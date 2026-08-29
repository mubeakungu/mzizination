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

Route::any('/events/handle', 'EventsController@handle');

// Telegram Bot Webhook
Route::post('/telegram/webhook', 'TelegramController@webhook');
Route::get('/telegram/set-webhook', 'TelegramController@setWebhook');
Route::get('/telegram/delete-webhook', 'TelegramController@deleteWebhook');
Route::get('/telegram/webhook-info', 'TelegramController@getWebhookInfo');

Route::post('/payment/fk', 'PaymentController@fk');
Route::post('/payment/rubpay', 'PaymentController@rubpay');
Route::post('/withdraw/handle', 'WithdrawController@fkwalletHandle');

Route::get('/r/{unique_id}', 'ReferralController@setReferral');

Route::group(['prefix' => 'ranks'], function () {
    Route::post('/get', 'UserController@ranks');
});

Route::group(['prefix' => 'faqs'], function () {
    Route::post('/get', 'UserController@faqs');
});

Route::group(['prefix' => 'methods'], function () {
    Route::post('/get', 'UserController@methods');
});
Route::group(['prefix' => '/plinko'], function () {
    Route::post('/init', 'PlinkoController@getMultipliers');
    Route::post('/play', 'PlinkoController@play');
});
Route::group(['prefix' => 'casino'], function () {
    Route::post('/count', 'SlotsController@countCasino');
    Route::post('/get', 'SlotsController@loadCasino');
});
Route::group(['prefix' => 'lives'], function () {
    Route::post('/filter', 'SlotsController@LivesgetByFilter');
    Route::post('/getRandom', 'SlotsController@getRandomLives');
    Route::post('/get/{id}', 'SlotsController@loadCasinoo');
    Route::post('/callback', 'SlotsController@callbackCasino');
    Route::post('/load', 'SlotsController@loadCasino');
});
Route::group(['prefix' => 'slots'], function () {
    Route::post('/count', 'SlotsController@countSlots');
    Route::post('/casino/count', 'SlotsController@countCasino');
    Route::any('/callback/{method}', 'SlotsController@callback1');
    Route::post('/getRandom', 'SlotsController@getRandom');
    Route::post('/load_slot/{id}', 'SlotsController@loadSlot');
    Route::post('/get', 'SlotsController@getSlotWithPagenate');
    Route::post('/filter', 'SlotsController@getByFilter');

    Route::group(['prefix' => 'tbs'], function () {
        //Route::get('/parse', 'SlotsController@parse');
    });

    Route::group(['prefix' => 'b2b'], function () {
        //Route::get('/parse', 'SlotsB2BController@parse');
    });

    Route::group(['prefix' => 'copy'], function () {
        //Route::get('/parse', 'COPYController@parse');
    });

    Route::post('/handle/b2b', 'B2BController@callback');
    Route::post('/handle/copy/{method}', 'COPYController@callback');
});

Route::group(['prefix' => 'api', 'middleware' => 'secretKey'], function () {
    Route::group(['prefix' => 'cashback'], function () {
        Route::post('/send', 'CashbackController@send');
        Route::post('/reset', 'CashbackController@reset');
    });

    Route::post('/getTimer', function() {
        return \App\Setting::query()->find(1)->bot_timer;
    });
    Route::post('/fake', 'FakeController@fake');
    
    // withdraws
    Route::group(['prefix' => 'withdraws', 'namespace' => 'Api'], function () {
        Route::post('/get', 'WithdrawsController@get');
        Route::post('/setStatus', 'WithdrawsController@setStatus');
    });
});

Route::group(['prefix' => 'tournaments'], function () {
    Route::post('/init', 'TournamentController@init');
});

Route::group(['prefix' => 'tournament'], function () {
    Route::post('/{id}/init', 'TournamentController@getInfo');
    Route::post('/{id}/join', 'TournamentController@join');
    //Route::post('/{id}/init', 'TournamentController@getInfo');
});

Route::group(['prefix' => 'profile', 'middleware' => 'auth'], function () {
    Route::post('/init', 'ProfileController@init');
    Route::post('/cashback/claim', 'ProfileController@cashback');
    Route::post('/telegram/link', 'ProfileController@getTelegramLinkCode');
    Route::post('/telegram/unlink', 'ProfileController@unlinkTelegram');
});

Route::group(['prefix' => 'bonus', 'middleware' => 'auth'], function () {
    Route::post('/init', 'BonusController@init');
    Route::post('/checkReposts', 'BonusController@checkReposts');
    Route::post('/transfer', 'BonusController@transfer');
    Route::post('/take', 'BonusController@take');
});

Route::group(['prefix' => 'user'], function () {
    Route::post('/init', 'UserController@init');
    Route::get('/logout', 'UserController@logout');
});

Route::group(['prefix' => '/auth'], function () {
    Route::get('/{provider}', ['as' => 'login', 'uses' => 'Auth\VkController@login']);
    Route::any('/{provider}/callback', 'Auth\VkController@callback');
});

Route::group(['prefix' => 'referral', 'middleware' => 'auth'], function () {
    Route::post('/get', 'ReferralController@init');
    Route::post('/take', 'ReferralController@take');
});

Route::group(['prefix' => 'dice', 'middleware' => 'auth'], function () {
    Route::post('/bet', 'DiceController@bet');
});

Route::group(['prefix' => 'wheel', 'middleware' => 'auth'], function () {
    Route::post('/start', 'WheelController@play');
});

Route::group(['prefix' => 'bubbles', 'middleware' => 'auth'], function () {
    Route::post('/play', 'BubblesController@play');
});

Route::group(['prefix' => 'mines', 'middleware' => 'auth'], function () {
    Route::post('/init', 'MinesController@init');
    Route::post('/start', 'MinesController@createGame');
    Route::post('/open', 'MinesController@openPath');
    Route::post('/take', 'MinesController@take');
});

Route::group(['prefix' => 'withdraw', 'middleware' => 'auth'], function () {
    Route::post('/create', 'WithdrawController@create');
    Route::post('/init', 'WithdrawController@init');
    Route::post('/decline', 'WithdrawController@decline');
});

Route::group(['prefix' => 'payment', 'middleware' => 'auth'], function () {
    Route::post('/create', 'PaymentController@create');
    Route::post('/init', 'PaymentController@init');
    Route::post('/worker', 'PaymentController@workerBalance');
});

// TON Payment Routes
Route::group(['prefix' => 'ton', 'middleware' => 'auth'], function () {
    Route::post('/info', 'TonPaymentController@getInfo');
    Route::post('/rate', 'TonPaymentController@getRate');
    Route::post('/create', 'TonPaymentController@create');
    Route::post('/check', 'TonPaymentController@checkStatus');
    Route::post('/check-all', 'TonPaymentController@checkAll');
    Route::post('/check-memo', 'TonPaymentController@checkMemo');
    Route::post('/history', 'TonPaymentController@history');
    Route::post('/cancel', 'TonPaymentController@cancel');
});

Route::group(['prefix' => 'promo', 'middleware' => 'auth'], function () {
    Route::post('/activate', 'PromoController@activate');
    Route::post('/create', 'PromoController@create');
});

Route::group(['prefix' => 'admin', 'namespace' => 'Admin', 'middleware' => 'auth'], function () {
    Route::group(['prefix' => 'promocodes', 'middleware' => 'access:manager'], function () {
        Route::get('/', 'PromocodeController@index')->name('admin.promocodes');
        Route::get('/create', 'PromocodeController@create')->name('admin.promocodes.create');
        Route::post('/create', 'PromocodeController@createPost');
        Route::get('/delete/{id}', 'PromocodeController@delete')->name('admin.promocodes.delete');
    });
    Route::post('/load', 'AdminController@load')->middleware('access:manager');
    Route::group(['middleware' => 'access:admin'], function () {
        Route::get('/', 'IndexController@index')->name('admin.index');
        Route::get('/users', 'UsersController@index')->name('admin.users');
        Route::get('/bots', 'BotsController@index')->name('admin.bots');
        Route::post('/versionUpdate', 'AdminController@versionUpdate');
        Route::post('/getUserByMonth', 'IndexController@getUserByMonth');
        Route::post('/getDepsByMonth', 'IndexController@getDepsByMonth');
        Route::post('/getVKinfo', 'IndexController@getVK');
        Route::post('/getCountry', 'IndexController@getCountry');

        Route::group(['prefix' => 'users'], function () {
            Route::get('/edit/{id}', 'UsersController@edit')->name('admin.users.edit');
            Route::post('/edit/{id}', 'UsersController@editPost');
            Route::get('/create/{type}/{id}', 'UsersController@createFake')->name('admin.users.createFake');
            Route::post('/create/{type}/{id}', 'UsersController@addFake');
            Route::get('/delete/{id}', 'UsersController@delete')->name('admin.users.delete');
            Route::post('/checker', 'UsersController@checker');
        });

        Route::group(['prefix' => 'bots'], function () {
            Route::get('/create', 'BotsController@create')->name('admin.bots.create');
            Route::post('/create', 'BotsController@createPost');
            Route::get('/edit/{id}', 'BotsController@edit')->name('admin.bots.edit');
            Route::post('/edit/{id}', 'BotsController@editPost');
            Route::get('/delete/{id}', 'BotsController@delete')->name('admin.bots.delete');
        });

        Route::group(['prefix' => 'settings'], function () {
            Route::get('/', 'SettingsController@index')->name('admin.settings');
            Route::post('/', 'SettingsController@save');
        });

        Route::group(['prefix' => 'antiminus'], function () {
            Route::get('/', 'AntiminusController@index')->name('admin.antiminus');
            Route::post('/', 'AntiminusController@save');
        });

        Route::group(['prefix' => 'ranks'], function () {
            Route::get('/', 'RanksController@index')->name('admin.ranks');
            Route::get('/edit/{id}', 'RanksController@edit')->name('admin.ranks.edit');
            Route::post('/', 'AntiminusController@save');
        });
        Route::group(['prefix' => 'withdraws'], function () {
            Route::get('/', 'WithdrawsController@index')->name('admin.withdraws');
            Route::post('/get', 'WithdrawsController@getById');
            Route::post('/send', 'WithdrawsController@send');
            Route::post('/decline', 'WithdrawsController@decline');
            Route::get('/fake/{id}', 'WithdrawsController@sendFake');
        });

        Route::group(['prefix' => 'deposits'], function () {
            Route::get('/', 'DepositsController@index')->name('admin.deposits');
        });

        Route::group(['prefix' => 'bonus'], function () {
            Route::get('/', 'BonusController@index')->name('admin.bonus');
            Route::post('/', 'BonusController@create')->name('admin.bonus.create');
            Route::get('/delete/{id}', 'BonusController@delete');
        });

        Route::group(['prefix' => 'tournaments'], function () {
            Route::get('/', 'TournamentsController@index')->name('admin.tournaments');
            Route::get('/create', 'TournamentsController@create')->name('admin.tournaments.create');
            Route::post('/create', 'TournamentsController@createPost');
            Route::get('/edit/{id}', 'TournamentsController@edit')->name('admin.tournaments.edit');
            Route::post('/edit/{id}', 'TournamentsController@editPost');
            Route::get('/delete/{id}', 'TournamentsController@delete')->name('admin.tournaments.delete');
        });

        Route::post('/getMerchant', 'IndexController@getMerchant');
    });
});
Route::get('/success', 'UserController@success');
Route::get('/play', 'UserController@play');
Route::any('/{any}', 'IndexController@index')->where('any', '.*')->name('index');
