@if ($errors->any() || session()->has('status') || session()->has('error') || session()->has('message'))
    <div class="tw-mx-auto tw-max-w-content tw-space-y-3 tw-px-4 tw-pt-4 md:tw-px-6" aria-live="polite">
        @if ($errors->any())
            <x-alert tone="danger" title="{{ __('Please check the form') }}" dismissible>
                <x-slot:icon><x-lucide-circle-alert class="tw-h-5 tw-w-5" /></x-slot:icon>
                <ul class="tw-list-disc tw-space-y-1 tw-pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        @if (session('status'))
            <x-alert tone="success" dismissible>
                <x-slot:icon><x-lucide-circle-check class="tw-h-5 tw-w-5" /></x-slot:icon>
                {{ session('status') }}
            </x-alert>
        @endif

        @if (session('message'))
            <x-alert tone="info" dismissible>
                <x-slot:icon><x-lucide-info class="tw-h-5 tw-w-5" /></x-slot:icon>
                {{ session('message') }}
            </x-alert>
        @endif

        @if (session('error'))
            <x-alert tone="danger" dismissible>
                <x-slot:icon><x-lucide-circle-alert class="tw-h-5 tw-w-5" /></x-slot:icon>
                {{ session('error') }}
            </x-alert>
        @endif
    </div>
@endif
