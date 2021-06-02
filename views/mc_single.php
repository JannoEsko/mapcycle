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

$page->setTitle("Mapcycle management | " . Constants::$TOOL_NAME);

$login_txt_action = "";
$login_txt = "";
if ($user->isLoggedIn()) {
    $login_txt_action = "onclick='logout();'";
    $login_txt = "Hello, " . $user->getName() . ". Click here to log out.";
} else {
    $login_txt_action = "onclick='$(\"#loginModal\").modal();'";
    $login_txt = "Log in";
}
$page
        ->registerReplaceableTag("{LOGIN_TXT_ACTION}", $login_txt_action)
        ->registerReplaceableTag("{LOGIN_TXT}", $login_txt)
        ->registerReplaceableTag("{NAVBAR_EXTRA}", "");


if (!isset($mapcycle_id) || intval($mapcycle_id) === 0) {
    throw new Exception("Mapcycle page without mapcycle id.");
} 

$mapcycle = new Mapcycle($mapcycle_id);
$mapcycle = $mapcycle->populate();

if ($mapcycle === null || !($mapcycle instanceof Mapcycle)) {
    throw new Exception("Mapcycle with id $mapcycle_id not found.");
}

if (!($user instanceof User) || !$user->getCan_approve_ents()) {
    throw new Exception("You're not privileged enough to access this page.");
}

if (!$user->getCan_approve_mapcycle() && intval($mapcycle->getMapcycle_creator_user_id()) !== intval($user->getMember_id())) {
    throw new Exception("You cannot manage this mapcycle.");
}


$entityMaps = EntityMap::getOrderedEntities($mapcycle);
$page->appendContent("<h6>This mapcycle has " . sizeof($entityMaps) . " maps connected to it.");

$page->appendContent("<br>It is created by " . XenForo::getUsernameByID(Constants::getXenSQL(), $mapcycle->getMapcycle_creator_user_id()));
if ($user->getCan_approve_mapcycle()) {
    $page->appendContent(" (ip: " . $mapcycle->getMapcycle_creator_ip() . ")");
}
$page->appendContent("<br>Mapcycle created at: " . $mapcycle->getMapcycle_created_at());
$page->appendContent("<br>Mapcycle current status: " . $mapcycle->getStatusString());
if ($mapcycle->getMapcycle_status_change_by() !== null && intval($mapcycle->getMapcycle_status_change_by()) > 0) {
    $page->appendContent("<br>Latest status change was done by " . XenForo::getUsernameByID(Constants::getXenSQL(), $mapcycle->getMapcycle_status_change_by()));
    if ($user->getCan_approve_mapcycle()) {
        $page->appendContent(" (ip: " . $mapcycle->getMapcycle_status_change_by_ip() . ")");
    }
}

$currlive = PushToLive::getLiveMapcycle();
$ptl = PushToLive::getPushtoliveByMapcycleId($mapcycle->getMapcycle_id());

/*
if ($ptl !== null && $ptl instanceof PushToLive) {
    if ($ptl->isLive()) {
        $page->appendContent("<br>This mapcycle is currently live. Date range - " . $ptl->getLive_from() . " - " . $ptl->getLive_to());
    } else {
        $page->appendContent("<br>This mapcycle is not currently live. It will be live between " . $ptl->getLive_from() . " - " . $ptl->getLive_to());
    }
} else {
    $page->appendContent("<br>This mapcycle is not scheduled to go live.");
}
*/

if ($currlive !== null && $currlive instanceof PushToLive) {
    if (intval($currlive->getMapcycle_id()) === intval($mapcycle->getMapcycle_id())) {
        $page->appendContent("<br>This mapcycle is currently live. Date range - " . $currlive->getLive_from() . " - " . $currlive->getLive_to());
    } else {
        $page->appendContent("<br>This mapcycle is not currently live.");
    }
} else {
    $page->appendContent("<br>This mapcycle is not currently live.");
}

if ($ptl !== null && is_array($ptl) && sizeof($ptl) > 0) {
    $page->appendContent("<br>Push to live dates on this mapcycle:");
    foreach ($ptl as $ptlRow) {
        if ($currlive !== null && $currlive instanceof PushToLive && intval($ptlRow->getPushtolive_id()) === intval($currlive->getPushtolive_id())) {
            continue;
        }
        $statusstring = "";
        
        if ($ptlRow->isDeleted()) {
            $statusstring = "(Deleted)";
        } else if ($ptlRow->isLive()) {
            $statusstring = "LIVE";
        } else {
            $statusstring = "Registered";
        }
        
        $page->appendContent("<br>    $statusstring - " . $ptlRow->getLive_from() . " - " . $ptlRow->getLive_to());
    }
    
    
} else {
    $page->appendContent("<br>This mapcycle is not scheduled to go live.");
}



$page->appendContent("</h6>");
$page->appendContent("<div id='beforeTable'></div><br>");
if ($user->getCan_approve_mapcycle()) {
    $page->appendContent("<button class='btn btn-outline-danger btn-block' onclick='deleteMapcycle({$mapcycle->getMapcycle_id()});'>Delete this mapcycle</button><br>");
    $page->appendAdditionalScript(<<<EOT
<script>
    
            function deleteMapcycle(mapcycle_id) {
                $("#deleteMapcycle_id").val(mapcycle_id);
                $("#deleteMapcycleModal").modal('toggle');
            }
            
            function realDelete() {
                var deleteMapcycle_id = $("#deleteMapcycle_id").val();
                $.post("{BASE_URL}/ajax", {deleteMapcycle: 1, deleteMapcycle_id: deleteMapcycle_id}, function(data) {
            
                    try {
                        data = JSON.parse(data);
            
                        if (typeof data.error !== "undefined") {
                            toastr.error(data.error);
                        }
            
                        if (typeof data.info !== "undefined") {
                            toastr.info(data.info);
                            $("#deleteMapcycleModal").modal('toggle');
                            if (typeof data.href !== "undefined") {
                                setTimeout(function() {window.location = data.href;}, 3000);
                            }
                        }
                    } catch (e) {
                        toastr.error("Something went wrong.");
                    }
                    
                });
            }
            
</script>
            
EOT
);
    $page->appendPostDivContent(<<<EOT
  <div id="deleteMapcycleModal" role="dialog" aria-labelledby="deleteMapcycleModalTitle" aria-hidden="true" class="modal fade" tabindex="-1">
        
            <div class="modal-dialog modal-lg" >
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 id="deleteMapcycleModalTitle" class="modal-title">Delete mapcycle</h4>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                            <span aria-hidden="true">&times;</span>
                        </button>

                    </div>
                    <div class="modal-body" id="deleteMapcycleModalBody">
            <input type='hidden' name='deleteMapcycle_id' id='deleteMapcycle_id'>
                        <h5>Are you sure you want to delete this mapcycle?</h5>
            <br>
            <h6>Deleting doesn't delete the mapcycle completely, it just "soft-deletes" it. Relations with entities etc still remain, but it cannot be used.</h6>
                        
        
                    </div>
        <div class='modal-footer'>
                 
        <button class='btn btn-outline-danger mr-auto' onclick="realDelete();">Delete</button>
        <button class='btn btn-outline-success float-right' data-dismiss="modal">Close</button>
        
        </div>
                </div>
            </div>
        
        </div>
EOT
);
}
$page->appendContent("<div class='btn-group d-flex'><button type='button' class='btn btn-outline-success w-100' onclick='enableDnd();'>Enable table row sorting</button><button type='button' class='btn btn-outline-danger w-100' onclick='disableDnd();'>Disable table row sorting</button></div><br>");

$page->appendContent("<div class='table-responsive'>");

$page->appendContent("<table class='table table-bordered table-hover table-sm' id='entityTable'>");

$page->appendContent("<thead>");
$page->appendContent("<tr>");
$page->appendContent("<th>Map name</th><th>Type</th><th>Spawns</th><th>Cvars</th><th>Description</th><th>Entity created by</th><th>Entity added by</th><th></th>");
$page->appendContent("</tr>");
$page->appendContent("</thead>");

$page->appendContent("<tbody id='entityBody'>");
/*
$allHaveCvars = true;
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
    
    $page->appendContent("<tr class='$trowClass' id='entityId{$entityMap->getEntitymap_id()}' data-entitymap-id='{$entityMap->getEntitymap_id()}'>");
    $page->appendContent("<td>" . $entity->getMap_name() . "</td>");
    $page->appendContent("<td>" . $entityMap->getGametype());
    if ($entityMap->isAltmap()) {
        $page->appendContent(" (altmap)");
    }
    $page->appendContent("</td>");
    $page->appendContent("<td>Red team - " . $entity->getRedTeamSpawnPoints() . "<br>Blue team - " . $entity->getBlueTeamSpawnPoints() . "</td>");
    $page->appendContent("<td>$cvarCount cvars</td>");
    $page->appendContent("<td>" . $entity->getMap_description() . "</td>");
    $page->appendContent("<td>" . XenForo::getUsernameByID(Constants::getXenSQL(), $entity->getUploaded_by()));
    if ($user->getCan_approve_mapcycle()) {
        $page->appendContent(" (ip: " . $entity->getUploaded_by_ip() . ")");
    }
    $page->appendContent("</td>");
    $page->appendContent("<td>" . XenForo::getUsernameByID(Constants::getXenSQL(), $entityMap->getAdded_by()));
    if ($user->getCan_approve_mapcycle()) {
        $page->appendContent(" (ip: " . $entityMap->getAdded_by_ip() . ")");
    }
    $page->appendContent("</td>");
    $page->appendContent("<td><button type='button' class='btn btn-primary btn-sm' onclick='viewEntityCard(" . $entity->getEntity_id() . ");'>View entity</button>");
    $page->appendContent("<button type='button' class='btn btn-success btn-sm' onclick='toggleCvars(" . $entityMap->getEntitymap_id() . ");'>Manage cvars</button>");
    $page->appendContent("</td>");
    
    $page->appendContent("</tr>");
    
}
*/
$page->appendContent("</tbody>");

$page->appendContent("</table>");
$page->appendContent("</div>");
$page->appendContent("<style>.checked{color:orange;}</style>");
$page->appendPostDivContent(<<<EOT
        <div id="entityCard" role="dialog" aria-labelledby="entityCardTitle" aria-hidden="true" class="modal modal-dbl fade" tabindex="-1">
        
            <div class="modal-dialog modal-lg" >
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 id="entityCardTitle" class="modal-title">Entity</h4>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                            <span aria-hidden="true">&times;</span>
                        </button>

                    </div>
                    <div class="modal-body" id="entityCardBody">
        <div class='row' id='entityCardRow'>
        
        </div>
                        
        
                    </div>
                </div>
            </div>
        
        </div>
        
        
        <div id="removeEntityModal" role="dialog" aria-labelledby="removeEntityModalTitle" aria-hidden="true" class="modal fade" tabindex="-1">
        
            <div class="modal-dialog modal-lg" >
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 id="removeEntityModalTitle" class="modal-title">Remove entity from mapcycle</h4>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                            <span aria-hidden="true">&times;</span>
                        </button>

                    </div>
                    <div class="modal-body" id="removeEntityModalBody">
        <input type='hidden' name='removeEntitymap_id' id='removeEntitymap_id'>
        <h5>Are you sure you want to remove this entity from the mapcycle?</h5>
        <div class='btn-group d-flex'>
   <button type='button' class='btn btn-outline-danger w-100' onclick='removeEntityMap();'>Yes, I'm sure</button><button type='button' class='btn btn-outline-success w-100' onclick='$("#removeEntityModal").modal("toggle");'>Don't remove</button>         
   
   </div>
                        
        
                    </div>
                </div>
            </div>
        
        </div>
        
        
        <div id="entityMapCvars" role="dialog" aria-labelledby="entityMapCvarsTitle" aria-hidden="true" class="modal fade" tabindex="-1">
        
            <div class="modal-dialog modal-lg" >
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 id="entityMapCvarsTitle" class="modal-title">Manage this map cvars</h4>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                            <span aria-hidden="true">&times;</span>
                        </button>

                    </div>
                    <div class="modal-body" id="entityMapCvarsBody">
                        <div id='entityMapCvarsBodyForm'>
        
        </div>
                        
        
                    </div>
        <div class='modal-footer'>
                 
        <button class='btn btn-outline-success mr-auto' onclick="$('#entitymap_cvars').submit();">Submit</button>
        <button class='btn btn-outline-danger float-right' data-dismiss="modal">Close</button>
        
        </div>
                </div>
            </div>
        
        </div>
        
        
EOT
);

if ($user->getCan_approve_mapcycle()) {
    $ptlForm = new Form();
    $ptlForm->setForm_id("ptlForm")->setForm_method("POST")->setInclude_button(false);
    $ptlForm->addText("<button type='submit' style='display: none;' id='submitPtlForm'></button>");
    $ptlForm->addFormObject(new FormObject("input", "insertPtl", "hidden", null, null, false, 0))
            ->addFormObject(new FormObject("input", "editPtl", "hidden", null, null, false, 0))
            ->addFormObject(new FormObject("input", "ptl_mapcycle_id", "hidden", null, null, false, 0))
            ->addFormObject(new FormObject("input", "liveFrom_dt", "text", "style='display: none;' required data-toggle='dateTimePicker'", "Pick go live start date"))
            ->addFormObject(new FormObject("input", "liveTo_dt", "text", "style='display: none;' required data-toggle='dateTimePicker'", "Pick go live end date"))
            ->addFormObject(new FormObject("input", "recurrence", "number", "required min=0 max=30", "Recurrence (leave as 0 to have it only one time; only works on add, not editing)", true, 0))
    ;
    $page->appendPostDivContent(<<<EOT
          
            <div id="managePtlModal" role="dialog" aria-labelledby="managePtlModalTitle" aria-hidden="true" class="modal fade">
        
            <div class="modal-dialog modal-lg" >
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 id="managePtlModalTitle" class="modal-title">Push to lives</h4>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                            <span aria-hidden="true">&times;</span>
                        </button>

                    </div>
                    <div class="modal-body" id="managePtlModalBody">
                        <div class='table-responsive'>
                            <table class='table table-bordered table-hover table-sm'>
            
                                <thead>
                                    <tr>
                                        <th>Pusher</th>
                                        <th>Pushed at</th>
                                        <th>Live from - To</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id='managePtlTable'>
            
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class='btn-group d-flex w-100'>
                            
                            <button class='btn btn-outline-danger w-100' data-dismiss='modal'>Close</button>
                        </div>
                    </div>
                </div>
            </div>
        
        </div>    
  <div id="finalizeMapcycle" role="dialog" aria-labelledby="finalizeMapcycleTitle" aria-hidden="true" class="modal fade">
        
            <div class="modal-dialog modal-lg" >
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 id="finalizeMapcycleTitle" class="modal-title">Finalize mapcycle</h4>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                            <span aria-hidden="true">&times;</span>
                        </button>

                    </div>
                    <div class="modal-body" id="finalizeMapcycleBody">
                        
        <h6>A mapcycle is finalized if all maps have cvars and if the first map is not an altmap<br>
            The finalizer (you) has to be sure that the cvars are correct, entities are playable (there is a possibility to test it before sending it to the main server) and he will be held accountable if they're not.<br>
                In the description, write a (short) overview on what kind of maps this mapcycle has (e.g. half heavily modified, half clean etc), so if someone wants to use this mapcycle in the future, they'll understand what is what quickly.</h6>
        <form id='finalizeMapcycleForm' method='POST'>
        <input type='hidden' id='finalizeMapcycle_mapcycle_id'  name='finalizeMapcycle_mapcycle_id'>
            <input type='hidden' id='finalizeMapcycle', value=1 name='finalizeMapcycle'>
            <div class='form-group'>
            <label for='finalizeMapcycle_mapcycle_description'>Mapcycle description</label>
            <textarea id='finalizeMapcycle_mapcycle_description' class='form-control' name='finalizeMapcycle_mapcycle_description' rows=5 required></textarea>
            </div>
            <button type='submit' id='submitFinalize' style='display: none;'></button>
            </form>
        <div class='btn-group d-flex'><button type='button' class='btn btn-outline-danger w-100' onclick='$("#submitFinalize").click();'>Approve</button><button type='button' class='btn btn-outline-success w-100' data-dismiss='modal'>Close</button></div>
                        
        
                    </div>
                </div>
            </div>
        
        </div>     
            
            <div id="unfinalizeMapcycleModal" role="dialog" aria-labelledby="unfinalizeMapcycleModalTitle" aria-hidden="true" class="modal fade">
        
            <div class="modal-dialog modal-lg" >
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 id="unfinalizeMapcycleModalTitle" class="modal-title">Set mapcycle as not approved</h4>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                            <span aria-hidden="true">&times;</span>
                        </button>

                    </div>
                    <div class="modal-body" id="unfinalizeMapcycleModalBody">
                        <input type='hidden' name='unfinalizeMapcycle_id' id='unfinalizeMapcycle_id'>
        <h6>Are you sure you want to set the mapcycle to not approved?<br>
            Only maps, which are not currently live, can be unfinalized, as live dates are removed when unfinalizing a mapcycle.
            </h6>
        <div class='btn-group d-flex'><button type='button' class='btn btn-outline-danger w-100' onclick='unfinalizeMc();'>Unapprove</button><button type='button' class='btn btn-outline-success w-100' data-dismiss='modal'>Close</button></div>
                        
        
                    </div>
                </div>
            </div>
        
        </div>  
            
            <div id="ptlFormModal" role="dialog" aria-labelledby="ptlFormModalTitle" aria-hidden="true" class="modal fade">
        
            <div class="modal-dialog modal-lg" >
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 id="ptlFormModalTitle" class="modal-title">Push to live modal</h4>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                            <span aria-hidden="true">&times;</span>
                        </button>

                    </div>
                    <div class="modal-body" id="ptlFormModalBody">
                        $ptlForm
                    </div>
                    <div class="modal-footer">
                        <div class='btn-group d-flex w-100'>
                            <button class='btn btn-outline-success w-100' onclick='$("#submitPtlForm").click();'>Submit</button>
                            <button class='btn btn-outline-danger w-100' data-dismiss='modal'>Close</button>
                        </div>
                    </div>
                </div>
            </div>
        
        </div>  
            
            
EOT
);
    $page->appendAdditionalScript(<<<EOT
            <script>
            
            function resendLive() {
            
                $.post("{BASE_URL}/ajax", {resendLive: 1}, function(data) {
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
            
            $("[data-toggle='dateTimePicker']").datetimepicker({
                sideBySide: true
                , inline: true
                , format: 'YYYY-MM-DD HH:mm:ss'
            });
            
            function manageptl(mapcycle_id) {
                $.post("{BASE_URL}/ajax", {getPtlTable: 1, ptl_mapcycle_id: mapcycle_id}, function(data) {
            
                    try {
                        data = JSON.parse(data);
            
                        if (typeof data.error !== "undefined") {
                            toastr.error(data.error);
                        } else if (typeof data.ptltable !== "undefined") {
                            $("#managePtlTable").html(data.ptltable);
                            $("#managePtlModal").modal('show');
                        }
                    } catch (e) {
                        toastr.error("Something went wrong.");
                    }
            
                });
            }
            
            $("#ptlForm").on('submit', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var serialized = $(this).serialize();
                $.post("{BASE_URL}/ajax", serialized, function(data) {
            
                    try {
                        data = JSON.parse(data);
            
                        if (typeof data.error !== "undefined") {
                            toastr.error(data.error);
                        } else if (typeof data.msg !== "undefined") {
                            toastr.success(data.msg);
                            //ptl tables update
                            //$("#ptlFormModal").modal('toggle');
                            location.reload(true);
                            //if (typeof data.mc_id !== "undefined") {
                            //    manageptl(data.mc_id);
                            //}
                        }
                    } catch (e) {
                        toastr.error("Something went wrong");
                    }
            
                });
            
            });
            
            function ptl(mapcycle_id) {
                $("#insertPtl").val(1);
                $("#editPtl").val(0);
                $("#ptl_mapcycle_id").val(mapcycle_id);
                $("#ptlFormModal").modal('toggle');
            }
            
            function deletePtl(ptl_id) {
                $.post("{BASE_URL}/ajax", {deletePtl: 1, ptl_id: ptl_id}, function(data) {
                    try {
                        data = JSON.parse(data);
            
                        if (typeof data.error !== "undefined") {
                            toastr.error(data.error);
                        } else if (data.msg !== "undefined") {
                            //toastr.success(data.msg);
                            location.reload(true);
                            //if (typeof data.mc_id !== "undefined") {
                            //    manageptl(data.mc_id);
                            //}
                        }
                    } catch (e) {
                        toastr.error("Something went wrong");
                    }   
                });
            }
            
            function editPtl(ptl_id, startdt, enddt) {
                $("#insertPtl").val(0);
                $("#editPtl").val(ptl_id);
                $("#ptl_mapcycle_id").val(0);
                $("#liveFrom_dt").val(startdt);
                $("#liveTo_dt").val(enddt);
                $("#liveFrom_dt").data("DateTimePicker").date(startdt);
                $("#liveTo_dt").data("DateTimePicker").date(enddt);
                $("#ptlFormModal").modal('toggle');
            }
            
            function pushToPreProd(mapcycle_id) {
                $.post("{BASE_URL}/ajax", {pushToPreProd: 1, preprod_mc_id: mapcycle_id}, function(data) {
            
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
            
            function finalizeMapcycle(mapcycle_id) {
        
                $("#finalizeMapcycle_mapcycle_id").val(mapcycle_id);
                $("#finalizeMapcycle").modal('toggle');
        }
            function unfinalizeMc() {
                var mapcycle_id = $("#unfinalizeMapcycle_id").val();
                $.post("{BASE_URL}/ajax", {unfinalizeMapcycle: 1, unfinalize_mc_id: mapcycle_id}, function(data) {
                    
                    try {
                        data = JSON.parse(data);
                        
                        if (typeof data.error !== "undefined") {
                            toastr.error(data.error);
                        } else if (typeof data.msg !== "undefined") {
                            //toastr.success(data.msg);
                            location.reload(true);
                            
                        }
                    } catch (e) {
                        toastr.error("Something went wrong.");
                    }
            
                });
            }
            function unfinalizeMapcycle(mapcycle_id) {
                $("#unfinalizeMapcycle_id").val(mapcycle_id);
                $("#unfinalizeMapcycleModal").modal('toggle');
            }
            
            $("#finalizeMapcycleForm").on("submit", function(e) {
            
                e.preventDefault();
                e.stopPropagation();
                var serialized = $(this).serialize();
                $.post("{BASE_URL}/ajax", serialized, function(data) {
            
                    try {
                        data = JSON.parse(data);
                        if (typeof data.error !== "undefined") {
                            toastr.error(data.error);
                        } else if (typeof data.msg !== "undefined") {
                            
                            location.reload(true);
                        }
                    } catch (e) {
                        toastr.error("Something went wrong");
                    }   
            
                });
            
            });
            </script>
            
EOT
);
}

$page->appendPostDivContent(<<<EOT
     <div id="showImgModal" role="dialog" aria-labelledby="showImgModalTitle" aria-hidden="true" class="modal fade" tabindex="-1">
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

$dnd = <<<EOT
        
 $(document).ready(function() {
        populateEntityTable();
            
        
        });    
        
        
        
        function removeCvar(cvar_name, cvar_friendly_name) {
        
            $("#addNewCvar").append(new Option(cvar_name + " - " + cvar_friendly_name, cvar_name, false, false)).trigger('change');
            $("#" + cvar_name).parent().remove();
            var idx = existingConvars.indexOf(cvar_name);
            if (idx > -1) {
              existingConvars.splice(idx, 1);
            }

        }
        
        function enableDnd() {
        $("#entityTable").rowSorter({
                dragClass: "table-info"
                , onDrop: function(tbody, trow, ni, oi) {
                    var rows = tbody.rows;
                    //console.log(tbody);
                    var arr = [];
                    for (var i=0; i<rows.length; i++) {
                        arr.push({'entitymap_id': $(rows[i]).data('entitymap-id'), 'order': i});
                        //console.log($(rows[i]).data('entitymap-id') + " was id, ordered at - " + i);
                    }
                    //console.log(JSON.stringify(arr));
        
                    pushMapcycleOrder(arr);
                }
            });
        }
        
        function disableDnd() {
            $("#entityTable").rowSorter().destroy();
        }
        
EOT
;



$page->appendAdditionalScript(<<<EOT
        
        <script>
        
        
        
        function addCvar() {
            var addNewCvar = $("#addNewCvar").val();
            $.post("{BASE_URL}/ajax", {addConvarToForm: addNewCvar, existingConvars: existingConvars}, function(data) {
                try {
                    data = JSON.parse(data);
        
                    if (typeof data.error !== "undefined") {
                        toastr.error(data.error);
                    } else if (typeof data.html !== "undefined") {
                        $(data.html).insertBefore("#newCvarSection");
                        existingConvars = data.existingConvars;
                        $("#addNewCvar option:selected").remove();
                        $("[data-toggle='select2']").select2({dropdownParent: $('#entityMapCvars')});
                        
                    }
                } catch (e) {
                    toastr.error("Something went wrong");
                }
            });
        }
        
        function registerCvars(serialized) {
            $.post("{BASE_URL}/ajax", serialized, function(data) {
        
                try {
                    data = JSON.parse(data);
                    console.log(data);
        
                    if (typeof data.error !== "undefined") {
                        toastr.error(data.error);
                    } else if (typeof data.msg !== "undefined") {
                        toastr.success(data.msg);
                        $("#entityMapCvars").modal('toggle');
                        populateEntityTable();
                    }
                } catch (e) {
                    toastr.error("Something went wrong");
                }
        
            });
        }
        
        var existingConvars;
        function toggleCvars(entitymap_id) {
            $.post("{BASE_URL}/ajax", {getEntitymapCvarForm: 1, entitymap_id: entitymap_id}, function(data) {
        
                try {
                    data = JSON.parse(data);
        
                    if (typeof data.error !== "undefined") {
                        toastr.error(data.error);
                    } else if (typeof data.html !== "undefined") {
                        $("#entityMapCvarsBodyForm").html(data.html);
                        $("#entityMapCvars").modal('toggle');
                        $("#entitymap_cvars").on('submit', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            
                            var serialized = $(this).serializeArray();
                            //serialized.push({name: "existingConvars", value: existingConvars.serializeArray()});
                            //serialized['existingConvars'] = existingConvars;
                            $.each(existingConvars, function(i, val) {
                                serialized.push({name: "existingConvars[]", value: val});
                            });
                            console.log(serialized);
                            registerCvars(serialized);
                        });
                        existingConvars = data.existingConvars;
                        $("#entityMapCvars").on('shown.bs.modal', function() {
                            $("[data-toggle='select2']").select2({dropdownParent: $('#entityMapCvars')});
                        });
                        console.log(existingConvars);
                    }
                } catch (e) {
                    toastr.error("Something went wrong");
                    console.log(e);
                }
        
            });
        }
        
        function removeEntityModal(entitymap_id) {
        
            $("#removeEntitymap_id").val(entitymap_id);
            $("#removeEntityModal").modal('toggle');
        
        }
        
        function removeEntityMap() {
            var entitymap_id = $("#removeEntitymap_id").val();
        
            $.post("{BASE_URL}/ajax", {removeEntitymap: 1, entitymap_id: entitymap_id}, function(data) {
                try {
                    data = JSON.parse(data);
        
                    if (typeof data.error !== "undefined") {
                        toastr.error(data.error);
                    }
        
                    if (typeof data.msg !== "undefined") {
                        toastr.success(data.msg);
                        $("#removeEntityModal").modal('toggle');
                        populateEntityTable();
                    }
        
                } catch (e) {
                    toastr.error("Something went wrong");
                }
            });
        }
        
        function pushMapcycleOrder(arr) {
        
            $.post("{BASE_URL}/ajax", {writeEntitymapOrder: arr}, function(data) {
                try {
                    data = JSON.parse(data);
        
                    if (typeof data.error !== "undefined") {
                        toastr.error(data.error);
                        populateEntityTable();
                    } else if (typeof data.msg !== "undefined") {
                        toastr.success(data.msg);
                    }
                } catch (e) {
                    toastr.error("Something went wrong.");
                }
            });
        
        }
        
            function viewEntityCard(entity_id) {
                $.post("{BASE_URL}/ajax", {getEntityCards: 1, singleEntity: entity_id}, function(data) {
                    try {
                        data = JSON.parse(data);
        
                        if (typeof data.error !== "undefined") {
                            toastr.error(data.error);
                        }
                        if (typeof data.html !== "undefined") {
                            $("#entityCardRow").html(data.html);
                            $("#entityCard").modal('toggle');
                        }
                    } catch (e) {
                        toastr.error("Something went wrong.");
                    }
                });
            }
        
        $dnd
        
        function imgModal(entity_id, map_name, created_by) {
            $("#carousel-indicators").html("");
            $("#carousel-indicator-list-items").html("");
            console.log("clrrr");
            $("#pictureCarousel").html("");
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
                            $("#carousel-indicator-list-items").append("<li data-target='#carousel-indicator-list' data-slide-to='" + i + "' " + classtxt + "></li>");
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
        
        
        function populateEntityTable() {
        
            $.post("{BASE_URL}/ajax", {getMapcycleEntitiesTable: 1, mapcycle_id: {$mapcycle->getMapcycle_id()}}, function(data) {
        
                try {
                    data = JSON.parse(data);
        
                    if (typeof data.error !== "undefined") {
                        toastr.error(data.error);
                    }
        
                    if (typeof data.entities !== "undefined") {
                        $("#entityBody").html(data.entities);
                    }
            
                    if (typeof data.prepend !== "undefined") {
                        $("#beforeTable").html(data.prepend);
                    } else {
                        $("#beforeTable").html("");
                    }
                    
                } catch (e) {
                    toastr.error("Something went wrong");
                }
        
            });
        
        }
        
        </script>
        
        
EOT
);

$page->render();