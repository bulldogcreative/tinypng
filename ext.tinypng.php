<?php

class Tinypng_ext
{
    public $name = 'TinyPNG';
    public $version = '2.0.0';
    public $description = 'Optimize your images for performance!';
    public $settings_exist = 'y';
    public $docs_url = 'https://github.com/BulldogCreative/tinypng/';

    public $settings = array();

    public function __construct($settings = array())
    {
        $this->settings = $settings;
    }

    public function activate_extension()
    {
        $this->settings = array(
            'api_key' => '',
        );

        $data = array(
            'class'    => __CLASS__,
            'method'   => 'tiny',
            'hook'     => 'file_after_save',
            'settings' => serialize($this->settings),
            'priority' => 10,
            'version'  => $this->version,
            'enabled'  => 'y',
        );

        ee()->db->insert('extensions', $data);
    }

    public function update_extension($current = '')
    {
        if($current == '' OR $current == $this->version) {
            return FALSE;
        }

        ee()->db->where('class', __CLASS__);
        ee()->db->update('extensions', array('version' => $this->version));
    }

    public function disable_extension()
    {
        ee()->db->where('class', __CLASS__);
        ee()->db->delete('extensions');
    }
}
