/**
 * Initialize the slide manager when page finishes loading.
 */
jQuery(document).ready(function($){
    var manager = new GocSlideManager($);
});


/**
 * A class (constructor function) to manage our slides.
 *
 * Remembers which slides the user has clicked, and saves
 * them to the hidden field 'manage-slides-selected'.
 *
 * Adds and removes 'is-selected' classes to slides
 * to provide a visual cue.
 *
 * @param $ jQuery object
 */
function GocSlideManager($) {

    /**
     * A single-dimensional array to hold our selected attachment ID integers.
     *
     * @param {array}
     */
    var selected = [];

    // Add initial selected IDs (that were previously saved)
    // to selected collection.
    var selectedIds = $('#manage-slides-selected').val();

    // Confirm that there are some selected slides (sometimes there won't be).
    if (selectedIds) {

        // Selected slides come in a string of comma-separated items,
        // so split them into pieces to make our job easier.
        var selectedIds = selectedIds.split(',');

        // Add each slide ID to our array of selected slide IDs.
        for (var selectedId in selectedIds) {
            var id = parseInt(selectedIds[selectedId]);
            selected.push(id);
        }
    }

    /**
     * Saves the selected slides to our hidden field.
     *
     * Slides are saved every time a slide is clicked.
     */
    var save = function save() {

        // JS will automatically translate our array
        // to a string of comma-separated items.
        $('#manage-slides-selected').val(selected);
    }

    /**
     * Selects or de-selects a slide whenever one is clicked,
     * and adds/removes CSS 'is-selected' class, which provides
     * the visual cue for individual, selected slides.
     */
    $('.goc-slide').click(function(){

        // Get full ID of element.
        var elementId = $(this).attr('id');

        // Get the number ID of the element.
        var selectedId = parseInt(elementId.replace('goc-slide-', ''));

        // Determine where, if anywhere, this ID is in the
        // selected collection.
        var foundIndex = $.inArray(selectedId, selected);

        // Check if the slide is in our array of selections.
        if (foundIndex === -1) {
            // Slide wasn't selected, select it.
            selected.push(selectedId);
            $(this).addClass('is-selected');
        } else {
            // Slide was selected, deselect it.
            selected.splice(foundIndex, 1);
            $(this).removeClass('is-selected');
        }

        // Save latest selected slides to our hidden field.
        save();

    });

}