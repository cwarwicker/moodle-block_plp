// These scripts are always included on every page in the block.
define(['jquery', 'core/str', 'block_plp/flatpickr', 'block_plp/raty', 'block_plp/freezetable'],
    function($, str, flatpickr, raty, freezetable) {

    var object = {};

    // Initialise the scripts.
    object.init = function() {

        // Bind scrollable, frozen tables (Must be called on the div above the table).
        $('div.block_plp-frozen-table').each(function(){

            // Default settings.
            let scrollable = true;
            let shadow = true;
            let freezeHead = true;
            let freezeColumn = true; // Currently seems to be a bug in the plugin and it doesn't work with no frozen cols.

            // Check if we want to freeze any columns.
            let columnNum = $(this).data('freeze-cols');
            if (columnNum === undefined) {
                columnNum = 1;
            }

            $(this).freezeTable({
                scrollable: scrollable,
                shadow: shadow,
                freezeHead: freezeHead,
                freezeColumn: freezeColumn,
                columnNum: columnNum,
            });

        });

        // Bind date pickers.
        $('input.block_plp-datepicker').each(function(){

            let min = (typeof $(this).data('min-date') !== 'undefined') ? $(this).data('min-date') : null;
            let max = (typeof $(this).data('max-date') !== 'undefined') ? $(this).data('max-date') : null;
            let alt = (typeof $(this).data('alt-date') !== 'undefined') ? $(this).data('alt-date') : false;
            let alt_format = (typeof $(this).data('alt-date-format') !== 'undefined') ? $(this).data('alt-date-format') : '';

            $(this).flatpickr({
                dateFormat: 'd-m-y',
                altInput: alt,
                altFormat: alt_format,
                minDate: min,
                maxDate: max
            });

        });

        // Load ratings. Need to loop through these as target is dependant on element data.
        $('div.block_plp-rating').each(function(){

            let element = $(this).data('element');
            let number = $(this).data('number');

            $(this).raty({
                starType: 'i',
                number: number,
                cancelButton: true,
                cancelPlace: 'right',
                hints: null,
                target: '#' + element,
                targetType: 'score',
                targetKeep: true
            });

        });

        // Load ratings for display only.
        $('div.block_plp-rating-display').raty({
            starType: 'i',
            readOnly: true
        });

    };

    // Get a JSON object array of strings, using the actual keys, not numeric keys, and run a callback, passing them in.
    // This is basically the same as the core get_strings except we can use actual component keys instead of numeric ones, which are
    // harder to debug and understand from an outside view.
    // Puts them in format: "component:key" in case we use same key across different components. Not very likely, but possible.
    object.get_strings = function(params, callback){

        let keys = params.map(obj => obj.component + ':' + obj.key);
        let strings = {};

        str.get_strings(params).then(function(results){
            $(results).each(function(indx, string){
                let key = keys[indx];
                strings[key] = string;
            });
            callback(strings);
        });

    };

    // Return client object.
    return object;

});