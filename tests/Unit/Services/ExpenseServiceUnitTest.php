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

class ExpenseServiceUnitTest extends TestCase
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

    public function test_validate_expense_data_with_valid_data()
    {
        $validData = [
            'title' => 'Restaurant',
            'amount' => 45.50,
            'description' => 'Déjeuner client',
            'expense_date' => '2024-04-14'
        ];

        // Utiliser la réflexion pour tester la méthode privée
        $reflection = new \ReflectionClass($this->expenseService);
        $method = $reflection->getMethod('validateExpenseData');
        $method->setAccessible(true);

        // Ne devrait pas lancer d'exception
        $this->assertNull($method->invoke($this->expenseService, $validData));
    }

    public function test_validate_expense_data_with_invalid_amount()
    {
        $invalidData = [
            'title' => 'Restaurant',
            'amount' => -10,
            'description' => 'Déjeuner client',
            'expense_date' => '2024-04-14'
        ];

        $reflection = new \ReflectionClass($this->expenseService);
        $method = $reflection->getMethod('validateExpenseData');
        $method->setAccessible(true);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount must be greater than 0');

        $method->invoke($this->expenseService, $invalidData);
    }

    public function test_validate_expense_data_with_empty_title()
    {
        $invalidData = [
            'title' => '',
            'amount' => 45.50,
            'description' => 'Déjeuner client',
            'expense_date' => '2024-04-14'
        ];

        $reflection = new \ReflectionClass($this->expenseService);
        $method = $reflection->getMethod('validateExpenseData');
        $method->setAccessible(true);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Title is required');

        $method->invoke($this->expenseService, $invalidData);
    }

    public function test_validate_payment_data_with_valid_method()
    {
        $validData = [
            'payment_method' => 'mobile_money',
            'reference' => 'TX123456',
            'paid_at' => '2024-04-14 10:30:00'
        ];

        $reflection = new \ReflectionClass($this->expenseService);
        $method = $reflection->getMethod('validatePaymentData');
        $method->setAccessible(true);

        // Ne devrait pas lancer d'exception
        $this->assertNull($method->invoke($this->expenseService, $validData));
    }

    public function test_validate_payment_data_with_invalid_method()
    {
        $invalidData = [
            'payment_method' => 'invalid_method',
            'reference' => 'TX123456'
        ];

        $reflection = new \ReflectionClass($this->expenseService);
        $method = $reflection->getMethod('validatePaymentData');
        $method->setAccessible(true);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid payment method');

        $method->invoke($this->expenseService, $invalidData);
    }

    public function test_store_proof_file_returns_path()
    {
        $file = UploadedFile::fake()->image('receipt.jpg');

        $reflection = new \ReflectionClass($this->expenseService);
        $method = $reflection->getMethod('storeProofFile');
        $method->setAccessible(true);

        $path = $method->invoke($this->expenseService, $file);

        $this->assertIsString($path);
        $this->assertStringStartsWith('expenses/', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_get_user_expenses_calls_repository()
    {
        $userId = 1;
        $filters = ['per_page' => 10];

        $this->expenseRepositoryMock
            ->shouldReceive('getByUserIdPaginated')
            ->once()
            ->with($userId, 10)
            ->andReturn('mocked_expenses');

        $result = $this->expenseService->getUserExpenses($userId, $filters);

        $this->assertArrayHasKey('expenses', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals('mocked_expenses', $result['expenses']);
        $this->assertEquals('User expenses retrieved successfully', $result['message']);
    }

    public function test_get_all_expenses_calls_repository()
    {
        $filters = ['status' => 'PENDING'];

        $this->expenseRepositoryMock
            ->shouldReceive('filter')
            ->once()
            ->with($filters)
            ->andReturn('mocked_expenses');

        $result = $this->expenseService->getAllExpenses($filters);

        $this->assertArrayHasKey('expenses', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals('mocked_expenses', $result['expenses']);
        $this->assertEquals('All expenses retrieved successfully', $result['message']);
    }
}
