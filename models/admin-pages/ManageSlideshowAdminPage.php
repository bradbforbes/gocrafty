<?php

/**
 * Displays and processes the 'Manage Slideshow' page.
 */
class ManageSlideshowAdminPage extends AdminPage
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

        // Initialize a new array to hold our slides.
        // Each attachment post will be converted to a simplified
        // slide array of data.
        $slides = array();

        // Get all attachment posts in the blog.
        $attachmentPosts = get_posts(
            array(
                'numberposts' => -1,
                'post_type' => 'attachment',
                'orderby' => 'post_title'
            )
        );

        // Copy the post data to simplified slide arrays.
        foreach ($attachmentPosts as $post) {
            $image = wp_get_attachment_image_src($post->ID, 'thumbnail');
            $slides[] = array(
                'id' => $post->ID,
                'title' => $post->post_title,
                'description' => $post->post_content,
                'image' => $image[0]
            );
        }



    ?>


    <!--
    ---------------------------------------------------------
        Draw Courses
    ---------------------------------------------------------

        Draw all MedEd Courses with a table of options
        for all available credit types.

    -->
    <div class="wrap">

        <div class="goc-header"><?php echo $slideshow->getTitle(); ?> Slideshow</div>
        <div class="goc-instructions">Click images to add or remove them from the Slideshow.</div>

        <div id="goc-page"><div class="dress">

            <?php if (count($slides) > 0): ?>

                <div class="goc-codes">
                <div class="goc-code">[goc_display id="<?php echo $slideshow->getId(); ?>"]</div>
                <div class="goc-code-small">[goc_display id="<?php echo $slideshow->getId(); ?>" width="600" height="350" pagination="false" fadetime="550" delay="5000" border="0"]</div>
                </div>

            <?php $s = 0; $r = 0; $columns = 5; ?>
            <?php foreach ($slides as $slide): ?>

                <?php if ($slideshow->hasAttachmentId($slide['id'])) { $selected = 'selected'; } else { $selected = ''; } ?>
                <div class="goc-slide"><div id="js-goc-slide-<?php echo $slide['id']; ?>" class="dress js-goc-slide <?php echo $selected; ?> equal-height equal-height-<?php echo $r; ?>">
                    <div class="title"><?php echo trail_off($slide['title'], 18); ?></div>
                    <img class="image" src="<?php echo $slide['image']; ?>" />
                </div></div>

                <?php endforeach; ?>
            <div class="clear"></div>



            <form id="manage-slides" name="manage-slides" method="POST" action="admin.php?page=goc-manage-slideshow&slideshow_id=<?php echo $slideshowId; ?>">
                <input type="hidden" id="manage-slides-selected" name="manage-slides-selected" value="<?php echo $slideshow->getSlideIds(); ?>" />
                <input type="submit" class="big-submit" name="page-save-changes" value="Save" />
                <a class="goc-order-link" href="admin.php?page=goc-manage-order&slideshow_id=<?php echo $slideshowId; ?>">Order Slides</a>
                <a class="goc-go-back" href="admin.php?page=goc-slideshows">Go back to Slideshows</a>
            </form>
            <?php else: ?>
                Slides are just images you've added to your WordPress Media Library.  So first go do that, then come back.
                <form method="POST" action="media-new.php">
                    <input type="submit" class="big-submit" name="goc-go-to-media-library" value="Go to Media Library" />
                </form>
            <?php endif; ?>

        </div></div>



    </div>
    <?php
    }

    public function doProcess() {

        if (isset($_POST['page-save-changes'])) {

            // Initialize ID of the Slideshow we're currently managing.
            $slideshowId = $_GET['slideshow_id'];

            // Instantiate our Slideshow to add our selected Slides to.
            $slideshow = new Slideshow($slideshowId);
            $slideshow->load(false);

            // Add our selected Slides to the Slideshow.
            $selectedIds = explode(',', $_POST['manage-slides-selected']);
            foreach ($selectedIds as $selectedId) {
                $slideshow->addSlide($selectedId, $slideshow->getId());
            }

            // Save our Slideshow.
            $slideshow->save();

            // Redirect to the Slideshows main page.
            header('Location: admin.php?page=goc-slideshows');
        }
    }
}
