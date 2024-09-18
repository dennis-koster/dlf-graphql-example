<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResetUserPassword
{
    public function __invoke($_, array $args): string
    {
        $user     = User::findOrFail($args['id']);
        $password = $args['password'] ?? Str::random(8);

        $user->update([
            'password' => Hash::make($password),
        ]);

        // Logic for sending an email to the user here

        return "Wachtwoord is gereset naar {$password}.";
    }
}
