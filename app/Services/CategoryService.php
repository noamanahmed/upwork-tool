<?php


namespace App\Services;

use App\Enums\CategoryStatusEnum;
use App\Models\Category;
use App\Models\Job;
use App\Models\JobCategories;
use App\Models\JobSkills;
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
        if(empty($data)) return;
        $locks = [];
        $categories = [];
        $pivotSkills = [];
        $pivotCategories = [];
        foreach ($data as $jobData) {
            if (empty($jobData)) continue;
            $node = $jobData['node'];
            $jobIds[] = $node['id'];
        }
        $alreadyExistingJobs = Job::whereIn('upwork_id',$jobIds)->get()->keyBy('upwork_id');
        $alreadyExistingJobSkills = JobSkills::whereIn('job_id',$alreadyExistingJobs->pluck('id')->toArray())->get()->keyBy('job_id');
        $alreadyExistingJobCategories = JobCategories::whereIn('job_id',$alreadyExistingJobs->pluck('id')->toArray())->get()->keyBy('job_id');
        foreach ($data as $jobData) {
            if(empty($jobData)) continue;
            $node = $jobData['node'];
            $job = $alreadyExistingJobs[$node['id']] ?? null;
            if (empty($job)) continue;
            $categoriesIds = [];
            $lock = Cache::lock('job_service_attach_categories_and_skills_for_job_' . $node['id'], 30);
            if (!$lock->get()) {
                continue;
            }
            if(!empty($node['job']['classification']['category']['id'] ?? false) && empty($alreadyExistingJobCategories[$job->id] ?? null) )
            {
                $categoriesIds[] = $node['job']['classification']['category']['id'];
            }
            if(!empty($node['job']['classification']['subCategory']['id'] ?? false) && empty($alreadyExistingJobCategories[$job->id] ?? null) )
            {
                $categoriesIds[] = $node['job']['classification']['subCategory']['id'];
            }
            $skillsIds = [];
            if(empty($alreadyExistingJobSkills[$job->id] ?? null))
            {
                foreach($node['job']['classification']['additionalSkills'] ?? [] as $skill)
                {
                    $skillsIds[] = $skill['id'];
                }
                foreach($node['job']['classification']['skills'] ?? [] as $skill)
                {
                    $skillsIds[] = $skill['id'];
                }
            }
            foreach($skillsIds as $skillId)
            {
                $pivotSkills[] = [
                    'job_id' => $job->id,
                    'skill_id' => $skillId,
                ];
            }
            foreach($categoriesIds as $categoryId)
            {
                $pivotCategories[] = [
                    'job_id' => $job->id,
                    'category_id' => $categoryId,
                ];
            }
            $locks[] = $lock;
        }
        if(!empty($pivotCategories))
        {
            JobCategories::insert($pivotCategories);
        }

        if(!empty($pivotSkills))
        {
            JobSkills::insert($pivotSkills);
        }
        foreach($locks as $lock)
        {
            $lock->release();
        }
    }
}

