<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        //return view('logout');
        return redirect('/');
    }

    public function handleAuthRedirect(): \Illuminate\Http\RedirectResponse
    {
        $user = auth()->user();
        if ($user) {
            return redirect()->route('redirect');
        }
        return redirect()->route('login');
    }

    public function handleRoleRedirect()
    {
        $user = auth()->user();

        if ($user->role()->name == "funcionario") {
            return Inertia::render('Commitments/Index');
        }

        if ($user->role()->name == "administrador") {
            return Inertia::render('Users/Index');
        }

    }

    public function redirectGoogleLogin()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            $email = $googleUser->email;
            $indexOfAtSymbol = stripos($email, "@", 0);
            $unibagueString = strrpos($email, "unibague", $indexOfAtSymbol);

            if ($unibagueString == false) {
                return redirect()->route('login');
            }
        } catch (\Exception $e) {
            return redirect()->route('login');
        }

        $user = User::where('email', $googleUser->email)->first();

        if (!$user) {
            $user = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
                'password' => 'automatic_generate_password'
            ]);

            //Assign the default role (functionary)
            $role = Role::where('name', 'funcionario')->first();
            Role::assignRole($user->id, $role->id);
            session(['role' => $role->id]);
        }

        Auth::login($user);

        if ($user->hasOneRole()) {
            session(['role' => $user->roles[0]->id]);

            return redirect()->route('redirect');
        }

        return redirect()->route('pickRole');
    }

    public function pickRole()
    {
        return Inertia::render('Auth/PickRole');
    }

}
