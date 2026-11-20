@extends('layouts.admin')

@section('admin-content')
    <x-admin-breadcrumb :title="__('Edit Faq')" :parent="__('Faq')" url="faq" />

    <x-card :title="__('Edit Faq')" :subtitle="__('Update the question or answer.')">
        <form method="post" action="{{ url('faq/'.$faq->id) }}" enctype="multipart/form-data" class="tw-grid tw-gap-5">
            @csrf
            @method('PUT')

            <x-input name="question" :label="__('Question')" :placeholder="__('Question')" :value="$faq->question" required />
            <x-textarea name="answer" :label="__('Answer')" :placeholder="__('Answer')" :value="$faq->answer" required />

            <div>
                <x-button type="submit">{{ __('Submit') }}</x-button>
            </div>
        </form>
    </x-card>
@endsection
