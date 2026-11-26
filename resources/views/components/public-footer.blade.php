@props(['setting' => null])

@php
    $brand = config('brand');
    $brandName = $brand['name'];
    $logo = $setting->logo ?? null;
    $logoUrl = $logo ? url('images/upload/'.$logo) : asset($brand['logo']);
    $email = $setting->email ?? null;
    $copyright = $setting->footer_copyright ?? null;
@endphp

<footer class="tw-bg-ink-950 tw-text-neutral-300">
    <div class="tw-mx-auto tw-max-w-content tw-px-4 tw-py-14 md:tw-px-6">
        <div class="tw-grid tw-gap-10 md:tw-grid-cols-2 lg:tw-grid-cols-4">
            <div>
                <div class="tw-flex tw-items-center tw-gap-2.5">
                    <img src="{{ $logoUrl }}" alt="" width="36" height="36" class="tw-h-9 tw-w-9 tw-object-contain">
                    <span class="tw-text-lg tw-font-extrabold tw-tracking-tight tw-text-neutral-50">{{ $brandName }}</span>
                </div>
                <p class="tw-mt-4 tw-max-w-xs tw-text-sm tw-leading-relaxed tw-text-neutral-400">
                    {{ $brand['about'] }}
                </p>
                <div class="tw-mt-5 tw-flex tw-items-center tw-gap-2">
                    @foreach ($brand['social'] as $network => $url)
                        <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
                            aria-label="{{ ucfirst($network) }}" class="tw-inline-flex tw-h-9 tw-w-9 tw-items-center tw-justify-center tw-rounded-pill tw-border tw-border-neutral-800 tw-text-neutral-400 tw-transition-colors hover:tw-border-primary-500 hover:tw-text-primary-400 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-500">
                            @if ($network === 'facebook')
                                <x-lucide-facebook class="tw-h-4 tw-w-4" />
                            @elseif ($network === 'twitter')
                                <x-lucide-twitter class="tw-h-4 tw-w-4" />
                            @else
                                <x-lucide-instagram class="tw-h-4 tw-w-4" />
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>

            <div>
                <h2 class="tw-text-xs tw-font-bold tw-uppercase tw-tracking-widest tw-text-neutral-400">{{ __('Explore') }}</h2>
                <ul class="tw-mt-4 tw-space-y-3 tw-text-sm">
                    <li><a href="{{ route('frontend.index') }}" class="hover:tw-text-primary-400">{{ __('All events') }}</a></li>
                    <li><a href="{{ url('/all-category') }}" class="hover:tw-text-primary-400">{{ __('Categories') }}</a></li>
                    <li><a href="{{ url('/all-blogs') }}" class="hover:tw-text-primary-400">{{ __('Stories') }}</a></li>
                    <li><a href="{{ url('/user/org-register') }}" class="hover:tw-text-primary-400">{{ __('Become an organizer') }}</a></li>
                </ul>
            </div>

            <div>
                <h2 class="tw-text-xs tw-font-bold tw-uppercase tw-tracking-widest tw-text-neutral-400">{{ __('Support') }}</h2>
                <ul class="tw-mt-4 tw-space-y-3 tw-text-sm">
                    <li><a href="{{ url('/contact') }}" class="hover:tw-text-primary-400">{{ __('Contact us') }}</a></li>
                    @if ($setting?->help_center)
                        <li><a href="{{ $setting->help_center }}" target="_blank" rel="noopener noreferrer" class="hover:tw-text-primary-400">{{ __('Help center') }}</a></li>
                    @endif
                    @if ($setting?->privacy_policy)
                        <li><a href="{{ $setting->privacy_policy }}" target="_blank" rel="noopener noreferrer" class="hover:tw-text-primary-400">{{ __('Privacy policy') }}</a></li>
                    @endif
                    @if ($setting?->terms_services)
                        <li><a href="{{ $setting->terms_services }}" target="_blank" rel="noopener noreferrer" class="hover:tw-text-primary-400">{{ __('Terms of service') }}</a></li>
                    @endif
                </ul>
            </div>

            <div>
                <h2 class="tw-text-xs tw-font-bold tw-uppercase tw-tracking-widest tw-text-neutral-400">{{ __('Say hello') }}</h2>
                @if ($email)
                    <a href="mailto:{{ $email }}" class="tw-mt-4 tw-inline-flex tw-items-center tw-gap-2 tw-text-sm hover:tw-text-primary-400">
                        <x-lucide-mail class="tw-h-4 tw-w-4 tw-shrink-0" />
                        {{ $email }}
                    </a>
                @endif
                <p class="tw-mt-4 tw-inline-flex tw-items-center tw-gap-2 tw-text-sm tw-text-neutral-400">
                    <x-lucide-map-pin class="tw-h-4 tw-w-4 tw-shrink-0" />
                    {{ $brand['location'] }}
                </p>
            </div>
        </div>

        <div class="tw-mt-12 tw-flex tw-flex-col tw-gap-2 tw-border-t tw-border-neutral-800 tw-pt-6 tw-text-xs tw-text-neutral-400 sm:tw-flex-row sm:tw-items-center sm:tw-justify-between">
            <p>{{ $copyright ?: '© '.date('Y').' '.$brandName.'. '.__('All rights reserved.') }}</p>
            <p>{{ __('Built for memorable events.') }}</p>
        </div>
    </div>
</footer>
