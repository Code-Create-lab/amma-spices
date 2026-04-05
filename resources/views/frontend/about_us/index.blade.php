@extends('frontend.layouts.app', ['title' => ''])

@push('styles')
@endpush


@section('content')
    <main class="main page-content">
        <div class="page-header">
            <div class="container">
                <div class="row" style="display: block;">
                    <div class="heading">
                        <h1 class="title  text-center">About Us</h1>
                        <span class="seprater-img"><img src="assets/img/seprater.png"></span>
                    </div>
                </div>
            </div><!-- End .container -->
        </div>
        <nav aria-label="breadcrumb" class="breadcrumb-nav">
            <div class="container">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('index') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">About Us</li>
                </ol>
            </div><!-- End .container -->
        </nav><!-- End .breadcrumb-nav -->

        <!-- Intro Section -->
        <section class="a-section">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <img src="assets/img/garam-masala.webp" style="width: 100%;">
                    </div>
                    <div class="col-md-6">
                        <h2 class="section-title">Our Story</h2>
                        <p class="intro-text">Our Story
                            Growing up in South of India, we would always watch Amma lovingly blend her spices fresh before
                            any meal preparation, thus filling our home with warmth and aroma.
                        </p>
                        <p class="intro-text">
                            Our Story
                            Growing up in South of India, we would always watch Amma lovingly blend her spices fresh before
                            any meal preparation, thus filling our home with warmth and aroma.

                            Now, from our little home-run family kitchen, we bring you these authentic South Indian spice
                            blends for you – crafted just like she made them, pure, traditional, and filled with motherly
                            love. Every blend is crafted with love to bring you the nostalgic taste of home-cooked meals.
                        </p>
                        <p class="intro-text">
                            Our mission is to make your daily cooking nourishing, easy, and full of flavour – just like Amma
                            intended.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Our Belief & What Makes Us Different -->
        <section class="about-section Belief">
            <div class="container">
                <!-- <div class="row">
                                        <div class="col-md-6">
                                            <div  class="about-section-in">
                                            <h2 class="section-title">Our Belief</h2>
                                            <p>We believe the human body is sacred, formed from Panch Tatva — Earth, Water, Fire, Air, and Space. Everything we create is rooted in respect for these elements and for what Mother Earth provides naturally.</p>
                                            <p>Amma's Spices stands for:</p>
                                            <ul class="styled-list">
                                                <li>Honest ingredients</li>
                                                <li>Traditional craftsmanship</li>
                                                <li>Skin health over artificial glamour</li>
                                                <li>Sustainability over excess</li>
                                                <li>Conscious choices over convenience</li>
                                            </ul>
                                        </div>
                                        </div>
                                        <div class="col-md-6">
                                            <img src="assets/img/Our-Belief.jpg">
                                        </div>
                                    </div> -->
                <div class="row botton-sec">

                    <div class="col-md-6">
                        <div class="about-bo-in">
                            <h2 class="section-title">Who we are</h2>

                            <h5>Meet The Women Behind Amma’s Spices</h5>
                            <p>Amma’s Spices is more than just a brand – it is a journey of love, tradition, and
                                togetherness. It is the story of a <strong>mother-in-law and daughter-in-law duo</strong>
                                who turned their shared passion for food into a purposeful business.</p>

                            <h5>Our Master Crafter – The Mother-in-Law</h5>
                            <p>At the heart of Amma’s Spices is our beloved mother-in-law, the master crafter behind each
                                blend. With decades of wisdom, she knows exactly how to balance spices to create the rich,
                                authentic flavours we all crave. Her recipes have been passed down through generations,
                                perfected with love and patience.</p>

                            <h5>The Brains Behind The Brand – The Daughter-in-law</h5>
                            <p>Bringing Amma’s culinary magic to the world is her daughter-in-law, the entrepreneurial force
                                who dreamt of making these traditional blends accessible to every home. From branding to
                                packaging, she ensures that Amma’s legacy reaches your kitchen with care, purity, and
                                purpose.</p>

                            <p>Together, they are building Amma’s Spices as a <strong>family-run business rooted in
                                    tradition and innovation</strong>. From <strong>start to packaging</strong>, every step
                                is handled with honesty and dedication, ensuring that what reaches you is <strong>pure,
                                    rich, and aromatic – just the way Amma makes it</strong>.</p>

                            <p>Their simple mission is to make everyday cooking taste even better with blends that are
                                fresh, authentic, and crafted with love.</p>

                            <p><strong>“Because good food isn’t just about taste – it’s about creating memories at every
                                    meal.”</strong></p>

                        </div>

                    </div>
                    <div class="col-md-6">
                        <img src="assets/img/aboutus.jpg" style="width: 100%;">
                    </div>
                </div>
            </div>
        </section>


        <div class="bodhi-pro">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <img src="assets/img/aboutUsMasala.jpg" style="width: 100%;">
                    </div>
                    <div class="col-md-6 about-txt">
                        <h2 class="section-title">Why Choose Us Section</h2>
                        <ul>
                            <li>Pure, premium quality ingredients</li>
                            <li>No artificial colours or preservatives</li>
                            <li>Traditional recipes perfected over generations</li>
                            <li>Freshly ground for maximum potency and taste</li>
                        </ul>
                        {{-- <div class="bod-p-r-nm">
                            <p class="bod-p-r-nm-te"> By choosing Amma's Spices, you are also choosing:</p>
                            <div class="styled-numbered">

                                <div class="ch-li">
                                    <img src="assets/img/Reduced_chemical_exposure.png">
                                    <p>Reduced chemical exposure</p>
                                </div>
                                <!-- <div class="ch-li">
                                                    <img src="assets/img/Less_water_and_waste.png">
                                                    <p>Less water and waste pollution</p>
                                                    </div> -->
                                <div class="ch-li">
                                    <img src="assets/img/lifestyle.png">
                                    <p>A more sustainable lifestyle</p>
                                </div>
                                <div class="ch-li lastch-li">
                                    <img src="assets/img/Healthier_skin.png">
                                    <p>Healthier skin means fewer salon and dermatology visits.</p>
                                </div>
                            </div>
                        </div> --}}
                    </div>
                </div>
            </div>



            {{-- <!-- Story & Brands Section -->
            <section class="story-section Belief">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <h2 class="section-title">The Birth Of Amma's Spices.</h2>

                            <p> I have frequently been asked by many of my clients, what exactly it was that prompted me to
                                create Amma's Spices. Truth be told, it was not just one thing, but a culmination of
                                various
                                experiences that led to this moment.</p>
                            <p>From childhood hikes with my father to tending a kitchen garden in rural northern India, from
                                city living in Mumbai to discovering traditional soap-making in Marseille, France. Each of
                                these
                                experience’s shaped this journey.</p>
                            <p>It all began with a memory of hiking with my father at a very young age that truly struck my
                                heart…</p>
                            <p>A luscious ensemble of green across those mountains. All green, everywhere around us. The
                                trees, the shrubs, the plantations…they spoke to me.</p>
                            <p>The aesthetics of such freshness and purity before my eyes ignited a fire inside me - I just
                                had
                                to know more about those plants: their names, the workings of their unique features, their
                                various uses, including how they could possibly heal our lives.</p>
                            <p>It was only my first hike, and yet there I was, already having found my true calling.</p>
                            <p>Years passed, and with them, so did my love for nature, and my understanding of it, thanks to
                                my father. But I hadn’t realized exactly what I wanted to do yet…until one day, when I was
                                on a
                                trip to the historical city of Marseille, in France…</p>
                            <p>I had an opportunity to visit a soap workshop, where I felt in awe at the techniques, the
                                locals
                                used to create genuine, pure, soap out of various herbs, local flowers, oils, etc.
                                It was at that very moment that I fell in love with this concept. The world of natural soaps
                                as a
                                whole inspired me to start this company, Amma's Spices.</p>

                        </div>
                        <!--   <div class="col-md-6">
                                            <img src="assets/img/The-Story-Behind-Bodhi-Bliss.jpg" style="width: 100%;">
                                        </div> -->
                    </div>
                </div>
            </section> --}}
    </main>
@endsection

@push('scripts')
@endpush
