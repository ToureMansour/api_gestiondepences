<?php

namespace Tests\Unit\Services;

use App\Interfaces\ExpenseRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Models\Expense;
use App\Models\User;
use App\Services\ExpenseService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Mockery;

class ExpenseServiceTest extends TestCase
{
    protected ExpenseService $expenseService;
    protected $expenseRepositoryMock;
    protected $userRepositoryMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->expenseRepositoryMock = Mockery::mock(ExpenseRepositoryInterface::class);
        $this->userRepositoryMock = Mockery::mock(UserRepositoryInterface::class);
        $this->expenseService = new ExpenseService(
            $this->expenseRepositoryMock,
            $this->userRepositoryMock
        );

        Storage::fake('public');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_expense_success()
    {
        $expenseData = [
            'title' => 'Restaurant',
            'amount' => 45.50,
            'description' => 'Déjeuner client',
            'expense_date' => '2024-04-14'
        ];

        $userId = 1;
        $file = UploadedFile::fake()->image('receipt.jpg');

        $user = new User([
            'id' => $userId,
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        $expense = new Expense([
            'id' => 1,
            'title' => $expenseData['title'],
            'amount' => $expenseData['amount'],
            'status' => 'PENDING'
        ]);

        $this->userRepositoryMock
            ->shouldReceive('findById')
            ->once()
            ->with($userId)
            ->andReturn($user);

        $this->expenseRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) use ($expenseData, $userId) {
                return $data['title'] === $expenseData['title'] &&
                       $data['amount'] === $expenseData['amount'] &&
                       $data['user_id'] === $userId &&
                       $data['status'] === 'PENDING' &&
                       isset($data['proof_file_path']);
            }))
            ->andReturn($expense);

        $result = $this->expenseService->createExpense($expenseData, $file, $userId);

        $this->assertArrayHasKey('expense', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals('Expense created successfully', $result['message']);
        $this->assertEquals($expense, $result['expense']);
    }

    public function test_create_expense_with_invalid_amount_throws_exception()
    {
        $expenseData = [
            'title' => 'Restaurant',
            'amount' => -10,
            'description' => 'Déjeuner client',
            'expense_date' => '2024-04-14'
        ];

        $userId = 1;
        $file = UploadedFile::fake()->image('receipt.jpg');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount must be greater than 0');

        $this->expenseService->createExpense($expenseData, $file, $userId);
    }

    public function test_update_expense_success()
    {
        $expenseId = 1;
        $userId = 1;
        $updateData = [
            'title' => 'Restaurant Modifié',
            'amount' => 50.00
        ];

        $expense = new Expense([
            'id' => $expenseId,
            'user_id' => $userId,
            'title' => 'Restaurant',
            'amount' => 45.50,
            'status' => 'PENDING'
        ]);

        $updatedExpense = new Expense([
            'id' => $expenseId,
            'user_id' => $userId,
            'title' => $updateData['title'],
            'amount' => $updateData['amount'],
            'status' => 'PENDING'
        ]);

        $this->expenseRepositoryMock
            ->shouldReceive('findById')
            ->once()
            ->with($expenseId)
            ->andReturn($expense);

        $this->expenseRepositoryMock
            ->shouldReceive('update')
            ->once()
            ->with($expenseId, $updateData)
            ->andReturn($updatedExpense);

        $result = $this->expenseService->updateExpense($expenseId, $updateData, $userId);

        $this->assertArrayHasKey('expense', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals('Expense updated successfully', $result['message']);
        $this->assertEquals($updatedExpense, $result['expense']);
    }

    public function test_update_expense_not_pending_throws_exception()
    {
        $expenseId = 1;
        $userId = 1;
        $updateData = ['title' => 'Restaurant Modifié'];

        $expense = new Expense([
            'id' => $expenseId,
            'user_id' => $userId,
            'status' => 'APPROVED'
        ]);

        $this->expenseRepositoryMock
            ->shouldReceive('findById')
            ->once()
            ->with($expenseId)
            ->andReturn($expense);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot update expense with status: APPROVED');

        $this->expenseService->updateExpense($expenseId, $updateData, $userId);
    }

    public function test_approve_expense_success()
    {
        $expenseId = 1;
        $expense = new Expense([
            'id' => $expenseId,
            'status' => 'PENDING'
        ]);

        $approvedExpense = new Expense([
            'id' => $expenseId,
            'status' => 'APPROVED'
        ]);

        $this->expenseRepositoryMock
            ->shouldReceive('findById')
            ->once()
            ->with($expenseId)
            ->andReturn($expense);

        $this->expenseRepositoryMock
            ->shouldReceive('update')
            ->once()
            ->with($expenseId, ['status' => 'APPROVED'])
            ->andReturn($approvedExpense);

        $result = $this->expenseService->approveExpense($expenseId);

        $this->assertArrayHasKey('expense', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals('Expense approved successfully', $result['message']);
        $this->assertEquals('APPROVED', $result['expense']->status);
    }

    public function test_reject_expense_success()
    {
        $expenseId = 1;
        $reason = 'Dépense non justifiée';
        $expense = new Expense([
            'id' => $expenseId,
            'status' => 'PENDING'
        ]);

        $rejectedExpense = new Expense([
            'id' => $expenseId,
            'status' => 'REJECTED',
            'rejection_reason' => $reason
        ]);

        $this->expenseRepositoryMock
            ->shouldReceive('findById')
            ->once()
            ->with($expenseId)
            ->andReturn($expense);

        $this->expenseRepositoryMock
            ->shouldReceive('update')
            ->once()
            ->with($expenseId, [
                'status' => 'REJECTED',
                'rejection_reason' => $reason
            ])
            ->andReturn($rejectedExpense);

        $result = $this->expenseService->rejectExpense($expenseId, $reason);

        $this->assertArrayHasKey('expense', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals('Expense rejected successfully', $result['message']);
        $this->assertEquals('REJECTED', $result['expense']->status);
        $this->assertEquals($reason, $result['expense']->rejection_reason);
    }

    public function test_reject_expense_without_reason_throws_exception()
    {
        $expenseId = 1;
        $reason = '';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Rejection reason is required');

        $this->expenseService->rejectExpense($expenseId, $reason);
    }
}
