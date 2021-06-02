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


$page->appendContent("<div class='row'>");

$page->appendContent("<div class='col border rounded' style='margin-left: 15px; margin-right: 15px;'><h4>Current mapcycle</h4>");

$ptl = PushToLive::getLiveMapcycle();

if ($ptl === null) {
    $page->appendContent("<h6>Currently there's no mapcycle live by the application. Therefore the latest live mapcycle or a manual one is in use.</h6>");
} else {
    
    $page->appendContentBR("Current mapcycle is pushed by " . XenForo::getUsernameByID(Constants::getXenSQL(), $ptl->getPush_created_by()));
    $page->appendContentBR("Mapcycle is live between " . $ptl->getLive_from() . " - " . $ptl->getLive_to());
    $page->appendContentBR("<br><div class='btn-group d-flex'><button class='btn btn-outline-primary w-100 btn-sm' onclick='location.href=\"" . Constants::$PAGE_URL . "/maps/mapcycle/" . $ptl->getMapcycle_id() . "\";'>Manage current live</button><button class='btn btn-outline-danger w-100 btn-sm' onclick='resendLive();'>Resend mapcycle to live</button></div><br>");
}

$page->appendContent("</div>");

$page->appendContent("</div><br>");
$page->appendContent("<button class='btn btn-outline-primary btn-block' type='button' onclick='createMC();'>Create new mapcycle</button><br>");
$page->appendContent("<div class='row'>");
$page->appendContent("<div class='col border rounded' style='margin-left: 15px; margin-right: 15px;'>");
$page->appendContent("<h4>Existing, approved mapcycles</h4>");
$page->appendContent("<div class='table-responsive'><table id='approvedMCs' class='table table-sm table-bordered table-hover'>");
$page->appendContent("<thead><tr><th>Creator</th><th>Description</th><th>Maps</th><th>PTL info</th><th></th></tr></thead>");
$page->appendContent("<tbody id='approvedMCsBody'>");
$page->appendContent("</tbody></table></div>");
$page->appendContent("</div>");
$page->appendContent("<div class='col border rounded' style='margin-left: 15px; margin-right: 15px;'>");
$page->appendContent("<h4>Mapcycles requiring approval</h4>");
$page->appendContent("<div class='table-responsive'><table id='notapprovedMCs' class='table table-sm table-bordered table-hover'>");
$page->appendContent("<thead><tr><th>Creator</th><th>Description</th><th>Maps</th><th></th></tr></thead>");
$page->appendContent("<tbody id='notapprovedMCsBody'>");
$page->appendContent("</tbody></table></div>");
$page->appendContent("</div>");


$page->appendContent("<div class='col border rounded' style='margin-left: 15px; margin-right: 15px;'>");
$page->appendContent("<h4>PTL Outlook</h4>");
$page->appendContent("<div class='table-responsive'><table id='ptlOutlookTable' class='table table-sm table-bordered table-hover'>");
$page->appendContent("<thead><tr><th>Mapcycle description</th><th>Pusher</th><th>Pushed at</th><th>Live from - to</th><th>Status</th><th></th></tr></thead>");
$page->appendContent("<tbody id='ptlOutlookBody'>");
$page->appendContent("</tbody></table></div>");
$page->appendContent("</div>");


$page->appendContent("</div>");

if ($user->getCan_approve_mapcycle()) {
    //$page->appendContent("<button class='btn btn-outline-danger btn-block' onclick='deleteMapcycle({$mapcycle->getMapcycle_id()});'>Delete this mapcycle</button><br>");
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
            
                        if (typeof data.msg !== "undefined") {
                            toastr.success(data.msg);
                            populateNotApprovedMCs();
                            $("#deleteMapcycleModal").modal('toggle');
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

if ($user->getCan_approve_mapcycle()) {
    $ptlForm = new Form();
    $ptlForm->setForm_id("ptlForm")->setForm_method("POST")->setInclude_button(false);
    $ptlForm->addText("<button type='submit' style='display: none;' id='submitPtlForm'></button>");
    $ptlForm->addFormObject(new FormObject("input", "insertPtl", "hidden", null, null, false, 0))
            ->addFormObject(new FormObject("input", "editPtl", "hidden", null, null, false, 0))
            ->addFormObject(new FormObject("input", "ptl_mapcycle_id", "hidden", null, null, false, 0))
            ->addFormObject(new FormObject("input", "liveFrom_dt", "text", "style='display: none;' required data-toggle='dateTimePicker'", "Pick go live start date"))
            ->addFormObject(new FormObject("input", "liveTo_dt", "text", "style='display: none;' required data-toggle='dateTimePicker'", "Pick go live end date"))
            ->addFormObject(new FormObject("input", "recurrence", "number", "required min=0 max=30", "Recurrence (leave as 0 to have it only one time; only works on add, not editing)", true, 0));
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
            
        <div id="ptlFormModal" role="dialog" aria-labelledby="ptlFormModalTitle" aria-hidden="true" class="modal fade" tabindex="-1">
        
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
                            $("#ptlFormModal").modal('toggle');
                            populatePTLOutlook();
                            populateApprovedMCs();
                            if ($("#managePtlModal").hasClass('show') && typeof data.mc_id !== "undefined") {
                                manageptl(data.mc_id);
                            }
                            
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
                            toastr.success(data.msg);
            
                            populatePTLOutlook();
                            if ($("#managePtlModal").hasClass('show') && typeof data.mc_id !== "undefined") {
                                manageptl(data.mc_id);
                            }
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
                            toastr.success(data.msg);
                            populateNotApprovedMCs();
                            populateApprovedMCs();
                            $("#unfinalizeMapcycleModal").modal('toggle');
                            //location.reload(true);
                            
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
                            toastr.success(data.msg);
                            $("#finalizeMapcycle").modal('toggle');
                            populateNotApprovedMCs();
                            populateApprovedMCs();
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

$page->appendAdditionalScript(<<<EOT
        
        <script>
        
        function duplicateMapcycle(mapcycle_id) {
            $.post("{BASE_URL}/ajax", {duplicateMapcycle: 1, mapcycle_id: mapcycle_id}, function(data) {
        
                try {
                    data = JSON.parse(data);
        
                    if (typeof data.error !== "undefined") {
                        toastr.error(data.error);
                    }
        
                    if (typeof data.msg !== "undefined") {
                        toastr.success(data.msg);
                        populateNotApprovedMCs();
                    }
                    
                } catch (e) {
                    toastr.error("Something went wrong");
                }
        
            });
        }
        
        function createMC() {
            $.post("{BASE_URL}/ajax", {createNewMapcycle: 1}, function(data) {
                
                try {
                    data = JSON.parse(data);
                    if (typeof data.error !== "undefined") {
                        toastr.error(data.error);
                    } else {
                        toastr.success(data.msg);
                        //add new mc row
                        populateNotApprovedMCs();
                    }
                    
                } catch (e) {
                    toastr.error("Something went wrong.");
                }
                
            });
        }
        
        function addMaps(mapcycle_id) {
        
            $.post("{BASE_URL}/ajax", {setMapToSession: 1, mapcycle_id: mapcycle_id}, function(data) {
        
                try  {
                    data = JSON.parse(data);
                    if (typeof data.error !== "undefined") {
                        toastr.error(data.error);
                    }
                    if (typeof data.info !== "undefined") {
                        toastr.info(data.info);
                        if (typeof data.href !== "undefined") {
                            setTimeout(function() {window.location = data.href;}, 3000);
                        }
                    }
                } catch (e) {
                    toastr.error("Something went wrong");
                }
        
            });
        
        }
        
        $(document).ready(function() {
            populateNotApprovedMCs();
            populateApprovedMCs();
            populatePTLOutlook();
        });
        
        function populateNotApprovedMCs() {
        
            $.post("{BASE_URL}/ajax", {getNotApprovedMCsTable: 1}, function(data) {
        
                try {
                    data = JSON.parse(data);
                    if (typeof data.error !== "undefined") {
                        toastr.error(data.error);
                    }
                    if (typeof data.notapproved !== "undefined") {
                        $("#notapprovedMCsBody").html(data.notapproved);
                    }
                } catch (e) {
                    toastr.error("Something went wrong with loading mc's table");
                }
            });
        
        }
        
        function populatePTLOutlook() {
        
            $.post("{BASE_URL}/ajax", {getPTLOutlook: 1}, function(data) {
        
                try {
                    data = JSON.parse(data);
                    if (typeof data.error !== "undefined") {
                        toastr.error(data.error);
                    }
                    if (typeof data.ptloutlook !== "undefined") {
                        $("#ptlOutlookBody").html(data.ptloutlook);
                    }
                } catch (e) {
                    toastr.error("Something went wrong with loading PTL Outlook");
                }
        
            });
        
        }
        
        function populateApprovedMCs() {
        
            $.post("{BASE_URL}/ajax", {getApprovedMCsTable: 1}, function(data) {
        
                try {
                    data = JSON.parse(data);
                    if (typeof data.error !== "undefined") {
                        toastr.error(data.error);
                    }
                    if (typeof data.approved !== "undefined") {
                        $("#approvedMCsBody").html(data.approved);
                    }
                } catch (e) {
                    toastr.error("Something went wrong with loading mc's table");
                }
            });
        
        }
        
        </script>
        
        
EOT
);

$page->render();