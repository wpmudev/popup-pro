(function($) {
    $(document).on('Upfront:loaded', function(){
        var dependencies = [
            Upfront.popup_config.baseUrl + 'js/upfront-element.js'
        ];
        require(dependencies, function(element){});
    });
})(jQuery);
