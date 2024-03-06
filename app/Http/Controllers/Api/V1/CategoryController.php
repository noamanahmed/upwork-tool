<?php

namespace App\Http\Controllers\Api\V1 ;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;

class CategoryController extends BaseController
{
    public function __construct(
        private CategoryService $categoryService
    ){}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->categoryService->index();
    }



    /**
     * Display a listing of the resource.
     */
    public function dropdown()
    {
        return $this->categoryService->dropdown();
    }


    /**
     * Display a listing of the resource.
     */
    public function dropdownForStatus()
    {
        return $this->categoryService->dropdownForStatus();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
        return $this->categoryService->store($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        return $this->categoryService->get($category->id);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        return $this->categoryService->update($category->id,$request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        return $this->categoryService->delete($category->id);
    }
}
