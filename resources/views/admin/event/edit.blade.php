@extends('layouts.admin')

@section('admin-content')
    <x-admin-breadcrumb :title="__('Edit Event')" :parent="__('Event')" url="events" />

    <x-flash-messages />

    <x-card :title="__('Edit Event')" :subtitle="__('Update the event details and sales information.')">
        @include('admin.event.partials.form')
    </x-card>
@endsection
