<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author Janno
 */
class User {
    
    private $member_id;
    private $name;
    private $member_group_id;
    private $can_approve_ents = false;
    private $can_approve_mapcycle = false;
    private $admin_access = false;
    
    public function __construct($member_id = null, $name = null, $member_group_id = null) {
        $this->member_id = $member_id;
        $this->name = $name;
        $this->member_group_id = $member_group_id;
    }
    
    public function isLoggedIn() {
        return intval($this->getMember_id()) > 0;
    }
    
    public function isPopulated() {
        return $this->isLoggedIn() && strlen(trim($this->getName())) > 0 && intval($this->getMember_group_id()) > 0;
    }
    
    public function getMember_id() {
        return $this->member_id;
    }

    public function getName() {
        return $this->name;
    }

    public function getMember_group_id() {
        return $this->member_group_id;
    }

    public function setMember_id($member_id) {
        $this->member_id = $member_id;
        return $this;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setMember_group_id($member_group_id) {
        $this->member_group_id = $member_group_id;
        return $this;
    }
    
    
    
    public function getAccessLevel() {

        $sql = Constants::getSQL();
        $query = Constants::$SQL_SELECT['GET_ACCESSLEVEL'];
        $params = array($this->getMember_group_id());
        $data = $sql->query($query, $params);
        if (!is_array($data) || sizeof($data) === 0) {
            return Constants::$MAPS_NORMALUSER; //normal user can vote for maps, can upload entities for review.
        }
        $data = $data[0];
        $sql_access_right = intval($data['access_right']);
        if ($sql_access_right === Constants::$MAPS_NOACCESS) {
            //throw new Exception("You are not allowed on this page");
            die("You are not allowed on this page");
        }
        if ($sql_access_right === Constants::$MAPS_EDITOR) {
            $this->setCan_approve_ents(true);
            return Constants::$MAPS_EDITOR;
        }
        
        if ($sql_access_right === Constants::$MAPS_ADMIN) {
            $this->setCan_approve_ents(true)
                    ->setCan_approve_mapcycle(true)
                    ->setAdmin_access(true);
            return Constants::$MAPS_ADMIN;
        }
    }
    
    public function populateAccess() {

        $sql = Constants::getSQL();
        $query = Constants::$SQL_SELECT['GET_ACCESSLEVEL'];
        $params = array($this->getMember_group_id());
        $data = $sql->query($query, $params);
        //throw new Exception(print_r($data));
        if (!is_array($data) || sizeof($data) === 0) {
            return $this; //normal user can vote for maps, can upload entities for review.
        }
        $data = $data[0];
        $sql_access_right = intval($data['access_right']);
        if ($sql_access_right === Constants::$MAPS_NOACCESS) {
            //throw new Exception("You are not allowed on this page");
            die("You are not allowed on this page");
        }
        if ($sql_access_right === Constants::$MAPS_EDITOR) {
            $this->setCan_approve_ents(true);
        }
        
        if ($sql_access_right === Constants::$MAPS_ADMIN) {
            $this->setCan_approve_ents(true)
                    ->setCan_approve_mapcycle(true)
                    ->setAdmin_access(true);
        }
        return $this;
    }
    
    public function toArray() {
        return array(
                "member_id" => $this->getMember_id()
                , "name" => $this->getName()
                , "member_group_id" => $this->getMember_group_id()
                , "can_approve_ents" => bool2int($this->getCan_approve_ents())
                , "can_approve_mapcycle" => bool2int($this->getCan_approve_mapcycle())
                , "admin_access" => bool2int($this->getAdmin_access())
        );
    }
    
    public function populate() {
        //member_id has to be populated.
        if (intval($this->getMember_id()) <= 0) {
            throw new LogicException("populate called on id 0");
        }
        //$userdata = XenForo::getUserDataByID(Constants::getXenSQL(), $this->getMember_id());
        $userdata = XenApi::getUserDataByID($this->getMember_id());
        //throw new Exception(print_r($userdata));
        if ($userdata === null || $userdata === false || sizeof($userdata) === 0) {
            return null;
        }
        
        
        return $this
                ->setName($userdata['username'])
                ->setMember_group_id($userdata['user_group_id'])
                ->populateAccess();
        
    }
    
    public function getFromArray($array) {
        if (isset($array['member_id'])) {
            return $this->setMember_id(intval($array['member_id']))->populate();
        }
        return null;
    }
    
    public function getAdmin_access() {
        return $this->admin_access;
    }

    public function setAdmin_access($admin_access) {
        $this->admin_access = $admin_access;
        return $this;
    }

        
    public function getCan_approve_ents() {
        return $this->can_approve_ents;
    }

    public function getCan_approve_mapcycle() {
        return $this->can_approve_mapcycle;
    }

    public function setCan_approve_ents($can_approve_ents) {
        $this->can_approve_ents = $can_approve_ents;
        return $this;
    }

    public function setCan_approve_mapcycle($can_approve_mapcycle) {
        $this->can_approve_mapcycle = $can_approve_mapcycle;
        return $this;
    }

    
        
    public function externalAuthentication($username, $password) {
        //$data = XenForo::authenticate(Constants::getXenSQL(), $username, $password);
        $data = XenApi::authenticate($username, $password);
        if (!is_array($data) && $data === false) {
            return null;
        }
        return $this->setMember_id(intval($data['member_id']))
                ->setName(trim($data['name']))
                ->setMember_group_id($data['member_group_id']);
    }
    
    public static function checkIP($ip) {
        $xensql = Constants::getXenSQL();
        if (strpos($ip, ",") !== false) {
            
            $iparr = explode(",", $ip);
            $ip = $iparr[0];

        }
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            $name = "mapip";
            $sql = Constants::getSQL();
            $query = "SELECT * FROM q3panel_banned_ips WHERE ip_address = ?";
            $params = array($ip);
            $dat = $sql->query($query, $params);
            if (sizeof($dat) !== 0) {
                throw new Exception("Ip $ip is banned or a VPN IP. If you think this is incorrect, please contact us over the forums.");
            }
            $query = "SELECT * FROM q3panel_allowed_ips WHERE ip_address = ?";
            $dat = $sql->query($query, $params);
            if (sizeof($dat) === 0) {
                $ch = curl_init("http://v2.api.iphub.info/ip/" . trim($ip));
                curl_setopt_array($ch, array(
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER => array("X-Key: " . Constants::$IPHUB_API_KEY)
                ));
                $ret = curl_exec($ch);
                $jsonarr = json_decode($ret, true);
                if (intval($jsonarr['block']) === 1) {
                    $query = "INSERT INTO q3panel_banned_ips (name, ip_address, server_id) VALUES (?, ?, 2)";
                    $params = array($name, $ip);
                    $dat = $sql->query($query, $params);
                    throw new Exception("Ip $ip is banned or a VPN IP. If you think this is incorrect, please contact us over the forums.");
                } else {
                    $query = "INSERT INTO q3panel_allowed_ips (ip_address, blocklevel, name) VALUES (?, ?, ?)";
                    $params = array($ip, $jsonarr['block'], $name);
                    $sql->query($query, $params);
                }
            } 
        } else {
            XenApi::addPost("IP $ip filter failed.", Constants::$IP_FILTER_FAILS_THREAD_ID, 1);
            throw new Exception("Ip $ip filter failed. We will take a look at this issue.");
        }
    }
}
