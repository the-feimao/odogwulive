@extends('master')

@section('content')
<section class="section">
    @include('admin.layout.breadcrumbs', [
        'title' => __('Add User'),  
        'headerData' => __('Users') ,
        'url' => 'app-user' ,          
    ]) 

    <div class="section-body">
        <div class="row">
            <div class="col-lg-8"><h2 class="section-title"> {{__('Add User')}}</h2></div>            
        </div>       
       
        <div class="row">
            <div class="col-12">
              <div class="card">     
                <div class="card-body">
                    <form method="post" action="{{url('app-user')}}" enctype="multipart/form-data" >
                        @csrf
                      
                        <div class="row">
                            <div class="col-lg-3">
                                <div class="form-group center">
                                    <label>{{__('Image')}}</label>
                                    <div id="image-preview" class="image-preview">
                                        <label for="image-upload" id="image-label"> <i class="fas fa-plus"></i></label>
                                        <input type="file" name="image" id="image-upload" />
                                    </div>
                                    @error('image')
                                        <div class="invalid-feedback">{{$message}}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-lg-9">
                                <div class="form-group">
                                    <label>{{__('Full Name')}}</label>
                                    <input type="text" name="name" placeholder={{__('Full Name')}}alue="{{old('name')}}" class="form-control @error('name')? is-invalid @enderror">
                                    @error('name')
                                        <div class="invalid-feedback">{{$message}}</div>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label>{{__('Email')}}</label>
                                    <input type="email" name="email" placeholder="{{__('Email')}}" value="{{old('email')}}" class="form-control @error('email')? is-invalid @enderror">
                                    @error('email')
                                        <div class="invalid-feedback">{{$message}}</div>
                                    @endif
                                </div>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__('Password')}}</label>
                                            <input type="password" name="password" placeholder="{{__('Password')}}" class="form-control @error('password')? is-invalid @enderror">
                                            @error('password')
                                                <div class="invalid-feedback">{{$message}}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label>{{__('status')}}</label>
                                            <select name="status" class="form-control select2">
                                                <option value="1">{{ __('Active') }}</option>   
                                                <option value="0" {{old('status')=="0"?'selected':''}}>{{ __('Block') }}</option>   
                                            </select>
                                            @error('status')
                                                <div class="invalid-feedback">{{$message}}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>                                                               
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
