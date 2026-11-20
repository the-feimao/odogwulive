@extends('layouts.admin')

@section('admin-content')
    <x-admin-breadcrumb :title="__('Add Event')" :parent="__('Event')" url="events" />

    <x-flash-messages />

    <x-card :title="__('Add Event')" :subtitle="__('Create an event, its location and its visibility.')">
        @include('admin.event.partials.form', ['event' => null])
    </x-card>
@endsection
