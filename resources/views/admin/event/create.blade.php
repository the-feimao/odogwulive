@extends('master')

@section('content')
<section class="section">
    @include('admin.layout.breadcrumbs', [
        'title' => __('Add Event'),  
        'headerData' => __('Event') ,
        'url' => 'events' ,          
    ]) 

    <div class="section-body">
        <div class="row">
            <div class="col-lg-8"><h2 class="section-title"> {{__('Add Event')}}</h2></div>            
        </div>       
       
        <div class="row">
            <div class="col-12">
              <div class="card">     
                <div class="card-body">
                    <form method="post" class="event-form" action="{{url('events')}}" enctype="multipart/form-data" >
                        @csrf
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group center">
                                    <label>{{__('Image')}}</label>
                                    <div id="image-preview" class="image-preview">
                                        <label for="image-upload" id="image-label"> <i class="fas fa-plus"></i></label>
                                        <input type="file" name="image" id="image-upload" />
                                    </div>
                                    @error('image')
                                        <div class="invalid-feedback block">{{$message}}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>{{__('Name')}}</label>
                                    <input type="text" name="name" value="{{old('name')}}" placeholder="{{__('Name')}}" class="form-control @error('name')? is-invalid @enderror">
                                    @error('name')
                                        <div class="invalid-feedback">{{$message}}</div>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label>{{__('Category')}}</label>
                                    <select name="category_id" class="form-control select2">
                                        <option value="">{{ __('Select Category') }}</option>   
                                        @foreach ($category as $item)
                                        <option value="{{$item->id}}" {{$item->id == old('category_id') ? 'Selected' : ''}}>{{$item->name}}</option>   
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <div class="invalid-feedback block">{{$message}}</div>
                                    @endif
                                </div> 
                            </div>
                        </div> 
                        
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>{{__('Start Time')}}</label>
                                    <input type="text" name="start_time" id="start_time" value="{{old('start_time')}}" placeholder="{{ __('Choose Start time') }}" class="form-control date @error('start_time')? is-invalid @enderror">
                                    @error('start_time')
                                        <div class="invalid-feedback">{{$message}}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>{{__('End Time')}}</label>
                                    <input type="text" name="end_time" id="end_time" value="{{old('end_time')}}" placeholder="{{ __('Choose End time') }}" class="form-control date @error('end_time')? is-invalid @enderror">
                                    @error('end_time')
                                        <div class="invalid-feedback">{{$message}}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @if(Auth::user()->hasRole('admin'))
                        <div class="form-group">
                            <label>{{__('Organization')}}</label>
                            <select name="user_id" required class="form-control select2" id="org-for-event">
                                <option  value="">{{__('Choose Organization')}}</option>
                                @foreach ($users as $item)
                                <option value="{{$item->id}}" {{$item->id==old('user_id')?'Selected':''}}>{{$item->first_name.' '.$item->last_name}}</option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{$message}}</div>
                            @endif
                        </div>
                        @endif
                        <div class="form-group">
                            <label>{{__('Scanner')}}</label>
                            <select name="scanner_id" required class="form-control scanner_id select2">
                                <option  value="">{{__('Choose Scanner')}}</option>
                                @foreach ($scanner as $item)
                                <option value="{{$item->id}}" {{$item->id==old('scanner_id')?'Selected':''}}>{{$item->first_name.' '.$item->last_name}}</option>
                                @endforeach
                            </select>
                            @error('scanner_id')
                                <div class="invalid-feedback">{{$message}}</div>
                            @endif
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>{{__('Maximum people will join in this event')}}</label>
                                    <input type="number" name="people" id="people" value="{{old('people')}}" placeholder="{{ __('Maximum people will join in this event') }}" class="form-control @error('people')? is-invalid @enderror">
                                    @error('people')
                                        <div class="invalid-feedback">{{$message}}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>{{__('status')}}</label>
                                    <select name="status" class="form-control select2">
                                        <option value="1">{{ __('Active') }}</option>
                                        <option value="0">{{ __('Inactive') }}</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{$message}}</div>
                                    @endif 
                                </div> 
                            </div>                           
                        </div>
                        <div class="form-group">
                            <label>{{__('Tags')}}</label>
                            <input type="text" name="tags" value="{{old('tags')}}" class="form-control inputtags @error('tags')? is-invalid @enderror">
                            @error('tags')
                                <div class="invalid-feedback">{{$message}}</div>
                            @endif
                        </div>
                        <div class="form-group">
                            <label>{{__('Description')}}</label>
                            <textarea name="description" Placeholder ="Description" class="textarea_editor @error('description')? is-invalid @enderror">
                                {{old('description')}}
                            </textarea>
                            @error('description')
                                <div class="invalid-feedback block">{{$message}}</div>
                            @endif
                        </div>
                        <h6 class="text-muted mt-4 mb-4">{{__('Location Detail')}}</h6>
                        <div class="form-group">                            
                            <div class="selectgroup">
                              <label class="selectgroup-item">
                                <input type="radio" name="type" {{old('type')=="online"? '' : 'checked'}} checked value="offline" class="selectgroup-input" checked="">
                                <span class="selectgroup-button">{{__('Venue')}}</span>
                              </label>
                              <label class="selectgroup-item">
                                <input type="radio" {{old('type')=="online"? 'checked' : ''}} name="type" value="online" class="selectgroup-input">
                                <span class="selectgroup-button">{{__('Online Event')}}</span>
                              </label>                                                       
                            </div>
                          </div>
                        <div class="location-detail {{old('type')=="online"? 'hide' : ''}}">
                            <div class="form-group">
                                <label>{{__('Event Address')}}</label>
                                <input type="text" name="address" id="address" value="{{old('address')}}" placeholder="{{ __('Event Address') }}" class="form-control @error('address')? is-invalid @enderror">
                                @error('address')
                                    <div class="invalid-feedback">{{$message}}</div>
                                @endif
                            </div>
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>{{__('Latitude')}}</label>
                                        <input type="text" name="lat" id="lat" value="{{old('lat')}}" placeholder="{{ __('Latitude') }}" class="form-control @error('lat')? is-invalid @enderror">
                                        @error('lat')
                                            <div class="invalid-feedback">{{$message}}</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>{{__('Longitude')}}</label>
                                        <input type="text" name="lang" id="lang" value="{{old('lang')}}" placeholder="{{ __('Longitude') }}" class="form-control @error('lang')? is-invalid @enderror">
                                        @error('lang')
                                            <div class="invalid-feedback">{{$message}}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">    
                            <div class="custom-switches-stacked mt-2">                               
                                <label class="custom-switch pl-0">
                                  <input type="radio" name="security" {{old('security')=="0"? '' : 'checked'}} value="1" class="custom-switch-input">
                                  <span class="custom-switch-indicator"></span>
                                  <span class="custom-switch-description">{{__('Public')}}</span>
                                </label>
                                <label class="custom-switch pl-0">
                                  <input type="radio" name="security"  {{old('security')=="0"? 'checked' : ''}} value="0" class="custom-switch-input">
                                  <span class="custom-switch-indicator"></span>
                                  <span class="custom-switch-description">{{__('Private')}}</span>
                                </label>
                              </div>                    
                        </div>
                        <div class="form-group">                            
                            <button type="submit" class="btn btn-primary">{{__('Submit')}}</button>                            
                        </div>
                    </form>
                </div>
              </div>
            </div>
          </div>
        </div>
    </section>
@endsection
