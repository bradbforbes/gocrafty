<?php

class Slideshow
{
    /**
     * The unique key assigned to this Slideshow.
     *
     * @var id
     */
    private $id;

    /**
     * The title of the slideshow.
     *
     * This is only used for administration and is never
     * seen by the public.
     *
     * @var string
     */
    private $title;

    /**
     * A collection of Slides.
     *
     * @see Slide
     * @var array|Slide[]
     */
    private $slides;


    public function __construct($id = null, $title = null) {
        $this->id = $id;
        $this->title = $title;
        $this->slides = array();
    }

    public function getId() { return $this->id; }
    public function getTitle() { return $this->title; }
    public function getSlides() { return $this->slides; }
    public function getSlideIds() {
        $ids = '';
        foreach ($this->slides as $slide) {
            $ids .= $slide->getAttachmentId() . ',';
        }
        $ids = substr($ids, 0, strlen($ids) - 1);
        return $ids;
    }

    public function setId($value) { $this->id = $value; }
    public function setTitle($value) { $this->title = $value; }

    /**
     * Check our collection of Slides to see if any of them
     * are of the provided attachment ID.
     *
     * @param $attachmentId
     * @return bool
     */
    public function hasAttachmentId($attachmentId) {
        foreach ($this->slides as $slide) {
            if ($slide->getAttachmentId() == $attachmentId)
                return true;
        }
        return false;
    }


    /**
     * Instantiate and add a Slide by an attachment ID.
     *
     * @param $attachmentId
     */
    public function addSlide($attachmentId, $position = 1) {
        $this->slides[] = new Slide($attachmentId, $this->getId(), null, $position);
    }


    /**
     * Save this Slideshow and its Slides to the DB.
     */
    public function save() {
        global $wpdb;

        // If we have an assigned ID, update the existing row.
        if (isset($this->id) && $this->id) {

            // Update the basic data for this Slideshow.
            $wpdb->update(GOC_TABLES_SLIDESHOWS, array('title' => $this->title), array('id' => $this->id));

            // Delete all existing Slide assignments to this Slideshow.
            $wpdb->query("DELETE FROM " . GOC_TABLES_SLIDES . " WHERE slideshow_id = '$this->id'");
        }
        // If we don't have an ID, insert a new row.
        else {
            $data = array(
                  'title' => $this->title
            );
            $wpdb->insert(GOC_TABLES_SLIDESHOWS, $data);
            $this->id = $wpdb->insert_id;
        }

        // Insert a Slide assignment for each Slide selected in this Slideshow.
        foreach ($this->slides as $slide) {
            $data = array(
                'attachment_id' => $slide->getAttachmentId(),
                'slideshow_id' => $slide->getSlideshowId(),
                'position' => $slide->getPosition()
            );
            $wpdb->insert(GOC_TABLES_SLIDES, $data);
        }
    }

    /**
     * Load everything we need for this Slideshow from the DB.
     *
     * We must have an assigned ID before we can load from the DB.
     *
     * @param bool $loadSlides
     * @return bool
     * @throws \Exception
     */
    public function load($loadSlides = true) {
        global $wpdb;

        // Make sure an ID is loaded.
        if (!$this->id) {
            throw new \Exception("An ID must be provided before loading a Slideshow");
        }

        // Attempt to select the row assigned to this ID.
        $query = "SELECT * FROM " . GOC_TABLES_SLIDESHOWS . " WHERE id = '$this->id'";
        $slideshow = $wpdb->get_row($query);

        // Stop everything if we couldn't find a Slideshow row for this ID.
        if (!$slideshow) {
            throw new \Exception("A row could not be found for the Slideshow ID " . $this->id);
        }

        // Copy the row title in.
        $this->title = $slideshow->title;

        if ($loadSlides) {
            // Retrieve any slides that have been assigned to this Slideshow.
            $query = "SELECT * FROM " . GOC_TABLES_SLIDES . " WHERE slideshow_id = '$this->id' ORDER BY position ASC";
            $slides = $wpdb->get_results($query);

            // Load any slides we were able to find into this Slideshow.
            if ($slides) {
                foreach ($slides as $slide) {
                    $this->slides[] = new Slide($slide->attachment_id, $slide->slideshow_id, $slide->id, $slide->position);
                }
            }
        }

        // Return success
        return true;
    }

    /**
     * Print out the HTML required by Crafty.
     */
    public function printHTML() {

    }
}
