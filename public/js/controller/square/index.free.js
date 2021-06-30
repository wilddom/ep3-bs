(function() {

    $(document).ready(function() {

        var buttonBook = $("#sb-button");

        /* Alternate time choice */

        var atStart = $("#sb-alternate-times-start");

        if (atStart.length) {
            var buttonReload = $("#sb-reload-button");

            atStart.on("change", function() {
                var hrefBook = buttonBook.attr("href");
                var hrefReload = buttonReload.attr("href");

                if (atStart.val()) {
                    var choice = atStart.val();

                    buttonBook.attr("href", hrefBook.replace(/\&ts\=[0-9][0-9]:[0-9][0-9]/, "&ts=" + choice));
                    buttonReload.attr("href", hrefReload.replace(/\&ts\=[0-9][0-9]:[0-9][0-9]/, "&ts=" + choice));
                    buttonReload.click();
                }
            });

            atStart.show();
        }

        var atEnd = $("#sb-alternate-times-end");

        if (atEnd.length) {
            var buttonReload = $("#sb-reload-button");

            atEnd.on("change", function() {
                var hrefBook = buttonBook.attr("href");
                var hrefReload = buttonReload.attr("href");

                if (atEnd.val()) {
                    var choice = atEnd.val();

                    buttonBook.attr("href", hrefBook.replace(/\&te\=[0-9][0-9]:[0-9][0-9]/, "&te=" + choice));
                    buttonReload.attr("href", hrefReload.replace(/\&te\=[0-9][0-9]:[0-9][0-9]/, "&te=" + choice));
                    buttonReload.click();
                }
            });

            atEnd.show();
        }

        /* Alternate date choice */

        var ad = $("#sb-alternate-date");

        if (ad.length) {
            ad.on("change", "#sb-date-start-choice, #sb-date-end-choice, #sb-time-start-choice, #sb-time-end-choice", function() {
                var alteredHref = buttonBook.attr("href");

                alteredHref = alteredHref.replace(/ds=[^&]+/, "ds=" + ad.find("#sb-date-start-choice").val());
                alteredHref = alteredHref.replace(/de=[^&]+/, "de=" + ad.find("#sb-date-end-choice").val());
                alteredHref = alteredHref.replace(/ts=[^&]+/, "ts=" + ad.find("#sb-time-start-choice").val());
                alteredHref = alteredHref.replace(/te=[^&]+/, "te=" + ad.find("#sb-time-end-choice").val());

                alteredHref = alteredHref.replace(/\/booking\/customization/, "");

                buttonBook.attr("href", alteredHref);
                buttonBook.find("span").attr("class", "symbolic symbolic-reload").text(ad.data("sb-new-button"));
            });

            ad.show();
        }

    });

})();
