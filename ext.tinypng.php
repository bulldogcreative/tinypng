<?php
/**
 * Sends the image to tinypng.com to make the file size smaller
 *
 * @version 1.0.0
 * @author Levi Durfee <levi.durfee@gmail.com>
 *
 */
class Tinypng_ext {

    var $name = "Tinypng";
    var $version = "1.0.0";
    var $description = "https://tinypng.com make your images smaller";
    var $settings_exist = "y";
    var $docs_url = "https://www.bulldogcreative.com/";

    var $apiUrl = "https://api.tinypng.com/shrink";

    var $settings = array();

    function __construct($settings = "")
    {
        $this->settings = $settings;
    }

    function activate_extension()
    {
        $data = array(
            "class"     => __CLASS__,
            "method"    => "tinyThis",
            "hook"      => "file_after_save",
            "settings"  => "",
            "priority"  => 10,
            "version"   => $this->version,
            "enabled"   => "y",
            );

        ee()->db->insert("extensions", $data);
    }

    function update_extension($current = "")
    {
        if($current == "" OR $current == $this->version)
        {
            return false;
        }

        if($current < "0.1.0")
        {
            // Update to version 0.1.0
        }

        ee()->db->where("class", __CLASS__);
        ee()->db->update("extensions", array("version" => $this->version));
    }

    function disable_extension()
    {
        ee()->db->where("class", __CLASS__);
        ee()->db->delete("extensions");
    }

    function tinyThis($file_id, $data)
    {
        if(!exif_imagetype($data["rel_path"]))
        {
            return false;
        }
        ee()->db->select("server_path");
        ee()->db->from("exp_upload_prefs");
        ee()->db->where("id", $data["upload_location_id"]);
        $path = ee()->db->get();
        if($path->num_rows())
        {
            foreach($path->result_array() as $row)
            {
                $local_path = $row["server_path"];
            }
        }
        $panda_path = $local_path . "_original";
        if(!file_exists($panda_path))
        {
            mkdir($panda_path, 0777, true);
        }

        $results = ee()->db->select("upload_location_id, title")
        ->from("file_dimensions")
        ->where(array(
            "upload_location_id" => $data["upload_location_id"],
            "title"              => "original",
            ))
        ->get();

        if($results->num_rows() == 0)
        {
            ee()->db->insert("file_dimensions",
                array(
                "site_id"            => 1,
                "upload_location_id" => $data["upload_location_id"],
                "title"              => "original",
                "short_name"         => "original",
                "resize_type"        => "none",
                ));
        }

        $input = $data["rel_path"];
        copy($input, $panda_path . "/" . $data["file_name"]);
        $output = $data["rel_path"];

        $request = curl_init();
        curl_setopt_array($request, array(
          CURLOPT_URL            => "https://api.tinypng.com/shrink",
          CURLOPT_USERPWD        => "api:" . $this->settings["apiKey"],
          CURLOPT_POSTFIELDS     => file_get_contents($input),
          CURLOPT_BINARYTRANSFER => true,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_HEADER         => true,
          /* Uncomment below if you have trouble validating our SSL certificate.
             Download cacert.pem from: http://curl.haxx.se/ca/cacert.pem */
          // CURLOPT_CAINFO => __DIR__ . "/cacert.pem",
          CURLOPT_SSL_VERIFYPEER => true
        ));

        $response = curl_exec($request);
        if (curl_getinfo($request, CURLINFO_HTTP_CODE) === 201) {
          /* Compression was successful, retrieve output from Location header. */
          $headers = substr($response, 0, curl_getinfo($request, CURLINFO_HEADER_SIZE));
          foreach (explode("\r\n", $headers) as $header) {
            if (substr($header, 0, 10) === "Location: ") {
              $request = curl_init();
              curl_setopt_array($request, array(
                CURLOPT_URL => substr($header, 10),
                CURLOPT_RETURNTRANSFER => true,
                /* Uncomment below if you have trouble validating our SSL certificate. */
                // CURLOPT_CAINFO => __DIR__ . "/cacert.pem",
                CURLOPT_SSL_VERIFYPEER => true
              ));
              file_put_contents($output, curl_exec($request));
            }
          }
        } else {
            print(curl_error($request));
            /* Something went wrong! */
            print("Compression failed");
        }
    }

    function settings_form($current)
    {
        ee()->load->helper("form");
        ee()->load->library("table");

        $vars = array();

        $apiKey = (isset($current["apiKey"])) ? $current["apiKey"] : "";

        $vars["settings"] = array(
            "apiKey" => form_input("apiKey", $apiKey),
            );
        return ee()->load->view("index", $vars, true);
    }

    function save_settings()
    {
        if(empty($_POST))
        {
            show_error(lang("unauthorized_access"));
        }

        unset($_POST["submit"]);

        ee()->lang->loadfile("tinypng");

        ee()->db->where("class", __CLASS__);
        ee()->db->update("extensions", array("settings" => serialize($_POST)));

        ee()->session->set_flashdata(
            "message_success",
            lang("preferences_updated")
        );
    }
}
