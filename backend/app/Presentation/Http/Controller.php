<?php

namespace App\Presentation\Http;

use Illuminate\Support\Str;

abstract class Controller extends \Illuminate\Routing\Controller
{
    use \App\Infrastructure\Persistence\Eloquent\Traits\ApiResponse;

    public function __construct()
    {
        // Closure middleware runs before any controller method
        $this->middleware(function ($request, $next) {
            $uuid = $request->route('uuid'); // check if route has {uuid}

            if ($uuid && !Str::isUuid($uuid)) {
                return $this->error('Invalid UUID format.', 400);
            }

            return $next($request);
        });
    }
}
