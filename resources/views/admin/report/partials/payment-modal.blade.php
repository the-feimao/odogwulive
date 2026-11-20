{{--
    Shared payout modal for settlement reports.

    The Bootstrap hooks (`modal`, `data-dismiss`, `#paymentModel`) and the legacy
    JavaScript IDs (`#payment`, `#user_id`, `#paypal-button-container`,
    `payLocally()`, `.stripe-button`) are intentionally preserved so the existing
    payment scripts keep working unchanged.
--}}
<div class="modal fade" id="paymentModel" tabindex="-1" role="dialog" aria-labelledby="paymentModelLabel" aria-hidden="true">
    <div class="modal-dialog tw-mx-auto tw-my-6 tw-max-w-xl" role="document">
        <div class="modal-content tw-overflow-hidden tw-rounded-card tw-border tw-border-neutral-200 tw-bg-neutral-0 tw-shadow-card-hover dark:tw-border-neutral-800 dark:tw-bg-ink-900">
            <div class="modal-header tw-flex tw-items-center tw-justify-between tw-gap-4 tw-border-b tw-border-neutral-100 tw-px-5 tw-py-4 dark:tw-border-neutral-800">
                <h5 class="modal-title tw-text-lg tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50" id="paymentModelLabel">
                    {{ __('Choose Payment Gateway') }}
                </h5>
                <button type="button" class="close tw-rounded-pill tw-p-1 tw-text-neutral-500 tw-transition hover:tw-bg-neutral-100 hover:tw-text-ink-900 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-text-neutral-300 dark:hover:tw-bg-neutral-800 dark:hover:tw-text-neutral-50"
                    data-dismiss="modal" aria-label="{{ __('Close') }}">
                    <x-lucide-x class="tw-h-5 tw-w-5" />
                </button>
            </div>

            <div class="modal-body all-payment tw-space-y-5 tw-p-5">
                <div class="b-bottom tw-grid tw-gap-3 tw-border-b tw-border-neutral-100 tw-pb-5 dark:tw-border-neutral-800 sm:tw-grid-cols-2 sm:tw-items-center">
                    <h6 class="tw-text-sm tw-font-semibold tw-text-ink-800 dark:tw-text-neutral-100">{{ __('Pay with Paypal') }}</h6>
                    <div class="paypal">
                        <div id="paypal-button-container"></div>
                    </div>
                </div>

                <div class="b-bottom tw-grid tw-gap-3 tw-border-b tw-border-neutral-100 tw-pb-5 dark:tw-border-neutral-800 sm:tw-grid-cols-2 sm:tw-items-center">
                    <h6 class="tw-text-sm tw-font-semibold tw-text-ink-800 dark:tw-text-neutral-100">{{ __('Pay with Stripe') }}</h6>
                    <div class="stripe">
                        <form method="post" action="{{ url('pay-to-org') }}">
                            @csrf
                            <input type="hidden" id="payment" name="payment">
                            <input type="hidden" id="user_id" name="user_id">
                            <input type="hidden" value="STRIPE" name="payment_type">
                            <div class="stripe-form">
                                <script src="https://checkout.stripe.com/checkout.js" class="stripe-button"
                                    data-key="{{ \App\Models\PaymentSetting::find(1)->stripePublicKey }}"
                                    data-amount="100"
                                    data-name="abc"
                                    data-description="nothing"
                                    data-image="https://stripe.com/img/documentation/checkout/marketplace.png"
                                    data-locale="auto"
                                    data-label="Pay with Stripe"
                                    data-currency="{{ $currency }}"></script>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="tw-grid tw-gap-3 tw-items-center sm:tw-grid-cols-2">
                    <h6 class="tw-text-sm tw-font-semibold tw-text-ink-800 dark:tw-text-neutral-100">{{ __('Pay Locally') }}</h6>
                    <button type="button" onclick="payLocally()"
                        class="tw-inline-flex tw-h-10 tw-w-full tw-items-center tw-justify-center tw-rounded-pill tw-bg-primary-500 tw-px-4 tw-text-sm tw-font-bold tw-text-ink-950 tw-transition hover:tw-bg-primary-400 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600">
                        {{ __('Pay with Local') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
