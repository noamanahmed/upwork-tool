<?php

namespace Database\Seeders;

use App\Models\ResumeAchievement;
use App\Models\ResumeCertification;
use App\Models\ResumeEducation;
use App\Models\ResumeExperience;
use App\Models\ResumeHobby;
use App\Models\ResumeLanguage;
use App\Models\ResumeProfile;
use App\Models\ResumeProject;
use App\Models\ResumeSkill;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ResumeSeeder extends Seeder
{
    public function run(): void
    {
        $config = config('admin', []);

        // 1. Profile
        $personal = $config['personal'] ?? [];
        $intro = is_array($config['introduction'] ?? null)
            ? implode("\n\n", $config['introduction'])
            : ($config['introduction'] ?? null);

        $profile = ResumeProfile::firstOrCreate(
            [
                'first_name' => $personal['first_name'] ?? 'Nauman',
                'last_name' => $personal['last_name'] ?? 'Ahmed',
            ],
            [
                'title' => $personal['title'] ?? 'Senior Full Stack Developer / Architect',
                'city' => $personal['city'] ?? 'Lahore',
                'state' => $personal['state'] ?? 'Punjab',
                'country' => $personal['country'] ?? 'Pakistan',
                'timezone' => $personal['timezone'] ?? 'Asia/Karachi',
                'experience_years' => $personal['experience_years'] ?? 8,
                'summary' => $intro,
                'is_active' => true,
            ]
        );

        // 2. Skills
        $skillsData = $config['skills'] ?? [];
        $sortOrder = 0;

        foreach ($skillsData as $category => $items) {
            if ($category === 'infrastructure' && is_array($items)) {
                foreach ($items as $subCat => $subItems) {
                    foreach ((array) $subItems as $skillName) {
                        ResumeSkill::firstOrCreate(
                            [
                                'profile_id' => $profile->id,
                                'name' => $skillName,
                            ],
                            [
                                'category' => 'infrastructure',
                                'proficiency' => 'advanced',
                                'sort_order' => $sortOrder++,
                                'is_active' => true,
                            ]
                        );
                    }
                }
                continue;
            }

            foreach ((array) $items as $skillName) {
                ResumeSkill::firstOrCreate(
                    [
                        'profile_id' => $profile->id,
                        'name' => $skillName,
                    ],
                    [
                        'category' => (string) $category,
                        'proficiency' => in_array($category, ['backend', 'databases', 'cms']) ? 'expert' : 'advanced',
                        'is_featured' => in_array($skillName, ['Laravel', 'Vue.js', 'MySQL', 'AWS', 'WordPress', 'Redis']),
                        'sort_order' => $sortOrder++,
                        'is_active' => true,
                    ]
                );
            }
        }

        // 3. Experiences
        $experiences = $config['experiences'] ?? [];
        foreach ($experiences as $i => $exp) {
            ResumeExperience::firstOrCreate(
                [
                    'profile_id' => $profile->id,
                    'company' => $exp['company'],
                    'role' => $exp['role'],
                ],
                [
                    'start_date' => !empty($exp['start_date']) ? Carbon::parse($exp['start_date']) : null,
                    'end_date' => !empty($exp['end_date']) ? Carbon::parse($exp['end_date']) : null,
                    'is_current' => empty($exp['end_date']),
                    'highlights' => $exp['highlights'] ?? [],
                    'sort_order' => $i,
                    'is_active' => true,
                ]
            );
        }

        // 4. Certifications
        $certifications = $config['certifications'] ?? [];
        foreach ($certifications as $i => $cert) {
            $year = $cert['year'] ?? 2023;
            ResumeCertification::firstOrCreate(
                [
                    'profile_id' => $profile->id,
                    'name' => $cert['name'],
                    'issuer' => $cert['issuer'],
                ],
                [
                    'issue_date' => Carbon::createFromDate($year, 1, 1),
                    'sort_order' => $i,
                    'is_active' => true,
                ]
            );
        }

        // 5. Case Studies / Projects
        $caseStudies = $config['case_studies'] ?? [];
        foreach ($caseStudies as $i => $cs) {
            ResumeProject::firstOrCreate(
                [
                    'profile_id' => $profile->id,
                    'title' => $cs['title'],
                ],
                [
                    'problem' => $cs['problem'] ?? null,
                    'solution' => $cs['solution'] ?? null,
                    'result' => $cs['result'] ?? null,
                    'tech_stack' => $cs['tech_stack'] ?? [],
                    'tags' => $cs['tags'] ?? [],
                    'sort_order' => $i,
                    'is_active' => true,
                ]
            );
        }

        // 6. Achievements
        $achievements = $config['achievements'] ?? [];
        $achSort = 0;
        foreach ($achievements as $category => $items) {
            foreach ((array) $items as $achievementText) {
                ResumeAchievement::firstOrCreate(
                    [
                        'profile_id' => $profile->id,
                        'title' => $achievementText,
                    ],
                    [
                        'category' => (string) $category,
                        'sort_order' => $achSort++,
                        'is_active' => true,
                    ]
                );
            }
        }

        // 7. Languages
        $languages = $config['languages'] ?? [];
        $langSort = 0;
        foreach ($languages as $langName => $proficiency) {
            ResumeLanguage::firstOrCreate(
                [
                    'profile_id' => $profile->id,
                    'name' => $langName,
                ],
                [
                    'proficiency' => (string) $proficiency,
                    'sort_order' => $langSort++,
                    'is_active' => true,
                ]
            );
        }

        // 8. Hobbies
        $hobbies = $config['hobbies'] ?? [];
        foreach ($hobbies as $i => $hobbyName) {
            ResumeHobby::firstOrCreate(
                [
                    'profile_id' => $profile->id,
                    'name' => $hobbyName,
                ],
                [
                    'sort_order' => $i,
                    'is_active' => true,
                ]
            );
        }
    }
}
