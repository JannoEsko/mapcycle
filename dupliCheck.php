<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
ini_set("memory_limit", "512M");
set_time_limit(0);
require_once __DIR__ . "/autoload_cron.php";

//get all entities into an array we can scan, remove all whitespace

$sql = Constants::getSQL();
$data = $sql->query("SELECT entity_id, map_name, map_entity, uploaded_by_ip, map_description, entity_approved FROM maps_entities WHERE deleted != 1  ORDER BY entity_approved DESC, entity_id ASC");

$allEntities = array();

foreach ($data as $row) {
    $tmp = str_replace("\r", "", $row['map_entity']);
    $tmp = str_replace("\n", "", $tmp);
    $tmp = str_replace(" ", "", $tmp);
    $tmp = strtolower($tmp);
    if (!in_array($tmp, $allEntities)) {
        $allEntities[$row['entity_id'] . $row['map_name']] = $tmp;
    } else {
        $dupliId = array_search($tmp, $allEntities);
        //softdelete it
        //$sql->query("UPDATE maps_entities SET deleted = 1, entity_approved = 0 WHERE entity_id = ?", array($row['entity_id']));
        echo "Entity id " . $row['entity_id'] . $row['map_name'] . "  duplicateof $dupliId\n";
    }
}
