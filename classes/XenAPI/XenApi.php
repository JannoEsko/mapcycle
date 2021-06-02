<?php

/**
 * Description of XenForo
 *
 * @author Janno
 */
class XenApi {
    static $DEFAULT_USER_ID = 1;
    public static $XENFORO_API_KEY = "";
    public static $XENFORO_API_URL = "https://forums.3d-sof2.com/api";
    
    static $SQL_QUERIES = array(
        "SEND_SIROPU_SHOUT" => "INSERT INTO xf_siropu_shoutbox_shout (shout_user_id, shout_message, shout_date) VALUES (?, ?, UNIX_TIMESTAMP())"
    );
    
    static function sendShoutSiropu(SQL $sql, $shout_user_id, $shout_message) {
        return $sql->query(self::$SQL_QUERIES['SEND_SIROPU_SHOUT'], array($shout_user_id, $shout_message));
    }
    
    static function getUserDataByName($username) {
        $endpoint = "users/find-name?";
        $username = trim($username);
        
        if (strlen($username) === 0) {
            return false;
        }
        $endpoint .= "username=$username";
        $data = json_decode(self::sendRequest($endpoint, 1, null, true, "GET", true, ""), true);
        
        if (array_key_exists("errors", $data)) {
            return false;
        }
        
        if (array_key_exists("exact", $data)) {
            $exact = $data['exact'];
            if ($exact === null || !is_array($exact) || sizeof($exact) === 0) {
                return false;
            }
            
            return array("user_id" => $exact['user_id'], "username" => $exact['username'], "email" => $exact['email'], "user_group_id" => $exact['user_group_id'], "secondary_group_ids" => $exact['secondary_group_ids']);
        }
        return false;
    }
    
    static function getUserDataByID($user_id) {
        $endpoint = "users/$user_id";
        
        
        
        $data = json_decode(self::sendRequest($endpoint, 1, null, true, "GET"), true);
        
        if (array_key_exists("errors", $data)) {
            return false;
        }
        
        if (array_key_exists("user", $data)) {
            $exact = $data['user'];
            if ($exact === null || !is_array($exact) || sizeof($exact) === 0) {
                return false;
            }
            
            return array("user_id" => $exact['user_id'], "username" => $exact['username'], "email" => $exact['email'], "user_group_id" => $exact['user_group_id'], "secondary_group_ids" => $exact['secondary_group_ids']);
        }
        return false;
    }
    
    
    
    static function moveThread($thread_id, $target_node_id, $user_id = null, $prefix_id = 0, $title = null, $notify_watchers = true, $starter_alert = false, $starter_alert_reason = null) {
        if ($user_id === null || intval($user_id) === 0) {
            $user_id = self::$DEFAULT_USER_ID;
        }
        $thread_id = intval($thread_id);
        
        if ($thread_id === 0) {
            return false;
        }
        
        $target_node_id = intval($target_node_id);
        
        if ($target_node_id === 0) {
            return false;
        }
        
        $postFields = array("target_node_id" => $target_node_id);
        
        if (intval($prefix_id) !== 0) {
            $postFields['prefix_id'] = intval($prefix_id);
        }
        
        if ($title !== null && strlen(trim($title)) > 0) {
            $postFields['title'] = trim($title);
        }
        
        if ($notify_watchers) {
            $postFields['notify_watchers'] = $notify_watchers;
        }
        
        if ($starter_alert) {
            $postFields['starter_alert'] = $starter_alert;
            
            if ($starter_alert_reason !== null && strlen(trim($starter_alert_reason)) > 0) {
                $postFields['starter_alert_reason'] = $starter_alert_reason;
            }
        }
        
        $endpoint = "threads/$thread_id/move";
        $data = json_decode(self::sendRequest($endpoint, $user_id, $postFields), true);
        if (array_key_exists("errors", $data)) {
            return false;
        }
        return true;
    }
    
    
    static function editPostDirty($post_id, $message, $user_id = null) {
        return self::editPost($post_id, $message, $user_id, true); //silent edit pretty much the same as dirty edit.
    }
    
    static function editPost($post_id, $message, $user_id = null, $silent = false, $clear_edit = false, $author_alert = false, $author_alert_reason = "", $attachment_key = "") {
        $post_id = intval($post_id);
        
        if ($post_id === 0) {
            return false;
        }
        
        if ($user_id === null || intval($user_id) === 0) {
            $user_id = self::$DEFAULT_USER_ID;
        }
        
        $endpoint = "posts/$post_id";
        $postFields = array("message" => $message);
        
        if ($silent) {
            $postFields['silent'] = true;
            
            if ($clear_edit) {

 

                $postFields['clear_edit'] = true;
            }
        }
        
        if ($author_alert) {
            $postFields['author_alert'] = true;
            
            if (strlen(trim($author_alert_reason)) > 0) {
                $postFields['author_alert_reason'] = $author_alert_reason;
            }
        }
        
        if (strlen(trim($attachment_key)) > 0) {
            $postFields['attachment_key'] = $attachment_key;
        }
        
        $data = json_decode(self::sendRequest($endpoint, $user_id, $postFields), true);
        
        if (array_key_exists("errors", $data)) {
            return false;
        }
        
        return true;
        
    }
    
    static function deletePost($post_id, $user_id = null, $hard_delete = false, $reason = "", $author_alert = false, $author_alert_reason = "") {
        
        $post_id = intval($post_id);
        
        if ($post_id === 0) {
            return false;
        }
        
        if ($user_id === null || intval($user_id) === 0) {
            $user_id = self::$DEFAULT_USER_ID;
        }
        
        $endpoint = "posts/$post_id";
        
        $postFields = array();
        
        if (strlen(trim($reason)) > 0) {
            $postFields['reason'] = trim($reason);
        }
        
        if ($hard_delete) {
            $postFields['hard_delete'] = $hard_delete;
        }
        
        if ($author_alert) {
            $postFields['author_alert'] = $author_alert;
        }
        
        if (strlen(trim($_POST['author_alert_reason'])) > 0) {
            $postFields['author_alert_reason'] = trim($author_alert_reason);
        }
        
        $data = json_decode(self::sendRequest($endpoint, $user_id, $postFields, true, "DELETE"));

        if (array_key_exists("errors", $data)) {
            return $data;
        }

        return true;
    
    }
    
    static function getUsernameByID($user_id) {
        
        $user_id = intval($user_id);
        
        if ($user_id === 0) {
            return null;
        }
        
        $endpoint = "users/$user_id";
        
        $data = json_decode(self::sendRequest($endpoint, null, null, true, "GET"), true);
        
        if (array_key_exists("errors", $data)) {
            return null;
        }
        
        return $data['user']['username'];
    }

 


    
    static function addPost($message, $thread_id, $user_id = null, $attachment_key = "") {
        $endpoint = "posts";
        
        if ($user_id === null || intval($user_id) === 0) {
            $user_id = self::$DEFAULT_USER_ID;
        }
        
        $postFields = array("thread_id" => intval($thread_id), "message" => $message, "attachment_key" => $attachment_key);
        //var_dump($postFields);
        $data = json_decode(self::sendRequest($endpoint, $user_id, $postFields), true);
        
        if (array_key_exists("errors", $data)) {
            return false;
        }
        
        return $data['post']['post_id'];
    }
    
    static function closeThread($thread_id, $user_id = null) {
        return self::updateThread($thread_id, null, null, false, null, null, null, $user_id);
    }
    
    static function updateThread($thread_id, $prefix_id = null, $title = null, $discussion_open = null, $sticky = null, $add_tags = null, $remove_tags = null, $user_id = null) {
        $thread_id = intval($thread_id);
        if ($thread_id === 0) {
            return false;
        }
        
        $endpoint = "threads/" . $thread_id;
        
        if ($user_id === null || intval($user_id) === 0) {
            $user_id = self::$DEFAULT_USER_ID;
        }
        
        $postFields = array();
        
        if ($prefix_id !== null) {
            $postFields['prefix_id'] = $prefix_id;
        }
        
        if ($title !== null) {
            $postFields['title'] = $title;
        }
        
        if ($discussion_open !== null) {
            $postFields['discussion_open'] = $discussion_open;
        }
        
        if ($sticky !== null) {
            $postFields['sticky'] = $sticky;
        }
        
        if ($add_tags !== null) {
            $postFields['add_tags'] = $add_tags;
        }
        
        if ($remove_tags !== null) {
            $postFields['remove_tags'] = $remove_tags;
        }
        
        $data = json_decode(self::sendRequest($endpoint, $user_id, $postFields), true);
        
        if (array_key_exists("errors", $data)) {
            return false;
        }
        
        return true;
        
        
    }
    
    
    public static function addThread($user_id, $node_id, $title, $message, $sticky = false, $discussion_open = true, $attachment_key = "", $bypass = true) {
        $endpoint = "threads";
        $postFields = array("node_id" => $node_id, "title" => $title, "message" => $message, "sticky" => $sticky, "discussion_open" => $discussion_open, "attachment_key" => $attachment_key);
        $data = json_decode(self::sendRequest($endpoint, $user_id, $postFields), true);
        if (array_key_exists("errors", $data)) {
            return false;
        }
        return $data['thread']['thread_id'];
    }
    
    
    
    
    public static function authenticate($username, $password) {
        $endpoint = "auth";
        $postFields = array("login" => $username, "password" => $password);
        $data = self::sendRequest($endpoint, null, $postFields, false);
        $data = json_decode($data, true);
        if (array_key_exists("errors", $data)) {
            return false;
        } else {
            return array("name" => $data['user']['username'], "member_id" => $data['user']['user_id'], "member_group_id" => $data['user']['user_group_id'], "forum_is_admin" => $data['user']['is_admin'], "forum_is_moderator" => $data['user']['is_moderator'], "forum_is_staff" => $data['user']['is_staff'], "forum_is_super_admin" => $data['user']['is_super_admin']);
        }
    }
    
    
    private static function sendRequest($endpoint, $user_id = null, $postFields = null, $bypass_permissions = true, $method = "POST", $return = true, $trailingSlash = "/") {
        $ch = curl_init(self::$XENFORO_API_URL . "/$endpoint" . "$trailingSlash");
        if ($return) {
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        }
        
        if ($bypass_permissions) {
            $postFields['api_bypass_permissions'] = 1; 
        }

        //var_export(self::$XENFORO_API_URL . "/$endpoint" . "$trailingSlash");

        $postFields = http_build_query($postFields);
        
        $httpHeaders = null;
        curl_setopt($ch, CURLOPT_VERBOSE, 1);



        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        switch ($method) {
            case "POST":
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
                break;
            case "GET":
                break; //in url
            case "DELETE":
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
                
                break;
            
            default:
                return null;
        }
        
        
        
        
        
        if ($user_id !== null) {
            $httpHeaders = array("XF-Api-Key: " . self::$XENFORO_API_KEY, "XF-Api-User: " . intval($user_id));
        } else {
            $httpHeaders = array("XF-Api-Key: " . self::$XENFORO_API_KEY);
        }
        $httpHeaders[] = "Content-Type: application/x-www-form-urlencoded";
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeaders);
        

        
        $data = curl_exec($ch);
        if (curl_errno($ch) > 0) {
            throw new Exception("CURL error occured - " . curl_errno($ch) . " - " . curl_error($ch));
        }
        curl_close($ch);
        if ($return) {
            return $data;
        }
        
    }
    
    
    
    
}