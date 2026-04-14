<?php

namespace Tests\Unit\Services;

use App\Interfaces\AuthRepositoryInterface;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Mockery;

class AuthServiceTest extends TestCase
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

    public function test_register_creates_user_with_token()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => 'employee'
        ];

        $user = new User([
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'employee'
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
        $this->assertIsString($result['token']);
    }

    public function test_register_with_default_role()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $user = new User([
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'employee'
        ]);

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

    public function test_login_returns_user_with_token()
    {
        $credentials = [
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $user = new User([
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'employee'
        ]);

        Auth::shouldReceive('attempt')
            ->once()
            ->with($credentials)
            ->andReturn(true);

        $userMock = Mockery::mock(User::class);
        $userMock->id = 1;
        $userMock->name = 'Test User';
        $userMock->email = 'test@example.com';
        $userMock->role = 'employee';

        Auth::shouldReceive('user')
            ->once()
            ->andReturn($userMock);

        $this->authRepositoryMock
            ->shouldReceive('updateLastLogin')
            ->once()
            ->with($userMock->id)
            ->andReturn(true);

        $userMock->shouldReceive('createToken')
            ->once()
            ->with('auth_token')
            ->andReturn((object)['plainTextToken' => 'test-token']);

        $result = $this->authService->login($credentials);

        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertEquals($userMock, $result['user']);
        $this->assertIsString($result['token']);
    }

    public function test_login_with_invalid_credentials_throws_exception()
    {
        $credentials = [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ];

        Auth::shouldReceive('attempt')
            ->once()
            ->with($credentials)
            ->andReturn(false);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid credentials');

        $this->authService->login($credentials);
    }

    public function test_logout_revokes_token()
    {
        $user = Mockery::mock(User::class);
        $user->id = 1;
        
        Auth::shouldReceive('user')
            ->once()
            ->andReturn($user);

        $tokenMock = Mockery::mock();
        $tokenMock->shouldReceive('delete')->once();

        $user->shouldReceive('currentAccessToken')
            ->once()
            ->andReturn($tokenMock);

        $this->authService->logout();

        // Si nous arrivons ici sans exception, le test est réussi
        $this->assertTrue(true);
    }
}
