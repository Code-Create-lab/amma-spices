 <div class="form-box" wire:ignore>.
     <button type="button" class="close" data-dismiss="modal" aria-label="Close">
         <span aria-hidden="true">&times;</span>
     </button>
     <div class="form-tab">
         <ul class="nav nav-pills nav-fill" role="tablist">
             <li class="nav-item">
                 <a class="nav-link {{ $activeTab === 'signin' ? 'active' : '' }}"
                     wire:click="$set('activeTab', 'signin')"
                     aria-selected="{{ $activeTab === 'signin' ? 'true' : 'false' }}" id="signin-tab-2"
                     data-toggle="tab" href="#signin-2" role="tab" aria-controls="signin-2"
                     aria-selected="false">Sign In</a>
             </li>
             {{-- <li class="nav-item">
                 <a class="nav-link {{ $activeTab === 'register' ? 'active' : '' }}"
                     wire:click="$set('activeTab', 'register')"
                     aria-selected="{{ $activeTab === 'register' ? 'true' : 'false' }}" id="register-tab-2"
                     data-toggle="tab" href="#register-2" role="tab" aria-controls="register-2"
                     aria-selected="true">Register</a>
             </li> --}}
         </ul>
         <div class="tab-content">
             <div class="tab-pane fade {{ $activeTab === 'signin' ? 'show active' : '' }}" id="signin-2"
                 role="tabpanel" aria-labelledby="signin-tab-2">
                 @livewire('login')

             </div><!-- .End .tab-pane -->
             {{-- <div class="tab-pane fade show {{ $activeTab === 'register' ? 'show active' : '' }}" id="register-2"
                 role="tabpanel" aria-labelledby="register-tab-2">
                @livewire('register')

             </div><!-- .End .tab-pane --> --}}
         </div><!-- End .tab-content -->
     </div><!-- End .form-tab -->
 </div><!-- End .form-box -->
