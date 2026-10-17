/**
 * Starts the count-up animation on the numbers rendered by the default
 * ElementStatCounters template.
 *
 * Uses IntersectionObserver (and Counter-Up2) only, so the module works on a
 * front end that ships no third-party DOM/scroll library.
 *
 * Requires client/src/counter-up.min.js, which exposes window.counterUp.default.
 */
(function () {
    'use strict';

    /**
     * Number nodes rendered by the default template, one per stat.
     *
     * @type {string}
     */
    var COUNTER_SELECTOR = '.stats__stat .number';

    /**
     * Animation timing, passed straight through to Counter-Up2.
     *
     * @type {number}
     */
    var DURATION = 2500;
    var DELAY = 50;

    /**
     * Run the count-up animation on a single number node.
     *
     * @param {function} counterUp Counter-Up2's default export
     * @param {Element} element The number node to animate
     */
    function startCounter(counterUp, element) {
        counterUp(element, {
            duration: DURATION,
            delay: DELAY
        });
    }

    /**
     * Animate every counter on the page, each one exactly once.
     */
    function init() {
        var counterUp = window.counterUp && window.counterUp['default'];

        // Nothing to do without Counter-Up2 - fail quietly rather than throw.
        if (typeof counterUp !== 'function') {
            return;
        }

        var counters = document.querySelectorAll(COUNTER_SELECTOR);

        // No IntersectionObserver support: start immediately rather than never.
        if (!('IntersectionObserver' in window)) {
            Array.prototype.forEach.call(counters, function (counter) {
                startCounter(counterUp, counter);
            });

            return;
        }

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) {
                    return;
                }

                // Unobserve before starting, so a counter never runs twice.
                observer.unobserve(entry.target);
                startCounter(counterUp, entry.target);
            });
        });

        Array.prototype.forEach.call(counters, function (counter) {
            observer.observe(counter);
        });
    }

    // The template requires this script at the end of the body, so the DOM may
    // already be parsed by the time it runs.
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
