// These scripts are specific to the viewing/editing of a user's PLP.
define(['jquery', 'block_plp/main'], function($, scripts) {

    var object = {};

    // Initialise the scripts.
    object.init = function() {

        // Bind events.
        object.bind();

        // Run extra scripts.
        object.post_load();

        console.log('[PLP] Loaded'); // TODO: Remove this.

    };

    // Bind events to DOM elements.
    object.bind = function(){

        // Toggle between editing and viewing mode in a section.
        $('a.block_plp-toggle-edit').off('click').on('click', function(e){

            let id = $(this).data('id');

            // Toggle the editing and viewing elements for this section.
            $('.block_plp-section-' + id   + '-view, .block_plp-section-' + id + '-edit').toggle();

            e.preventDefault();

        });

        // Toggle item display.
        $('div.block_plp-item-header').off('click').on('click', function(){
            $(this).siblings('div.block_plp-item-content').slideToggle();
        });

    };

    // Any extra scripts to run once the main scripts have loaded.
    object.post_load = function(){

        // Looking at the plugin pages, if there are any with empty columns (3 columns by default), remove the empty columns
        // to allow the sections in the other columns to expand.
        $('div.block_plp-plugin-page').each(function(){
            let cols = $(this).find('div.block_plp-plugin-page-col');
            $(cols).each(function(){
                let html = $(this).html().trim();
                if (html === '') {
                    $(this).remove();
                }
            });
        });

    };

    // Return object.
    return object;

});