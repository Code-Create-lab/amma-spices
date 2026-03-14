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
                        <img src="assets/img/Our-Belief.jpg" style="width: 100%;">
                    </div>
                    <div class="col-md-6">
                        <h2 class="section-title">About Amma's Spices Soap Co.</h2>
                        <p class="intro-text">
                            Amma's Spices Soap Co. is a celebration of nature, simplicity, and mindful living.
                            Named after the sacred Fig tree under which Lord Buddha attained enlightenment.
                            Amma's Spices is a creation that deviates from the artificial extravagance of most products in
                            today’s world, instead placing all of its emphasis on the hidden beauty of organic-ness.
                        </p>
                        <p class="intro-text">
                            Devoted to rekindling a profound state of bliss and awakening that bathing was originally
                            intended to bring us, Amma's Spices essentially serves as a message for us to reconnect with our
                            roots and return to the sacred Panch Tatva that our body is made of.
                            Treating our body as a sacred temple and offering it everything that Mother Earth has provided
                            us with.
                        </p>
                        <p class="intro-text">
                            Having been in close proximity with nature all my life, I envision a world in which we finally
                            break free from this unhealthy mindset surrounding pretentiousness and superfluity and truly
                            embrace nature once and for all.
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
                            <h2 class="section-title">What Makes Our Product Different</h2>
                            <p>Our products are made with the ingredients that can easily be found in any kitchen, using:
                                Fresh Fruits, Herbs, Vegetables, Fresh Milk, Natural Butters, Essential Oils, Cold Pressed
                                Oils,
                                Natural Colors, Natural Fragrances.</p>
                            <p>All of these combined, will not only provide an expert organic fragrance, but they will also
                                supply the skin with ample nutrition and moisture to give it a glowing radiance and maintain
                                fairness, and ultimately result in clean, long-term youthful skin.</p>
                            <p class="">From the tender skin of a child to the evolving skin of an adult, our skincare
                                respects every
                                stage of life and the nature that sustains it. Because when it comes to skin, mindfulness is
                                not a choice. It is a responsibility.</p>

                        </div>

                    </div>
                    <div class="col-md-6">
                        <img src="assets/img/What-Makes-Our-Soaps-Different.jpg" style="width: 100%;">
                    </div>
                </div>
            </div>
        </section>


        <div class="bodhi-pro">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <img src="assets/img/The-Story-Behind-Bodhi-Bliss.jpg" style="width: 100%;">
                    </div>
                    <div class="col-md-6">
                        <h2 class="section-title">A Conscious Choice for Skin & Planet</h2>
                        <p class="">Amma's Spices Soap Co. products may not look fancy, but they work. With rising
                            pollution and
                            increasing skin sensitivities, we believe it is time to return to basics and care for our skin
                            naturally. We do not claim miracles — only honesty.
                            My family and I have used these home-made products for years and the difference is felt, not
                            advertised.
                        </p>
                        <div class="bod-p-r-nm">
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
                        </div>
                    </div>
                </div>
            </div>



            <!-- Story & Brands Section -->
            <section class="story-section Belief">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <h2 class="section-title">The Birth Of Amma's Spices Soap Co.</h2>

                            <p> I have frequently been asked by many of my clients, what exactly it was that prompted me to
                                create Amma's Spices Soap Co. Truth be told, it was not just one thing, but a culmination of
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
                                whole inspired me to start this company, Amma's Spices Soap Co.</p>

                        </div>
                        <!--   <div class="col-md-6">
                                    <img src="assets/img/The-Story-Behind-Bodhi-Bliss.jpg" style="width: 100%;">
                                </div> -->
                    </div>
                </div>
            </section>
    </main>
@endsection

@push('scripts')
@endpush
