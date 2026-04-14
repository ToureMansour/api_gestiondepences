<?php

namespace Tests\Feature\Expenses;

use App\Models\User;
use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExpenseControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;
    protected string $userToken;
    protected string $adminToken;

    protected function setUp(): void
    {
        parent::setUp();
        
        Storage::fake('public');
        
        $this->user = User::factory()->create(['role' => 'employee']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        
        $this->userToken = $this->user->createToken('test-token')->plainTextToken;
        $this->adminToken = $this->admin->createToken('test-token')->plainTextToken;
    }

    public function test_user_can_create_expense()
    {
        $expenseData = [
            'title' => 'Restaurant',
            'amount' => 45.50,
            'description' => 'Déjeuner client',
            'expense_date' => '2024-04-14'
        ];

        $file = UploadedFile::fake()->image('receipt.jpg');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken
        ])->postJson('/api/expenses', array_merge($expenseData, ['proof' => $file]));

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'id',
                        'title',
                        'amount',
                        'description',
                        'status',
                        'user' => [
                            'id',
                            'name',
                            'email'
                        ]
                    ]
                ]);

        $this->assertDatabaseHas('expenses', [
            'title' => $expenseData['title'],
            'amount' => $expenseData['amount'],
            'user_id' => $this->user->id,
            'status' => 'PENDING'
        ]);
    }

    public function test_user_cannot_create_expense_without_authentication()
    {
        $expenseData = [
            'title' => 'Restaurant',
            'amount' => 45.50,
            'expense_date' => '2024-04-14'
        ];

        $response = $this->postJson('/api/expenses', $expenseData);

        $response->assertStatus(401);
    }

    public function test_user_can_view_own_expenses()
    {
        $expense = Expense::factory()->create(['user_id' => $this->user->id]);
        Expense::factory()->create(); // Expense for another user

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken
        ])->getJson('/api/expenses');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'title',
                                'amount',
                                'status',
                                'user'
                            ]
                        ],
                        'current_page',
                        'last_page',
                        'per_page',
                        'total'
                    ]
                ]);

        // Should only see own expenses
        $this->assertEquals(1, $response->json('data.total'));
    }

    public function test_admin_can_view_all_expenses()
    {
        Expense::factory()->create(['user_id' => $this->user->id]);
        Expense::factory()->create(['user_id' => $this->admin->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken
        ])->getJson('/api/expenses');

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('data.total'));
    }

    public function test_user_can_view_own_expense()
    {
        $expense = Expense::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken
        ])->getJson("/api/expenses/{$expense->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'id',
                        'title',
                        'amount',
                        'status',
                        'user'
                    ]
                ]);
    }

    public function test_user_cannot_view_other_user_expense()
    {
        $otherUser = User::factory()->create();
        $expense = Expense::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken
        ])->getJson("/api/expenses/{$expense->id}");

        $response->assertStatus(403);
    }

    public function test_user_can_update_pending_expense()
    {
        $expense = Expense::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'PENDING'
        ]);

        $updateData = [
            'title' => 'Restaurant Modifié',
            'amount' => 50.00
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken
        ])->putJson("/api/expenses/{$expense->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'id',
                        'title',
                        'amount'
                    ]
                ]);

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'title' => $updateData['title'],
            'amount' => $updateData['amount']
        ]);
    }

    public function test_user_cannot_update_approved_expense()
    {
        $expense = Expense::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'APPROVED'
        ]);

        $updateData = ['title' => 'Restaurant Modifié'];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken
        ])->putJson("/api/expenses/{$expense->id}", $updateData);

        $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                    'message' => 'Cannot update expense with status: APPROVED'
                ]);
    }

    public function test_user_can_cancel_pending_expense()
    {
        $expense = Expense::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'PENDING'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken
        ])->deleteJson("/api/expenses/{$expense->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'id',
                        'status'
                    ]
                ]);

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'status' => 'CANCELLED'
        ]);
    }

    public function test_create_expense_validation_errors()
    {
        $invalidData = [
            'title' => '',
            'amount' => -10,
            'expense_date' => 'invalid-date'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->userToken
        ])->postJson('/api/expenses', $invalidData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['title', 'amount', 'expense_date', 'proof']);
    }
}
