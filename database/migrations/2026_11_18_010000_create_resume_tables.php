<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Resume Profiles (Identity & Personal Info)
        Schema::create('resume_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('title')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('timezone')->nullable();
            $table->unsignedTinyInteger('experience_years')->default(0);
            $table->text('summary')->nullable();
            $table->string('github_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('website_url')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        // 2. Work Experiences
        Schema::create('resume_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->nullable()->constrained('resume_profiles')->cascadeOnDelete();
            $table->string('company');
            $table->string('role');
            $table->string('location')->nullable();
            $table->string('employment_type')->nullable(); // Full-time, Contract, Freelance, Part-time
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->json('highlights')->nullable();
            $table->json('technologies')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        // 3. Educations
        Schema::create('resume_educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->nullable()->constrained('resume_profiles')->cascadeOnDelete();
            $table->string('institution');
            $table->string('degree');
            $table->string('field_of_study')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->string('grade')->nullable();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        // 4. Skills
        Schema::create('resume_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->nullable()->constrained('resume_profiles')->cascadeOnDelete();
            $table->string('name')->index();
            $table->string('category')->default('general')->index(); // backend, frontend, databases, devops, cms, etc.
            $table->string('proficiency')->default('intermediate'); // beginner, intermediate, advanced, expert
            $table->unsignedTinyInteger('years_of_experience')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        // 5. Certifications
        Schema::create('resume_certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->nullable()->constrained('resume_profiles')->cascadeOnDelete();
            $table->string('name');
            $table->string('issuer');
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('credential_id')->nullable();
            $table->string('credential_url')->nullable();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        // 6. Languages
        Schema::create('resume_languages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->nullable()->constrained('resume_profiles')->cascadeOnDelete();
            $table->string('name')->index();
            $table->string('proficiency')->default('Fluent'); // Native, Fluent, Professional, Intermediate, Basic
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        // 7. Hobbies / Interests
        Schema::create('resume_hobbies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->nullable()->constrained('resume_profiles')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        // 8. Projects / Case Studies
        Schema::create('resume_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->nullable()->constrained('resume_profiles')->cascadeOnDelete();
            $table->string('title');
            $table->string('client_name')->nullable();
            $table->text('problem')->nullable();
            $table->text('solution')->nullable();
            $table->text('result')->nullable();
            $table->json('tech_stack')->nullable();
            $table->json('tags')->nullable();
            $table->string('project_url')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        // 9. Achievements
        Schema::create('resume_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->nullable()->constrained('resume_profiles')->cascadeOnDelete();
            $table->string('title');
            $table->string('category')->default('general')->index(); // backend, infrastructure, cms, frontend, general
            $table->string('metric')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resume_achievements');
        Schema::dropIfExists('resume_projects');
        Schema::dropIfExists('resume_hobbies');
        Schema::dropIfExists('resume_languages');
        Schema::dropIfExists('resume_certifications');
        Schema::dropIfExists('resume_skills');
        Schema::dropIfExists('resume_educations');
        Schema::dropIfExists('resume_experiences');
        Schema::dropIfExists('resume_profiles');
    }
};
