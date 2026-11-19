const checkoutForm = document.getElementById('checkout-form');

if (checkoutForm) {
    const quantityInput = document.getElementById('quantity');
    const paymentInput = document.getElementById('payment');
    const stripePaymentInput = document.getElementById('stripe_payment');
    const paymentTokenInput = document.getElementById('payment_token');
    const submitButton = document.getElementById('form_submit');
    const stripeSection = document.getElementById('stripe-form-section');
    const stripeForm = document.getElementById('stripe-form');
    const paypalSection = document.getElementById('paypal-button-section');
    const paypalContainer = document.getElementById('paypal-button-container');
    const statusRegion = document.getElementById('checkout-payment-status');

    const ticketPrice = Number(document.getElementById('ticket_price')?.value || 0);
    const taxTotal = Number(document.getElementById('tax_total')?.value || 0);
    const currencyCode = (document.getElementById('currency_code')?.value || '').trim().toUpperCase();
    const currencySymbol = checkoutForm.dataset.currencySymbol || '';
    const usesMinorUnits = currencyCode === 'USD' || currencyCode === 'EUR';

    const byId = (id) => document.getElementById(id);
    const money = (value) => currencySymbol + Number(value).toLocaleString(undefined, {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });

    const setStatus = (message) => {
        if (!statusRegion) {
            return;
        }

        statusRegion.textContent = message || '';
        statusRegion.hidden = !message;
    };

    const renderStripeButton = () => {
        if (!stripeForm) {
            return;
        }

        stripeForm.replaceChildren();
        const script = document.createElement('script');
        script.src = 'https://checkout.stripe.com/checkout.js';
        script.className = 'stripe-button';
        script.dataset.key = byId('stripePublicKey')?.value || '';
        script.dataset.amount = stripePaymentInput?.value || '0';
        script.dataset.name = 'CodesCompanion';
        script.dataset.description = 'test';
        script.dataset.image = 'https://stripe.com/img/documentation/checkout/marketplace.png';
        script.dataset.locale = 'auto';
        script.dataset.currency = currencyCode;
        stripeForm.appendChild(script);
    };

    const updateTotals = () => {
        const tpo = Number(byId('tpo')?.value || 1);
        const available = Number(byId('available')?.value || 1);
        const limit = Math.max(1, Math.min(tpo, available));
        let quantity = Number(quantityInput?.value || 1);

        if (!Number.isFinite(quantity) || quantity < 1) {
            quantity = 1;
        }
        if (quantity > limit) {
            quantity = limit;
        }

        if (quantityInput) {
            quantityInput.value = String(quantity);
        }

        document.querySelectorAll('[data-checkout-qty]').forEach((node) => {
            node.textContent = String(quantity);
        });

        const total = ticketPrice * quantity + taxTotal;
        document.querySelectorAll('[data-checkout-subtotal]').forEach((node) => {
            node.textContent = money(ticketPrice * quantity);
        });
        document.querySelectorAll('[data-checkout-tax]').forEach((node) => {
            node.textContent = money(taxTotal);
        });
        document.querySelectorAll('[data-checkout-total]').forEach((node) => {
            node.textContent = money(total);
        });

        const gatewayAmount = usesMinorUnits ? total * 100 : total;

        if (paymentInput) {
            paymentInput.value = String(gatewayAmount);
        }
        if (stripePaymentInput) {
            stripePaymentInput.value = String(gatewayAmount);
        }

        document.querySelectorAll('[data-qty-action="inc"]').forEach((button) => {
            button.disabled = quantity >= limit;
        });
        document.querySelectorAll('[data-qty-action="dec"]').forEach((button) => {
            button.disabled = quantity <= 1;
        });

        const selected = checkoutForm.querySelector('input[name="payment_type"]:checked');
        if (selected?.value === 'STRIPE') {
            renderStripeButton();
        }
    };

    document.querySelectorAll('[data-qty-action]').forEach((button) => {
        button.addEventListener('click', () => {
            const current = Number(quantityInput?.value || 1);
            const next = button.dataset.qtyAction === 'inc' ? current + 1 : current - 1;

            if (quantityInput) {
                quantityInput.value = String(Math.max(1, next));
            }
            updateTotals();
        });
    });

    const unlockSubmit = () => {
        if (submitButton) {
            submitButton.hidden = false;
            submitButton.disabled = false;
        }
    };

    const handlePaymentMethod = (method) => {
        if (paymentTokenInput) {
            paymentTokenInput.value = '';
        }
        setStatus('');

        if (stripeSection) {
            stripeSection.hidden = method !== 'STRIPE';
        }
        if (stripeForm) {
            stripeForm.replaceChildren();
        }
        if (paypalSection) {
            paypalSection.hidden = true;
        }
        if (paypalContainer) {
            paypalContainer.replaceChildren();
        }

        if (method === 'STRIPE') {
            if (submitButton) {
                submitButton.hidden = true;
            }
            renderStripeButton();
            return;
        }

        unlockSubmit();

        if (method === 'PAYPAL') {
            if (!window.paypal_sdk || !paypalContainer) {
                setStatus('PayPal is unavailable right now. Please choose another payment method.');
                return;
            }

            if (submitButton) {
                submitButton.disabled = true;
            }
            window.paypal_sdk.Buttons({
                createOrder: (_data, actions) => actions.order.create({
                    purchase_units: [{
                        amount: { value: paymentInput?.value || '0' },
                    }],
                }),
                onApprove: (_data, actions) => actions.order.capture().then((details) => {
                    if (paymentTokenInput) {
                        paymentTokenInput.value = details.id;
                    }
                    unlockSubmit();
                }),
            }).render('#paypal-button-container');
            paypalSection.hidden = false;
            return;
        }

        if (method === 'RAZOR') {
            if (!window.Razorpay) {
                setStatus('Razorpay is unavailable right now. Please choose another payment method.');
                return;
            }

            if (submitButton) {
                submitButton.disabled = true;
            }
            const razorpay = new window.Razorpay({
                key: byId('razor_key')?.value || '',
                amount: Number(paymentInput?.value || 0) * 100,
                name: 'CodesCompanion',
                description: 'test',
                image: 'https://i.imgur.com/n5tjHFD.png',
                handler: (transaction) => {
                    if (paymentTokenInput) {
                        paymentTokenInput.value = transaction.razorpay_payment_id;
                    }
                    unlockSubmit();
                },
            });
            razorpay.open();
        }
    };

    checkoutForm.querySelectorAll('input[name="payment_type"]').forEach((radio) => {
        radio.addEventListener('change', () => handlePaymentMethod(radio.value));
    });

    updateTotals();

    const initiallySelected = checkoutForm.querySelector('input[name="payment_type"]:checked');
    if (initiallySelected && ['STRIPE', 'PAYPAL', 'RAZOR'].includes(initiallySelected.value)) {
        handlePaymentMethod(initiallySelected.value);
    }
}
