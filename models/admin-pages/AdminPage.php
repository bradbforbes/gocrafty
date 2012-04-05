<?php

/**
 * Acts as a base for implemented Admin Pages.
 *
 * Handles hooks to register page with WP and
 * display it in Admin Menu.
 *
 * Implements the Template Method design pattern for
 * displaying and processing individual pages.
 */
class AdminPage
{
    /**
     * The title of the Admin Page.
     *
     * Used to generate a slug if a unique one isn't provided.
     *
     * Displayed as-is in the Admin Menu if menuTitle is not specified.
     *
     * @var string
     */
    private $title;

    /**
     * The unique identifier string required by WP.
     *
     * Optional, title can be filtered and used instead.
     *
     * @var null|string
     */
    private $slug;

    /**
     * The WP User capability required to be able to use this page.
     *
     * Defaults to 'activate_plugins'.  Seems reasonable enough.
     *
     * @var null|string
     */
    private $capability;

    /**
     * A custom title to display in the Admin Menu.
     *
     * Optional, 'title' used instead if not provided.
     *
     * @var null|string
     */
    private $menuTitle;

    /**
     * An integer specifying where the page should appear in the
     * Admin Menu.
     *
     * If omitted, the page will be placed at the bottom.
     *
     * @link http://codex.wordpress.org/Function_Reference/add_menu_page
     * @var null|int
     */
    private $menuPosition;

    /**
     * An absolute URL to a custom icon image to display on the Admin Menu
     * for this page.  Icon should be 16x16.  Supports PNG transparency.
     *
     * @var null|string
     */
    private $iconUrl;

    /**
     * This appears to be unused.
     *
     * @var string
     */
    private $processError;


    /**
     * Initialize the page, smartly setting some helpful defaults for us.
     *
     * @param $title
     * @param null $slug
     * @param null $capability
     * @param null $menuTitle
     * @param null $menuPosition
     * @param null $iconUrl
     */
    public function __construct($title, $slug = null, $capability = null, $menuTitle = null, $menuPosition = null, $iconUrl = null) {
        $this->title = $title;

        if (isset($slug))
            $this->slug = $slug;
        else
            $this->slug = sanitize_title($title);

        if (isset($capability))
            $this->capability = $capability;
        else
            $this->capability = 'activate_plugins';

        if (isset($menuTitle))
            $this->menuTitle = $menuTitle;
        else
            $this->menuTitle = $title;

        if (isset($menuPosition))
            $this->menuPosition = $menuPosition;
        else
            $this->menuPosition = null;

        if (isset($iconUrl))
            $this->iconUrl = $iconUrl;
        else
            $this->iconUrl = '';


    }

    /**
     * Hook the page into WP.
     */
    public function activate() {
        add_menu_page($this->title, $this->menuTitle, $this->capability, $this->slug, array($this, 'display'), $this->iconUrl, $this->menuPosition);

        // Hide the page entirely from the Admin Menu.
        if ($this->menuPosition === -1) {
            remove_menu_page($this->slug);
        }
    }

    /**
     * Template Method pointing to child.
     */
    public function display() {
        $this->doDisplay();
    }

    /**
     * Template Method pointing to child.
     *
     * All registered pages are processed at 'init' hook.  This means child pages need
     * to validate carefully before they actually do any processing.
     */
    public function process() {
        $this->doProcess();
    }

    public function setProcessError($value) {
        $this->processError = $value;
    }

    public function getProcessError() { return $this->processError; }
    public function hasProcessError() { return !empty($this->processError); }
}
