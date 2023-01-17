@extends('master')

@section('content')
<section class="section">
    @include('admin.layout.breadcrumbs', [
        'title' => __('Users'),
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
                        <div class="col-lg-8"><h2 class="section-title mt-0"> {{__('View Users')}}</h2></div>
                        <div class="col-lg-4 text-right">
                            @can('user_create')
                            <button class="btn btn-primary add-button"><a href="{{url('users/create')}}"><i class="fas fa-plus"></i> {{__('Add New')}}</a></button>
                            @endcan
                        </div>
                    </div>
                  <div class="table-responsive">
                    <table class="table" id="report_table">
                        <thead>
                            <tr>
                                <th></th>
                                <th>{{__('User')}}</th>
                                <th>{{__('Phone')}}</th>
                                <th>{{__('Role')}}</th>
                                @if(Gate::check('user_edit') || Gate::check('user_delete'))
                                <th>{{__('Action')}}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $item)
                                <tr>
                                    <td></td>
                                    <td>
                                        <div class="media">
                                            <img alt="image" class="mr-3 avatar" src="{{url('images/upload/'.$item->image)}}">
                                            <div class="media-body">
                                                <div class="media-title mb-0">
                                                   {{$item->first_name.' '.$item->last_name}}
                                                </div>
                                                <div class="media-description text-muted"> {{$item->email}} </div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td>{{$item->phone}}</td>
                                    <td>
                                        @forelse ($item->roles as $roles)
                                            <span class="badge badge-primary  m-1">{{$roles->name}}</span>
                                        @empty
                                            <span class="badge badge-warning  m-1">{{__('No Data')}}</span>
                                        @endforelse
                                    </td>
                                    @if(Gate::check('user_edit') || Gate::check('user_delete'))
                                    <td>
                                        @if (!$item->hasRole('admin'))
                                            @can('user_edit')
                                                <a href="{{ route('users.edit', $item->id) }}" class="btn-icon"><i class="fas fa-edit"></i></a>
                                            @endcan
                                        @endif
                                    </td>
                                    @endif
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
