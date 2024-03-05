<?php

namespace App\Http\Controllers\Api\V1 ;

use App\Http\Requests\StoreUpWorkRequest;
use App\Http\Requests\UpdateUpWorkRequest;
use App\Models\UpWork;
use App\Services\UpWorkService;
use Request;

class UpWorkController extends BaseController
{
    public function __construct(
        private UpWorkService $upworkService
    ){}

    public function init(Request $request)
    {
        return $this->upworkService->init();
    }
    public function code(Request $request)
    {
        return $this->upworkService->code();
    }
    public function jobs(Request $request)
    {
        return $this->upworkService->jobs();
    }
}
