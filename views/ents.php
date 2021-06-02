<?php
global $user, $error, $errorstr, $success, $succstr;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$page = new Page();
if ($error) {
    $page->appendContent("<div class='alert alert-danger'>" . $errorstr . "</div>");
}

if ($success) {
    $page->appendContent("<div class='alert alert-success'>" . $succstr . "</div>");
}
$page->setTitle(Constants::$TOOL_NAME);

/*
if (isset($filter) || isset($uploaded_by)) {
    if (strlen(trim($filter)) === 0) {
        $filter = null;
    }
    
    if (intval($uploaded_by) === 0) {
        $uploaded_by = null;
    }
} else {
    $filter = null;
    $uploaded_by = null;
}
*/

$postfcreator = 0;
$postfapprover = 0;
$postfavgvote = 0;
$postfsort = 0;
$postfpageitms = 30;
$postfpage = 0;
$postfmap = null;
$postfnonapr = 0;
$postflivemc = 0;
$postfhideadd = 0;

if (isset($_POST['postfnonapr']) && intval(trim($_POST['postfnonapr'])) > 0) {
    $postfnonapr = intval(trim($_POST['postfnonapr']));
}

if (isset($_POST['postfapprover']) && intval(trim($_POST['postfapprover'])) > 0) {
    $postfapprover = intval(trim($_POST['postfapprover']));
}

if (isset($_POST['postflivemc']) && intval(trim($_POST['postflivemc'])) > 0) {
    $postflivemc = intval(trim($_POST['postflivemc']));
}
if (isset($_POST['postfhideadd']) && intval(trim($_POST['postfhideadd'])) > 0) {
    $postfhideadd = intval(trim($_POST['postfhideadd']));
}

if (int2bool($postfnonapr)) {
    $postfnonapr = " checked=checked ";
} else {
    $postfnonapr = "";
}

if (int2bool($postfhideadd)) {
    $postfhideadd = " checked=checked ";
} else {
    $postfhideadd = "";
}

if (int2bool($postflivemc)) {
    $postflivemc = " checked=checked ";
} else {
    $postflivemc = "";
}

if (isset($_POST['postfcreator']) && intval(trim($_POST['postfcreator'])) > 0) {
    $postfcreator = intval(trim($_POST['postfcreator']));
}

if (isset($_POST['postfavgvote']) && intval(trim($_POST['postfavgvote'])) >= 2 && intval(trim($_POST['postfavgvote'])) <= 5) {
    $postfavgvote = intval(trim($_POST['postfavgvote']));
}

if (isset($_POST['postfsort']) && intval(trim($_POST['postfsort'])) > 0 && intval(trim($_POST['postfsort'])) <= 6) {
    $postfsort = intval(trim($_POST['postfsort']));
}

if (isset($_POST['postfpageitms']) && intval(trim($_POST['postfpageitms'])) >= 15 && intval(trim($_POST['postfpageitms'])) <= 120) {
    $postfpageitms = intval(trim($_POST['postfpageitms']));
}

if (isset($_POST['postfpage']) && intval(trim($_POST['postfpage'])) > 0) {
    $postfpage = intval(trim($_POST['postfpage']));
}

if (isset($_POST['postfmap']) && strlen(trim($_POST['postfmap'])) > 0) {
    $postfmap = trim($_POST['postfmap']);
}


//$entities = Entity::getEntities(null, $filter, $uploaded_by);
if (!$user->isLoggedIn()) {
    $page->appendContent("<h5>To upload, vote and perform other actions with entities, please log in.</h5>");
}

$form = new Form();
$entityCreators = new SelectObject("uploaded_by", "", "Entity creator");
$creatorList = Entity::getEntityCreators();
$approverList = Entity::getEntityApprovers();
$entityCreators->appendSelect_options(new SelectOption("Pick a creator", 0, 0 === $postfcreator));
$entityApprovers = new SelectObject("approved_by", "", "Entity approver");
$entityApprovers->appendSelect_options(new SelectOption("Pick an approver", 0, 0 === $postfapprover));
foreach ($creatorList as $creator) {
    $entityCreators->appendSelect_options(
        new SelectOption($creator['name'] . " (" . $creator['ent_count'] . " approved entities uploaded)", $creator['uploaded_by'], intval($creator['uploaded_by']) === $postfcreator)
    );
}

foreach ($approverList as $creator) {
    $entityApprovers->appendSelect_options(
        new SelectOption($creator['name'] . " (" . $creator['ent_count'] . " entities approved)", $creator['uploaded_by'], intval($creator['uploaded_by']) === $postfapprover)
    );
}

$pageItems = new SelectObject("page_items", "", "Entities on page");

$pageItems
        ->appendSelect_options(new SelectOption("15", 15, 15 === $postfpageitms))
        ->appendSelect_options(new SelectOption("30", 30, 30 === $postfpageitms))
        ->appendSelect_options(new SelectOption("60", 60, 60 === $postfpageitms))
        ->appendSelect_options(new SelectOption("90", 90, 90 === $postfpageitms))
        ->appendSelect_options(new SelectOption("120", 120, 120 === $postfpageitms))
;

$sortBy = new SelectObject("sort_by", "", "Sort by");

$sortBy
        ->appendSelect_options(new SelectOption("Creation date, descending", 0, 0 === $postfsort))
        ->appendSelect_options(new SelectOption("Creation date, ascending", 1, 1 === $postfsort))
        ->appendSelect_options(new SelectOption("By map name, ascending", 2, 2 === $postfsort))
        ->appendSelect_options(new SelectOption("By map name, descending", 3, 3 === $postfsort))
        ->appendSelect_options(new SelectOption("By average vote, descending", 4, 4 === $postfsort))
        ->appendSelect_options(new SelectOption("By score (votes x avg vote), descending", 5, 5 === $postfsort))
        ->appendSelect_options(new SelectOption("By vote count, descending", 6, 6 === $postfsort))
;

$avgvote = new SelectObject("avg_vote_filter", "", "Filter by average vote");

$avgvote 
        ->appendSelect_options(new SelectOption("No filter", 0, 0 === $postfavgvote))
        ->appendSelect_options(new SelectOption("Higher or equal to 2 stars", 2, 2 === $postfavgvote))
        ->appendSelect_options(new SelectOption("Higher or equal to 3 stars", 3, 3 === $postfavgvote))
        ->appendSelect_options(new SelectOption("Higher or equal to 4 stars", 4, 4 === $postfavgvote))
        ->appendSelect_options(new SelectOption("5 stars", 5, 5 === $postfavgvote));

$form->setForm_id("filter_form");
$form->setForm_method("GET");
$form 
        ->addFormObject(new FormObject("input", "filter", "text", "", "Filter by map name", true, $postfmap))
        ->addFormObject($entityCreators)
        ->addFormObject($entityApprovers)
        ->addFormObject($avgvote)
        ->addFormObject($sortBy)
        ->addFormObject($pageItems)
        ->setSubmit_btn_text("Filter");
$page->appendContent($form);

if (isset($_SESSION['manageMapcycle']) && intval($_SESSION['manageMapcycle']) > 0) {
    $checked = $postfhideadd;
    
    if (isset($_SESSION['hideAdded']) && intval($_SESSION['hideAdded']) === 1) {
        $checked = "checked='checked'";
    }
    
    $page->appendContent("<h6><div class='form-check'><label class='form-check-label' for='hideAddedEnts'><input $checked type='checkbox' class='form-check-input' onclick='hideAddedEnts();' id='hideAddedEnts' name='hideAddedEnts'> Hide already added entities for current mapcycle</label></div></h6>");

} 

$page->appendContent("<h6><div class='form-check'><label class='form-check-label' for='showLiveMCEnts'><input $postflivemc type='checkbox' class='form-check-input' onclick='showLiveMCEnts();' id='showLiveMCEnts' name='showLiveMCEnts'> Show entities in the current live mapcycle</label></div></h6>");

if ($user->getCan_approve_ents()) {
    $page->appendContent("<h6><div class='form-check'><label class='form-check-label' for='showNotApprovedEnts'><input $postfnonapr type='checkbox' class='form-check-input' onclick='showNotApprovedEnts();' id='showNotApprovedEnts' name='showNotApprovedEnts'> Show entities which require approval</label></div></h6>");


}

$page->appendContent("<style>.checked{color:orange;}</style>");
$page->appendContent("<div class='row' id='entityCards'>");

$page->appendContent("<h4>Loading entities...</h4>");



$page->appendContent("</div>");
$page->appendContent("<br><br><div class='float-right' id='pagination'></div><br><br><br><br><br>");
$page->appendPostDivContent(<<<EOT
     <div id="showImgModal" role="dialog" aria-labelledby="showImgModalTitle" aria-hidden="true" class="modal fade">
            <div class="modal-dialog modal-lg" >
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 id="showImgModalTitle" class="modal-title"></h4>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        
                    </div>
                    <div class="modal-body" id="showImgModalBody">
<div id="carousel-indicator-list" class="carousel slide" data-ride="carousel">
  <ol class="carousel-indicators" id="carousel-indicator-list-items">
    
  </ol>
  <div class="carousel-inner" id='pictureCarousel'>
    
  </div>
  <a class="carousel-control-prev" href="#carousel-indicator-list" role="button" data-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="sr-only">Previous</span>
  </a>
  <a class="carousel-control-next" href="#carousel-indicator-list" role="button" data-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="sr-only">Next</span>
  </a>
</div>
   </div>
                    <div class="modal-footer">
                        
                            
        
        
                            
                                <button type="button" class="btn btn-default " data-dismiss="modal" >Close</button>
                            
                    </div>
                </div> </div>
            </div>
EOT
);


$login_txt_action = "";
$login_txt = "";
$navbar_extra = "";
$entityApprovedElement = "";
$form = new Form();
if ($user->isLoggedIn()) {
    $login_txt_action = "onclick='logout();'";
    $login_txt = "Hello, " . $user->getName() . ". Click here to log out.";
    $navbar_extra = "<span class='mr-auto'><span class='text-white headlink'>&nbsp;&nbsp;</span><a href='/maps/entities' class='text-white headlink'>Entities</a><span class='text-white headlink'>&nbsp;|&nbsp;</span><a href='#' class='text-white headlink' onclick='$(\"#entUpload\").modal();'>Upload new entity</a>";
    if ($user->getCan_approve_ents()) {
        $navbar_extra .= "";
        $slobj = new SelectObject("entity_approved", "required", "Entity status");
        $slobj->appendSelect_options(new SelectOption("Awaiting approval", 0, true))
                ->appendSelect_options(new SelectOption("Approved", 1))
                ->appendSelect_options(new SelectOption("Rejected", 2));
        $entityApprovedElement = $slobj . ""; //call magic function toString.
        $entityApprovedElement .= "<h5 class='text-danger'>By approving the entity, you will be held responsible if it crashes the server. You have to test the entity before approving!</h5>";
        $slobj2 = new SelectObject("entity_approved", "required", "Entity status");
        $slobj2->appendSelect_options(new SelectOption("Awaiting approval", 0))
                ->appendSelect_options(new SelectOption("Approved", 1, true))
                ->appendSelect_options(new SelectOption("Rejected", 2));
        $entityApprovedElement2 = $slobj2 . ""; //call magic function toString.
        $entityApprovedElement2 .= "<h5 class='text-danger'>By approving the entity, you will be held responsible if it crashes the server. You have to test the entity before approving!</h5>";
   
    
    }
    $navbar_extra .= "</span>";
} else {
    $login_txt_action = "onclick='$(\"#loginModal\").modal();'";
    $login_txt = "Log in";
    
}
$page
        ->registerReplaceableTag("{LOGIN_TXT_ACTION}", $login_txt_action)
        ->registerReplaceableTag("{LOGIN_TXT}", $login_txt)
        ->registerReplaceableTag("{NAVBAR_EXTRA}", $navbar_extra);




$page->appendPostDivContent(<<<EOT
   
   <div id="entUpload" role="dialog" aria-labelledby="entUploadTitle" aria-hidden="true" class="modal fade">
            <div class="modal-dialog modal-lg" >
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 id="entUploadTitle" class="modal-title">Upload new entity</h4>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        
                    </div>
                    <div class="modal-body" id="entUploadBody">
                        <form id="entityForm" role="form" method="post" enctype="multipart/form-data">
                            <input id='uploadent' type="hidden" name="uploadent" value="1">
                            <h5>Notice about entities</h5>
        <h6>By uploading an entity on this page, you give us irrevocable rights to use, modify and distribute the entity as we see fit.<br>The entity will be
            seen by the editors and the staff. <br>At first, the entity is in review status, which requires one of our editors / staff members to review the entity to see whether it fits for 3D# Hide&Seek<br>
                <br>For every entity, we require you to upload a minimum of <span style='color: red;'>1 picture(s)</span> which must be coming from the map, otherwise the entity will be rejected.<br>Continuous false-entries and rejections can lead up to a suspension on this platform.<br><br>
                    By uploading, you <u>are agreeing</u> to the conditions above.</h6>
                            <div class="form-group">
                                <label for="map_name">Map name</label>
                                <input id="map_name" type="text" name="map_name" class="form-control" required placeholder="Map name (e.g. mp_shop, finca1, pra4, ...)">
                            </div>
                            <div class="form-group">
                                <label for="map_description">Map description</label>
                                <textarea rows=5 id="map_description" class="form-control" name="map_description" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="map_entity">Map entity</label>
                                <textarea rows=5 id="map_entity" class="form-control" name="map_entity" required></textarea>
                            </div>
                            <h6 class='text-danger' style='display: none;' id='enterror'></h6>
                            <small>You need to enter the entity contents in here, not the file</small>
                            <div class="form-group">
                                <label for="imgs">Add images</label>
                                <input class="form-control form-control-file" accept="image/*" multiple="multiple" type="file" name="imgs[]" id="imgs">
                            </div>
                            $entityApprovedElement
                            <div class="form-group">
                                <button id="submitEntity" style='display: none;' type="submit" class="btn btn-default btn-block" >Submit</button>
                            </div>
                        </form>
                        
                        
                        </div>
                    <div class="modal-footer">
                        
                            
        <button type="button" class="btn btn-success mr-auto" onclick="$('#submitEntity').click();">Submit</button>
        
                            
                                <button type="button" class="btn btn-default " data-dismiss="modal" >Close</button>
                            
                    </div>
                </div> </div>
            </div>
EOT
   );
$pushmcform = new Form();
$pushmcform->setForm_id("pushmc_form");
$pushmcform->setForm_method("POST");
$pushmcform->setSubmit_btn_text("Push");
$gametypeSelect = new SelectObject("pushmc_gt", "required", "Gametype");
$gametypeSelect
        ->appendSelect_options(new SelectOption("Hide & Seek", "h&s", true))
        ->appendSelect_options(new SelectOption("Zombies", "h&z"))
        ->appendSelect_options(new SelectOption("Capture the Flag", "ctf"))
        ->appendSelect_options(new SelectOption("Deathmatch", "dm"))
        ->appendSelect_options(new SelectOption("Team Deathmatch", "tdm"))
        ->appendSelect_options(new SelectOption("Infiltration", "inf"))
        ->appendSelect_options(new SelectOption("Elimination", "elim"));

$pushmcform->addFormObject($gametypeSelect)
        ->addText("<div class='checkbox'><label for='pushmc_altmap'><input type='checkbox' name='pushmc_altmap' id='pushmc_altmap'> Should this map be added as altmap?</label></div>")
        ->addFormObject(new FormObject("input", "pushmc_entity_id", "hidden", null, null, false, 0))
        ->addFormObject(new FormObject("input", "pushEntityToMapcycle", "hidden", null, null, false, 1));

$page->appendPostDivContent(<<<EOT
        <div id="pushMC" role="dialog" aria-labelledby="pushMCTitle" aria-hidden="true" class="modal fade">
        
            <div class="modal-dialog modal-lg" >
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 id="pushMCTitle" class="modal-title">Push map to mapcycle</h4>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                            <span aria-hidden="true">&times;</span>
                        </button>

                    </div>
                    <div class="modal-body" id="pushMCBody">
                        <h6 id='spawnpointCount'></h6>
                        <h6>Please select the gametype and whether it's an altmap below.</h6>
                        $pushmcform
                        
        
                    </div>
                </div>
            </div>
        
        </div>
        
        
EOT
);

$page->appendPostDivContent(<<<EOT
   
   <div id="entModify" role="dialog" aria-labelledby="entModifyTitle" aria-hidden="true" class="modal fade">
            <div class="modal-dialog modal-lg" >
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 id="entModifyTitle" class="modal-title">Modify entity</h4>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        
                    </div>
                    <div class="modal-body" id="entModifyBody">
                        <form id="entModifyForm" role="form" method="post" enctype="multipart/form-data">
                            
                            <input id='modifyEnt' type="hidden" name="modifyEnt" value="0">
        <input id='postfcreator' type="hidden" name="postfcreator" value="0">
        <input id='postfavgvote' type="hidden" name="postfavgvote" value="0">
        <input id='postfsort' type="hidden" name="postfsort" value="0">
        <input id='postfpageitms' type="hidden" name="postfpageitms" value="0">
        <input id='postfpage' type="hidden" name="postfpage" value="0">
        <input id='postfmap' type="hidden" name="postfmap" value="0">
        <input id='postfhideadd' type="hidden" name="postfhideadd" value="0">
        <input id='postflivemc' type="hidden" name="postflivemc" value="0">
        <input id='postfnonapr' type="hidden" name="postfnonapr" value="0">
        <input id='postfapprover' type="hidden" name="postfapprover" value="0">
        
                            <div id="entity_metadata"></div>
                            <div class="form-group">
                                <label for="modify_map_name">Map name</label>
                                <input id="modify_map_name" type="text" name="modify_map_name" class="form-control" required placeholder="Map name (e.g. mp_shop, finca1, pra4, ...)">
                            </div>
                            <div class="form-group">
                                <label for="modify_map_description">Map description</label>
                                <textarea rows=5 id="modify_map_description" class="form-control" name="modify_map_description" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="modify_map_entity">Map entity</label>
                                <textarea rows=5 id="modify_map_entity" class="form-control" name="modify_map_entity" required></textarea>
                            </div>
                            <h6 class='text-danger' style='display: none;' id='enterror'></h6>
                            <small>You need to enter the entity contents in here, not the file</small>
                            <div class="form-group">
                                <label for="overwriteImgs"><input type="checkbox" id="overwriteImgs" name="overwriteImgs"> Overwrite images</label><br>
                                <label for="newimgs">Add images</label>
                                <input class="form-control form-control-file" accept="image/*" multiple="multiple" type="file" name="newimgs[]" id="newimgs">
                            </div>
                            <small>Overwriting deletes existing images, if you do not select that checkbox, it will append to the already existing images</small>
                            $entityApprovedElement2
                            <div class="form-group">
                                <button id="submitModifyEntity" style='display: none;' type="submit" class="btn btn-default btn-block" >Submit</button>
                            </div>
                        </form>
                        
                        
                        </div>
                    <div class="modal-footer">
                        
                            
        <button type="button" class="btn btn-success mr-auto" onclick="$('#submitModifyEntity').click();">Submit</button>
        
                            
                                <button type="button" class="btn btn-default " data-dismiss="modal" >Close</button>
                            
                    </div>
                </div> </div>
            </div>
        
        
        
        <div id="deleteEnt" role="dialog" aria-labelledby="deleteEntTitle" aria-hidden="true" class="modal fade">
        
            <div class="modal-dialog modal-lg" >
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 id="deleteEntTitle" class="modal-title">Delete entity</h4>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                            <span aria-hidden="true">&times;</span>
                        </button>

                    </div>
                    <div class="modal-body" id="deleteEntityBody">
                        <h5>Are you sure you want to delete the entity?</h5>
        
        <h6>Deleting an entity just sets the status as deleted, doesn't physically remove it, but it does remove it from all mapcycles onto which it is mapped already.<br>Entity can only be restored by anyone having SQL access</h6>
        
        <input type='hidden' id='delete_entity_id' value=0 name='delete_entity_id'>
        <div class='btn-group d-flex'><button type='button' class='btn btn-outline-danger w-100' onclick='realDelete();'>I'm sure, delete the entity</button><button type='button' class='btn btn-outline-success w-100' onclick='$("#deleteEnt").modal("toggle");'>Don't delete</button></div>
                        
        
                    </div>
                </div>
            </div>
        
        </div>
EOT
   );


if ($user->getCan_approve_ents()) {
    $page->appendAdditionalScript(<<<EOT
            <script>
  $(document).ready(function() {
            $("#newimgs").prettyFile({minFiles: 1, required: false});
        });
        
            function pushEntityToPreprod(entity_id) {
            
                $.post("{BASE_URL}/ajax", {pushEntityToPreprod: entity_id}, function(data) {
            
                    try {
                        data = JSON.parse(data);
            
                        if (typeof data.error !== "undefined") {
                            toastr.error(data.error);
                        } else if (typeof data.msg !== "undefined") {
                            toastr.success(data.msg);
                        }
                    } catch (e) {
                        toastr.error("Something went wrong");
                    }
                });
            
            }
            
        function modifyEntity(entity_id) {
        
            $("#modifyEnt").val(entity_id);
            var postfmap = $("#filter").val();
            var postfcreator = $("#uploaded_by").val();
            var postfavgvote = $("#avg_vote_filter").val();
            var postfsort = $("#sort_by").val();
            var postfpageitms = $("#page_items").val();
            var postfapprover = $("#approved_by").val();
            var postfhideadd = $("#hideAddedEnts").is(":checked");
            if (postfhideadd) {
                postfhideadd = 1;
            } else {
                postfhideadd = 0;
            }
            
            var postflivemc = $("#showLiveMCEnts").is(":checked");
            if (postflivemc) {
                postflivemc = 1;
            } else {
                postflivemc = 0;
            }
        
            var postfnonapr = $("#showNotApprovedEnts").is(":checked");
            if (postfnonapr) {
                postfnonapr = 1;
            } else {
                postfnonapr = 0;
            }
        
        
            $.post("{BASE_URL}/ajax", {getModifyEntity: entity_id}, function(data) {
                try {
                    data = JSON.parse(data);
        
                    if (typeof data.error !== "undefined") {
                        toastr.error(data.error);
                    } else {
                        $("#modifyEnt").val(data.entity_id);
                        $("#postfnonapr").val(postfnonapr);
                        $("#postflivemc").val(postflivemc);
                        $("#postfhideadd").val(postfhideadd);
                        $("#entity_metadata").val(data.entity_metadata);
                        $("#modify_map_name").val(data.modify_map_name);
                        $("#modify_map_description").val(data.modify_map_description);
                        $("#modify_map_entity").val(data.modify_map_entity);
                        $("#postfmap").val(postfmap);
                        $("#postfcreator").val(postfcreator);
                        $("#postfavgvote").val(postfavgvote);
                        $("#postfsort").val(postfsort);
                        $("#postfpageitms").val(postfpageitms);
                        $("#postfpage").val(currPage);
                        $("#postfapprover").val(postfapprover);
                        $("#entModify").modal('toggle');
                    }
                } catch (e) {
                    toastr.error("Something went wrong");
                }
        
            });
        
        }
             function toggleEntityDownload(entity_id, map_name) {
        
            fetch('{BASE_URL}/ajax/getEntityDownload/' + entity_id)
                .then(resp => resp.blob())
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;
                    a.download = map_name + ".ent";
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    
                    
                
                });
        
        }
            function deleteEntity(entity_id) {
                $("#delete_entity_id").val(entity_id);
                $("#deleteEnt").modal('toggle');
            }
            
            function realDelete() {
            
                var entity_id = $("#delete_entity_id").val();
                
                $.post("{BASE_URL}/ajax", {softDeleteEntity: 1, entity_id: entity_id}, function(data) {
            
                    try {
                        data = JSON.parse(data);
            
                        if (typeof data.error !== "undefined") {
                            toastr.error(data.error);
                        }
            
                        if (typeof data.msg !== "undefined") {
                            toastr.success(data.msg);
                            $("#deleteEnt").modal('toggle');
                            getEntityCards();
                        }
                    } catch (e) {
                        toastr.error("Something went wrong");
                    }
            
                });
            
            }
            
            </script>
            
EOT
);
}

$page->appendAdditionalScript(<<<EOT
        
        <script>
        
        
        function rateEntity(entity_id, vote) {
            $.post("{BASE_URL}/ajax", {rateEntity: 1, entity_id: entity_id, vote: vote}, function(data) {
                try {
                    data = JSON.parse(data);
                    if (typeof data.msg !== "undefined") {
                        toastr.success(data.msg);
                        $("#stars_" + entity_id).attr("onmouseleave", "handleMouseRating(" + entity_id + ", " + vote + ");");
                        handleMouseRating(entity_id, vote);
                    }
                } catch (e) {
                
                }
            });
        }
        
        function handleMouseRating(entity_id, star_count) {
            $("#1star_" + entity_id).removeClass('checked');
            $("#2star_" + entity_id).removeClass('checked');
            $("#3star_" + entity_id).removeClass('checked');
            $("#4star_" + entity_id).removeClass('checked');
            $("#5star_" + entity_id).removeClass('checked');
            for (var i = 1; i <= star_count; i++) {
                $("#" + i + "star_" + entity_id).addClass('checked');
            }
        }
        
        function getEntMismatchDetails() {
            var ent = $("#map_entity").val();
            $.post("{BASE_URL}/ajax", {getEntMismatch: 1, entity: ent}, function(data) {
                try {
                    data = JSON.parse(data);
        
                    if (typeof data.error !== "undefined") {
                        toastr.error(data.error);
                    } else if (typeof data.html !== "undefined") {
                        $("#enterror").append("<br>"  + data.html);
                    }
                } catch (e) {
                    toastr.error("Something went wrong");
                }
            });
        }
        
        $("#submitEntity").on('click', function(e) {handleEntitySubmit(e);});
        function handleEntitySubmit(e) {
            $("#enterror").hide(500);
            if ($("#map_entity").val().trim().length === 0) {
                return;
            }
            var brOpen = $("#map_entity").val().match(/{/g);
            var brClose = $("#map_entity").val().match(/}/g);
            if (brOpen === null || brClose === null) {
                e.preventDefault();
                e.stopPropagation();
                $("#enterror").html("<a href='#' onclick='getEntMismatchDetails();'>The { and } tag count doesn't match (or doesn't exist at all). Please fix the entity. Click on me to get details.</a>");
                $("#enterror").show(500);
                return false;
            }
            if (brOpen.length !== brClose.length) {
                e.preventDefault();
                e.stopPropagation();
                $("#enterror").html("<a href='#' onclick='getEntMismatchDetails();'>The { and } tag count doesn't match (or doesn't exist at all). Please fix the entity. Click on me to get details.</a>");
                $("#enterror").show(500);
                return false;
            }
        }
        
        $("#filter_form").submit(function(e){
            e.preventDefault();
            e.stopPropagation();
            currPage = 0;
            getEntityCards();
        });
        var currPage = $postfpage;
        $(document).ready(function() {
            getEntityCards();
        });
        
        function getEntityCards() {
        
            var uploaded_by = $("#uploaded_by").val();
            var filter = $("#filter").val();
            var hideAdded = $("#hideAddedEnts").is(":checked");
            if (hideAdded) {
                hideAdded = 1;
            } else {
                hideAdded = 0;
            }
            
            var showLiveMCEnts = $("#showLiveMCEnts").is(":checked");
            if (showLiveMCEnts) {
                showLiveMCEnts = 1;
            } else {
                showLiveMCEnts = 0;
            }
        
            var showNotApprovedEnts = $("#showNotApprovedEnts").is(":checked");
            if (showNotApprovedEnts) {
                showNotApprovedEnts = 1;
            } else {
                showNotApprovedEnts = 0;
            }
        
            var page_items = $("#page_items").val();
            var sort_by = $("#sort_by").val();
            var avg_vote_filter = $("#avg_vote_filter").val();
            var approved_by = $("#approved_by").val();
            
            
            
        
            $.post("{BASE_URL}/ajax", {getEntityCards: 1, approved_by: approved_by, currPage: currPage, page_items: page_items, sort_by: sort_by, avg_vote_filter: avg_vote_filter, showNotApprovedEnts: showNotApprovedEnts, uploaded_by: uploaded_by, filter: filter, hideAdded: hideAdded, showLiveMCEnts: showLiveMCEnts}, function(data) {
        
                try  {
                    data = JSON.parse(data);
                    if (typeof data.error !== "undefined") {
                        toastr.error(data.error);
                    }
        
                    if (typeof data.html !== "undefined") {
                        $("#entityCards").html(data.html);
                        $("#pagination").html(data.paginate);
                    }
                } catch (e) {
                    toastr.error("Something went wrong with getting entities. Try again.");
                }
        
            });
        
        }
        
        function addToMapcycle(serialized) {
            $.post("{BASE_URL}/ajax", serialized, function(data) {
            
                try {
                    data = JSON.parse(data);
        
                    if (typeof data.error !== "undefined") {
                        toastr.error(data.error);
                    } else if (typeof data.msg !== "undefined") {
                        toastr.success(data.msg);
                        $("#pushMC").modal('toggle');
                        getEntityCards();
                    }
                } catch (e) {
                    toastr.error("Something went wrong with pushing to mapcycle");
                }
            });
        }
        
        $("#pushmc_form").on('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var serialized = $(this).serialize();
            addToMapcycle(serialized);
        });
        
        function pushToMapcycle(entity_id) {
            $("#pushmc_entity_id").val(entity_id);
        
            $.post("{BASE_URL}/ajax", {getSpawnPoints: 1, entity_id: entity_id}, function(data) {
                
                try {
                    data = JSON.parse(data);
        
                    if (typeof data.error !== "undefined") {
                        toastr.error(data.error);
                    } else if (typeof data.spawnpoints !== "undefined") {
                        $("#spawnpointCount").html(data.spawnpoints);
                        $("#pushMC").modal('toggle');
                    }
                } catch (e) {
                    toastr.error("Something went wrong with getting spawnpoints data from entity.");
                }
            });
            
            
        }
        
        
        
        function imgModal(entity_id, map_name, created_by) {
            $("#carousel-indicators").html("");
            $("#pictureCarousel").html("");
            $("#carousel-indicator-list-items").html("");
            $.get(
                "{BASE_URL}/getImages/" + entity_id
                , {}
                , function(data) {
                    try {
                        data = JSON.parse(data);
                        $.each(data, function(i, item) {
                            var classtxt = "";
                            var imgClass = "";
                            if (i === 0) {
                                classtxt = "class='active'";
                                imgClass = "active";
                            }
                            $("#carousel-indicator-list-items").append("<li id='carouselList' data-target='#carousel-indicator-list' data-slide-to='" + i + "' " + classtxt + "></li>");
                            $("#pictureCarousel").append("<div class='carousel-item " + imgClass + "'><img class='d-block w-100' src='" + item + "'></div>");
                        });
                        
                        $("#showImgModalTitle").html("Entity id " + entity_id + ", map " + map_name + ", created by " + created_by);
                        $("#showImgModal").modal();
                    } catch (e) {
                        toastr.error("Something went wrong. Please try again or try later");
                    }
                }
            );
        }
        
        
        
        function hideAddedEnts() {
            
            getEntityCards();
        }
        
        function showNotApprovedEnts() {
            currPage = 0;
            getEntityCards();
        }
        
        function showLiveMCEnts() {
            currPage = 0;
            getEntityCards();
        }
        
        </script>
       
        
EOT
);
$page->render();