@extends('main')

@section('content')
<style>
    .hm-gradient {
    background-image: linear-gradient(to top, #f3e7e9 0%, #e3eeff 99%, #e3eeff 100%);
    }
    .darken-grey-text {
        color: #2E2E2E;
    }

    
</style>
      <!-- content begin -->
      <div id="content" class="no-bottom no-top" style="background-size: cover;">

        <!-- revolution slider begin -->
    
        <!-- section begin -->
        <section id="section-ticket" class="jarallax text-light"
            style="position: relative; z-index: 0; background-size: cover;">

            <div class="wm wm-border dark wow fadeInDown animated"
                style="visibility: visible; animation-name: fadeInDown; background-size: cover;">                                  
                 {{--  --}}
            </div>
            <div class="container" style="background-size: cover;">
                <div class="row" style="background-size: cover;">
                    <div class="col-md-6 offset-md-3 text-center wow fadeInUp animated"
                        style="visibility: visible; animation-name: fadeInUp; background-size: cover;">
                        <h1>Choose a Ticket</h1>
                        <div class="separator" style="background-size: cover;"><span><i
                                    class="fa fa-square"></i></span></div>
                        <div class="spacer-single" style="background-size: cover;"></div>
                    </div>
                    <p>test {{$data->paid_ticket}}</p>
                   
    
                    @foreach ($data->paid_ticket as $item)
                                       

                    <div class="col-lg-4">
                        <article class="ticket">
                          <header class="ticket__wrapper">
                            <div class="ticket__header">
                              <span>{{$item->ticket_number}}</span>
                              @if($item->type=="free")
                              <span>{{__('FREE')}}</span>
                              @else
                                <span>{{$currency.$item->price}}</span>
                              @endif     
                            </div>
                          </header>
                          <div class="ticket__divider">
                            <div class="ticket__notch"></div>
                            <div class="ticket__notch ticket__notch--right"></div>
                          </div>
                          <div class="ticket__body">
                            <section class="ticket__section">
                              <h3>{{$item->name}}</h3>
                              <p>{{$item->description}}</p>                            
                            </section>
                            <section class="ticket__section">
                              <h3>{{__('Sales')}}</h3>
                              <p class="mb-0"><span>{{__('Start')}} : </span>{{$item->start_time->format('Y-m-d h:i a')}}</p>
                              <p class="mb-0"><span>{{__('end')}} : </span>{{$item->end_time->format('Y-m-d h:i a')}}</p>
                              @if($item->available_qty <= 0)                                
                              @else
                                <p><span>{{__('Quantity')}} : </span>{{$item->available_qty.' pcs left'}}</p>                              
                              @endif
                            </section>                          
                          </div>
                          <footer class="ticket__footer text-right">  
                            @if($item->available_qty <= 0)
                            <p class="mt-1 mb-1 text-center coupon-data">{{__('Sold Out')}}</p>  
                            @else
                              @if(Auth::guard('appuser')->check())
                                <a href="{{url('checkout/'.$item->id)}}"><button class="btn btn-a" >{{__('Book Now')}}</button></a>
                              @else
                                <a href="{{url('user/login')}}"><button class="btn btn-a" >{{__('Book Now')}}</button></a>
                              @endif
                            @endif
                          </footer>
                        </article>
                      </div> 
                   
                    @endforeach

                   
                    <div class="col-md-4" style="background-size: cover;">
                        <div class="pricing-s1 mb30 wow fadeInUp animated"
                            style="visibility: visible; animation-name: fadeInUp; background-size: cover;">
                            <div class="top" style="background-size: cover;">
                                <h2>Regular(1 Days)</h2>
                                <p class="price"><span class="currency id-color">NGN</span> <b>10,000 </b></p>
                            </div>
                            <div class="bottom" style="background-size: cover;">
                                <ul>

                                    <li><i class="icon_check_alt2"></i>1 Day</li>

                                </ul>
                                <a href="#" class="btn btn-custom text-white">Buy Ticket</a>
                            </div>
                            <div class="ps1-deco" style="background-size: cover;"></div>
                        </div>
                    </div>
                    <div class="col-md-4" style="background-size: cover;">
                        <div class="pricing-s1 mb30 wow fadeInUp animated"
                            style="visibility: visible; animation-name: fadeInUp; background-size: cover;">
                            <div class="top" style="background-size: cover;">
                                <h2>Regular(2 Days)</h2>
                                <p class="price"><span class="currency id-color">NGN</span> <b>19,000 </b></p>
                            </div>
                            <div class="bottom" style="background-size: cover;">
                                <ul>
                                    <li><i class="icon_check_alt2"></i>2 Days</li>

                                </ul>
                                <a href="#" class="btn btn-custom text-white">Buy Ticket</a>
                            </div>
                            <div class="ps1-deco" style="background-size: cover;"></div>
                        </div>
                    </div>
                    <div class="col-md-4" style="background-size: cover;">
                        <div class="pricing-s1 mb30 wow fadeInUp animated"
                            style="visibility: visible; animation-name: fadeInUp; background-size: cover;">
                            <div class="top" style="background-size: cover;">
                                <h2>VIP</h2>
                                <p class="price"><span class="currency id-color">NGN</span> <b>1.2M</b></p>
                            </div>
                            <div class="bottom" style="background-size: cover;">
                                <ul>
                                    <li><i class="icon_check_alt2"></i>Table For 2</li>
                                    <li><i class="icon_check_alt2"></i>2 Days</li>
                                </ul>
                                <a href="#" class="btn btn-custom text-white">Buy Ticket</a>
                            </div>
                            <div class="ps1-deco" style="background-size: cover;"></div>
                        </div>
                    </div>
                    <div class="col-md-4" style="background-size: cover;">
                        <div class="pricing-s1 mb30 wow fadeInUp"
                            style="visibility: hidden; animation-name: none; background-size: cover;">
                            <div class="ribbon color-2 text-white" style="background-size: cover;">Recommend</div>
                            <div class="top" style="background-size: cover;">
                                <h2>Deluxe</h2>
                                <p class="price"><span class="currency id-color">NGN</span> <b>5M</b></p>
                            </div>
                            <div class="bottom" style="background-size: cover;">
                                <ul>
                                    <li><i class="icon_check_alt2"></i>Table For 2</li>
                                    <li><i class="icon_check_alt2"></i>5 Days</li>
                                </ul>
                                <a href="#" class="btn btn-custom text-white">Buy Ticket</a>
                            </div>
                            <div class="ps1-deco" style="background-size: cover;"></div>
                        </div>
                    </div>
                    <div class="col-md-4" style="background-size: cover;">
                        <div class="pricing-s1 mb30 wow fadeInUp"
                            style="visibility: hidden; animation-name: none; background-size: cover;">
                            <div class="top" style="background-size: cover;">
                                <h2>Platinum</h2>
                                <p class="price"><span class="currency id-color">NGN</span> <b>10M</b></p>
                            </div>
                            <div class="bottom" style="background-size: cover;">
                                <ul>
                                    <li><i class="icon_check_alt2"></i>Table For 10</li>
                                    <li><i class="icon_check_alt2"></i>2 Days</li>
                                </ul>
                                <a href="#" class="btn btn-custom text-white">Buy Ticket</a>
                            </div>
                            <div class="ps1-deco" style="background-size: cover;"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="jarallax-container-1"
                style="position: absolute; top: 0px; left: 0px; width: 100%; height: 100%; overflow: hidden; z-index: -100; clip-path: polygon(0px 0px, 100% 0px, 100% 100%, 0px 100%); background-size: cover;">
                <img src="images/bg/2.jpg" class="jarallax-img" alt=""
                    style="object-fit: cover; object-position: 50% 50%; max-width: none; position: absolute; top: 0px; left: 0px; width: 626.4px; height: 1652.2px; overflow: hidden; pointer-events: none; transform-style: preserve-3d; backface-visibility: hidden; will-change: transform, opacity; margin-top: -387.1px; transform: translate3d(0px, -11.725px, 0px);">
            </div>
        </section>
        <!-- section close -->


        <!-- footer begin -->
        
        @include('web.footer')
        <!-- footer close -->
    </div>
@endsection