@extends('master')

@section('content')
<section class="section">
    @include('admin.layout.breadcrumbs', [
        'title' => __('Profile'),            
    ]) 

    <div class="section-body">
        <h2 class="section-title">{{ __('Hi,') }} {{Auth::user()->name}}!</h2>
        <p class="section-lead">
            {{__('Change information about yourself on this page.')}}
        </p>    
        <div class="row mt-sm-4">
            <div class="col-12">
                @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                @endif
            </div>
            <div class="col-12 col-md-12 col-lg-5">
              <div class="card profile-widget">
                <div class="profile-widget-header">
                  <img alt="image" src="{{url('images/upload/'.Auth::user()->image)}}" class="rounded-circle profile-widget-picture">
                  <div class="profile-widget-items">
                    @if(Auth::user()->hasRole('admin'))
                        <div class="profile-widget-item">
                            <div class="profile-widget-item-label">{{__('Organizations')}}</div>
                            <div class="profile-widget-item-value">{{App\Models\User::role('organization')->count()}}</div>
                        </div>
                    @endif
                    @if(Auth::user()->hasRole('organization'))
                        <div class="profile-widget-item">
                            <div class="profile-widget-item-label">{{__('Followers')}}</div>
                            <div class="profile-widget-item-value">{{count(Auth::user()->followers)}}</div>
                        </div>
                    @endif

                    <div class="profile-widget-item">
                      <div class="profile-widget-item-label">{{__('Events')}}</div>                      
                      <div class="profile-widget-item-value">
                        @if(Auth::user()->hasRole('admin'))
                        {{App\Models\Event::count()}}
                        @elseif(Auth::user()->hasRole('organization'))
                        {{App\Models\Event::where('user_id',Auth::user()->id)->count()}}
                        @endif
                        </div>
                    </div>
                  </div>
                </div>
                <div class="profile-widget-description">
                  <div class="profile-widget-name">{{Auth::user()->name}}</div>
                   <b>{{Auth::user()->email}}</b>
                   <p>{{Auth::user()->bio}}</p>
                </div>           
              </div>

              @if(Auth::user()->hasRole('organization'))
                
                @php 
                    $followers = \App\Models\AppUser::whereIn('id',Auth::user()->followers)->get();
                @endphp
                
                <div class="card mt-4">
                    <div class="card-header">
                    <h4>{{__('Followers ('.count($followers).')')}}</h4>
                    </div>
                    <div class="card-body scroll-type">
                        @if(count($followers)==0)

                        @else 
                            <ul class="list-unstyled list-unstyled-border">
                                @foreach ($followers as $item)
                                    <li class="media">
                                        <img class="mr-3 avatar" width="50" src="{{url('images/upload/'.$item->image)}}" alt="avatar">
                                        <div class="media-body">                                      
                                            <div class="media-title">{{$item->name.' '.$item->last_name}}</div>
                                            <span class="text-small text-muted">{{$item->email}}</span>
                                        </div>
                                    </li>                                                                  
                                @endforeach
                            </ul>
                        @endif                                                                              
                    </div>
                </div>
              @endif
            </div>
            <div class="col-12 col-md-12 col-lg-7">
              <div class="card mt-4">
                  <div class="card-header">
                    <h4>{{__('Edit Profile')}}</h4>
                  </div>
                  <div class="card-body">
                        <form method="post" class="needs-validation" action="{{url('edit-profile')}}">
                        @csrf
                            <div class="text-muted d-inline"><h6>{{__('Admin Information')}}</h6></div>
                            <div class="row">
                                <div class="form-group col-md-12 col-12">
                                    <label>{{__('Name')}}</label>
                                    <input type="text" name="name" class="form-control" value="{{Auth::user()->name}}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{$message}}</div>
                                    @enderror
                                </div>                         
                            </div>
                            <div class="row">
                                <div class="form-group col-md-12 col-12">
                                    <label>{{__('Email')}}</label>
                                    <input type="email" readonly name="email" class="form-control" value="{{Auth::user()->email}}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{$message}}</div>
                                    @enderror
                                </div>                                              
                            </div>
                            <div class="form-group">
                                <label>{{__('Language')}}</label>
                                <select name="language" class="form-control" required>
                                    @foreach ($languages as $item)
                                        <option value="{{$item->name}}" {{$item->name == old('language',Auth::user()->language) ? 'Selected' : ''}}>{{$item->name}}</option>   
                                    @endforeach
                                </select>
                                @error('language')
                                    <div class="invalid-feedback block">{{$message}}</div>
                                @endif
                            </div> 
                            <div class="form-group">
                                <label>{{__('Bio')}}</label>
                                <textarea name="bio" placeholder="{{ __('About you') }}" class="form-control @error('bio')? is-invalid @enderror">{{Auth::user()->bio}}</textarea>
                                @error('bio')
                                    <div class="invalid-feedback">{{$message}}</div>
                                @endif
                            </div>    
                          <div class="form-group text-right">
                              <button type="submit" class="btn btn-primary">{{__('Save Changes')}}</button>
                          </div>  
                      </form>    
                       <form method="post" class="needs-validation" action="{{url('change-password')}}">
                      @csrf
                          <div class="text-muted d-inline"><h6>{{__('Change Password')}}</h6></div>
                          <div class="row">
                              <div class="form-group col-md-12 col-12">
                                  <label>{{__('Current Password')}}</label>
                                  <input type="password" name="current_password" Placeholder="{{ __('Current Password') }}" class="form-control"  required>
                                  @error('current_password')
                                      <div class="invalid-feedback">{{$message}}</div>
                                  @enderror
                                      @if(Session::has('error_msg'))
                                          <span class="invalid-feedback block" >
                                              {{Session::get('error_msg')}}
                                          </span>
                                      @endif
                              </div>                         
                          </div>
                          <div class="row">
                              <div class="form-group col-md-12 col-12">
                                  <label>{{__('New Password')}}</label>
                                  <input type="password" name="password" placeholder="{{ __('New Password') }}" class="form-control" required>
                                  @error('password')
                                      <div class="invalid-feedback block">{{$message}}</div>
                                  @enderror
                              </div>                      
                          </div>
                           <div class="row">
                              <div class="form-group col-md-12 col-12">
                                  <label>{{__('Confirm Password')}}</label>
                                  <input type="password" name="confirm_password" Placeholder="{{ __('Confirm Password') }}" class="form-control"  required>
                                  @error('confirm_password')
                                      <div class="invalid-feedback block">{{$message}}</div>
                                  @enderror
                              </div>                         
                          </div>
                          <div class="form-group text-right">
                              <button type="submit" class="btn btn-primary">{{__('Changes')}}</button>
                          </div>  
                      </form>                              
                  </div>
                 
               
              </div>
            </div>
          </div>

        
    </div>
</section>
@endsection
