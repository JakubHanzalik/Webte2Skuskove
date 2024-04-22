<?php
use Pecee\SimpleRouter\SimpleRouter;

SimpleRouter::group(['prefix' => '/api'], function () {

    SimpleRouter::get('/login', function () {
        return 'Hello world';
    });
});
