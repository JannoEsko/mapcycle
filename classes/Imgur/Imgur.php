<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Imgur
 *
 * @author jants
 */
class Imgur {
    
    private $img;
    private $client_id;
    private $formImgName;
    public function __construct($client_id, $img, $formImgName = "img") {
        $this->client_id = $client_id;
        $this->img = $img;
        $this->formImgName = $formImgName;
    }
    
    public function send() {
        $out[] = "";
        foreach ($this->img[$this->formImgName]['tmp_name'] as $tmpname) {
            if (!empty($tmpname) && is_uploaded_file($tmpname)) {
                /*
                $handle = fopen($tmpname, "r");
                $data = fread($handle, filesize($tmpname));
                 */
                $data = file_get_contents($tmpname);
                $vars = array("image" => base64_encode($data));
                $output = $this->request($vars);
                $tmp = json_decode($output);
                if (sizeof($tmp) === 0) {
                    $output = $this->request($vars);
                }
                $out[] = json_decode($output, true);
            }
            
        }
        array_shift($out);
        return $out;
        
    }
    
    private function request($vars, $timeout = 30) {
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => "https://api.imgur.com/3/image",
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_HTTPHEADER => array("Authorization: Client-Id " . $this->client_id),
            CURLOPT_POST => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POSTFIELDS => $vars,
        ));
        $out = curl_exec($ch);
        //throw new Exception(print_r($out));
        curl_close($ch);
        return $out;
    }
}
