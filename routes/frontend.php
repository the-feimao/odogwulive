<?php
use App\Http\Controllers\AccountPasswordController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\SitemapController;


Route::get('/', [FrontendController::class, 'main']);
Route::get('/index', [FrontendController::class, 'viewIndex'])->name('frontend.index');

// The bare URL has no event ID/name. Keep the route name, but send visitors to
// the event listing where they can choose a specific event.
Route::get('/event-tickets', function () {
    return redirect('/all-events');
})->name('frontend.tickets');

Route::get('/eventdetail/{id}/{name}', [FrontendController::class, 'viewEventTickets'])->name('event.details');


// Route::get('/', [FrontendController::class, 'home']);

Route::group(['prefix' => 'user'], function () {
    Route::get('/register', [FrontendController::class, 'register']);
    Route::post('/register', [FrontendController::class, 'userRegister']);
    Route::get('/login', [FrontendController::class, 'login']);
    Route::post('/login', [FrontendController::class, 'userLogin'])->middleware('throttle:login');
    Route::get('/resetPassword', [FrontendController::class, 'resetPassword']);
    Route::post('/resetPassword', [FrontendController::class, 'userResetPassword']);
    Route::get('/org-register', [FrontendController::class, 'orgRegister']);
    Route::post('/org-register', [FrontendController::class, 'organizerRegister']);
});

// One POST endpoint serves both password forms. The controller dispatches on
// the submitted field names/guard, so neither form action needs to change.
Route::post('/change-password', [AccountPasswordController::class, 'change']);

    Route::get('/all-events', [FrontendController::class, 'allEvents']);
    Route::post('/all-events', [FrontendController::class, 'allEvents']);
    Route::get('/events-category/{id}/{name}', [FrontendController::class, 'categoryEvents']);
    Route::get('/event-type/{type}', [FrontendController::class, 'eventType']);
    Route::get('/event/{id}/{name}', [FrontendController::class, 'eventDetail']);
    Route::get('/organization/{id}/{name}', [FrontendController::class, 'orgDetail']);
    Route::post('/report-event', [FrontendController::class, 'reportEvent']);
    Route::get('/all-category', [FrontendController::class, 'allCategory']);
    Route::get('/all-blogs', [FrontendController::class, 'blogs']);
    Route::get('/blog-detail/{id}/{name}', [FrontendController::class, 'blogDetail']);
    Route::get('/contact', [FrontendController::class, 'contact']);
    Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap.xml');

    // Public, capability-token Flutterwave flow for mobile/API payers. These
    // routes are outside the appuser group and do not grant order access.
    Route::get('/payment-link/{order}/{token}', [FrontendController::class, 'publicPayment'])
        ->name('frontend.publicPayment')->middleware('throttle:60,1');
    Route::get('/payment-link/{order}/{token}/callback', [FrontendController::class, 'publicPaymentCallback'])
        ->name('frontend.publicPayment.callback')->middleware('throttle:60,1');

    Route::group(['middleware' =>'appuser'], function () {
        Route::get('/checkout/{id}', [FrontendController::class, 'checkout']);
        Route::post('/createOrder', [FrontendController::class, 'createOrder']);
        Route::get('/user/profile', [FrontendController::class, 'profile']);
        Route::get('/add-favorite/{id}/{type}', [FrontendController::class, 'addFavorite']);
        Route::get('/add-followList/{id}', [FrontendController::class, 'addFollow']);
        Route::post('/add-bio', [FrontendController::class, 'addBio']);
        Route::get('/change-password', [FrontendController::class, 'changePassword']);
        Route::post('/upload-profile-image', [FrontendController::class, 'uploadProfileImage']);
        Route::post('/user/logout', [FrontendController::class, 'userLogout'])->name('frontend.logout');
        Route::get('/my-tickets', [FrontendController::class, 'userTickets']);
        Route::get('/my-tickets/{id}/invoice-print', [FrontendController::class, 'orderInvoicePrint'])
            ->name('frontend.invoice.print');
        Route::post('/check-coupon', [FrontendController::class, 'checkCoupon'])->name('frontend.checkCoupon');
        Route::get('/update_profile', [FrontendController::class, 'update_profile']);
        Route::post('/update_user_profile', [FrontendController::class, 'update_user_profile']);
        Route::get('/getOrder/{id}', [FrontendController::class, 'getOrder']);
        Route::post('/add-review', [FrontendController::class, 'addReview']);
        Route::get('/web/create-payment/{id}', [FrontendController::class, 'makePayment']);
        Route::post('/web/payment/{id}', [FrontendController::class, 'initialize'])->name('frontendPay');
        Route::get('/web/rave/callback/{id}', [FrontendController::class, 'callback'])->name('frontendCallback');

    });

?>
