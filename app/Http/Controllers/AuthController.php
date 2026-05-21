<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    private const USER_ROLES = [
        'super admin',
        'Advertice admin',
        'site admin',
        'reporting',
    ];

    /**
     * Show the login page.
     *
     * @return \Illuminate\View\View
     */
    public function showLogin()
    {
        return view('login');
    }

    /**
     * Show the registration page.
     *
     * @return \Illuminate\View\View
     */
    public function showRegister()
    {
        return view('register');
    }

    /**
     * Handle user registration. Validates passwords, checks for existing email and creates a new user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');
        $password_confirmation = $request->input('password_confirmation');
        $role = 'reporting';

        if ($password !== $password_confirmation) {
            return redirect('/register')->with('error', 'Passwords do not match.');
        }

        $existing = DB::select("SELECT * FROM users WHERE email = ?", [$email]);
        if ($existing) {
            return redirect('/register')->with('error', 'Email already exists.');
        }

        $hashedPassword = Hash::make($password);
        $timestamp = Carbon::now();

        DB::insert("INSERT INTO users (name, email, role, password, created_at) VALUES (?, ?, ?, ?, ?)", [
            $name,
            $email,
            $role,
            $hashedPassword,
            $timestamp
        ]);

        return redirect('/login')->with('error', 'Registered successfully. Please login.');
    }

    /**
     * Authenticate user by email and password and store user info in session.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
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
            'email' => $user[0]->email,
            'role' => $user[0]->role ?? null,
        ]);

        return redirect('/dashboard');
    }

    /**
     * Logout the current user by clearing and invalidating the session.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        session()->flush(); // clear all session
        session()->invalidate(); // invalidate session
        session()->regenerateToken(); // security

        return redirect('/login');
    }

    /**
     * Manage users page and handler. On GET shows users and optional edit; on POST creates a new user after validation.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function manageUsers(Request $request)
    {
        if ($request->isMethod('post')) {
            $name = $request->input('name');
            $email = $request->input('email');
            $role = $request->input('role');
            $password = $request->input('password');
            $password_confirmation = $request->input('password_confirmation');

            if ($password !== $password_confirmation) {
                return back()->with('error', 'Passwords do not match.');
            }

            $existing = DB::select("SELECT * FROM users WHERE email = ?", [$email]);
            if ($existing) {
                return back()->with('error', 'Email already exists.');
            }

            if (!in_array($role, self::USER_ROLES, true)) {
                return back()->with('error', 'Please select a valid user role.');
            }

            DB::insert("INSERT INTO users (name, email, role, password, created_at) VALUES (?, ?, ?, ?, ?)", [
                $name,
                $email,
                $role,
                Hash::make($password),
                Carbon::now()
            ]);

            return redirect('/users')->with('success', 'User created successfully.');
        }

        $users = DB::select("SELECT * FROM users ORDER BY created_at DESC");
        $roles = self::USER_ROLES;

        $editUser = null;
        if ($request->filled('edit')) {
            $editUser = DB::table('users')->where('id', $request->query('edit'))->first();
        }

        return view('user', compact('users', 'editUser', 'roles'));
    }

    /**
     * Update an existing user by id. Validates input and optionally updates the password.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateUser(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'role' => ['required', Rule::in(self::USER_ROLES)],
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $user = DB::table('users')->where('id', $id)->first();

        if (!$user) {
            return redirect('/users')->with('error', 'User not found.');
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'updated_at' => Carbon::now(),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        DB::table('users')->where('id', $id)->update($data);

        if (session('user.id') == $id) {
            Session::put('user', [
                'id' => $id,
                'name' => $request->name,
                'email' => $request->email,
                'role' => $request->role,
            ]);
        }

        return redirect('/users')->with('success', 'User updated successfully.');
    }

    /**
     * Delete a user by id (cannot delete the currently logged-in user).
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteUser($id)
    {
        if (session('user.id') == $id) {
            return redirect('/users')->with('error', 'You cannot delete the currently logged-in user.');
        }

        $deleted = DB::table('users')->where('id', $id)->delete();

        if (!$deleted) {
            return redirect('/users')->with('error', 'User not found.');
        }

        return redirect('/users')->with('success', 'User deleted successfully.');
    }
}
