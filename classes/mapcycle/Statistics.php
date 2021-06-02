<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Statistics
 * @author Janno
 */
class Statistics {
    
    private $stat_id;
    private $stat_dt;
    private $entity_id;
    private $map_id;
    private $mapcycle_id;
    private $gametype;
    private $clients;
    
    public function __construct($stat_id = null, $stat_dt = null, $entity_id = null, $map_id = null, $mapcycle_id = null, $gametype = null, $clients = null) {
        $this->stat_id = $stat_id;
        $this->stat_dt = $stat_dt;
        $this->entity_id = $entity_id;
        $this->map_id = $map_id;
        $this->mapcycle_id = $mapcycle_id;
        $this->gametype = $gametype;
        $this->clients = $clients;
    }
    
    public function write() {
        //no updates.
        if (intval($this->getStat_id()) === 0) {
            $sql = Constants::getSQL();
            $query = Constants::$SQL_INSERT['INSERT_PARSED_STAT'];
            $params = array($this->getStat_dt(), $this->getEntity_id(), $this->getMap_id(), $this->getMapcycle_id(), $this->getGametype(), $this->getClients());
            $data = $sql->query($query, $params);
            
            if (array_key_exists("last_insert_id", $data)) {
                $this->setStat_id(intval($data['last_insert_id']));
            }
            
        }
        return $this;
    }
    
    public function getStat_id() {
        return $this->stat_id;
    }

    public function getStat_dt() {
        return $this->stat_dt;
    }

    public function getEntity_id() {
        return $this->entity_id;
    }
    
    /**
     * 
     * @return Entity
     */
    public function getEntity() {
        $entity = new Entity($this->getEntity_id());
        return $entity->populate();
    }

    public function getMap_id() {
        return $this->map_id;
    }

    public function getMapcycle_id() {
        return $this->mapcycle_id;
    }
    
    /**
     * 
     * @return Mapcycle
     */
    public function getMapcycle() {
        $mapcycle = new Mapcycle($this->getMapcycle_id());
        return $mapcycle->populate();
    }
    
    
    public function getGametype() {
        return $this->gametype;
    }

    public function getClients() {
        return $this->clients;
    }

    public function setStat_id($stat_id) {
        $this->stat_id = $stat_id;
        return $this;
    }

    public function setStat_dt($stat_dt) {
        $this->stat_dt = $stat_dt;
        return $this;
    }

    public function setEntity_id($entity_id) {
        $this->entity_id = $entity_id;
        return $this;
    }

    public function setMap_id($map_id) {
        $this->map_id = $map_id;
        return $this;
    }

    public function setMapcycle_id($mapcycle_id) {
        $this->mapcycle_id = $mapcycle_id;
        return $this;
    }

    public function setGametype($gametype) {
        $this->gametype = $gametype;
        return $this;
    }

    public function setClients($clients) {
        $this->clients = $clients;
        return $this;
    }

    
    public static function getStatistics($stat_id = null, $stat_dt_fm = null, $stat_dt_to = null, $timeFrom = null, $timeTo = null, $entity_id = null, $map_id = null, $mapcycle_id = null, $gametype = null, $clients = null, $sortBy = 0) {
        $whereSet = false;
        $query = "SELECT * FROM maps_statistics ";
        $params = array();
        
        if ($stat_id !== null) {
            $whereSet = true;
            $query .= " WHERE stat_id = ?";
            $params[] = $stat_id;
        }
        
        if ($stat_dt_fm !== null && $stat_dt_to !== null) {
            if ($whereSet) {
                $query .= " AND ";
            } else {
                $query .= " WHERE ";
            }
            $query .= " stat_dt >= ? AND stat_dt <= ? ";
            $params[] = $stat_dt_fm;
            $params[] = $stat_dt_to;
        }
        
        if ($timeFrom !== null && $timeTo !== null) {
            if ($whereSet) {
                $query .= " AND ";
            } else {
                $query .= " WHERE ";
            }
            $query .= " TIME(stat_dt) >= ? AND TIME(stat_dt) <= ? ";
            $params[] = $timeFrom;
            $params[] = $timeTo;
        }
        
        if ($entity_id !== null) {
            if ($whereSet) {
                $query .= " AND ";
            } else {
                $query .= " WHERE ";
            }
            $query .= " entity_id = ? ";
            $params[] = $entity_id;
        }
        
        if ($map_id !== null) {
            if ($whereSet) {
                $query .= " AND ";
            } else {
                $query .= " WHERE ";
            }
            $query .= " map_id = ? ";
            $params[] = $map_id;
        }
        
        if ($mapcycle_id !== null) {
            if ($whereSet) {
                $query .= " AND ";
            } else {
                $query .= " WHERE ";
            }
            $query .= " mapcycle_id = ? ";
            $params[] = $mapcycle_id;
        }
        
        if ($gametype !== null) {
            if ($whereSet) {
                $query .= " AND ";
            } else {
                $query .= " WHERE ";
            }
            $query .= " gametype = ? ";
            $params[] = $gametype;
        }
        
        if ($clients !== null) {
            if ($whereSet) {
                $query .= " AND ";
            } else {
                $query .= " WHERE ";
            }
            $query .= " clients >= ? ";
            $params[] = $clients;
        }
        
        switch ($sortBy) {
            default:
                $query .= " ORDER BY stat_dt ASC ";
                break;
        }
        $sql = Constants::getSQL();
        $data = $sql->query($query, $params);
        $returnable = array();
        
        if ($data !== null && is_array($data) && sizeof($data) > 0) {
            foreach ($data as $row) {
                $returnable[] = self::generateFromSQLRow($row);
            }
        }
        
        return $returnable;
    }
    
    public static function generateFromSQLRow($row) {
        return new static($row['stat_id'], $row['stat_dt'], $row['entity_id'], $row['map_id'], $row['mapcycle_id'], $row['gametype'], $row['clients']);
    }




    public static function parseServerpanelStats(SQL $q3panel) {
        //inherit SQL because it might not be in the same db in the future.
        $cleanOld = "DELETE FROM q3panel_hnsstat WHERE parsed = 1";
        $getData = "SELECT * FROM q3panel_hnsstat WHERE parsed = 0";
        $updateParsed = "UPDATE q3panel_hnsstat SET parsed = 1";
        $q3panel->beginTransaction();
        //$q3panel->query($cleanOld);
        $data = $q3panel->query($getData);
        $q3panel->query($updateParsed);
        $q3panel->commit();
        
        if ($data !== null && is_array($data) && sizeof($data) > 0) {
            foreach ($data as $row) {
                $clients = $row['clients'];
                $entitymap_id = intval($row['entitymap_id']);
                $map_id = $row['map_id'];
                $push_at = $row['push_at'];
                if ($entitymap_id === 0) {
                    continue;
                }
                $entityMap = new EntityMap($entitymap_id);
                $entityMap = $entityMap->populate();
                if ($entityMap === null || !($entityMap instanceof EntityMap)) {
                    continue; //just bypass row, no exceptions.
                }
                $obj = new static(null, $push_at, $entityMap->getEntity_id(), $map_id, $entityMap->getMapcycle_id(), $entityMap->getGametype(), $clients);
                $obj->write();
            }
        }
    }
}
