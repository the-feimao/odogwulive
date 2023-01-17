@extends('app')

@section('content')
        
        
        <!-- header begin -->
        <header class="transparent header-mobile">
            <div class="info" style="background-size: cover;">
                <div class="container" style="background-size: cover;">
                    <div class="row" style="background-size: cover;">
                        <div class="col-md-12" style="background-size: cover;">
                            <div class="column" style="background-size: cover;">Working Hours Monday - Friday <span
                                    class="id-color"><strong>08:00-16:00</strong></span></div>
                            <div class="column" style="background-size: cover;">Toll Free <span
                                    class="id-color"><strong>1800.899.900</strong></span></div>
                            <!-- social icons -->
                            <div class="column social" style="background-size: cover;">
                                <a href="#"><i class="fa fa-facebook"></i></a>
                                <a href="#"><i class="fa fa-twitter"></i></a>
                                <a href="#"><i class="fa fa-rss"></i></a>
                                <a href="#"><i class="fa fa-google-plus"></i></a>
                                <a href="#"><i class="fa fa-envelope-o"></i></a>
                            </div>
                            <!-- social icons close -->
                        </div>
                    </div>
                </div>
            </div>

            <div class="container" style="background-size: cover;">
                <div class="row" style="background-size: cover;">
                    <div class="col-md-12" style="background-size: cover;">
                        <!-- logo begin -->
                        <div id="logo" style="background-size: cover;">
                            <a href="{{url('/')}}">
                                <img class="logo" src="./web/logo.png" style="width: 72px;" alt="">
                            </a>
                        </div>
                        <!-- logo close -->

                        <!-- small button begin -->
                        <span id="menu-btn" class="unclick"></span>
                        <!-- small button close -->

                        <div class="header-extra" style="background-size: cover;">
                            <div class="v-center" style="background-size: cover;">
                                <span id="b-menu">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </span>
                            </div>
                        </div>

                        <!-- mainmenu begin -->
                        <ul id="mainmenu" class="ms-2 dotted-separator" style="display: none;">
                            <li><a href="https://odogwufest.com/landing.html#section-ticket">Ticket<span></span></a></li>
                            <li><a href="https://odogwufest.com/landing.html#section-merch">Merch<span></span></a></li>
                            <li><a href="https://odogwufest.com/landing.html#section-sponsors">Stalls & Stands<span></span></a></li>
                            <li><a href="https://odogwufest.com/landing.html#section-register">Contact Us<span></span></a></li>
                            <li><a href="https://odogwufest.com/landing.html#section-about">About Us<span></span></a></li>
                      
                            </ul>

                        <!-- mainmenu close -->



                    </div>


                </div>
            </div>
        </header>
        <!-- header close -->


        <!-- content begin -->
        <div id="content" class="no-bottom no-top" style="background-size: cover;">

            <!-- revolution slider begin -->
            <!-- initial slider -->
            <!-- revolution slider close -->

            <section id="section-countdown" aria-label="section" class="gradient-to-right pt40 pb40" style="padding-top: 100px;">
                <div class="container">
                    <div class="row">
                        <div class="col-md-10 offset-md-1">
                            <div class="spacer-single"></div>
                            <div class="col-md-6 offset-md-3 text-center wow fadeInUp">
                                <h1>29th December 2022 <br> Odogwu Fest:</h1>
                                <div class="spacer-single"></div>
                            </div>
                            <div id="defaultCountdown"></div>

                        </div>
                    </div>
                </div>
            </section>

            <!-- section begin -->
            <section id="section-about" data-bgimage=""
                style="background: url(&quot;web/bg/1.jpg&quot;) 0% 0% / cover no-repeat fixed;">
                <div class="wm wm-border dark wow fadeInDown animated"
                    style="visibility: visible; animation-name: fadeInDown; background-size: cover;">welcome</div>
                <div class="container" style="background-size: cover;">
                    <div class="row align-items-center" style="background-size: cover;">

                        <div class="col-lg-6 wow fadeInLeft animated" data-wow-delay="0s"
                            style="visibility: visible; animation-delay: 0s; animation-name: fadeInLeft; background-size: cover;">
                            <h2>The Annual <span style="color: #ffa500;">Odogwu Fest</span> </h2>
                            <p>
                                Odogwu Fest is concert that delivers more than music. We deliver an enthralling
                                production, fun and comfortable atmosphere, amazing artiste line up and quality
                                performance.
                                Over all, Odogwu Fest leaves an imprint of an unforgettable memory onto the guests that
                                attend.

                            </p>

                            <div class="spacer10" style="background-size: cover;"></div>

                            <a href="#" class="btn-custom font-weight-bold text-white sm-mb-30">About Us</a>
                        </div>

                        <div class="col-lg-6 mb-sm-30 text-center wow fadeInRight animated"
                            style="visibility: visible; animation-name: fadeInRight; background-size: cover;">
                            <div class="de-images" style="background-size: cover;">
                                <img class="di-small wow fadeIn animated" src="web/misc/2.jpg" alt=""
                                    style="visibility: visible; animation-name: fadeIn;">
                                <img class="di-small-2 wow fadeIn animated" src="web/misc/3.jpg" alt=""
                                    style="visibility: visible; animation-name: fadeIn;">
                                <img class="di-big img-fluid wow fadeInRight animated" data-wow-delay=".25s"
                                    src="web/misc/1.jpg" alt=""
                                    style="visibility: visible; animation-delay: 0.25s; animation-name: fadeInRight;">
                            </div>
                        </div>

                    </div>
                </div>
            </section>

           

           <section id="section-ticket" class="jarallax text-light"
            style="position: relative; z-index: 0; background-size: cover; width: 100%;">
        
                <div class="wm wm-border dark wow fadeInDown animated"
                    style="visibility: visible; animation-name: fadeInDown; background-size: cover;"></div>
                <div class="container" style="background-size: cover;">
                    {{--content here  --}}
                    <div class="row" style="background-size: cover;">
                        <div class="col-md-6 offset-md-3 text-center wow fadeInUp animated"
                            style="visibility: visible; animation-name: fadeInUp; background-size: cover;">
                            <h1>Upcoming Events</h1>
                            <div class="separator" style="background-size: cover;"><span><i class="fa fa-square"></i></span>
                            </div>
                            <div class="spacer-single" style="background-size: cover;"></div>
                        </div>
        
                        @foreach ($events as $item)
                        @if($loop->iteration <= 6) 
                        <div class="card border-primary mb-4 text-center hoverable">
                            <div class="card-body">
                                <!--Grid row-->
                                <div class="row">
        
                                    <!--Grid column-->
                                    <div class="col-md-4 offset-md-1 mx-3 my-3 text-center">
                                        <!--Featured image-->
                                        <img src="{{url('images/upload/'.$item->image)}}" class="img-fluid"
                                            alt="Sample image for first version of blog listing">
                                    </div>
                                    <!--Grid column-->
        
                                    <!--Grid column-->
                                    <div class="col-md-7 text-left ml-3 mt-3">
                                        <!--Excerpt-->
                                        <a href="" class="green-text">
                                            @if(Auth::guard('appuser')->check())
                                            <button type="button" onclick="addFavorite({{$item->id}},'event')" class="btn p-0">
                                                <i class="fa fa-heart {{in_array($item->id,array_filter(explode(',',Auth::guard('appuser')->user()->favorite)))==true?'active':''}}"></i>
                                            </button>
                                            @else
                                            <button type="button" class="btn p-0"> <a href="{{url('user/login')}}">
                                                <i class="fa fa-heart"></i></a>
                                            </button>
                                            @endif
                                        </a>
                                        <h4 class="mb-4"><strong>{{$item->name}}</strong></h4>
                                        <p>{!!$item->description!!}</p>
                                        <p>{{$item->start_time->format('l').', '.$item->start_time->format('d F Y')}}</p>
        
        
                                        <a href="   {{url('event/'.$item->id.'/'.preg_replace('/\s+/', '-', $item->name))}}"
                                            class="btn btn-lg btn-warning">Book Now</a>
                                    </div>
        
                                    <!--Grid column-->
                                </div>
                                <!--Grid row-->
                            </div>
                        </div>
                        @endif
                        @endforeach  
                </div>
                
            
                <div id="jarallax-container-1" style="
                    position: absolute; 
                    top: 0px; 
                    left: 0px; 
                    width: 100%; 
                    height: 100%; 
                    overflow: hidden;
                    z-index: -100; 
                    clip-path: polygon(0px 0px, 100% 0px, 100% 100%, 0px 100%); 
                    background-size: cover;">
                    <img src="{{asset('web/bg/2.jpg')}}" class="jarallax-img" alt="" style="object-fit: cover; 
                        object-position: 50% 50%; 
                        max-width: 100%; position: absolute; 
                        top: 0px; 
                        left: 0px; 
                        width: 100%; 
                        height: 1652.2px; 
                        overflow: hidden; 
                        pointer-events: none; 
                        transform-style: preserve-3d; 
                        backface-visibility: hidden; 
                        will-change: transform, opacity; 
                        margin-top: -387.1px; 
                        transform: translate3d(0px, -11.725px, 0px);">
                </div>
            </section>
            <!-- section close -->

             <!-- section begin -->

             <section id="section-merch" class="jarallax text-light"
             style="position: relative; z-index: 0; background-size: cover;">

             <div class="wm wm-border dark wow fadeInDown animated"
                 style="visibility: visible; animation-name: fadeInDown; background-size: cover;">Merchandise</div>
             <div class="container" style="background-size: cover;">
                 <div class="row" style="background-size: cover;">
                     <div class="col-md-6 offset-md-3 text-center wow fadeInUp animated"
                         style="visibility: visible; animation-name: fadeInUp; background-size: cover;">
                         <h1>Merch</h1>
                         <div class="separator" style="background-size: cover;"><span><i
                                     class="fa fa-square"></i></span></div>
                         <div class="spacer-single" style="background-size: cover;"></div>
                     </div>

                     <div class="clearfix" style="background-size: cover;"></div>

                     <div class="col-xl-3 col-lg-3 col-sm-6 mb30 wow fadeInUp animated"
                         style="visibility: visible; animation-name: fadeInUp; background-size: cover;">
                         <!-- team member -->
                         <div class="de-team-list" style="background-size: cover;">
                             <div class="team-pic" style="background-size: cover;">
                                 <img src="web/team/1.jpg" class="img-responsive" alt="">
                             </div>
                             <div class="team-desc" style="background-size: cover;">
                                 <p class="lead">Odogwu fest</p>
                                 <div class="small-border" style="background-size: cover;"></div>
                                 <p>Leave you with an imprint of an unforgettable memory onto the guests that attend.
                                 </p>

                                 <div class="social" style="background-size: cover;">
                                     <a href="https://facebook.com/Odogwufest"><i
                                             class="fa fa-facebook fa-lg"></i></a>
                                     <a href="https://twitter.com/Odogwufest" target="_blank"><i
                                             class="fa fa-twitter fa-lg"></i></a>
                                     <a href="https://www.instagram.com/Odogwufest/" target="_blank"><i
                                             class="fa fa-instagram fa-lg"></i></a>

                                 </div>
                             </div>
                         </div>
                         <!-- team close -->
                     </div>

                     <div class="col-xl-3 col-lg-3 col-sm-6 mb30 wow fadeInUp animated"
                         style="visibility: visible; animation-name: fadeInUp; background-size: cover;">
                         <!-- team member -->
                         <div class="de-team-list" style="background-size: cover;">
                             <div class="team-pic" style="background-size: cover;">
                                 <img src="web/team/2.jpg" class="img-responsive" alt="">
                             </div>
                             <div class="team-desc" style="background-size: cover;">
                                 <p class="lead">Odogwu fest</p>
                                 <div class="small-border" style="background-size: cover;"></div>
                                 <p>Leave you with an imprint of an unforgettable memory onto the guests that attend.
                                 </p>

                                 <div class="social" style="background-size: cover;">
                                     <a href="https://facebook.com/Odogwufest"><i
                                             class="fa fa-facebook fa-lg"></i></a>
                                     <a href="https://twitter.com/Odogwufest" target="_blank"><i
                                             class="fa fa-twitter fa-lg"></i></a>
                                     <a href="https://www.instagram.com/Odogwufest/" target="_blank"><i
                                             class="fa fa-instagram fa-lg"></i></a>

                                 </div>
                             </div>
                         </div>
                         <!-- team close -->
                     </div>

                     <div class="col-xl-3 col-lg-3 col-sm-6 mb30 wow fadeInUp animated"
                         style="visibility: visible; animation-name: fadeInUp; background-size: cover;">
                         <!-- team member -->
                         <div class="de-team-list" style="background-size: cover;">
                             <div class="team-pic" style="background-size: cover;">
                                 <img src="web/team/3.jpg" class="img-responsive" alt="">
                             </div>
                             <div class="team-desc" style="background-size: cover;">

                                 <p class="lead">Odogwu fest</p>
                                 <div class="small-border" style="background-size: cover;"></div>
                                 <p>Leave you with an imprint of an unforgettable memory onto the guests that attend.
                                 </p>

                                 <div class="social" style="background-size: cover;">
                                     <a href="https://facebook.com/Odogwufest"><i
                                             class="fa fa-facebook fa-lg"></i></a>
                                     <a href="https://twitter.com/Odogwufest" target="_blank"><i
                                             class="fa fa-twitter fa-lg"></i></a>
                                     <a href="https://www.instagram.com/Odogwufest/" target="_blank"><i
                                             class="fa fa-instagram fa-lg"></i></a>

                                 </div>
                             </div>
                         </div>
                         <!-- team close -->
                     </div>

                     <div class="col-xl-3 col-lg-3 col-sm-6 mb30 wow fadeInUp animated"
                     style="visibility: visible; animation-name: fadeInUp; background-size: cover;">
                     <!-- team member -->
                     <div class="de-team-list" style="background-size: cover;">
                         <div class="team-pic" style="background-size: cover;">
                             <img src="web/team/4.jpg" class="img-responsive" alt="">
                         </div>
                         <div class="team-desc" style="background-size: cover;">

                             <p class="lead">Odogwu fest</p>
                             <div class="small-border" style="background-size: cover;"></div>
                             <p>Leave you with an imprint of an unforgettable memory onto the guests that attend.
                             </p>

                             <div class="social" style="background-size: cover;">
                                 <a href="https://facebook.com/Odogwufest"><i
                                         class="fa fa-facebook fa-lg"></i></a>
                                 <a href="https://twitter.com/Odogwufest" target="_blank"><i
                                         class="fa fa-twitter fa-lg"></i></a>
                                 <a href="https://www.instagram.com/Odogwufest/" target="_blank"><i
                                         class="fa fa-instagram fa-lg"></i></a>

                             </div>
                         </div>
                     </div>

                     
                     <!-- team close -->
                 </div>

                     <div class="clearfix" style="background-size: cover;"></div>

                 </div>
             </div>
             <div id="jarallax-container-0"
                 style="position: absolute; top: 0px; left: 0px; width: 100%; height: 100%; overflow: hidden; z-index: -100; clip-path: polygon(0px 0px, 100% 0px, 100% 100%, 0px 100%); background-size: cover;">
                 <img src="web/bg/1.jpg" class="jarallax-img" alt=""
                     style="object-fit: cover; object-position: 50% 50%; max-width: none; position: absolute; top: 0px; left: 0px; width: 626.4px; height: 962.694px; overflow: hidden; pointer-events: none; transform-style: preserve-3d; backface-visibility: hidden; will-change: transform, opacity; margin-top: -42.3469px; transform: translate3d(0px, -359.709px, 0px);">
             </div>
         </section>
         <!-- section close -->



            <!-- section begin -->
            <section id="section-sponsors" data-bgimage=""
                style="background: url(&quot;web/bg/3.jpg&quot;) center center / cover no-repeat fixed;">
                <div class="wm wm-border dark wow fadeInDown "
                    style="visibility: hidden; animation-name: none; background-size: cover;">sponsors</div>
                <div class="container" style="background-size: cover;">
                    <div class="row" style="background-size: cover;">
                        <div class="col-md-6 offset-md-3 text-center wow fadeInUp"
                            style="visibility: hidden; animation-name: none; background-size: cover;">
                            <h1>Official Sponsors</h1>
                            <div class="separator" style="background-size: cover;"><span><i
                                        class="fa fa-square"></i></span></div>
                            <div class="spacer-single" style="background-size: cover;"></div>
                        </div>

                        <div class="col-md-12 text-center wow fadeInUp"
                            style="visibility: hidden; animation-name: none; background-size: cover;">

                            <div class="spacer-single" style="background-size: cover;"></div>
                            <img src="web/logo/1.png" alt="" class="grey-hover">
                            <img src="web/logo/3b.png" alt="" class="grey-hover">

                            <div class="spacer-double" style="background-size: cover;"></div>


                        </div>
                    </div>
                </div>
            </section>
            <!-- section close -->

            <!-- section begin -->
            <section id="section-ticket-print" class="no-top no-bottom" aria-label="section"
                style="background-size: cover;">
                <div id="gallery" class="gallery zoom-gallery full-gallery de-gallery pf_full_width wow fadeInUp"
                    data-wow-delay=".3s"
                    style="visibility: hidden; animation-delay: 0.3s; animation-name: none; background-size: cover; position: relative; height: 202.524px;">

                    <!-- gallery item -->
                    <div class="item residential"
                        style="background-size: cover; position: absolute; left: 0px; top: 0px;">
                        <div class="picframe" style="background-size: cover;">
                            <a href="web/portfolio/1.jpg" title="Event Photo">
                                <span class="overlay" style="opacity: 0;">
                                    <span class="pf_text">
                                        <span class="project-name">View Image</span>
                                    </span>
                                </span>
                                <img src="web/portfolio/1.jpg" alt="">
                            </a>
                        </div>
                    </div>
                    <!-- close gallery item -->

                    <!-- gallery item -->
                    <div class="item hospitaly"
                        style="background-size: cover; position: absolute; left: 312px; top: 0px;">
                        <div class="picframe" style="background-size: cover;">
                            <a href="web/portfolio/2.jpg" title="Event Photo">
                                <span class="overlay" style="opacity: 0;">
                                    <span class="pf_text">
                                        <span class="project-name">View Image</span>
                                    </span>
                                </span>
                                <img src="web/portfolio/2.jpg" alt="">
                            </a>
                        </div>
                    </div>
                    <!-- close gallery item -->

                    <!-- gallery item -->
                    <div class="item hospitaly"
                        style="background-size: cover; position: absolute; left: 312px; top: 101px;">
                        <div class="picframe" style="background-size: cover;">
                            <a href="web/portfolio/3.jpg" title="Event Photo">
                                <span class="overlay" style="opacity: 0;">
                                    <span class="pf_text">
                                        <span class="project-name">View Image</span>
                                    </span>
                                </span>
                                <img src="web/portfolio/3.jpg" alt="">
                            </a>
                        </div>
                    </div>
                    <!-- close gallery item -->

                    <!-- gallery item -->
                    <div class="item residential"
                        style="background-size: cover; position: absolute; left: 0px; top: 101px;">
                        <div class="picframe" style="background-size: cover;">
                            <a href="web/portfolio/4.jpg" title="Event Photo">
                                <span class="overlay" style="opacity: 0;">
                                    <span class="pf_text">
                                        <span class="project-name">View Image</span>
                                    </span>
                                </span>
                                <img src="web/portfolio/4.jpg" alt="">
                            </a>
                        </div>
                    </div>
                    <!-- close gallery item -->
                </div>
            </section>
            <!-- section close -->

            <!-- section begin -->
            <section id="section-register" style="background-size: cover;">
                <div class="wm wm-border dark wow fadeInDown"
                    style="visibility: hidden; animation-name: none; background-size: cover;">ContactUs</div>
                <div class="container" style="background-size: cover;">
                    <div class="row" style="background-size: cover;">
                        <div class="col-md-6 offset-md-3 text-center wow fadeInUp"
                            style="visibility: hidden; animation-name: none; background-size: cover;">
                            <h1>Make an Enquiry</h1>
                            <div class="separator" style="background-size: cover;"><span><i
                                        class="fa fa-square"></i></span></div>
                            <div class="spacer-single" style="background-size: cover;"></div>
                        </div>

                        <div class="col-md-8 offset-md-2 wow fadeInUp"
                            style="visibility: hidden; animation-name: none; background-size: cover;">
                            <form name="contactForm" id="contact_form" method="post"
                                action="https://www.madebydesignesia.com/themes/exhibiz/email.php">
                                <div class="row" style="background-size: cover;">
                                    <div class="col-md-6" style="background-size: cover;">
                                        <div id="name_error" class="error" style="background-size: cover;">Please enter
                                            your name.</div>
                                        <div style="background-size: cover;">
                                            <input type="text" name="name" id="name" class="form-control"
                                                placeholder="Your Name">
                                        </div>

                                        <div id="email_error" class="error" style="background-size: cover;">Please enter
                                            your valid E-mail ID.</div>
                                        <div style="background-size: cover;">
                                            <input type="text" name="email" id="email" class="form-control"
                                                placeholder="Your Email">
                                        </div>

                                        <div id="phone_error" class="error" style="background-size: cover;">Please enter
                                            your phone number.</div>
                                        <div style="background-size: cover;">
                                            <input type="text" name="phone" id="phone" class="form-control"
                                                placeholder="Your Phone">
                                        </div>
                                    </div>
                                    <div class="col-md-6" style="background-size: cover;">
                                        <div id="message_error" class="error" style="background-size: cover;">Please
                                            enter your message.</div>
                                        <div style="background-size: cover;">
                                            <textarea name="message" id="message" class="form-control"
                                                placeholder="Additional Message"></textarea>
                                        </div>
                                    </div>

                                    <div class="col-md-12 text-center" style="background-size: cover;">
                                        <p id="submit">
                                            <input type="submit" id="send_message" value="Submit"
                                                class="btn btn-line">
                                        </p>
                                        <div id="mail_success" class="success" style="background-size: cover;">Your
                                            message has been sent successfully.</div>
                                        <div id="mail_fail" class="error" style="background-size: cover;">Sorry, error
                                            occured this time sending your message.</div>
                                    </div>
                                </div>
                            </form>
                        </div>


                    </div>
                </div>

            </section>
            <!-- section close -->

            <!-- section begin -->
            <section id="call-to-action" class="gradient-to-right text-light call-to-action" aria-label="cta"
                style="background-size: cover;">
                <div class="container" style="background-size: cover;">
                    <div class="row align-items-center" style="background-size: cover;">
                        <div class="col-lg-8 col-md-7" style="background-size: cover;">
                            <h3 class="size-2 no-margin">Get your seat before the price goes up</h3>
                        </div>

                        <div class="col-lg-4 col-md-5 text-right" style="background-size: cover;">
                            <a href="#section-ticket" class="btn-custom text-white scroll-to">Get Ticket</a>
                        </div>
                    </div>
                </div>
            </section>
            <!-- logo carousel section close -->

            <!-- footer begin -->
           
        @include('web.footer')
            <!-- footer close -->
        </div>
@endsection