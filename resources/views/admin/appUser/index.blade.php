@extends('master')

@section('content')
<section class="section">
    @include('admin.layout.breadcrumbs', [
        'title' => __('Attendees'),            
    ]) 

    <div class="section-body">
  
       
        <div class="row">
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
            <div class="col-12">
              <div class="card">     
                <div class="card-body">
                    <div class="row mb-4 mt-2">
                        <div class="col-lg-8"><h2 class="section-title mt-0"> {{__('View Attendees')}}</h2></div>
                        <div class="col-lg-4 text-right">
                            @can('app_user_create')
                            <button class="btn btn-primary add-button"><a href="{{url('app-user/create')}}"><i class="fas fa-plus"></i> {{__('Add New')}}</a></button>                
                            @endcan
                        </div>
                    </div>   
                  <div class="table-responsive">
                    <table class="table" id="report_table">
                        <thead>
                            <tr>
                                <th></th>                                
                                <th>{{__('Image')}}</th>
                                <th>{{__('Name')}}</th>
                                <th>{{__('Email')}}</th>                                
                                <th>{{__('Status')}}</th>                                                                    
                                <th>{{__('Action')}}</th>                             
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $item)
                                <tr>
                                    <td></td>                                    
                                    <th> <img class="avatar avatar-lg" src="{{url('images/upload/'.$item->image)}}"> </th>
                                    <td>{{$item->name}}</td>         
                                    <td>{{$item->email}}</td>                                                                              
                                    <td>                                        
                                        <h5><span class="badge {{$item->status=="1"?'badge-success': 'badge-danger'}}  m-1">{{$item->status=="1"?'Active': 'Block'}}</span></h5>
                                    </td>
                                    <td>   
                                        <a href="{{ url('view-user/'.$item->id) }}" title="User Detail" class="btn-icon text-success"><i class="fas fa-eye"></i></a>                                                                                
                                        @can('block_app_user')  
                                           
                                            @if($item->status=="0")                                                
                                                <a href="{{ url('block-user/'.$item->id) }}" title="Unblock {{$item->name}}" class="btn-icon text-success"><i class="fas fa-unlock-alt"></i></a>                                                                             
                                            @else                         
                                                <a href="{{ url('block-user/'.$item->id) }}" title="Block {{$item->name}}" class="btn-icon text-danger"><i class="fas fa-ban text-danger"></i></a>                                                                         
                                            @endif
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach                           
                        </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
    </section>
@endsection
