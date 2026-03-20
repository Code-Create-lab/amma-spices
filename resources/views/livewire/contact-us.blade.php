<div>
    <form wire:submit="save" class="contact-form mb-3">
        <div class="row justify-content-center">
            <div class="col-md-6 sm-mb-30px">
                {{-- <label for="name" class="form-label fw-600 text-dark-gray mb-0">Enter your name*</label> --}}
                <div class="position-relative form-group mb-25px">
                    <span class="form-icon"><i class="bi bi-emoji-smile"></i></span>
                    <input wire:model="full_name"
                        class="ps-0 border-radius-0px border-color-extra-medium-gray bg-transparent form-control required"
                        id="name" type="text" placeholder="What's your good name?" required />
                    @error('full_name')
                        <span class="error text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="col-md-6 sm-mb-30px">
                {{-- <label for="email" class="form-label fw-600 text-dark-gray mb-0">Email address*</label> --}}
                <div class="position-relative form-group mb-25px">
                    <span class="form-icon"><i class="bi bi-envelope"></i></span>
                    <input wire:model="email"
                        class="ps-0 border-radius-0px border-color-extra-medium-gray bg-transparent form-control required"
                        id="email" type="email" placeholder="Enter your email address" required />
                    @error('email')
                        <span class="error text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="col-md-6 sm-mb-30px">
                {{-- <label for="phone" class="form-label fw-600 text-dark-gray mb-0">Phone number*</label> --}}
                <div class="position-relative form-group mb-25px">
                    <span class="form-icon"><i class="bi bi-telephone"></i></span>
                    <input wire:model="phone_number"
                        class="ps-0 border-radius-0px border-color-extra-medium-gray bg-transparent form-control required"
                        id="phone" type="tel" placeholder="Enter your phone number" />
                    @error('phone_number')
                        <span class="error text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="col-md-6 sm-mb-30px">
                {{-- <label for="subject" class="form-label fw-600 text-dark-gray mb-0">Subject</label> --}}
                <div class="position-relative form-group mb-25px">
                    <span class="form-icon"><i class="bi bi-journals"></i></span>
                    <input wire:model="subject"
                        class="ps-0 border-radius-0px border-color-extra-medium-gray bg-transparent form-control"
                        id="subject" type="text" placeholder="Subject" />
                    @error('subject')
                        <span class="error text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="col-12 mb-4">
                {{-- <label for="message" class="form-label fw-600 text-dark-gray mb-0">Your message</label> --}}
                <div class="position-relative form-group form-textarea mb-0">
                    <textarea wire:model="message"
                        class="ps-0 border-radius-0px border-color-extra-medium-gray bg-transparent form-control required"
                        id="message" placeholder="Message" rows="4" required></textarea>
                    <span class="form-icon"><i class="bi bi-chat-square-dots"></i></span>
                    @error('message')
                        <span class="error text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- <div class="col-md-6">
                <p class="mb-0 fs-14 lh-24 text-center text-md-start">
                    We are committed to protecting your privacy. We will never collect information about you without your explicit consent.
                </p>
            </div> --}}

            <div class="col-md-6 text-center text-md-end sm-mt-25px">
                <button
                    class="btn btn-outline-primary-2 "
                    type="submit">
                    Send message
                </button>
            </div>

            <div class="col-12">
                @if (session()->has('message'))
                    <div class="alert alert-success mt-3">
                        {{ session('message') }}
                    </div>
                @endif
            </div>
        </div>
    </form>
</div>
