<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Quotation;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        // $this->call(LeadSeeder::class);
        // $this->call(QuotationSeeder::class);
        // $this->call(LanguageSeeder::class);
        // $this->call(TranslationSeeder::class);
        // $this->call(PermissionSeeder::class);

        $this->call(RoleSeeder::class);
        $this->call(RolePermissionSeeder::class);
        $this->call(UserSeeder::class);

        // $this->call(LocationSeeder::class);
        // $this->call(ContractSeeder::class);
        // $this->call(CompanySeeder::class);
        // $this->call(EmployerSeeder::class);
        // $this->call(EmployeeSeeder::class);
        // $this->call(SkillSeeder::class);
        // $this->call(CategorySeeder::class);
        // $this->call(ProductSeeder::class);
        // $this->call(CustomerSeeder::class);
        // $this->call(OrderSeeder::class);
        // $this->call(TaskSeeder::class);
        $this->call(JobSearchSeeder::class);
    }
}
