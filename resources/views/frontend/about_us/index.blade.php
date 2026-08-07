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

        {{-- ══════════════════════════════════════════
         FOUNDER STORY — first section on the page.
         Replaces both the old "Our Story" intro and the
         "Who we are" block.
    ══════════════════════════════════════════ --}}
        @php
            $founderJourney = [
                [
                    'title' => 'A recipe, not a formula',
                    'text' => "Amma's blends were measured by hand and memory, cooked for the family long before anyone thought of selling them.",
                ],
                [
                    'title' => 'The first batch for a stranger',
                    'text' => 'Friends who tasted the food started asking for jars. The first packs left the same home kitchen we still cook in.',
                ],
                [
                    'title' => 'The kitchen becomes a workshop',
                    'text' => 'Roasting, grinding and packing moved to a fixed weekly rhythm — small quantities, made to order rather than to stock.',
                ],
                [
                    'title' => "Amma's Spices today",
                    'text' => 'The same two women, the same recipes, now with lab-tested batches and packs that travel across India.',
                ],
            ];

            $founderWeek = [
                [
                    'title' => 'Sourced, not bought blind',
                    'text' => 'Whole spices come from growers we buy from directly, chosen lot by lot for oil content and aroma.',
                ],
                [
                    'title' => 'Cleaned &amp; sun-dried',
                    'text' => 'Every lot is hand-cleaned of stones, stalk and dust, then dried before it is allowed near the roaster.',
                ],
                [
                    'title' => 'Roasted and ground weekly',
                    'text' => 'Small batches are roasted and ground the same week they ship, so the oils are still in the powder.',
                ],
                [
                    'title' => 'Packed the day it is made',
                    'text' => 'Blended, weighed and sealed by hand in our own kitchen — no third-party filling, no long warehouse wait.',
                ],
            ];

            $founderValues = [
                'Family-run, start to seal',
                'Cooked in a home kitchen',
                'Small weekly batches',
                'Nothing hidden on the label',
            ];
        @endphp
        <section class="as-founder">
            <div class="container">
                <div class="as-founder-top">
                    <div class="as-founder-gallery">
                        <figure>
                            <img src="{{ asset('assets/img/aboutus.jpg') }}" width="450" height="600" loading="lazy"
                                alt="Amma and her daughter-in-law, the two women behind Amma's Spices">
                            <figcaption class="as-founder-caption">The two women behind every jar</figcaption>
                        </figure>
                    </div>

                    <div>
                        <div class="as-founder-label">Founder Story</div>
                        <h2 class="as-founder-title">Two Women, One Kitchen, Every Jar</h2>
                        <p class="as-founder-body">Growing up in South India, we watched Amma blend her spices fresh
                            before every meal — the smell of it filling the house long before the food reached the table.
                            Amma's Spices is not a brand that was designed and then filled. It started as a
                            mother-in-law and daughter-in-law cooking together: one who knew the recipes by heart, one
                            who believed other homes deserved them too.</p>
                        <p class="as-founder-body">Everything you buy from us is still made where it began — a small
                            home-run kitchen, in small quantities, by the same two pairs of hands. Pure, traditional, and
                            made to bring back the taste of a home-cooked meal.</p>
                        <p class="as-founder-body">Our mission is simple: make your everyday cooking nourishing, easy
                            and full of flavour — just like Amma intended.</p>

                        <div class="as-founder-people">
                            <div class="as-founder-person">
                                <span class="as-founder-person-mark" aria-hidden="true">A</span>
                                <div>
                                    <div class="as-founder-person-role">The Master Crafter</div>
                                    <p class="as-founder-person-name">Amma — the mother-in-law</p>
                                    <p class="as-founder-person-text">Decades of cooking taught her the balance no
                                        machine gets right: how long to roast, when to stop grinding, how much of what.
                                        Every recipe we sell is hers, unchanged.</p>
                                </div>
                            </div>
                            <div class="as-founder-person">
                                <span class="as-founder-person-mark" aria-hidden="true">S</span>
                                <div>
                                    <div class="as-founder-person-role">The Brand Builder</div>
                                    <p class="as-founder-person-name">The daughter-in-law</p>
                                    <p class="as-founder-person-text">She took those jars beyond the family — sourcing,
                                        lab testing, packaging and delivery — so Amma's kitchen could reach yours without
                                        losing what makes it hers.</p>
                                </div>
                            </div>
                        </div>

                        <blockquote class="as-founder-quote">"Because good food isn't just about taste — it's about
                            creating memories at every meal."</blockquote>
                    </div>
                </div>

                {{-- ── Journey timeline ── --}}
                <div class="as-founder-subhead">
                    <h3>How we got here</h3>
                    <p>No factory, no investor, no shortcut. Just a recipe that kept being asked for.</p>
                </div>
                <div class="as-journey">
                    @foreach ($founderJourney as $step)
                        <div class="as-journey-step">
                            <span class="as-journey-dot" aria-hidden="true"></span>
                            <h4>{{ $step['title'] }}</h4>
                            <p>{{ $step['text'] }}</p>
                        </div>
                    @endforeach
                </div>

                {{-- ── Sourcing & weekly production ── --}}
                <div class="as-founder-subhead">
                    <h3>Inside one week in our kitchen</h3>
                    <p>Where the spices come from, and what happens between the farm and your jar.</p>
                </div>
                <div class="as-week">
                    @foreach ($founderWeek as $i => $step)
                        <div class="as-week-card">
                            <span class="as-week-num">STEP {{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <h4>{!! $step['title'] !!}</h4>
                            <p>{!! $step['text'] !!}</p>
                        </div>
                    @endforeach
                </div>

                {{-- ── Family values ── --}}
                <ul class="as-founder-values">
                    @foreach ($founderValues as $value)
                        <li>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                <path d="M20 6 9 17l-5-5" />
                            </svg>
                            {{ $value }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>


        <style>
            .abt-wcu-list {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 20px 28px;
                padding: 0;
                margin: 20px 0 0;
                list-style: none;
            }

            .abt-wcu-item {
                display: flex;
                align-items: center;
                gap: 14px;
            }

            .abt-wcu-item img {
                width: 44px;
                height: 44px;
                object-fit: contain;
                flex-shrink: 0;
                filter: brightness(0) saturate(100%) invert(70%) sepia(55%) saturate(500%) hue-rotate(5deg);
            }

            .abt-wcu-item span {
                font-size: 14px;
                font-weight: 500;
                color: #333;
                line-height: 1.4;
            }

            @media (max-width: 767px) {
                .abt-wcu-list {
                    grid-template-columns: 1fr;
                    gap: 16px;
                }
            }
        </style>

        <div class="bodhi-pro">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <img src="assets/img/aboutUsMasala.jpg" style="width: 100%;">
                    </div>
                    <div class="col-md-6 about-txt">
                        <h2 class="section-title">Why Choose Us</h2>
                        <ul class="abt-wcu-list">
                            <li class="abt-wcu-item">
                                <img src="{{ asset('assets/img/preservative_free.svg') }}" alt="Preservative free">
                                <span>Preservative free</span>
                            </li>
                            <li class="abt-wcu-item">
                                <img src="{{ asset('assets/img/Reduced_chemical_exposure.png') }}" alt="No Chemicals">
                                <span>No artificial additives</span>
                            </li>
                            <li class="abt-wcu-item">
                                <img src="{{ asset('assets/img/Chemical-Free-Skin-Safe.png') }}" alt="Lab Tested">
                                <span>All products are lab-tested and certified</span>
                            </li>
                            <li class="abt-wcu-item">
                                <img src="{{ asset('assets/img/Healthier_skin.png') }}" alt="Authentic">
                                <span>Authentic recipes &amp; flavours</span>
                            </li>
                            <li class="abt-wcu-item">
                                <img src="{{ asset('assets/img/made_with_natural_ingredients.svg') }}"
                                    alt="Natural ingredients">
                                <span>Made with natural ingredients</span>
                            </li>



                            <li class="abt-wcu-item">
                                <img src="{{ asset('assets/img/high_natural_oil.svg') }}" alt="High natural oil content">
                                <span>Natural oil content</span>
                            </li>
                            <li class="abt-wcu-item">
                                <img src="{{ asset('assets/img/proud_product_of_india.png') }}"
                                    alt="Proud product of India">
                                <span>Proud product of India</span>
                            </li>
                            <li class="abt-wcu-item">
                                <img src="{{ asset('assets/img/lifestyle.png') }}" alt="Sustainable">
                                <span>Sustainable, home kitchen crafted</span>
                            </li>





                        </ul>
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
