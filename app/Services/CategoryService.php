<?php


namespace App\Services;

use App\Enums\CategoryStatusEnum;
use App\Models\Category;
use App\Models\Job;
use App\Models\Skill;
use App\Repositories\CategoryRepository;
use App\Transformers\CategoryCollectionTransformer;
use App\Transformers\CategoryTransformer;
use Cache;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

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
    public function attachCategoriesToJobsFromApiResponse($data)
    {
        foreach ($data as $jobData) {
            if(empty($jobData)) continue;
            $node = $jobData['node'];
            $job = Job::where('upwork_id', $node['id'])->first();
            if (empty($job)) continue;
            $categoriesIds = [];
            $lock = Cache::lock('job_service_attach_categories_and_skills_for_job_' . $node['id'], 30);
            if (!$lock->get()) {
                continue;
            }
            if(!empty($node['job']['classification']['category']['id'] ?? false))
            {
                $categoriesIds[] = $node['job']['classification']['category']['id'];
            }
            if(!empty($node['job']['classification']['subCategory']['id'] ?? false))
            {
                $categoriesIds[] = $node['job']['classification']['subCategory']['id'];
            }
            $skillsIds = [];
            foreach($node['job']['classification']['additionalSkills'] ?? [] as $skill)
            {
                $skillsIds[] = $skill['id'];
            }
            foreach($node['job']['classification']['skills'] ?? [] as $skill)
            {
                $skillsIds[] = $skill['id'];
            }
            if(!empty($skillsIds))
            {
                $skills = Skill::whereIn('id',$skillsIds)->get();
                $job->skills()->sync($skills);
            }
            if(!empty($categoriesIds))
            {
                $categories = Category::whereIn('id',$categoriesIds)->get();
                $job->categories()->sync($categories);
            }
            $lock->release();
        }
    }
}

