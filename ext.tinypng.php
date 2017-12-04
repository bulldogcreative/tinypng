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

    public function settings()
    {
        $settings = array();
        $settings['api_key'] = array('i', '', '');

        return $settings;
    }

    public function save_settings()
    {
        if(empty($_POST)) {
            show_error(lang('unauthorized_access'));
        }

        ee()->lang->loadfile('tinypng');

        ee('CP/Alert')->makeInline('tinypng-save')
            ->asSuccess()
            ->withTitle(lang('message_success'))
            ->addToBody(lang('preferences_updated'))
            ->defer();

        ee()->functions->redirect(ee('CP/URL')->make('addons/settings/tinypng'));
    }

    public function tiny($file_id, $data)
    {
        if(strpose($data['mime_type'], 'image') === false) {
            return false;
        }
    }

    private function getPath($uploadLocationId)
    {
        $path = ee()->db->select('server_path')
            ->where('id', $uploadLocationId)
            ->limit(1)
            ->get('exp_upload_prefs');

        return $path->row('server_path');
    }

    private function sendImage()
    {

    }

    private function downloadImage()
    {

    }
}
