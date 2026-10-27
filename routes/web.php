<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\HospitalController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserFaqController;
use App\Http\Controllers\UserGalleryController;
use App\Http\Controllers\UserNewsController;
use App\Http\Controllers\UserArticleController;
use App\Http\Controllers\UserBlogController;
use App\Http\Controllers\UserReviewController;
use App\Http\Controllers\UserServiceController;
use App\Http\Controllers\UserSliderController;
use App\Http\Controllers\UserCmsPageController;
use App\Http\Controllers\UserAboutUsController;
use App\Http\Controllers\UserVideoController;
use App\Http\Controllers\UserSmtpSettingsController;
use App\Http\Controllers\UserSocialMediaController;
use App\Http\Controllers\UserWebsiteSettingsController;
use App\Http\Controllers\UserHospitalDoctorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Route::controller(LoginController::class)->group(function () {
    Route::get('login', 'userLogin')->name('login');
    Route::post('login', 'userLogins')->name('user.logins');
    Route::post('user/login', 'userLogins')->name('user.legacy-logins');
    Route::get('user/login', fn () => redirect()->route('login'));
    Route::get('admin/login', 'adminLogin')->name('admin.login');
    Route::post('admin/logins', 'adminLogins')->name('admin.logins');
});

Route::prefix('review')->name('public.reviews.')->controller(UserReviewController::class)->group(function () {
    Route::get('{token}', 'publicForm')
        ->where('token', '[A-Za-z0-9]{64}')
        ->middleware('throttle:30,1')
        ->name('create');
    Route::post('{token}', 'publicStore')
        ->where('token', '[A-Za-z0-9]{64}')
        ->middleware('throttle:10,1')
        ->name('store');
});

Route::middleware(['auth:admin'])->group(function () {
    Route::post('admin/logout', [LoginController::class, 'logout'])->name('logout');

    Route::controller(AdminController::class)->group(function () {
        Route::get('admin', 'index')->name('admin.index');
    });

    Route::prefix('category')->controller(CategoryController::class)->name('category.')->group(function () {
        Route::get('', 'index')->name('index');
        Route::post('', 'store')->name('store');
        Route::put('{category}', 'update')->name('update');
        Route::patch('{category}/status', 'toggleStatus')->name('status');
    });

    Route::prefix('service')->controller(ServiceController::class)->name('service.')->group(function () {
        Route::get('', 'index')->name('index');
        Route::post('', 'store')->name('store');
        Route::put('{service}', 'update')->name('update');
        Route::patch('{service}/status', 'toggleStatus')->name('status');
    });

    Route::prefix('users/doctors')->controller(DoctorController::class)->name('doctor.')->group(function () {
        Route::get('', 'index')->name('index');
        Route::post('', 'store')->name('store');
        Route::put('{doctor}', 'update')->name('update');
        Route::patch('{doctor}/status', 'toggleStatus')->name('status');
        Route::post('{doctor}/login-access', 'createLoginAccess')->name('login-access');
        Route::get('{doctor}/login-access', 'viewLoginAccess')->name('login-access.view');
        Route::post('{doctor}/login-access/reset', 'resetLoginAccess')->name('login-access.reset');
    });

    Route::prefix('users/hospitals')->controller(HospitalController::class)->name('hospital.')->group(function () {
        Route::get('', 'index')->name('index');
        Route::post('', 'store')->name('store');
        Route::put('{hospital}', 'update')->name('update');
        Route::patch('{hospital}/status', 'toggleStatus')->name('status');
        Route::post('{hospital}/login-access', 'createLoginAccess')->name('login-access');
        Route::get('{hospital}/login-access', 'viewLoginAccess')->name('login-access.view');
        Route::post('{hospital}/login-access/reset', 'resetLoginAccess')->name('login-access.reset');
    });

});


Route::prefix('user')->middleware(['auth:user'])->group(function () {
    Route::post('logout', [LoginController::class, 'userLogout'])->name('user.logout');

    Route::controller(UserController::class)->group(function () {
        Route::get('', 'index')->name('user.index');
    });

    Route::get('doctors', [UserHospitalDoctorController::class, 'index'])
        ->name('user.doctors.index');
    Route::post('doctors', [UserHospitalDoctorController::class, 'store'])
        ->name('user.doctors.store');

    Route::prefix('reviews')->name('user.reviews.')->controller(UserReviewController::class)->group(function () {
        Route::get('', 'index')->name('index');
        Route::get('qr', 'qrPage')->name('qr');
        Route::post('', 'store')->name('store');
        Route::put('{review}', 'update')->whereNumber('review')->name('update');
        Route::patch('{review}/status', 'toggleStatus')->whereNumber('review')->name('status');
    });

    Route::prefix('faqs')->name('user.faqs.')->controller(UserFaqController::class)->group(function () {
        Route::get('', 'index')->name('index');
        Route::post('', 'store')->name('store');
        Route::put('{faq}', 'update')->whereNumber('faq')->name('update');
        Route::patch('{faq}/status', 'toggleStatus')->whereNumber('faq')->name('status');
    });

    Route::prefix('gallery')->name('user.gallery.')->controller(UserGalleryController::class)->group(function () {
        Route::get('', 'index')->name('index');
        Route::post('', 'store')->name('store');
        Route::put('{image}', 'update')->whereNumber('image')->name('update');
        Route::patch('{image}/status', 'toggleStatus')->whereNumber('image')->name('status');
        Route::delete('{image}', 'destroy')->whereNumber('image')->name('destroy');
    });

    Route::prefix('videos')->name('user.videos.')->controller(UserVideoController::class)->group(function () {
        Route::get('', 'index')->name('index');
        Route::get('{video}/stream', 'stream')->whereNumber('video')->name('stream');
        Route::post('', 'store')->name('store');
        Route::put('{video}', 'update')->whereNumber('video')->name('update');
        Route::patch('{video}/status', 'toggleStatus')->whereNumber('video')->name('status');
        Route::delete('{video}', 'destroy')->whereNumber('video')->name('destroy');
    });

    Route::prefix('news')->name('user.news.')->controller(UserNewsController::class)->group(function () {
        Route::get('', 'index')->name('index');
        Route::post('', 'store')->name('store');
        Route::put('{news}', 'update')->whereNumber('news')->name('update');
        Route::patch('{news}/status', 'toggleStatus')->whereNumber('news')->name('status');
        Route::delete('{news}', 'destroy')->whereNumber('news')->name('destroy');
    });

    Route::prefix('articles')->name('user.articles.')->controller(UserArticleController::class)->group(function () {
        Route::get('', 'index')->name('index');
        Route::post('', 'store')->name('store');
        Route::put('{article}', 'update')->whereNumber('article')->name('update');
        Route::patch('{article}/status', 'toggleStatus')->whereNumber('article')->name('status');
        Route::delete('{article}', 'destroy')->whereNumber('article')->name('destroy');
    });

    Route::prefix('blogs')->name('user.blogs.')->controller(UserBlogController::class)->group(function () {
        Route::get('', 'index')->name('index');
        Route::post('', 'store')->name('store');
        Route::put('{blog}', 'update')->whereNumber('blog')->name('update');
        Route::patch('{blog}/status', 'toggleStatus')->whereNumber('blog')->name('status');
        Route::delete('{blog}', 'destroy')->whereNumber('blog')->name('destroy');
    });

    Route::prefix('services')->name('user.services.')->controller(UserServiceController::class)->group(function () {
        Route::get('', 'index')->name('index');
        Route::post('', 'store')->name('store');
        Route::put('catalog/{service}', 'updateCatalog')->whereNumber('service')->name('catalog.update');
        Route::patch('catalog/{service}/status', 'toggleCatalogStatus')->whereNumber('service')->name('catalog.status');
        Route::put('custom/{userService}', 'updateCustom')->whereNumber('userService')->name('custom.update');
        Route::patch('custom/{userService}/status', 'toggleCustomStatus')->whereNumber('userService')->name('custom.status');
        Route::delete('custom/{userService}', 'destroyCustom')->whereNumber('userService')->name('custom.destroy');
    });

    Route::prefix('slider')->name('user.slider.')->controller(UserSliderController::class)->group(function () {
        Route::get('', 'index')->name('index');
        Route::post('', 'store')->name('store');
        Route::put('{slide}', 'update')->whereNumber('slide')->name('update');
        Route::patch('{slide}/status', 'toggleStatus')->whereNumber('slide')->name('status');
        Route::delete('{slide}', 'destroy')->whereNumber('slide')->name('destroy');
    });

    Route::prefix('cms-pages')->name('user.cms-pages.')->controller(UserCmsPageController::class)->group(function () {
        Route::get('', 'index')->name('index');
        Route::post('', 'store')->name('store');
        Route::put('{page}', 'update')->whereNumber('page')->name('update');
        Route::patch('{page}/status', 'toggleStatus')->whereNumber('page')->name('status');
        Route::delete('{page}', 'destroy')->whereNumber('page')->name('destroy');
    });

    Route::prefix('about-us')->name('user.about-us.')->controller(UserAboutUsController::class)->group(function () {
        Route::get('', 'index')->name('index');
        Route::put('', 'save')->name('save');
    });

    Route::prefix('smtp')->name('user.smtp.')->controller(UserSmtpSettingsController::class)->group(function () {
        Route::get('', 'index')->name('index');
        Route::put('', 'save')->name('save');
        Route::post('test', 'sendTest')->middleware('throttle:3,1')->name('test');
    });

    Route::prefix('social-media')->name('user.social-media.')->controller(UserSocialMediaController::class)->group(function () {
        Route::get('', 'index')->name('index');
        Route::post('', 'store')->name('store');
        Route::put('{socialLink}', 'update')->whereNumber('socialLink')->name('update');
        Route::patch('{socialLink}/status', 'toggleStatus')->whereNumber('socialLink')->name('status');
        Route::delete('{socialLink}', 'destroy')->whereNumber('socialLink')->name('destroy');
    });

    Route::prefix('website-settings')->name('user.website-settings.')->controller(UserWebsiteSettingsController::class)->group(function () {
        Route::get('', 'index')->name('index');
        Route::put('', 'save')->name('save');
    });
});