<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Log;

// Observer Pattern
use App\Http\Controllers\Observer\Subjects\UserSubject;
use App\Http\Controllers\Observer\Observers\EmailNotificationObserver;
use App\Http\Controllers\Observer\Observers\LoggingObserver;
use App\Http\Controllers\Observer\Observers\AnalyticsObserver;
use App\Http\Controllers\Observer\Observers\AdminNotificationObserver;

class AuthController extends Controller
{
    private UserSubject $userSubject;

    public function __construct()
    {
        // Initialize Observer Pattern
        $this->userSubject = new UserSubject();
        
        // Attach all observers
        $this->userSubject->attach(new EmailNotificationObserver());
        $this->userSubject->attach(new LoggingObserver());
        $this->userSubject->attach(new AnalyticsObserver());
        $this->userSubject->attach(new AdminNotificationObserver());
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * ENHANCED: Handle vehicle booking intent after login WITH OBSERVER PATTERN
     */
    public function login(Request $request)
    {
        // Validate the form data
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->only('email', 'remember'));
        }

        // Get credentials
        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember');

        // Attempt to authenticate
        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();
        
            // CRITICAL: Check if user is admin - redirect them to admin login
            if ($user->is_admin) {
                Auth::logout(); // Logout admin user
                return back()->withErrors([
                    'email' => 'Admin users must use the Admin Login panel. Please use the Admin link in the navigation.'
                ])->withInput($request->only('email'));
            }

            // Check if user is active (for regular users only)
            if ($user->status !== 'active') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Your account has been suspended. Please contact support.'
                ])->withInput($request->only('email'));
            }

            // OBSERVER PATTERN: Notify observers about user login
            $loginData = [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'login_time' => now(),
                'remember_me' => $remember
            ];
            
            $this->userSubject->notifyUserLogin($user, $request->ip(), $loginData);

            // ENSURE first-time flag is NOT set for returning users
            session()->forget('is_first_time_user');

            // Default redirect to dashboard (regular users only)
            return redirect()->intended(route('dashboard'))
                ->with('success', 'Welcome back, ' . $user->name . '!');
        }

        // Authentication failed
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email', 'remember'));
    }

    /**
     * ENHANCED: Handle vehicle booking intent after registration WITH OBSERVER PATTERN
     */
    public function register(Request $request)
    {
        // Much simpler and more user-friendly validation
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|min:1',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20|min:10',
            'date_of_birth' => 'nullable|date|before:-16 years', // Must be at least 16 years old
            'address' => 'nullable|string|max:1000',
            'password' => [
                'required',
                'confirmed',
                'min:6', // Reduced from 8 to 6 characters
                // Removed complex requirements - just basic length
            ],
        ], [
            // Only essential, helpful error messages
            'name.required' => 'Please tell us your name.',
            'email.required' => 'We need your email address to create your account.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'Looks like you already have an account! Try logging in instead.',
            'phone.min' => 'Phone number seems too short. Please check and try again.',
            'date_of_birth.before' => 'You must be at least 16 years old to register.',
            'password.required' => 'Please create a password to secure your account.',
            'password.confirmed' => 'Password confirmation doesn\'t match. Please try again.',
            'password.min' => 'Password should be at least 6 characters long.'
        ]);

        // Simplified additional validations
        $validator->after(function ($validator) use ($request) {
            // More flexible phone validation - just check if it contains enough digits
            if ($request->phone) {
                $digitsOnly = preg_replace('/\D/', '', $request->phone);
                if (strlen($digitsOnly) < 10) {
                    $validator->errors()->add('phone', 'Please enter a valid phone number with at least 10 digits.');
                }
            }
        });

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->except('password', 'password_confirmation'));
        }

        try {
            // Create the user
            $user = User::create([
                'name' => trim($request->name),
                'email' => strtolower(trim($request->email)),
                'phone' => $request->phone ? trim($request->phone) : null,
                'date_of_birth' => $request->date_of_birth,
                'address' => $request->address ? trim($request->address) : null,
                'password' => Hash::make($request->password),
                'status' => 'active'
            ]);

            // OBSERVER PATTERN: Notify observers about new user registration
            $registrationData = [
                'source' => 'web_registration',
                'registration_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'has_phone' => !empty($user->phone),
                'has_address' => !empty($user->address),
                'has_date_of_birth' => !empty($user->date_of_birth),
                'registration_time' => now()
            ];

            $this->userSubject->notifyUserRegistered($user, $registrationData);

            // Log the user in automatically
            Auth::login($user);

            // Regenerate session
            $request->session()->regenerate();

            // SET FLAG for first-time registration
            session(['is_first_time_user' => true]);

            // Default redirect to dashboard
            return redirect()
                ->route('dashboard')
                ->with('success', 'Welcome to RentWheels, ' . $user->name . '! Your account has been created successfully.');

        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('User registration failed: ' . $e->getMessage());

            return back()
                ->withErrors(['general' => 'Oops! Something went wrong. Please try again in a moment.'])
                ->withInput($request->except('password', 'password_confirmation'));
        }
    }

    public function logout(Request $request)
    {
        $userName = Auth::user()->name ?? 'User';

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Goodbye ' . $userName . '! You have been logged out successfully.');
    }

    /**
     * Check if email is available (AJAX endpoint)
     */
    public function checkEmail(Request $request)
    {
        $email = $request->input('email');
        $exists = User::where('email', strtolower(trim($email)))->exists();

        return response()->json([
            'available' => !$exists,
            'message' => $exists ? 'This email is already registered.' : 'Email is available.'
        ]);
    }
}