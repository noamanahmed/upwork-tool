<?php

use App\Http\Controllers\Api\V1\AccountController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CompanyController;
use App\Http\Controllers\Api\V1\ContractController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\EmailController;
use App\Http\Controllers\Api\V1\EmployeeController;
use App\Http\Controllers\Api\V1\EmployerController;
use App\Http\Controllers\Api\V1\LeadController;
use App\Http\Controllers\Api\V1\LocationController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PermissionController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\QuotationController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\SkillController;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\TranslationController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\LanguageController;
use App\Http\Controllers\Api\V1\UpWorkController;
use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::prefix('translations')->group(function(){
    Route::get('/',[TranslationController::class,'index']);
});
Route::prefix('languages')->group(function(){
    Route::get('/',[LanguageController::class,'index']);
    Route::get('dropdown',[LanguageController::class,'dropdown']);
    Route::get('/{language}',[LanguageController::class,'show']);

});

Route::prefix('auth')->group(function(){
    Route::post('login',[AuthController::class,'login']);
    Route::post('register',[AuthController::class,'register']);
    Route::get('resend-verification-email',[AuthController::class,'resendVerificationEmail'])->middleware(['auth:sanctum']);
    Route::post('verify-email',[AuthController::class,'verifyEmail'])->middleware(['auth:sanctum']);
    Route::post('forgot-password',[AuthController::class,'forgotPassword']);
    Route::post('reset-password',[AuthController::class,'resetPassword']);
});

Route::middleware(['auth:sanctum'])->group(function() {
    Route::prefix('account')->group(function(){
        Route::get('profile',[AccountController::class,'profile']);
        Route::patch('profile',[AccountController::class,'updateProfile']);
        Route::get('settings',[AccountController::class,'settings']);
        Route::patch('settings',[AccountController::class,'updateSettings']);
    });

    Route::apiCrudResource('users',UserController::class);
    Route::get('users/type/dropdown',[UserController::class,'dropdownForType']);
    Route::apiCrudResource('roles',RoleController::class);
    Route::get('permissions',[PermissionController::class,'index']);
});

Route::get('/upwork/auth',[UpWorkController::class,'init']);
Route::get('/upwork/code',[UpWorkController::class,'code']);
Route::get('/upwork/jobs',[UpWorkController::class,'jobs']);
Route::get('/upwork/categories',[UpWorkController::class,'categories']);
Route::get('/upwork/skills',[UpWorkController::class,'skills']);
Route::get('/upwork/timezones',[UpWorkController::class,'timezones']);
Route::get('/upwork/languages',[UpWorkController::class,'languages']);
Route::get('/upwork/countries',[UpWorkController::class,'countries']);
Route::get('/upwork/regions',[UpWorkController::class,'regions']);
Route::get('/upwork/job/{jobId}',[UpWorkController::class,'job']);

Route::fallback(function(){
    abort(404);
});
