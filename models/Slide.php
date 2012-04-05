<?php

class Slide
{

    /**
     * The unique key assigned to this Slide.
     *
     * @var int
     */
    private $id;

    /**
     * The attachment post ID that contains the actual images.
     *
     * @var int
     */
    private $attachmentId;

    /**
     * The unique key of the Slideshow this Slide is contained in.
     *
     * @var int
     */
    private $slideshowId;

    /**
     * The position number of the Slide in the Slideshow.  Lowest comes first.
     *
     * @var int
     */
    private $position;


    /**
     * Instantiate a new Slide with a retrieve Slide row object from WPDB.
     *
     * Almost always, the row object will be retrieved and passed in by a Slideshow.
     *
     * @see Slideshow::load()
     * @param object $data A retrieved Slide row Object from WPDB
     */
    public function __construct($attachmentId, $slideshowId, $id = null, $position = null) {
        $this->attachmentId = $attachmentId;
        $this->slideshowId = $slideshowId;
        $this->id = $id;
        $this->position = $position;
    }
    
    public function getId() { return $this->id; }
    public function getAttachmentId() { return $this->attachmentId; }
    public function getSlideshowId() { return $this->slideshowId; }
    public function getPosition() { return $this->position; }

    public function setId($value) { $this->id = $value; }
    public function setAttachmentId($value) { $this->attachmentId = $value; }
    public function setSlideshowId($value) { $this->slideshowId = $value; }
    public function setPosition() { $this->position = $value; }
}
