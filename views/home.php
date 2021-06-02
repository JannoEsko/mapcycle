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

$page->appendContent("<h4>Welcome to 3D# Mapcycle page.</h4>");

$page->appendContent("<h6>You can use your forum credentials to log in from the top right corner. By logging in, you can also vote on maps and upload entities.</h6>");
$page->appendContent("<h6>On the left side bar, you can go to Maps -> Entities to see all the entities we have, you can also filter based on current live mapcycle</h6>");
$customMapsPackage = "/3D_custommaps.pk3";

$individualMaps = array(
    array("map" => "bigfreakinghouse", "size" => "2.8 MB", "dl" => "/bigfreakinhouse.pk3")
    , array("map" => "gaf_glasswar", "size" => "760 KB (less than 1MB)", "dl" => "/gaf_glasswar.pk3")
    , array("map" => "mp_colcity", "size" => "1.2 MB", "dl" => "/mp_colcity.pk3")
    , array("map" => "mp_crazyxmas", "size" => "11 MB", "dl" => "/mp_crazyxmas.pk3")
    , array("map" => "My_Home", "size" => "5.4 MB", "dl" => "/My_Home.pk3")
    , array("map" => "rats", "size" => "1 MB", "dl" => "/rats.pk3")
    , array("map" => "swimminpool", "size" => "5.1 MB", "dl" => "/swimminpool.pk3")
    , array("map" => "toyz", "size" => "18 MB", "dl" => "/toyz.pk3")
    , array("map" => "^wOverdose", "size" => "8.9 MB", "dl" => "/woverdose.pk3")
);

$individualMapsTableString = "";

foreach ($individualMaps as $im) {
    $individualMapsTableString .= "<tr><td><b>" . $im['map'] . "</b></td><td>" . $im['size'] . "</td><td><a href=\"" . $im['dl'] . "\" target=\"_blank\" class=\"btn btn-primary btn-block\">Download " . $im['map'] . "</a></td></tr>";
}

$page->appendContent(<<<EOT
   <div class="row">
        <div class="col" style='padding-bottom: 5px;'>
            <div class="card h-100">
                <div class="card-body">
        
                    <h5 class="card-title">Custom maps package</h5>
                    <p class="card-text" style='color: black;'>You can download the whole 3D# Custom Maps package by clicking the button below.<br>Please put the file in your SOF2/base/ folder.<br>By downloading this, you don't need to download the maps individually.</p>
                    <a href="$customMapsPackage" target="_blank" class='btn btn-primary btn-block'>Download maps package</a>
                </div>
        
            </div>
        
        </div>
        <div class="col" style='padding-bottom: 5px;'>
            <div class="card h-100">
                <div class="card-body">
        
                    <h5 class="card-title">Individual maps</h5>
                    <p class="card-text" style='color: black;'>We also offer a possibility to download the maps individually, maybe you have some of the maps already or maybe you don't want to exhaust your data limits... :)<br>Please put all of the .pk3 files into your SOF2/base/ folder.</p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Map name</th>
                                    <th>Map size</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                $individualMapsTableString
                            </tbody>
                        </table>
        
                    </div>
                </div>
        
            </div>
        </div>
        
        
    </div>
   
EOT
);

$page->render();
