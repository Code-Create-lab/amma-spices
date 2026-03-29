 <footer class="footer footer-2">

     <div class="container">
         <div class="footer-middle">
             <div class="row">
                 <div class="fot-col-div">
                     <div class="widget widget-about">
                         <a href="{{ route('index') }}">
                             <img src="{{ asset('assets/img/logo-bodhi.png') }}" alt=" Logo" class="fot-logo">
                         </a>
                     </div><!-- End .widget about-widget -->
                 </div><!-- End .col-sm-12 col-lg-3 -->

                 <div class="fot-col-div">
                     <div class="widget mb-4">
                         <h4 class="widget-title text-white">Information</h4><!-- End .widget-title -->

                         <ul class="widget-list">
                             <li><a href="{{ route('index') }}">Home</a></li>
                             <li><a href="{{ route('frontend.about-us') }}">About Us </a></li>
                             <li><a href="{{ route('contact-us.index') }}">Contact us</a></li>
                         </ul><!-- End .widget-list -->
                     </div><!-- End .widget -->
                 </div><!-- End .col-sm-4 col-lg-3 -->

                 <div class="fot-col-div">
                     <div class="widget">
                         <h4 class="widget-title text-white">Customer Service</h4><!-- End .widget-title -->

                         <ul class="widget-list">
                             <li><a href="{{ route('frontend.terms_and_conditions') }}">Terms & conditions</a></li>
                             {{-- <li><a href="#">Return & Exchange Policy</a></li> --}}
                             <li><a href="{{ route('frontend.shipping_and_delivery') }}">Shipping & delivery</a></li>
                             <li><a href="{{ route('frontend.privacy_policy') }}">Privacy policy</a></li>
                         </ul><!-- End .widget-list -->
                     </div><!-- End .widget -->
                 </div><!-- End .col-sm-4 col-lg-3 -->

                 <div class="fot-col-div">
                     <div class="widget">
                         <h4 class="widget-title text-white">My Account</h4><!-- End .widget-title -->

                         <ul class="widget-list">
                             <li><a href="{{ route('login.index') }}">Login</a></li>
                             <li><a href="{{ route('wishlist') }}">My wishlist</a></li>
                             {{-- <li><a target="_blank" href="{{ route('customer.track-orders.index') }}">Track my order</a> --}}
                             </li>
                         </ul><!-- End .widget-list -->
                     </div><!-- End .widget -->
                 </div><!-- End .col-sm-64 col-lg-3 -->
                 <div class="fot-col-div">
                     <div class="widget">
                         <h4 class="widget-title text-white">Social Media</h4><!-- End .widget-title -->
                         <div class="social-icons social-icons-color">
                             <a href="https://www.facebook.com//" class="social-icon social-facebook" title="Facebook"
                                 target="_blank"><i class="icon-facebook-f"></i></a>
                             <a href="https://x.com/" class="social-icon social-twitter" title="Twitter"
                                 target="_blank"><i class="icon-twitter"></i></a>
                             <a href="https://www.instagram.com/" class="social-icon social-instagram" title="Instagram"
                                 target="_blank"><i class="icon-instagram"></i></a>
                             <a href="https://youtube.com/" class="social-icon social-youtube" title="Youtube"
                                 target="_blank"><i class="icon-youtube"></i></a>
                         </div><!-- End .soial-icons -->

                     </div><!-- End .widget -->
                 </div><!-- End .col-sm-64 col-lg-3 -->
             </div><!-- End .row -->
             <p class="footer-copyright">Copyright © 2026 Amma's Spices. All Rights Reserved. </p>
             <!-- End .footer-copyright -->
         </div><!-- End .container -->
     </div><!-- End .footer-middle -->


 </footer><!-- End .footer -->
 <script></script>
