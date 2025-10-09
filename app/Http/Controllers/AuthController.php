<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('login');
    }

    public function showRegister()
    {
        return view('register');
    }

    public function register(Request $request)
    {
        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');
        $password_confirmation = $request->input('password_confirmation');

        if ($password !== $password_confirmation) {
            return redirect('/register')->with('error', 'Passwords do not match.');
        }

        $existing = DB::select("SELECT * FROM users WHERE email = ?", [$email]);
        if ($existing) {
            return redirect('/register')->with('error', 'Email already exists.');
        }

        $hashedPassword = Hash::make($password);
        $timestamp = Carbon::now();

        DB::insert("INSERT INTO users (name, email, password, created_at) VALUES (?, ?, ?, ?)", [
            $name,
            $email,
            $hashedPassword,
            $timestamp
        ]);

        return redirect('/login')->with('error', 'Registered successfully. Please login.');
    }

    public function login(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');

        $user = DB::select("SELECT * FROM users WHERE email = ?", [$email]);

        if (!$user || !Hash::check($password, $user[0]->password)) {
            return redirect('/login')->with('error', 'Invalid credentials.');
        }

        // Store user in session
        Session::put('user', [
            'id' => $user[0]->id,
            'name' => $user[0]->name,
            'email' => $user[0]->email
        ]);

        return redirect('/dashboard');
    }

    public function logout()
    {
        Session::forget('user');
        return redirect('/login')->with('error', 'Logged out successfully.');
    }

    public function manageUsers(Request $request)
    {
        if ($request->isMethod('post')) {
            $name = $request->input('name');
            $email = $request->input('email');
            $password = $request->input('password');
            $password_confirmation = $request->input('password_confirmation');

            if ($password !== $password_confirmation) {
                return back()->with('error', 'Passwords do not match.');
            }

            $existing = DB::select("SELECT * FROM users WHERE email = ?", [$email]);
            if ($existing) {
                return back()->with('error', 'Email already exists.');
            }

            DB::insert("INSERT INTO users (name, email, password, created_at) VALUES (?, ?, ?, ?)", [
                $name,
                $email,
                Hash::make($password),
                Carbon::now()
            ]);

            return redirect('/users')->with('success', 'User created successfully.');
        }

        $users = DB::select("SELECT * FROM users ORDER BY created_at DESC");
        return view('user', ['users' => $users]);
    }
}
