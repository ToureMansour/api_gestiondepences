<?php

namespace Tests\Unit\Services;

use App\Interfaces\AuthRepositoryInterface;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Mockery;

class SimpleAuthServiceTest extends TestCase
{
    protected AuthService $authService;
    protected $authRepositoryMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->authRepositoryMock = Mockery::mock(AuthRepositoryInterface::class);
        $this->authService = new AuthService($this->authRepositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_register_creates_user_with_hashed_password()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => 'employee'
        ];

        $user = User::factory()->make([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'role' => $userData['role']
        ]);

        $this->authRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) use ($userData) {
                return $data['name'] === $userData['name'] &&
                       $data['email'] === $userData['email'] &&
                       $data['role'] === $userData['role'] &&
                       Hash::check($userData['password'], $data['password']);
            }))
            ->andReturn($user);

        $result = $this->authService->register($userData);

        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertEquals($user, $result['user']);
    }

    public function test_register_uses_default_role_when_not_provided()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $user = User::factory()->make(['role' => 'employee']);

        $this->authRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) {
                return $data['role'] === 'employee';
            }))
            ->andReturn($user);

        $result = $this->authService->register($userData);

        $this->assertEquals($user, $result['user']);
    }

    public function test_register_with_admin_role()
    {
        $userData = [
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'role' => 'admin'
        ];

        $user = User::factory()->make(['role' => 'admin']);

        $this->authRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) {
                return $data['role'] === 'admin';
            }))
            ->andReturn($user);

        $result = $this->authService->register($userData);

        $this->assertEquals($user, $result['user']);
    }
}
