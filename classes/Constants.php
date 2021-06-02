<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Constants
 *
 * @author jesko
 */
class Constants {
    
    static $MAX_IMAGE_SIZE = 7000000;
    static $IMGUR_SECRET = "";
    static $IMGUR_CLID = "";
    static $TOOL_NAME = "maps";
    static $TOOL_VERSION = "0.01";
    static $TOOL_BRANCH = "dev";
    static $DEBUG = true;
    static $PAGE_TEMPLATE = "boilerplate.html";
    static $PAGE_TEMPLATE_HIDDEN_SIDEBAR = "boilerplate_hiddensidebar.html";
    static $PAGE_URL = "https://maps.3d-sof2.com";
    static $PAGE_BASE_PATH = "";
    static $IPHUB_API_KEY = "";
    static $IP_FILTER_FAILS_THREAD_ID = 7;
    static $MAPS_NOACCESS = -1;
    static $MAPS_NORMALUSER = 0;
    static $MAPS_EDITOR = 1;
    static $MAPS_ADMIN = 2;
    static $ENTITY_BRACKET_COUNT_THRESHOLD = 10;
    static $ENTITY_NOT_APPROVED = 0;
    static $ENTITY_APPROVED = 1;
    static $ENTITY_REJECTED = 2;
    static $MC_FILE_MAXSIZE = 16000;
    static $FILE_PATH_MC = "1fx/files";
    //static $FILE_PATH_ALTENT = "1fx/maps/alt";
    static $FILE_PATH_ENTGT = "1fx/maps/{GAMETYPE}";
    
    static $PROD_SERVER_ID = 2;
    static $PREPROD_SERVER_ID = 7;
    
    static $SQL_HOST = "";
    static $SQL_USER = "";
    static $SQL_PASS = "";
    static $SQL_DB = "";
    private static $SQLHANDLE = null;
    
    static $XEN_HOST = "";
    static $XEN_USER = "";
    static $XEN_PASS = "";
    static $XEN_DB = "";
    private static $XENHANDLE = null;
    
    private static $SP_MC_API_USER = "";
    private static $SP_MC_API_PW = "";
    private static $SP_MC_API_URL = "";
    
    
    public static function getMCApiUser() {
        return self::$SP_MC_API_USER;
    }
    
    public static function getMCApiPassword() {
        return self::$SP_MC_API_PW;
    }
    
    public static function getMCApiURL() {
        return self::$SP_MC_API_URL;
    }
    
    public static $GAMETYPES = array(
        "h&z"
        , "h&s"
        , "dm"
        , "tdm"
        , "elim"
        , "inf"
        , "ctf"
    );
    
    
    /**
     * 
     * @return SQL application SQL handle
     */
    public static function getSQL() {
        if (self::$SQLHANDLE !== null && self::$SQLHANDLE instanceof SQL) {
            return self::$SQLHANDLE;
        }
        self::$SQLHANDLE = new SQL(self::$SQL_HOST, self::$SQL_USER, self::$SQL_PASS, self::$SQL_DB);
        return self::$SQLHANDLE;
    }
    
    /**
     * 
     * @return SQL forum SQL handle
     */
    public static function getXenSQL() {
        if (self::$XENHANDLE !== null && self::$XENHANDLE instanceof SQL) {
            return self::$XENHANDLE;
        }
        self::$XENHANDLE = new SQL(self::$XEN_HOST, self::$XEN_USER, self::$XEN_PASS, self::$XEN_DB);
        return self::$XENHANDLE;
    }
    
    static $DB_TYPES = array(
        0 => "mySQL"
        , 1 => "MSSQL"
        , 2 => "DB2"
    );
    
    static $SQL_SELECT = array(
        "GET_ACCESSLEVEL" => "SELECT * FROM maps_admins WHERE group_id = ?"
        , "GET_ENTITY_BY_ID" => "SELECT me.*, COALESCE(AVG(mev.vote), 0) AS average_vote FROM maps_entities me LEFT JOIN maps_entities_votes mev ON (mev.entity_id = me.entity_id) WHERE me.entity_id  = ? GROUP BY me.entity_id"
        , "GET_ENTITY_CREATOR_IDS" => "SELECT COUNT(*) AS ent_count, uploaded_by FROM maps_entities GROUP BY uploaded_by ORDER BY ent_count DESC"
        , "GET_ENTITY_CREATOR_IDS_BY_APPROVAL_STATUS" => "SELECT COUNT(*) AS ent_count, uploaded_by FROM maps_entities WHERE entity_approved = ? AND COALESCE(deleted, 0) = 0 GROUP BY uploaded_by ORDER BY ent_count DESC"
        , "GET_ENTITIES" => "SELECT me.*, COALESCE(AVG(mev.vote), 0) AS average_vote FROM maps_entities me LEFT JOIN maps_entities_votes mev ON (mev.entity_id = me.entity_id)  GROUP BY me.entity_id"
        , "GET_ENTITIES_BY_APPROVAL_STATUS" => "SELECT me.*, COALESCE(AVG(mev.vote), 0) AS average_vote FROM maps_entities me LEFT JOIN maps_entities_votes mev ON (mev.entity_id = me.entity_id) WHERE me.entity_approved = ? GROUP BY me.entity_id"
        , "GET_ENTITIES_BY_MAP_CREATOR" => "SELECT me.*, COALESCE(AVG(mev.vote), 0) AS average_vote FROM maps_entities me LEFT JOIN maps_entities_votes mev ON (mev.entity_id = me.entity_id) WHERE me.entity_approved = ? AND me.map_name LIKE ? AND me.uploaded_by = ? GROUP BY me.entity_id "
        , "GET_ENTITIES_BY_MAP" => "SELECT me.*, COALESCE(AVG(mev.vote), 0) AS average_vote FROM maps_entities me LEFT JOIN maps_entities_votes mev ON (mev.entity_id = me.entity_id) WHERE me.entity_approved = ? AND me.map_name LIKE ? GROUP BY me.entity_id "
        , "GET_ENTITIES_BY_CREATOR" => "SELECT me.*, COALESCE(AVG(mev.vote), 0) AS average_vote FROM maps_entities me LEFT JOIN maps_entities_votes mev ON (mev.entity_id = me.entity_id) WHERE me.entity_approved = ? AND me.uploaded_by = ? GROUP BY me.entity_id "
        , "GET_ALL_ENT_VOTES_BY_VOTER_ID" => "SELECT * FROM maps_entities_votes WHERE ent_voter = ?"
        , "GET_ALL_ENT_VOTES_BY_ENTITY_ID" => "SELECT * FROM maps_entities_votes WHERE entity_id = ?"
        , "GET_VOTES_BY_ENTITY_ID_VOTER" => "SELECT * FROM maps_entities_votes WHERE entity_id = ? AND ent_voter = ?"
        , "GET_VOTES_BY_VOTE_ID" => "SELECT * FROM maps_entities_votes WHERE ent_vote_id = ?"
        , "GET_MAPCYCLES" => "SELECT * FROM maps_mapcycle"
        , "GET_MAPCYCLE_BY_ID" => "SELECT * FROM maps_mapcycle WHERE mapcycle_id = ?"
        , "GET_MAPCYCLES_BY_CREATOR_ID" => "SELECT * FROM maps_mapcycle WHERE mapcycle_creator_user_id = ?"
        , "GET_MAPCYCLES_BY_STATUS" => "SELECT * FROM maps_mapcycle WHERE mapcycle_status = ?"
        , "GET_MAPCYCLES_BY_CREATOR_ID_STATUS" => "SELECT * FROM maps_mapcycle WHERE mapcycle_creator_user_id = ? AND mapcycle_status = ?"
        , "GET_ENTITYMAPS_BY_ENTITY_ID" => "SELECT * FROM maps_entitymap WHERE entity_id = ?"
        , "GET_ENTITYMAPS_BY_MAPCYCLE_ID" => "SELECT * FROM maps_entitymap WHERE mapcycle_id = ?"
        , "GET_ENTITYMAPS_BY_MAPCYCLE_ID_ORDER" => "SELECT * FROM maps_entitymap WHERE mapcycle_id = ? ORDER BY ISNULL(map_order), map_order ASC, entitymap_id ASC"
        , "GET_ENTITYMAP_BY_ID" => "SELECT * FROM maps_entitymap WHERE entitymap_id = ?"
        , "GET_ENTITYMAP_BY_ENTITY_ID_MAPCYCLE_ID" => "SELECT * FROM maps_entitymap WHERE entity_id = ? AND mapcycle_id = ?"
        , "GET_CVARS" => "SELECT * FROM maps_mapcycle_cvars"
        , "GET_CVAR_BY_ID" => "SELECT * FROM maps_mapcycle_cvars WHERE cvar_id = ?"
        , "GET_CVARS_BY_CVAR_NAME_VALUE" => "SELECT * FROM maps_mapcycle_cvars WHERE cvar_name = ? AND cvar_value = ?"
        , "GET_SINGLE_CVAR_BY_CVAR_NAME" => "SELECT * FROM maps_mapcycle_cvars WHERE cvar_name = ? LIMIT 1"
        , "GET_CVARS_MAP_BY_CVAR_ID_ENTITYMAP_ID" => "SELECT * FROM maps_mapcycle_cvars_map WHERE entitymap_id = ? AND cvar_id = ?"
        , "GET_CVARS_MAP_BY_ENTITYMAP_ID" => "SELECT * FROM maps_mapcycle_cvars_map WHERE entitymap_id = ?"
        , "GET_CVARS_MAP_BY_ENTITYMAP_ID_ORDER_MOTD" => "SELECT mmcm.* FROM maps_mapcycle_cvars_map mmcm INNER JOIN maps_mapcycle_cvars mmc ON (mmc.cvar_id = mmcm.cvar_id) WHERE mmcm.entitymap_id = ? ORDER BY (CASE WHEN (mmc.cvar_name = 'g_motd') THEN 0 ELSE 1 END) ASC, mmcm.cvar_map_id ASC"
        , "GET_CVARS_MAP_BY_ID" => "SELECT * FROM maps_mapcycle_cvars_map WHERE cvar_map_id = ?"
        , "GET_MAPPED_CVARS_BY_ENTITY_MAPCYCLE" => "SELECT mmc.* FROM maps_mapcycle_cvars mmc INNER JOIN maps_mapcycle_cvars_map mmcm ON (mmcm.cvar_id = mmc.cvar_id AND mmcm.entity_id = ? AND mmcm.mapcycle_id = ?)"
        , "GET_CURRENT_LIVE" => "SELECT * FROM maps_mapcycle_pushtolive WHERE live = 1"
        , "GET_CURRENT_LIVE_CANDIDATE" => "SELECT * FROM maps_mapcycle_pushtolive WHERE live_from <= CURRENT_TIMESTAMP AND live_to >= CURRENT_TIMESTAMP AND deleted = 0"
        , "GET_PUSHTOLIVE_BY_ID" => "SELECT * FROM maps_mapcycle_pushtolive WHERE pushtolive_id = ?"
        , "GET_PUSHTOLIVE_BY_USER" => "SELECT * FROM maps_mapcycle_pushtolive WHERE push_created_by = ? ORDER BY live_from ASC"
        , "GET_PUSHTOLIVE_BY_MAPCYCLE" => "SELECT * FROM maps_mapcycle_pushtolive WHERE mapcycle_id = ? ORDER BY live_from ASC"
        , "GET_PUSHTOLIVE_BY_MAPCYCLE_ACTIVE" => "SELECT * FROM maps_mapcycle_pushtolive WHERE mapcycle_id = ? AND deleted = 0 AND live_to >= CURRENT_TIMESTAMP ORDER BY live_from ASC"
        , "GET_PUSHTOLIVE_BY_DATES_LF_LT" => "SELECT * FROM maps_mapcycle_pushtolive WHERE live_from <= ? AND live_to >= ? ORDER BY live_from ASC"
        , "CHECK_PUSHTOLIVE_COLLISION" => "SELECT * FROM maps_mapcycle_pushtolive WHERE ((live_from <= ? AND live_to >= ?) OR (live_from <= ? AND live_to >= ?)) AND deleted = 0 "
        , "CHECK_PUSHTOLIVE_COLLISION_EXCL_PTL" => "SELECT * FROM maps_mapcycle_pushtolive WHERE pushtolive_id != ? AND ((live_from <= ? AND live_to >= ?) OR (live_from <= ? AND live_to >= ?)) AND deleted = 0 "
        , "GET_PUSHTOLIVE_NOT_DELETED" => "SELECT * FROM maps_mapcycle_pushtolive WHERE deleted = 0 ORDER BY live_from ASC"
        , "GET_PUSHTOLIVE_ALL" => "SELECT * FROM maps_mapcycle_pushtolive ORDER BY live_from ASC"
        , "GET_PUSHTOLIVE_OUTLOOK" => "SELECT * FROM maps_mapcycle_pushtolive WHERE deleted = 0 AND live_to >= CURRENT_TIMESTAMP ORDER BY live_from ASC"
        , "CHECK_ENTITYMAP_COLLISION" => "SELECT entmap.*, ent.map_name FROM maps_entitymap entmap INNER JOIN maps_entities ent ON (ent.entity_id = entmap.entity_id AND ent.entity_id != ?) WHERE entmap.mapcycle_id = ? AND ent.map_name = ? AND entmap.altmap = ? AND entmap.gametype = ? "
        , "GET_DEFAULT_CVARS" => "SELECT * FROM maps_mapcycle_cvars WHERE isdefault = 1"
        , "GET_ENTITY_COUNT" => "SELECT COUNT(*) AS cnt FROM maps_entities WHERE deleted = 0"
        , "GET_ENTITY_APPROVER_IDS" => "SELECT entity_approval_changed, COUNT(entity_id) AS ent_count FROM maps_entities WHERE entity_approval_changed IS NOT NULL AND entity_approved = 1 AND COALESCE(deleted, 0) = 0 GROUP BY entity_approval_changed ORDER BY ent_count DESC"
        , "GET_MAP_ENTITY_STRIPPED_WHITESPACE" => "SELECT entity_id, map_description, map_name FROM maps_entities WHERE LOWER(REPLACE(REPLACE(REPLACE(TRIM(map_entity), ' ', ''), '\r\n', ''), '\t', '')) = ?"
    );
    
    static $SQL_INSERT = array(
        "INSERT_MAP_ENTITY_LOG" => "INSERT INTO maps_entities_log (triggered_by, triggered_by_ip, change_val, oldval, newval) VALUES (?, ?, ?, ?, ?)"
        , "INSERT_ENTITY_VOTE" => "INSERT INTO maps_entities_votes (entity_id, ent_voter, ent_voter_ip, vote) VALUES (?, ?, ?, ?)"
        , "INSERT_ENTITY" => "INSERT INTO maps_entities (map_name, map_description, map_entity, imgur_links, uploaded_by, uploaded_by_ip, entity_approved, entity_approval_changed, entity_approval_changed_ip) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        , "INSERT_MAPCYCLE" => "INSERT INTO maps_mapcycle (mapcycle_description, mapcycle_creator_user_id, mapcycle_creator_ip, mapcycle_status, mapcycle_status_change_by, mapcycle_status_change_by_ip) VALUES (?, ?, ?, ?, ?, ?)"
        , "INSERT_ENTITYMAP" => "INSERT INTO maps_entitymap (mapcycle_id, entity_id, added_by, added_by_ip, map_order, gametype, altmap) VALUES (?, ?, ?, ?, ?, ?, ?)"
        , "INSERT_CVAR_MAP" => "INSERT INTO maps_mapcycle_cvars_map (cvar_id, entitymap_id) VALUES (?, ?)"
        , "INSERT_PUSHTOLIVE" => "INSERT INTO maps_mapcycle_pushtolive (push_created_by, push_created_by_ip, mapcycle_id, live_from, live_to) VALUES (?, ?, ?, ?, ?)"
        , "INSERT_CVAR" => "INSERT INTO maps_mapcycle_cvars (cvar_name, cvar_value, cvar_friendly_name) VALUES (?, ?, ?)"
        , "INSERT_PARSED_STAT" => "INSERT INTO maps_statistics (stat_dt, entity_id, map_id, mapcycle_id, gametype, clients) VALUES (?, ?, ?, ?, ?, ?)"
        );
    
    static $SQL_UPDATE = array(
        "UPDATE_ENTITY_VOTE" => "UPDATE maps_entities_votes SET ent_voter_ip = ?, vote = ? WHERE ent_vote_id = ?"
        , "UPDATE_ENTITY" => "UPDATE maps_entities SET map_name = ?, map_description = ?, map_entity = ?, imgur_links = ?, entity_approved = ?, deleted = ?, entity_approval_changed = ?, entity_approval_changed_ip = ? WHERE entity_id = ?"
        , "UPDATE_MAPCYCLE" => "UPDATE maps_mapcycle SET mapcycle_description = ?, mapcycle_status = ?, mapcycle_status_change_by = ?, mapcycle_status_change_by_ip = ? WHERE mapcycle_id = ?"
        , "UPDATE_ENTITYMAP" => "UPDATE maps_entitymap SET mapcycle_id = ?, entity_id = ?, map_order = ?, gametype = ?, altmap = ? WHERE entitymap_id = ?"
        , "UPDATE_ENTITYMAP_BY_MC_ENT" => "UPDATE maps_entitymap SET map_order = ?, gametype = ?, altmap = ? WHERE mapcycle_id = ? AND entity_id = ?"
        , "UPDATE_CVAR_MAP" => "UPDATE maps_mapcycle_cvars_map SET entitymap_id = ?, cvar_id = ? WHERE cvar_map_id = ?"
        , "UPDATE_PUSHTOLIVE" => "UPDATE maps_mapcycle_pushtolive SET live_from = ?, live_to = ?, deleted = ?, live = ? WHERE pushtolive_id = ?"
        , "UPDATE_ORDER_BY_ENTITYMAP_ID" => "UPDATE maps_entitymap SET map_order = ? WHERE entitymap_id = ?"
        
        );
    
    static $SQL_DELETE = array(
        "DELETE_ENTITY_VOTE" => "DELETE from maps_entities_votes WHERE ent_vote_id = ?"
        , "DELETE_ENTITY" => "DELETE FROM maps_entities WHERE entity_id = ?"
        , "DELETE_MAPCYCLE" => "DELETE FROM maps_mapcycle WHERE mapcycle_id = ?"
        , "DELETE_ENTITYMAP" => "DELETE FROM maps_entitymap WHERE entitymap_id = ?"
        , "DELETE_ENTITYMAP_BY_MC" => "DELETE FROM maps_entitymap WHERE mapcycle_id = ?"
        , "DELETE_ENTITYMAP_BY_ENTITY" => "DELETE FROM maps_entitymap WHERE entity_id = ?"
        , "DELETE_CVAR_MAP" => "DELETE FROM maps_mapcycle_cvars_map WHERE cvar_map_id = ?"
        , "DELETE_CVAR_MAP_BY_ENTITYMAP_ID" => "DELETE FROM maps_mapcycle_cvars_map WHERE entitymap_id = ?"
        , "DELETE_PUSHTOLIVE" => "DELETE FROM maps_mapcycle_pushtolive WHERE pushtolive_id = ?"
        , "DELETE_PUSHTOLIVE_BY_MC" => "DELETE FROM maps_mapcycle_pushtolive WHERE mapcycle_id = ?"
        );
    
    private static $CSS = <<<EOT
        <link rel="shortcut icon" href="{}/static/favicon.ico" type="image/x-icon">
        <link rel="icon" href="{}/static/favicon.ico" type="image/x-icon">
        <link rel="stylesheet" href="{}/static/css/bootstrap.min.css">
        <link rel="stylesheet" href="{}/static/css/bootstrap-grid.min.css">
        <link rel="stylesheet" href="{}/static/css/bootstrap-reboot.min.css">
        <link rel="stylesheet" href="{}/static/css/shards.min.css">
        <link rel="stylesheet" href="{}/static/css/toastr.min.css">
        <link rel="stylesheet" href="{}/static/css/jquery-ui.min.css">
        <link rel="stylesheet" href="{}/static/css/jquery-ui.structure.min.css">
        <link rel="stylesheet" href="{}/static/css/jquery-ui.theme.min.css">
        <link rel="stylesheet" href="{}/static/css/fontawesome-all.min.css">
        <link rel="stylesheet" href="{}/static/DataTables/datatables.min.css">
        <link rel="stylesheet" href="{}/static/DataTables/excfilter/excel-bootstrap-table-filter-style.css">
        <link rel="stylesheet" href="{}/static/contextMenu/jquery.contextMenu.min.css">
            <link rel="stylesheet" href="{}/static/css/select2.min.css">
            <link rel="stylesheet" href="{}/static/css/bsdt.css">
        <link rel="stylesheet" href="{}/static/css/app.css?12saaaa222aaaaa">
EOT;
    
    private static $JS = <<<EOT
        <script src="{}/static/js/jquery.min.js"></script>
            <script src="{}/static/js/jquery-ui.min.js"></script>
        <script src="{}/static/js/bootstrap.bundle.min.js"></script>
        <script src="{}/static/js/popper.min.js"></script>
        <script src="{}/static/js/clipboard.min.js"></script>
        
        
        <script src="{}/static/js/toastr.min.js"></script>
        <script src="{}/static/js/shards.min.js"></script>
        <script src="{}/static/js/fontawesome-all.min.js"></script>
            <script src="{}/static/js/tooltip.min.js"></script>
        <script src="{}/static/DataTables/datatables.min.js"></script>
        <script src="{}/static/DataTables/excfilter/excel-bootstrap-table-filter-bundle.min.js"></script>
            <script src="{}/static/js/jquery.doubleScroll.js"></script>
        <script src="{}/static/contextMenu/jquery.ui.position.min.js"></script>
        <script src="{}/static/contextMenu/jquery.contextMenu.min.js"></script>
        <script src="{}/static/js/clipboard.min.js"></script>
        <script src="{}/static/js/prettyfile.js?2a"></script>
        <script src="{}/static/js/starRating.js"></script>
            <script src="{}/static/js/jq_dnd.js?2"></script>
            <script src="{}/static/js/select2.full.min.js"></script>
            <script src="{}/static/js/moment.js"></script>
            <script src="{}/static/js/bsdt.js?a2"></script>
            <script src="{}/static/js/jquery.canvasjs.min.js?1"></script>
        <script src="{}/static/js/app.js?13s222"></script>
EOT;
    
    public static function getCSS() {
        return str_replace("{}", self::$PAGE_URL, self::$CSS);
    }
    
    public static function getJS() {
        return str_replace("{}", self::$PAGE_URL, self::$JS);
    }
}
