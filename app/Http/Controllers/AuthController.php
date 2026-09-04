<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use App\Models\Log;

class AuthController extends Controller
{
    private const USER_ROLES = [
        'administrative level',
        'super admin',
        'team chandana',
        'team nalaka',
        'report admin',
        'site admin',
        'advertising admin',
    ];

    private function getPriorityLevelForRole(string $role): int
    {
        $r = strtolower(trim($role));

        return match ($r) {
            'administrative level' => 0,
            'super admin' => 1,
            'site admin' => 2,
            'advertising admin' => 3,
            'report admin' => 4,
            'team chandana' => 5,
            'team nalaka' => 6,
            default => 99,
        };
    }

    private function normalizeRoleName(?string $role): string
    {
        $r = strtolower(trim((string) $role));

        return match ($r) {
            'administrative level', 'administrative' => 'administrative level',
            'super admin', 'superadmin', 'super' => 'super admin',
            'team chandana', 'chandana' => 'team chandana',
            'team nalaka', 'nalaka' => 'team nalaka',
            'reporter', 'reporting', 'reportingrole', 'report admin' => 'report admin',
            'site admin' => 'site admin',
            'advertice admin',
            'advertising',
            'advertising role',
            'advertising admin' => 'advertising admin',
            default => 'report admin',
        };
    }

    private function getCurrentUserRole(): string
    {
        return strtolower(trim((string) data_get(session('user'), 'role', '')));
    }

    private function canManageUsers(): bool
    {
        return in_array(
            $this->getCurrentUserRole(),
                ['administrative level', 'super admin'],
                true
            );
    }


    private function getAssignableRoles(): array
    {
        $currentRole = $this->getCurrentUserRole();
        // Administrative Level can create every type of user
            if ($currentRole === 'administrative level') {
            return self::USER_ROLES;
        }

        // Super Admin can create users,
        // but CANNOT create Administrative Level
        if ($currentRole === 'super admin') {
            return array_values(
                array_filter(
                    self::USER_ROLES,
                    fn ($role) => $role !== 'administrative level'
                )
         );
        }

        return [];
    }

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
    * Handle admin registration. Validates passwords, checks for existing email and creates a new admin.
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
        $role = $this->normalizeRoleName('report admin');

        if ($password !== $password_confirmation) {
            return redirect('/register')->with('error', 'Passwords do not match.');
        }

        $existing = DB::select("SELECT * FROM admins WHERE email = ?", [$email]);
        if ($existing) {
            return redirect('/register')->with('error', 'Email already exists.');
        }

        $hashedPassword = Hash::make($password);
        $timestamp = Carbon::now();

        DB::insert("INSERT INTO admins (admin_name, priority_level, email, role, password, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)", [
            $name,
            $this->getPriorityLevelForRole($role),
            $email,
            $role,
            $hashedPassword,
            1,
            $timestamp
        ]);

        $newAdminId = DB::getPdo()->lastInsertId();
        Log::record((int) $newAdminId, 'Registered a new account');

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

        $user = DB::select("SELECT * FROM admins WHERE email = ?", [$email]);

        if (!$user || !Hash::check($password, $user[0]->password)) {
            return redirect('/login')->with('error', 'Invalid credentials.');
        }

        // Store user in session
        Session::put('user', [
            'id' => $user[0]->id,
            'name' => $user[0]->admin_name,
            'email' => $user[0]->email,
            'role' => $this->normalizeRoleName($user[0]->role ?? null),
        ]);

        $loggedInRole = $this->normalizeRoleName($user[0]->role ?? null);
        Log::record((int) $user[0]->id, 'Logged in');

            return match ($loggedInRole) {
            'team chandana' =>
                redirect('/advertisements/lahipita/approved'),
            'team nalaka' =>
                redirect('/advertisements/hitad/approved'),
            default =>
                redirect('/dashboard'),
        };

        // send users to dashboard (reporting users are allowed to view dashboard too)
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
        $adminId = data_get(session('user'), 'id');
        if ($adminId) {
            Log::record((int) $adminId, 'Logged out');
        }

        session()->flush(); // clear all session
        session()->invalidate(); // invalidate session
        session()->regenerateToken(); // security

        return redirect('/login');
    }

    /**
    * Manage admins page and handler. On GET shows admins and optional edit; on POST creates a new admin after validation.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function manageUsers(Request $request)
    {
        
        if (!$this->canManageUsers()) {
            abort(403, 'You do not have permission to manage users.');
        }

        $currentRole = $this->getCurrentUserRole();

        $roles = $this->getAssignableRoles();
        if ($request->isMethod('post')) {
            $name = $request->input('name');
            $email = $request->input('email');
            $role = strtolower(trim((string) $request->input('role')));
            $password = $request->input('password');
            $password_confirmation = $request->input('password_confirmation');

            if ($password !== $password_confirmation) {
                return back()->with('error', 'Passwords do not match.');
            }

            $existing = DB::select("SELECT * FROM admins WHERE email = ?", [$email]);
            if ($existing) {
                return back()->with('error', 'Email already exists.');
            }

            if (!in_array($role, $roles, true)) {
                return back()->with('error','You do not have permission to create this user role.');
            }

            DB::insert("INSERT INTO admins (admin_name, priority_level, email, role, password, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)", [
                $name,
                $this->getPriorityLevelForRole($role),
                $email,
                $role,
                Hash::make($password),
                1,
                Carbon::now()
            ]);

            return redirect('/users')->with('success', 'User created successfully.');
        }

        $query = DB::table('admins')
            ->select(
                'id',
                'admin_name as name',
                'email',
                'role',
                'created_at');
            if ($currentRole === 'super admin') {
            $query->where('role', '!=', 'administrative level');
        }

        if ($request->filled('email')) {
            $query->where('email', 'LIKE', '%' . $request->input('email') . '%');
        }

        $users = $query
            ->orderByDesc('created_at')
            ->get();

        $editUser = null;
        if ($request->filled('edit')) {
            $editUser = DB::table('admins')
                ->select('id', 'admin_name as name', 'email', 'role', 'created_at', 'updated_at')
                ->where('id', $request->query('edit'))
                ->first();
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
        if (!$this->canManageUsers()) {
        abort(403, 'You do not have permission to manage users.');
        }

        $currentRole = $this->getCurrentUserRole();
        $user = DB::table('admins')->where('id', $id)->first();

        if (!$user) {
            return redirect('/users')->with('error', 'User not found.');
        }

        // Super Admin cannot modify Administrative Level accounts
        if (
            $currentRole === 'super admin' &&
            strtolower(trim((string) $user->role)) === 'administrative level'
        ) {
            return redirect('/users')->with('error','Super Admin cannot modify Administrative Level users.');
        }

        $allowedRoles = $this->getAssignableRoles();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('admins', 'email')->ignore($id)],
            'role' => ['required', Rule::in($allowedRoles)],
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $user = DB::table('admins')->where('id', $id)->first();

        if (!$user) {
            return redirect('/users')->with('error', 'User not found.');
        }

        $data = [
            'admin_name' => $request->name,
            'email' => $request->email,
            'role' => $this->normalizeRoleName($request->role),
            'priority_level' => $this->getPriorityLevelForRole($this->normalizeRoleName($request->role)),
            'updated_at' => Carbon::now(),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        DB::table('admins')->where('id', $id)->update($data);

        if (session('user.id') == $id) {
            Session::put('user', [
                'id' => $id,
                'name' => $request->name,
                'email' => $request->email,
                'role' => $this->normalizeRoleName($request->role),
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
        
        if (!$this->canManageUsers()) {
            abort(403, 'You do not have permission to manage users.');
        }

        $currentRole = $this->getCurrentUserRole();
        $user = DB::table('admins')->where('id', $id)->first();

        if (!$user) {
            return redirect('/users')->with('error', 'User not found.');
        }

        // Super Admin cannot delete Administrative Level
        if (
            $currentRole === 'super admin' &&
            strtolower(trim((string) $user->role)) === 'administrative level'
        ) {
            return redirect('/users')->with('error','Super Admin cannot delete Administrative Level users.');
        }
        if (session('user.id') == $id) {
            return redirect('/users')->with('error', 'You cannot delete the currently logged-in user.');
        }

        $deleted = DB::table('admins')->where('id', $id)->delete();

        if (!$deleted) {
            return redirect('/users')->with('error', 'User not found.');
        }

        return redirect('/users')->with('success', 'User deleted successfully.');
    }
}
