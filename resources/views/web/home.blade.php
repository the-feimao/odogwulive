@extends('main')

@section('slider')


    <div id="carouselExampleCaptions" class="carousel slide pointer-event" data-bs-ride="true"
        style="background-size: cover;">

        <div class="carousel-inner" style="background-size: cover;">
            <div class="carousel-item active" data-bs-interval="5000" style="background-size: cover;">
                <img src="./web/slider/wide5.jpg" class="d-block w-100" alt="..."
                    style="height: 100vh; object-fit:cover;">
                <div class="carousel-caption d-md-block text-left"
                    style="top: 100px !important; background-size: cover;height: 100vh;">
                    <a href="{{route('frontend.index')}}#section-ticket">
                        <h2 data-bs-target="#carouselExampleCaptions"
                            class="wow fadeIn animated"
                            style="font-size: 40px; visibility: visible; animation-name: fadeIn;">TICKETS</h2>        
                    </a><br>
                    <a href="https://odogwufest.com/landing.html#section-merch">
                        <h2 data-bs-target="#carouselExampleCaptions"
                            class="wow fadeIn animated"
                            style="font-size: 40px; visibility: visible; animation-name: fadeIn;">MERCH</h2>
                    </a><br>

                    <a href="https://odogwufest.com/landing.html#section-sponsors">
                        <h2 data-bs-target="#carouselExampleCaptions"
                            class="wow fadeIn animated"
                            style="font-size: 40px; visibility: visible; animation-name: fadeIn;">STALLS & STANDS</h2>
                    </a>
                    <br>

                    <a href="https://odogwufest.com/landing.html#section-register">
                    <h2 data-bs-target="#carouselExampleCaptions"
                        class="wow fadeIn animated"
                        style="font-size: 40px; visibility: visible; animation-name: fadeIn; opacity: 500;">CONTACT
                        US</h2>
                    </a>
                    <br>
                    <a href="https://odogwufest.com/landing.html#section-about">
                        <h2 data-bs-target="#carouselExampleCaptions"
                            class="wow fadeIn animated"
                            style="font-size: 40px; visibility: visible; animation-name: fadeIn; opacity: 500;">ABOUT US
                        </h2>
                    </a>
                    <br>
                </div>
            </div>
            <div class="carousel-item" data-bs-interval="5000" style="background-size: cover;">
                <img src="./web/slider/wide1.jpg" class="d-block w-100" alt="..."
                    style="height: 100vh; object-fit:cover;">
                <div class="carousel-caption d-md-block text-left"
                    style="top: 45px !important; background-size: cover;height: 100vh;">
                    <a href="https://odogwufest.com/landing.html#section-ticket">
                        <h2 data-bs-target="#carouselExampleCaptions"
                            class="wow fadeIn animated"
                            style="font-size: 40px; visibility: visible; animation-name: fadeIn;">TICKETS</h2>        
                    </a><br>
                    <a href="https://odogwufest.com/landing.html#section-merch">
                        <h2 data-bs-target="#carouselExampleCaptions"
                            class="wow fadeIn animated"
                            style="font-size: 40px; visibility: visible; animation-name: fadeIn;">MERCH</h2>
                    </a><br>

                    <a href="https://odogwufest.com/landing.html#section-sponsors">
                        <h2 data-bs-target="#carouselExampleCaptions"
                            class="wow fadeIn animated"
                            style="font-size: 40px; visibility: visible; animation-name: fadeIn;">STALLS & STANDS</h2>
                    </a>
                    <br>

                    <a href="https://odogwufest.com/landing.html#section-register">
                    <h2 data-bs-target="#carouselExampleCaptions"
                        class="wow fadeIn animated"
                        style="font-size: 40px; visibility: visible; animation-name: fadeIn; opacity: 500;">CONTACT
                        US</h2>
                    </a>
                    <br>
                    <a href="https://odogwufest.com/landing.html#section-about">
                        <h2 data-bs-target="#carouselExampleCaptions"
                            class="wow fadeIn animated"
                            style="font-size: 40px; visibility: visible; animation-name: fadeIn; opacity: 500;">ABOUT US
                        </h2>
                    </a>
                        <br>

                </div>
            </div>
            <div class="carousel-item" data-bs-interval="5000" style="background-size: cover;">
                <img src="./web/slider/wide2.jpg" class="d-block w-100" alt="..."
                    style="height: 100vh; object-fit:cover;">
                <div class="carousel-caption d-md-block text-left"
                    style="top: 45px !important; background-size: cover; height: 100vh;">
                    <a href="https://odogwufest.com/landing.html#section-ticket">
                        <h2 data-bs-target="#carouselExampleCaptions"
                            class="wow fadeIn animated"
                            style="font-size: 40px; visibility: visible; animation-name: fadeIn;">TICKETS</h2>        
                    </a><br>
                    <a href="https://odogwufest.com/landing.html#section-merch">
                        <h2 data-bs-target="#carouselExampleCaptions"
                            class="wow fadeIn animated"
                            style="font-size: 40px; visibility: visible; animation-name: fadeIn;">MERCH</h2>
                    </a><br>

                    <a href="https://odogwufest.com/landing.html#section-sponsors">
                        <h2 data-bs-target="#carouselExampleCaptions"
                            class="wow fadeIn animated"
                            style="font-size: 40px; visibility: visible; animation-name: fadeIn;">STALLS & STANDS</h2>
                    </a>
                    <br>

                    <a href="https://odogwufest.com/landing.html#section-register">
                    <h2 data-bs-target="#carouselExampleCaptions"
                        class="wow fadeIn animated"
                        style="font-size: 40px; visibility: visible; animation-name: fadeIn; opacity: 500;">CONTACT
                        US</h2>
                    </a>
                    <br>
                    <a href="https://odogwufest.com/landing.html#section-about">
                        <h2 data-bs-target="#carouselExampleCaptions"
                            class="wow fadeIn animated"
                            style="font-size: 40px; visibility: visible; animation-name: fadeIn; opacity: 500;">ABOUT US
                        </h2>
                    </a>
                        
                        <br>

                </div>
            </div>
            <div class="carousel-item" data-bs-interval="5000" style="background-size: cover;">
                <img src="./web/slider/wide4.jpg" class="d-block w-100" alt="..."
                    style="height: 100vh; object-fit:cover;">
                <div class="carousel-caption d-md-block text-left"
                    style="top: 45px !important; background-size: cover; height: 100vh;">
                    <a href="https://odogwufest.com/landing.html#section-ticket">
                        <h2 data-bs-target="#carouselExampleCaptions"
                            class="wow fadeIn animated"
                            style="font-size: 40px; visibility: visible; animation-name: fadeIn;">TICKETS</h2>        
                    </a><br>
                    <a href="https://odogwufest.com/landing.html#section-merch">
                        <h2 data-bs-target="#carouselExampleCaptions"
                            class="wow fadeIn animated"
                            style="font-size: 40px; visibility: visible; animation-name: fadeIn;">MERCH</h2>
                    </a><br>

                    <a href="https://odogwufest.com/landing.html#section-sponsors">
                        <h2 data-bs-target="#carouselExampleCaptions"
                            class="wow fadeIn animated"
                            style="font-size: 40px; visibility: visible; animation-name: fadeIn;">STALLS & STANDS</h2>
                    </a>
                    <br>

                    <a href="https://odogwufest.com/landing.html#section-register">
                    <h2 data-bs-target="#carouselExampleCaptions"
                        class="wow fadeIn animated"
                        style="font-size: 40px; visibility: visible; animation-name: fadeIn; opacity: 500;">CONTACT
                        US</h2>
                    </a>
                    <br>
                    <a href="https://odogwufest.com/landing.html#section-about">
                        <h2 data-bs-target="#carouselExampleCaptions"
                            class="wow fadeIn animated"
                            style="font-size: 40px; visibility: visible; animation-name: fadeIn; opacity: 500;">ABOUT US
                        </h2>
                    </a>
                        
                        <br>

                </div>
            </div>

        </div>

    </div>
    
@endsection