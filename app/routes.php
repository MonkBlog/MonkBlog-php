<?php

// Shared variables

$pageList = [];
$siteTitle = Config::get( 'site.title', '' );
$siteVersion = Config::get( 'site.version', '' );
$tagline = Config::get( 'site.tagline', '' );
if ( php_sapi_name() != 'cli' ) {
    $pageList = Page::where( 'is_published', '=', true )->remember( Config::get( 'site.cacheduration', 5 ) )->get();
    $siteTitle = Option::where( 'name', '=', 'site_title' )->remember( Config::get( 'site.cacheduration', 5 ) )->get()->first();
    $siteVersion = Option::where( 'name', '=',  'monk_version' )->remember( Config::get( 'site.cacheduration', 5 ) )->get()->first();
    $tagline = Option::where( 'name', '=',  'tagline' )->remember( Config::get( 'site.cacheduration', 5 ) )->get()->first();
}

View::share( 'siteTitle',  $siteTitle );
View::share( 'monkVersion', $siteVersion );
View::share( 'tagline',  $tagline );
View::share( 'pageList', $pageList );
View::share( 'dateFormat', 'l, M jS @ g:ia' );

// Public routes

Route::get( '/', [ 'as' => 'home', 'uses' => 'HomeController@getHome' ] );
Route::get( 'post/{slug}', [ 'as' => 'post.public.show', 'uses' => 'PostsController@getPostBySlug' ] );
Route::get( '/archive/{offset}/{limit}', [ 'as' => 'archive', 'uses' => 'PostsController@archive' ] );

// Auth routes

Route::get( '/login', [ 'as' => 'login', 'uses' => 'AuthController@getLogin' ] );
Route::post( '/login', [ 'as' => 'login.post', 'uses' => 'AuthController@postLogin' ] );
Route::get( '/logout', [ 'as' => 'logout', 'uses' => 'AuthController@getLogout' ] );

// Admin routes

Route::group( [ 'prefix' => 'admin', 'before' => 'auth' ], function () {
    Route::get( '/', [ 'as' => 'admin.home', 'uses' => 'HomeController@getAdminHome' ] );
    Route::get( '/posts/{id}/publish', [ 'as' => 'admin.posts.publish', 'uses' => 'PostsController@publish' ] );
    Route::get( '/posts/{id}/delete', [ 'as' => 'admin.posts.confirmdestroy', 'uses' => 'PostsController@confirmDestroy' ] );
    Route::get( '/pages/{id}/publish', [ 'as' => 'admin.pages.publish', 'uses' => 'PagesController@publish' ] );
    Route::get( '/pages/{id}/delete', [ 'as' => 'admin.pages.confirmdestroy', 'uses' => 'PagesController@confirmDestroy' ] );
    Route::get( '/categories/{id}/delete', [ 'as' => 'admin.categories.confirmdestroy', 'uses' => 'CategoriesController@confirmDestroy' ] );
    Route::get( '/users/{id}/delete', [ 'as' => 'admin.users.confirmdestroy', 'uses' => 'UsersController@confirmDestroy' ] );
    Route::get( '/users/{id}/resetPassword', [ 'as' => 'admin.users.updatePassword', 'uses' => 'UsersController@updatePassword' ] );
    Route::put( '/users/{id}/savePassword', [ 'as' => 'admin.users.savePassword', 'uses' => 'UsersController@savePassword' ] );
    Route::get( '/options/{id}/delete', [ 'as' => 'admin.options.confirmdestroy', 'uses' => 'OptionsController@confirmDestroy' ] );
    Route::resource( 'categories', 'CategoriesController' );
    Route::resource( 'pages', 'PagesController' );
    Route::resource( 'posts', 'PostsController' );
    Route::resource( 'tags', 'TagsController' );
    Route::resource( 'users', 'UsersController' );
    Route::resource( 'options', 'OptionsController' );
} );

// Public pages

Route::get( '/{slug}', [ 'as' => 'page.public.show', 'uses' => 'PagesController@getPageBySlug' ] );
