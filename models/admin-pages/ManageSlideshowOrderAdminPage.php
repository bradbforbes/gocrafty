<?php

/**
 * Displays and processes the 'Manage Slideshow Order' page.
 */
class ManageSlideshowOrderAdminPage extends AdminPage
{
    public function __construct($title, $slug = null, $capability = null, $menuTitle = null, $menuPosition = null, $iconUrl = null) {
        parent::__construct($title, $slug, $capability, $menuTitle, $menuPosition, $iconUrl);
    }

    public function doDisplay() {

        // Initialize ID of the Slideshow we're currently managing.
        $slideshowId = $_GET['slideshow_id'];

        // TODO: Check that we find a Slideshow for GET ID.
        $slideshow = new Slideshow($slideshowId);
        $slideshow->load();

        $slides = $slideshow->getSlides();


    ?>

    <!--
        Handle Sortable Slides.

        A comma-separate list of order is saved to
        the hidden field 'goc-sortable-order' whenever
        the user does any sorting.
    -->
    <script>
        jQuery(document).ready(function($){

            console.log($('#goc-sortable-order').val());

            $( "#sortable" ).sortable({
                update: function(event, ui) { saveOrder(event, ui); }
            });
            $( "#sortable" ).disableSelection();

            function saveOrder(event) {
                var order = $('#sortable').sortable('toArray');
                order = order.toString();
                order = order.replace(/sortable-slide-/g, '');
                $('#goc-sortable-order').val(order);
            }
        });
    </script>

    <!-- Define some simple formatting of the sortable items -->
    <style>
        #sortable li { margin: 3px 3px 3px 0; padding: 1px; float: left; }
    </style>


    <div class="wrap">

        <div class="goc-header">
            <div class="goc-header-title"><?php echo $slideshow->getTitle(); ?> Slideshow</div>
            <div class="goc-header-instructions">Click and drag Slides around to reorder them.</div>
        </div>



        <div class="goc-content">

            <!-- Check that this Slideshow has some slides. -->
            <?php if (count($slides) > 0): ?>

            <ul id="sortable">
                <?php foreach ($slides as $slide): ?>
                    <li id="sortable-slide-<?php echo $slide->getAttachmentId(); ?>" class="ui-state-default" style="width: 175px; height: 200px;">
                        <?php $image = wp_get_attachment_image_src($slide->getAttachmentId(), 'thumbnail'); $src = $image[0]; ?>
                        <div id="goc-slide-<?php echo $slide->getId(); ?>" class="goc-slide is-selected">
                            <div class="goc-slide-title"><?php echo trail_off(get_the_title($slide->getAttachmentId()), 18); ?></div>
                            <img class="goc-slide-image" src="<?php echo $src; ?>" />
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="clear"></div>

            <!-- Print a simple form with 'Save' and 'Go back to Slideshows' options. -->
            <form id="manage-slides" name="manage-slides" method="POST" action="admin.php?page=goc-manage-order&slideshow_id=<?php echo $slideshowId; ?>">
                <input type="hidden" id="goc-sortable-order" name="goc-sortable-order" value="<?php echo $slideshow->getSlideIds(); ?>" />
                <input type="submit" class="big-submit" name="page-save-order-changes" value="Save" />
                <a class="goc-go-back" href="admin.php?page=goc-slideshows">Go back to Slideshows</a>
            </form>

            <!-- Display a simple message if this Slideshow has no slides yet. -->
            <?php else: ?>
                Slides are just images you've added to your WordPress Media Library.  So first go do that, then come back.
                <form method="POST" action="media-new.php">

                    <input type="submit" class="big-submit" name="goc-go-to-media-library" value="Go to Media Library" />
                </form>
            <?php endif; ?>

        </div>



    </div>
    <?php
    }

    public function doProcess() {

        if (isset($_POST['page-save-order-changes'])) {

            // Initialize ID of the Slideshow we're currently managing.
            $slideshowId = $_GET['slideshow_id'];

            // Instantiate our Slideshow to add our selected Slides to.
            $slideshow = new Slideshow($slideshowId);
            $slideshow->load(false);

            // Add our selected Slides to the Slideshow.
            $sortedIds = explode(',', $_POST['goc-sortable-order']);
            $position = 0;
            foreach ($sortedIds as $sortedId) {
                $position++;
                $slideshow->addSlide($sortedId, $position);
            }

            // Save our Slideshow.
            $slideshow->save();

            // Redirect to the Slideshows main page.
            header('Location: admin.php?page=goc-slideshows');
        }
    }
}
