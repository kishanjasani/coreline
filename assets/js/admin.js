/**
 * Coreline Admin JavaScript
 *
 * @package Coreline
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Update login URL preview when slug changes
        const $slugInput = $('#coreline-custom_login_slug');
        const $loginUrlDisplay = $('#coreline-login-url');

        if ($slugInput.length && $loginUrlDisplay.length) {
            $slugInput.on('input', function() {
                const slug = $(this).val().trim() || corelineAdmin.defaultSlug;
                const newUrl = corelineAdmin.homeUrl + slug;
                $loginUrlDisplay.text(newUrl);
            });
        }

        // Show/hide login slug field based on checkbox
        const $enableCheckbox = $('input[name="coreline_settings[custom_login_url_enabled]"]');
        const $slugRow = $slugInput.closest('tr');

        function toggleSlugField() {
            if ($enableCheckbox.is(':checked')) {
                $slugRow.fadeIn(200);
            } else {
                $slugRow.fadeOut(200);
            }
        }

        if ($enableCheckbox.length && $slugRow.length) {
            toggleSlugField();
            $enableCheckbox.on('change', toggleSlugField);
        }

        // Confirmation before disabling custom login URL
        const $form = $('#coreline-settings form');

        $form.on('submit', function(e) {
            const wasEnabled = $enableCheckbox.data('initial-state');
            const isEnabled = $enableCheckbox.is(':checked');

            // Store initial state on page load
            if (typeof wasEnabled === 'undefined') {
                $enableCheckbox.data('initial-state', isEnabled);
                return true;
            }

            // If disabling custom login URL, show confirmation
            if (wasEnabled && !isEnabled) {
                const confirmed = confirm(
                    'Warning: You are about to disable the custom login URL feature.\n\n' +
                    'After saving, you will need to use the default wp-login.php URL to access the login page.\n\n' +
                    'Do you want to continue?'
                );

                if (!confirmed) {
                    e.preventDefault();
                    return false;
                }
            }

            return true;
        });

        // Copy login URL to clipboard
        if ($loginUrlDisplay.length) {
            $loginUrlDisplay.css('cursor', 'pointer').attr('title', 'Click to copy');

            $loginUrlDisplay.on('click', function() {
                const url = $(this).text();

                // Modern clipboard API
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(url).then(function() {
                        showCopyFeedback($loginUrlDisplay);
                    }).catch(function() {
                        fallbackCopy(url, $loginUrlDisplay);
                    });
                } else {
                    fallbackCopy(url, $loginUrlDisplay);
                }
            });
        }

        /**
         * Fallback copy method for older browsers
         */
        function fallbackCopy(text, $element) {
            const $temp = $('<input>');
            $('body').append($temp);
            $temp.val(text).select();

            try {
                document.execCommand('copy');
                showCopyFeedback($element);
            } catch (err) {
                console.error('Failed to copy:', err);
            }

            $temp.remove();
        }

        /**
         * Show visual feedback after copying
         */
        function showCopyFeedback($element) {
            const originalBg = $element.css('background-color');
            const originalText = $element.attr('title');

            $element
                .css('background-color', '#46b450')
                .css('color', '#fff')
                .attr('title', 'Copied!');

            setTimeout(function() {
                $element
                    .css('background-color', originalBg)
                    .css('color', '#2271b1')
                    .attr('title', originalText);
            }, 1000);
        }
    });

})(jQuery);
