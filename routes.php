<?php
//always check ip.
User::checkIP(getUserIP());
$error = false;
$errorstr = "";
$success = false;
$succstr = "";

$loginOut = false;

if (isset($_POST['logout'])) {
    session_destroy();
    $_SESSION = array();
    session_start();
    $loginOut = true;
}

$user = new User();
if (isset($_SESSION['member_id'])) {
    
    $_SESSION = array_merge($user->setMember_id(intval($_SESSION['member_id']))->populate()->populateAccess()->toArray(), $_SESSION);
}

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $user = $user->externalAuthentication($username, $password);
    if ($user === null) {
        $error = true;
        $errorstr = "Login failed.";
        $user = new User();
    } else {
        $_SESSION = $user->externalAuthentication($username, $password)->populateAccess()->toArray();
    }
    //$loginOut = true;
    
}

$router = new Router();
$router->setBasePath(Constants::$PAGE_BASE_PATH);



function newEntity() {
    global $error, $errorstr, $success, $succstr, $user, $loginOut;
    //rules - at least 3 files.
    if (!$loginOut && isset($_POST['uploadent']) && intval($_POST['uploadent']) === 1) {
        if ($user->isLoggedIn()) {

            $imgopt = __DIR__ . "/classes/ImageOptimizer";
            require_once $imgopt . "/autoload.php";
            
            $fileCount = sizeof($_FILES['imgs']['name']);
            if ($fileCount >= 1) {
                //final one, check entity, check count of { and count of } characters, it's a cheap shot but could work.
                $map_entity = trim($_POST['map_entity']);

                if (substr_count($map_entity, "{") !== substr_count($map_entity, "}") || substr_count($map_entity, "{") < Constants::$ENTITY_BRACKET_COUNT_THRESHOLD || substr_count($map_entity, "}") < Constants::$ENTITY_BRACKET_COUNT_THRESHOLD) {
                    $error = true;
                    $errorstr = "Entity file either has not matching bracket count or is too small, so not considered to be an entity.";
                    $analyze = openCloseAnalyzer($map_entity);
                    
                    if (is_array($analyze) && sizeof($analyze) > 0) {
                        $errorstr .= "<br>Possible problems:";
                        foreach ($analyze as $anl) {
                            $errorstr .= "<br>Line: " . $anl['line'] . " (saw character <b>" . $anl['seenChar'] . "</b> , expecting <b>" . $anl['expectChar'] . "</b> ) ";
                        }
                    }
                } else {
                    
                    //duplicate check
                    $dupliCheck = Entity::checkDuplicateEntity($_POST['map_entity']);
                    
                    if ($dupliCheck['entityFound']) {
                        $error = true;
                        $errorstr = "Found at least one map which has the same entity contents as your uploaded one. ID " . $dupliCheck['entity_id'] . " (map name: " . $dupliCheck['map_name'] . "map description: " . $dupliCheck['map_description'] . ")";
                    } else {   

                        //first check - is it an image.

                        $imgs = $_FILES['imgs'];
                        for ($i = 0; $i < $fileCount; $i++) {


                            $check = getimagesize($imgs['tmp_name'][$i]);
                            $fileName = basename($imgs['name'][$i]);
                            $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                            if ($check === false) {
                                $error = true;
                                $errorstr = "One of the uploaded files was not recognized to be an image!";
                                break;
                            } 

                            if (intval($imgs['size'][$i]) > Constants::$MAX_IMAGE_SIZE) {
                                $error = true;
                                $errorstr = "One of the uploaded images exceeded the image upload size (~6MB).";
                                break;
                            }

                            if ($fileType !== "jpg" && $fileType !== "gif" && $fileType !== "png") {
                                $error = true;
                                $errorstr = "Only jpg, gif and png's are allowed!";
                                break;
                            }
                        }

                        if (!$error) {
                            //means we're good for upload.
                            $links = array();
                            for ($i = 0; $i < $fileCount; $i++) {
                                $fileName = basename($imgs['name'][$i]);
                                $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                                $uniqid = uniqid($user->getName() . "_" . htmlentities($_POST['map_name']), true);
                                $uploadTo = __DIR__ . "/uploads/" . $uniqid . ".$fileType";
                                $uploadToLink = Constants::$PAGE_URL . "/uploads/$uniqid.$fileType"; 
                                $uploadToSmall = __DIR__ . "/uploads/" . $uniqid . "s.$fileType";
                                move_uploaded_file($imgs['tmp_name'][$i], $uploadTo);
                                //optimize it.
                                $factory = new \ImageOptimizer\OptimizerFactory();
                                $optimizer = $factory->get();

                                $optimizer->optimize($uploadTo); //overwrites file...
                                $imageObj = imagecreatefromstring(file_get_contents($uploadTo));
                                $smallImage = imagescale($imageObj, 90, 90);

                                if ($fileType === "jpg") {
                                    imagejpeg($smallImage, $uploadToSmall);
                                } else if ($fileType === "png") {
                                    imagepng($smallImage, $uploadToSmall);
                                } else {
                                    imagegif($smallImage, $uploadToSmall);
                                }
                                $links[] = $uploadToLink;
                            }

                            $entity = new Entity();
                            $entity_approved = 0;
                            if ($user->getCan_approve_ents()) {
                                $entity_approved = intval(trim($_POST['entity_approved']));
                                if ($entity_approved === Constants::$ENTITY_APPROVED || $entity_approved === Constants::$ENTITY_REJECTED) {
                                    $entity->setEntity_approval_changed($user->getMember_id());
                                    $entity->setEntity_approval_changed_ip(getUserIP());
                                }
                            }
                            $entity_sts_text = "";

                            if ($entity_approved === Constants::$ENTITY_NOT_APPROVED) {
                                $entity_sts_text = "Your entity is awaiting approval.";
                            }

                            $entity
                                    ->setEntity_approved($entity_approved)
                                    ->setImgur_links(serialize($links))
                                    ->setMap_description(trim(htmlentities($_POST['map_description'])))
                                    ->setMap_entity(trim($_POST['map_entity']))
                                    ->setMap_name(trim(htmlentities($_POST['map_name'])))
                                    ->setUploaded_by($user->getMember_id())
                                    ->setUploaded_by_ip(getUserIP())
                                    ->write();
                            $success = true;
                            $succstr = "Entity has been saved. $entity_sts_text";
                        }

                        /*
                        $imgur = new Imgur(Constants::$IMGUR_CLID, $_FILES, "imgs");
                        $data = $imgur->send();
                        if (sizeof($data) !== $fileCount) {
                            $error = true;
                            $errorstr = "Image upload to Imgur failed (either you supplied something which you shouldn't have or there was just an interruption). Please try again.";
                        } else {
                            $links = null;
                            foreach ($data as $row) {
                                $links[] = $row['data']['link'];
                                if (!(array_key_exists("success", $row)) || intval($row['success']) !== 1) {
                                    $error = true;
                                    $errorstr = "Image upload to Imgur failed (either you supplied something which you shouldn't have or there was just an interruption). Please try again.";
                                }
                            }
                            if (!$error) {
                                //proceed to writing.
                                $entity = new Entity();
                                $entity_approved = 0;
                                if ($user->getCan_approve_ents()) {
                                    $entity_approved = intval(trim($_POST['entity_approved']));
                                    if ($entity_approved === Constants::$ENTITY_APPROVED || $entity_approved === Constants::$ENTITY_REJECTED) {
                                        $entity->setEntity_approval_changed($user->getMember_id());
                                        $entity->setEntity_approval_changed_ip(getUserIP());
                                    }
                                }
                                $entity_sts_text = "";

                                if ($entity_approved === Constants::$ENTITY_NOT_APPROVED) {
                                    $entity_sts_text = "Your entity is awaiting approval.";
                                }

                                $entity
                                        ->setEntity_approved($entity_approved)
                                        ->setImgur_links(serialize($links))
                                        ->setMap_description(trim($_POST['map_description']))
                                        ->setMap_entity(trim($_POST['map_entity']))
                                        ->setMap_name(trim($_POST['map_name']))
                                        ->setUploaded_by($user->getMember_id())
                                        ->setUploaded_by_ip(getUserIP())
                                        ->write();
                                $success = true;
                                $succstr = "Entity has been saved. $entity_sts_text";
                            }
                        }*/
                    }
                }
            } else {
                $error = true;
                $errorstr = "You must upload at least 1 image(s).";
            }
        } else {
            $error = true;
            $errorstr = "You must be logged in to upload entities.";
        }
    }
       
}

function modifyEntity() {
    global $error, $errorstr, $success, $succstr, $user, $loginOut;
    //rules - at least 3 files.
    if (!$loginOut && isset($_POST['modifyEnt']) && intval($_POST['modifyEnt']) > 0) {
        if ($user->isLoggedIn() && $user->getCan_approve_ents()) {
            
            $imgopt = __DIR__ . "/classes/ImageOptimizer";
            require_once $imgopt . "/autoload.php";
            
            $fileCount = sizeof($_FILES['newimgs']['name']);
            if ($_FILES['newimgs']['error'][0] === UPLOAD_ERR_NO_FILE) {
                $fileCount = 0;
            }
            $entity = new Entity();
            $entity = $entity->setEntity_id(intval($_POST['modifyEnt']))->populate();
            
            if ($entity === null || !($entity instanceof Entity)) {
                $error = true;
                $errorstr = "Didn't find entity!";
            } else {
                //final one, check entity, check count of { and count of } characters, it's a cheap shot but could work.
                $map_entity = trim($_POST['modify_map_entity']);

                if (substr_count($map_entity, "{") !== substr_count($map_entity, "}") || substr_count($map_entity, "{") < Constants::$ENTITY_BRACKET_COUNT_THRESHOLD || substr_count($map_entity, "}") < Constants::$ENTITY_BRACKET_COUNT_THRESHOLD) {
                    $error = true;
                    $errorstr = "Entity file either has not matching bracket count or is too small, so not considered to be an entity.";
                    $analyze = openCloseAnalyzer($map_entity);
                    
                    if (is_array($analyze) && sizeof($analyze) > 0) {
                        $errorstr .= "<br>Possible problems:";
                        foreach ($analyze as $anl) {
                            $errorstr .= "<br>Line: " . $anl['line'] . " (saw character <b>" . $anl['seenChar'] . "</b> , expecting <b>" . $anl['expectChar'] . "</b> ) ";
                        }
                    }
                    
                } else {
                    $links = unserialize($entity->getImgur_links());
                    
                    $overwriteImgs = isset($_POST['overwriteImgs']) && trim($_POST['overwriteImgs']) === "on";
                    if ($overwriteImgs) {
                        $links = null;
                    }
                    if ($fileCount !== 0) {
                        
                        $imgs = $_FILES['newimgs'];
                        for ($i = 0; $i < $fileCount; $i++) {


                            $check = getimagesize($imgs['tmp_name'][$i]);
                            $fileName = basename($imgs['name'][$i]);
                            $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                            if ($check === false) {
                                $error = true;
                                $errorstr = "One of the uploaded files was not recognized to be an image!";
                                break;
                            } 

                            if (intval($imgs['size'][$i]) > Constants::$MAX_IMAGE_SIZE) {
                                $error = true;
                                $errorstr = "One of the uploaded images exceeded the image upload size (~6MB).";
                                break;
                            }

                            if ($fileType !== "jpg" && $fileType !== "gif" && $fileType !== "png") {
                                $error = true;
                                $errorstr = "Only jpg, gif and png's are allowed!";
                                break;
                            }
                        }

                        if (!$error) {
                            //means we're good for upload.
                            for ($i = 0; $i < $fileCount; $i++) {
                                $fileName = basename($imgs['name'][$i]);
                                $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                                $uniqid = uniqid($user->getName() . "_" . htmlentities($_POST['map_name']), true);
                                $uploadTo = __DIR__ . "/uploads/" . $uniqid . ".$fileType";
                                $uploadToLink = Constants::$PAGE_URL . "/uploads/$uniqid.$fileType"; 
                                $uploadToSmall = __DIR__ . "/uploads/" . $uniqid . "s.$fileType";
                                move_uploaded_file($imgs['tmp_name'][$i], $uploadTo);
                                //optimize it.
                                $factory = new \ImageOptimizer\OptimizerFactory();
                                $optimizer = $factory->get();

                                $optimizer->optimize($uploadTo); //overwrites file...
                                $imageObj = imagecreatefromstring(file_get_contents($uploadTo));
                                $smallImage = imagescale($imageObj, 90, 90);

                                if ($fileType === "jpg") {
                                    imagejpeg($smallImage, $uploadToSmall);
                                } else if ($fileType === "png") {
                                    imagepng($smallImage, $uploadToSmall);
                                } else {
                                    imagegif($smallImage, $uploadToSmall);
                                }
                                $links[] = $uploadToLink;
                            }
                        }
                    }
                        
                        /*$imgur = new Imgur(Constants::$IMGUR_CLID, $_FILES, "newimgs");
                        $data = $imgur->send();
                         if (sizeof($data) !== $fileCount) {
                            $error = true;
                            $errorstr = "Image upload to Imgur failed (either you supplied something which you shouldn't have or there was just an interruption). Please try again.";
                        } else {
                            //$links = null;
                            foreach ($data as $row) {
                                $links[] = $row['data']['link'];
                                if (!(array_key_exists("success", $row)) || intval($row['success']) !== 1) {
                                    $error = true;
                                    $errorstr = "Image upload to Imgur failed (either you supplied something which you shouldn't have or there was just an interruption). Please try again.";
                                }
                            }
                        }*/
                    
                   
                        
                        
                            //proceed to writing.
                            //$entity = new Entity();
                    
                    if (!$error) {
                            $entity_approved = 0;
                            if ($user->getCan_approve_ents()) {
                                $entity_approved = intval(trim($_POST['entity_approved']));
                                if ($entity_approved === Constants::$ENTITY_APPROVED || $entity_approved === Constants::$ENTITY_REJECTED) {
                                    $entity->setEntity_approval_changed($user->getMember_id());
                                    $entity->setEntity_approval_changed_ip(getUserIP());
                                }
                            }
                            $entity_sts_text = "";

                            if ($entity_approved === Constants::$ENTITY_NOT_APPROVED) {
                                $entity_sts_text = "Your entity is awaiting approval.";
                            }

                            $entity
                                    ->setEntity_approved($entity_approved)
                                    ->setImgur_links(serialize($links))
                                    ->setMap_description(trim(htmlentities($_POST['modify_map_description'])))
                                    ->setMap_entity(trim($_POST['modify_map_entity']))
                                    ->setMap_name(trim(htmlentities($_POST['modify_map_name'])))
                                    ->setUploaded_by($user->getMember_id())
                                    ->setUploaded_by_ip(getUserIP())
                                    ->write();
                            $success = true;
                            $succstr = "Entity has been saved. $entity_sts_text";
                        }
                    }
            }
              
        } else {
            $error = true;
            $errorstr = "You must be logged in and have rights to modify entities.";
        }
    }
       
}

function rateEntity() {
    global $error, $errorstr, $success, $succstr, $user, $loginOut;
    $entity = new Entity();
    if (isset($_POST['rateEntity']) && intval($_POST['rateEntity']) === 1 && !$loginOut && $user->isLoggedIn()) {
        $entity_id = $_POST['entity_id'];
        $ent_voter = $user->getMember_id();
        $ent_voter_ip = getUserIP();
        $vote = intval($_POST['vote']);
        
        $entityVote = new EntityVote(null, $entity_id, $ent_voter, $ent_voter_ip, $vote);
        $entityVote = $entityVote->populate(false)->setVote($vote)->setEnt_voter_ip($ent_voter_ip)->write();
        
        return json_encode(array("msg" => "Vote cast successfully."));
        
    }
}

function handleAjax() {
    global $error, $errorstr, $success, $succstr, $user, $loginOut;
    
    if (isset($_POST['getRollingPlayerStats'])) {
        $jsonArr = array();
        $dtobj = new DateTime();
        $stat_dt_to = $dtobj->format("Y-m-d H:i:s");
        $dtobj->modify("-7 days");
        $stat_dt_fm = $dtobj->format("Y-m-d H:i:s");
        
        $data = Statistics::getStatistics(null, $stat_dt_fm, $stat_dt_to);
        
        
        $dataArr = array();
        if (sizeof($data) > 0) {
            foreach ($data as $row) {
                
                    $entity = $row->getEntity();
                if (!array_key_exists($row->getMapcycle_id(), $dataArr)) {
                    $mcData = $row->getMapcycle();
                    $dataArr[$row->getMapcycle_id()] = array("name" => "Mapcycle " . $row->getMapcycle_id() . " by " . XenForo::getUsernameByID(Constants::getXenSQL(), $mcData->getMapcycle_creator_user_id()), "values" => array());
                }
                $dataArr[$row->getMapcycle_id()]['values'][] = array("label" => $row->getStat_dt() . " - Map: " . $entity->getMap_name() . " (" . $row->getGametype() . ")", "y" => intval($row->getClients())
                    , "click" => "", "mapcycle_id" => $row->getMapcycle_id(), "entity_id" => $row->getEntity_id()
                        , "modalTitle" => "Map: " . $entity->getMap_name() . " (" . $row->getGametype() . ")");
            }
        }
        
        foreach ($dataArr as $mcid => $mcdt) {
            $jsonArr[] = array("type" => "spline", "visible" => "true", "showInLegend" => "true", "yValueFormatString" => "#0 players", "name" => $mcdt['name'], "dataPoints" => $mcdt['values']);
        }
        
        echo json_encode($jsonArr);
        die();
    
    } else if (isset($_POST['rateEntity']) && intval($_POST['rateEntity']) === 1) {
        echo rateEntity();
        die();
    } else if (isset($_POST['getApprovalList'], $_POST['filter'], $_POST['uploaded_by']) && $user instanceof User && $user->getCan_approve_ents()) {
        $filter = trim($_POST['filter']);
        $uploaded_by = intval(trim($_POST['uploaded_by']));
        
        if (strlen($filter) === 0) {
            $filter = null;
        }
        
        if ($uploaded_by === 0) {
            $uploaded_by = null;
        }
        
        $ents = Entity::getEntities(Constants::$ENTITY_NOT_APPROVED, $filter, $uploaded_by);
        $returnable = null;
        if ($ents !== null && is_array($ents) && sizeof($ents) > 0) {
            foreach ($ents as $ent) {
                $returnable['success'] = 1;
                $imgurlinks = unserialize($ent->getImgur_links());
                $forumUsername = XenForo::getUsernameByID(Constants::getXenSQL(), $ent->getUploaded_by());
                $imgs = "<center><a style='cursor: pointer;' onclick='imgModal(" . $ent->getEntity_id() . ", \"" . $ent->getMap_name() . "\", \"" . $forumUsername . "\");'>";
                foreach ($imgurlinks as $i => $img) {
    $extension = substr($img, strlen($img) - 4);
    $img = substr($img, 0, strlen($img) - 4) . "s" . $extension;
    $imgs .= "<img src='$img' class='img-responsive'>";
    //$page->appendContentBR("<a href='$img' target='_blank' class='a-abc-active'>Image $i</a>");
}
$imgs.="</a></center>";
                $returnable['ents'][] = array(
                    "entity_id" => $ent->getEntity_id()
                        , "map_name" => $ent->getMap_name()
                        , "map_description" => $ent->getMap_description()
                        , "imgur_links" => unserialize($ent->getImgur_links())
                    , "uploaded_by" => $ent->getUploaded_by()
                        , "uploaded_by_name" => $forumUsername
                    , "uploaded_by_ip" => $ent->getUploaded_by_ip()
                        , "html" => <<<EOT
   <div class='col-sm-4 mx-auto mt-3'>
<div class='card  h-100'>
<div class='card-header  bg-abc'>{$ent->getMap_name()}</div>
<div class='card-body'>    
  {$ent->getMap_description()}
  <br>
  {$imgs}

</div>
<div class='card-footer'>
  <small>Entity created by <a href='https://forums.3d-sof2.com/members/a.{$ent->getUploaded_by()}' target='_blank' class='a-abc-active'>{$forumUsername}</a> (IP addresses - {$ent->getUploaded_by_ip()}).</small><br>
  <button type='button' class='btn btn-success btn-block' onclick='modifyEntity({$ent->getEntity_id()});'>Modify entity</button>
  <button id='ent_dl_{$ent->getEntity_id()}' type='button' class='btn btn-primary btn-block' onclick='toggleEntityDownload({$ent->getEntity_id()}, "{$ent->getMap_name()}");'>Download entity</button>
     </div>
</div>
</div>
                    
EOT
                );
            }
            
        }
        if ($returnable === null) {
                $returnable = array();
            }
        echo json_encode($returnable);
    } else if (isset($_GET['getNotApprovedEntity']) && $user instanceof User && $user->getCan_approve_ents ()) {
        
        $ent_id = intval($_GET['getNotApprovedEntity']);
        if ($ent_id !== 0) {
            $entity = new Entity($ent_id);
            $entity = $entity->populate();
            if ($entity !== null && $entity instanceof Entity) {
                echo $entity->getMap_entity();
                die();
            }
        }
    } else if (isset($_POST['getModifyEntity']) && intval($_POST['getModifyEntity']) > 0 && $user instanceof User && $user->getCan_approve_ents()) {
        $entity_id = intval($_POST['getModifyEntity']);
        $entity = new Entity($entity_id);
        $entity = $entity->populate();
        
        if ($entity === null || !($entity instanceof Entity)) {
            echo json_encode(array("error" => "Entity not found."));
        } else {
            echo json_encode(array(
                "entity_id" => $entity->getEntity_id()
                    , "entity_metadata" => "Entity uploaded by " . XenForo::getUsernameByID(Constants::getXenSQL(), $entity->getUploaded_by()) . " (" . $entity->getUploaded_by_ip() . ")."
                , "modify_map_name" => $entity->getMap_name()
                    , "modify_map_description" => $entity->getMap_description()
                    , "modify_map_entity" => $entity->getMap_entity()
                    
            ));
        }
    } else if (isset($_POST['getNotApprovedMCsTable']) && $user instanceof User && $user->getCan_approve_ents()) {
        $mcs = Mapcycle::getNotApprovedMapcycles();
        
        $output = array();
        if ($mcs !== null && is_array($mcs) && sizeof($mcs) > 0) {
            foreach ($mcs as $mc) {
                $creator = XenForo::getUsernameByID(Constants::getXenSQL(), $mc->getMapcycle_creator_user_id());
                $options = "";
                if ($user->getCan_approve_mapcycle()) {
                    $creator .= " (ip: " . $mc->getMapcycle_creator_ip() . ")";
                }
                $siteurl = Constants::$PAGE_URL;
                
                if ($user->getCan_approve_mapcycle() || intval($mc->getMapcycle_creator_user_id()) === intval($user->getMember_id())) {
                    $options .= "<div class='btn-group d-flex'><button class='btn btn-outline-primary btn-sm w-100' type='button' onclick='addMaps({$mc->getMapcycle_id()});'>Add maps to this</button>";
                    $options .= "<button class='btn btn-outline-info btn-sm w-100' type='button' onclick='window.location = \"$siteurl/maps/mapcycle/{$mc->getMapcycle_id()}\"';>Manage this mapcycle</button>";
                if ($user->getCan_approve_mapcycle()) {
                    $options .= "<button class='btn btn-outline-danger btn-sm w-100' type='button' onclick='deleteMapcycle({$mc->getMapcycle_id()});'>Delete mapcycle</button>";
                }
                    $options .= "</div><br>";
                }
                
                
                
                $options .= "<button class='btn btn-outline-dark btn-sm w-100' type='button' onclick='duplicateMapcycle({$mc->getMapcycle_id()});'>Duplicate this mapcycle</button>";
                
                $description = nl2br(htmlentities($mc->getMapcycle_description()));
                $maps = "";
                $em = EntityMap::getOrderedEntities($mc);
                $canApproveMapcycle = true;
                if ($em !== null && is_array($em) && sizeof($em) > 0) {
                    $maps = sizeof($em) . " maps connected";
                    foreach ($em as $emap) {
                        $cvarMap = CvarMap::getCvarMapByEntityMap($emap);
                        if ($cvarMap !== null && is_array($cvarMap) && sizeof($cvarMap) > 0 && $canApproveMapcycle) {
                            
                        } else {
                            $canApproveMapcycle = false;
                        }
                    }
                    
                    if ($canApproveMapcycle && $user->getCan_approve_mapcycle()) {
                        $options .= "<br><br><button class='btn btn-outline-success btn-sm w-100' type='button' onclick='finalizeMapcycle({$mc->getMapcycle_id()});'>Finalize and approve mapcycle</button>";
                    }
                    
                } else {
                    $maps = "0 maps connected";
                }
                
                if (!array_key_exists('notapproved', $output)) {
                    $output['notapproved'] = "";
                }
                
                $output['notapproved'] .= "<tr><td>$creator</td><td>$description</td><td>$maps</td><td>$options</td></tr>";
            }
        } else {
            $output['notapproved'] = "<tr><td colspan=4>No mapcycles to show</td></tr>";
        }
        echo json_encode($output);
    } else if (isset($_POST['createNewMapcycle']) && $user instanceof User && $user->getCan_approve_ents()) {
        $mc = new Mapcycle();
        $mc->setMapcycle_creator_ip(getUserIP())->setMapcycle_creator_user_id($user->getMember_id())->setMapcycle_status(Mapcycle::$MAPCYCLE_NOTAPPROVED)->write();
        echo json_encode(array("msg" => "New empty mapcycle created, which you can manage below"));
    } else if (isset($_POST['setMapToSession'], $_POST['mapcycle_id']) && intval($_POST['mapcycle_id']) > 0) {
        $mc = new Mapcycle(intval($_POST['mapcycle_id']));
        $mc = $mc->populate();
        $output = array();
        if ($mc === null || !($mc instanceof Mapcycle)) {
            $output['error'] = "Mapcycle doesn't exist!";
        } else {
            if ($user->getCan_approve_mapcycle() || intval($user->getMember_id()) === intval($mc->getMapcycle_creator_user_id())) {
                if ($mc->isNotApproved()) {
                    $output['info'] = "Mapcycle added for management. Proceeding to entity page.";
                    $output['href'] = Constants::$PAGE_URL . "/maps/entities";
                    $_SESSION['manageMapcycle'] = $mc->getMapcycle_id();
                } else {
                    $output['error'] = "Mapcycle has to be in status Not approved to make edits.";
                }
                
            } else {
                $output['error'] = "You cannot manage this mapcycle, because you're not the creator or you don't have permissions to manage all mapcycles.";
            }
        }
        
        echo json_encode($output);
    } else if (isset($_POST['getEntityCards'])) {
        $mc = null;
        $hideAdded = false;
        $filterMCId = null;
        $excludeMCId = null;
        $totalEntityCount = 0;
        
        if (isset($_SESSION['manageMapcycle']) && intval($_SESSION['manageMapcycle']) > 0) {
            $mc = new Mapcycle(intval($_SESSION['manageMapcycle']));
            $mc = $mc->populate();
            if (isset($_POST['hideAdded']) && intval($_POST['hideAdded']) === 1) {
                $hideAdded = true;
                $excludeMCId = $mc->getMapcycle_id();
            }
        }
        
        $liveMCMap = null;
        $showLive = false;
        
        if (isset($_POST['showLiveMCEnts']) && intval($_POST['showLiveMCEnts']) === 1) {
            $showLive = true;
            $currentLive = PushToLive::getLiveMapcycle();
            if ($currentLive !== null && $currentLive instanceof PushToLive) {
                $liveMC = new Mapcycle($currentLive->getMapcycle_id());
                $liveMC = $liveMC->populate();
                //$liveMCMap = EntityMap::getOrderedEntities($liveMC);
                $filterMCId = $liveMC->getMapcycle_id();
            }
        }
        
        $uploaded_by = null;
        $filter = null;
        
        if (isset($_POST['uploaded_by']) && intval(trim($_POST['uploaded_by'])) > 0) {
            $uploaded_by = trim($_POST['uploaded_by']);
        }
        
        if (isset($_POST['filter']) && strlen(trim($_POST['filter'])) > 0) {
            $filter = trim($_POST['filter']);
        }
        $entities = array();
        $singleEntity = false;
        
        $currPage = isset($_POST['currPage']) ? intval($_POST['currPage']) : 0;
        $page_items = isset($_POST['page_items']) ? intval($_POST['page_items']) : 0;
        if ($page_items > 120 || $page_items < 15) {
            $page_items = 30;
        }
        
        $sort_by = isset($_POST['sort_by']) ? intval($_POST['sort_by']) : 0;
        $avgvote = isset($_POST['avg_vote_filter']) ? intval($_POST['avg_vote_filter']) : 0;
        if ($avgvote < 1 || $avgvote > 5) {
            $avgvote = 0;
        }
        
        $approvedBy = isset($_POST['approved_by']) ? intval($_POST['approved_by']) : null;
        if ($approvedBy < 1) {
            $approvedBy = null;
        }
        
        
        
        
        if (isset($_POST['singleEntity']) && intval($_POST['singleEntity']) > 0) {
            /*$entity = new Entity(intval($_POST['singleEntity']));
            $entity = $entity->populate();
            $showLive = false;
            $hideAdded = false;
            if ($entity === null || !($entity instanceof Entity)) {
                echo json_encode(array("html" => ""));
                die();
            } else {*/
                $entities = Entity::getEntitiesByParams(false, intval($_POST['singleEntity']));
                $singleEntity = true;/*
            }*/
        } else {
            if (isset($_POST['showNotApprovedEnts']) && intval($_POST['showNotApprovedEnts']) === 1) {
                $entities = Entity::getEntities(Constants::$ENTITY_NOT_APPROVED, $filter, $uploaded_by, 0, $avgvote, $page_items, $sort_by, $currPage, $filterMCId, $excludeMCId, false, $approvedBy);
            
                $totalEntityCount = Entity::getEntities(Constants::$ENTITY_NOT_APPROVED, $filter, $uploaded_by, 0, $avgvote, $page_items, $sort_by, $currPage, $filterMCId, $excludeMCId, true, $approvedBy);
            
            } else {
                $entities = Entity::getEntities(null, $filter, $uploaded_by, 0, $avgvote, $page_items, $sort_by, $currPage, $filterMCId, $excludeMCId, false, $approvedBy);
                $totalEntityCount = Entity::getEntities(null, $filter, $uploaded_by, 0, $avgvote, $page_items, $sort_by, $currPage, $filterMCId, $excludeMCId, true, $approvedBy);
            }
            
        }
        
        
        $output = array();
        //$entityMap = null;
        /*if ($mc !== null && $mc instanceof Mapcycle) {
            $entityMap = EntityMap::getOrderedEntities($mc);
        }*/
        $html = "";
        //$skip = false;
         
        foreach ($entities as $entity) { 
            /*
            $skip = false;
            if ($showLive && $liveMCMap !== null && is_array($liveMCMap) && sizeof($liveMCMap) > 0) {
                $skip = true;
                
                foreach ($liveMCMap as $lmm) {
                    if (intval($lmm->getEntity_id()) === intval($entity->getEntity_id())) {
                        $skip = false;
                        break;
                    } else {
                        $skip = true;
                    }
                }
            }
            if ($hideAdded && $entityMap !== null && is_array($entityMap) && sizeof($entityMap) > 0) {
                foreach ($entityMap as $emap) {
                    if (intval($entity->getEntity_id()) === intval($emap->getEntity_id())) {
                        $skip = true;
                        break;
                    }
                }
            }
            
            
            
            if ($skip) {
                continue;
            }
            
            */
            
            $forumUsername = XenForo::getUsernameByID(Constants::getXenSQL(), $entity->getUploaded_by());
            if ($singleEntity) {
                $html .= "<div class='mx-auto'>";
            } else {
                $html .= "<div class='col-sm-4 mx-auto mt-3'>";
            }
            
            $html .= "<div class='card  h-100'>";
            $html .= "<div class='card-header  bg-abc'>" . htmlentities($entity->getMap_name()) . "</div>";
            $html .= "<div class='card-body'>";
            if ($mc !== null && $mc instanceof Mapcycle && $entityMap !== null && is_array($entityMap) && sizeof($entityMap) > 0) {
                foreach ($entityMap as $entmp) {
                    if (intval($entmp->getEntity_id()) === intval($entity->getEntity_id())) {
                        $html .= "<h6>This map is added to the current mapcycle</h6>";
                        break;
                    }
                }
            }
            $html .= "<p class='card-text'>" . nl2br(htmlentities($entity->getMap_description()));
            $imgurlinks = unserialize($entity->getImgur_links());
            $html .= "<center><a style='cursor: pointer;' onclick='imgModal(" . $entity->getEntity_id() . ", \"" . htmlentities($entity->getMap_name()) . "\", \"" . $forumUsername . "\");'>";
            foreach ($imgurlinks as $i => $img) {
                $extension = substr($img, strlen($img) - 4);
                $img = substr($img, 0, strlen($img) - 4) . "s" . $extension;
                $html .= "<img src='$img' class='img-responsive'>";
            }
            $html .= "</a></center>";

            $html .= "<br>";
            $currentVoteCount = $entity->getAverage_vote();

            $showStarCount = intval(round($currentVoteCount));
            $mapScore = round($entity->getMap_score(), 2);

            $html .= "<center><span class='btn btn-outline-black'>Score: $mapScore<br>Average vote: " . round($currentVoteCount, 2) . "<br>Total votes: " . intval($entity->getTotal_votes()) . "</span><br><br>";
            
            
            if ($user->isLoggedIn()) {
                if ($user->getCan_approve_ents()) {
                    $html .= "<center><span class='btn btn-outline-black'>Red spawns: " . $entity->getRedTeamSpawnPoints() . "<br>Blue spawns: " . $entity->getBlueTeamSpawnPoints() . "</span></center>";
                }
                $myVote = null;
                $voteAction1 = "";

                $voteAction2 = "";
                $voteAction3 = "";
                $voteAction4 = "";
                $voteAction5 = "";
                $myVote = new EntityVote();
                $myVote = $myVote->setEntity_id($entity->getEntity_id())->setEnt_voter($user->getMember_id())->populate();
                $showStarCount = 0;
                if ($myVote !== null) {
                    $showStarCount = $myVote->getVote();
                }
                $v1c = "";
                $v2c = "";
                $v3c = "";
                $v4c = "";
                $v5c = "";
                $voteAction1 = "class=\"" . (($showStarCount >= 1) ? "checked":"") . "\" style=\"cursor: pointer;\" onmouseover=\"handleMouseRating(" . $entity->getEntity_id() . ", 1);\" onclick=\"rateEntity(" . $entity->getEntity_id() . ", 1);\"";
                $voteAction2 = "class=\"" . (($showStarCount >= 2) ? "checked":"") . "\" style=\"cursor: pointer;\" onmouseover=\"handleMouseRating(" . $entity->getEntity_id() . ", 2);\" onclick=\"rateEntity(" . $entity->getEntity_id() . ", 2);\"";
                $voteAction3 = "class=\"" . (($showStarCount >= 3) ? "checked":"") . "\" style=\"cursor: pointer;\" onmouseover=\"handleMouseRating(" . $entity->getEntity_id() . ", 3);\" onclick=\"rateEntity(" . $entity->getEntity_id() . ", 3);\"";
                $voteAction4 = "class=\"" . (($showStarCount >= 4) ? "checked":"") . "\" style=\"cursor: pointer;\" onmouseover=\"handleMouseRating(" . $entity->getEntity_id() . ", 4);\" onclick=\"rateEntity(" . $entity->getEntity_id() . ", 4);\"";
                $voteAction5 = "class=\"" . (($showStarCount >= 5) ? "checked":"") . "\" style=\"cursor: pointer;\" onmouseover=\"handleMouseRating(" . $entity->getEntity_id() . ", 5);\" onclick=\"rateEntity(" . $entity->getEntity_id() . ", 5);\"";

                $html .= "<div id=\"stars_" . $entity->getEntity_id() . "\" style=\"display: inline!important;padding-left: 20px;padding-right:20px\" onmouseleave=\"handleMouseRating(" . $entity->getEntity_id() . ", " . $showStarCount . ");\">";

                $html .= "<h6>Cast / change your vote by clicking on the star</h6><center><span data-toggle='tooltip' title='1 Star'  id=\"1star_" . $entity->getEntity_id() . "\" $voteAction1><em class=\"fa fa-star\"></em></span>";
                $html .= "<span data-toggle='tooltip' title='2 stars'   id=\"2star_" . $entity->getEntity_id() . "\" $voteAction2><em class=\"fa fa-star\"></em></span>";
                $html .= "<span data-toggle='tooltip' title='3 stars'   id=\"3star_" . $entity->getEntity_id() . "\" $voteAction3><em class=\"fa fa-star\"></em></span>";
                $html .= "<span data-toggle='tooltip' title='4 stars'   id=\"4star_" . $entity->getEntity_id() . "\" $voteAction4><em class=\"fa fa-star\"></em></span>";
                $html .= "<span data-toggle='tooltip' title='5 stars'   id=\"5star_" . $entity->getEntity_id() . "\" $voteAction5><em class=\"fa fa-star\"></em></span>";

                $html .= "</center></div>";
            }
            $html .= "</center>";
            $html .= "</p>";

            $html .= "</div>";
            $ipstr = "";
            if ($user->getCan_approve_mapcycle()) {
                $ipstr = " <span class='text-muted' style='font-size: 0.7rem;'>(" . $entity->getUploaded_by_ip() . ")</span>";
            }
            $html .= "<div class=\"card-footer\" style='font-size: 0.8rem;'>Uploaded by <a target='_blank' class='a-abc-active' href='https://forums.3d-sof2.com/members/a." . $entity->getUploaded_by() . "/'>" . $forumUsername . "</a>" . $ipstr;

            if ($user->getCan_approve_ents()) {
                $html .= <<<EOT
                        <br><br>
                        <button type='button' class='btn btn-block btn-outline-info' onclick='modifyEntity({$entity->getEntity_id()});' >Modify entity</button>
                        <button id='ent_dl_{$entity->getEntity_id()}' type='button' class='btn btn-primary btn-block' onclick='toggleEntityDownload({$entity->getEntity_id()}, "{$entity->getMap_name()}");'>Download entity</button>
                        <button type='button' class='btn btn-block btn-outline-warning' onclick='pushEntityToPreprod({$entity->getEntity_id()});'>Send to preprod</button>
EOT
;
                if (isset($_SESSION['manageMapcycle']) && intval($_SESSION['manageMapcycle']) > 0) {
                    $html .= "<button type='button' class='btn btn-block btn-outline-primary' onclick='pushToMapcycle({$entity->getEntity_id()});'>Push to mapcycle</button>";
                }
                
                if ($user->getCan_approve_mapcycle()) {
                    $html .= "<button type='button' class='btn  btn-block btn-outline-danger' onclick='deleteEntity({$entity->getEntity_id()});'>Delete entity</button>";
                }
                
                $html .= "";
            }

            $html .= "</div>";
            $html .= "</div>";
            $html .= "</div>";    
        }
        $output['totalEntities'] = $totalEntityCount;
        
        //at bottom add pagination.
        //currpage is at $currPage
        $totalNeededPages = intval(ceil($totalEntityCount / $page_items));
        $paginationBtns = "<div class='btn-group flex-wrap'>";
        
        for ($i = 0; $i < $totalNeededPages; $i++) {
            $dispPage = $i + 1;
            $active = "";
            if ($currPage === $i) {
                $active = "active";
            }
            $paginationBtns .= "<button class='btn btn-outline-primary $active' onclick='currPage = $i;getEntityCards();'>$dispPage</button>";
        }
        $paginationBtns .= "</div>";
        $output['paginate'] = $paginationBtns;
        $output['html'] = $html;
        
        echo json_encode($output);
    } else if (isset($_POST['pushEntityToMapcycle'], $_POST['pushmc_entity_id'], $_SESSION['manageMapcycle']) && intval($_POST['pushmc_entity_id']) > 0 && intval($_SESSION['manageMapcycle']) > 0 && $user instanceof User && $user->getCan_approve_ents()) {
        $mc = new Mapcycle(intval($_SESSION['manageMapcycle']));
        $mc = $mc->populate();
        $entity = new Entity(intval($_POST['pushmc_entity_id']));
        $altmap = isset($_POST['pushmc_altmap']) && trim($_POST['pushmc_altmap']) === "on";
        $gametype = trim($_POST['pushmc_gt']);
        $entity = $entity->populate();
        if (!in_array($gametype, Constants::$GAMETYPES)) {
            $output['error'] = "Gametype $gametype is not allowed.";
        } else if ($mc === null || !($mc instanceof Mapcycle)) {
            $output['error'] = "Mapcycle not found.";
        } else if (intval($mc->getMapcycle_creator_user_id()) !== intval($user->getMember_id()) && !$user->getCan_approve_mapcycle()) {
            $output['error'] = "You do not have permissions to modify this mapcycle, as you're not the owner or you're not privileged enough.";
        } else if ($entity === null || !($entity instanceof Entity)) {
            $output['error'] = "Entity not found.";
        } else if (!$entity->isApproved()) {
            $output['error'] = "The entity is not approved";
        } else if ($mc->isDeleted()) {
            $output['error'] = "The mapcycle is deleted, you cannot push maps to this.";
        } else {
            //ent approved, mc allowed and found.
            $entmap = new EntityMap(null, $mc->getMapcycle_id(), $entity->getEntity_id(), $user->getMember_id(), getUserIP(), null, $gametype, bool2int($altmap));
            
            //check collision.
            
            $collision = EntityMap::checkEntityCollision($entmap);
            
            if ($collision !== null && is_array($collision) && sizeof($collision) > 0) {
                if ($altmap) {
                    $output['error'] = "Map " . $entity->getMap_name() . " cannot be added as such a map already exists in altmap category.";
                } else {
                    $output['error'] = "Map " . $entity->getMap_name() . " cannot be added as such a map already exists with gametype $gametype";
                }
            } else {
            

                //cvars and shit come later, at the moment we just write it into the temporary mapcycle.
                $entmap->write();
                
                if ($mc->isApproved()) {
                    $mc->setMapcycle_status(Mapcycle::$MAPCYCLE_NOTAPPROVED)->setMapcycle_status_change_by($user->getMember_id())->setMapcycle_status_change_by_ip(getUserIP());
                    $output['msg'] = "The mapcycle has been unapproved because of modifications and entity has been added to the mapcycle.";
                } else {
                    $output['msg'] = "Entity has been added to the mapcycle.";
                }
                
                
            }
        }
        echo json_encode($output);
    } else if (isset($_POST['getSpawnPoints'], $_POST['entity_id'], $_SESSION['manageMapcycle']) && intval($_POST['entity_id']) > 0 && intval($_SESSION['manageMapcycle']) > 0 && $user instanceof User && $user->getCan_approve_ents()) {
        $entity = new Entity(intval($_POST['entity_id']));
        $entity = $entity->populate();
        if ($entity === null || !($entity instanceof Entity)) {
            $output['error'] = "Entity not found.";
        } else {
            $blueTeam = $entity->getBlueTeamSpawnPoints();
            $redTeam = $entity->getRedTeamSpawnPoints();
            $output['spawnpoints'] = "There are $blueTeam spawnpoints for Blue team and $redTeam spawnpoints for red team.";
        }
        echo json_encode($output);
    } else if (isset($_POST['getMapcycleEntitiesTable'], $_POST['mapcycle_id']) && intval($_POST['mapcycle_id']) > 0 && $user instanceof User && $user->getCan_approve_ents()) {
        $mapcycle = new Mapcycle(intval($_POST['mapcycle_id']));
        $mapcycle = $mapcycle->populate();
        
        if ($mapcycle === null || !($mapcycle instanceof Mapcycle)) {
            $output['error'] = "Mapcycle not found.";
        } else {
            $allHaveCvars = true;
            
            $entityMaps = EntityMap::getOrderedEntities($mapcycle);
            $html = "<tr><td colspan=8>No maps to show.</td></tr>";
            if ($entityMaps !== null && is_array($entityMaps) && sizeof($entityMaps) > 0) {
                $html = "";
                foreach ($entityMaps as $entityMap) {
                    $entity = $entityMap->getEntity();
                    $cvars = CvarMap::getCvarMapByEntityMap($entityMap);
                    $cvarCount = 0;

                    if ($cvars !== null && is_array($cvars)) {
                        $cvarCount = sizeof($cvars);
                    }

                    if ($cvarCount === 0) {
                        $allHaveCvars = false;
                    }

                    $trowClass = "";

                    if ($cvarCount === 0) {
                        $trowClass = "";
                    } else {
                        $trowClass = "table-success";
                    }

                    $html .= "<tr class='$trowClass' id='entityId{$entityMap->getEntitymap_id()}' data-entitymap-id='{$entityMap->getEntitymap_id()}'>";
                    $html .= "<td>" . htmlentities($entity->getMap_name()) . "</td>";
                    $html .= "<td>" . $entityMap->getGametype();
                    if ($entityMap->isAltmap()) {
                        $html .= " (altmap)";
                    }
                    $html .= "</td>";
                    $html .= "<td>Red team - " . $entity->getRedTeamSpawnPoints() . "<br>Blue team - " . $entity->getBlueTeamSpawnPoints() . "</td>";
                    $html .= "<td>$cvarCount cvars</td>";
                    $html .= "<td>" . nl2br(htmlentities($entity->getMap_description())) . "</td>";
                    $html .= "<td>" . XenForo::getUsernameByID(Constants::getXenSQL(), $entity->getUploaded_by());
                    if ($user->getCan_approve_mapcycle()) {
                        $html .= " (ip: " . $entity->getUploaded_by_ip() . ")";
                    }
                    $html .= "</td>";
                    $html .= "<td>" . XenForo::getUsernameByID(Constants::getXenSQL(), $entityMap->getAdded_by());
                    if ($user->getCan_approve_mapcycle()) {
                        $html .= " (ip: " . $entityMap->getAdded_by_ip() . ")";
                    }
                    $html .= "</td>";
                    $html .= "<td><div class='btn-group d-flex'><button type='button' class='btn btn-primary btn-sm w-100' onclick='viewEntityCard(" . $entity->getEntity_id() . ");'>View entity</button>";
                    $html .= "<button type='button' class='btn btn-success btn-sm w-100' onclick='toggleCvars(" . $entityMap->getEntitymap_id() . ");'>Manage cvars</button>";
                    
                    $html .= "</div><br><div class='btn-group d-flex'>";
                    $html .= "<button type='button' class='btn btn-danger btn-sm w-100' onclick='removeEntityModal(" . $entityMap->getEntitymap_id() . ");'>Remove this map</button>";
                    $html .= "</div></td>";

                    $html .= "</tr>";

                }
                
                if ($allHaveCvars) {
                    //prepend buttons.
                    //$html = "<h4>TODO ALLHAVECVARS</h4>" . $html;
                    $output['prepend'] = "";
                    if ($user->getCan_approve_mapcycle()) {
                        
                        $currPtl = PushToLive::getLiveMapcycle();
                        if ($currPtl !== null && $currPtl instanceof PushToLive && intval($currPtl->getMapcycle_id()) === intval($mapcycle->getMapcycle_id())) {
                            $output['prepend'] .= "<button type='button' class='btn btn-outline-danger btn-block' onclick='resendLive();'>Resend mapcycle to prod</button>";
                            
                        }
                        
                        if ($mapcycle->isApproved()) {
                            $output['prepend'] .= "<div class='btn-group d-flex'><button class='btn btn-outline-success w-100' type='button' onclick='ptl({$mapcycle->getMapcycle_id()});'>Add push to live dates</button><button class='btn btn-outline-warning w-100' type='button' onclick='manageptl({$mapcycle->getMapcycle_id()});'>Manage push-to-lives</button></div><br>";
                            $output['prepend'] .= "<button type='button' class='btn btn-outline-info btn-block' onclick='pushToPreProd({$mapcycle->getMapcycle_id()});'>Push mapcycle to preprod</button>";
                            $output['prepend'] .= "<button type='button' class='btn btn-outline-dark btn-block' onclick='unfinalizeMapcycle({$mapcycle->getMapcycle_id()});'>Set mapcycle as not approved</button>";
                        } else if ($mapcycle->isNotApproved()) {
                            $output['prepend'] .= "<button type='button' class='btn btn-outline-dark btn-block' onclick='finalizeMapcycle({$mapcycle->getMapcycle_id()});'>Finalize and approve mapcycle</button>";
                        }
                    }
                }
            }
            
            $output['entities'] = $html;
        }
        echo json_encode($output);
    } else if (isset($_POST['writeEntitymapOrder']) && is_array($_POST['writeEntitymapOrder']) && sizeof($_POST['writeEntitymapOrder']) > 0 && $user instanceof User && $user->getCan_approve_ents()) {
        $newOrder = $_POST['writeEntitymapOrder'];
        echo json_encode(EntityMap::updateEntityOrder($newOrder));
    } else if (isset($_POST['softDeleteEntity'], $_POST['entity_id']) && intval($_POST['entity_id']) > 0 && $user instanceof User && $user->getCan_approve_mapcycle()) {
        $entity = new Entity(intval($_POST['entity_id']));
        $entity = $entity->populate();
        
        if ($entity === null || !($entity instanceof Entity)) {
            $output['error'] = "Entity not found.";
        } else {
            $entityMaps = EntityMap::getEntityMapsByEntity($entity);
            
            if ($entityMaps !== null && is_array($entityMaps) && sizeof($entityMaps) > 0) {
                foreach ($entityMaps as $entityMap) {
                    CvarMap::deleteCvarMapByEntitymap($entityMap);
                    $entityMap->delete();
                }
            }
            
            $entity->setDeleted(1)->setEntity_approved(Constants::$ENTITY_REJECTED)->setEntity_approval_changed($user->getMember_id())->setEntity_approval_changed_ip(getUserIP())->write();
            $output['msg'] = "Entity successfully removed from all mapcycles and it has been deleted.";
            
        }
        
        echo json_encode($output);
        
    } else if (isset($_POST['removeEntitymap'], $_POST['entitymap_id']) && intval($_POST['entitymap_id']) > 0 && $user instanceof User && $user->getCan_approve_ents()) {
        $entitymap = new EntityMap(intval($_POST['entitymap_id']));
        $entitymap = $entitymap->populate();
        
        if ($entitymap === null || !($entitymap instanceof EntityMap)) {
            $output['error'] = "Entitymap not found.";
        } else {
            $mapcycle = $entitymap->getMapcycle();
            
            if ($mapcycle === null || !($mapcycle instanceof Mapcycle)) {
                $output['error'] = "Mapcycle not found.";
            } else if ($mapcycle->isApproved() && !$user->getCan_approve_mapcycle()) {
                $output['error'] = "You cannot remove maps from an approved mapcycle.";
            } else {
                if (!$user->getCan_approve_mapcycle() && intval($user->getMember_id()) !== intval($mapcycle->getMapcycle_creator_user_id())) {
                    $output['error'] = "You cannot manage this mapcycle!";
                } else {
                    CvarMap::deleteCvarMapByEntitymap($entitymap);
                    $entitymap->delete();
                    $output['msg'] = "Map removed from mapcycle.";
                }
            }
        }
        echo json_encode($output);
    } else if (isset($_POST['duplicateMapcycle'], $_POST['mapcycle_id']) && intval($_POST['mapcycle_id']) > 0 && $user instanceof User && $user->getCan_approve_ents()) {
        $duplicatable = new Mapcycle(intval($_POST['mapcycle_id']));
        $duplicatable = $duplicatable->populate();
        
        if ($duplicatable === null || !($duplicatable instanceof Mapcycle)) {
            $output['error'] = "Cannot duplicate, because mapcycle was not found.";
        } else {
            $entityMaps = EntityMap::getOrderedEntities($duplicatable);
            $newMapcycle = $duplicatable->setMapcycle_id(null)->setMapcycle_status(Mapcycle::$MAPCYCLE_NOTAPPROVED)->setMapcycle_creator_ip(getUserIP())->setMapcycle_creator_user_id($user->getMember_id())->write();
            
            if ($newMapcycle === null || !($newMapcycle instanceof Mapcycle)) {
                $output['error'] = "Generating new mapcycle failed.";
            } else {
                if ($entityMaps !== null && is_array($entityMaps) && sizeof($entityMaps) > 0) {
                    foreach ($entityMaps as $entityMap) {
                        $cvarMap = CvarMap::getCvarMapByEntityMap($entityMap);
                        $newMap = $entityMap->setAdded_by($user->getMember_id())->setAdded_by_ip(getUserIP())->setEntitymap_id(null)->setMapcycle_id($newMapcycle->getMapcycle_id())->write();
                        if ($newMap === null || !($newMap instanceof EntityMap)) {
                            $output['error'] = "Entitymap generation failed.";
                            break;
                        } else if ($cvarMap !== null && is_array($cvarMap) && sizeof($cvarMap) > 0) {
                            foreach ($cvarMap as $cmap) {
                                $newCmap = $cmap->setCvar_map_id(null)->setEntitymap_id($newMap->getEntitymap_id())->write();
                                
                            }
                        }
                    }
                }
                if (!array_key_exists("error", $output)) {
                    $output['msg'] = "New mapcycle generated.";
                }
            }
            
        }
        echo json_encode($output);
    } else if (isset($_POST['getEntitymapCvarForm'], $_POST['entitymap_id']) && intval($_POST['entitymap_id']) > 0 && $user instanceof User && $user->getCan_approve_ents()) {
        $entitymap = new EntityMap(intval($_POST['entitymap_id']));
        $entitymap = $entitymap->populate();
        if ($entitymap === null || !($entitymap instanceof EntityMap)) {
            $output['error'] = "Entitymap not found.";
        } else {
            $form = new Form();
            $form->setForm_id("entitymap_cvars");
            $form->setSubmit_btn_text("Save");
            $form->setInclude_button(false);
            $form->setForm_method("POST");
            $form->addFormObject(new FormObject("input", "cvar_entitymap_id", "hidden", null, null, false, $entitymap->getEntitymap_id()));
            $form->addFormObject(new FormObject("input", "setCvarsToEntityMap", "hidden", null, null, false, 1));
            $cvarMaps = CvarMap::getCvarMapByEntityMap($entitymap);
            $existingConvars = array();
            if ($cvarMaps !== null && is_array($cvarMaps) && sizeof($cvarMaps) > 0) {
                foreach ($cvarMaps as $cvarMap) {
                    $cvar = $cvarMap->getCvar();
                    $form->addFormObject(Cvar::getCvarFormObject($cvar));
                    $existingConvars[] = $cvar->getCvar_name();
                }
            } else {
                $cvar = Cvar::findCvarByCvarName("g_motd"); //basic cvar which every map should have.
                $form->addFormObject(Cvar::getCvarFormObject($cvar));
                $existingConvars[] = "g_motd";
            }
            
            $addNew = new SelectObject("addNewCvar", "required data-toggle='select2'", "Add another cvar");
            
            $cvarList = Cvar::getCvarsWithExclusion($existingConvars);
            if (sizeof($cvarList) > 0) {
                foreach ($cvarList as $cvl) {
                    $addNew->appendSelect_options(new SelectOption($cvl->getCvar_name() . " - " . $cvl->getCvar_friendly_name(), $cvl->getCvar_name()));
                }
            }
            $form->addText("<div id='newCvarSection'>");
            $form->addFormObject($addNew);
            $form->addText("<button type='button' class='btn btn-block btn-outline-primary' onclick='addCvar();'>Add cvar</button>");
            $form->addText("</div>");
            $output['html'] = $form . "";
            $output['existingConvars'] = $existingConvars;
            
        }
        echo json_encode($output);
    } else if (isset($_POST['getAdditionalCvars'], $_POST['existingConvars']) && $user instanceof User && $user->getCan_approve_ents()) {
        $notInString = "";
        
    } else if (isset($_POST['addConvarToForm']) && $user instanceof User && $user->getCan_approve_ents()) {
        $convar = trim($_POST['addConvarToForm']);
        $currentExclusion = $_POST['existingConvars'];
        $currentExclusion[] = $convar;
        $obj = Cvar::findCvarByCvarName($convar);
        if ($obj === null || !($obj instanceof Cvar)) {
            $output['error'] = "Cvar not found";
        } else {
            $output['html'] = Cvar::getCvarFormObject($obj) . "";
            $output['existingConvars'] = $currentExclusion;
        }
        echo json_encode($output);
    } else if (isset($_POST['setCvarsToEntityMap'], $_POST['cvar_entitymap_id']) && intval($_POST['cvar_entitymap_id']) > 0 && $user instanceof User && $user->getCan_approve_ents()) {
        $existingCvars = $_POST['existingConvars'];
        $serialized = array_diff($_POST, $existingCvars);
        if (!isset($serialized['cvar_entitymap_id']) || intval($serialized['cvar_entitymap_id']) === 0) {
            $output['error'] = "No entitymap_id found.";
        } else {
            $entitymap = new EntityMap(intval($serialized['cvar_entitymap_id']));
            $entitymap = $entitymap->populate();
            $output = array();
            if ($entitymap === null || !($entitymap instanceof EntityMap)) {
                $output['error'] = "Entitymap not found.";
            } else {
                $mc = $entitymap->getMapcycle();
                if ($mc === null || !($mc instanceof Mapcycle)) {
                    $output['error'] = "Mapcycle not found.";
                } else if (!$user->getCan_approve_mapcycle() && intval($user->getMember_id()) !== intval($mc->getMapcycle_creator_user_id())) {
                    $output['error'] = "You cannot manage this mapcycle.";
                } else if ($mc->isApproved() && !$user->getCan_approve_mapcycle()) {
                    $output['error'] = "You cannot manage an approved mapcycle.";
                } else {
                    //clear out old cvarmap.
                    CvarMap::deleteCvarMapByEntitymap($entitymap);
                    $inclusionCvars = Cvar::getCvarsWithInclusion($serialized, true);

                    foreach ($inclusionCvars as $icv) {
                        if (in_array($icv->getCvar_name(), $existingCvars)) {
                            $existingCvars = array_diff($existingCvars, array($icv->getCvar_name()));
                        }
                        $resp = $serialized[$icv->getCvar_name()];

                        if (is_array($resp)) {
                            //select2 objects have multiple clauses.
                            $tmpVal = "";
                            switch (trim($icv->getCvar_name())) {
                                case "availableWeapons":
                                    $tmpVal = Cvar::generateAvailableWeapons($resp);
                                    break;
                                case "hideSeek_Weapons":
                                    $tmpVal = Cvar::generateHSWeapon($resp);
                                    break;
                                case "hideSeek_Nades":
                                    $tmpVal = Cvar::generateHSNade($resp);
                                    break;
                                case "hideSeek_Extra":
                                    $tmpVal = Cvar::generateHSExtra($resp);
                                    break;
                                case "dmflags":
                                    $dmflagint = 0;
                                    if (sizeof($resp) > 0) {
                                        foreach ($resp as $rsp) {
                                            $dmflagint += intval($rsp);
                                        }
                                    } 
                                    $tmpVal = $dmflagint . "";
                                    break;
                            }


                            if (trim($icv->getCvar_value()) !== trim($tmpVal)) {
                                //$icv->setCvar_id(null)->setCvar_value(trim($tmpVal))->write();
                                $icv = Cvar::findOrCreateCvar($icv->getCvar_name(), trim($tmpVal));
                            }

                        } else {
                            if (trim($icv->getCvar_value()) !== trim($resp)) {
                                //$icv->setCvar_id(null)->setCvar_value(trim($resp))->write();
                                $icv = Cvar::findOrCreateCvar($icv->getCvar_name(), trim($resp));
                            }
                        }

                        $cvarMap = new CvarMap(null, $icv->getCvar_id(), $entitymap->getEntitymap_id());
                        $cvarMap->write();
                    }
                    
                    if (is_array($existingCvars) && sizeof($existingCvars) > 0) {
                        foreach ($existingCvars as $unmappedCvars) {
                            //the leftovers (select2 multiples which were posted without values).
                            switch (trim($unmappedCvars)) {
                                case "availableWeapons":
                                    $tmpVal = Cvar::generateAvailableWeapons(array(), true);
                                    break;
                                case "hideSeek_Weapons":
                                    $tmpVal = Cvar::generateHSWeapon(array(), true);
                                    break;
                                case "hideSeek_Nades":
                                    $tmpVal = Cvar::generateHSNade(array(), true);
                                    break;
                                case "hideSeek_Extra":
                                    $tmpVal = Cvar::generateHSExtra(array(), true);
                                    break;
                                case "dmflags":
                                    $tmpVal = "0";
                                    break;
                            }
                            $cvar = Cvar::findOrCreateCvar(trim($unmappedCvars), $tmpVal);
                            if ($cvar !== null && $cvar instanceof Cvar) {
                                $cvarMap = new CvarMap(null, $cvar->getCvar_id(), $entitymap->getEntitymap_id());
                                $cvarMap->write();
                            }
                        }
                        
                    }

                    $output['msg'] = "Cvars registered successfully.";

                }
            }
        }
        echo json_encode($output);
        
    } else if (isset($_POST['deleteMapcycle'], $_POST['deleteMapcycle_id']) && intval($_POST['deleteMapcycle_id']) > 0 && $user instanceof User && $user->getCan_approve_mapcycle()) {
        $mapcycle = new Mapcycle(intval($_POST['deleteMapcycle_id']));
        $mapcycle = $mapcycle->populate();
        
        if ($mapcycle === null || !($mapcycle instanceof Mapcycle)) {
            $output['error'] = "Mapcycle not found.";
        } else if (!$mapcycle->isNotApproved()) {
            $output['error'] = "Only not approved mapcycles can be deleted. Revert the approval and try again.";
        } else {
            $mapcycle->setMapcycle_status(Mapcycle::$MAPCYCLE_DELETED)->write();
            $output['info'] = "Mapcycle status changed. Going back to mapcycle overview page.";
            $output['href'] = Constants::$PAGE_URL . "/maps/mapcycle";
            $output['msg'] = "Mapcycle status changed.";
        }
        
        echo json_encode($output);
    } else if (isset($_POST['finalizeMapcycle'], $_POST['finalizeMapcycle_mapcycle_id'], $_POST['finalizeMapcycle_mapcycle_description']) && intval($_POST['finalizeMapcycle_mapcycle_id']) > 0 && strlen(trim($_POST['finalizeMapcycle_mapcycle_description'])) > 0 && $user instanceof User && $user->getCan_approve_mapcycle()) {
        $mapcycle = new Mapcycle(intval($_POST['finalizeMapcycle_mapcycle_id']));
        $mapcycle = $mapcycle->populate();
        
        if ($mapcycle === null || !($mapcycle instanceof Mapcycle)) {
            $output['error'] = "Mapcycle not found.";
        } else if (!$mapcycle->isNotApproved()) {
            $output['error'] = "Can't finalize mapcycle which doesn't have the status Not approved";
        } else {
            $entityMaps = EntityMap::getOrderedEntities($mapcycle);
            if ($entityMaps === null || !is_array($entityMaps) || sizeof($entityMaps) === 0) {
                $output['error'] = "No entitymaps found or an empty array found.";
            } else {
                $prevCvars = array();
                for ($i = 0; $i < sizeof($entityMaps); $i++) {
                    $entmap = $entityMaps[$i];
                    if ($i === 0) {
                        if ($entmap->isAltmap()) {
                            $output['error'] = "First map cannot be an altmap. Change the ordering of the entities in the mapcycle management.";
                            break;
                        }
                    }
                    $prevCvars = Cvar::redundancyCheck($entmap, $prevCvars);
                }
                
                if (!array_key_exists("error", $output)) {
                    $output['msg'] = "Mapcycle finalized.";
                    $mapcycle->setMapcycle_status(Mapcycle::$MAPCYCLE_APPROVED)
                            ->setMapcycle_status_change_by($user->getMember_id())
                            ->setMapcycle_status_change_by_ip(getUserIP())
                            ->setMapcycle_description(trim($_POST['finalizeMapcycle_mapcycle_description']))
                            ->write();
                }
            }
        }
        
        echo json_encode($output);
    } else if (isset($_POST['getApprovedMCsTable']) && $user instanceof User && $user->getCan_approve_ents()) {
        $mcs = Mapcycle::getApprovedMapcycles();
        
        $output = array();
        if ($mcs !== null && is_array($mcs) && sizeof($mcs) > 0) {
            foreach ($mcs as $mc) {
                $creator = XenForo::getUsernameByID(Constants::getXenSQL(), $mc->getMapcycle_creator_user_id());
                $options = "";
                if ($user->getCan_approve_mapcycle()) {
                    $creator .= " (ip: " . $mc->getMapcycle_creator_ip() . ")";
                }
                $siteurl = Constants::$PAGE_URL;
                
                if ($user->getCan_approve_mapcycle() || intval($mc->getMapcycle_creator_user_id()) === intval($user->getMember_id())) {
                    $options .= "<div class='btn-group d-flex'>";
                    $options .= "<button class='btn btn-outline-info btn-sm w-100' type='button' onclick='window.location = \"$siteurl/maps/mapcycle/{$mc->getMapcycle_id()}\"';>Manage this mapcycle</button>";
                if ($user->getCan_approve_mapcycle()) {
                    $options .= "<button class='btn btn-outline-danger btn-sm w-100' type='button' onclick='unfinalizeMapcycle({$mc->getMapcycle_id()});'>Unapprove mapcycle</button>";
                }
                    $options .= "</div><br>";
                }
                
                
                
                $options .= "<button class='btn btn-outline-dark btn-sm w-100' type='button' onclick='duplicateMapcycle({$mc->getMapcycle_id()});'>Duplicate this mapcycle</button>";
                
                $description = nl2br(htmlentities($mc->getMapcycle_description()));
                $maps = "";
                $em = EntityMap::getOrderedEntities($mc);
                $canApproveMapcycle = true;
                if ($em !== null && is_array($em) && sizeof($em) > 0) {
                    $maps = sizeof($em) . " maps connected";
                    foreach ($em as $emap) {
                        $cvarMap = CvarMap::getCvarMapByEntityMap($emap);
                        if ($cvarMap !== null && is_array($cvarMap) && sizeof($cvarMap) > 0 && $canApproveMapcycle) {
                            
                        } else {
                            $canApproveMapcycle = false;
                        }
                    }
                    
                    if ($canApproveMapcycle && $user->getCan_approve_mapcycle()) {
                        $options .= "<br><br><div class='btn-group d-flex'><button class='btn btn-outline-success btn-sm w-100' type='button' onclick='ptl({$mc->getMapcycle_id()});'>Add push to live dates</button><button class='btn btn-outline-warning btn-sm w-100' type='button' onclick='manageptl({$mc->getMapcycle_id()});'>Manage push-to-lives</button></div>";
                        $options .= "<br><br><button class='btn btn-outline-info btn-sm w-100' type='button' onclick='pushToPreProd({$mc->getMapcycle_id()});'>Push mapcycle to preprod</button>";
                    }
                    
                } else {
                    $maps = "0 maps connected";
                }
                
                if (!array_key_exists('approved', $output)) {
                    $output['approved'] = "";
                }
                
                $ptls = "";
                $ptlNum = 0;
                $ptlArr = PushToLive::getPushtoliveByMapcycleId($mc->getMapcycle_id());
                
                if ($ptlArr !== null && is_array($ptlArr) && sizeof($ptlArr) > 0) {
                    $ptlNum = sizeof($ptlArr);
                }
                
                $ptls = "This mapcycle has $ptlNum active push-to-lives registered in total.";
                
                $output['approved'] .= "<tr><td>$creator</td><td>$description</td><td>$maps</td><td>$ptls</td><td>$options</td></tr>";
            }
        } else {
            $output['approved'] = "<tr><td colspan=5>No mapcycles to show</td></tr>";
        }
        echo json_encode($output);
    } else if (isset($_POST['unfinalizeMapcycle'], $_POST['unfinalize_mc_id']) && intval($_POST['unfinalize_mc_id']) > 0 && $user instanceof User && $user->getCan_approve_mapcycle()) {
        $mapcycle = new Mapcycle(intval($_POST['unfinalize_mc_id']));
        $mapcycle = $mapcycle->populate();
        
        if ($mapcycle === null || !($mapcycle instanceof Mapcycle)) {
            $output['error'] = "Mapcycle not found.";
        } else {
            $currlive = PushToLive::getLiveMapcycle();
            
            if ($currlive !== null && $currlive instanceof PushToLive) {
                if (intval($currlive->getMapcycle_id()) === intval($mapcycle->getMapcycle_id())) {
                    $output['error'] = "This mapcycle is currently live. Remove it from live or add another mapcycle to unapprove it.";
                } 
            } 
            
            $ptls = PushToLive::getPushtoliveByMapcycleId($mapcycle->getMapcycle_id());
            
            if ($ptls !== null && is_array($ptls) && sizeof($ptls) > 0) {
                foreach ($ptls as $ptl) {
                    $ptl->delete();
                }
            }
            
            $mapcycle->setMapcycle_status(Mapcycle::$MAPCYCLE_NOTAPPROVED)->setMapcycle_status_change_by($user->getMember_id())->setMapcycle_status_change_by_ip(getUserIP())->write();
            $output['msg'] = "Mapcycle status changed.";
            
        }
        
        
        echo json_encode($output);
    } else if (isset($_POST['pushToPreProd'], $_POST['preprod_mc_id']) && intval($_POST['preprod_mc_id']) > 0 && $user instanceof User && $user->getCan_approve_mapcycle()) {
        $mapcycle = new Mapcycle(intval($_POST['preprod_mc_id']));
        $mapcycle = $mapcycle->populate();
        
        if ($mapcycle === null || !($mapcycle instanceof Mapcycle)) {
            $output['error'] = "Mapcycle not found";
        } else {
            $mctext = "";
            try {
                $mctext = $mapcycle->compile();
                if (strlen($mctext) > Constants::$MC_FILE_MAXSIZE) {
                    $output['error'] = "The mapcycle file exceeds the maximum size of " . Constants::$MC_FILE_MAXSIZE . " bytes (MC size is " . strlen($mctext) . " bytes). Remove some maps and try again.";
                } else {
                    $output = PushToLive::pushToPreProd($mapcycle);
                }
            } catch (Exception $ex) {
                $output['error'] = $ex->getMessage();
            }
        }
        
        echo json_encode($output);
    } else if (isset($_POST['insertPtl'], $_POST['editPtl'], $_POST['ptl_mapcycle_id'], $_POST['liveFrom_dt'], $_POST['liveTo_dt']) && $user instanceof User && $user->getCan_approve_mapcycle()) {
        $output = array();
        $lfdt = DateTime::createFromFormat("Y-m-d H:i:s", trim($_POST['liveFrom_dt']));
        $ltdt = DateTime::createFromFormat("Y-m-d H:i:s", trim($_POST['liveTo_dt']));
        
        if ($ltdt <= $lfdt) {
            $output['error'] = "liveTo date is equal or before liveFrom date, sir donkey kong";
        } else {
            if (intval($_POST['insertPtl']) === 1) {
                $mapcycle = new Mapcycle(intval($_POST['ptl_mapcycle_id']));
                $mapcycle = $mapcycle->populate();
                
                if ($mapcycle === null || !($mapcycle instanceof Mapcycle) || !$mapcycle->isApproved()) {
                    $output['error'] = "Mapcycle not found or it is not approved.";
                } else if (!isset($_POST['recurrence']) || intval($_POST['recurrence']) <= 0) {
                    $collisions = PushToLive::checkCollision($lfdt->format("Y-m-d H:i:s"), $ltdt->format("Y-m-d H:i:s"));
                    
                    if ($collisions !== null && is_array($collisions) && sizeof($collisions) > 0) {
                        $output['error'] = "Cannot push to live, because the dates are colliding with other Push-To-Lives.";
                        
                    } else {
                    
                        $ptl = new PushToLive();
                        $ptl
                                ->setLive_from($lfdt->format("Y-m-d H:i:s"))
                                ->setLive_to($ltdt->format("Y-m-d H:i:s"))
                                ->setMapcycle_id($mapcycle->getMapcycle_id())
                                ->setPush_created_by($user->getMember_id())
                                ->setPush_created_by_ip(getUserIP())
                                ->write();
                        $output['msg'] = "Push dates registered.";
                    }
                } else {
                    if (intval($_POST['recurrence']) > 30) {
                        $output['error'] = "Recurrence larger than 30.";
                    } else if ($lfdt->format("Y-m-d") !== $ltdt->format("Y-m-d")) {
                        $output['error'] = "When using recurrence, the dates must match.";
                    } else {
                        for ($i = 0; $i < intval($_POST['recurrence']); $i++) {
                            if ($i !== 0) {
                                $lfdt->modify("+1 day");
                                $ltdt->modify("+1 day");
                            }
                            $collisions = PushToLive::checkCollision($lfdt->format("Y-m-d H:i:s"), $ltdt->format("Y-m-d H:i:s"));

                            if ($collisions !== null && is_array($collisions) && sizeof($collisions) > 0) {
                                $output['error'] .= "Cannot push recurrence $i live, because the dates are colliding with other Push-To-Lives.<br>";

                            } else {

                                $ptl = new PushToLive();
                                $ptl
                                        ->setLive_from($lfdt->format("Y-m-d H:i:s"))
                                        ->setLive_to($ltdt->format("Y-m-d H:i:s"))
                                        ->setMapcycle_id($mapcycle->getMapcycle_id())
                                        ->setPush_created_by($user->getMember_id())
                                        ->setPush_created_by_ip(getUserIP())
                                        ->write();
                                $output['msg'] = "Push dates registered.";
                            }
                        }
                        
                    }
                }
            } else {
                $ptl = new PushToLive(intval($_POST['editPtl']));
                $ptl = $ptl->populate();
                
                if ($ptl === null || !($ptl instanceof PushToLive) || $ptl->isDeleted()) {
                    $output['error'] = "PushToLive not found or it is deleted.";
                } else if ($ptl->isDeleted()) {
                    $output['error'] = "Can't update PTL if it's deleted.";
                
                } else {
                    $collisions = PushToLive::checkCollisionWithExclusion($ptl, $lfdt->format("Y-m-d H:i:s"), $ltdt->format("Y-m-d H:i:s"));
                    
                    if ($collisions !== null && is_array($collisions) && sizeof($collisions) > 0) {
                        $output['error'] = "Cannot change push to live, because the dates are colliding with other Push-To-Lives";
                    } else {
                        $ptl
                            ->setLive_from($lfdt->format("Y-m-d H:i:s"))
                            ->setLive_to($ltdt->format("Y-m-d H:i:s"))
                            ->setPush_created_by($user->getMember_id())
                            ->setPush_created_by_ip(getUserIP())
                            ->write();
                        $output['msg'] = "Push dates updated.";
                        $output['mc_id'] = $ptl->getMapcycle_id();
                    }
                    
                    
                }
            }
        }
        
        echo json_encode($output);
    } else if (isset($_POST['getPtlTable'], $_POST['ptl_mapcycle_id']) && intval($_POST['ptl_mapcycle_id']) > 0 && $user instanceof User && $user->getCan_approve_mapcycle()) {
        $mapcycle = new Mapcycle(intval($_POST['ptl_mapcycle_id']));
        $mapcycle = $mapcycle->populate();
        
        if ($mapcycle === null || !($mapcycle instanceof Mapcycle)) {
            $output['error'] = "Mapcycle not found.";
        } else {
            $ptls = PushToLive::getPushtoliveByMapcycleId($mapcycle->getMapcycle_id());
            $output['ptltable'] = "";
            $found = false;
            
            if ($ptls !== null && is_array($ptls) && sizeof($ptls) > 0) {
                
                foreach ($ptls as $ptl) {
                    $found = true;
                    $pusher = XenForo::getUsernameByID(Constants::getXenSQL(), $ptl->getPush_created_by()) . " (ip: " . $ptl->getPush_created_by_ip() . ")";
                    $pushedat = $ptl->getPush_created_at();
                    $liveFromTo = "From: " . $ptl->getLive_from() . "<br>To: " . $ptl->getLive_to();
                    $status = "";
                    $options = "";
                    if ($ptl->isDeleted()) {
                        $status = "Deleted";
                    } else if ($ptl->isLive()) {
                        $status = "Currently live";
                        $options = "<button type='button' class='btn btn-outline-info btn-sm btn-block' onclick='editPtl({$ptl->getPushtolive_id()}, \"{$ptl->getLive_from()}\", \"{$ptl->getLive_to()}\");'>Modify PTL</button><br><button type='button' class='btn btn-outline-danger btn-sm btn-block' onclick='deletePtl({$ptl->getPushtolive_id()});'>Delete</button>";
                    } else {
                        $status = "Registered";
                        $options = "<button type='button' class='btn btn-outline-info btn-sm btn-block' onclick='editPtl({$ptl->getPushtolive_id()}, \"{$ptl->getLive_from()}\", \"{$ptl->getLive_to()}\");'>Modify PTL</button><br><button type='button' class='btn btn-outline-danger btn-sm btn-block' onclick='deletePtl({$ptl->getPushtolive_id()});'>Delete</button>";
                    }
                    
                    $output['ptltable'] .= "<tr><td>$pusher</td><td>$pushedat</td><td>$liveFromTo</td><td>$status</td><td>$options</td></tr>";
                }
            }
            
            if (!$found) {
                $output['ptltable'] = "<tr><td colspan=5>No Push-To-Live's found</td></tr>";
            }
            
        }
        
        
        echo json_encode($output);
    } else if (isset($_POST['deletePtl'], $_POST['ptl_id']) && intval($_POST['ptl_id']) > 0 && $user instanceof User && $user->getCan_approve_mapcycle()) {
        $ptl = new PushToLive(intval($_POST['ptl_id']));
        $ptl = $ptl->populate();
        
       if ($ptl === null || !($ptl instanceof PushToLive)) {
           $output['error'] = "PTL not found.";
       } else {
           $ptl->setDeleted(1)->write();
           $output['msg'] = "PTL deleted";
           $output['mc_id'] = $ptl->getMapcycle_id();
       }
       
       
       echo json_encode($output);
        
    } else if (isset($_POST['resendLive']) && $user instanceof User && $user->getCan_approve_mapcycle()) {
        $currlive = PushToLive::getLiveMapcycle();
        if ($currlive !== null && $currlive instanceof PushToLive) {
            $currlive->setLive(0)->write();
            $output['msg'] = "Current live mapcycle registered to be resent to prod server.";
        } else {
            $output['error'] = "Couldn't retrigger live, as didn't find current live.";
        }
        
        echo json_encode($output);
    } else if (isset($_POST['getPTLOutlook']) && $user instanceof User && $user->getCan_approve_ents()) {
        $ptlOutlook = PushToLive::getPushToLiveOutlook();
        $output['ptloutlook'] = "";
        $found = false;
        if ($ptlOutlook !== null && is_array($ptlOutlook) && sizeof($ptlOutlook) > 0) {
            $found = true;
            
            foreach ($ptlOutlook as $ptl) {
                $pusher = XenForo::getUsernameByID(Constants::getXenSQL(), $ptl->getPush_created_by());
                $pushedat = $ptl->getPush_created_at();
                $mapcycle = new Mapcycle($ptl->getMapcycle_id());
                $mapcycle = $mapcycle->populate();
                $mcdesc = "";
                
                if ($mapcycle !== null && $mapcycle instanceof Mapcycle) {
                    $mcdesc = $mapcycle->getMapcycle_description();
                }
                
                $liveFromTo = "From: " . $ptl->getLive_from() . "<br>To: " . $ptl->getLive_to();
                $status = "";
                $opts = "<button onclick=\"location.href='" . Constants::$PAGE_URL . "/maps/mapcycle/" . $ptl->getMapcycle_id() . "';\" class='btn btn-outline-primary btn-block btn-sm'>Manage mapcycle</button>";
                if ($ptl->isLive()) {
                    $status = "Live";
                } else {
                    $status = "Registered";
                }
                
                if ($user->getCan_approve_mapcycle()) {
                    $pusher .= " (ip: " . $ptl->getPush_created_by_ip() . ")";
                    $opts .= "<br><div class='btn-group d-flex'><button type='button' class='btn btn-outline-info btn-sm btn-block' onclick='editPtl({$ptl->getPushtolive_id()}, \"{$ptl->getLive_from()}\", \"{$ptl->getLive_to()}\");'>Modify PTL</button><br><button type='button' class='btn btn-outline-danger btn-sm btn-block' onclick='deletePtl({$ptl->getPushtolive_id()});'>Delete</button></div>";
                }
                $output['ptloutlook'] .= "<tr><td>$mcdesc</td><td>$pusher</td><td>$pushedat</td><td>$liveFromTo</td><td>$status</td><td>$opts</td></tr>";
            }
        }
        
        if (!$found) {
            $output['ptloutlook'] = "<tr><td colspan=5>No Push-to-lives found</td></tr>";
        }
        
        
        echo json_encode($output);
    } else if (isset($_POST['getEntMismatch'], $_POST['entity']) && $user instanceof User) {
        $ent = trim($_POST['entity']);
        $data = openCloseAnalyzer($ent);
        $returnable = array();
        $returnable['html'] = "";
        if (sizeof($data) !== 0) {
            foreach ($data as $anl) {
                $returnable['html'] .= "Possible problems:<br>Line: " . $anl['line'] . " (saw character <b>" . $anl['seenChar'] . "</b> , expecting <b>" . $anl['expectChar'] . "</b> ) <br>";
            }
        }
        
        echo json_encode($returnable);
    } else if (isset($_POST['pushEntityToPreprod']) && intval($_POST['pushEntityToPreprod']) > 0 && $user instanceof User && $user->getCan_approve_ents()) {
        $entity_id = intval($_POST['pushEntityToPreprod']);
        $entity = new Entity($entity_id);
        $entity = $entity->populate();
        $output = array();
        if ($entity === null || !($entity instanceof Entity)) {
            $output['error'] = "Entity not found...?";
        } else {
            //just pushing to h&s folder.
            $output = PushToLive::pushEntityToPreprod($entity);
        }
        
        echo json_encode($output);
    }
    
    
    die();
}

$router->map("GET|POST", "/ajax", function() {
    handleAjax();
});

$router->map("GET", "/ajax/getEntityDownload/[i:entity_id]", function($entity_id) {
    global $user;
    if ($user !== null && $user instanceof User && $user->getCan_approve_ents()) {
        $entity_id = intval($entity_id);
        if ($entity_id !== 0) {
            $entity = new Entity($entity_id);
            $entity = $entity->populate();
            if ($entity !== null && $entity instanceof Entity) {
                echo $entity->getMap_entity();
                die();
            } else {
                echo "{entity === null || !instanceof Entity}";
            }
        } else {
            echo "{entity_id === 0}";
        }
    } else {
        echo "{nice try :) }";
    }
    die();
});

$router->map("POST", "/analyze", function() {
    die(print_r(openCloseAnalyzer($_POST['ent'])));
});

$router->map("GET", "/getImages/[i:entity_id]", function($entity_id) {
    if (intval($entity_id) > 0) {
        $entity_id = intval($entity_id);
        $entity = new Entity($entity_id);
        $entity = $entity->populate();
        if ($entity === null) {
            die();
        } else {
            $imgurlinks = unserialize($entity->getImgur_links());
            $arr = null;
            foreach ($imgurlinks as $i => $link) {
                $arr[] = $link;
            }
            die(json_encode($arr));
        }
    } else {
        die();
    }
});

$router->map('GET|POST', "/", function() {
    require_once __DIR__ . "/views/home.php";
});

$router->map("GET|POST", "/maps/entities", function() {
    global $error, $errorstr, $success, $succstr, $user, $loginOut;
    newEntity();
    modifyEntity();
    require_once __DIR__ . "/views/ents.php";
});

$router->map("GET|POST", "/maps/entities/[:uploaded_by]/[:filter]", function($uploaded_by, $filter) {
    global $error, $errorstr, $success, $succstr, $user, $loginOut;
    newEntity();
    require_once __DIR__ . "/views/ents.php";
});

$router->map("GET|POST", "/maps/entities/[:uploaded_by]/", function($uploaded_by) {
    global $error, $errorstr, $success, $succstr, $user, $loginOut;
    newEntity();
    require_once __DIR__ . "/views/ents.php";
});


$router->map("GET|POST", "/maps/entities/approve", function() {
    global $error, $errorstr, $success, $succstr, $user, $loginOut;
    //make card based with ajax, approval deletes card, change updates card.
    newEntity(); //just in case we posted a new entity on approve page
    modifyEntity(); //incase we modified an entity.
    if (!$loginOut && $user instanceof User && $user->getCan_approve_ents()) {
        require_once __DIR__ . "/views/ents_appr.php";
    } else {
        require_once __DIR__ . "/views/home.php";
    }
    
});

$router->map("GET|POST", "/maps/mapcycle", function() {
    global $error, $errorstr, $success, $succstr, $user, $loginOut;
    
    if ($user !== null && !$loginOut && $user instanceof User && $user->getCan_approve_ents()) {
        require_once __DIR__ . "/views/mc.php";
    } else {
        throw new Exception("You do not have access to enter this page");
    }
    
});

$router->map("GET|POST", "/maps/mapcycle/[i:mapcycle_id]", function($mapcycle_id) {
    global $error, $errorstr, $success, $succstr, $user, $loginOut;
    
    if ($user !== null && !$loginOut && $user instanceof User && $user->getCan_approve_ents()) {
        require_once __DIR__ . "/views/mc_single.php";
    } else {
        throw new Exception("You do not have access to enter this page");
    }
    
});



$router->map("GET|POST", "/statistics", function() {
    require_once __DIR__ . "/views/hnsstat.php";
});
$router->map("GET|POST", "/statistics/", function() {
    require_once __DIR__ . "/views/hnsstat.php";
});

$router->map("GET|POST", "/entgen", function() {
    require_once __DIR__ . "/kawa_entgen/index.html";
    die();
});

$match = $router->match();

if ($match && is_callable($match['target'])) {
    call_user_func_array($match['target'], $match['params']);
} else {
    require_once __DIR__ . "/views/404.php";
}
