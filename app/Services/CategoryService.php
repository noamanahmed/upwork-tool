<?php


namespace App\Services;

use App\Enums\CategoryStatusEnum;
use App\Models\Category;
use App\Models\Skill;
use App\Repositories\CategoryRepository;
use App\Transformers\CategoryCollectionTransformer;
use App\Transformers\CategoryTransformer;

class CategoryService extends BaseService{

    public function __construct(){
        $this->repository = new CategoryRepository();
        $this->transformer = new CategoryTransformer();
        $this->collectionTransformer = new CategoryCollectionTransformer();
        $this->statusMapperEnum = CategoryStatusEnum::class;
    }
    public function insertCategoriesFromApiResponse($data)
    {
        $data = $data[0];
        foreach($data as $categoryData)
        {
            $category = Category::find($categoryData['id']);
            if(!is_null($category)) continue;
            $category = new Category();
            $category->id = $categoryData['id'];
            $category->name = $categoryData['preferredLabel'];
            $category->description = $categoryData['preferredLabel'];
            $category->parent_id = null;
            $category->save();
        }
        foreach($data as $categoryData)
        {
            $parentCategory = Category::find($categoryData['id']);
            if(is_null($parentCategory)) continue;
            foreach($categoryData['subcategories'] as $subCategoryData)
            {
                $subCategory = Category::find($subCategoryData['id']);
                if(!is_null($subCategory)) continue;
                $subCategory = new Category();
                $subCategory->id = $subCategoryData['id'];
                $subCategory->parent_id = $categoryData['id'];
                $subCategory->name = $subCategoryData['preferredLabel'];
                $subCategory->description = $subCategoryData['preferredLabel'];
                $subCategory->save();
            }

        }
    }
    public function insertSkillsFromApiResponse($data)
    {
        $skills = [];
        foreach($data as $skillData)
        {
            $skill = Skill::find($skillData['id']);
            if(!is_null($skill)) continue;
            $skills[] = [
                'id' => $skillData['id'],
                'name' => $skillData['preferredLabel'],
                'description' => $skillData['preferredLabel'],
            ];
            if(count($skills) > 100)
            {
                Skill::insert($skills);
                $skills = [];
            }
        }
        if(count($skills) > 0)
        {
            Skill::insert($skills);
            $skills = [];
        }
    }
}

