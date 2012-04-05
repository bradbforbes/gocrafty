<?php

/**
 * Displays and processes the 'Slideshows' page.
 */
class SlideshowsAdminPage extends AdminPage
{
    public function __construct($title, $slug = null, $capability = null, $menuTitle = null, $menuPosition = null, $iconUrl = null) {
        parent::__construct($title, $slug, $capability, $menuTitle, $menuPosition, $iconUrl);
    }

    public function doDisplay() {
        global $wpdb;

        // Initialize an array of Slideshow objects to contain
        // all of Slideshows to display on this page.
        $slideshows = array();

        // Retrieve all of our Slideshows rows.
        $slideshowRows = $wpdb->get_results("SELECT * FROM " . GOC_TABLES_SLIDESHOWS . " ORDER BY title");

        // Instantiate and load each Slideshow.
        foreach ($slideshowRows as $row) {
            $slideshow = new Slideshow($row->id, $row->title);
            $slideshow->load();
            $slideshows[] = $slideshow;
        }


    ?>



    <!--
    ---------------------------------------------------------
        Draw Courses
    ---------------------------------------------------------

        Draw all MedEd Courses with a table of options
        for all available credit types.

    ---------------------------------------------------------
    -->
    <div class="wrap">

        <?php if (isset($_SESSION['goc-slideshows-message'])): ?>
        <div class="updated" style="margin: 20px 0 0 0;">
            <p><?php echo $_SESSION['goc-slideshows-message']; ?></p>
        </div>
        <?php unset($_SESSION['goc-slideshows-message']); ?>
        <?php endif; ?>

        <div class="goc-header">Slideshows</div>
        <div class="goc-instructions">Click a Slideshow preview to add or remove Slides.</div>

        <?php if (count($slideshows) > 0): ?>
        <?php foreach ($slideshows as $slideshow): ?>
            <a class="goc-slideshow-link" href="admin.php?page=goc-manage-slideshow&slideshow_id=<?php echo $slideshow->getId(); ?>">
                <div class="goc-slideshow">
                    <div class="title">
                        <?php echo $slideshow->getTitle(); ?>
                    </div>
                    <div class="slides">
                        <?php foreach ($slideshow->getSlides() as $slide): ?>
                        <?php echo wp_get_attachment_image($slide->getAttachmentId(), 'thumbnail'); ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>

        <div style="padding: 0 20px 20px 20px; text-align: center;">
            <form method="POST" action="admin.php?page=goc-slideshows">
                Delete a Slideshow permanently: <select id="delete-slideshow" name="delete-slideshow">
                <?php foreach ($slideshows as $slideshow): ?>
                <option value="<?php echo $slideshow->getId(); ?>"><?php echo $slideshow->getTitle(); ?></option>
                <?php endforeach; ?>
            </select>
                <input type="submit" name="page-delete-slideshow" value="Delete Slideshow" />
            </form>
        </div>
        <?php else: ?>
        <div style="padding: 0 0 20px 20px;">You have not started any Slideshows yet.</div>
        <?php endif; ?>

        <div class="goc-header">Add a new Slideshow</div>
        <div class="goc-instructions">After adding a Slideshow, you can select images from your Media Library to include in it.</div>

        <form method="POST" action="admin.php?page=goc-slideshows">
        <div class="goc-form">


            <div class="label">
                Title
            </div>
            <div class="input">
                <input type="text" id="new-slideshow-title" name="new-slideshow-title" />
            </div>
            <div class="clear"></div>

            <input type="submit" value="Add" class="big-submit" name="page-add-slideshow" />
        </div>
            </form>


    </div>
    <?php
    }

    public function doProcess() {
        global $wpdb;

        if (isset($_POST['page-add-slideshow'])) {

            $slideshowTitle = $_POST['new-slideshow-title'];

            if (empty($slideshowTitle)) {
                $_SESSION['goc-slideshows-message'] = '<b>Please provide a title when adding a Slideshow.</b>';
            } else {

                // More thorough than htmlentities().
                $slideshowTitle = mb_convert_encoding($slideshowTitle, 'HTML-ENTITIES');

                // Instantiate our Slideshow with the provided data.
                $slideshow = new Slideshow(null, $slideshowTitle);

                // Save our new Slideshow to the DB.
                $slideshow->save();

                $_SESSION['goc-slideshows-message'] = '<b>Slideshow added!</b>  <a href="admin.php?page=goc-manage-slideshow&slideshow_id=' . $slideshow->getId() . '">Start adding some slides to it!</a>';

            }
        }

        if (isset($_POST['page-delete-slideshow'])) {

            $slideshowId = $_POST['delete-slideshow'];
            
            $query = "DELETE FROM " . GOC_TABLES_SLIDESHOWS . " WHERE id = '$slideshowId'";
            $wpdb->query($query);
            
            $_SESSION['goc-slideshows-message'] = '<b>Slideshow was deleted successfully.</b>';
        }
    }
}
