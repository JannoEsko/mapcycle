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
$data = $sql->query("SELECT map_name, map_entity FROM maps_entities");

$allEntities = array();

foreach ($data as $row) {
    $tmp = str_replace("\r", "", $row['map_entity']);
    $tmp = str_replace("\n", "", $tmp);
    $tmp = str_replace(" ", "", $tmp);
    if (!array_key_exists(strtolower($row['map_name']), $allEntities) || !in_array($tmp, $allEntities[strtolower($row['map_name'])])) {
        $allEntities[strtolower($row['map_name'])][] = $tmp;
    }
}

$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . "/blk"));
$up_by = 1;
$up_by_ip = "127.0.0.1";
$ins_qry = "INSERT INTO maps_entities (map_name, map_description, map_entity, uploaded_by, uploaded_by_ip) VALUES (?, ?, ?, ?, ?)";
$i = 0;
$sql = Constants::getSQL();
foreach ($rii as $inp) {
    $tmp = "";
    if ($inp->isDir()) {
        continue;
    }
    if (strtolower($inp->getExtension()) === "ent") {
        
        $mapName = strtolower($inp->getBasename(".ent"));
        $mapDesc = $inp->getPathname() . " (from old 3D# Backups and Stefan's provided stuff)";
        
        $entity = file_get_contents($inp->getPathname());
        $tmp = str_replace("\r", "", $entity);
        $tmp = str_replace("\n", "", $tmp);
        $tmp = str_replace(" ", "", $tmp);
        if (!array_key_exists($mapName, $allEntities) || !in_array($tmp, $allEntities[$mapName])) {
            $allEntities[$mapName][] = $tmp;
            $sql->query($ins_qry, array($mapName, $mapDesc, $entity, $up_by, $up_by_ip));
        }
        $i++;
    }
    
}

echo "$i maps processed.";
