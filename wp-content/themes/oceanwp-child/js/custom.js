/**
 * Madhu Spices Japan - Custom JavaScript
 * UI/UX Enhancements
 */

(function($) {
    'use strict';

    // Wait for DOM ready
    $(document).ready(function() {

        /**
         * Sticky Header with shrink effect
         */
        var header = $('#site-header');
        var headerWrapper = $('#site-header-sticky-wrapper');
        var body = $('body');
        var scrollThreshold = 50;

        function handleScroll() {
            var scrollTop = $(window).scrollTop();

            if (scrollTop > scrollThreshold) {
                header.addClass('is-scrolled');
                headerWrapper.addClass('is-scrolled');
                body.addClass('is-scrolled');
            } else {
                header.removeClass('is-scrolled');
                headerWrapper.removeClass('is-scrolled');
                body.removeClass('is-scrolled');
            }
        }

        // Initial check
        handleScroll();

        // Throttled scroll handler
        var scrollTimeout;
        $(window).on('scroll', function() {
            if (scrollTimeout) {
                window.cancelAnimationFrame(scrollTimeout);
            }
            scrollTimeout = window.requestAnimationFrame(handleScroll);
        });

        /**
         * Add to Cart Animation
         */
        $(document.body).on('adding_to_cart', function(event, $button, data) {
            $button.addClass('adding-to-cart');
        });

        $(document.body).on('added_to_cart', function(event, fragments, cart_hash, $button) {
            $button.removeClass('adding-to-cart');

            // Show success feedback
            showNotification('Product added to cart!', 'success');
        });

        /**
         * Notification System
         */
        function showNotification(message, type) {
            var notification = $('<div class="madhu-notification ' + type + '">' + message + '</div>');

            $('body').append(notification);

            setTimeout(function() {
                notification.addClass('show');
            }, 10);

            setTimeout(function() {
                notification.removeClass('show');
                setTimeout(function() {
                    notification.remove();
                }, 300);
            }, 3000);
        }

        /**
         * Smooth scroll for anchor links
         */
        $('a[href*="#"]:not([href="#"])').on('click', function(e) {
            var target = $(this.hash);
            if (target.length) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: target.offset().top - 100
                }, 500);
            }
        });

        /**
         * Product Quick View Enhancement
         */
        $('.products .product').each(function() {
            var $product = $(this);
            var $image = $product.find('.woo-entry-image, .attachment-woocommerce_thumbnail').first();

            // Add overlay for quick actions if not exists
            if (!$product.find('.product-overlay').length) {
                // Hover effect already handled by CSS
            }
        });

        /**
         * Quantity Input Enhancement
         */
        $(document).on('click', '.quantity .plus, .quantity .minus', function() {
            var $qty = $(this).closest('.quantity').find('.qty');
            var currentVal = parseFloat($qty.val());
            var max = parseFloat($qty.attr('max'));
            var min = parseFloat($qty.attr('min'));
            var step = parseFloat($qty.attr('step')) || 1;

            if (!currentVal || isNaN(currentVal)) currentVal = 0;
            if (isNaN(min)) min = 0;
            if (isNaN(max)) max = Infinity;

            if ($(this).hasClass('plus')) {
                if (currentVal < max) {
                    $qty.val(currentVal + step).trigger('change');
                }
            } else {
                if (currentVal > min) {
                    $qty.val(currentVal - step).trigger('change');
                }
            }
        });

        /**
         * Lazy Load Enhancement
         */
        if ('IntersectionObserver' in window) {
            var lazyImages = document.querySelectorAll('img.lazy, img[loading="lazy"]');

            var imageObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        var image = entry.target;
                        image.classList.add('fade-in');
                        imageObserver.unobserve(image);
                    }
                });
            }, {
                rootMargin: '50px 0px',
                threshold: 0.01
            });

            lazyImages.forEach(function(image) {
                imageObserver.observe(image);
            });
        }

        /**
         * Form Validation Enhancement
         */
        $('.woocommerce-checkout input, .woocommerce-checkout select').on('blur', function() {
            var $field = $(this);
            var $wrapper = $field.closest('.form-row');

            if ($field.prop('required') && !$field.val()) {
                $wrapper.addClass('has-error');
            } else {
                $wrapper.removeClass('has-error');
            }
        });

        /**
         * Mobile Menu Enhancement
         */
        $('.sidr-class-dropdown-menu > a').on('click', function(e) {
            var $parent = $(this).parent();
            if ($parent.find('.sub-menu').length) {
                e.preventDefault();
                $parent.toggleClass('expanded');
                $parent.find('.sub-menu').first().slideToggle(200);
            }
        });

        /**
         * Search Enhancement
         */
        var searchInput = $('.oceanwp-searchform input[type="search"], #searchform input[type="search"]');

        searchInput.on('focus', function() {
            $(this).closest('form').addClass('focused');
        }).on('blur', function() {
            $(this).closest('form').removeClass('focused');
        });

        /**
         * Back to Top Button
         */
        var backToTop = $('#scroll-top');

        $(window).on('scroll', function() {
            if ($(this).scrollTop() > 300) {
                backToTop.addClass('show');
            } else {
                backToTop.removeClass('show');
            }
        });

        /**
         * Product Gallery Touch Support
         */
        if ('ontouchstart' in window) {
            $('.woocommerce-product-gallery__image').on('touchstart', function(e) {
                var touch = e.originalEvent.touches[0];
                $(this).data('touchX', touch.pageX);
            }).on('touchend', function(e) {
                var startX = $(this).data('touchX');
                var touch = e.originalEvent.changedTouches[0];
                var diffX = touch.pageX - startX;

                if (Math.abs(diffX) > 50) {
                    if (diffX > 0) {
                        // Swipe right - previous image
                        $('.flex-prev').trigger('click');
                    } else {
                        // Swipe left - next image
                        $('.flex-next').trigger('click');
                    }
                }
            });
        }

        /**
         * Keyboard Navigation for Products
         */
        $('.products .product').attr('tabindex', '0').on('keydown', function(e) {
            if (e.key === 'Enter') {
                $(this).find('a.woocommerce-LoopProduct-link').first()[0].click();
            }
        });

        /**
         * ARIA Live Region for Cart Updates
         */
        if (!$('#cart-live-region').length) {
            $('body').append('<div id="cart-live-region" role="status" aria-live="polite" class="sr-only"></div>');
        }

        $(document.body).on('added_to_cart', function() {
            $('#cart-live-region').text('Item added to cart');
            setTimeout(function() {
                $('#cart-live-region').text('');
            }, 1000);
        });

    });

    /**
     * Window Load Events
     */
    $(window).on('load', function() {
        // Add loaded class for CSS animations
        $('body').addClass('page-loaded');

        // Initialize any remaining lazy loaded elements
        $('.products .product').addClass('fade-in');
    });

})(jQuery);

/**
 * Notification Styles (injected via JS to avoid extra HTTP request)
 */
(function() {
    var style = document.createElement('style');
    style.textContent = `
        .madhu-notification {
            position: fixed;
            bottom: 100px;
            right: 20px;
            padding: 16px 24px;
            background: #1a1a1a;
            color: #fff;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
            z-index: 99999;
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.3s ease, transform 0.3s ease;
            font-family: 'Roboto', sans-serif;
            font-size: 14px;
        }
        .madhu-notification.show {
            opacity: 1;
            transform: translateY(0);
        }
        .madhu-notification.success {
            background: #2e7d32;
        }
        .madhu-notification.error {
            background: #c9302c;
        }
        @media (max-width: 768px) {
            .madhu-notification {
                right: 10px;
                left: 10px;
                bottom: 90px;
            }
        }
    `;
    document.head.appendChild(style);
})();
