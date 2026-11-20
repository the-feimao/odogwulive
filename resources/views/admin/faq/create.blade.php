@extends('layouts.admin')

@section('admin-content')
    <x-admin-breadcrumb :title="__('Add Faq')" :parent="__('Faq')" url="faq" />

    <x-card :title="__('Add Faq')" :subtitle="__('Publish a helpful answer for attendees.')">
        <form method="post" action="{{ url('faq') }}" enctype="multipart/form-data" class="tw-grid tw-gap-5">
            @csrf

            <x-input name="question" :label="__('Question')" :placeholder="__('Question')" :value="old('question')" required />
            <x-textarea name="answer" :label="__('Answer')" :placeholder="__('Answer')" :value="old('answer')" required />

            <div>
                <x-button type="submit">{{ __('Submit') }}</x-button>
            </div>
        </form>
    </x-card>
@endsection
