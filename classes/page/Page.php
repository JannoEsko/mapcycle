<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Page
 *
 * @author jesko
 */
class Page {
    //put your code here
    
    private $title;
    private $content;
    private $additionalScripts;
    private $postDivContent;
    private $replaceableTags;
    
    public function __construct($title = "", $content = "", $additionalScripts = "", $postDivContent = "", $replaceableTags = null) {
        $this->title = $title;
        $this->content = $content;
        $this->additionalScripts = $additionalScripts;
        $this->postDivContent = $postDivContent;
        $this->replaceableTags = $replaceableTags;
        
    }
    
    public function getReplaceableTags() {
        return $this->replaceableTags;
    }

    public function setReplaceableTags($replaceableTags) {
        $this->replaceableTags = $replaceableTags;
        return $this;
    }

    public function registerReplaceableTag($tagName, $tagValue) {
        if ($this->replaceableTags === null) {
            $this->replaceableTags = array();
        } 
        $this->replaceableTags[$tagName] = $tagValue;
        return $this;
    }
    
    public function appendPostDivContent($postDivContent) {
        $this->postDivContent .= "\r\n" . $postDivContent;
        return $this;
    }
    
    public function getPostDivContent() {
        return $this->postDivContent;
    }

    public function setPostDivContent($postDivContent) {
        $this->postDivContent = $postDivContent;
        return $this;
    }

    public function getAdditionalScripts() {
        return $this->additionalScripts;
    }

    public function setAdditionalScripts($additionalScripts) {
        $this->additionalScripts = $additionalScripts;
        return $this;
    }

    public function appendAdditionalScript($script) {
        $this->additionalScripts .= "\r\n" . $script;
        return $this;
    }
    
    public function getTitle() {
        return $this->title;
    }

    public function getContent() {
        return $this->content;
    }

    public function setTitle($title) {
        $this->title = $title;
        return $this;
    }

    public function setContent($content) {
        $this->content = $content;
        return $this;
    }
    
    public function appendContent($content, $nl2br = false) {
        if ($nl2br) {
            $content = nl2br($content);
        }
        $this->content .= "\r\n" . $content; //add a new line for better source readability.
        return $this;
    }
    
    public function appendArray($content, $nl2br = true) {
        return $this->appendContentBR(print_r($content, true), $nl2br);
    }
    
    public function appendContentBR($content, $nl2br = false) {
        if ($nl2br) {
            $content = nl2br($content);
        }
        $this->content .= "\r\n<br>$content";
        return $this;
    }
    
    
    public function appendTable($headers, $content, $keys = null, $div = true, $tableclass = "table table-bordered table-hover", $table_id = null, $tfoot = false, $divclass = "table-responsive", $return = false, $divhide = false) {
        
        if ($div === null) {
            $div = true;
        }
        
        if ($divclass === null) {
            $divclass = "table-responsive";
        }
        
        if ($return === null) {
            $return = false;
        }
        
        if ($divhide === null) {
            $divhide = false;
        }
        
        if ($tableclass === null) {
            $tableclass = "table table-bordered table-hover";
        }
        
        if (!is_array($headers)) {
            throw new InvalidArgumentException("headers must be arrays");
        }
        
        if ($content === null) {
            $content = array();
        }
        
        $str = "";
        if ($div) {
            $str .= "<div class=\"$divclass\"";
            
            if ($divhide) {
                $str .= " style='display: none;'";
            }
            
            $str .= ">\r\n";
        }
        $str .= "<table id=\"$table_id\" class=\"$tableclass\"";
        
        if ($divhide) {
            $str .= " style='display: none;'";
        }
        
        $str .= ">\r\n<thead>\r\n<tr>";
        
        foreach ($headers as $header) {
            
            $str .= "\r\n<th>$header</th>";
        }
        $str .= "\r\n</tr>\r\n</thead>\r\n<tbody>";
        foreach ($content as $values) {
            if (array_key_exists("class", $values)) {
                $str .= "\r\n<tr class=\"" . $values['class'] . "\"";
            } else {
                $str .= "\r\n<tr";
            }
            if (array_key_exists("trid", $values)) {
                $str .= " id=\"" . $values['trid'] . "\"";
            }
            
            if (array_key_exists("extras", $values)) {
                $str .= " " . $values['extras'] . " ";
            }
            
            $str .= ">";
            
            foreach ($values as $key => $value) {
                if (is_array($value) && (array_key_exists("tdid", $value) || array_key_exists("tooltip", $value))) {
                    if (array_key_exists("data-clipboard-text", $value)) {
                        $str .= "\r\n<td id=\"" . $value['tdid'] . "\" data-toggle='tooltip' title='" . $value['tooltip'] . "' class='dotted' data-clipboard-text='" . $value['data-clipboard-text'] . "'>" . $value['value'] . "</td>";
                    }
                    else if (array_key_exists("tooltip", $value)) {
                        $str .= "\r\n<td id=\"" . $value['tdid'] . "\" data-toggle='tooltip' title='" . $value['tooltip'] . "' class='dotted'>" . $value['value'] . "</td>";
                    } else {
                        $str .= "\r\n<td id=\"" . $value['tdid'] . "\">" . $value['value'] . "</td>";
                    }
                    
                    
                    
                    continue;
                }
                if (($keys === null || in_array($key, $keys)) && $key !== 'class' && $key !== 'trid' && $key !== "posttd" && $key !== "extras" && $key !== "posttdid" && $key !== "posttdextras") {
                    $str .= "\r\n<td>$value</td>";
                }
            }
            $str .= "\r\n</tr>";
            if (array_key_exists("posttd", $values)) {
                $str .= "\r\n<tr><td colspan=" . sizeof($headers);
                if (array_key_exists("posttdid", $values)) {
                    $str .= " id='" . $values['posttdid'] . "' ";
                } 
                
                if (array_key_exists("posttdextras", $values)) {
                    $str .= " " . $values['posttdextras'] . " ";
                }
                
                $str .= ">" . $values['posttd'] . "</td></tr>\r\n";
            }
            
        }
        $str .= "\r\n</tbody>";
        
        if ($tfoot) {
            $str .= "\r\n<tfoot>\r\n<tr>";
            foreach ($headers as $header) {
            
                $str .= "\r\n<th>$header</th>";
            }
            $str .= "\r\n</tr>\r\n</tfoot>";
        }
        
        $str .= "</table>";
        
        if ($div) {
            $str .= "\r\n</div>";
        }
        
        if ($return) {
            return $str;
        }
        
        return $this->appendContent($str);
    }
    
    public function displayError($ex, $isAdmin = false) {
        $this->setContent("<div class='alert alert-danger'><b>An error occured. Please try the action again. <br>Error details: " . $ex->getMessage());
        if ($isAdmin) {
            $this->appendContentBR("<br>Initial file: " . $ex->getFile() . " : " . $ex->getLine());
            $this->appendContentBR("<br>Stack trace:<br>" . nl2br($ex->getTraceAsString()));
        }
        $this->appendContent("</div>");
        $this->render();
        exit();
    }

    public function render($template = null) {
        if ($template === null) {
            $template = Constants::$PAGE_TEMPLATE;
        }
        $debugStr = "";
        if (Constants::$DEBUG) {
            $debugStr = "+debug";
        }
        
        $pageContents = 
            str_replace("{BASE_URL}", Constants::$PAGE_URL, 
                str_replace("{DEBUG}", $debugStr, 
                    str_replace("{TOOL_NAME}", Constants::$TOOL_NAME, 
                        str_replace("{TOOL_VERSION}", Constants::$TOOL_VERSION, 
                            str_replace("{TOOL_BRANCH}", Constants::$TOOL_BRANCH, 
                                str_replace("{POSTDIV}", $this->getPostDivContent(), 
                                    str_replace("{SCRIPT}", Constants::getJS() . $this->getAdditionalScripts(), 
                                        str_replace("{CSS}", Constants::getCSS(), 
                                            str_replace("{BODY}", $this->getContent(), 
                                                str_replace("{TITLE}", $this->getTitle(), 
                                                    file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . ".." . 
                                                        DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "static" . 
                                                        DIRECTORY_SEPARATOR . "html" . DIRECTORY_SEPARATOR . 
                                                        $template
                                                    )
                                                )
                                            )
                                        )
                                    )
                                )
                            )
                        )
                    )
                )
            );
        
        if ($this->getReplaceableTags() !== null && is_array($this->getReplaceableTags()) && sizeof($this->getReplaceableTags()) > 0) {
            foreach ($this->getReplaceableTags() as $replaceableTag => $replaceableValue) {
                $pageContents = str_replace($replaceableTag, $replaceableValue, $pageContents);
            }
        }
        
        echo $pageContents;
        
        return $this;
    }

}

