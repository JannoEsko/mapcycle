<?php
global $user, $error, $errorstr, $success, $succstr;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$page = new Page();

$page->setTitle(Constants::$TOOL_NAME);

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

$page->appendContent("<div id='totChart'></div><br>");
$page->appendContent("");
$page->appendPostDivContent(<<<EOT
        
        <div id="entmodal" role="dialog" aria-labelledby="entmodaltitle" aria-hidden="true" class="modal fade" tabindex="-1">
        
            <div class="modal-dialog modal-lg" >
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 id="entmodaltitle" class="modal-title">Details</h4>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                            <span aria-hidden="true">&times;</span>
                        </button>

                    </div>
                    <div class="modal-body">
                        <div id="mcbtnbody"></div>
        <br>
                        <div id="entityBody"></div>
                    </div>
        <div class='modal-footer'>
        <button class='btn btn-outline-success float-right' data-dismiss="modal">Close</button>
        
        </div>
                </div>
            </div>
        
        </div>
        
        
        
        
        
        
        
        
        
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

$page->appendAdditionalScript(<<<EOT
        
        <script>
        var chart;
        $.post("{BASE_URL}/ajax", {getRollingPlayerStats: 1}, function(data) {
            try {
                var hnsstat = JSON.parse(data);
                $.each(hnsstat, function(i, dta) {
                    $.each(dta.dataPoints, function(j, points) {
                        points.click = entityModal;
                    });
                });
                chart = new CanvasJS.Chart("totChart", {
                    theme:"light2",
                    animationEnabled: true,
                    title:{
                            text: "Players"
                    },
                    axisY :{
                            title: "Number of players",
                            suffix: ""
                    },
                    toolTip: {
                            shared: "true"
                    },
                    legend:{
                            cursor:"pointer",
                            itemclick : toggleDataSeries
                    },
                    data: hnsstat
            });
            chart.render();

            } catch (e) {}
        
        });
        
        
function toggleDataSeries(e) {
	if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible ){
		e.dataSeries.visible = false;
	} else {
		e.dataSeries.visible = true;
	}
	chart.render();
}


        function entityModal(e) {
            var entity_id = e.dataPoint.entity_id;
            var mapcycle_id = e.dataPoint.mapcycle_id;
            var modalTitle = e.dataPoint.modalTitle;
            $.post("{BASE_URL}/ajax", {getEntityCards: 1, singleEntity: entity_id}, function(data) {
                try {
                    data = JSON.parse(data);

                    if (typeof data.error !== "undefined") {
                        toastr.error(data.error);
                    }
                    if (typeof data.html !== "undefined") {
                        $("#entityBody").html(data.html);
                        $("#entmodaltitle").html("Details for " + modalTitle);
                        $("#mcbtnbody").html("<a class='btn btn-outline-dark btn-block' target='_blank' href='{BASE_URL}/maps/mapcycle/" + mapcycle_id + "'>Manage the mapcycle</a>");
                        $("#entmodal").modal('toggle');
                    }
                } catch (e) {
                    toastr.error("Something went wrong.");
                }
            });
            
            
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
        
        
        
        </script>
        
EOT
);
$page->render();
