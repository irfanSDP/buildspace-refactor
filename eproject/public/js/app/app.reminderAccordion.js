(function () {

    var accordionIcons = {
        header: "fa fa-plus",    // custom icon class
        activeHeader: "fa fa-minus" // custom icon class
    };

    $(".accordion").accordion({
        autoHeight: true,
        heightStyle: "content",
        collapsible: true,
        animate: 300,
        icons: accordionIcons,
        header: "h4",
        navigation: true
    });

    // Triggered when the browser hash changes.
    $(window).on("hashchange", function () {
        var newString = location.hash.split('-');
        var tabId = newString[0];

        var tabContainer = $('a[href="' + tabId + '"]');

        // open the tab first
        tabContainer.tab('show');

        // click the accordion based on the active container tab
        var accordionIndex = $("#myTabContent1 .active h4").index($(location.hash));

        $(".accordion").accordion("option", "active", accordionIndex);
    });

    // Make sure this works on initial page load.
    $(window).trigger("hashchange");

})();