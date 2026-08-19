<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\ProductionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_the_real_employee_dataset_idempotently(): void
    {
        $this->seed(ProductionSeeder::class);

        $this->assertSame(5, Company::count());
        $this->assertSame(1782, Employee::count());
        $this->assertSame(438, Employee::where('status', 'active')->count());
        $this->assertSame(1344, Employee::where('status', 'resigned')->count());
        $this->assertSame(0, User::count());

        $this->assertDatabaseHas('companies', [
            'code' => 'MSI',
            'name' => 'PT Media Selular Indonesia',
        ]);

        $this->assertDatabaseHas('employees', [
            'nip' => '2014.01.08.01',
            'full_name' => 'JEJEN MUTAKHIR',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('employees', [
            'nip' => '2016.03.21.01',
            'full_name' => 'RIAN OKTORA',
            'status' => 'resigned',
        ]);

        $activeEmployee = Employee::query()
            ->with('division.parent.parent')
            ->where('nip', '2014.01.08.01')
            ->firstOrFail();

        $this->assertSame('DEPO CIREBON', $activeEmployee->division->name);
        $this->assertSame('REALME', $activeEmployee->division->parent?->name);
        $this->assertSame('DISTRIBUTION SALES', $activeEmployee->division->parent?->parent?->name);

        $this->seed(ProductionSeeder::class);

        $this->assertSame(5, Company::count());
        $this->assertSame(1782, Employee::count());
    }
}
