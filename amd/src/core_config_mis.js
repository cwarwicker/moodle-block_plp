define(['jquery', 'core/str'], function($, str) {

    var config = {};
    config.strings = [];

    // Initialise the config scripts.
    config.init = function() {

        config.bind();

        console.log('loaded config_mis src');

    };

    // Bind elements.
    config.bind = function() {

        $('a.block_plp-mis_connection_test').on('click', function(e){

            // Clear the output div.
            $('.block_plp-mis_connection_result').html('');

            var params = {};
            params.sesskey = M.cfg.sesskey;
            params.page = 'mis';
            params.action = 'test';
            params.type = $('select#id_type').val();
            params.host = $('input#id_host').val();
            params.database = $('input#id_database').val();
            params.user = $('input#id_user').val();
            params.pass = $('input#id_pass').val();

            // Test the connection using these details.
            $.post(M.cfg.wwwroot + '/blocks/plp/config.php', params, function(data){

                // If there was an error, display the message.
                if (data.result === true) {

                    // TODO: I don't like this way moodle does strings in JS. Make it a bit better, returning as json with keys.
                    str.get_strings([{key: 'mis:test:ok', component: 'block_plp'}]).then(function(results){
                        $('.block_plp-mis_connection_result').html('<div class="alert alert-success alert-block">' +
                            '<i class="fa fa-check-circle" /> ' + results[0] + '</div>');
                    });

                } else {
                    $('.block_plp-mis_connection_result').html('<div class="alert alert-danger alert-block">' +
                        '<i class="fa fa-exclamation-circle" /> ' + data.error + '</div>');
                }

            });

            e.preventDefault();

        });

    };

    // Return client object
    return config;

});