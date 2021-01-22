define(['jquery', 'jqueryui', 'block_plp/main'], function($, ui, plp) {

    var object = {};
    object._params = {};
    object._num = 0;
    object.pages = [];

    // Initialise the scripts.
    object.init = function(params) {

        // Store passed parameters.
        object._params = params;

        // Bind all events on page load.
        object.bind();

        // TODO: Remove
        console.log('PLP LOADED');

    };

    // Bind element events.
    object.bind = function(){

        // Toggle the name input/span when we click out of the input.
        $('input.block_plp-plugin-name-input').off('blur').on('blur', function(){

            // Hide the input.
            $(this).hide();

            // Update the page object with the new name.
            let pagenum = $(this).data('pagenum');
            let page = object.find_page(pagenum);
            page.name = $(this).val();

            // Update the text on the link.
            $(this).siblings('span.block_plp-plugin-name-span').text($(this).val());

            // Show the span again.
            $(this).siblings('span.block_plp-plugin-name-span').show();

        });

        // Call an action on a plugin page.
        $('a.block_plp-plugin_pages_action').off('click').on('click', function(e){

            let action = $(this).data('action');
            let pagenum = $(this).data('pagenum');

            // Add a page to the list of pages.
            if (action === 'add_page') {
                object.add_page();
            }

            // Load the edit screens for this page.
            if (action === 'rename_page') {

                let item = $('li#block_plp-page-' + pagenum);
                $(item).find('span.block_plp-plugin-name-span').hide();
                $(item).find('input.block_plp-plugin-name-input').show();
                $(item).find('input.block_plp-plugin-name-input').focus();

            }

            // Delete the page and any sections beneath it.
            if (action === 'delete_page') {
                object.delete_page(pagenum);
            }

            // Add a section to the page.
            if (action === 'add_section') {
                object.add_section(pagenum);
            }

            // Prevent the link from doing anything else.
            e.preventDefault();

        });
        //
        // // Call an action on a plugin section.
        // $('a.block_plp-plugin_section_action').off('click').on('click', function(e){
        //
        //     let action = $(this).data('action');
        //     let pagenum = $(this).data('page');
        //     let num = $(this).data('num');
        //
        //     // Load the edit screens for this page.
        //     if (action === 'edit') {
        //         // TODO.
        //     }
        //
        //     // Delete the page and any sections beneath it.
        //     if (action === 'delete') {
        //         let page = object.find_page(pagenum);
        //         object.delete_section(page, num);
        //     }
        //
        //     // Prevent the link from doing anything else.
        //     e.preventDefault();
        //
        // });
        //
        // Bind sortable plugin pages.
        $('#block_plp-plugin_pages').sortable({
            handle: '.block_plp-plugin_page_handle',
            update: object.update_page_orders,
            placeholder: 'ui-state-highlight'
        });
        //
        // // Bind sortable page sections.
        // $('#block_plp-plugin_sections_sortable').sortable({
        //     handle: '.block_plp-plugin_section_handle',
        //     update: object.update_section_orders
        // });
        //
        // // Bind updating the name in the plugin object as you type.
        // $('.block_plp-plugin_page_name').off('input').on('input', function(e){
        //     let num = $(this).data('num');
        //     let page = object.find_page(num);
        //     page.name = $(this).val();
        // });
        //
        // $('a#block_plp-plugin_section_add_new').off('click').on('click', function(e){
        //
        //     // Page number should be stored in the data-page attribute.
        //     let num = $(this).data('page');
        //     let page = object.find_page(num);
        //     object.add_section(page);
        //
        //     // Prevent the link from doing anything else.
        //     e.preventDefault();
        //
        // });
        //
        // // Bind updating the section name in the page object as you type.
        // $('.block_plp-plugin_page_section_name').off('input').on('input', function(e){
        //
        //     // Get the page number and section number.
        //     let pagenum = $(this).data('page');
        //     let num = $(this).data('num');
        //
        //     // Find the section on the page object.
        //     let section = object.find_section(object.find_page(pagenum), num);
        //
        //     // Update its name.
        //     section.name = $(this).val();
        //
        // });
        //
        // // Update the section object, if its type, location of confidentiality is updated.
        // $('.block_plp-plugin_page_section_type, .block_plp-plugin_page_section_location,' +
        //     ' .block_plp-plugin_page_section_confidentiality').off('change').on('change', function(e){
        //
        //     // Get the page number and section number.
        //     let pagenum = $(this).data('page');
        //     let num = $(this).data('num');
        //
        //     // Find the section on the page object.
        //     let section = object.find_section(object.find_page(pagenum), num);
        //
        //     // Get the table row for this section.
        //     let row = $('tr.block_plp-page_section-' + num);
        //
        //     // Update the section object based on the values found in the inputs.
        //     section.type = $(row).find('.block_plp-plugin_page_section_type').val();
        //     section.location = $(row).find('.block_plp-plugin_page_section_location').val();
        //     section.confidentiality = $(row).find('.block_plp-plugin_page_section_confidentiality').val();
        //
        // });

    };

    // Delete a page.
    object.delete_page = function(num){

        // Remove the list item and anything beneath it.
        $('.block_plp-page-' + num).remove();

        // Remove the page object.
        object.pages = object.pages.filter(function(item){
            return (item.number !== num);
        });

        // Re-order the sort order of the pages, now that one is removed.
        object.update_page_orders();

    };
    //
    // // Delete a section.
    // object.delete_section = function(page, num){
    //
    //     // Remove the table row.
    //     $('.block_plp-page_section-' + num).remove();
    //
    //     // Remove the page object.
    //     page.sections = page.sections.filter(function(item){
    //         return (item.number !== num);
    //     });
    //
    //     // Re-order the sort order of the pages, now that one is removed.
    //     object.update_section_orders();
    //
    // };
    //
    // Update the order of the pages, based on their position in the table.
    object.update_page_orders = function(){

        let sort = 0;
        let items = $('ul#block_plp-plugin_pages > li.block_plp-page');

        // Loop through the items.
        $(items).each(function(){

            // Find the 'num' data attribute so we can find the corresponding page object.
            let pagenum = $(this).data('pagenum');
            let page = object.find_page(pagenum);

            // Set it's new sort value and then increment for the next one.
            page.sortnum = sort;
            sort++;

        });

    };
    //
    // // Update the order of the sections, based on their position in the table.
    // object.update_section_orders = function(){
    //
    //     // Work out which page number is selected.
    //     var pagenum = $('a#block_plp-plugin_section_add_new').data('page');
    //     let page = object.find_page(pagenum);
    //
    //     let sort = 0;
    //     let rows = $('tr.block_plp-page-' + pagenum);
    //
    //     // Loop through the table rows.
    //     $(rows).each(function(){
    //
    //         // Find the 'num' data attribute so we can find the corresponding page object.
    //         let num = $(this).find('input').first().data('num');
    //         let section = object.find_section(page, num);
    //
    //         // Set it's new sort value and then increment for the next one.
    //         section.sortnum = sort;
    //         sort++;
    //
    //     });
    //
    // };
    //
    // Find a page by its number in the array of page objects.
    object.find_page = function(num){

        for (let i = 0; i < object.pages.length; i++) {

            let page = object.pages[i];
            if (page.number === num) {
                return page;
            }

        }

        return null;

    };
    //
    // // Find a section by its number on a specific page object.
    // object.find_section = function(page, num) {
    //
    //     for (let i = 0; i < page.sections.length; i++) {
    //
    //         let section = page.sections[i];
    //         if (section.number === num) {
    //             return section;
    //         }
    //
    //     }
    //
    //     return null;
    //
    // };

    // Add a new page to the plugin.
    object.add_page = function(){

        // Build variables to use in creating the page elements.
        let num = object._num;
        let name = '';
        let actions = '';
        object._num++;

        if (name === '') {
            name = 'Page ' + num;
        }

        object.pages.push({
            'number': num,
            'sortnum': num,
            'name': name,
            'sections': [],
            '_sectionnum': 0
        });

        // Create the action links.
        // Move the page - this sets the sortable handle.
        actions +='<a href="#" class="block_plp-plugin_page_handle block_plp-action" data-pagenum="' + num + '"><i class="fa fa-arrows"></i></a>';

        // Delete the page.
        actions += '<a href="#" class="block_plp-plugin_pages_action block_plp-action" data-action="delete_page" data-pagenum="' + num + '"><i' +
            ' class="fa fa-trash"></i></a>';

        // Add a new section to the page.
        actions += '<a href="#" class="block_plp-plugin_pages_action block_plp-action" data-action="add_section" data-pagenum="' + num + '"><i' +
            ' class="fa fa-plus"></i></a>';

        // Edit the page name.
        actions +='<a href="#" class="block_plp-plugin_pages_action block_plp-action" data-action="rename_page" data-pagenum="' + num + '"><i' +
            ' class="fa fa-edit"></i></a>';

        $('#block_plp-plugin_pages').append('<li id="block_plp-page-' + num + '" class="block_plp-page-' + num + ' block_plp-page" data-pagenum="' + num + '">' +
            '<input type="text" class="block_plp-plugin-name-input" data-pagenum="' + num + '" style="display:none;" value="' + name + '">' +
            '<span class="block_plp-plugin-name-span">' + name + '</span>' +
            '<br>' + actions +
            '<ul id="block_plp-page-sections-' + num + '"></ul>' +
            '</li>');

        // Get strings and then apply titles to actions.
        plp.get_strings([
            {key: 'move', component: 'moodle'},
            {key: 'delete', component: 'moodle'},
            {key: 'edit', component: 'moodle'},
            {key: 'plugin:add:section', component: 'block_plp'}
        ], function(s){
            $('#block_plp-page-' + num + ' a.block_plp-plugin_page_handle').attr('title', s['moodle:move']);
            $('#block_plp-page-' + num + ' a.block_plp-plugin_pages_action[data-action="delete_page"]').attr('title', s['moodle:delete']);
            $('#block_plp-page-' + num + ' a.block_plp-plugin_pages_action[data-action="rename_page"]').attr('title', s['moodle:edit']);
            $('#block_plp-page-' + num + ' a.block_plp-plugin_pages_action[data-action="add_section"]').attr('title', s['block_plp:plugin:add:section']);
        });

        // Re-call the bind elements, for these new elements we just added.
        object.bind();

        // Update the sort orders again so they are correct.
        object.update_page_orders();

    };

    // // Add a section to a plugin page.
    // object.add_section = function(page){
    //
    //     // Get the section number to use for this new section.
    //     let sectionnum = page._sectionnum;
    //
    //     // Increment section number for next section.
    //     page._sectionnum++;
    //
    //     let data = {
    //         'number': sectionnum,
    //         'sortnum': sectionnum,
    //         'name': '',
    //         'type': '',
    //         'location': '',
    //         'confidentiality': ''
    //     };
    //
    //     // Add the new section to the page's sections array.
    //     page.sections.push(data);
    //
    //     // Show the section.
    //     object.show_section(page, data);
    //
    // };
    //
    // // Add a section's HTML to the sections table.
    // object.show_section = function(page, section){
    //
    //     // Create variable to store extra html in.
    //     let html = {
    //         'types': '<option></option>',
    //         'locations': '<option></option>',
    //         'confidentiality': '' // This doesn't need a default empty option, as most of the time it won't be changed from public.
    //     };
    //
    //     // Load the possible valid types into the select menu.
    //     for (let i = 0; i < object._params.section_types.length; i++) {
    //         html.types += '<option value="' + object._params.section_types[i] + '">' + object._params.section_types[i] + '</option>';
    //     }
    //
    //     // Load the possible valid locations into the select menu.
    //     for (let i = 0; i < object._params.section_locations.length; i++) {
    //         html.locations += '<option value="' + object._params.section_locations[i] + '">' + object._params.section_locations[i] + '</option>';
    //     }
    //
    //     // Load the possible valid confidentiality types into the select menu.
    //     // Moodle annoyingly converts this array with numeric keys into an JSON object, so we need to loop through the keys.
    //     Object.keys(object._params.section_confidentiality).forEach(function(index){
    //         html.confidentiality += '<option value="' + index + '">' + object._params.section_confidentiality[index] + '</option>';
    //     });
    //
    //     // Section action links.
    //     let actions = '';
    //
    //     // Move the section - this sets the sortable handle.
    //     actions += '<a href="#" class="block_plp-plugin_section_handle block_plp-action" data-page="' + page.number + '"><i class="fa fa-arrows"></i></a>';
    //
    //     // // Edit the page.
    //     // actions +='<a href="#" class="block_plp-plugin_section_action block_plp-action" data-action="edit" data-num="' + num + '"><i' +
    //     //     ' class="fa fa-edit"></i></a>';
    //
    //     // Delete the section.
    //     actions += '<a href="#" class="block_plp-plugin_section_action block_plp-action" data-action="delete" data-page="' + page.number + '"' +
    //         ' data-num="' + section.number + '"><i class="fa fa-trash"></i></a>';
    //
    //     // Append the new row for this section to the sections table.
    //     // $('table#block_plp-plugin_sections_table > tbody').append('<tr class="block_plp-page_section block_plp-page-' + page.number + ' block_plp-page_section-' + section.number + '">' +
    //     //     '<td><input type="text" class="block_plp-plugin_page_section_name" data-page="' + page.number +'" data-num="' + section.number + '" value="' + section.name + '" /></td>' +
    //     //     '<td><select class="block_plp-plugin_page_section_type" data-page="' + page.number +'" data-num="' + section.number + '">' + html.types + '</select></td>' +
    //     //     '<td><select class="block_plp-plugin_page_section_location" data-page="' + page.number +'" data-num="' + section.number + '">' + html.locations + '</select></td>' +
    //     //     '<td><select class="block_plp-plugin_page_section_confidentiality" data-page="' + page.number +'" data-num="' + section.number + '">' + html.confidentiality + '</select></td>' +
    //     //     '<td>' + actions + '</td>' +
    //     //     '</tr>');
    //
    //     $('#block_plp-plugin_sections_table').append('<div class="row">' +
    //         '<div class="col"><input type="text" class="block_plp-plugin_page_section_name form-control" data-page="' + page.number +'" data-num="' + section.number + '" value="' + section.name + '" /></div>' +
    //         '<div class="col"><select class="block_plp-plugin_page_section_type form-control" data-page="' + page.number +'" data-num="' + section.number + '">' + html.types + '</select></div>' +
    //         '<div class="col"><select class="block_plp-plugin_page_section_location form-control" data-page="' + page.number +'" data-num="' + section.number + '">' + html.locations + '</select></div>' +
    //         '<div class="col"><select class="block_plp-plugin_page_section_confidentiality form-control" data-page="' + page.number +'" data-num="' + section.number + '">' + html.confidentiality + '</select></div>' +
    //         '<div class="col">' + actions + '</div>' +
    //         '</div>');
    //
    //     // Re-call the bind elements, for these new elements we just added.
    //     object.bind();
    //
    // };

    // Return client object.
    return object;

});