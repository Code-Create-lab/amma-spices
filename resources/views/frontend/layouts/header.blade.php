  <header class="header header-28  sticky-header">
      <!-- <p class="tag-line-text">All our products are lab-tested and certified.</p> -->
     
      <div class="sticky-wrapper">
         <div class="header-top">
            @livewire('nav-cart')
          </div>
          <div class="header-middle">
              <div class="container">
                  <div class="header-left">
                      <button class="mobile-menu-toggler" id="mobile-bar">
                          <span class="sr-only">Toggle mobile menu</span>
                          <i class="icon-bars"></i>
                      </button>
                      <a href="{{ route('index') }}" class="logo"><img src="{{ asset('assets/img/logo-bodhi.png') }}"
                              alt=" Logo" width="50" height="25"></a>
                      <nav class="main-nav">
                          <ul class="menu sf-arrows">
                              <li class="megamenu-container {{ Route::currentRouteName() == 'index' ? 'active' :  ""}}  megamenu-list">
                                  <a href="{{ route('index') }}" class="">Home</a>

                              </li>
                              <li class="megamenu-list {{ Route::currentRouteName() == 'shop.page.index' ? 'active' :  ""}}">
                                  <a href="{{route('shop.page.index')}}" class="">Shop</a>

                              </li>
                              <li class="megamenu-list {{ Route::currentRouteName() == 'frontend.gallery' ? 'active' :  '' }}">
                                  <a href="{{route('frontend.gallery')}}" class="">Gallery</a>

                              </li>
                              <li class="megamenu-list {{ Route::currentRouteName() == 'frontend.about-us' ? 'active' :  '' }}">
                                  <a href="{{route('frontend.about-us')}}" class="">About</a>

                              </li>
                              <li class="megamenu-list {{ Route::currentRouteName() == 'contact-us.index' ? 'active' :  ""}}">
                                  <a href="{{route('contact-us.index')}}" class="">Contact Us</a>

                              </li>
                              
                             

                          </ul>
                      </nav>
                      <div class="header-st-bar">
                          {{-- @livewire('nav-cart') --}}
                      </div>
                  </div>

              </div>
          </div>
      </div>
  </header>
