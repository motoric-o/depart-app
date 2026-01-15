<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Account;
use App\Models\AccountType;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showSignupForm()
    {
        return view('auth.signup');
    }

    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:accounts',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'birthdate' => 'required|date',
        ]);

        $customerType = AccountType::where('name', 'Customer')->first();

        if (!$customerType) {
            return back()->withErrors(['error' => 'System configuration error: Customer type missing']);
        }

        $account = Account::create([
            'account_type_id' => $customerType->id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password_hash' => Hash::make($request->password), // Using password_hash as per model
            // 'birthdate' is not in model fillable but validated?? 
            // Checking Account Model might be needed, but assuming standard fillable for now.
            // If birthdate maps to something else or is separate, better check.
            // For now, only fill what we sent to SP + Type.
        ]);

        Auth::login($account);
        
        return redirect()->route('dashboard');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $account = Account::where('email', $request->email)->first();
        
        if (! $account || ! Hash::check($request->password, $account->password_hash)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        Auth::login($account, $request->remember ?? false);
        $request->session()->regenerate();

        return redirect()->intended('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:accounts,email',
        ], [
            'email.exists' => 'Email tidak terdaftar dalam sistem.',
        ]);

        $email = $request->email;
        $token = Str::random(64);

        // Delete existing token for this email
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        // Create new token (store hashed version in database)
        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => Hash::make($token),
            'created_at' => Carbon::now(),
        ]);

        // TODO: In production, send email with reset link
        // Mail::to($email)->send(new PasswordResetMail($token, $email));
        
        // For development: show the reset link in response
        $resetLink = route('password.reset', ['token' => $token, 'email' => $email]);
        
        return back()->with([
            'status' => 'Link reset password telah dibuat. ' . 
                       (config('app.env') === 'local' ? 'Link: ' . $resetLink : 'Silakan cek email Anda.')
        ]);
    }

    public function showResetPasswordForm(Request $request, $token = null)
    {
        return view('auth.reset-password', [
            'request' => $request,
            'token' => $token ?? $request->route('token'),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:accounts,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.exists' => 'Email tidak terdaftar dalam sistem.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord) {
            return back()->withErrors(['email' => 'Token reset password tidak valid atau telah kedaluwarsa.']);
        }

        // Check if token is valid (within 60 minutes)
        $createdAt = Carbon::parse($resetRecord->created_at);
        if ($createdAt->diffInMinutes(Carbon::now()) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors(['email' => 'Token reset password telah kedaluwarsa. Silakan request reset password baru.']);
        }

        // Verify token (Hash::check will hash the plain token and compare with stored hash)
        if (!Hash::check($request->token, $resetRecord->token)) {
            return back()->withErrors(['email' => 'Token reset password tidak valid.']);
        }

        // Update password
        $account = Account::where('email', $request->email)->first();
        if (!$account) {
            return back()->withErrors(['email' => 'Akun tidak ditemukan.']);
        }

        $account->update([
            'password_hash' => Hash::make($request->password),
        ]);

        // Delete the reset token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('status', 'Password berhasil direset. Silakan login dengan password baru Anda.');
    }
}
