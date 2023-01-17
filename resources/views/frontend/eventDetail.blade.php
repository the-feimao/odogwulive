@extends('frontend.master', ['activePage' => 'event'])

@section('content')
 
    @include('frontend.layout.breadcrumbs', [
        'title' => $data->name,            
        'page' => __('Event Detail'),            
    ]) 
    
    <style>
       
    
    @media only screen and (max-width: 1024px){
        .property-single .owl-carousel .owl-item img {
                height: 260px !important;
            }
            .gallery-property {
           margin-bottom: 0.1rem; 
        }
    }
    </style>

  <section class="property-single nav-arrow-b">
    <div class="container">
      <div class="row">
          <div class="col-lg-12">
            @if (session('status'))
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                  {{ session('status') }}                 
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
            @endif
          </div>
          <div class="col-lg-12 col-md-12 col-sm-12" >
            <div id="property-single-carousel" class="owl-carousel owl-arrow gallery-property">
                <div class="carousel-item-b">
                  <img src="{{url('images/upload/'.$data->image)}}" style="object-fit:contain;" alt="">
                </div>
                @foreach (explode(',',$data->gallery) as $item)
                    <div class="carousel-item-b">
                        <img src="{{url('images/upload/'.$item)}}" style="object-fit:contain;" alt="">
                    </div> 
                @endforeach
              </div>
          </div>
        
    
        <div class="col-md-12">
            <div class="title-box-d">
                <h3 class="title-d">{{__('Tickets on sale')}}</h3>
            </div>
            <ul class="nav nav-pills-a nav-pills mb-4 section-t1" id="pills-tab" role="tablist">
                <li class="nav-item">
                  <a class="nav-link active" id="pills-video-tab" data-toggle="pill" href="#pills-video" role="tab" aria-controls="pills-video" aria-selected="true">{{__('Paid')}}</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" id="pills-plans-tab" data-toggle="pill" href="#pills-plans" role="tab" aria-controls="pills-plans" aria-selected="false">{{__('Free')}}</a>
                </li>               
            </ul>


            
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="pills-video" role="tabpanel" aria-labelledby="pills-video-tab">                  
                  <div class="row">

                    @if(count($data->paid_ticket)==1)
                      <div class="col-lg-12 text-center">
                        <div class="empty-state">
                          <img src="{{url('frontend/images/empty.png')}}">
                          <h6 class="mt-4"> {{__('No Paid Tickets found')}}!</h6>
                        </div>
                      </div>                     
                    @else
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
                    @endif   
                  </div>            
                </div>
                <div class="tab-pane fade" id="pills-plans" role="tabpanel" aria-labelledby="pills-plans-tab">
                  <div class="row">
                    @if(count($data->free_ticket)==0)
                      <div class="col-lg-12 text-center">
                        <div class="empty-state">
                          <img src="{{url('frontend/images/empty.png')}}">
                          <h6 class="mt-4"> {{__('No Free Tickets found')}}!</h6>
                        </div>
                      </div>                     
                    @else
                      @foreach ($data->free_ticket as $item)
                        <div class="col-lg-4">
                          <article class="ticket">
                            <header class="ticket__wrapper">
                              <div class="ticket__header">
                                <span>{{$item->ticket_number}}</span>
                                @if($item->type=="free")
                                <span>FREE</span>
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
                              <p class="mt-1 mb-1 text-center coupon-data">Sold Out</p>  
                              @else
                                @if(Auth::guard('appuser')->check())
                                  <a href="{{url('checkout/'.$item->id)}}"><button class="btn btn-a">{{__('Book Now')}}</button></a>
                                @else
                                  <a href="{{url('user/login')}}"><button class="btn btn-a">{{__('Book Now')}}</button></a>
                                @endif    
                              @endif                                                
                            </footer>
                          </article>
                        </div>                     
                      @endforeach
                    @endif
                  </div>                   
                </div>                            
            </div> 
            
        </div>
   

      </div>
    </div>
  </section>

@endsection