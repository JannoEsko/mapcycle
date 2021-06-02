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



$form = new Form();
$entityCreators = new SelectObject("uploaded_by", "", "Entity creator");
$creatorList = Entity::getEntityCreators(true);
$entityCreators->appendSelect_options(new SelectOption("Pick a creator", 0));
foreach ($creatorList as $creator) {
    $entityCreators->appendSelect_options(
        new SelectOption($creator['name'] . " (" . $creator['ent_count'] . " entities uploaded)", $creator['uploaded_by'])
    );
}



$form->setForm_id("filter_form");
$form->setForm_method("GET");
$form
        ->addFormObject(new FormObject("input", "filter", "text", "", "Filter by map name"))
        ->addFormObject($entityCreators)
        ->setSubmit_btn_text("Filter");
$page->appendContent($form);


$login_txt_action = "";
$login_txt = "";
$navbar_extra = "";
$entityApprovedElement = "";

if ($user->isLoggedIn()) {
    $login_txt_action = "onclick='logout();'";
    $login_txt = "Hello, " . $user->getName() . ". Click here to log out.";
    $navbar_extra = "<span class='mr-auto'><span class='text-white headlink'>&nbsp;&nbsp;</span><a href='/maps/entities' class='text-white headlink'>Entities</a><span class='text-white headlink'>&nbsp;|&nbsp;</span><a href='#' class='text-white headlink' onclick='$(\"#entUpload\").modal();'>Upload new entity</a>";
    if ($user->getCan_approve_ents()) {
        $navbar_extra .= "<span class='text-white headlink'>&nbsp;|&nbsp;</span><a href='/maps/entities/approve' class='text-white headlink'>Entity approvals</a>";
        $slobj = new SelectObject("entity_approved", "required", "Entity status");
        $slobj->appendSelect_options(new SelectOption("Awaiting approval", 0, true))
                ->appendSelect_options(new SelectOption("Approved", 1))
                ->appendSelect_options(new SelectOption("Rejected", 2));
        $entityApprovedElement = $slobj . ""; //call magic function toString.
        $entityApprovedElement .= "<h5 class='text-danger'>By approving the entity, you will be held responsible if it crashes the server. You have to test the entity before approving!</h5>";
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


$page->appendContent("<div class='row' id='appr_maps_list'>");

$page->appendContent("<h4>Loading...</h4>");

$page->appendContent("</div>");
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

$page->appendPostDivContent(<<<EOT
   
   <div id="entUpload" role="dialog" aria-labelledby="entUploadTitle" aria-hidden="true" class="modal fade" tabindex="-1">
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
                <br>For every entity, we require you to upload a minimum of <span style='color: red;'>3 pictures</span> which must be coming from the map, otherwise the entity will be rejected.<br>Continuous false-entries and rejections can lead up to a suspension on this platform.<br><br>
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


$page->appendPostDivContent(<<<EOT
   
   <div id="entModify" role="dialog" aria-labelledby="entModifyTitle" aria-hidden="true" class="modal fade" tabindex="-1">
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
                            $entityApprovedElement
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
EOT
   );

$page->appendAdditionalScript(<<<EOT
        
        <script>
        
        
        $("#filter_form").on('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            getEntityList();
        });
        
        function getEntityList() {
            var filter = $("#filter").val();
            var uploaded_by = $("#uploaded_by").val();
            $.post("{BASE_URL}/ajax", {getApprovalList: 1, filter: filter, uploaded_by: uploaded_by}, function(data) {
                try {
                    data = JSON.parse(data);
                    if (typeof data.success !== "undefined" && data.success === 1) {
                        $("#appr_maps_list").html("");
                        $.each(data.ents, function(i, val) {
                            $("#appr_maps_list").append(val.html);
                        });
                    } else {
                        $("#appr_maps_list").html("<h4>No maps to approve.</h4>");
                    }
                } catch (e) {
                    toastr.error("Something went wrong. Please try the action again.");
                }
            });
        }
        
        $(document).ready(function() {
            getEntityList();
            $("#newimgs").prettyFile({minFiles: 1, required: false});
        });
        
        function entityApprove(entity_id) {
            
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
                $("#enterror").html("The { and } tag count doesn't match (or doesn't exist at all). Please fix the entity.");
                $("#enterror").show(500);
                return false;
            }
            if (brOpen.length !== brClose.length) {
                e.preventDefault();
                e.stopPropagation();
                $("#enterror").html("The { and } tag count doesn't match. Please fix the entity.");
                $("#enterror").show(500);
                return false;
            }
        }
        function imgModal(entity_id, map_name, created_by) {
            $("#carousel-indicators").html("");
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
        
        function modifyEntity(entity_id) {
        
            $("#modifyEnt").val(entity_id);
        
            $.post("{BASE_URL}/ajax", {getModifyEntity: entity_id}, function(data) {
                try {
                    data = JSON.parse(data);
        
                    if (typeof data.error !== "undefined") {
                        toastr.error(data.error);
                    } else {
                        $("#modifyEnt").val(data.entity_id);
                        $("#entity_metadata").val(data.entity_metadata);
                        $("#modify_map_name").val(data.modify_map_name);
                        $("#modify_map_description").val(data.modify_map_description);
                        $("#modify_map_entity").val(data.modify_map_entity);
                        $("#entModify").modal('toggle');
                    }
                } catch (e) {
                    toastr.error("Something went wrong");
                }
        
            });
        
        }
        
        function toggleEntityDownload(entity_id, map_name) {
        
            fetch('{BASE_URL}/ajax/getNotApprovedEntity/' + entity_id)
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
        </script>
       
        
EOT
);
$page->render();