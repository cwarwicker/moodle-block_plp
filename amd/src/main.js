// These scripts are always included on every page in the plugin.
define(['jquery', 'core/str', 'block_plp/datepicker', 'block_plp/raty'], function($, str, datepicker, raty) {

    var object = {};

    // Initialise the scripts.
    object.init = function() {

        // Load datepickers.
        $('input.block_plp-datepicker').datepicker({
            format: 'dd-mm-yyyy',
            todayBtn: "linked",
            todayHighlight: true,
            weekStart: 1,
            clearBtn: true
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