(function ($) {
    $(window).on('load', function () {
        var counterUp = window.counterUp['default']; // import counterUp from "counterup2"
        var counters = $('.stats__stat .number');

        counters.show();

        /* Start counting, do this on DOM ready or with Waypoints. */
        counters.each(function (ignore, counter) {
            var waypoint = new Waypoint( {
                element: $(this),
                handler: function() {
                    counterUp(counter, {
                        duration: 2500,
                        delay: 50
                    });
                    this.destroy();
                },
                offset: 'bottom-in-view',
            } );
        });
    });
})(jQuery);
