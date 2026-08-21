<?php

use App\Services\DbSampleService;
use Illuminate\Support\Facades\Route;
use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use App\Services\SampleService;

Route::get('/metrics', function (CollectorRegistry $registry) {
    $renderer = new RenderTextFormat();
    return response($renderer->render($registry->getMetricFamilySamples()))
        ->header('Content-Type', RenderTextFormat::MIME_TYPE);
});

Route::get('/', function () {
    sleep(random_int(1, 5));
    return view('welcome');
});

Route::get('/sample-error', function () {
    abort(500);
});

Route::get('/posts', function (SampleService $service) {
    return $service->getPosts();
});

Route::get('/posts/{id}', function (SampleService $service) {
    return $service->getPosts();
});

Route::get('/delay',function (){
    sleep(random_int(1,5));
    return 'done';
});

Route::get('/users', function (DbSampleService $service) {
    return $service->listUsers();
});
