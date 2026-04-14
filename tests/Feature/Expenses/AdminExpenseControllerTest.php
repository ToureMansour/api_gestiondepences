<?php

namespace Tests\Feature\Expenses;

use App\Models\User;
use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminExpenseControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $employee;
    protected string $adminToken;
    protected string $employeeToken;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->employee = User::factory()->create(['role' => 'employee']);
        
        $this->adminToken = $this->admin->createToken('admin-token')->plainTextToken;
        $this->employeeToken = $this->employee->createToken('employee-token')->plainTextToken;
    }

    public function test_admin_can_approve_expense()
    {
        $expense = Expense::factory()->create([
            'user_id' => $this->employee->id,
            'status' => 'PENDING'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken
        ])->postJson("/api/expenses/{$expense->id}/approve");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'id',
                        'status',
                        'user'
                    ]
                ]);

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'status' => 'APPROVED'
        ]);
    }

    public function test_employee_cannot_approve_expense()
    {
        $expense = Expense::factory()->create([
            'user_id' => $this->employee->id,
            'status' => 'PENDING'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->employeeToken
        ])->postJson("/api/expenses/{$expense->id}/approve");

        $response->assertStatus(403);
    }

    public function test_admin_can_reject_expense()
    {
        $expense = Expense::factory()->create([
            'user_id' => $this->employee->id,
            'status' => 'PENDING'
        ]);

        $rejectData = [
            'reason' => 'Dépense non conforme à la politique'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken
        ])->postJson("/api/expenses/{$expense->id}/reject", $rejectData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'id',
                        'status',
                        'rejection_reason'
                    ]
                ]);

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'status' => 'REJECTED',
            'rejection_reason' => $rejectData['reason']
        ]);
    }

    public function test_admin_cannot_reject_expense_without_reason()
    {
        $expense = Expense::factory()->create([
            'user_id' => $this->employee->id,
            'status' => 'PENDING'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken
        ])->postJson("/api/expenses/{$expense->id}/reject", []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['reason']);
    }

    public function test_admin_can_mark_expense_as_paid()
    {
        $expense = Expense::factory()->create([
            'user_id' => $this->employee->id,
            'status' => 'APPROVED'
        ]);

        $paymentData = [
            'payment_method' => 'mobile_money',
            'reference' => 'TX123456',
            'paid_at' => '2024-04-14 10:30:00'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken
        ])->postJson("/api/expenses/{$expense->id}/pay", $paymentData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'id',
                        'status',
                        'payment_method',
                        'payment_reference',
                        'paid_at'
                    ]
                ]);

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'status' => 'PAID',
            'payment_method' => $paymentData['payment_method'],
            'payment_reference' => $paymentData['reference']
        ]);
    }

    public function test_admin_cannot_mark_pending_expense_as_paid()
    {
        $expense = Expense::factory()->create([
            'user_id' => $this->employee->id,
            'status' => 'PENDING'
        ]);

        $paymentData = [
            'payment_method' => 'mobile_money',
            'reference' => 'TX123456'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken
        ])->postJson("/api/expenses/{$expense->id}/pay", $paymentData);

        $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                    'message' => 'Cannot mark as paid expense with status: PENDING'
                ]);
    }

    public function test_admin_cannot_approve_already_approved_expense()
    {
        $expense = Expense::factory()->create([
            'user_id' => $this->employee->id,
            'status' => 'APPROVED'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken
        ])->postJson("/api/expenses/{$expense->id}/approve");

        $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                    'message' => 'Cannot approve expense with status: APPROVED'
                ]);
    }

    public function test_admin_cannot_reject_already_rejected_expense()
    {
        $expense = Expense::factory()->create([
            'user_id' => $this->employee->id,
            'status' => 'REJECTED'
        ]);

        $rejectData = ['reason' => 'Another reason'];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken
        ])->postJson("/api/expenses/{$expense->id}/reject", $rejectData);

        $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                    'message' => 'Cannot reject expense with status: REJECTED'
                ]);
    }

    public function test_unauthenticated_user_cannot_access_admin_endpoints()
    {
        $expense = Expense::factory()->create();

        $response = $this->postJson("/api/expenses/{$expense->id}/approve");
        $response->assertStatus(401);

        $response = $this->postJson("/api/expenses/{$expense->id}/reject");
        $response->assertStatus(401);

        $response = $this->postJson("/api/expenses/{$expense->id}/pay");
        $response->assertStatus(401);
    }
}
