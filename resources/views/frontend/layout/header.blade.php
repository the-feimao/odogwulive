<nav class="navbar navbar-default navbar-trans navbar-expand-lg top-header fixed-top {{url()->current() == url('/')?'':'navbar-another'}}">
    <div class="container">       
      <a class="navbar-brand text-brand" href="{{url('/')}}">
        <img src="{{url('frontend/images/logo.png')}}" style="width:50px;">
      </a>   
      {{-- <div class="navbar-collapse collapse justify-content-center">     
        <div class="search-location">
          <input type="text" name="location" id="search_address" placeholder="{{__('Search Location')}}">
          <i class="fa fa-map-marker"></i>
        </div>
      </div>
      <button type="button" class="btn btn-b-n navbar-toggle-box-collapse d-none d-md-block search-btn" data-bs-toggle="collapse" data-bs-target="#navbarTogglerDemo01" aria-expanded="false">
        <span class="fa fa-search" aria-hidden="true"></span>
      </button> --}}
      @if(Auth::guard('appuser')->check())  
        <div class="dropdown ml-2 profileDropdown"> 
            <a class="dropdown-toggle" href="javascript:void(0)" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <img class="header-profile-img" src="{{url('images/upload/'.Auth::guard('appuser')->user()->image)}}">
            </a>
            <div class="dropdown-menu" aria-labelledby="profileDropdown">
              <a class="dropdown-item" href="{{url('user/profile')}}">{{__('Profile')}}</a>
              <a class="dropdown-item" href="{{url('my-tickets')}}">{{__('My Tickets')}}</a>
              <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();
              document.getElementById('logout-form').submit();">{{__('Logout')}}</a>
              <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
              </form>
            </div>
        </div>
        @else 

      @endif
         <a class="nav-link" href="{{url('user/login')}}"><i class="fa fa-lock"></i>{{__('Sign in')}}</a>

    </div>
</nav>

{{-- second nav was here --}}
  
  @if(url()->current() == url('/'))
  
  <?php $banner = App\Models\Banner::where('status',1)->orderBy('id','DESC')->get(); ?>
    <div class="intro intro-carousel">
      <div id="carousel" class="owl-carousel owl-theme">
        @foreach ($banner as $item)
          <div class="carousel-item-a intro-item bg-image" style="background-image: url({{url('images/upload/'.$item->image)}})">
            <div class="overlay overlay-a"></div>
            <div class="intro-content display-table">
              <div class="table-cell">
                <div class="container">
                  <div class="row">
                    <div class="col-lg-8">
                      <div class="intro-body">                  
                        <h1 class="intro-title mb-4">
                          <span class="color-b">{{explode(' ',$item->title)[0]}} </span>
                          @foreach (explode(' ',$item->title) as $item)
                            {{$loop->iteration>1?$item:''}}
                          @endforeach
                        </h1>
                        <p class="intro-subtitle intro-price">
                          <a href="{{url('all-events')}}"><span class="price-a">{{__('Book Now')}}</span></a>
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>    
        @endforeach             
      </div>
    </div>

  @endif
