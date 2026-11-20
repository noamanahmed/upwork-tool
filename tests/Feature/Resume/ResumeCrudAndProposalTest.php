<?php

use App\Models\AiJobProposal;
use App\Models\ResumeAchievement;
use App\Models\ResumeCertification;
use App\Models\ResumeEducation;
use App\Models\ResumeExperience;
use App\Models\ResumeHobby;
use App\Models\ResumeLanguage;
use App\Models\ResumeProfile;
use App\Models\ResumeProject;
use App\Models\ResumeSkill;
use App\Services\Template\MustacheEngine;
use Database\Seeders\ResumeSeeder;

beforeEach(function () {
    $this->artisan('migrate:fresh');
});

test('resume models have is_active default to true and associate correctly', function () {
    $profile = ResumeProfile::create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'title' => 'Staff Engineer',
        'city' => 'San Francisco',
        'country' => 'USA',
        'experience_years' => 10,
    ]);

    expect($profile->is_active)->toBeTrue();
    expect($profile->full_name)->toBe('John Doe');

    // Experience
    $exp = ResumeExperience::create([
        'profile_id' => $profile->id,
        'company' => 'Acme Corp',
        'role' => 'Principal Architect',
        'is_current' => true,
        'highlights' => ['Scaled systems to 1M users'],
        'technologies' => ['Laravel', 'Redis', 'Docker'],
    ]);
    expect($exp->is_active)->toBeTrue();

    // Education
    $edu = ResumeEducation::create([
        'profile_id' => $profile->id,
        'institution' => 'MIT',
        'degree' => 'B.S.',
        'field_of_study' => 'Computer Science',
    ]);
    expect($edu->is_active)->toBeTrue();

    // Skill
    $skill = ResumeSkill::create([
        'profile_id' => $profile->id,
        'name' => 'Laravel',
        'category' => 'backend',
        'proficiency' => 'expert',
        'is_featured' => true,
    ]);
    expect($skill->is_active)->toBeTrue();

    // Certification
    $cert = ResumeCertification::create([
        'profile_id' => $profile->id,
        'name' => 'AWS Solutions Architect',
        'issuer' => 'AWS',
    ]);
    expect($cert->is_active)->toBeTrue();

    // Language
    $lang = ResumeLanguage::create([
        'profile_id' => $profile->id,
        'name' => 'English',
        'proficiency' => 'Native',
    ]);
    expect($lang->is_active)->toBeTrue();

    // Hobby
    $hobby = ResumeHobby::create([
        'profile_id' => $profile->id,
        'name' => 'Chess',
    ]);
    expect($hobby->is_active)->toBeTrue();

    // Project
    $proj = ResumeProject::create([
        'profile_id' => $profile->id,
        'title' => 'High-Throughput Microservice',
        'result' => 'Handled 50k req/sec',
        'tags' => ['microservices', 'laravel', 'redis'],
    ]);
    expect($proj->is_active)->toBeTrue();

    // Achievement
    $ach = ResumeAchievement::create([
        'profile_id' => $profile->id,
        'title' => 'Reduced server costs by 60%',
        'category' => 'infrastructure',
    ]);
    expect($ach->is_active)->toBeTrue();

    // Test profile relationships
    expect($profile->experiences)->toHaveCount(1);
    expect($profile->educations)->toHaveCount(1);
    expect($profile->skills)->toHaveCount(1);
    expect($profile->certifications)->toHaveCount(1);
    expect($profile->languages)->toHaveCount(1);
    expect($profile->hobbies)->toHaveCount(1);
    expect($profile->projects)->toHaveCount(1);
    expect($profile->achievements)->toHaveCount(1);
});

test('resume seeder populates tables from config admin', function () {
    $this->seed(ResumeSeeder::class);

    expect(ResumeProfile::count())->toBeGreaterThanOrEqual(1);
    expect(ResumeSkill::count())->toBeGreaterThan(10);
    expect(ResumeExperience::count())->toBeGreaterThan(2);
    expect(ResumeProject::count())->toBeGreaterThan(2);
    expect(ResumeAchievement::count())->toBeGreaterThan(2);
    expect(ResumeLanguage::count())->toBeGreaterThanOrEqual(3);
    expect(ResumeHobby::count())->toBeGreaterThanOrEqual(3);

    $profile = ResumeProfile::first();
    expect($profile->first_name)->toBe('Nauman');
    expect($profile->last_name)->toBe('Ahmed');
    expect($profile->is_active)->toBeTrue();
});

test('ai proposal embeds resume tables data into dynamic context and mustache template', function () {
    $this->seed(ResumeSeeder::class);

    $job = upworkTestJob(6001);
    $job->update([
        'title' => 'Need an expert Laravel and AWS developer for microservices architecture',
        'description' => 'Looking for senior backend engineer with deep experience in Laravel, Redis, and HIPAA healthcare platform.',
    ]);

    $proposal = new AiJobProposal([
        'job_id' => $job->id,
        'provider' => 'gemini',
    ]);
    $proposal->setRelation('job', $job);

    $context = $proposal->buildTemplateContext();

    expect($context['freelancer']['name'])->toBe('Nauman Ahmed');
    expect($context['freelancer']['title'])->toContain('Full Stack Developer');
    expect($context['freelancer']['skills'])->toContain('Laravel');
    expect($context['freelancer']['experience'])->not->toBeEmpty();
    expect($context['freelancer']['certifications'])->toContain('AWS Certified Developer');
    expect($context['freelancer']['languages'])->toContain('English');
    expect($context['freelancer']['hobbies'])->toContain('Football');

    // Test Mustache rendering of the embedded CV fields
    $template = <<<'EOT'
Candidate: {{ freelancer.name }}
Title: {{ freelancer.title }}
Matched Skills: {{ freelancer.skills }}
Experience: {{ freelancer.experience }}
Certifications: {{ freelancer.certifications }}
Languages: {{ freelancer.languages }}
Case Study:
{{ freelancer.case_study }}
EOT;

    $rendered = MustacheEngine::render($template, $context);

    expect($rendered)
        ->toContain('Candidate: Nauman Ahmed')
        ->toContain('Matched Skills: Laravel')
        ->toContain('AWS Certified Developer')
        ->toContain('Relevant Past Work:');
});

test('inactive resume items are excluded from proposal context', function () {
    $this->seed(ResumeSeeder::class);

    // Deactivate Laravel skill
    ResumeSkill::where('name', 'Laravel')->update(['is_active' => false]);

    $job = upworkTestJob(6002);
    $job->update([
        'title' => 'Laravel expert needed',
        'description' => 'Need Laravel only.',
    ]);

    $proposal = new AiJobProposal([
        'job_id' => $job->id,
        'provider' => 'gemini',
    ]);
    $proposal->setRelation('job', $job);

    $context = $proposal->buildTemplateContext();

    // Inactive Laravel skill should not be included
    $matchedSkills = explode(', ', $context['freelancer']['skills']);
    expect($matchedSkills)->not->toContain('Laravel');
});

test('resume resource forms construct without unresolvable closure exceptions', function () {
    $this->seed(ResumeSeeder::class);

    $resources = [
        \App\Filament\Resources\ResumeAchievementResource::class,
        \App\Filament\Resources\ResumeCertificationResource::class,
        \App\Filament\Resources\ResumeEducationResource::class,
        \App\Filament\Resources\ResumeExperienceResource::class,
        \App\Filament\Resources\ResumeHobbyResource::class,
        \App\Filament\Resources\ResumeLanguageResource::class,
        \App\Filament\Resources\ResumeProjectResource::class,
        \App\Filament\Resources\ResumeSkillResource::class,
    ];

    foreach ($resources as $resource) {
        $form = $resource::form(new \Filament\Forms\Form(new class extends \Filament\Pages\Page {}));
        expect($form->getComponents())->not->toBeEmpty();
    }
});
