@extends('layouts.admin')

@section('admin-content')
    <x-admin-breadcrumb :title="__('Add Template')" :parent="__('Templates')" url="notification-template" />

    <x-flash-messages />

    <x-card :title="__('Add Template')" :subtitle="__('Write the subject, email body and app notification message.')">
        <form method="post" class="event-form tw-grid tw-gap-6" action="{{ url('notification-template') }}">
            @csrf

            <div class="tw-grid tw-gap-5 md:tw-grid-cols-2">
                <x-input name="title" :label="__('Title')" :placeholder="__('Title')" :value="old('title')" />
                <x-input name="subject" :label="__('Subject')" :placeholder="__('Subject')" :value="old('subject')" />
            </div>

            <x-textarea name="mail_content" :label="__('Email Content')" placeholder="Email notification content"
                :value="old('mail_content')" rows="8" class="textarea_editor" />

            <x-textarea name="message_content" :label="__('App notification message Content')"
                placeholder="App notification message content" :value="old('message_content')" rows="5" />

            <div>
                <x-button type="submit">{{ __('Submit') }}</x-button>
            </div>
        </form>
    </x-card>
@endsection
