<div>

    {{-- Step 1: Enter Username & Send OTP --}}
    @if (!$otpSent)
        <form wire:submit="sendOtp">
            <div class="form-group">
                <label for="singin-email-2">Mobile or Email address *</label>
                <input type="text" wire:model.live.debounce.300ms="username" class="form-control" id="singin-email-2"
                    name="singin-email" placeholder="Enter your email" required>
                @error('username')
                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                @enderror

                {{-- Show hint based on input --}}
                @if ($isMobileLogin)
                    <small class="text-muted">Mobile number detected - OTP will be sent via SMS</small>
                @elseif($isEmailLogin)
                    <small class="text-muted">Email detected - OTP will be sent via email</small>
                @endif
            </div><!-- End .form-group -->

            <div class="form-footer">
                <button type="submit" class="btn btn-outline-primary-2"
                    @if ($isLoading) disabled @endif>
                    @if ($isLoading)
                        <i class="icon-spinner spinner"></i> Sending OTP...
                    @else
                        SEND OTP
                        <i class="icon-long-arrow-right"></i>
                    @endif
                </button>

                {{-- <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="signin-remember-2">
                    <label class="custom-control-label" for="signin-remember-2">Remember Me</label>
                </div><!-- End .custom-checkbox --> --}}
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

        {{-- Step 2: Verify OTP & Login --}}
    @else
        <div class="otp-verification-section">
            {{-- OTP Sent Alert - Themed --}}
            <div class="alertbox-lo">
                <h5 style="color: #8B4513; margin-bottom: 10px; font-weight: 600;">
                    @if ($isMobileLogin)
                        <i class="icon-mobile" style="color: #A0522D;"></i> OTP Sent to Mobile!
                    @else
                        <i class="icon-envelope" style="color: #A0522D;"></i> OTP Sent to Email!
                    @endif
                </h5>
                <small style="color: #666;">
                    We've sent a 6-digit verification code to
                    <strong style="color: #8B4513;">{{ $username }}</strong>
                    <button type="button" wire:click="resetForm" class="btn btn-link btn-sm p-0"
                        style="color: #8B4513; text-decoration: underline;">
                        <i class="icon-pencil"></i> Change Username
                    </button>
                </small>
            </div>

            <form wire:submit="loginWithOtp">
                {{-- Show entered username - Themed --}}
                {{-- <div class="mb-3 p-3"
                    style="background: linear-gradient(135deg, #FFF7ED 0%, #ffffff 100%); border: 2px solid #f4d9b8; border-radius: 8px;">
                    <div class="row">
                        <div class="col-12">
                            <small class="text-muted" style="color: #A0522D !important; font-weight: 600;">
                                @if ($isMobileLogin)
                                    Mobile:
                                @else
                                    Email:
                                @endif
                            </small>
                            <p class="mb-1" style="color: #8B4513; font-weight: 500;">{{ $username }}</p>
                        </div>
                    </div>
                   
                </div> --}}

                <div class="form-group">
                    <label for="login-otp">Enter OTP *</label>
                    <input type="text" wire:model="otp" class="form-control form-control-lg text-center"
                        id="login-otp" placeholder="Enter 6-digit code" maxlength="6"
                        style="letter-spacing: 5px; font-size: 20px; border: 1px solid #f4d9b8; background-color: #FFFBF5;"
                        required>
                    @error('otp')
                        <span class="text-danger small d-block mt-1">{{ $message }}</span>
                    @enderror
                    <small class="form-text mt-1" style="color: #A0522D;">
                        <i class="icon-clock"></i> OTP is valid for 10 minutes
                    </small>
                </div><!-- End .form-group -->

                <div class="form-footer">
                    <button type="submit" class="btn btn-outline-primary-2" style="width: 100%;"
                        wire:loading.attr="disabled" wire:target="loginWithOtp">
                        <span wire:loading.remove wire:target="loginWithOtp">
                            LOGIN
                            <i class="icon-long-arrow-right"></i>
                        </span>
                        <span wire:loading wire:target="loginWithOtp">
                            <i class="icon-spinner spinner"></i> Verifying...
                        </span>
                    </button>

                    <button type="button" wire:click="resendOtp" class="btn btn-outline-secondary"
                        style="width: 100%; border-color: #cbaf91; color: #cbaf91;" wire:loading.attr="disabled"
                        wire:target="resendOtp, sendOtp, loginWithOtp">
                        <span wire:loading.remove wire:target="resendOtp">
                            <i class="icon-refresh"></i> Resend OTP
                        </span>
                        <span wire:loading wire:target="resendOtp">
                            <i class="icon-spinner spinner"></i> Resending...
                        </span>
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
    <script>
    document.addEventListener('livewire:initialized', () => {
        // Force loading state to clear after dispatch events
        Livewire.hook('commit', ({ component, commit, respond }) => {
            respond(() => {
                // Reset any loading states
                setTimeout(() => {
                    const loadingElements = document.querySelectorAll('[wire\\:loading]');
                    loadingElements.forEach(el => {
                        el.style.display = 'none';
                    });
                }, 100);
            });
        });
    });
</script>
</div>
