<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        // Force email validation to fail by adding an invalid email format
        //$request->merge(['email' => 'not-an-email']);
        
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'min:8', 'max:32'],
        ], [
            'name.required' => 'required',
            'email.required' => 'required',
            'email.unique' => 'email-unique',
            'password.required' => 'required',
            'password.min' => 'password-short',
            'password.max' => 'password-short'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        // Auth::login($user);

        return response()->json([
            'code' => 'SUCCESS',
            'data' => [
                'user' => auth()->user()
            ]
        ]);
    }
}
