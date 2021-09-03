<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Mapcycle
 *
 * @author jants
 */
class Mapcycle {
    
    private $mapcycle_id;
    private $mapcycle_description;
    private $mapcycle_creator_user_id;
    private $mapcycle_creator_ip;
    private $mapcycle_status;
    private $mapcycle_status_change_by;
    private $mapcycle_status_change_by_ip;
    private $mapcycle_created_at;
    
    public static $MAPCYCLE_NOTAPPROVED = 0;
    public static $MAPCYCLE_APPROVED = 1;
    public static $MAPCYCLE_DELETED = 2;
    
    public function getMapcycle_id() {
        return $this->mapcycle_id;
    }

    public function getMapcycle_description() {
        return $this->mapcycle_description;
    }

    public function getMapcycle_creator_user_id() {
        return $this->mapcycle_creator_user_id;
    }

    public function getMapcycle_creator_ip() {
        return $this->mapcycle_creator_ip;
    }

    public function getMapcycle_status() {
        return $this->mapcycle_status;
    }
    
    public function getStatusString() {
        if ($this->isDeleted()) {
            return "Deleted";
        } else if ($this->isNotApproved()) {
            return "Not approved";
        } else if ($this->isApproved()) {
            return "Approved";
        }
        return "Status not mapped.";
    }
    
    public function isDeleted() {
        return intval($this->getMapcycle_status()) === self::$MAPCYCLE_DELETED;
    }
    
    public function isNotApproved() {
        return intval($this->getMapcycle_status()) === self::$MAPCYCLE_NOTAPPROVED;
    }
    
    public function isApproved() {
        return intval($this->getMapcycle_status()) === self::$MAPCYCLE_APPROVED;
    }

    public function getMapcycle_status_change_by() {
        return $this->mapcycle_status_change_by;
    }

    public function getMapcycle_status_change_by_ip() {
        return $this->mapcycle_status_change_by_ip;
    }

    public function getMapcycle_created_at() {
        return $this->mapcycle_created_at;
    }

    public function setMapcycle_id($mapcycle_id) {
        $this->mapcycle_id = $mapcycle_id;
        return $this;
    }

    public function setMapcycle_description($mapcycle_description) {
        $this->mapcycle_description = $mapcycle_description;
        return $this;
    }

    public function setMapcycle_creator_user_id($mapcycle_creator_user_id) {
        $this->mapcycle_creator_user_id = $mapcycle_creator_user_id;
        return $this;
    }

    public function setMapcycle_creator_ip($mapcycle_creator_ip) {
        $this->mapcycle_creator_ip = $mapcycle_creator_ip;
        return $this;
    }

    public function setMapcycle_status($mapcycle_status) {
        $this->mapcycle_status = $mapcycle_status;
        return $this;
    }

    public function setMapcycle_status_change_by($mapcycle_status_change_by) {
        $this->mapcycle_status_change_by = $mapcycle_status_change_by;
        return $this;
    }

    public function setMapcycle_status_change_by_ip($mapcycle_status_change_by_ip) {
        $this->mapcycle_status_change_by_ip = $mapcycle_status_change_by_ip;
        return $this;
    }

    public function setMapcycle_created_at($mapcycle_created_at) {
        $this->mapcycle_created_at = $mapcycle_created_at;
        return $this;
    }

    public function __construct($mapcycle_id = null, $mapcycle_description = null, $mapcycle_creator_user_id = null, $mapcycle_creator_ip = null, $mapcycle_status = null, $mapcycle_status_change_by = null, $mapcycle_status_change_by_ip = null, $mapcycle_created_at = null) {
        $this->mapcycle_id = $mapcycle_id;
        $this->mapcycle_description = $mapcycle_description;
        $this->mapcycle_creator_user_id = $mapcycle_creator_user_id;
        $this->mapcycle_creator_ip = $mapcycle_creator_ip;
        $this->mapcycle_status = $mapcycle_status;
        $this->mapcycle_status_change_by = $mapcycle_status_change_by;
        $this->mapcycle_status_change_by_ip = $mapcycle_status_change_by_ip;
        $this->mapcycle_created_at = $mapcycle_created_at;
    }
    
    public function populate() {
        //only uniqueness is mc id itself.
        $sql = Constants::getSQL();
        $query = Constants::$SQL_SELECT['GET_MAPCYCLE_BY_ID'];
        
        if ($this->getMapcycle_id() === null || intval($this->getMapcycle_id()) === 0) {
            throw new BadFunctionCallException("call populate() without mc_id");
        } else {
            $params = array($this->getMapcycle_id());
            $data = $sql->query($query, $params);
            
            if ($data !== null && is_array($data) && sizeof($data) === 1) {
                $data = $data[0];
                return 
                $this->setMapcycle_id($data['mapcycle_id'])
                        ->setMapcycle_created_at($data['mapcycle_created_at'])
                        ->setMapcycle_creator_ip($data['mapcycle_creator_ip'])
                        ->setMapcycle_creator_user_id($data['mapcycle_creator_user_id'])
                        ->setMapcycle_description($data['mapcycle_description'])
                        ->setMapcycle_status($data['mapcycle_status'])
                        ->setMapcycle_status_change_by($data['mapcycle_status_change_by'])
                        ->setMapcycle_status_change_by_ip($data['mapcycle_status_change_by_ip'])
                        ;
            } 
            
            return null;
            
        }
    }
    
    public function write() {
        $sql = Constants::getSQL();
        if ($this->getMapcycle_id() === null || intval($this->getMapcycle_id()) === 0) {
            //write
            $query = Constants::$SQL_INSERT['INSERT_MAPCYCLE'];
            $params = array(
                $this->getMapcycle_description()
                    , $this->getMapcycle_creator_user_id()
                    , $this->getMapcycle_creator_ip()
                    , $this->getMapcycle_status()
                    , $this->getMapcycle_status_change_by()
                    , $this->getMapcycle_status_change_by_ip()
            );
            
            $data = $sql->query($query, $params);
            
            if ($data !== null && is_array($data) && array_key_exists("last_insert_id", $data)) {
                return $this->setMapcycle_id($data['last_insert_id']);
            }
            
        } else {
            //update
            $query = Constants::$SQL_UPDATE['UPDATE_MAPCYCLE'];
            $params = array(
                $this->getMapcycle_description()
                    , $this->getMapcycle_status()
                    , $this->getMapcycle_status_change_by()
                    , $this->getMapcycle_status_change_by_ip()
                    , $this->getMapcycle_id()
            );
            $sql->query($query, $params);
            return $this;
        } 
        
        return null;
    }
    
    public function delete($softDelete = true) {
        if ($softDelete) {
            return $this->setMapcycle_status(Mapcycle::$MAPCYCLE_DELETED)->write();
        } else {
            throw new Exception("harddeleting mapcycle is not supported");
        }
    }
    
    
    /**
     * Compiles the actual mapcycle, creates the file tree and prepares for upload.
     */
    public function compile($override = false) {
        //prerequisites
        //1 - we need the MC to be registered.
        if ($this->getMapcycle_id() === null || intval($this->getMapcycle_id()) === 0) {
            throw new BadFunctionCallException("call compile() on non-saved MC");
        }
        
        //2 - it has to be in status approved.
        if (!$this->isApproved()) {
            throw new BadFunctionCallException("can't call compile() on non-approved MC");
        }
        
        //3 - has to have the appropriate rights, but this is not checked by this function, is checked by the caller.
        //4 - check whether our maps are still approved.
        
        $mappedEntities = EntityMap::getOrderedEntities($this);
        
        if ($mappedEntities !== null && is_array($mappedEntities) && sizeof($mappedEntities) > 0) {
            foreach ($mappedEntities as $ment) {
                $entityFile = $ment->getEntity();
                
                if (!$entityFile->isApproved() && !$override) {
                    throw new BadFunctionCallException("entity " . $entityFile->getEntity_id() . " is not approved.");
                }
                
                if ($entityFile->isDeleted() && !$override) {
                    throw new BadFunctionCallException("entity " . $entityFile->getEntity_id() . " is deleted");
                }
                
            }
        }
        
        //we should be good.
        
        return $this->generateMapcycleText($mappedEntities);
        
    }
    
    
    
    private function generateMapcycleText($entityMaps) {
        $returnable = "mapcycle\r\n{";
        
        if ($entityMaps !== null && is_array($entityMaps) && sizeof($entityMaps) > 0) {
            
            for ($i = 0; $i < sizeof($entityMaps); $i++) {
                $entityMap = $entityMaps[$i];
                $returnable .= $this->generateMapRowText($i, $entityMap);
            }
            
        }
        
        $returnable .= "\r\n}";
        return $returnable;
    }
    
    private function generateMapRowText($map_id, EntityMap $entityMap) {
        
        return "\r\n\tmap" . intval($map_id) . "\r\n\t{" . $this->generateMapEntityText($entityMap, $map_id)
                . "\t}";
    }
    
    private function generateMapEntityText(EntityMap $entityMap, $map_id = 0) {
        $cvars = CvarMap::getCvarMapByEntityMap($entityMap);
        //as i dont have the whole picture im dealing with what i got.
        $mapAltmap = "map";
        
        if ($entityMap->isAltmap()) {
            $mapAltmap = "altmap";
        }
        
        $entity = $entityMap->getEntity();
        $gtText = "g_gametype \"" . $entityMap->getGametype() . "\"";
        if ($entityMap->getGametype() === "inf") {
            $gtText = "g_gametype \"dm\"\r\n\t\t\t" . $gtText;
        }
        $returnable = "\r\n\t\tcommand \"$mapAltmap " . $entity->getMap_name() . "\"\r\n\t\tcvars\r\n\t\t{\r\n\t\t\t$gtText\r\n\t\t\t3d_entitymap_id \"" . $entityMap->getEntitymap_id() . "\"\r\n\t\t\t3d_map_id \"" . $map_id . "\"\r\n";
        
        if ($cvars !== null && is_array($cvars) && sizeof($cvars) > 0) {
            foreach ($cvars as $cv) {
                $cvar = $cv->getCvar();
                $returnable .= "\t\t\t" . $cvar->getCvar_name() . " \"" . $cvar->getCvar_value() . "\"\r\n";
            }
            
        }
        
        $returnable .= "\t\t}\r\n";
        
        return $returnable;
        
    }
    
    public static function generateFromSQLRow($row) {
        $obj = new static();
        return $obj->setMapcycle_created_at($row['mapcycle_created_at'])->setMapcycle_creator_ip($row['mapcycle_creator_ip'])->setMapcycle_creator_user_id($row['mapcycle_creator_user_id'])
                ->setMapcycle_description($row['mapcycle_description'])->setMapcycle_id($row['mapcycle_id'])->setMapcycle_status($row['mapcycle_status'])
                ->setMapcycle_status_change_by($row['mapcycle_status_change_by'])->setMapcycle_status_change_by_ip($row['mapcycle_status_change_by_ip']);
    }
    
    /**
     * 
     * @param type $status
     * @return Mapcycle[]
     */
    private static function getMapcyclesByStatus($status) {
        $sql = Constants::getSQL();
        $query = Constants::$SQL_SELECT['GET_MAPCYCLES_BY_STATUS'];
        $params = array($status);
        $data = $sql->query($query, $params);
        
        $returnable = array();
        
        if ($data !== null && is_array($data) && sizeof($data) > 0) {
            foreach ($data as $row) {
                $returnable[] = self::generateFromSQLRow($row);
            }
        }
        
        return $returnable;        
    }
    
    public static function getApprovedMapcycles() {
        return self::getMapcyclesByStatus(Mapcycle::$MAPCYCLE_APPROVED);
    }
    
    public static function getNotApprovedMapcycles() {
        return self::getMapcyclesByStatus(Mapcycle::$MAPCYCLE_NOTAPPROVED);
    }
    
    public static function getDeletedMapcycles() {
        return self::getMapcyclesByStatus(Mapcycle::$MAPCYCLE_DELETED);
    }
    
    
    
}

class EntityMap {
    
    private $entitymap_id;
    private $mapcycle_id;
    private $entity_id;
    private $added_by;
    private $added_by_ip;
    private $map_order;
    private $gametype;
    private $altmap;
    
    public function getEntitymap_id() {
        return $this->entitymap_id;
    }

    public function getMapcycle_id() {
        return $this->mapcycle_id;
    }
    
    public function getMapcycle() {
        $mc = new Mapcycle($this->getMapcycle_id());
        return $mc->populate();
    }

    public function getEntity_id() {
        return $this->entity_id;
    }
    
    /**
     * 
     * @return Entity 
     */
    public function getEntity() {
        $ent = new Entity($this->getEntity_id());
        return $ent->populate();
    }

    public function getAdded_by() {
        return $this->added_by;
    }

    public function getAdded_by_ip() {
        return $this->added_by_ip;
    }

    public function getMap_order() {
        return $this->map_order;
    }

    public function setEntitymap_id($entitymap_id) {
        $this->entitymap_id = $entitymap_id;
        return $this;
    }

    public function setMapcycle_id($mapcycle_id) {
        $this->mapcycle_id = $mapcycle_id;
        return $this;
    }

    public function setEntity_id($entity_id) {
        $this->entity_id = $entity_id;
        return $this;
    }

    public function setAdded_by($added_by) {
        $this->added_by = $added_by;
        return $this;
    }

    public function setAdded_by_ip($added_by_ip) {
        $this->added_by_ip = $added_by_ip;
        return $this;
    }

    public function setMap_order($map_order) {
        $this->map_order = $map_order;
        return $this;
    }
    
    public function getGametype() {
        return $this->gametype;
    }

    public function getAltmap() {
        return $this->altmap;
    }
    
    public function isAltmap() {
        return int2bool(intval($this->getAltmap()));
    }

    public function setGametype($gametype) {
        $this->gametype = $gametype;
        return $this;
    }

    public function setAltmap($altmap) {
        $this->altmap = $altmap;
        return $this;
    }

    
    public function __construct($entitymap_id = null, $mapcycle_id = null, $entity_id = null, $added_by = null, $added_by_ip = null, $map_order = null, $gametype = null, $altmap = null) {
        $this->entitymap_id = $entitymap_id;
        $this->mapcycle_id = $mapcycle_id;
        $this->entity_id = $entity_id;
        $this->added_by = $added_by;
        $this->added_by_ip = $added_by_ip;
        $this->map_order = $map_order;
        $this->gametype = $gametype;
        $this->altmap = $altmap;
    }
    
    public function populate() {
        $sql = Constants::getSQL();
        $query = "";
        $params = array();
        //2 choices - either by entitymap_id or by mapcycle_id + entity_id
        
        if ($this->getEntitymap_id() !== null && intval($this->getEntitymap_id()) > 0) {
            $query = Constants::$SQL_SELECT['GET_ENTITYMAP_BY_ID'];
            $params = arraY($this->getEntitymap_id());
        } else if ($this->getMapcycle_id() !== null && $this->getEntity_id() !== null && intval($this->getMapcycle_id()) > 0 && intval($this->getEntity_id()) > 0) {
            $query = Constants::$SQL_SELECT['GET_ENTITYMAP_BY_ENTITY_ID_MAPCYCLE_ID'];
            $params = array($this->getEntity_id(), $this->getMapcycle_id());
        } else {
            throw new BadFunctionCallException("can't call populate() without entmap id or mc+ent id");
        }
        
        $data = $sql->query($query, $params);
        
        if ($data !== null && is_array($data) && sizeof($data) === 1) {
            $data = $data[0];
            return 
                $this->setAdded_by($data['added_by'])
                    ->setAdded_by_ip($data['added_by_ip'])
                    ->setAltmap($data['altmap'])
                    ->setEntity_id($data['entity_id'])
                    ->setEntitymap_id($data['entitymap_id'])
                    ->setGametype($data['gametype'])
                    ->setMap_order($data['map_order'])
                    ->setMapcycle_id($data['mapcycle_id']);
        }
        
        return null;
        
    }
    
    public function write() {
        $sql = Constants::getSQL();
        if ($this->getEntitymap_id() !== null && intval($this->getEntitymap_id()) > 0) {
            //update
            $query = Constants::$SQL_UPDATE['UPDATE_ENTITYMAP'];
            $params = array(
                $this->getMapcycle_id()
                    , $this->getEntity_id()
                    , $this->getMap_order()
                    , $this->getGametype()
                    , $this->getAltmap()
                    , $this->getEntitymap_id()
            );
            
            $sql->query($query, $params);
            return $this;
        } else {
            //write
            $query = Constants::$SQL_INSERT['INSERT_ENTITYMAP'];
            //mapcycle_id, entity_id, added_by, added_by_ip, map_order, gametype, altmap
            $params = array(
                $this->getMapcycle_id()
                    , $this->getEntity_id()
                    , $this->getAdded_by()
                    , $this->getAdded_by_ip()
                    , $this->getMap_order()
                    , $this->getGametype()
                    , $this->getAltmap()
            );
            
            $data = $sql->query($query, $params);
            
            if ($data !== null && is_array($data) && array_key_exists("last_insert_id", $data)) {
                return $this->setEntitymap_id($data['last_insert_id']);
            }
            
        }
        
        return null;
    }
    
    public function delete() {
        $sql = Constants::getSQL();
        $query = Constants::$SQL_DELETE['DELETE_ENTITYMAP'];
        if (intval($this->getEntitymap_id()) > 0) {
            $params = array($this->getEntitymap_id());
            $sql->query($query, $params);
            return null;
        } else {
            throw new BadFunctionCallException("Call delete() on uninitialized entitymap");
        }
    }
    
    public static function deleteEntityMapsByEntity(Entity $entity) {
        $sql = Constants::getSQL();
        $query = Constants::$SQL_DELETE['DELETE_ENTITYMAP_BY_ENTITY'];
        $params = array($entity->getEntity_id());
        return $sql->query($query, $params);
    }
    
    public static function checkEntityCollision(EntityMap $entityMap) {
        $entity = $entityMap->getEntity();
        $sql = Constants::getSQL();
        $query = Constants::$SQL_SELECT['CHECK_ENTITYMAP_COLLISION'];
        $params = array($entity->getEntity_id(), $entityMap->getMapcycle_id(), $entity->getMap_name(), $entityMap->getAltmap(), $entityMap->getGametype());
        
        $data = $sql->query($query, $params);
        $returnable = array();
        if ($data !== null && is_array($data) && sizeof($data) > 0) {
            foreach ($data as $row) {
                $returnable[] = self::generateObjectFromSQLRow($row);
            }
        }
        
        return $returnable;
        
    }
    
    public static function generateObjectFromSQLRow($row) {
        $obj = new static();
        $obj 
                ->setAdded_by($row['added_by'])
                ->setAdded_by_ip($row['added_by_ip'])
                ->setAltmap($row['altmap'])
                ->setEntity_id($row['entity_id'])
                ->setEntitymap_id($row['entitymap_id'])
                ->setGametype($row['gametype'])
                ->setMap_order($row['map_order'])
                ->setMapcycle_id($row['mapcycle_id']);
        return $obj;
    }
    
    public static function isEntityMapped(Entity $entity) {
        return sizeof(self::getEntityMapsByEntity($entity)) > 0;
    }
    
    public static function getEntityMapsByEntity(Entity $entity) {
        $entity_id = $entity->getEntity_id();
        $sql = Constants::getSQL();
        $query = Constants::$SQL_SELECT['GET_ENTITYMAPS_BY_ENTITY_ID'];
        $params = array($entity_id);
        $data = $sql->query($query, $params);
        
        $returnable = array();
        
        if ($data !== null && is_array($data) && sizeof($data) > 0) {
            foreach ($data as $row) {
                $returnable[] = self::generateObjectFromSQLRow($row);
            }
        }
        
        return $returnable;
    }
    
    public static function getOrderedEntities(Mapcycle $mapcycle) {
        $sql = Constants::getSQL();
        $query = Constants::$SQL_SELECT['GET_ENTITYMAPS_BY_MAPCYCLE_ID_ORDER'];
        $params = array($mapcycle->getMapcycle_id());
        
        $returnable = array();
        
        $data = $sql->query($query, $params);
        
        if ($data !== null && is_array($data) && sizeof($data) > 0) {
            foreach ($data as $row) {
                $returnable[] = self::generateObjectFromSQLRow($row);
            }
        }
        
        return $returnable;
        
    } 
    
    public static function updateEntityOrder($newOrder, $orderKey = "order", $entitymap_id_key = "entitymap_id") {
        //dry SQL update because of... reasons
        //i can create objects of this but why do xx updates when i can do a x run instead.
        $sql = Constants::getSQL();
        $query = Constants::$SQL_UPDATE['UPDATE_ORDER_BY_ENTITYMAP_ID'];
        if (is_array($newOrder) && sizeof($newOrder) > 0) {
            foreach ($newOrder as $row) {
                $order = $row[$orderKey];
                $entitymap_id = $row[$entitymap_id_key];
                
                //check first whether the first map is altmap.
                
                if (intval($order) === 0) {
                    $entitymap = new EntityMap(intval($entitymap_id));
                    $entitymap = $entitymap->populate();
                    if ($entitymap === null || !($entitymap instanceof EntityMap)) {
                        return array("error" => "Entitymap not found...");
                    } else {
                        if ($entitymap->isAltmap()) {
                            return array("error" => "First map cannot be an altmap!");
                        }
                    }
                }
                $sql->query($query, array($order, $entitymap_id));
            }
        }
        return array("msg" => "Order saved successfully.");
    }

    
}

class Cvar {
    
    private $cvar_id;
    private $cvar_name;
    private $cvar_value;
    private $cvar_friendly_name;
    private $isdefault;
    
    public function getCvar_id() {
        return $this->cvar_id;
    }
    
    public function getCvar_friendly_name() {
        return $this->cvar_friendly_name;
    }

    public function setCvar_friendly_name($cvar_friendly_name) {
        $this->cvar_friendly_name = $cvar_friendly_name;
        return $this;
    }

    
    public function getCvar_name() {
        return $this->cvar_name;
    }

    public function getCvar_value() {
        return $this->cvar_value;
    }

    public function setCvar_id($cvar_id) {
        $this->cvar_id = $cvar_id;
        return $this;
    }

    public function setCvar_name($cvar_name) {
        $this->cvar_name = $cvar_name;
        return $this;
    }

    public function setCvar_value($cvar_value) {
        $this->cvar_value = $cvar_value;
        return $this;
    }

    public function __construct($cvar_id = null, $cvar_name = null, $cvar_value = null, $cvar_friendly_name = null, $isdefault = null) {
        $this->cvar_id = $cvar_id;
        $this->cvar_name = $cvar_name;
        $this->cvar_value = $cvar_value;
        $this->cvar_friendly_name = $cvar_friendly_name;
        $this->isdefault = $isdefault;
    }
    
    public function getIsdefault() {
        return $this->isdefault;
    }

    public function setIsdefault($isdefault) {
        $this->isdefault = $isdefault;
        return $this;
    }

    public function isDefault() {
        return int2bool($this->getIsdefault());
    }
        
    public function equals($o) {
        if ($o instanceof Cvar) {
            return $o->getCvar_name() === $this->getCvar_name() && $o->getCvar_value() === $this->getCvar_value();
        } 
        return false;
    }
    
    public static function findCvarByCvarName($cvarName) {
        $sql = Constants::getSQL();
        $query = Constants::$SQL_SELECT['GET_SINGLE_CVAR_BY_CVAR_NAME'];
        $params = array($cvarName);
        $data = $sql->query($query, $params);
        if ($data !== null && is_array($data) && sizeof($data) === 1) {
            $data = $data[0];
            return new static($data['cvar_id'], $data['cvar_name'], $data['cvar_value'], $data['cvar_friendly_name'], $data['isdefault']);
        }
        return null;
    }
    
    public static function findOrCreateCvar($cvarName, $cvarValue) {
        $cvar = new static();
        $cvar = $cvar->setCvar_name($cvarName)->setCvar_value($cvarValue)->populate();
        
        if ($cvar === null || !($cvar instanceof Cvar) || intval($cvar->getCvar_id()) === 0) {
            //find by name.
            
            $cvar = self::findCvarByCvarName($cvarName);
            
            if ($cvar === null || !($cvar instanceof Cvar)) {
                //i dont have the same cvar, therefore I'm quitting because I will not write unallowed cvars.
                throw new BadFunctionCallException("Can't create a cvar which is not already existing");
            }
            
            $cvar = $cvar->setCvar_name($cvarName)->setCvar_value($cvarValue)->write();
            
        }
        
        return $cvar;
    }
    
    public static function getDefaultCvars() {
        $sql = Constants::getSQL();
        $query = Constants::$SQL_SELECT['GET_DEFAULT_CVARS'];
        $dat = $sql->query($query);
        $returnable = array();
        
        if ($dat !== null && is_array($dat) && sizeof($dat) > 0) {
            foreach ($dat as $data) {
                $returnable[] = new static($data['cvar_id'], $data['cvar_name'], $data['cvar_value'], $data['cvar_friendly_name'], $data['isdefault']);
            }
        }
        
        return $returnable;
        
    }
    
    public static function getCvarsWithExclusion($exclusion) {
        $cvars = self::getDefaultCvars();
        $returnable = array();
        if ($cvars !== null && is_array($cvars) && sizeof($cvars) > 0) {
            foreach ($cvars as $cvar) {
                if (in_array($cvar->getCvar_name(), $exclusion)) {
                    continue;
                }
                $returnable[] = $cvar;
            }
        }
        return $returnable;
    }
    
    public static function getCvarsWithInclusion($inclusion, $byKey = false) {
        $cvars = self::getDefaultCvars();
        $returnable = array();
        if ($cvars !== null && is_array($cvars) && sizeof($cvars) > 0) {
            foreach ($cvars as $cvar) {
                if ($byKey) {
                    if (array_key_exists($cvar->getCvar_name(), $inclusion)) {
                        $returnable[] = $cvar;
                    }
                } else {
                    if (in_array($cvar->getCvar_name(), $inclusion)) {
                        $returnable[] = $cvar;
                    }
                }
                
            }
        }
        return $returnable;
    }
    
    public static function getCvarFormObject(Cvar $cvar = null, $cvarName = null, $disabled = "") {
        if ($cvar === null && $cvarName === null) {
            throw new BadFunctionCallException("Can't call getCvarFormObject without any args");
        }
        
        if ($cvarName === null || strlen(trim($cvarName)) === 0) {
            if ($cvar === null || !($cvar instanceof Cvar)) {
                throw new BadFunctionCallException("Can't call getCvarFormObject without any args");
            }
            $cvarName = $cvar->getCvar_name();
        } 
        
        if ($cvar === null || !($cvar instanceof Cvar)) {
            $cvar = self::findCvarByCvarName($cvarName);
        }
        
        switch ($cvar->getCvar_name()) {
            //yes-no convars
            case "3d_deadmonkey":
            case "hideseek_ExtendedRoundStats":
            case "g_objectiveLocations":
            case "g_useNoRoof":
            case "g_useNoMiddle":
            case "g_useNoWhole":
            case "g_useNoLower":
            case "g_autoEvenTeams":
            case "g_autoSwapTeams":
            case "g_disableNades":
            case "g_followEnemy":
            case "3d_fragwars":
            case "g_friendlyFire":
            case "g_instaGib":
            case "g_caserun":
            case "g_enableM203":
            case "g_pickupsDisabled":
            case "g_tdmUseTeamSpawns":
            case "g_camperPunish":
            case "g_camperSniper":
            case "g_allowThirdPerson":
            case "g_RpgStyle":
            case "g_enableTeamCmds":
            case "g_drownKills":
                $selectObj = new SelectObject($cvar->getCvar_name(), "required $disabled", $cvar->getCvar_name() . " - " . $cvar->getCvar_friendly_name() . "<button class='btn btn-outline-danger btn-sm float-right' onclick='removeCvar(\"" . $cvar->getCvar_name() . "\", \"" . $cvar->getCvar_friendly_name() . "\");'>Remove</button>");
                $selectObj->setLabelClass('w-100');
                $selectObj->appendSelect_options(new SelectOption("Off / disabled / no", 0, intval($cvar->getCvar_value()) === 0))
                        ->appendSelect_options(new SelectOption("On / enabled / yes", 1, intval($cvar->getCvar_value()) === 1));
                return $selectObj;
            //text inputs, g_motd separate so it shouldnt be removed
            case "g_motd":
                $formobj = new FormObject("input", $cvar->getCvar_name(), "text", "required $disabled", $cvar->getCvar_name() . " - " . $cvar->getCvar_friendly_name(), true, $cvar->getCvar_value());
                return $formobj;
            case "g_customWeaponFile":
            case "g_camperPunishment":
                $formobj = new FormObject("input", $cvar->getCvar_name(), "text", "required $disabled", $cvar->getCvar_name() . " - " . $cvar->getCvar_friendly_name() . "<button class='btn btn-outline-danger btn-sm float-right' onclick='removeCvar(\"" . $cvar->getCvar_name() . "\", \"" . $cvar->getCvar_friendly_name() . "\");'>Remove</button>", true, $cvar->getCvar_value());
                $formobj->setLabelClass('w-100');
                return $formobj;
            //numeric inputs
            case "g_speed":
            case "g_gravity":
            case "scorelimit":
            case "timelimit":
            case "g_maxGameClients":
            case "g_roundTimeLimit":
            case "hideSeek_roundstartdelay":
            case "g_respawnInterval":
            case "g_respawnInvulnerability":
            case "g_timeouttospec":
            case "g_camperAllowTime":
            case "g_camperRadius":
            case "g_knockback":
            case "g_drownStart":
                $formobj = new FormObject("input", $cvar->getCvar_name(), "number", "required $disabled", $cvar->getCvar_name() . " - " . $cvar->getCvar_friendly_name() . "<button class='btn btn-outline-danger btn-sm float-right' onclick='removeCvar(\"" . $cvar->getCvar_name() . "\", \"" . $cvar->getCvar_friendly_name() . "\");'>Remove</button>", true, $cvar->getCvar_value());
                $formobj->setLabelClass('w-100');
                return $formobj;
            //floats
            case "g_drownSpeed":
                $formobj = new FormObject("input", $cvar->getCvar_name(), "number", "required $disabled step=.1", $cvar->getCvar_name() . " - " . $cvar->getCvar_friendly_name() . "<button class='btn btn-outline-danger btn-sm float-right' onclick='removeCvar(\"" . $cvar->getCvar_name() . "\", \"" . $cvar->getCvar_friendly_name() . "\");'>Remove</button>", true, $cvar->getCvar_value());
                $formobj->setLabelClass('w-100');
                return $formobj;
            //special cases.
            case "availableWeapons":
                $selectObj = new SelectObject($cvar->getCvar_name() . "[]", "multiple=multiple required data-toggle='select2' $disabled", $cvar->getCvar_name() . " - " . $cvar->getCvar_friendly_name() . "<button class='btn btn-outline-danger btn-sm float-right' onclick='removeCvar(\"" . $cvar->getCvar_name() . "\", \"" . $cvar->getCvar_friendly_name() . "\");'>Remove</button>");
                $selectObj->setLabelClass('w-100');
                $selectObj
                        ->appendSelect_options(new SelectOption("Knife", "knife", self::isWeaponAvailable($cvar->getCvar_value(), "knife")))
                        ->appendSelect_options(new SelectOption("M1911A (pistol)", "m1911a", self::isWeaponAvailable($cvar->getCvar_value(), "m1911a")))
                        ->appendSelect_options(new SelectOption("USSOCOM (pistol)", "ussocom", self::isWeaponAvailable($cvar->getCvar_value(), "ussocom")))
                        ->appendSelect_options(new SelectOption("M590 Shotgun (secondary)", "shotgun", self::isWeaponAvailable($cvar->getCvar_value(), "shotgun")))
                        ->appendSelect_options(new SelectOption("Micro Uzi (secondary)", "uzi", self::isWeaponAvailable($cvar->getCvar_value(), "uzi")))
                        ->appendSelect_options(new SelectOption("M3A1 (secondary)", "M3A1", self::isWeaponAvailable($cvar->getCvar_value(), "M3A1")))
                        ->appendSelect_options(new SelectOption("USAS12 Autoshotgun (primary)", "USAS", self::isWeaponAvailable($cvar->getCvar_value(), "USAS")))
                        ->appendSelect_options(new SelectOption("M4 (primary)", "M4", self::isWeaponAvailable($cvar->getCvar_value(), "M4")))
                        ->appendSelect_options(new SelectOption("AK74 (primary)", "AK", self::isWeaponAvailable($cvar->getCvar_value(), "AK")))
                        ->appendSelect_options(new SelectOption("MSG90A1 Sniper (primary)", "Sniper", self::isWeaponAvailable($cvar->getCvar_value(), "Sniper")))
                        ->appendSelect_options(new SelectOption("M60 Machinegun (primary)", "Machinegun", self::isWeaponAvailable($cvar->getCvar_value(), "Machinegun")))
                        ->appendSelect_options(new SelectOption("MM1 Grenade Launcher (primary)", "MM1", self::isWeaponAvailable($cvar->getCvar_value(), "MM1")))
                        ->appendSelect_options(new SelectOption("RPG-7 (primary)", "RPG", self::isWeaponAvailable($cvar->getCvar_value(), "RPG")))
                        ->appendSelect_options(new SelectOption("M84 Flash (grenade)", "Flash", self::isWeaponAvailable($cvar->getCvar_value(), "Flash")))
                        ->appendSelect_options(new SelectOption("SMOH92 Frag (grenade)", "Frag", self::isWeaponAvailable($cvar->getCvar_value(), "Frag")))
                        ->appendSelect_options(new SelectOption("ANM14 Fire (grenade)", "Fire", self::isWeaponAvailable($cvar->getCvar_value(), "Fire")))
                        ->appendSelect_options(new SelectOption("M15 Smoke (grenade)", "Smoke", self::isWeaponAvailable($cvar->getCvar_value(), "Smoke")))
                        ;
                return $selectObj;
            case "hideSeek_Weapons":
                $selectObj = new SelectObject($cvar->getCvar_name() . "[]", "multiple=multiple required data-toggle='select2' $disabled", $cvar->getCvar_name() . " - " . $cvar->getCvar_friendly_name() . "<button class='btn btn-outline-danger btn-sm float-right' onclick='removeCvar(\"" . $cvar->getCvar_name() . "\", \"" . $cvar->getCvar_friendly_name() . "\");'>Remove</button>");
                $selectObj->setLabelClass('w-100');
                $selectObj
                        ->appendSelect_options(new SelectOption("RPG", "rpg", self::isHSWeaponAvailable($cvar->getCvar_value(), "rpg")))
                        ->appendSelect_options(new SelectOption("M4", "m4", self::isHSWeaponAvailable($cvar->getCvar_value(), "m4")))
                        ->appendSelect_options(new SelectOption("MM1", "mm1", self::isHSWeaponAvailable($cvar->getCvar_value(), "mm1")))
                        ;
                return $selectObj;
            case "hideSeek_Nades":
                $selectObj = new SelectObject($cvar->getCvar_name() . "[]", "multiple=multiple required data-toggle='select2' $disabled", $cvar->getCvar_name() . " - " . $cvar->getCvar_friendly_name() . "<button class='btn btn-outline-danger btn-sm float-right' onclick='removeCvar(\"" . $cvar->getCvar_name() . "\", \"" . $cvar->getCvar_friendly_name() . "\");'>Remove</button>");
                $selectObj->setLabelClass('w-100');
                $selectObj
                        ->appendSelect_options(new SelectOption("SMOHG92 (frag)", "Frag", self::isHSNadeAvailable($cvar->getCvar_value(), "Frag")))
                        ->appendSelect_options(new SelectOption("M84 (flash)", "Flash", self::isHSNadeAvailable($cvar->getCvar_value(), "Flash")))
                        ->appendSelect_options(new SelectOption("M15 (smoke)", "Smoke", self::isHSNadeAvailable($cvar->getCvar_value(), "Smoke")))
                        ->appendSelect_options(new SelectOption("AMN14 (fire)", "Fire", self::isHSNadeAvailable($cvar->getCvar_value(), "Fire")))
                        ;
                return $selectObj;
            case "hideSeek_Extra":
                $selectObj = new SelectObject($cvar->getCvar_name() . "[]", "multiple=multiple required data-toggle='select2' $disabled", $cvar->getCvar_name() . " - " . $cvar->getCvar_friendly_name() . "<button class='btn btn-outline-danger btn-sm float-right' onclick='removeCvar(\"" . $cvar->getCvar_name() . "\", \"" . $cvar->getCvar_friendly_name() . "\");'>Remove</button>");
                $selectObj->setLabelClass('w-100');
                $selectObj
                        ->appendSelect_options(new SelectOption("MDN11 (box)", "box", self::isHSExtraAvailable($cvar->getCvar_value(), "box")))
                        ->appendSelect_options(new SelectOption("F1 (teleport)", "tele", self::isHSExtraAvailable($cvar->getCvar_value(), "tele")))
                        ->appendSelect_options(new SelectOption("L2A2 (uppercut)", "uc", self::isHSExtraAvailable($cvar->getCvar_value(), "uc")))
                        ->appendSelect_options(new SelectOption("Nightvision (invisibility goggles)", "invi", self::isHSExtraAvailable($cvar->getCvar_value(), "invi")))
                        ->appendSelect_options(new SelectOption("Briefcase (faster knife attack)", "brief", self::isHSExtraAvailable($cvar->getCvar_value(), "brief")))
                        ->appendSelect_options(new SelectOption("M67 (random [?] grenade)", "random", self::isHSExtraAvailable($cvar->getCvar_value(), "random")))
                        ;
                return $selectObj;
            case "dmflags":
                $selectObj = new SelectObject($cvar->getCvar_name() . "[]", "multiple=multiple data-toggle='select2' $disabled", $cvar->getCvar_name() . " - " . $cvar->getCvar_friendly_name() . "<button class='btn btn-outline-danger btn-sm float-right' onclick='removeCvar(\"" . $cvar->getCvar_name() . "\", \"" . $cvar->getCvar_friendly_name() . "\");'>Remove</button>");
                $selectObj->setLabelClass('w-100');
                $selectObj
                        ->appendSelect_options(new SelectOption("No falldamage", 8, self::dmflagsCheck($cvar->getCvar_value(), 8)))
                        ->appendSelect_options(new SelectOption("Fixed FOV (be sure you know what you're doing)", 16, self::dmflagsCheck($cvar->getCvar_value(), 16)))
                        ->appendSelect_options(new SelectOption("No footsteps", 32, self::dmflagsCheck($cvar->getCvar_value(), 32)));
                return $selectObj;
                
                
        }
        
        return null;
        
        
    }
    
    private static function yesNoGenerator($input, $options, $yes = 2, $no = 0) {
        if (in_array($input, $options)) {
            return "$yes";
        }
        return "$no";
    }
    
    public static function generateHSExtra($options, $generateEmpty = false) {
        $tmpstr = "";
        if (!is_array($options) || sizeof($options) === 0) {
            if ($generateEmpty) {
                return "000000";
            }
            return "111111";
        }
        $tmpstr = self::yesNoGenerator("box", $options, 1)
                . self::yesNoGenerator("tele", $options, 1)
                . self::yesNoGenerator("uc", $options, 1)
                . self::yesNoGenerator("invi", $options, 1)
                . self::yesNoGenerator("brief", $options, 1)
                . self::yesNoGenerator("random", $options, 1);
        
        return $tmpstr;
    }
    
    public static function isHSExtraAvailable($hideSeek_extra, $weaponName) {
        $intmap = array(
            "box" => 0
            , "tele" => 1
            , "uc" => 2
            , "invi" => 3
            , "brief" => 4
            , "random" => 5
        );
        $wpnInt = $intmap[$weaponName];
        return intval($hideSeek_extra[$wpnInt]) !== 0;
    }
    
    public static function generateHSNade($options, $generateEmpty = false) {
        $tmpstr = "";
        if (!is_array($options) || sizeof($options) === 0) {
            if ($generateEmpty) {
                return "0000";
            }
            return "1111";
        }
        $tmpstr = self::yesNoGenerator("Frag", $options, 1)
                . self::yesNoGenerator("Flash", $options, 1)
                . self::yesNoGenerator("Smoke", $options, 1)
                . self::yesNoGenerator("Fire", $options, 1);
        
        return $tmpstr;
    }
    
    public static function isHSNadeAvailable($hideSeek_nades, $weaponName) {
        $intmap = array(
            "Frag" => 0
            , "Flash" => 1
            , "Smoke" => 2
            , "Fire" => 3
        );
        $wpnInt = $intmap[$weaponName];
        return intval($hideSeek_nades[$wpnInt]) !== 0;
    }
    
    public static function generateHSWeapon($options, $generateEmpty = false) {
        $tmpstr = "";
        if (!is_array($options) || sizeof($options) === 0) {
            if ($generateEmpty) {
                return "000";
            }
            return "111";
        }
        $tmpstr = self::yesNoGenerator("rpg", $options, 1)
                . self::yesNoGenerator("m4", $options, 1)
                . self::yesNoGenerator("mm1", $options, 1);
        
        return $tmpstr;
    }
    
    public static function isHSWeaponAvailable($hideSeek_weapons, $weaponName) {
        $intmap = array(
            "rpg" => 0
            , "m4" => 1
            , "mm1" => 2
        );
        
        $wpnInt = $intmap[$weaponName];
        return intval($hideSeek_weapons[$wpnInt]) !== 0;
    }
    
    public static function generateAvailableWeapons($options) {
        $tmpstr = "";
        if (!is_array($options) || sizeof($options) === 0) {
            return "200200002200000000000";
        }
        $tmpstr = self::yesNoGenerator("knife", $options)
                . self::yesNoGenerator("m1911a", $options)
                . self::yesNoGenerator("ussocom", $options)
                . self::yesNoGenerator("shotgun", $options)
                . self::yesNoGenerator("uzi", $options)
                . self::yesNoGenerator("M3A1", $options)
                . self::yesNoGenerator("USAS", $options)
                . self::yesNoGenerator("M4", $options)
                . self::yesNoGenerator("AK", $options)
                . self::yesNoGenerator("Sniper", $options)
                . self::yesNoGenerator("Machinegun", $options)
                . self::yesNoGenerator("MM1", $options)
                . self::yesNoGenerator("RPG", $options)
                . "0"
                . self::yesNoGenerator("Flash", $options)
                . "000"
                . self::yesNoGenerator("Frag", $options)
                . self::yesNoGenerator("Fire", $options)
                . self::yesNoGenerator("Smoke", $options)
                
        ;
        
        return $tmpstr;
    }
    
    public static function isWeaponAvailable($availableWeapons, $weaponName) {
        $intmap = array(
            "knife" => 0
            , "m1911a" => 1
            , "ussocom" => 2
            , "shotgun" => 3
            , "uzi" => 4
            , "M3A1" => 5
            , "USAS" => 6
            , "M4" => 7
            , "AK" => 8
            , "Sniper" => 9
            , "Machinegun" => 10
            , "MM1" => 11
            , "RPG" => 12
            , "Flash" => 14
            , "Frag" => 18
            , "Fire" => 19
            , "Smoke" => 20
        );
        
        $wpnInt = $intmap[$weaponName];
        return intval($availableWeapons[$wpnInt]) !== 0;
    }
    
	/*
	This used to be a redundancy check (and remover), but turned out I needed to duplicate all cvars instead, cba'd to rename
	*/
    public static function redundancyCheck(EntityMap $entityMap, $cvarList = null) {
        
        if ($cvarList === null) {
            $cvarList = array();
        }
        
        if ($entityMap === null || !($entityMap instanceof EntityMap)) {
            return array();
        }
        
        $cvarmaps = CvarMap::getCvarMapByEntityMap($entityMap);
        $foundVariables = array();
        if ($cvarmaps !== null && is_array($cvarmaps) && sizeof($cvarmaps) > 0) {
            foreach ($cvarmaps as $cvarmap) {
                $cvar = $cvarmap->getCvar();
                if ($cvar === null || !($cvar instanceof Cvar)) {
                    continue;
                }
                array_push($foundVariables, $cvar->getCvar_name());
                /*if ($cvarList !== null && is_array($cvarList)) {
                    if (array_key_exists($cvar->getCvar_name(), $cvarList)) {
                        $prevCvar = $cvarList[$cvar->getCvar_name()];
                        if ($prevCvar instanceof Cvar) {
                            /*if ($prevCvar->equals($cvar)) {
                                //$cvarmap->delete(); //redundant.
                                continue;
                            } 
                            
                        }
                    }
                }*/
                $cvarList[$cvar->getCvar_name()] = $cvar;
            }
        }
        //throw new Exception("foundVariables - " . print_r($foundVariables, true));
        if ($cvarList !== null && is_array($cvarList) && sizeof($cvarList) > 0) {
            foreach ($cvarList as $cvName => $cvObj) {
                if (in_array($cvName, $foundVariables)) {
                    continue;
                }
                $cvMap = new CvarMap(null, $cvObj->getCvar_id(), $entityMap->getEntitymap_id());
                $cvMap->write();
            }
        }
        
        return $cvarList;
    }
    
    
    
    public function populate() {
        $sql = Constants::getSQL();
        $query = "";
        $params = array();
        if ($this->getCvar_id() !== null && intval($this->getCvar_id()) > 0) {
            $query = Constants::$SQL_SELECT['GET_CVAR_BY_ID'];
            $params = array($this->getCvar_id());
        } else if ($this->getCvar_name() !== null && $this->getCvar_value() !== null && strlen(trim($this->getCvar_name())) > 0 && strlen(trim($this->getCvar_value())) > 0) {
            $query = Constants::$SQL_SELECT['GET_CVARS_BY_CVAR_NAME_VALUE'];
            $params = array($this->getCvar_name(), $this->getCvar_value());
        } else {
            throw new BadFunctionCallException("can't call populate without cvar id or cvar name and value");
        }
        
        $data = $sql->query($query, $params);
        
        if ($data !== null && is_array($data) && sizeof($data) === 1) {
            $data = $data[0];
            return $this->setCvar_id($data['cvar_id'])->setCvar_name($data['cvar_name'])->setCvar_value($data['cvar_value'])->setCvar_friendly_name($data['cvar_friendly_name'])->setIsdefault($data['isdefault']);
        } else {
            return null;
        }
    }
    
    public function write() {
        if ($this->getCvar_id() !== null && intval($this->getCvar_id()) > 0) {
            //i dont want to update a cvar, i'd rather write a new one.
            $this->setCvar_id(null);
        } 
        
        $sql = Constants::getSQL();
        $query = Constants::$SQL_INSERT['INSERT_CVAR'];
        $params = array($this->getCvar_name(), $this->getCvar_value(), $this->getCvar_friendly_name());
        
        $data = $sql->query($query, $params);
        
        if ($data !== null && is_array($data) && array_key_exists("last_insert_id", $data)) {
            return $this->setCvar_id($data['last_insert_id']);
        }
        return null;
    }
    
    public static function dmflagsCheck($dmflag, $checkAgainst) {
        return intval($dmflag) === intval($checkAgainst) || intval($dmflag) & intval($checkAgainst);
    }
    
    

}

class CvarMap {
    
    private $cvar_map_id;
    private $cvar_id;
    private $entitymap_id;
    
    public function getCvar_map_id() {
        return $this->cvar_map_id;
    }

    public function getEntitymap_id() {
        return $this->entitymap_id;
    }
    
    public function setEntitymap_id($entitymap_id) {
        $this->entitymap_id = $entitymap_id;
        return $this;
    }

    
    public function setCvar_map_id($cvar_map_id) {
        $this->cvar_map_id = $cvar_map_id;
        return $this;
    }

    public function __construct($cvar_map_id = null, $cvar_id = null, $entitymap_id = null) {
        $this->cvar_map_id = $cvar_map_id;
        $this->cvar_id = $cvar_id;
        $this->entitymap_id = $entitymap_id;
    }
    
    public function getCvar_id() {
        return $this->cvar_id;
    }
    
    /**
     * 
     * @return Cvar 
     */
    public function getCvar() {
        $cvar = new Cvar($this->getCvar_id());
        return $cvar->populate();
    }
    
    /**
     * 
     * @return Entity
     */
    public function getEntity() {
        return $this->getEntityMap()->getEntity();
    }
    
    public function getEntityMap() {
        $entityMap = new EntityMap($this->getEntitymap_id());
        return $entityMap->populate();
    }
    
    

    public function setCvar_id($cvar_id) {
        $this->cvar_id = $cvar_id;
        return $this;
    }
    
    public function write() {
        $sql = Constants::getSQL();
        if ($this->getCvar_map_id() !== null && intval($this->getCvar_map_id()) > 0) {
            $query = Constants::$SQL_UPDATE['UPDATE_CVAR_MAP'];
            $params = array($this->getEntitymap_id(), $this->getCvar_id(), $this->getCvar_map_id());
            $sql->query($query, $params);
        } else {
            $query = Constants::$SQL_INSERT['INSERT_CVAR_MAP'];
            $params = array($this->getCvar_id(), $this->getEntitymap_id());
            $data = $sql->query($query, $params);
            
            if ($data === null || !is_array($data) || !array_key_exists("last_insert_id", $data)) {
                return null;
            }
            $this->setCvar_map_id($data['last_insert_id']);
        }
        
        return $this;
    }
    
    public function delete() {
        if ($this->getCvar_map_id() === null || intval($this->getCvar_map_id()) === 0) {
            throw new BadFunctionCallException("call delete() without initializing object");
        }
        
        $sql = Constants::getSQL();
        $query = Constants::$SQL_DELETE['DELETE_CVAR_MAP'];
        $params = array($this->getCvar_map_id());
        $sql->query($query, $params);
    }
    
    public static function deleteCvarMapByEntitymap(EntityMap $entityMap) {
        $sql = Constants::getSQL();
        $query = Constants::$SQL_DELETE['DELETE_CVAR_MAP_BY_ENTITYMAP_ID'];
        $params = array($entityMap->getEntitymap_id());
        $sql->query($query, $params);
    }
    
    public function populate() {
        $sql = Constants::getSQL();
        $query = "";
        $params = array();
        if ($this->getCvar_map_id() !== null && intval($this->getCvar_map_id()) > 0) {
            $query = Constants::$SQL_SELECT['GET_CVARS_MAP_BY_ID'];
            $params = array($this->getCvar_map_id());
        } else if ($this->getEntitymap_id() !== null && $this->getCvar_id() !== null && intval($this->getEntitymap_id()) > 0 && intval($this->getCvar_id()) > 0) {
            $query = Constants::$SQL_SELECT['GET_CVARS_MAP_BY_CVAR_ID_ENTITYMAP_ID'];
            $params = array($this->getEntitymap_id(), $this->getCvar_id());
        } else {
            throw new BadFunctionCallException("call populate() without sufficient args");
        }
        
        $data = $sql->query($query, $params);
        
        if ($data !== null && is_array($data) && sizeof($data) === 1) {
            $data = $data[0];
            return 
                $this->setCvar_id($data['cvar_id'])
                    ->setCvar_map_id($data['cvar_map_id'])
                    ->setEntitymap_id($data['entitymap_id']);
        } 
        return null;
        
    }
    
    public static function generateObjectFromSQLRow($row) {
        $obj = new static();
        
        $obj
                ->setCvar_id($row['cvar_id'])
                ->setCvar_map_id($row['cvar_map_id'])
                ->setEntitymap_id($row['entitymap_id'])
                ;
        
        return $obj;
    }
    
    public static function getCvarMapByEntityMap(EntityMap $entityMap) {
        $sql = Constants::getSQL();
        $query = Constants::$SQL_SELECT['GET_CVARS_MAP_BY_ENTITYMAP_ID_ORDER_MOTD'];
        $params = array($entityMap->getEntitymap_id());
        $data = $sql->query($query, $params);
        $returnable = array();
        if ($data !== null && is_array($data) && sizeof($data) > 0) {
            foreach ($data as $row) {
                $returnable[] = self::generateObjectFromSQLRow($row);
            }
        }
        
        return $returnable;
    }
    
}

class PushToLive {
    
    private $pushtolive_id;
    private $push_created_by;
    private $push_created_at;
    private $push_created_by_ip;
    private $mapcycle_id;
    private $live_from;
    private $live_to;
    private $deleted;
    private $live;
    
    public static $DELETED = 1;
    public static $ACTIVE = 0;
    
    public static $MC_LIVE = 1;
    public static $MC_NOTLIVE = 0;
    
    public function __construct($pushtolive_id = null, $push_created_by = null, $push_created_at = null, $push_created_by_ip = null, $mapcycle_id = null, $live_from = null, $live_to = null, $deleted = null, $live = null) {
        $this->pushtolive_id = $pushtolive_id;
        $this->push_created_by = $push_created_by;
        $this->push_created_at = $push_created_at;
        $this->push_created_by_ip = $push_created_by_ip;
        $this->mapcycle_id = $mapcycle_id;
        $this->live_from = $live_from;
        $this->live_to = $live_to;
        $this->deleted = $deleted;
        $this->live = $live;
    }
    
    public function getLive() {
        return $this->live;
    }

    public function setLive($live) {
        $this->live = $live;
        return $this;
    }

        
    public function getDeleted() {
        return $this->deleted;
    }

    public function setDeleted($deleted) {
        $this->deleted = $deleted;
        return $this;
    }

    public function isDeleted() {
        return int2bool($this->getDeleted());
    }
        
    public function isLive() {
        //return trim($this->getLive_from()) <= date("Y-m-d H:i:s") && trim($this->getLive_to()) >= date("Y-m-d H:i:s");
        return int2bool($this->getLive()) && !$this->isDeleted(); //cant be live if isDeleted
    }
    
    public static function getLiveMapcycle() {
        $sql = Constants::getSQL();
        $query = Constants::$SQL_SELECT['GET_CURRENT_LIVE'];
        $data = $sql->query($query);
        if ($data !== null && is_array($data) && sizeof($data) === 1) {
            return self::generateFromSQLRow($data[0]);
        }
        return null;
    }
    
    public static function getLiveCandidate() {
        
        $sql = Constants::getSQL();
        $query = Constants::$SQL_SELECT['GET_CURRENT_LIVE_CANDIDATE'];
        $data = $sql->query($query);
        if ($data !== null && is_array($data) && sizeof($data) === 1) {
            return self::generateFromSQLRow($data[0]);
        }
        return null;
    }
    
    public function populate() {
        $sql = Constants::getSQL();
        $query = "";
        $params = array();
        if ($this->getPushtolive_id() !== null && intval($this->getPushtolive_id()) > 0) {
            $query = Constants::$SQL_SELECT['GET_PUSHTOLIVE_BY_ID'];
            $params = array($this->getPushtolive_id());
        } else if ($this->getLive_from() !== null && $this->getLive_to() !== null) {
            $query = Constants::$SQL_SELECT['GET_PUSHTOLIVE_BY_DATES_LF_LT'];
            $params = array($this->getLive_from(), $this->getLive_to());
        } else {
            $query = Constants::$SQL_SELECT['GET_CURRENT_LIVE'];
            
        }
        
        $data = $sql->query($query, $params);
        
        if ($data !== null && is_array($data) && sizeof($data) === 1) {
            $data = $data[0];
            return $this->setLive_from($data['live_from'])->setLive_to($data['live_to'])->setMapcycle_id($data['mapcycle_id'])->setPush_created_at($data['push_created_at'])
                    ->setPush_created_by($data['push_created_by'])->setPush_created_by_ip($data['push_created_by_ip'])->setPushtolive_id($data['pushtolive_id'])->setDeleted($data['deleted'])->setLive($data['live']);
        }
        
        return null;
    }
    
    public function write() {
        $sql = Constants::getSQL();
        if ($this->getPushtolive_id() !== null && intval($this->getPushtolive_id()) > 0) {
            $query = Constants::$SQL_UPDATE['UPDATE_PUSHTOLIVE'];
            $params = array($this->getLive_from(), $this->getLive_to(), $this->getDeleted(), $this->getLive(), $this->getPushtolive_id());
            $sql->query($query, $params);
        } else {
            $query = Constants::$SQL_INSERT['INSERT_PUSHTOLIVE'];
            $params = array($this->getPush_created_by(), $this->getPush_created_by_ip(), $this->getMapcycle_id(), $this->getLive_from(), $this->getLive_to());
            $data = $sql->query($query, $params);
            if ($data !== null && is_array($data) && array_key_exists("last_insert_id", $data)) {
                $this->setPushtolive_id($data['last_insert_id']);
            }
        }
        return $this;
    }
    
    public function delete($softDelete = true) {
        $sql = Constants::getSQL();
        
        if ($softDelete) {
            return $this->setDeleted(PushToLive::$DELETED)->setLive(PushToLive::$MC_NOTLIVE)->write();
        } else {
            $query = Constants::$SQL_DELETE['DELETE_PUSHTOLIVE'];
            $params = array($this->getPushtolive_id());
            $sql->query($query, $params);
        }
    }
    
    public static function generateFromSQLRow($row) {
        return new static($row['pushtolive_id'], $row['push_created_by'], $row['push_created_at'], $row['push_created_by_ip'], $row['mapcycle_id'], $row['live_from'], $row['live_to'], $row['deleted'], $row['live']);
    }
    
    public static function getPushtoliveByMapcycleId($mapcycle_id, $onlyActive = true) {
        $sql = Constants::getSQL();
        if ($onlyActive) {
            $query = Constants::$SQL_SELECT['GET_PUSHTOLIVE_BY_MAPCYCLE_ACTIVE'];
        } else {
            $query = Constants::$SQL_SELECT['GET_PUSHTOLIVE_BY_MAPCYCLE'];
        }
        $params = array($mapcycle_id);
        
        $data = $sql->query($query, $params);
        $returnable = array();
        if ($data !== null && is_array($data) && sizeof($data) > 0) {
            foreach ($data as $row) {
                $returnable[] = self::generateFromSQLRow($row);
            }
        }
        return $returnable;
    }
    
    public static function getPushtoliveByUserId($user_id) {
        $sql = Constants::getSQL();
        $query = Constants::$SQL_SELECT['GET_PUSHTOLIVE_BY_USER'];
        $params = array($user_id);
        
        $data = $sql->query($query, $params);
        $returnable = array();
        if ($data !== null && is_array($data) && sizeof($data) > 0) {
            foreach ($data as $row) {
                $returnable[] = self::generateFromSQLRow($row);
            }
        }
        return $returnable;
    }
    
    public static function checkCollision($live_from, $live_to) {
        $sql = Constants::getSQL();
        $query = Constants::$SQL_SELECT['CHECK_PUSHTOLIVE_COLLISION'];
        $params = array($live_from, $live_from, $live_to, $live_to);
        $data = $sql->query($query, $params);
        $returnable = array();
        if ($data !== null && is_array($data) && sizeof($data) > 0) {
            foreach ($data as $row) {
                $returnable[] = self::generateFromSQLRow($row);
            }
        }
        
        return $returnable;
        
    }
    
    public static function checkCollisionWithExclusion(PushToLive $ptl, $live_from, $live_to) {
        $sql = Constants::getSQL();
        $query = Constants::$SQL_SELECT['CHECK_PUSHTOLIVE_COLLISION_EXCL_PTL'];
        $params = array($ptl->getPushtolive_id(), $live_from, $live_from, $live_to, $live_to);
        $data = $sql->query($query, $params);
        $returnable = array();
        if ($data !== null && is_array($data) && sizeof($data) > 0) {
            foreach ($data as $row) {
                $returnable[] = self::generateFromSQLRow($row);
            }
        }
        
        return $returnable;
    }
    
    public static function getPushToLives($includeDeleted = false) {
        $sql = Constants::getSQL();
        $query = "";
        
        if (!$includeDeleted) {
            $query = Constants::$SQL_SELECT['GET_PUSHTOLIVE_NOT_DELETED'];
        } else {
            $query = Constants::$SQL_SELECT['GET_PUSHTOLIVE_ALL'];
        }
        
        $returnable = array();
        
        $data = $sql->query($query);
        
        if ($data !== null && is_array($data) && sizeof($data) > 0) {
            foreach ($data as $row) {
                $returnable[] = self::generateFromSQLRow($row);
            }
        }
        
        return $returnable;
    }
    
    public static function getPushToLiveOutlook() {
        $sql = Constants::getSQL();
        $query = Constants::$SQL_SELECT['GET_PUSHTOLIVE_OUTLOOK'];
        
        $returnable = array();
        
        $data = $sql->query($query);
        
        if ($data !== null && is_array($data) && sizeof($data) > 0) {
            foreach ($data as $row) {
                $returnable[] = self::generateFromSQLRow($row);
            }
        }
        
        return $returnable;
    }
    
    public static function checkCollisionWithPushToLive(PushToLive $ptl) {
        return self::checkCollision($ptl->getLive_from(), $ptl->getLive_to());
    }
    
    public function getPushtolive_id() {
        return $this->pushtolive_id;
    }

    public function getPush_created_by() {
        return $this->push_created_by;
    }

    public function getPush_created_at() {
        return $this->push_created_at;
    }

    public function getPush_created_by_ip() {
        return $this->push_created_by_ip;
    }

    public function getMapcycle_id() {
        return $this->mapcycle_id;
    }

    public function getLive_from() {
        return $this->live_from;
    }

    public function getLive_to() {
        return $this->live_to;
    }

    public function setPushtolive_id($pushtolive_id) {
        $this->pushtolive_id = $pushtolive_id;
        return $this;
    }

    public function setPush_created_by($push_created_by) {
        $this->push_created_by = $push_created_by;
        return $this;
    }

    public function setPush_created_at($push_created_at) {
        $this->push_created_at = $push_created_at;
        return $this;
    }

    public function setPush_created_by_ip($push_created_by_ip) {
        $this->push_created_by_ip = $push_created_by_ip;
        return $this;
    }

    public function setMapcycle_id($mapcycle_id) {
        $this->mapcycle_id = $mapcycle_id;
        return $this;
    }

    public function setLive_from($live_from) {
        $this->live_from = $live_from;
        return $this;
    }

    public function setLive_to($live_to) {
        $this->live_to = $live_to;
        return $this;
    }
    
    public function pushToServer($server_id) {
        //the actual push...
        
        
        
    }
    
    private static function getServerCredentials($server_id) {
        $postData = http_build_query(array(
            "username" => Constants::getMCApiUser()
                , "password" => Constants::getMCApiPassword()
                , "getServers" => $server_id
                , "withPassword" => 1
        ));
        
        
        
        $ch = curl_init(Constants::getMCApiURL());
        
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true
            , CURLOPT_POST => true
            , CURLOPT_POSTFIELDS => $postData
        ));
        
        $result = curl_exec($ch);
        
        $result = json_decode($result, true);
        
        
        if (array_key_exists("servers", $result)) {
            $servers = $result['servers'];
            
            if (sizeof($servers) === 1) {
                $servers = $servers[0];
                return array("srv_user" => $servers['server_account'], "srv_pass" => $servers['server_password']);
            }
        }
        
        return null;
    }
    
    public function pushToProd($useException = true) {
        $credentials = self::getServerCredentials(Constants::$PROD_SERVER_ID);
        $mapcycle = new Mapcycle($this->getMapcycle_id());
        $mapcycle = $mapcycle->populate();
        
        if ($credentials === null || !is_array($credentials) || !array_key_exists("srv_user", $credentials)) {
            if ($useException) {
                throw new Exception("Prod server credentials fetch failed.");
            }
            return array("error" => "Prod server credentials fetch failed.");
        }
        
        if ($mapcycle === null || !($mapcycle instanceof Mapcycle)) {
            if ($useException) {
                throw new Exception("Mapcycle not found.");
            }
            return array("error" => "Mapcycle not found.");
        }
        
        if (!$mapcycle->isApproved()) {
            if ($useException) {
                throw new Exception("Mapcycle is not approved, cannot push to prod");
            }
            return array("error" => "Mapcycle is not approved, cannot push to prod");
        }
        
        $entmap = EntityMap::getOrderedEntities($mapcycle);
        
        if ($entmap === null || !is_array($entmap) || sizeof($entmap) === 0) {
            if ($useException) {
                throw new Exception("Entmap empty (meaning there are no maps in the mapcycle???)");
            }
            return array("error" => "Entmap empty (meaning there are no maps in the mapcycle???)");
        }
        
        $writables = array();
        $redundancyCvars = array();
        for ($i = 0; $i < sizeof($entmap); $i++) {
            $emap = $entmap[$i];
            if ($i === 0 && $emap->isAltmap()) {
                if ($useException) {
                    throw new Exception("First map cannot be an altmap. Change the ordering before sending to prod.");
                }
                return array("error" => "First map cannot be an altmap. Change the ordering before sending to prod.");
            }
            $entity = $emap->getEntity();
            
            if ($entity === null || !($entity instanceof Entity)) {
                if ($useException) {
                    throw new Exception("A non-existing entity in entmap...");
                }
                return array("error" => "A non-existing entity in entmap...");
            }
            
            $redundancyCvars = Cvar::redundancyCheck($emap, $redundancyCvars);
            
            if ($emap->isAltmap()) {
                $writables['ents']['alt'][$entity->getMap_name()] = $entity->getMap_entity();
            } else {
                $writables['ents'][$emap->getGametype()][$entity->getMap_name()] = $entity->getMap_entity();
            }
        }
        
        //should be good to go for prod.
        
        $username = $credentials['srv_user'];
        $password = $credentials['srv_pass'];
        
        //write MC:
        
        $mapcycleContents = $mapcycle->compile();
        
        if (strlen($mapcycleContents) > Constants::$MC_FILE_MAXSIZE) {
            if ($useException) {
                throw new Exception("Mapcycle contents file exceeds MC_FILE_MAXSIZE (MC size - " . strlen($mapcycleContents) . ", maxsize - " . Constants::$MC_FILE_MAXSIZE);
            }
            return array("error" => "Mapcycle contents file exceeds MC_FILE_MAXSIZE (MC size - " . strlen($mapcycleContents) . ", maxsize - " . Constants::$MC_FILE_MAXSIZE);
        }
        
        if (self::writeFile(Constants::$FILE_PATH_MC, "mapcycle.txt", $mapcycleContents, $username, $password) === false) {
            return array("error" => "Failed on writing mapcycle.");
        }
        
        foreach ($writables['ents'] as $gametype => $mapData) {
            foreach ($mapData as $mapName => $mapEntity) {
                $filePath = str_replace("{GAMETYPE}", $gametype, Constants::$FILE_PATH_ENTGT);
                if (self::writeFile($filePath, $mapName . ".ent", $mapEntity, $username, $password) === false) {
                    return array("error" => "Failed on writing map $mapName (gt $gametype) to the entity folder.");
                }
            }
        }
        return array("msg" => "Mapcycle successfully pushed to prod");
    }

    public static function pushEntityToPreprod(Entity $entity) {
        $credentials = self::getServerCredentials(Constants::$PREPROD_SERVER_ID);
        
        if ($credentials === null || !is_array($credentials) || !array_key_exists("srv_user", $credentials)) {
            return array("error" => "Preprod server credentials fetch failed.");
        }
        
        $username = $credentials['srv_user'];
        $password = $credentials['srv_pass'];
        
        $filePath = str_replace("{GAMETYPE}", "h&s", Constants::$FILE_PATH_ENTGT);
        if (self::writeFile($filePath, $entity->getMap_name() . ".ent", $entity->getMap_entity(), $username, $password) === false) {
            return array("error" => "Failed on writing map (gt h&s) to the entity folder.");
        }
        return array("msg" => "Entity pushed to preprod (gt h&s folder)");
    }

    public static function pushToPreProd(Mapcycle $mapcycle) {
        $credentials = self::getServerCredentials(Constants::$PREPROD_SERVER_ID);
        
        if ($credentials === null || !is_array($credentials) || !array_key_exists("srv_user", $credentials)) {
            return array("error" => "Preprod server credentials fetch failed.");
        }
        
        if ($mapcycle === null || !($mapcycle instanceof Mapcycle)) {
            return array("error" => "Mapcycle not found.");
        }
        
        if (!$mapcycle->isApproved()) {
            return array("error" => "Mapcycle is not approved, cannot push to preprod");
        }
        
        $entmap = EntityMap::getOrderedEntities($mapcycle);
        
        if ($entmap === null || !is_array($entmap) || sizeof($entmap) === 0) {
            return array("error" => "Entmap empty (meaning there are no maps in the mapcycle???)");
        }
        
        $writables = array();
        $redundancyCvars = array();
        for ($i = 0; $i < sizeof($entmap); $i++) {
            $emap = $entmap[$i];
            if ($i === 0 && $emap->isAltmap()) {
                return array("error" => "First map cannot be an altmap. Change the ordering before sending to preprod.");
            }
            $entity = $emap->getEntity();
            
            if ($entity === null || !($entity instanceof Entity)) {
                return array("error" => "A non-existing entity in entmap...");
            }
            
            $redundancyCvars = Cvar::redundancyCheck($emap, $redundancyCvars);
            
            if ($emap->isAltmap()) {
                $writables['ents']['alt'][$entity->getMap_name()] = $entity->getMap_entity();
            } else {
                $writables['ents'][$emap->getGametype()][$entity->getMap_name()] = $entity->getMap_entity();
            }
        }
        
        //should be good to go for preprod.
        
        $username = $credentials['srv_user'];
        $password = $credentials['srv_pass'];
        
        //write MC:
        if (self::writeFile(Constants::$FILE_PATH_MC, "mapcycle.txt", $mapcycle->compile(), $username, $password) === false) {
            return array("error" => "Failed on writing mapcycle.");
        }
        
        foreach ($writables['ents'] as $gametype => $mapData) {
            foreach ($mapData as $mapName => $mapEntity) {
                $filePath = str_replace("{GAMETYPE}", $gametype, Constants::$FILE_PATH_ENTGT);
                if (self::writeFile($filePath, $mapName . ".ent", $mapEntity, $username, $password) === false) {
                    return array("error" => "Failed on writing map $mapName (gt $gametype) to the entity folder.");
                }
            }
        }
        return array("msg" => "Mapcycle successfully pushed to preprod");
    }
    
	/*
	This is a horrible function. PHP doesn't reuse the connection, therefore it spawns a new one each call.
	It's better to rewrite it to a standard, single ftp connection and push files through a single connection.
	now rewritten to reuse the FTP handle.
	*/
    private static $FTP_HANDLERS = array();
    private static function writeFile($filePath, $fileName, $fileContents, $username, $password, $ip = "127.0.0.1", $port = 2123) {
        $ftpHandle = null;
        if (array_key_exists("$ip:$port:$username", self::$FTP_HANDLERS)) {
            $ftpHandle = self::$FTP_HANDLERS["$ip:$port:$username"];
            if (!ftp_pasv($ftpHandle, true)) {
                $ftpHandle = null;
                unset(self::$FTP_HANDLERS["$ip:$port:$username"]);
            } else {
            }
        }
        
        if ($ftpHandle === null) {
            $ftpHandle = ftp_connect($ip, $port);
            ftp_login($ftpHandle, $username, $password);
            ftp_pasv($ftpHandle, true);
            self::$FTP_HANDLERS["$ip:$port:$username"] = $ftpHandle;
        }
        $stream = fopen('data://text/plain,' . $fileContents, 'r');
        return ftp_fput($ftpHandle, "$filePath/$fileName", $stream);
    }

    
}
