<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Entity
 *ALTER TABLE maps_entities ADD COLUMN deleted TINYINT DEFAULT 0 COMMENT '0 - not deleted, 1 - deleted'
 * @author Janno
 */
class Entity {
    
    private $entity_id;
    private $map_name;
    private $map_description;
    private $map_entity;
    private $imgur_links;
    private $uploaded_by;
    private $uploaded_by_ip;
    private $entity_approved;
    private $deleted;
    private $average_vote;
    private $entity_approval_changed;
    private $entity_approval_changed_ip;
    private $total_votes;
    private $map_score;
    
    public function __construct($entity_id = null, $map_name = null, $map_description = null, $map_entity = null, $imgur_links = null, $uploaded_by = null, $uploaded_by_ip = null, $entity_approved = null, $deleted = null, $average_vote = null, $entity_approval_changed = null, $entity_approval_changed_ip = null, $total_votes = null, $map_score = null) {
        $this->entity_id = $entity_id;
        $this->map_name = $map_name;
        $this->map_description = $map_description;
        $this->map_entity = $map_entity;
        $this->imgur_links = $imgur_links;
        $this->uploaded_by = $uploaded_by;
        $this->uploaded_by_ip = $uploaded_by_ip;
        $this->entity_approved = $entity_approved;
        $this->deleted = $deleted;
        $this->average_vote = $average_vote;
        $this->entity_approval_changed = $entity_approval_changed;
        $this->entity_approval_changed_ip = $entity_approval_changed_ip;
        $this->total_votes = $total_votes;
        $this->map_score = $map_score;
    }
    
    public function getMap_score() {
        return $this->map_score;
    }

    public function setMap_score($map_score) {
        $this->map_score = $map_score;
        return $this;
    }

        
    public function getTotal_votes() {
        return $this->total_votes;
    }

    public function setTotal_votes($total_votes) {
        $this->total_votes = $total_votes;
        return $this;
    }

        
    public static function getEntitiesByParams($getOnlyCount = false, $entity_id = null, $map_name = null, $map_description = null, $map_entity = null, $imgur_links = null, $uploaded_by = null, $uploaded_by_ip = null, $entity_approved = null, $deleted = null, $average_vote = null, $entity_approval_changed = null, $entity_approval_changed_ip = null, $filterByMCId = 0, $excludeByMCId = 0, $orderBy = "entity_id DESC", $limitFrom = 0, $limitTo = 30) {
        $whereSet = false;
        //build query.
        
        if ($getOnlyCount) {
            $query = "SELECT COUNT(DISTINCT me.entity_id) AS entcount FROM maps_entities me LEFT JOIN maps_entities_votes mev ON (mev.entity_id = me.entity_id) ";
        } else {
            $query = "SELECT me.*, COALESCE(AVG(mev.vote), 0) AS average_vote, COALESCE(COUNT(mev.vote), 0) AS total_votes, (COALESCE(COUNT(mev.vote), 0) * COALESCE(AVG(mev.vote), 0)) AS map_score FROM maps_entities me LEFT JOIN maps_entities_votes mev ON (mev.entity_id = me.entity_id) ";
        }
        //me.*, COALESCE(AVG(mev.vote), 0) AS average_vote FROM maps_entities me LEFT JOIN maps_entities_votes mev ON (mev.entity_id = me.entity_id)
        $params = array();
        
        if ($entity_id !== null) {
            if (!$whereSet) {
                $whereSet = true;
                $query .= " WHERE ";
            } else {
                $query .= " AND ";
            }
            
            $query .= " me.entity_id = ?";
            $params[] = $entity_id;
        }
        
        if ($map_name !== null) {
            if (!$whereSet) {
                $whereSet = true;

                $query .= " WHERE ";
            } else {
                $query .= " AND ";
            }
            
            $query .= " me.map_name LIKE ?";
            $params[] = "%$map_name%";
        }
        
        if ($map_description !== null) {
            if (!$whereSet) {
                $whereSet = true;

                $query .= " WHERE ";
            } else {
                $query .= " AND ";
            }
            
            $query .= " me.map_description = ?";
            $params[] = $map_description;
        }
        if ($map_entity !== null) {
            if (!$whereSet) {
                $whereSet = true;

                $query .= " WHERE ";
            } else {
                $query .= " AND ";
            }
            
            $query .= " me.map_entity = ?";
            $params[] = $map_entity;
        }
        
        if ($imgur_links !== null) {
            if (!$whereSet) {
                $whereSet = true;

                $query .= " WHERE ";
            } else {
                $query .= " AND ";
            }
            
            $query .= " me.imgur_links = ?";
            $params[] = $imgur_links;
        }
        if ($uploaded_by !== null) {
            if (!$whereSet) {
                $whereSet = true;

                $query .= " WHERE ";
            } else {
                $query .= " AND ";
            }
            
            $query .= " me.uploaded_by = ?";
            $params[] = $uploaded_by;
        }
        if ($uploaded_by_ip !== null) {
            if (!$whereSet) {
                $whereSet = true;

                $query .= " WHERE ";
            } else {
                $query .= " AND ";
            }
            
            $query .= " me.uploaded_by_ip = ?";
            $params[] = $uploaded_by_ip;
        }
        
        if ($entity_approved !== null) {
            if (!$whereSet) {
                $whereSet = true;

                $query .= " WHERE ";
            } else {
                $query .= " AND ";
            }
            
            $query .= " me.entity_approved = ?";
            $params[] = $entity_approved;
        }
        if ($deleted !== null) {
            if (!$whereSet) {
                $whereSet = true;

                $query .= " WHERE ";
            } else {
                $query .= " AND ";
            }
            
            $query .= " COALESCE(me.deleted, 0) = ? ";
            $params[] = $deleted;
        } else {
            //dont show deleted entities.
            if (!$whereSet) {
                $whereSet = true;
                $query .= " WHERE ";
            } else {
                $query .= " AND ";
            }
            
            $query .= " COALESCE(me.deleted, 0) = ? ";
            $params[] = 0;
        }
        
        
        
        if ($entity_approval_changed !== null) {
            if (!$whereSet) {
                $whereSet = true;

                $query .= " WHERE ";
            } else {
                $query .= " AND ";
            }
            
            $query .= " me.entity_approval_changed = ?";
            $params[] = $entity_approval_changed;
        }
        if ($entity_approval_changed_ip !== null) {
            if (!$whereSet) {
                $whereSet = true;

                $query .= " WHERE ";
            } else {
                $query .= " AND ";
            }
            
            $query .= " me.entity_approval_changed_ip = ?";
            $params[] = $entity_approval_changed_ip;
        }
        
        if ($filterByMCId !== null && intval($filterByMCId) > 0) {
            if (!$whereSet) {
                $whereSet = true;

                $query .= " WHERE ";
            } else {
                $query .= " AND ";
            }
            
            $query .= " me.entity_id IN (SELECT entity_id FROM maps_entitymap WHERE mapcycle_id = ?)";
            $params[] = intval($filterByMCId);
        }
        
        if ($excludeByMCId !== null && intval($excludeByMCId) > 0) {
            if (!$whereSet) {
                $whereSet = true;

                $query .= " WHERE ";
            } else {
                $query .= " AND ";
            }
            
            $query .= " me.entity_id NOT IN (SELECT entity_id FROM maps_entitymap WHERE mapcycle_id = ?)";
            $params[] = intval($excludeByMCId);
        }
        
        $sql = Constants::getSQL();
        //print_r($query);
        if ($getOnlyCount) {
            if ($average_vote !== null && intval($average_vote) > 0) {

                $query .= " HAVING COALESCE(AVG(mev.vote), 0) >= ? ";
                $params[] = intval($average_vote);
            }
            
            $entcountdata = $sql->query($query, $params);
            if (is_array($entcountdata) && sizeof($entcountdata) === 1) {
                return intval($entcountdata[0]['entcount']);
            }
            return 0;
        }
        
        $query .= " GROUP BY me.entity_id ";
        if ($average_vote !== null && intval($average_vote) > 0) {
            
            $query .= " HAVING COALESCE(AVG(mev.vote), 0) >= ? ";
            $params[] = intval($average_vote);
        }
        if ($orderBy !== null && strlen(trim($orderBy)) > 0) {
            //we dont call this function with a false argument.
            //we dont call it with an unknown input from user. 
            //Otherwise this would be the most stupid SQL injectable code ever.
            $query .= " ORDER BY $orderBy ";
        }
        
        
        
        if ($limitFrom !== null && intval($limitFrom) >= 0 && $limitTo !== null && intval($limitTo) > 0) {
            $query .= " LIMIT " . intval($limitFrom) . "," . intval($limitTo);
        } else if ($limitFrom !== null && intval($limitFrom) > 0) {
            $query .= " LIMIT " . intval($limitFrom);
        }
        $data = $sql->query($query, $params);
        $returnable = array();
        if (is_array($data) && sizeof($data) > 0) {
            foreach ($data as $row) {
                $returnable[] = self::generateFromSQLRow($row);
            }
        }
        return $returnable;
    }
    
    public static function getEntityCount() {
        $query = Constants::$SQL_SELECT['GET_ENTITY_COUNT'];
        $sql = Constants::getSQL();
        $data = $sql->query($query);
        if (is_array($data) && sizeof($data) === 1) {
            return intval($data['cnt']);
        }
    }
    
    function getEntity_approval_changed() {
        return $this->entity_approval_changed;
    }

    function getEntity_approval_changed_ip() {
        return $this->entity_approval_changed_ip;
    }

    function setEntity_approval_changed($entity_approval_changed) {
        EntityLog::log("entity_approval_changed", $this->getEntity_approval_changed(), $entity_approval_changed);
        $this->entity_approval_changed = $entity_approval_changed;
        return $this;
    }

    function setEntity_approval_changed_ip($entity_approval_changed_ip) {
        EntityLog::log("entity_approval_changed_ip", $this->getEntity_approval_changed_ip(), $entity_approval_changed_ip);
        $this->entity_approval_changed_ip = $entity_approval_changed_ip;
        return $this;
    }

        
    public function getAverage_vote() {
        return $this->average_vote;
    }

    public function setAverage_vote($average_vote) {
        $this->average_vote = $average_vote;
        return $this;
    }

        
    public function getDeleted() {
        return $this->deleted;
    }

    public function setDeleted($deleted) {
        $this->deleted = $deleted;
        return $this;
    }
    
    public static function checkDuplicateEntity($input) {
        $sql = Constants::getSQL();
        $query = Constants::$SQL_SELECT['GET_MAP_ENTITY_STRIPPED_WHITESPACE'];
        $input = trim($input);
        $input = str_replace("\r", "", $input);
        $input = str_replace("\n", "", $input);
        $input = str_replace("\t", "", $input);
        $input = str_replace(" ", "", $input);
        $input = strtolower($input);
        $params = array($input);
        
        $data = $sql->query($query, $params);
        
        
        if (sizeof($data) > 0) {
            $data = $data[0];
            return array("entityFound" => true, "entity_id" => $data['entity_id'], "map_description" => $data['map_description'], "map_name" => $data['map_name']);
        }
        
        return array("entityFound", false);
    }

        
    public function write() {
        $sql = Constants::getSQL();
        if (intval($this->getEntity_id()) === 0) {
            //run insert
            $query = Constants::$SQL_INSERT['INSERT_ENTITY'];
            //we just push data, we dont care how it came in. Adm restriction is checked outside of the class.
            $params = array($this->getMap_name(), $this->getMap_description(), $this->getMap_entity(), $this->getImgur_links(), $this->getUploaded_by(), $this->getUploaded_by_ip(), $this->getEntity_approved(), $this->getEntity_approval_changed(), $this->getEntity_approval_changed_ip());
            $data = $sql->query($query, $params);
            return $this->setEntity_id($data['last_insert_id']);
        } else {
            //run update
            $query = Constants::$SQL_UPDATE['UPDATE_ENTITY'];
            $params = array($this->getMap_name(), $this->getMap_description(), $this->getMap_entity(), $this->getImgur_links(), $this->getEntity_approved(), $this->getDeleted(), $this->getEntity_approval_changed(), $this->getEntity_approval_changed_ip(), $this->getEntity_id());
            $sql->query($query, $params);
            return $this;
        }
    }
    
    public function delete($softDelete = true) {
        if ($softDelete) {
            return $this->setDeleted(1)->write();
        } else {
            $sql = Constants::getSQL();
            
            $query = Constants::$SQL_DELETE['DELETE_ENTITY'];
            $params = array($this->getEntity_id());
            
            $sql->query($query, $params);
            
            return null;
        }
    }

    public static function getEntityCreators($all = false) {
        $sql = Constants::getSQL();
        $query = Constants::$SQL_SELECT['GET_ENTITY_CREATOR_IDS_BY_APPROVAL_STATUS'];
        $params = array(Constants::$ENTITY_APPROVED);
        if ($all) {
            $query = Constants::$SQL_SELECT['GET_ENTITY_CREATOR_IDS'];
            $params = null;
        }
        $data = $sql->query($query, $params);
        $returnable = null;
        if (sizeof($data) > 0) {
            foreach ($data as $row) {
                $ent_count = $row['ent_count'];
                $uploaded_by = $row['uploaded_by'];
                $name = XenForo::getUsernameByID(Constants::getXenSQL(), $uploaded_by);
                $returnable[] = array("ent_count" => $ent_count, "uploaded_by" => $uploaded_by, "name" => $name);
            }
        }
        return $returnable;
        
    }
    
    public static function getEntityApprovers() {
        $sql = Constants::getSQL();
        $query = Constants::$SQL_SELECT['GET_ENTITY_APPROVER_IDS'];
        $data = $sql->query($query);
        $returnable = null;
        if (sizeof($data) > 0) {
            foreach ($data as $row) {
                $ent_count = $row['ent_count'];
                $uploaded_by = $row['entity_approval_changed'];
                $name = XenForo::getUsernameByID(Constants::getXenSQL(), $uploaded_by);
                $returnable[] = array("ent_count" => $ent_count, "uploaded_by" => $uploaded_by, "name" => $name);
            }
        }
        return $returnable;
        
    }
    
    public function getBlueTeamSpawnPoints() {
        return $this->getSpawnPointsBySpawnFlag(2);
    }
    
    public function getRedTeamSpawnPoints() {
        return $this->getSpawnPointsBySpawnFlag(1);
    }
    
	/*
	this is a horrible function but i seriously couldn't be arsed to create an object row of the whole entity, lazy af
	*/
    public function getSpawnPointsBySpawnFlag($spawnFlag) {
        $tempEntity = explode("\r\n", $this->getMap_entity());
        $isSpawnPoint = false;
        $spawnPoints = 0;
        $findableSpawnPointName = "classnamegametype_player";
        $findableSpawnFlag = "spawnflags" . intval($spawnFlag);
        foreach ($tempEntity as $tentrow) {
            $tentrow = preg_replace('/\s+/', '', $tentrow); //greedy fucker.
            $tentrow = str_replace("\"", "", $tentrow);
            $tentrow = str_replace("'", "", $tentrow);
            if ($tentrow === $findableSpawnFlag) {
                $isSpawnPoint = true;
                continue;
            }
            
            if ($isSpawnPoint) {
                $isSpawnPoint = false;
                $spawnPoints++;
            }
            
        }
        
        return $spawnPoints;
    }
    
    public static function getEntities($approvalStatus = null, $filter_map = null, $filter_creator = null, $deleted = 0, $avgvote = 0, $paginate = 30, $order = -1, $page = 0, $filterMC = 0, $excludeMC = 0, $getOnlyCount = false, $approvedBy = null) {
        if ($approvalStatus === null || intval(trim($filter_creator)) > 0) {
            $approvalStatus = Constants::$ENTITY_APPROVED;
        }
        
        $apprparam = null;
        $orderBy = null;
        
        switch (intval($order)) {
            case 1:
                $orderBy = "entity_id ASC";
                break;
            case 2:
                $orderBy = "map_name ASC";
                break;
            case 3:
                $orderBy = "map_name DESC";
                break;
            case 4:
                $orderBy = "average_vote DESC";
                break;
            case 5:
                $orderBy = "map_score DESC";
                break;
            case 6:
                $orderBy = "total_votes DESC";
                break;
            default:
                $orderBy = "entity_id DESC";
                break;
        }
        
        $limitFrom = intval($paginate) * intval($page);
        
        $limitTo = intval($paginate);
        if ($limitFrom !== 0) {
            //$limitFrom++;
            //$limitTo++;
        }
        
        if ($approvalStatus !== null || $approvalStatus !== -1) {
            $apprparam = intval($approvalStatus);
        }
        /*
        $sql = Constants::getSQL();
        $query = "";
        $params = null;
        
        
        
        if ($approvalStatus === -1) {
            //get all
            $query = Constants::$SQL_SELECT['GET_ENTITIES'];
        } else if (strlen(trim($filter_map)) > 0 && intval(trim($filter_creator)) > 0) {
            $query = Constants::$SQL_SELECT['GET_ENTITIES_BY_MAP_CREATOR'];
            $params = array($approvalStatus, "%$filter_map%", $filter_creator);
        } else if (strlen(trim($filter_map)) > 0) {
            $query = Constants::$SQL_SELECT['GET_ENTITIES_BY_MAP'];
            $params = array($approvalStatus, "%$filter_map%");
        } else if (strlen(trim($filter_creator)) > 0) {
            $query = Constants::$SQL_SELECT['GET_ENTITIES_BY_CREATOR'];
            $params = array($approvalStatus, $filter_creator);
        } else {
            $query = Constants::$SQL_SELECT['GET_ENTITIES_BY_APPROVAL_STATUS'];
            $params = array($approvalStatus);
        }
        
        $data = $sql->query($query, $params);
        $returnable = null;
        if (sizeof($data) > 0) {
            foreach ($data as $row) {
                $returnable[] = new static($row['entity_id'], $row['map_name'], $row['map_description'], $row['map_entity'], $row['imgur_links'], $row['uploaded_by'], $row['uploaded_by_ip'], $row['entity_approved'], $row['deleted'], $row['average_vote']);
            }
        }
        
        
        return $returnable;
         * 
         *  $entity_approved = null, $deleted = null, $average_vote = null, $entity_approval_changed = null, $entity_approval_changed_ip = null, $orderBy = "entity_id DESC", $limitFrom = 0, $limitTo = 30) {
    
        */
        
        return self::getEntitiesByParams($getOnlyCount, null, $filter_map, null, null, null, $filter_creator, null, $apprparam, $deleted, $avgvote, $approvedBy, null, $filterMC, $excludeMC, $orderBy, $limitFrom, $limitTo);
    }
    
    public static function generateFromSQLRow($row) {
        return new static($row['entity_id'], $row['map_name'], $row['map_description'], $row['map_entity'], $row['imgur_links'], $row['uploaded_by'], $row['uploaded_by_ip'], $row['entity_approved'], $row['deleted'], $row['average_vote'], $row['entity_approval_changed'], $row['entity_approval_changed_ip'], $row['total_votes'], $row['map_score']);
            
    }
    
    public function getEntity_id() {
        return $this->entity_id;
    }

    public function getMap_name() {
        return $this->map_name;
    }

    public function getMap_description() {
        return $this->map_description;
    }

    public function getMap_entity() {
        return $this->map_entity;
    }

    public function getImgur_links() {
        return $this->imgur_links;
    }

    public function getUploaded_by() {
        return $this->uploaded_by;
    }

    public function getUploaded_by_ip() {
        return $this->uploaded_by_ip;
    }

    public function getEntity_approved() {
        return $this->entity_approved;
    }
    
    public function isApproved() {
        return intval($this->getEntity_approved()) === Constants::$ENTITY_APPROVED;
    }
    
    public function isDeleted() {
        return int2bool($this->getDeleted());
    }

    public function setEntity_id($entity_id) {
        $this->entity_id = $entity_id;
        return $this;
    }
    
    public function populate() {
        //populate requires entity_id to be set.
        if (intval($this->getEntity_id()) === 0) {
            throw new LogicException("call populate on entity_id 0");
        }
        
        $query = Constants::$SQL_SELECT['GET_ENTITY_BY_ID'];
        $params = array($this->getEntity_id());
        $sql = Constants::getSQL();
        $data = $sql->query($query, $params);
        
        if (sizeof($data) === 1) {
            $data = $data[0];
            return $this->setEntity_approved($data['entity_approved'])
                    ->setImgur_links($data['imgur_links'])
                    ->setMap_description($data['map_description'])
                    ->setMap_entity($data['map_entity'])
                    ->setMap_name($data['map_name'])
                    ->setUploaded_by($data['uploaded_by'])
                    ->setUploaded_by_ip($data['uploaded_by_ip'])
                    ->setAverage_vote($data['average_vote'])
                    ->setEntity_approval_changed($data['entity_approval_changed'])
                    ->setEntity_approval_changed_ip($data['entity_approval_changed_ip'])
                    /*->setTotal_votes($data['total_votes'])
                    ->setMap_score($data['map_score'])*/;
        }
        return null;
        
    }

    public function setMap_name($map_name) {
        EntityLog::log("map_name", $this->map_name, $map_name);
        $this->map_name = $map_name;
        
        return $this;
    }

    public function setMap_description($map_description) {
        EntityLog::log("map_description", $this->map_description, $map_description);
        $this->map_description = $map_description;
        return $this;
    }

    public function setMap_entity($map_entity) {
        EntityLog::log("map_entity", $this->map_entity, $map_entity);
        $this->map_entity = $map_entity;
        return $this;
    }

    public function setImgur_links($imgur_links) {
        EntityLog::log("imgur_links", $this->imgur_links, $imgur_links);
        $this->imgur_links = $imgur_links;
        return $this;
    }

    public function setUploaded_by($uploaded_by) {
        EntityLog::log("uploaded_by", $this->uploaded_by, $uploaded_by);
        $this->uploaded_by = $uploaded_by;
        return $this;
    }

    public function setUploaded_by_ip($uploaded_by_ip) {
        EntityLog::log("uploaded_by_ip", $this->uploaded_by_ip, $uploaded_by_ip);
        $this->uploaded_by_ip = $uploaded_by_ip;
        return $this;
    }

    public function setEntity_approved($entity_approved) {
        EntityLog::log("entity_approved", $this->entity_approved, $entity_approved);
        $this->entity_approved = $entity_approved;
        return $this;
    }

    
    
    
}

class EntityVote {
    

    private $ent_vote_id;
    private $entity_id;
    private $ent_voter;
    private $ent_voter_ip;
    private $vote;
    
    public function __construct($ent_vote_id = null, $entity_id = null, $ent_voter = null, $ent_voter_ip = null, $vote = null) {
        $this->ent_vote_id = $ent_vote_id;
        $this->entity_id = $entity_id;
        $this->ent_voter = $ent_voter;
        $this->ent_voter_ip = $ent_voter_ip;
        $this->vote = $vote;
    }
    
    public function getEnt_vote_id() {
        return $this->ent_vote_id;
    }

    public function getEntity_id() {
        return $this->entity_id;
    }

    public function getEnt_voter() {
        return $this->ent_voter;
    }

    public function getEnt_voter_ip() {
        return $this->ent_voter_ip;
    }

    public function getVote() {
        return $this->vote;
    }

    public function setEnt_vote_id($ent_vote_id) {
        $this->ent_vote_id = $ent_vote_id;
        return $this;
    }

    public function setEntity_id($entity_id) {
        $this->entity_id = $entity_id;
        return $this;
    }

    public function setEnt_voter($ent_voter) {
        $this->ent_voter = $ent_voter;
        return $this;
    }

    public function setEnt_voter_ip($ent_voter_ip) {
        $this->ent_voter_ip = $ent_voter_ip;
        return $this;
    }

    public function setVote($vote) {
        $this->vote = $vote;
        return $this;
    }


    public function write() {
        $sql = Constants::getSQL();
        if (intval($this->getEnt_vote_id()) === 0) {
            //run insert
            $query = Constants::$SQL_INSERT['INSERT_ENTITY_VOTE'];
            $params = array($this->getEntity_id(), $this->getEnt_voter(), $this->getEnt_voter_ip(), $this->getVote());
            $data = $sql->query($query, $params);
            return $this->setEnt_vote_id($data['last_insert_id']);
        } else {
            //run update
            $query = Constants::$SQL_UPDATE['UPDATE_ENTITY_VOTE'];
            $params = array($this->getEnt_voter_ip(), $this->getVote(), $this->getEnt_vote_id());
            $sql->query($query, $params);
            return $this;
        }
    }
    
    public function remove() {
        $sql = Constants::getSQL();
        $query = Constants::$SQL_DELETE['DELETE_ENTITY_VOTE'];
        $params = array($this->getEnt_vote_id());
        
        return $sql->query($query, $params);
    }
    
    public function populate($nullOnEmpty = true) {
        //need one of 2 - either the actual PK or entity ID and voter.
        $query = "";
        $params = null;
        if (intval($this->getEnt_vote_id()) === 0) {
            if (intval($this->getEntity_id()) === 0) {
                throw new InvalidArgumentException("Can't populate without ent_vote_id and entity_id");
            } else {
                $query = Constants::$SQL_SELECT['GET_VOTES_BY_ENTITY_ID_VOTER'];
                $voter_id = intval($this->getEnt_voter());
                if ($voter_id === 0) {
                    $voter_id = intval($_SESSION['member_id']);
                    if ($voter_id === 0) {
                        throw new InvalidArgumentException("cant get member info");
                    }
                }
                $params = array($this->getEntity_id(), $voter_id);
            }
        } else {
            $query = Constants::$SQL_SELECT['GET_VOTES_BY_VOTE_ID'];
            $params = array($this->getEnt_vote_id());
        }
        
        $sql = Constants::getSQL();
        $data = $sql->query($query, $params);
        
        if (sizeof($data) === 1) {
            $data = $data[0];
            return $this->setEnt_vote_id($data['ent_vote_id'])
                    ->setEnt_voter($data['ent_voter'])
                    ->setEnt_voter_ip($data['ent_voter_ip'])
                    ->setEntity_id($data['entity_id'])
                    ->setVote($data['vote']);
        } else if ($nullOnEmpty) {
            return null;
        }
        return $this;
        
        
        
    }
    
    public static function getEntityVotesByID($ent_voter) {
        $sql = Constants::getSQL();
        $query = Constants::$SQL_SELECT['GET_ALL_ENT_VOTES_BY_VOTER_ID'];
        $params = array($ent_voter);
        $data = $sql->query($query, $params);
        $returnable = null;
        if (sizeof($data) > 0) {
            foreach ($data as $row) {
                $returnable[] = new static($row['ent_vote_id'], $row['entity_id'], $row['ent_voter'], $row['ent_voter_ip'], $row['vote']);
            }
        }
        return $returnable;
    }
    
    public static function getEntityVotesByEntityID($entity_id) {
        $sql = Constants::getSQL();
        $query = Constants::$SQL_SELECT['GET_ALL_ENT_VOTES_BY_ENTITY_ID'];
        $params = array($entity_id);
        $data = $sql->query($query, $params);
        $returnable = null;
        if (sizeof($data) > 0) {
            foreach ($data as $row) {
                $returnable[] = new static($row['ent_vote_id'], $row['entity_id'], $row['ent_voter'], $row['ent_voter_ip'], $row['vote']);
            }
        }
        return $returnable;
    }
    
    

    
}

class EntityLog {

    private $ent_log_id;
    private $triggered_by;
    private $triggered_by_ip;
    private $change_val;
    private $oldval;
    private $newval;
    
    public function __construct($ent_log_id = null, $triggered_by = null, $triggered_by_ip = null, $change_val = null, $oldval = null, $newval = null) {
        $this->ent_log_id = $ent_log_id;
        $this->triggered_by = $triggered_by;
        $this->triggered_by_ip = $triggered_by_ip;
        $this->change_val = $change_val;
        $this->oldval = $oldval;
        $this->newval = $newval;
    }
    
    public function getTriggered_by_ip() {
        return $this->triggered_by_ip;
    }

    public function setTriggered_by_ip($triggered_by_ip) {
        $this->triggered_by_ip = $triggered_by_ip;
        return $this;
    }

        
    public function getEnt_log_id() {
        return $this->ent_log_id;
    }

    public function getTriggered_by() {
        return $this->triggered_by;
    }

    public function getChange_val() {
        return $this->change_val;
    }

    public function getOldval() {
        return $this->oldval;
    }

    public function getNewval() {
        return $this->newval;
    }

    public function setEnt_log_id($ent_log_id) {
        $this->ent_log_id = $ent_log_id;
        return $this;
    }

    public function setTriggered_by($triggered_by) {
        $this->triggered_by = $triggered_by;
        return $this;
    }

    public function setChange_val($change_val) {
        $this->change_val = $change_val;
        return $this;
    }

    public function setOldval($oldval) {
        $this->oldval = $oldval;
        return $this;
    }

    public function setNewval($newval) {
        $this->newval = $newval;
        return $this;
    }


    public function writeLog() {
        if ($this->getEnt_log_id !== null || intval($this->getEnt_log_id()) !== 0) {
            throw new InvalidArgumentException("cannot update logs.");
        }
        
        $sql = Constants::getSQL();
        $query = Constants::$SQL_INSERT['INSERT_MAP_ENTITY_LOG'];
        $params = array($this->getTriggered_by(), $this->getTriggered_by_ip(), $this->getChange_val(), $this->getOldval(), $this->getNewval());
        $data = $sql->query($query, $params);
        return $this->setEnt_log_id($data['last_insert_id']);
    }
    
    public static function log($change_val, $oldval, $newval, $triggered_by = null, $triggered_by_ip = null) {
        if ($triggered_by === null) {
            $triggered_by = $_SESSION['member_id'];
        }
        
        if ($triggered_by_ip === null) {
            $triggered_by_ip = getUserIP();
        }
        
        if ($oldval === null) {
            return;
        }
        
        if (trim($oldval) === trim($newval)) {
            return;
        }
        
        
        
        $log = new static(null, $triggered_by, $triggered_by_ip, $change_val, $oldval, $newval);
        $log->writeLog();
    }

}
