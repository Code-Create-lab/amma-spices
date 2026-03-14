<?php

namespace App\Http\Livewire;

use App\Mail\WelcomeMail;
use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Illuminate\Support\Facades\RateLimiter;


class Register extends Component
{

    public $user_phone;
    public $name;
    public $email;
    public $otp;
    public $mobile_otp;
    public $email_otp;
    public $password;
    public $password_confirmation;
    public $privacy = false;
    public $otpSent = false;
    public $isLoading = false; // Added for manual loading control


    public function mount()
    {
        // dd(auth()->user());
    }

    /**
     * Send OTP for registration
     */
    public function sendOtp()
    {
        $this->isLoading = true;

        try {
            // Validate input including privacy checkbox
            $validated = $this->validate([
                'name' => 'required|string|max:255',
                'user_phone' => 'required|digits:10|unique:users,user_phone',
                'email' => 'required|email|max:255|unique:users,email',
                'privacy' => 'accepted',
            ], [
                'name.required' => 'Name is required.',
                'user_phone.required' => 'Mobile number is required.',
                'user_phone.digits' => 'Mobile number must be exactly 10 digits.',
                'user_phone.unique' => 'This mobile number is already registered.',
                'email.required' => 'Email is required.',
                'email.email' => 'Please enter a valid email address.',
                'email.unique' => 'This email is already registered.',
                'privacy.accepted' => 'You must agree to the privacy policy.',
            ]);

            // Rate limiting: max 3 OTP requests per hour per email/mobile
            $key = 'otp-attempt:' . $this->email;

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

            // Store OTP temporarily
            $otpData = [
                'name' => $this->name,
                'user_phone' => $this->user_phone,
                'email' => $this->email,
                'otp_hash' => $hashedOtp,
                'otp_expires_at' => Carbon::now()->addMinutes(10),
                'otp_attempts' => 0
            ];

            // Store in cache (10 minutes)
            cache()->put('otp_registration:' . $this->email, $otpData, 600);

            // Send OTP via Email
            $this->sendOtpEmail($this->email, $this->name, $otp);

            // Optional: Send OTP via SMS
            // $this->sendOtpSms($this->user_phone, $otp);

            // Increment rate limiter
            RateLimiter::hit($key, 3600); // 1 hour decay

            // Update UI state
            $this->otpSent = true;

            session()->flash('success', 'OTP sent successfully to your email. Valid for 10 minutes.');

            // Log for security monitoring
            \Log::info('OTP sent for registration', [
                'email' => $this->email,
                'mobile' => $this->user_phone,
                'ip' => request()->ip()
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Re-throw validation exceptions so Livewire can handle them
            $this->isLoading = false;
            throw $e;
        } catch (\Exception $e) {
            \Log::error('OTP sending failed', [
                'error' => $e->getMessage(),
                'email' => $this->email
            ]);

            session()->flash('error', 'Failed to send OTP. Please try again later.');
        } finally {
            $this->isLoading = false;
        }
    }

    /**
     * Generate cryptographically secure 6-digit OTP
     */
    private function generateSecureOtp(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Send OTP via email using Mailable
     */
    private function sendOtpEmail(string $email, string $name, string $otp): void
    {
        Mail::send('frontend.mail.email-otp', [
            'name' => $name,
            'otp' => $otp,
            'expiryMinutes' => 10
        ], function ($message) use ($email, $name) {
            $message->to($email, $name)
                ->subject("Verify Your Email - Amma's Spices");
        });
    }

    /**
     * Send OTP via SMS (implement based on your SMS provider)
     */
    private function sendOtpSms(string $mobile, string $otp): void
    {
        // Example implementation - adjust based on your SMS provider
        /*
        $message = "Your Amma's Spices verification code is: {$otp}. Valid for 10 minutes. Do not share this code.";
        
        // Twilio example:
        $twilio = new \Twilio\Rest\Client(config('services.twilio.sid'), config('services.twilio.token'));
        $twilio->messages->create(
            "+91{$mobile}",
            [
                'from' => config('services.twilio.phone'),
                'body' => $message
            ]
        );
        */
    }

    /**
     * Verify OTP and create user account
     */
    public function verifyOtpAndRegister()
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
            $otpData = cache()->get('otp_registration:' . $this->email);

            if (!$otpData) {
                session()->flash('error', 'OTP expired or invalid. Please request a new one.');
                $this->resetForm();
                $this->isLoading = false;
                return;
            }

            // Check expiration
            if (Carbon::now()->greaterThan($otpData['otp_expires_at'])) {
                cache()->forget('otp_registration:' . $this->email);
                session()->flash('error', 'OTP has expired. Please request a new one.');
                $this->resetForm();
                $this->isLoading = false;
                return;
            }

            // Check attempt limit (max 3 wrong attempts)
            if ($otpData['otp_attempts'] >= 3) {
                cache()->forget('otp_registration:' . $this->email);
                session()->flash('error', 'Too many failed attempts. Please request a new OTP.');
                $this->resetForm();
                $this->isLoading = false;
                return;
            }

            // Verify OTP
            if (!Hash::check($this->otp, $otpData['otp_hash'])) {
                $otpData['otp_attempts']++;
                cache()->put('otp_registration:' . $this->email, $otpData, 600);

                $remainingAttempts = 3 - $otpData['otp_attempts'];
                session()->flash('error', "Invalid OTP. {$remainingAttempts} attempts remaining.");
                $this->isLoading = false;
                return;
            }

            // OTP verified - create user account
            $user = User::create([
                'name' => $otpData['name'],
                'email' => $otpData['email'],
                'user_phone' => $otpData['user_phone'],
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'password' => Hash::make(uniqid()), // Temporary password
            ]);

            // Clear OTP data
            cache()->forget('otp_registration:' . $this->email);

            // Clear rate limiter
            RateLimiter::clear('otp-attempt:' . $this->email);

            // Log the user in
            auth()->login($user);

            session()->flash('success', "Registration successful! Welcome to Amma's Spices.");

            // Log successful registration
            \Log::info('User registered successfully', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            // Redirect to home or dashboard
            return redirect()->route('index');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->isLoading = false;
            throw $e;
        } catch (\Exception $e) {
            \Log::error('User registration failed', [
                'error' => $e->getMessage(),
                'email' => $this->email
            ]);

            session()->flash('error', 'Registration failed. Please try again.');
        } finally {
            $this->isLoading = false;
        }
    }

    /**
     * Resend OTP
     */
    public function resendOtp()
    {
        // Clear any existing OTP data
        cache()->forget('otp_registration:' . $this->email);

        // Clear rate limiter to allow resend
        RateLimiter::clear('otp-attempt:' . $this->email);

        // Send new OTP
        $this->sendOtp();
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

    public function register()
    {

        $this->validate([
            'name' => 'required',
            'email' => 'required|email',
            'user_phone' => 'required|digits:10',
            // 'password' => 'required|min:3|confirmed',            // using confirmed rule
            // 'password_confirmation' => 'required',
            'privacy' => 'required',
        ], [
            'password.confirmed' => 'Passwords do not match. Please re-enter your password to confirm.',
            'password_confirmation.required' => 'Please confirm your password.',
        ]);
        $getUser = User::where('email', $this->email)
            ->orWhere('user_phone', $this->user_phone);

        if ($getUser->exists() && $getUser->first()->is_guest) {

            $user = User::where('id', $getUser->first()->id)->update([
                'user_phone' => $this->user_phone,
                'email' => $this->email,
                'name' => $this->name,
                // 'password' => Hash::make($this->password),
                "is_guest" => 0
            ]);


            auth()->login($user);
            $this->sendWelcomeSMS($getUser->first()->user_phone, $getUser->first()->name, $getUser->first()->email);

            // Reset form
            $this->reset(['name', 'email', 'user_phone']);
            $this->resetValidation();

            return $this->dispatch('toast', [
                'type' => 'success',
                'success' => true,
                'message' => 'User Registered successfully!',
            ]);
        }


        if ($getUser->exists() && $getUser->first()->is_guest == 0) {
            $this->dispatch('toast', [
                'type' => 'error',
                'success' => false,
                'message' => 'User Already Exist, Please Login',
            ]);
            return;
        }


        $user = User::create([
            'user_phone' => $this->user_phone,
            'email' => $this->email,
            'name' => $this->name,
            'password' => Hash::make($this->password),
            'reg_date' => Carbon::now()
        ]);

        auth()->login($user);
        // $this->sendWelcomeSMS($user->user_phone, $user->name, $user->email);
        $this->mergeSessionCartToDatabase();
        // Reset form
        $this->reset(['name', 'email', 'user_phone', 'password']);
        $this->resetValidation();

        $this->dispatch('toast', [
            'type' => 'success',
            'success' => true,
            'message' => 'User Registered successfully!',
        ]);
        return redirect()->route('index');
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



    private function sendWelcomeSMS($mobile, $name, $email)
    {
        Mail::to($email)
            ->bcc('snehal.yugasa@gmail.com')
            ->send(new WelcomeMail($name));
        try {
            $smsParams = [
                'user' => 'zakh',
                'password' => 'zakh2025', // Consider moving to .env file
                'senderid' => 'ZAKHEC',
                'channel' => 'Trans',
                'DCS' => 0,
                'route ' => 42,
                'flashsms' => 0,
                'number' => '91' . $mobile, // Changed from 'mobile' to 'number', removed '+'
                'text' => "Welcome to ZAKH, {$name}! Your account has been created successfully. Start exploring and enjoy your shopping experience with us! -ZAKHEC", // Changed from 'message' to 'text'
                'Peid' => '1701175853936286545', // Changed from 'entityid' to 'Peid'
                'DLTTemplateId' => '1707176034724804046' // Changed from 'tempid' to 'DLTTemplateId'
            ];

            $response = Http::get('http://www.admagister.net/api/mt/SendSMS', $smsParams);

            $responseWhatsapp = Http::withToken('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJjbGllbnRJZCI6Inpha2hfYm90IiwiZW1haWwiOiJ6YWtoQG1haWxpbmF0b3IuY29tIiwidGltZXN0YW1wIjoiMjAyNS0xMC0zMVQwNjozNTozMS4zNTJaIiwiY2hhbm5lbCI6IndoYXRzYXBwIiwiaWF0IjoxNzYxODkyNTMxfQ.gmwdMS60eECJ9Mdx3VUq9KvMMnBZXQET2U6PVpfDHeI')
                ->post('https://api.helloyubo.com/v3/whatsapp/notification', [
                    "clientId" => "zakh_bot",
                    "channel" => "whatsapp",
                    "send_to" => "91.$mobile",
                    "templateName" => "account_create",
                    "parameters" => [$name],
                    "msg_type" => "TEXT",
                    "header" => "",
                    "footer" => "-ZAKHEC",
                    "buttonUrlParam" => null,
                    "button" => "false",
                    "media_url" => "",
                    "lang" => "en",
                    "msg" => null,
                    "userName" => null,
                    "parametersArrayObj" => null,
                    "headerParam" => null
                ]);

            // Get response
            // $whatsappResponse = $responseWhatsapp->json();
            Log::info('paymentViaGateway:', ["data" => $responseWhatsapp->body()]);

            if ($response->successful()) {
                Log::info('SMS sent successfully', ['mobile' => $mobile, 'response' => $response->body()]);
                return true;
            } else {
                Log::error('SMS sending failed', ['mobile' => $mobile, 'response' => $response->body()]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('SMS API Error: ' . $e->getMessage(), ['mobile' => $mobile]);
            return false;
        }
    }

    public function render()
    {
        return view('livewire.register');
    }
}
