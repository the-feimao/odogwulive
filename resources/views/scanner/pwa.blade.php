@extends('layouts.public')

@section('title', 'Ticket scanner')
@section('manifest', route('pwa.scanner-manifest'))
@section('app-title', 'Scanner')

@section('content')
    <div id="scanner-app" class="tw-mx-auto tw-max-w-6xl tw-px-4 tw-py-10 sm:tw-px-6 lg:tw-px-8">
        <header class="tw-mb-8 tw-flex tw-flex-wrap tw-items-end tw-justify-between tw-gap-4">
            <div>
                <p class="tw-text-xs tw-font-bold tw-uppercase tw-tracking-[0.18em] tw-text-accent-600 dark:tw-text-accent-300">
                    Staff tool
                </p>
                <h1 class="tw-mt-2 tw-text-3xl tw-font-extrabold tw-tracking-tight tw-text-ink-900 dark:tw-text-neutral-50">
                    Ticket scanner
                </h1>
                <p class="tw-mt-2 tw-max-w-2xl tw-text-sm tw-text-neutral-600 dark:tw-text-neutral-300">
                    Sign in with your scanner account, choose an assigned event, then validate attendee QR codes.
                </p>
            </div>

            <button type="button" id="scanner-logout"
                class="tw-hidden tw-rounded-pill tw-border tw-border-neutral-300 tw-px-4 tw-py-2 tw-text-sm tw-font-bold tw-text-neutral-700 tw-transition hover:tw-border-ink-900 hover:tw-text-ink-900 dark:tw-border-neutral-700 dark:tw-text-neutral-200 dark:hover:tw-border-neutral-200 dark:hover:tw-text-neutral-50">
                Sign out
            </button>
        </header>

        <section id="scanner-auth"
            class="tw-mx-auto tw-max-w-md tw-rounded-card tw-border tw-border-neutral-200 tw-bg-neutral-0 tw-p-6 tw-shadow-card dark:tw-border-neutral-800 dark:tw-bg-ink-900">
            <h2 class="tw-text-xl tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">Scanner sign in</h2>
            <p class="tw-mt-1 tw-text-sm tw-text-neutral-600 dark:tw-text-neutral-300">
                Use the same credentials as the existing scanner app.
            </p>

            <form id="scanner-login-form" class="tw-mt-6 tw-space-y-4">
                <div>
                    <label for="scanner-email" class="tw-block tw-text-sm tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">Email</label>
                    <input id="scanner-email" name="email" type="email" autocomplete="username" required
                        class="tw-mt-1 tw-w-full tw-rounded-stub tw-border tw-border-neutral-300 tw-bg-neutral-0 tw-px-3 tw-py-2.5 tw-text-sm tw-text-ink-900 tw-placeholder-neutral-400 focus:tw-border-accent-500 focus:tw-outline-none dark:tw-border-neutral-700 dark:tw-bg-ink-950 dark:tw-text-neutral-50"
                        placeholder="scanner@example.com">
                </div>

                <div>
                    <label for="scanner-password" class="tw-block tw-text-sm tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">Password</label>
                    <input id="scanner-password" name="password" type="password" autocomplete="current-password" required
                        class="tw-mt-1 tw-w-full tw-rounded-stub tw-border tw-border-neutral-300 tw-bg-neutral-0 tw-px-3 tw-py-2.5 tw-text-sm tw-text-ink-900 tw-placeholder-neutral-400 focus:tw-border-accent-500 focus:tw-outline-none dark:tw-border-neutral-700 dark:tw-bg-ink-950 dark:tw-text-neutral-50"
                        placeholder="••••••••">
                </div>

                <p id="scanner-auth-error" role="alert"
                    class="tw-hidden tw-rounded-stub tw-bg-danger-50 tw-px-3 tw-py-2 tw-text-sm tw-text-danger-700 dark:tw-bg-danger-900/30 dark:tw-text-danger-100"></p>

                <button type="submit" id="scanner-login-button"
                    class="tw-w-full tw-rounded-pill tw-bg-primary-500 tw-px-5 tw-py-3 tw-text-sm tw-font-extrabold tw-text-ink-950 tw-transition hover:tw-bg-primary-600 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-accent-500 focus:tw-ring-offset-2 disabled:tw-cursor-wait disabled:tw-opacity-70">
                    Sign in
                </button>
            </form>
        </section>

        <div id="scanner-workspace" class="tw-hidden tw-grid tw-gap-6 lg:tw-grid-cols-[minmax(0,1fr)_minmax(0,1.2fr)]">
            <section class="tw-rounded-card tw-border tw-border-neutral-200 tw-bg-neutral-0 tw-p-5 tw-shadow-card dark:tw-border-neutral-800 dark:tw-bg-ink-900">
                <div class="tw-flex tw-items-center tw-justify-between tw-gap-3">
                    <h2 class="tw-text-lg tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">Assigned events</h2>
                    <button type="button" id="scanner-refresh-events"
                        class="tw-rounded-pill tw-border tw-border-neutral-300 tw-px-3 tw-py-1.5 tw-text-xs tw-font-bold tw-text-neutral-700 tw-transition hover:tw-border-accent-500 hover:tw-text-accent-600 dark:tw-border-neutral-700 dark:tw-text-neutral-200">
                        Refresh
                    </button>
                </div>

                <div id="scanner-events" class="tw-mt-4 tw-grid tw-gap-3"></div>
                <p id="scanner-events-empty"
                    class="tw-mt-4 tw-hidden tw-rounded-stub tw-bg-neutral-50 tw-p-4 tw-text-sm tw-text-neutral-600 dark:tw-bg-ink-950 dark:tw-text-neutral-300">
                    No published events are assigned to this scanner yet.
                </p>
            </section>

            <section id="scanner-scan-section"
                class="tw-hidden tw-rounded-card tw-border tw-border-neutral-200 tw-bg-neutral-0 tw-p-5 tw-shadow-card dark:tw-border-neutral-800 dark:tw-bg-ink-900">
                <p class="tw-text-xs tw-font-bold tw-uppercase tw-tracking-[0.16em] tw-text-neutral-500 dark:tw-text-neutral-400">
                    Scanning
                </p>
                <h2 id="scanner-selected-event" class="tw-mt-1 tw-text-xl tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50"></h2>

                <div id="scanner-reader"
                    class="tw-mt-5 tw-overflow-hidden tw-rounded-card tw-border tw-border-dashed tw-border-neutral-300 tw-bg-neutral-50 dark:tw-border-neutral-700 dark:tw-bg-ink-950"></div>

                <div id="scanner-result" role="status" aria-live="polite"
                    class="tw-hidden tw-mt-4 tw-rounded-stub tw-px-4 tw-py-3 tw-text-sm tw-font-semibold"></div>

                <form id="scanner-manual-form" class="tw-mt-5 tw-flex tw-flex-wrap tw-items-end tw-gap-3">
                    <div class="tw-min-w-[220px] tw-flex-1">
                        <label for="scanner-manual-code" class="tw-block tw-text-sm tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">
                            Manual ticket code
                        </label>
                        <input id="scanner-manual-code" name="ticket_code" type="text" autocomplete="off" required
                            class="tw-mt-1 tw-w-full tw-rounded-stub tw-border tw-border-neutral-300 tw-bg-neutral-0 tw-px-3 tw-py-2.5 tw-text-sm tw-text-ink-900 focus:tw-border-accent-500 focus:tw-outline-none dark:tw-border-neutral-700 dark:tw-bg-ink-950 dark:tw-text-neutral-50"
                            placeholder="Enter code if the camera is unavailable">
                    </div>
                    <button type="submit"
                        class="tw-rounded-pill tw-bg-accent-600 tw-px-5 tw-py-2.5 tw-text-sm tw-font-bold tw-text-neutral-0 tw-transition hover:tw-bg-accent-700">
                        Validate
                    </button>
                </form>

                <button type="button" id="scanner-stop"
                    class="tw-hidden tw-mt-4 tw-rounded-pill tw-border tw-border-neutral-300 tw-px-4 tw-py-2 tw-text-xs tw-font-bold tw-text-neutral-700 dark:tw-border-neutral-700 dark:tw-text-neutral-200">
                    Stop camera
                </button>
            </section>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/scanner-pwa.js')
@endpush
