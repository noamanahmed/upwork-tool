<?php

namespace App\Http\Controllers\Api\V1 ;

use App\Http\Requests\StoreLanguageRequest;
use App\Http\Requests\UpdateLanguageRequest;
use App\Models\Language;
use App\Services\LanguageService;

class LanguageController extends BaseController
{
    public function __construct(
        private LanguageService $languageService
    ){}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->languageService->index();
    }



    /**
     * Display a listing of the resource.
     */
    public function dropdown()
    {
        return $this->languageService->dropdown();
    }

    /**
     * Display the specified resource.
     */
    public function show(Language $language)
    {
        return $this->languageService->get($language->id);
    }

}

