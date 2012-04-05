jQuery(document).ready(function($){


    var manager = new GocSlideManager($);

});

function GocSlideManager($) {

    console.log('Loaded');

    /**
     * An array to hold our selected attachment IDs.
     */
    var selected = [];

    // Add initial selected IDs (that were previously saved)
    // to selected collection.
    var selectedIds = $('#manage-slides-selected').val();
    if (selectedIds) {
        var selectedIds = selectedIds.split(',');
        for (var selectedId in selectedIds) {
            var id = parseInt(selectedIds[selectedId]);
            selected.push(id);
        }
        console.log('Selected:' + selected);
    }

    var save = function save() {

        console.log('Selected: ' + selected);

        $('#manage-slides-selected').val(selected);

    }

    $('.js-goc-slide').click(function(){

        console.log('Clicked');

        // Get full ID of element.
        var elementId = $(this).attr('id');

        // Get the number ID of the element.
        var selectedId = parseInt(elementId.replace('js-goc-slide-', ''));

        // Determine where, if anywhere, this ID is in the
        // selected collection.
        var foundIndex = $.inArray(selectedId, selected);

        // Add the ID if it is not in the collection yet.
        if (foundIndex === -1) {
            selected.push(selectedId);
            $('#' + elementId).addClass('selected');

        } else {
            selected.splice(foundIndex, 1);
            $('#' + elementId).removeClass('selected');
        }

        save();

    });

}