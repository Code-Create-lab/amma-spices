<div>
    {{-- Step 1: Enter Details & Send OTP --}}
    @if (!$otpSent)
        <form wire:submit="register">
            <div class="form-group">
                <label for="register-name">Your Name*</label>
                <input type="text" wire:model="name" class="form-control" id="register-name"
                    placeholder="Enter your full name" required>
                @error('name')
                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                @enderror
            </div><!-- End .form-group -->

            <div class="form-group">
                <label for="register-email">Your Email Address*</label>
                <input type="email" wire:model="email" class="form-control" id="register-email"
                    placeholder="example@email.com" required>
                @error('email')
                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                @enderror
            </div><!-- End .form-group -->

            <div class="form-group">
                <label for="register-mobile">Your Mobile Number*</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"
                            style="background-color: #FFF7ED; border-color: #f4d9b8; color: #8B4513;">+91</span>
                    </div>
                    <input type="text" wire:model="user_phone" class="form-control" id="register-mobile"
                        placeholder="10-digit mobile number" maxlength="10" required>
                </div>
                @error('user_phone')
                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                @enderror
            </div><!-- End .form-group -->

            <div class="custom-control custom-checkbox">
                <input wire:model="privacy" type="checkbox" class="custom-control-input" id="register-policy-2"
                    required>
                <label class="custom-control-label" for="register-policy-2">
                    I agree to the <a href="#" style="color: #8B4513;">privacy policy</a> *
                </label>
                @error('privacy')
                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                @enderror
            </div><!-- End .custom-checkbox -->

            <div class="form-footer">
                <button type="submit" class="btn btn-outline-primary-2"
                    @if ($isLoading) disabled @endif>
                    @if ($isLoading)
                        <i class="icon-spinner spinner"></i>
                        Registering...
                        {{-- Sending OTP... --}}
                    @else
                        {{-- SEND OTP --}}
                        Register
                        <i class="icon-long-arrow-right"></i>
                    @endif
                </button>
            </div><!-- End .form-footer -->

            @if (session()->has('error'))
                <div class="alert alert-danger mt-3">{{ session('error') }}</div>
            @endif

            @if (session()->has('success'))
                <div class="alert mt-3" style="background-color: #d4edda; border-color: #c3e6cb; color: #155724;">
                    {{ session('success') }}
                </div>
            @endif
        </form>

        {{-- Step 2: Verify OTP & Complete Registration --}}
    @else
        <div class="otp-verification-section">
            {{-- OTP Sent Alert - Themed --}}
            <div class="alert mt-3 mb-4"
                style="background-color: #FFF7ED; border-left: 4px solid #8B4513; border-radius: 8px; padding: 20px;">
                <h5 style="color: #8B4513; margin-bottom: 10px; font-weight: 600;">
                    <i class="icon-envelope" style="color: #A0522D;"></i> OTP Sent!
                </h5>
                <small style="color: #666;">We've sent a 6-digit verification code to <strong
                        style="color: #8B4513;">{{ $email }}</strong></small>
            </div>

            <form wire:submit="verifyOtpAndRegister">
                {{-- Show entered data - Themed --}}
                <div class="mb-3 p-3"
                    style="background: linear-gradient(135deg, #FFF7ED 0%, #ffffff 100%); border: 2px solid #f4d9b8; border-radius: 8px;">
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted" style="color: #A0522D !important; font-weight: 600;">Name:</small>
                            <p class="mb-1" style="color: #8B4513; font-weight: 500;">{{ $name }}</p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted"
                                style="color: #A0522D !important; font-weight: 600;">Mobile:</small>
                            <p class="mb-1" style="color: #8B4513; font-weight: 500;">+91 {{ $user_phone }}</p>
                        </div>
                    </div>
                    <button type="button" wire:click="resetForm" class="btn btn-link btn-sm p-0"
                        style="color: #8B4513; text-decoration: underline;">
                        <i class="icon-pencil"></i> Change Details
                    </button>
                </div>

                <div class="form-group">
                    <label for="register-otp">Enter OTP*</label>
                    <input type="text" wire:model="otp" class="form-control form-control-lg text-center"
                        id="register-otp" placeholder="Enter 6-digit code" maxlength="6"
                        style="letter-spacing: 8px; font-size: 24px; font-weight: bold; font-family: 'Courier New', monospace; border: 2px solid #f4d9b8; background-color: #FFFBF5;"
                        required>
                    @error('otp')
                        <span class="text-danger small d-block mt-1">{{ $message }}</span>
                    @enderror
                    <small class="form-text" style="color: #A0522D;">
                        <i class="icon-clock"></i> OTP is valid for 10 minutes
                    </small>
                </div><!-- End .form-group -->

                <div class="form-footer">
                    <button type="submit" class="btn btn-outline-primary-2 mb-2" style="width: 100%;"
                        @if ($isLoading) disabled @endif>
                        @if ($isLoading)
                            <i class="icon-spinner spinner"></i> Verifying...
                        @else
                            VERIFY & REGISTER
                            <i class="icon-long-arrow-right"></i>
                        @endif
                    </button>

                    <button type="button" wire:click="resendOtp" class="btn btn-outline-secondary"
                        style="width: 100%; border-color: #A0522D; color: #A0522D;"
                        @if ($isLoading) disabled @endif>
                        @if ($isLoading)
                            <i class="icon-spinner spinner"></i> Resending...
                        @else
                            <i class="icon-refresh"></i> Resend OTP
                        @endif
                    </button>
                </div><!-- End .form-footer -->

                @if (session()->has('error'))
                    <div class="alert alert-danger mt-3">{{ session('error') }}</div>
                @endif

                @if (session()->has('success'))
                    <div class="alert mt-3"
                        style="background-color: #d4edda; border-color: #c3e6cb; color: #155724; border-left: 4px solid #28a745; border-radius: 8px; padding: 15px;">
                        <i class="icon-check"></i> {{ session('success') }}
                    </div>
                @endif
            </form>
        </div>
    @endif

    {{-- Security Notice - Themed --}}
    <div class="mt-4 p-3" style="background-color: #fff9e6; border-left: 4px solid #ffc107; border-radius: 8px;">
        <small style="color: #856404;">
            <i class="icon-lock" style="color: #ffc107;"></i>
            <strong>Security Note:</strong> Never share your OTP with anyone.
            Amma's Spices will never ask for your OTP.
        </small>
    </div>
    <style>
        /* Loading Spinner Animation */
        .spinner {
            display: inline-block;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Themed Form Controls */
        .form-control:focus {
            border-color: #f4d9b8;
            box-shadow: 0 0 0 0.2rem rgba(244, 217, 184, 0.25);
        }

        /* Themed Checkbox */
        .custom-control-input:checked~.custom-control-label::before {
            background-color: #8B4513;
            border-color: #8B4513;
        }

        /* Button Hover Effects */
        .btn-outline-primary-2:hover:not([disabled]) {
            background-color: #8B4513 !important;
            border-color: #8B4513 !important;
        }

        .btn-outline-secondary:hover:not([disabled]) {
            background-color: #A0522D !important;
            border-color: #A0522D !important;
            color: #ffffff !important;
        }

        /* Disabled button state */
        button[disabled],
        button:disabled {
            opacity: 0.65 !important;
            cursor: not-allowed !important;
        }

        /* Input Focus Effect */
        #register-otp:focus {
            border-color: #8B4513;
            box-shadow: 0 0 0 0.2rem rgba(139, 69, 19, 0.15);
            background-color: #FFF7ED;
        }

        /* Link Styling */
        a {
            color: #8B4513;
        }

        a:hover {
            color: #A0522D;
        }
    </style>
</div>
