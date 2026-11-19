(() => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const statusRegion = document.getElementById('public-social-status');
    let statusTimer;

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

    const toggleFollow = async (id) => {
        const button = document.querySelector('[data-follow-id="'.concat(id, '"]'));
        if (!button || button.disabled) {
            return;
        }

        button.disabled = true;

        try {
            const response = await fetch(''.concat(window.location.origin, '/add-followList/', id), {
                method: 'GET',
                credentials: 'same-origin',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
            });

            if (!response.ok) {
                throw new Error('Request failed: '.concat(response.status));
            }

            const payload = await response.json();
            if (!payload.success) {
                return;
            }

            const message = String(payload.msg || '');
            const isFollowing = message.toLowerCase().indexOf('remove') === -1;
            const label = isFollowing ? button.dataset.unfollowLabel : button.dataset.followLabel;

            button.textContent = label || (isFollowing ? 'Unfollow' : 'Follow +');
            button.setAttribute('aria-pressed', isFollowing ? 'true' : 'false');
            setStatus(message);
        } catch (error) {
            setStatus('Could not update your following list.');
        } finally {
            button.disabled = false;
        }
    };

    // Preserve the legacy global hook used by organizer pages.
    window.follow = (id) => toggleFollow(id);
})();
