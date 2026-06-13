<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Hash;

abstract class TestCase extends BaseTestCase
{
    protected function createUser(string $role): User
    {
        return User::create([
            'name' => ucfirst($role).' User',
            'email' => "{$role}@example.com",
            'password' => Hash::make('password'),
            'role' => $role,
        ]);
    }
}
