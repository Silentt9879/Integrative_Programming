<?php
namespace App\Http\Controllers\Observer\Enhanced;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

// Observer Pattern Imports - Jayvian
use App\Http\Controllers\Observer\Subjects\UserSubject;
use App\Http\Controllers\Observer\Observers\EmailNotificationObserver;
use App\Http\Controllers\Observer\Observers\LoggingObserver;
use App\Http\Controllers\Observer\Observers\AnalyticsObserver;
use App\Http\Controllers\Observer\Observers\AdminNotificationObserver;

class AuthController extends \App\Http\Controllers\Controller
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
    
    public function login(Request $request)
    {
        // Your existing login validation and logic here...
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->only('email', 'remember'));
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();
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

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Welcome back, ' . $user->name . '!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email', 'remember'));
    }
    
    public function register(Request $request)
    {
        // Your existing registration validation...
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|min:1',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20|min:10',
            'date_of_birth' => 'nullable|date|before:-16 years',
            'address' => 'nullable|string|max:1000',
            'password' => [
                'required',
                'confirmed',
                'min:6',
            ],
        ]);

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

            Auth::login($user);
            $request->session()->regenerate();

            return redirect()
                ->route('dashboard')
                ->with('success', 'Welcome to RentWheels, ' . $user->name . '! Your account has been created successfully.');

        } catch (\Exception $e) {
            \Log::error('User registration failed: ' . $e->getMessage());

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
}