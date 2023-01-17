<html lang="en">

<head>
    <meta charset="utf-8">
    <link rel="icon" href="{{asset('web/logo.png')}}" type="image/gif" sizes="16x16">
    <title>Odogwu Fest</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="author" content="">

    <!-- CSS Files
    ================================================== -->
    <link href="{{asset('web/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css" id="bootstrap" />
    <link href="{{asset('web/css/bootstrap-grid.min.css')}}" rel="stylesheet" type="text/css" id="bootstrap-grid" />
    <link href="{{asset('web/css/bootstrap-reboot.min.css')}}" rel="stylesheet" type="text/css" id="bootstrap-reboot" />
    <link href="{{asset('web/css/plugins.css')}}" rel="stylesheet" type="text/css">
    <link href="{{asset('web/css/style.css')}}" rel="stylesheet" type="text/css">
    <link href="{{asset('web/css/rev-settings.css')}}" rel="stylesheet" type="text/css">
    <link href="{{asset('web/css/color.css')}}" rel="stylesheet" type="text/css">

    <!-- color scheme -->
    <link rel="stylesheet" href="{{asset('web/css/colors/magenta.css')}}" type="text/css" id="colors">

    <!-- RS5.0 Stylesheet -->
    <link rel="stylesheet" href="{{asset('web/revolution/css/settings.css')}}" type="text/css">
    <link rel="stylesheet" href="{{asset('web/revolution/css/layers.css')}}" type="text/css">
    <link rel="stylesheet" href="{{asset('web/revolution/css/navigation.css')}}" type="text/css">
    <link rel="stylesheet" href="{{asset('web/css/rev-settings.css')}}" type="text/css">

    <!-- custom font -->
    <link rel="stylesheet" href="{{asset('web/css/font-style.css')}}" type="text/css">

    <style>
        .carousel-caption>a>h2:hover {
            color: #ffa500;
        }
      .team-pic>img {
            height: 100%;
            object-fit: cover;
        }

        @media only screen and (max-width: 500px) {

            .carousel-caption>h2 {
                font-size: 25px !important;
            }

            .carousel-caption>a>h2 {
                font-size: 25px !important;
            }

        }

        .link-rapper {
            padding-left: 10%;
        }
    </style>
</head>

<body id="homepage">
    <div id="wrapper" style="background-size: cover;">
        <!-- header begin -->
        <header class="transparent header-mobile">
            <div class="info" style="background-size: cover;">
                <div class="container" style="background-size: cover;">
                    <div class="row" style="background-size: cover;">
                        <div class="col-md-12" style="background-size: cover;">
    
                            <!-- social icons -->
                            <div class="column social" style="background-size: cover;">
                                <a href="https://facebook.com/Odogwufest"><i class="fa fa-facebook fa-lg"></i></a>
                                <a href="https://twitter.com/Odogwufest" target="_blank"><i class="fa fa-twitter fa-lg"></i></a>
                                <a href="https://www.instagram.com/Odogwufest/" target="_blank"><i class="fa fa-instagram fa-lg"></i></a>
    
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
                                <img class="logo" src="{{asset('web/logo.png')}}" style="width: 72px;" alt="">
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
                            <li><a href="{{route('frontend.tickets')}}#section-ticket">Ticket<span></span></a></li>
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
        <!-- revolution slider begin -->
       @yield('slider')
        <!-- revolution slider close -->
        <!-- content begin -->
    </div>
 <div id="de-extra-wrap" class="de_light" style="background-size: cover;">
        <span id="b-menu-close">
            <span></span>
            <span></span>
        </span>
        <div class="de-extra-content" style="background-size: cover;">
            <h3>About Us</h3>
            <p>
                Odogwu Fest is concert that delivers more than music. We deliver an enthralling production, fun and
                comfortable atmosphere, amazing artiste line up and quality performance.
                Over all, Odogwu Fest leaves an imprint of an unforgettable memory onto the guests that attend.
            </p>

            <div class="spacer10" style="background-size: cover;"></div>

           

            <div class="spacer-single" style="background-size: cover;"></div>

            <h3>Where &amp; When?</h3>
            <div class="h6 padding10 pt0 pb0" style="background-size: cover;"><i
                    class="i_h3 fa fa-calendar-check-o id-color"></i>December 2022</div>
            <div class="h6 padding10 pt0 pb0" style="background-size: cover;"><i
                    class="i_h3 fa fa-map-marker id-color"></i>Lagos, Nigeria</div>
            <div class="h6 padding10 pt0 pb0" style="background-size: cover;"><i
                    class="i_h3 fa fa-envelope-o id-color"></i>info@odogwu.com</div>
        </div>
    </div>
    <div id="de-overlay" style="background-size: cover; display: none;"></div>

    @yield('content')
   
    <!-- Javascript Files
    ================================================== -->
    <script src="{{asset('web/js/plugins.js')}}"></script>
    <script src="{{asset('web/js/designesia.js')}}"></script>
    <script src="{{asset('web/js/validation.js')}}"></script>
    <script src="{{asset('web/js/countdown-custom.js')}}"></script>

    <!-- RS5.0 Core JS Files -->
    <script src="{{asset('web/revolution/js/jquery.themepunch.tools.min838f.js?rev=5.0')}}"></script>
    <script src="{{asset('web/revolution/js/jquery.themepunch.revolution.min838f.js?rev=5.0')}}"></script>

    <!-- RS5.0 Extensions Files -->
    <script src="{{asset('web/revolution/js/extensions/revolution.extension.video.min.js')}}"></script>
    <script src="{{asset('web/revolution/js/extensions/revolution.extension.slideanims.min.js')}}"></script>
    <script src="{{asset('web/revolution/js/extensions/revolution.extension.layeranimation.min.js')}}"></script>
    <script src="{{asset('web/revolution/js/extensions/revolution.extension.navigation.min.js')}}"></script>
    <script src="{{asset('web/revolution/js/extensions/revolution.extension.actions.min.js')}}"></script>
    <script src="{{asset('web/revolution/js/extensions/revolution.extension.kenburn.min.js')}}"></script>
    <script src="{{asset('web/revolution/js/extensions/revolution.extension.migration.min.js')}}"></script>
    <script src="{{asset('web/revolution/js/extensions/revolution.extension.parallax.min.js')}}"></script>
    <script>
        jQuery(document).ready(function () {
            // revolution slider
            jQuery("#slider-revolution").revolution({
                sliderType: "standard",
                sliderLayout: "fullwidth",
                delay: 2000,
                navigation: {
                    arrows: {
                        enable: false
                    },
                    bullets: {
                        enable: false,
                        style: 'hermes'
                    },

                },
                parallax: {
                    type: "mouse",
                    origo: "slidercenter",
                    speed: 2000,
                    levels: [2, 3, 4, 5, 6, 7, 12, 16, 10, 50],
                },
                spinner: "off",
                gridwidth: 1140,
                // gridheight: ,
                disableProgressBar: "off"
            });
        });
    </script>
</body>

</html>