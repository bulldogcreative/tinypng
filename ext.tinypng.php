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

    /**
     * Method that is called by the hook to run TinyPNG on the image
     */
    public function tiny($file_id, $data)
    {
        if(strpos($data['mime_type'], 'image') === false) {
            return false;
        }

        $path = $this->getPath($data['upload_location_id']);
        $this->makeOriginalPath($path);

        $this->createUploadLocation('original', $data);
        copy($path . $data['file_name'], $path . '_original/' . $data['file_name']);

        $uploadResponse = $this->sendImage($path . $data['file_name']);
        $downloadResponse = $this->downloadImage($uploadResponse['response'], $uploadResponse['request'], $path . $data['file_name']);

        if($downloadResponse) {
            $this->updateFileSize($uploadResponse['response'], $data);
        }
    }

    /**
     * Create an upload location for original files if it doesn't exist
     */
    public function createUploadLocation($name, $data)
    {
        $results = ee()->db->select('upload_location_id, title')
            ->from('file_dimensions')
            ->where(array(
                'upload_location_id' => $data['upload_location_id'],
                'title'              => $name,
            ))
            ->get();

        if($results->num_rows() == 0) {
            ee()->db->insert('file_dimensions', array(
                'site_id'            => $data['site_id'],
                'upload_location_id' => $data['upload_location_id'],
                'title'              => $name,
                'short_name'         => $name,
                'resize_type'        => 'none',
            ));
        }
    }

    /**
     * getPath returns the folder path for the upload directory
     */
    private function getPath($uploadLocationId)
    {
        $path = ee()->db->select('server_path')
            ->where('id', $uploadLocationId)
            ->limit(1)
            ->get('exp_upload_prefs');

        return $path->row('server_path');
    }

    /**
     * If the folder for the original files doesn't exist, create it
     */
    private function makeOriginalPath($path)
    {
        if(!file_exists($path . '_original')) {
            mkdir($path . '_original', 0777, true);
        }
    }

    /**
     * Send the image to TinyPNG to get compressed
     */
    private function sendImage($file)
    {
        $request = curl_init();
        curl_setopt_array($request, array(
            CURLOPT_URL            => 'https://api.tinypng.com/shrink',
            CURLOPT_USERPWD        => 'api  :' . $this->settings['api_key'],
            CURLOPT_POSTFIELDS     => file_get_contents($file),
            CURLOPT_BINARYTRANSFER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_SSL_VERIFYPEER => true
        ));

        return ['response' => curl_exec($request), 'request' => $request];
    }

    /**
     * Download the compressed image from TinyPNG
     */
    private function downloadImage($response, $request, $file)
    {
        if (curl_getinfo($request, CURLINFO_HTTP_CODE) === 201) {
            /* Compression was successful, retrieve output from Location header. */
            $headers = substr($response, 0, curl_getinfo($request, CURLINFO_HEADER_SIZE));
            foreach (explode("\r\n", $headers) as $header) {
                if (substr($header, 0, 10) === "Location: ") {
                    $request = curl_init();
                    curl_setopt_array($request, array(
                        CURLOPT_URL            => substr($header, 10),
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_SSL_VERIFYPEER => true
                    ));
                    file_put_contents($file, curl_exec($request));
                }
            }
        } else {
            print(curl_error($request));
            print("Compression failed");

            return false;
        }

        return true;
    }

    /**
     * Update the size of the file in the database after it's compressed
     */
    private function updateFileSize($response, $data)
    {
        $responseArray = explode("\r\n", $response);
        for($x=0;$x<count($responseArray);$x++) {
            // If last line of response - it contains json
            if(substr($responseArray[$x], 2, 5) === "input") {
                $newData = json_decode($responseArray[$x], true);
                $data["file_size"] = $newData["output"]["size"];
                ee()->db->update("files",
                    array("file_size" => $data["file_size"]),
                    array("title" => $data["title"])
                );
            }
        }
    }
}
