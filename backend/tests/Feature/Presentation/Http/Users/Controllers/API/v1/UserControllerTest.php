<?php

use App\Presentation\Http\Users\Controllers\API\V1\UserController as V1UserController;

it('user controller exists', function () {
    expect(class_exists(V1UserController::class))->toBeTrue();
});
