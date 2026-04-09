<?php

namespace App\Http\Livewire;

use App\Jobs\SendOtpJob;
use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;

class Login extends Component
{
    public $username = '';
    public $otp = '';
    public $phone = '';
    public $email = '';
    public $otpSent = false;
    public $isLoading = false;
    public $isEmailLogin = false;
    public $isMobileLogin = false;

    /**
     * Detect if username is email
     */
    public function updatedUsername($value)
    {
        $this->reset(['otpSent', 'otp', 'isEmailLogin', 'isMobileLogin']);

        if (empty($value)) {
            return;
        }

        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->isEmailLogin = true;
            $this->isMobileLogin = false;
        } else {
            $this->isEmailLogin = false;
            $this->isMobileLogin = false;
        }
    }

    /**
     * Send OTP for login (both email and mobile)
     */
    public function sendOtp()
    {
        $this->isLoading = true;

        try {
            // Validate input
            if ($this->isMobileLogin) {
                $validated = $this->validate([
                    'username' => 'required|digits:10',
                ], [
                    'username.required' => 'Mobile number is required.',
                    'username.digits' => 'Mobile number must be exactly 10 digits.',
                ]);

                // $validated = $this->validate([
                //     'username' => [
                //         'required',
                //         'digits:10',
                //         Rule::exists('users', 'user_phone')->where(function ($query) {
                //             $query->where('block', 2);
                //         }),
                //     ],
                // ], [
                //      'username.required' => 'Mobile number is required.',
                //     'username.digits' => 'Mobile number must be exactly 10 digits.',

                // ]);
            } elseif ($this->isEmailLogin) {
                $validated = $this->validate([
                    'username' => 'required|email',
                ], [
                    'username.required' => 'Email is required.',
                    'username.email' => 'Please enter a valid email address.',
                ]);

                // $validated = $this->validate([
                //     'username' => [
                //         'required',
                //         'email',
                //         Rule::exists('users', 'email')->where(function ($query) {
                //             $query->where('block', 2);
                //         }),
                //     ],
                // ], [
                //     'username.required' => 'Email is required.',
                //     'username.email' => 'Please enter a valid email address.',
                // ]);
            } else {
                session()->flash('error', 'Please enter a valid email address.');
                $this->isLoading = false;
                return;
            }

            // Find user
            $user = null;
            if ($this->isMobileLogin) {
                $user = User::where('user_phone', $this->username)->first();
                $this->phone = $this->username;
            } else {
                $user = User::where('email', $this->username)->first();
                $this->email = $this->username;
            }

            // dd($user);
            if ($user && $user->block == 1) {

                session()->flash('error', 'Your account is blocked. Please contact support.');
                return response()->json([
                    'status'  => false,
                    'message' => 'Your account is blocked. Please contact support.',
                ], 403);
            }

            if (!$user) {
                // session()->flash('error', "Account doesn't exist! Please register first.");
                // $this->isLoading = false;
                // return;
                // OTP verified - create user account
                $user = User::create([
                    'name' => '',
                    'email' => $this->email,
                    'user_phone' => $this->phone,
                    'reg_date' => Carbon::now(),
                    'password' => Hash::make(uniqid()), // Temporary password
                ]);
            }

            // Rate limiting: max 3 OTP requests per hour
            $key = 'login-otp-attempt:' . $this->username;

            if (RateLimiter::tooManyAttempts($key, 3)) {
                $seconds = RateLimiter::availableIn($key);
                session()->flash('error', 'Too many OTP requests. Please try again in ' . ceil($seconds / 60) . ' minutes.');
                $this->isLoading = false;
                return;
            }

            // Generate cryptographically secure OTP
            $otp = $this->generateSecureOtp();

            // Hash OTP for secure storage
            $hashedOtp = Hash::make($otp);

            // Store OTP temporarily in cache
            $otpData = [
                'user_id' => $user->id,
                'username' => $this->username,
                'otp_hash' => $hashedOtp,
                'otp_expires_at' => Carbon::now()->addMinutes(10),
                'otp_attempts' => 0
            ];

            cache()->put('login_otp:' . $this->username, $otpData, 600); // 10 minutes

            $user_data = [
                'email' => $user->email,
                'mobile_no' => $user->user_phone,
                'name' => $user->name,
                'otp' => $otp,
                'is_email' => false
            ];
            // Send OTP
            if ($this->isMobileLogin) {
                // $this->sendOTPSMS($user->user_phone, $otp);
                // SendOtpJob::dispatch($user_data);
                session()->flash('success', 'OTP sent successfully to your mobile number.');
            } else {


                $user_data['is_email'] = true;
                SendOtpJob::dispatch($user_data);
                // $this->sendOtpEmail($user->email, $user->name, $otp);
                session()->flash('success', 'OTP sent successfully to your email.11');
            }

            // Increment rate limiter
            RateLimiter::hit($key, 3600); // 1 hour decay

            $this->otpSent = true;

            // Log for security monitoring
            Log::info('Login OTP sent', [
                'username' => $this->username,
                'otp' => $otp,
                'type' => $this->isMobileLogin ? 'mobile' : 'email',
                'ip' => request()->ip()
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->isLoading = false;
            throw $e;
        } catch (\Exception $e) {
            Log::error('Login OTP sending failed', [
                'error' => $e->getMessage(),
                'username' => $this->username
            ]);

            session()->flash('error', 'Failed to send OTP. Please try again later.');
        } finally {
            $this->isLoading = false;
        }
    }

    /**
     * Verify OTP and login user
     */
    public function loginWithOtp()
    {
        $this->isLoading = true;

        try {
            $this->validate([
                'otp' => 'required|digits:6'
            ], [
                'otp.required' => 'Please enter the OTP.',
                'otp.digits' => 'OTP must be 6 digits.'
            ]);

            // Retrieve stored OTP data
            $otpData = cache()->get('login_otp:' . $this->username);

            if (!$otpData) {
                session()->flash('error', 'OTP expired or invalid. Please request a new one.');
                $this->resetForm();
                $this->isLoading = false;
                return;
            }

            // Check expiration
            if (Carbon::now()->greaterThan($otpData['otp_expires_at'])) {
                cache()->forget('login_otp:' . $this->username);
                session()->flash('error', 'OTP has expired. Please request a new one.');
                $this->resetForm();
                $this->isLoading = false;
                return;
            }

            // Check attempt limit (max 3 wrong attempts)
            if ($otpData['otp_attempts'] >= 3) {
                cache()->forget('login_otp:' . $this->username);
                session()->flash('error', 'Too many failed attempts. Please request a new OTP.');
                $this->resetForm();
                $this->isLoading = false;
                return;
            }

            // Master OTP for testing (remove in production)
            $isMasterOtp = ($this->otp == '123456' && $this->username == '7979068408');

            // Verify OTP
            if (!Hash::check($this->otp, $otpData['otp_hash']) && !$isMasterOtp) {
                $otpData['otp_attempts']++;
                cache()->put('login_otp:' . $this->username, $otpData, 600);

                $remainingAttempts = 3 - $otpData['otp_attempts'];
                session()->flash('error', "Invalid OTP. {$remainingAttempts} attempts remaining.");
                $this->isLoading = false;
                return;
            }

            // Get user
            $user = User::find($otpData['user_id']);

            if (!$user) {
                session()->flash('error', "User not found. Please register.");
                $this->resetForm();
                $this->isLoading = false;
                return;
            }

            // Clear OTP data
            cache()->forget('login_otp:' . $this->username);

            // Clear rate limiter
            RateLimiter::clear('login-otp-attempt:' . $this->username);

            // Login user
            Auth::login($user);

            // Merge session cart to database
            $this->mergeSessionCartToDatabase();

            session()->flash('success', 'Login successful! Welcome back.');

            // Log successful login
            Log::info('User logged in successfully', [
                'user_id' => $user->id,
                'username' => $this->username
            ]);

            // Redirect to cart
            // return redirect()->route('getCartItems');
            return redirect()->route('index');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->isLoading = false;
            throw $e;
        } catch (\Exception $e) {
            Log::error('Login failed', [
                'error' => $e->getMessage(),
                'username' => $this->username
            ]);

            session()->flash('error', 'Login failed. Please try again.');
        } finally {
            $this->isLoading = false;
        }
    }

    /**
     * Resend OTP
     */
    /**
     * Resend OTP
     */
    public function resendOtp()
    {
        try {
            // Validate input first
            if ($this->isMobileLogin) {
                $this->validate([
                    'username' => 'required|digits:10',
                ]);
            } elseif ($this->isEmailLogin) {
                $this->validate([
                    'username' => 'required|email',
                ]);
            }

            // Clear existing OTP data
            cache()->forget('login_otp:' . $this->username);

            // Clear rate limiter to allow resend
            RateLimiter::clear('login-otp-attempt:' . $this->username);

            // Find user
            $user = null;
            if ($this->isMobileLogin) {
                $user = User::where('user_phone', $this->username)->first();
            } else {
                $user = User::where('email', $this->username)->first();
            }

            if (!$user) {
                session()->flash('error', "Account doesn't exist! Please register first.");
                return;
            }

            // Generate cryptographically secure OTP
            $otp = $this->generateSecureOtp();

            // Hash OTP for secure storage
            $hashedOtp = Hash::make($otp);

            // Store OTP temporarily in cache
            $otpData = [
                'user_id' => $user->id,
                'username' => $this->username,
                'otp_hash' => $hashedOtp,
                'otp_expires_at' => Carbon::now()->addMinutes(10),
                'otp_attempts' => 0
            ];

            cache()->put('login_otp:' . $this->username, $otpData, 600);

            $user_data = [
                'email' => $user->email,
                'mobile_no' => $user->user_phone,
                'name' => $user->name,
                'otp' => $otp,
                'is_email' => $this->isEmailLogin ? true : false
            ];
            // dd($user_data);
            // Dispatch job
            SendOtpJob::dispatch($user_data);

            // Success message
            if ($this->isMobileLogin) {
                session()->flash('success', 'OTP resent successfully to your mobile number.');
            } else {
                session()->flash('success', 'OTP resent successfully to your email.');
            }

            // Log for security monitoring
            Log::info('Login OTP resent', [
                'username' => $this->username,
                'type' => $this->isMobileLogin ? 'mobile' : 'email',
                'ip' => request()->ip()
            ]);

            // Force Livewire to acknowledge completion
            $this->dispatch('otp-resent');
        } catch (\Exception $e) {
            Log::error('Resend OTP failed', [
                'error' => $e->getMessage(),
                'username' => $this->username
            ]);

            session()->flash('error', 'Failed to resend OTP. Please try again later.');
        }
    }

    /**
     * Reset form to initial state
     */
    public function resetForm()
    {
        $this->otpSent = false;
        $this->otp = '';
        $this->isLoading = false;
    }

    /**
     * Generate cryptographically secure 6-digit OTP
     */
    private function generateSecureOtp(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Send OTP via email
     */
    private function sendOtpEmail(string $email, string $name, string $otp): void
    {
        try {
            \Mail::send('frontend.mail.email-otp', [
                'name' => $name,
                'otp' => $otp,
                'expiryMinutes' => 10
            ], function ($message) use ($email, $name) {
                $message->to($email, $name)
                    ->subject("Login OTP - Amma's Spices");
            });
        } catch (\Exception $e) {
            Log::error('Email OTP sending failed', [
                'error' => $e->getMessage(),
                'email' => $email
            ]);
        }
    }

    /**
     * Merge session cart to database
     */
    public function mergeSessionCartToDatabase()
    {
        $sessionCart = session()->get('cart', []);
        $sessionWishlist = session()->get('wishlist', []);
        $userId = Auth::id();

        // Merge cart items
        foreach ($sessionCart as $key => $item) {
            $dbCartItem = Cart::where('product_id', $item['product_id'])
                ->where('variation_id', $item['variation_id'])
                ->where('user_id', $userId)
                ->first();

            if ($dbCartItem) {
                $dbCartItem->quantity += $item['quantity'];
                $dbCartItem->save();
            } else {
                Cart::create([
                    'product_id' => $item['product_id'],
                    'variation_id' => $item['variation_id'],
                    'user_id' => $userId,
                    'quantity' => $item['quantity'],
                ]);
            }
        }

        // Merge wishlist items
        foreach ($sessionWishlist as $key => $item) {
            $getProduct = Product::find($item['product_id']);

            $dbWishListItem = Wishlist::where('product_id', $item['product_id'])
                ->where('user_id', $userId)
                ->first();

            if ($dbWishListItem) {
                $dbWishListItem->quantity += 1;
                $dbWishListItem->save();
            } else {
                $mrp = $getProduct->variation->mrp ?? $getProduct->base_mrp;
                $price = $getProduct->variation->price ?? $getProduct->base_price;

                Wishlist::create([
                    'product_id' => $item['product_id'],
                    'user_id' => $userId,
                    'quantity' => 1,
                    'price' => $price,
                    'mrp' => $mrp,
                    'product_image' => $getProduct->product_image,
                    'product_name' => $getProduct->product_name,
                    'description' => $getProduct->description,
                    'store_id' => 1,
                ]);
            }
        }
    }

    /**
     * Send OTP via SMS
     */
    private function sendOTPSMS($mobile, $otp)
    {
        try {
            $smsParams = [
                'user' => 'zakh',
                'password' => 'zakh2025',
                'senderid' => 'ZAKHEC',
                'channel' => 'Trans',
                'DCS' => 0,
                'flashsms' => 0,
                'number' => '91' . $mobile,
                'text' => "Your OTP to login to your ZAKHEC account is {$otp}. Please do not share this code with anyone for security reasons.",
                'Peid' => '1701175853936286545',
                'DLTTemplateId' => '1707175992012497279'
            ];

            // $response = Http::get('http://www.admagister.net/api/mt/SendSMS', $smsParams);
            //Log::info('OTP SMS sent successfully', ['response' => $response->body()]);
            // Http::withToken('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJjbGllbnRJZCI6Inpha2hfYm90IiwiZW1haWwiOiJ6YWtoQG1haWxpbmF0b3IuY29tIiwidGltZXN0YW1wIjoiMjAyNS0xMC0zMVQwNjozNTozMS4zNTJaIiwiY2hhbm5lbCI6IndoYXRzYXBwIiwiaWF0IjoxNzYxODkyNTMxfQ.gmwdMS60eECJ9Mdx3VUq9KvMMnBZXQET2U6PVpfDHeI')
            //     ->post('https://api.helloyubo.com/v3/whatsapp/notification', [
            //         "clientId" => "zakh_bot",
            //         "channel" => "whatsapp",
            //         "send_to" => "91$mobile",
            //         "templateName" => "log_in_otp",
            //         "parameters" => ["$otp"],
            //         "msg_type" => "TEXT",
            //         "header" => "",
            //         "footer" => "",
            //         "buttonUrlParam" => $otp,
            //         "button" => "true",
            //         "media_url" => "",
            //         "lang" => "en_US",
            //         "msg" => null,
            //         "userName" => null,
            //         "parametersArrayObj" => null,
            //         "headerParam" => null
            //     ]);

            // if ($response->successful()) {
            //     Log::info('OTP SMS sent successfully', ['mobile' => $mobile]);
            //     return true;
            // } else {
            //     Log::error('OTP SMS sending failed', ['mobile' => $mobile, 'response' => $response->body()]);
            //     return false;
            // }
        } catch (\Exception $e) {
            Log::error('OTP SMS API Error: ' . $e->getMessage(), ['mobile' => $mobile]);
            return false;
        }
    }

    /**
     * Send welcome SMS (kept for registration flow if needed)
     */
    private function sendWelcomeSMS($mobile, $name)
    {
        try {
            $smsParams = [
                'user' => 'zakh',
                'password' => 'zakh2025',
                'senderid' => 'ZAKHEC',
                'channel' => 'Trans',
                'DCS' => 0,
                'flashsms' => 0,
                'number' => '91' . $mobile,
                'text' => "Welcome to ZAKH, {$name}! Your account has been created successfully. Start exploring and enjoy your shopping experience with us! -ZAKHEC",
                'Peid' => '1701175853936286545',
                'DLTTemplateId' => '1707176034724804046'
            ];

            $response = Http::get('http://www.admagister.net/api/mt/SendSMS', $smsParams);

            if ($response->successful()) {
                Log::info('Welcome SMS sent successfully', ['mobile' => $mobile]);
                return true;
            } else {
                Log::error('Welcome SMS sending failed', ['mobile' => $mobile]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Welcome SMS API Error: ' . $e->getMessage(), ['mobile' => $mobile]);
            return false;
        }
    }

    public function render()
    {
        return view('livewire.login');
    }
}
