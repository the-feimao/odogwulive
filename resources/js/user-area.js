(() => {
    const root = document.querySelector('[data-user-area]');
    const statusRegion = document.getElementById('user-area-status');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    let statusTimer;

    const endpoint = (name, fallback) => root?.dataset[name] || fallback;

    const setStatus = (message) => {
        if (!statusRegion) {
            return;
        }

        window.clearTimeout(statusTimer);
        statusRegion.textContent = message || '';
        statusRegion.hidden = !message;

        if (message) {
            statusTimer = window.setTimeout(() => {
                statusRegion.textContent = '';
                statusRegion.hidden = true;
            }, 6000);
        }
    };

    const getJson = async (url) => {
        const response = await fetch(url, {
            method: 'GET',
            credentials: 'same-origin',
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
        });

        if (!response.ok) {
            throw new Error('Request failed: '.concat(response.status));
        }

        return response.json();
    };

    const getHtml = async (url) => {
        const response = await fetch(url, {
            method: 'GET',
            credentials: 'same-origin',
            headers: { Accept: 'text/html', 'X-CSRF-TOKEN': csrfToken },
        });

        if (!response.ok) {
            throw new Error('Request failed: '.concat(response.status));
        }

        return response.text();
    };

    const postParams = async (url, params) => {
        const response = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: new URLSearchParams(params).toString(),
        });

        if (!response.ok) {
            throw new Error('Request failed: '.concat(response.status));
        }

        return response.json();
    };

    /* Profile: photo upload and inline bio. */
    const imageForm = document.getElementById('imageUploadForm');
    const imageInput = document.getElementById('imgUpload');
    const imagePreview = document.getElementById('imagePreview');
    const openImageButton = document.getElementById('OpenImgUpload');

    if (openImageButton && imageInput) {
        openImageButton.addEventListener('click', () => imageInput.click());
    }

    if (imageForm && imageInput) {
        imageInput.addEventListener('change', async () => {
            const file = imageInput.files && imageInput.files[0];
            if (!file) {
                return;
            }

            const previewUrl = URL.createObjectURL(file);
            if (imagePreview) {
                imagePreview.src = previewUrl;
            }

            setStatus('Uploading your photo…');

            try {
                const response = await fetch(imageForm.action, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: new FormData(imageForm),
                });

                if (!response.ok) {
                    throw new Error('Upload failed: '.concat(response.status));
                }

                const payload = await response.json();
                if (payload.success && payload.data && imagePreview && root?.dataset.imageBase) {
                    imagePreview.src = root.dataset.imageBase.concat('/', payload.data);
                }
                setStatus('Profile photo updated.');
            } catch (error) {
                setStatus('Could not update your photo.');
            }
        });
    }

    const bioButton = document.querySelector('.btn-bio');
    const bioControl = document.querySelector('.bio-control');
    const bioSection = document.querySelector('[data-bio-section]');

    if (bioButton && bioControl) {
        bioButton.addEventListener('click', () => {
            bioControl.hidden = false;
            bioControl.classList.remove('hide');
            bioButton.hidden = true;
            bioControl.focus();
        });

        bioControl.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                bioControl.blur();
            }
        });

        bioControl.addEventListener('blur', async () => {
            const bio = bioControl.value.trim();
            setStatus('Saving your bio…');

            try {
                const payload = await postParams(endpoint('bioEndpoint', '/add-bio'), { bio });
                if (payload.success) {
                    const display = bioSection?.querySelector('[data-bio-display]');
                    if (display) {
                        display.textContent = payload.data || 'Add a short bio so other attendees can recognise you.';
                    }
                    setStatus('Bio updated.');
                } else {
                    setStatus('Could not update your bio.');
                }
            } catch (error) {
                setStatus('Could not update your bio.');
            }
        });
    }

    /* Profile: favourites and following. The inline onclick hooks keep their legacy names. */
    window.addFavorite = async (id, type) => {
        try {
            const payload = await getJson(endpoint('favoriteEndpoint', '/add-favorite').concat('/', id, '/', type));
            if (!payload.success) {
                return;
            }

            const buttons = document.querySelectorAll(
                '[data-favorite-id="'.concat(id, '"][data-favorite-type="', type, '"]')
            );

            buttons.forEach((button) => {
                const active = button.getAttribute('aria-pressed') !== 'true';
                button.setAttribute('aria-pressed', active ? 'true' : 'false');
                button.setAttribute(
                    'aria-label',
                    active
                        ? (type === 'blog' ? 'Remove from saved blogs' : 'Remove from saved events')
                        : (type === 'blog' ? 'Save blog' : 'Save event')
                );

                const icon = button.querySelector('svg');
                if (icon) {
                    icon.classList.toggle('tw-fill-current', active);
                    icon.classList.toggle('tw-text-primary-600', active);
                }
            });

            setStatus(payload.msg || 'Saved list updated.');

            if (root?.hasAttribute('data-profile-page')) {
                window.setTimeout(() => window.location.reload(), 600);
            }
        } catch (error) {
            setStatus('Could not update your saved list.');
        }
    };

    window.follow = async (id) => {
        try {
            const payload = await getJson(endpoint('followEndpoint', '/add-followList').concat('/', id));
            if (!payload.success) {
                return;
            }

            const button = document.querySelector('[data-follow-id="'.concat(id, '"]'));
            if (button) {
                const active = button.getAttribute('aria-pressed') !== 'true';
                button.setAttribute('aria-pressed', active ? 'true' : 'false');
                button.textContent = active ? 'Unfollow' : 'Follow';
            }

            setStatus(payload.msg || 'Following list updated.');

            if (root?.hasAttribute('data-profile-page')) {
                window.setTimeout(() => window.location.reload(), 600);
            }
        } catch (error) {
            setStatus('Could not update your following list.');
        }
    };

    /* My tickets: payment toggle, review modal and order detail loading. */
    window.viewPayment = () => {
        const panel = document.getElementById('payment-data') || document.querySelector('.payment-data');
        if (panel) {
            panel.hidden = !panel.hidden;
        }
    };

    window.addRate = (id) => {
        const modal = document.getElementById('reviewModel');
        const rateInput = modal?.querySelector('input[name="rate"]');
        if (rateInput) {
            rateInput.value = id;
        }

        modal?.querySelectorAll('[data-rate]').forEach((star) => {
            const active = Number(star.dataset.rate) <= Number(id);
            star.classList.toggle('tw-bg-accent-100', active);
            star.classList.toggle('tw-text-accent-600', active);
            star.classList.toggle('tw-text-neutral-400', !active);
            star.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
    };

    const reviewModal = document.getElementById('reviewModel');

    const openReviewModal = () => {
        if (!reviewModal) {
            return;
        }
        // Tailwind's display utilities override the browser's [hidden] rule,
        // so visibility is controlled by the tw-hidden/tw-flex classes.
        reviewModal.hidden = false;
        reviewModal.classList.remove('tw-hidden');
        reviewModal.classList.add('tw-flex');
        reviewModal.querySelector('textarea[name="message"]')?.focus();
    };

    const closeReviewModal = () => {
        if (!reviewModal) {
            return;
        }
        reviewModal.classList.add('tw-hidden');
        reviewModal.classList.remove('tw-flex');
        reviewModal.hidden = true;
    };

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('.open-addReview');
        if (!trigger) {
            return;
        }

        event.preventDefault();
        const orderId = document.getElementById('order_id');
        if (orderId) {
            orderId.value = trigger.dataset.id || '';
        }

        const title = document.getElementById('exampleModalLongTitle');
        if (title) {
            title.textContent = 'Add Review to '.concat(trigger.dataset.order || '');
        }

        openReviewModal();
    });

    reviewModal?.querySelectorAll('[data-review-close]').forEach((button) => {
        button.addEventListener('click', closeReviewModal);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && reviewModal && !reviewModal.hidden) {
            closeReviewModal();
        }
    });

    const detail = document.querySelector('[data-order-detail]');
    const orderButtons = Array.from(document.querySelectorAll('.event-data[data-order-id]'));

    if (detail && orderButtons.length) {
        const currency = root?.dataset.currencySymbol || '';
        const imageBase = root?.dataset.imageBase || '';
        const fallbackImage = root?.dataset.imageFallback || '';
        const invoiceBase = endpoint('invoiceBase', '/my-tickets');
        const qrBase = endpoint('qrBase', '/my-tickets');
        const orderEndpoint = endpoint('orderEndpoint', '/getOrder');
        const qrContainer = detail.querySelector('[data-order-qr-content]');

        const badgeBase = 'tw-inline-flex tw-items-center tw-rounded-pill tw-px-2.5 tw-py-1 tw-text-xs tw-font-bold';
        const badgeClasses = {
            Pending: 'tw-bg-warning-100 tw-text-warning-700 dark:tw-bg-warning-500/20 dark:tw-text-warning-200',
            Complete: 'tw-bg-success-100 tw-text-success-700 dark:tw-bg-success-500/20 dark:tw-text-success-200',
            Cancel: 'tw-bg-danger-100 tw-text-danger-700 dark:tw-bg-danger-500/20 dark:tw-text-danger-200',
        };

        const setText = (name, value) => {
            const node = detail.querySelector('[data-detail="'.concat(name, '"]'));
            if (node) {
                node.textContent = value === null || value === undefined ? '' : String(value);
            }
        };

        const setBadge = (name, value, classes) => {
            const node = detail.querySelector('[data-detail="'.concat(name, '"]'));
            if (node) {
                node.textContent = value;
                node.className = badgeBase.concat(' ', classes || badgeClasses.Pending);
            }
        };

        const money = (value) => currency.concat(Number(value || 0).toLocaleString(undefined, {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2,
        }));

        const setTicketCount = (count) => {
            const total = Math.max(0, Number(count || 0));
            setText('ticket-count', String(total).concat(' ', total === 1 ? 'ticket' : 'tickets'));
        };

        const renderOrder = (order) => {
            setText('order-id', order.order_id);
            setText('time', order.time);
            setText('event-name', order.event?.name);
            setText('organizer', [order.organization?.first_name, order.organization?.last_name].filter(Boolean).join(' '));
            setText('start', order.start_time);
            setText('end', order.end_time);
            setText('location', order.event?.type === 'online' ? 'Online event' : (order.event?.address || 'Venue to be announced'));

            const image = detail.querySelector('[data-detail="event-image"]');
            if (image) {
                image.src = order.event?.image ? imageBase.concat('/', order.event.image) : fallbackImage;
                image.alt = order.event?.name || '';
            }

            setBadge('status', order.order_status, badgeClasses[order.order_status]);

            const paid = order.payment_status === 1 || order.payment_status === '1';
            setBadge(
                'payment-status',
                paid ? 'Paid' : 'Waiting',
                paid
                    ? 'tw-bg-success-100 tw-text-success-700 dark:tw-bg-success-500/20 dark:tw-text-success-200'
                    : 'tw-bg-warning-100 tw-text-warning-700 dark:tw-bg-warning-500/20 dark:tw-text-warning-200'
            );
            setText('payment-method', order.payment_type || '-');
            setText('payment-token', order.payment_token || '-');

            setText('ticket-name', order.ticket?.name);
            setText('ticket-quantity', order.quantity);
            setTicketCount(order.quantity);

            const free = order.ticket?.type === 'free';
            setText('subtotal', free ? 'FREE' : money(Number(order.ticket?.price || 0) * Number(order.quantity || 0)));
            setText('discount', free ? '0.00' : money(order.coupon_discount));
            setText('tax', free ? '0.00' : money(order.tax));
            setText('total', free ? 'FREE' : money(order.payment));

            const reviewBlock = detail.querySelector('[data-detail="review-block"]');
            if (reviewBlock) {
                reviewBlock.hidden = !order.review;
            }
            detail.querySelectorAll('[data-review-star]').forEach((star, index) => {
                const active = Boolean(order.review) && Number(order.review.rate) >= index + 1;
                star.classList.toggle('tw-text-accent-400', active);
                star.classList.toggle('tw-fill-current', active);
                star.classList.toggle('tw-text-neutral-600', !active);
            });

            const reviewAction = detail.querySelector('[data-detail="review-action"]');
            const showReview = (!order.review && order.order_status === 'Complete') || order.order_status === 'Cancel';
            if (reviewAction) {
                reviewAction.hidden = !showReview;
                reviewAction.dataset.id = order.id;
                reviewAction.dataset.order = order.order_id;
            }

            const invoiceLink = detail.querySelector('[data-detail="invoice-link"]');
            if (invoiceLink) {
                invoiceLink.href = invoiceBase.concat('/', order.id, '/invoice-print');
            }

            const ticketsPrintLink = detail.querySelector('[data-detail="tickets-print-link"]');
            if (ticketsPrintLink) {
                ticketsPrintLink.href = invoiceBase.concat('/', order.id, '/invoice-print');
            }

            const paymentPanel = document.getElementById('payment-data') || document.querySelector('.payment-data');
            if (paymentPanel) {
                paymentPanel.hidden = true;
            }
        };

        orderButtons.forEach((button) => {
            button.addEventListener('click', async () => {
                orderButtons.forEach((candidate) => candidate.classList.remove('active'));
                button.classList.add('active');

                try {
                    const payload = await getJson(orderEndpoint.concat('/', button.dataset.orderId));
                    if (payload.success) {
                        renderOrder(payload.data);

                        if (qrContainer) {
                            try {
                                qrContainer.innerHTML = await getHtml(
                                    qrBase.concat('/', button.dataset.orderId, '/qr')
                                );
                                const passes = qrContainer.querySelectorAll('[data-ticket-qr]').length;
                                if (passes) {
                                    setTicketCount(passes);
                                }
                            } catch (error) {
                                setStatus('Could not load the QR tickets.');
                            }
                        }
                    }
                } catch (error) {
                    setStatus('Could not load that order.');
                }
            });
        });
    }
})();
