<?php


require_once __DIR__ . "/classes/mapcycle/Entity.php";
require_once __DIR__ . "/classes/mapcycle/Mapcycle.php";
require_once __DIR__ . "/classes/form/Form.php";



function exceptionHandler($ex) {
    //execution is halted, so show an error message.
    global $user, $page;
    $isAdmin = false;
    if (!($user instanceof User)) {
        if (isset($_SESSION['member_id'])) {
            $member_id = intval($_SESSION['member_id']);
            $user = new User($member_id);
            $isAdmin = $user->populate()->populateAccess()->getAdmin_access();
        } 
    } else {
        $isAdmin = $user->populateAccess()->getAdmin_access();
    }
    if (!($page instanceof Page)) {
        //no page initialized.
        $page = new Page("Error | Mapcycle");
    }
    $login_txt_action = "";
$login_txt = "";
if ($user !== null && $user instanceof User && $user->isLoggedIn()) {
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
    $page->displayError($ex, $isAdmin);
}

set_exception_handler("exceptionHandler");


spl_autoload_register(function($class_name) {
    $classes_dir = __DIR__ . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR;
    $file = $classes_dir . $class_name . DIRECTORY_SEPARATOR . $class_name . '.php';
    $file2 = $classes_dir . $class_name . '.php';
    $file3 = $classes_dir . "XenAPI" . DIRECTORY_SEPARATOR . $class_name . ".php";
    $file4 = $classes_dir . strtolower($class_name) . DIRECTORY_SEPARATOR . $class_name . ".php";
    $file5 = $classes_dir . strtolower($class_name) . "s" . DIRECTORY_SEPARATOR . $class_name . ".php";
    $file6 = $classes_dir . "mapcycle" . DIRECTORY_SEPARATOR . $class_name . ".php";
    if (file_exists($file)) require_once $file;
    if (file_exists($file2)) require_once $file2;
    if (file_exists($file3)) require_once $file3;
    if (file_exists($file4)) require_once $file4;
    if (file_exists($file5)) require_once $file5;
    if (file_exists($file6)) require_once $file6;
}, true, true);

$sql = new SQL(Constants::$SQL_HOST, Constants::$SQL_USER, Constants::$SQL_PASS, Constants::$SQL_DB);





function bool2str($input, $trueval = "Yes", $falseval = "No") {
    if ($input) {
        return $trueval;
    }
    return $falseval;
}

function getUserIP() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    } else if (isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
        $ipaddress = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
    } else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    } else if (isset($_SERVER['HTTP_FORWARDED'])) {
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    } else if (isset($_SERVER['REMOTE_ADDR'])) {
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    } else {
        $ipaddress = 'UNKNOWN';
    }
    return $ipaddress;
}

function int2bool($input) {
    return intval($input) === 1;
}

function bool2int($input) {
    if ($input) {
        return 1;
    }
    return 0;
}


function leftchar($string, $leftSpcCount, $char = " ", $trimFirst = true) {
    if ($trimFirst) {
        $string = trim($string);
    }
    return substr($string . str_repeat($char, $leftSpcCount), 0, $leftSpcCount);
}

function utf8_array($array) {
    array_walk_recursive($array, function(&$item, $key) {
        if (!mb_detect_encoding($item, "utf-8", true)) {
            $item = utf8_encode($item);
        }
    });
    return $array;
}

function trimarray($array) {
	array_walk_recursive($array, function(&$item, $key) {
		$item = trim($item);
	});
	return $array;
}

function findInArray($needle, $haystack) {
    foreach ($haystack as $hs) {
        if (trim($hs) === trim($needle)) {
            return true;
        }
    }
    return false;
}

function rectrim($str, $char) {
    $len = strlen($str);
    $newstr = trim(trim($str), $char);
    $newlen = strlen($newstr);
    if ($newlen === $len) {
        return $newstr;
    } else {
        return rectrim($newstr, $char);
    }
}

function translate($str) {
    $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
    $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
    return str_replace($a, $b, $str);
}

function openCloseAnalyzer($input) {
    if (strpos($input, "\r\n")) {
        $input = explode("\r\n", $input);
    } else if (strpos($input, "\r")) {
        $input = explode("\r", $input);
    } else {
        $input = explode("\n", $input);
    }
    
    
    $lineProblems = array();
    $ob = 0;
    $cb = 0;
    $fline = 1;
    $lsln = 1;
    
    $lastSeenChar = "";
    $lsstr = "";
    
    foreach ($input as $line) { 
        $line = trim($line);
        if (stripos("{", $line) !== false || trim($line) === "{") {

            if ($lastSeenChar === "{") {
                $lineProblems[] = array("line" => $fline, "seenChar" => "{", "expectChar" => "}", "lsstr" => $lsstr, "lsln" => $lsln);
            }
            $lastSeenChar = "{";
            $lsstr = $line;
            $lsln = $fline;
            $ob++;
        } else if (stripos("}", $line) !== false || trim($line) === "}") {

            if ($lastSeenChar === "}") {
                $lineProblems[] = array("line" => $fline, "seenChar" => "}", "expectChar" => "{", "lsstr" => $lsstr, "lsln" => $lsln);
            }
            $lastSeenChar = "}";
            $lsstr = $line;
            $lsln = $fline;
            $cb++;
        }
        
        $fline++;
    }
    
    return $lineProblems;
    
}



require_once __DIR__ . "/routes.php";


