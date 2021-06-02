<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Form
 *
 * @author jesko
 */
class Form {
    
    private $form_method;
    private $form_objects;
    private $submit_btn_class;
    private $submit_btn_text;
    private $form_id;
    private $include_button;
    
    public function __construct($form_method = null, $form_objects = null, $submit_btn_text = "Save", $submit_btn_class = "btn btn-abc btn-block", $form_id = null) {
        $this->form_method = $form_method;
        $this->form_objects = $form_objects;
        $this->submit_btn_class = $submit_btn_class;
        $this->submit_btn_text = $submit_btn_text;
        $this->form_id = $form_id;
        $this->include_button = true;
    }
    public function getInclude_button() {
        return $this->include_button;
    }

    public function setInclude_button($include_button) {
        $this->include_button = $include_button;
        return $this;
    }

    public function getForm_id() {
        return $this->form_id;
    }

    public function setForm_id($form_id) {
        $this->form_id = $form_id;
        return $this;
    }

        
    public function __toString() {
        $str = "";
        
        $str .= "<form method=\"" . $this->getForm_method() . "\" role=\"form\" id=\"" . $this->getForm_id() . "\">\r\n";
        
        foreach ($this->getForm_objects() as $form_object) {
            $str .= $form_object;
        }
        if ($this->getInclude_button()) {
            $str .= "<button type=\"submit\" class=\"" . $this->getSubmit_btn_class() . "\">" . $this->getSubmit_btn_text() . "</button>";
        }
        $str .= "</form>\r\n";
        return $str;
    }

    public function getSubmit_btn_text() {
        return $this->submit_btn_text;
    }

    public function setSubmit_btn_text($submit_btn_text) {
        $this->submit_btn_text = $submit_btn_text;
        return $this;
    }

        
    public function getForm_method() {
        return $this->form_method;
    }

    public function getForm_objects() {
        return $this->form_objects;
    }

    public function getSubmit_btn_class() {
        return $this->submit_btn_class;
    }

    public function setForm_method($form_method) {
        $this->form_method = $form_method;
        return $this;
    }

    public function setForm_objects($form_objects) {
        $this->form_objects = $form_objects;
        return $this;
    }
    
    public function addText($text) {
        if ($this->getForm_objects() === null) {
            $this->setForm_objects(array());
        }
        array_push($this->form_objects, $text);
        return $this;
    }
    
    public function addFormObject(FormObject $form_object) {
        if ($this->getForm_objects() === null) {
            $this->setForm_objects(array());
        }
        array_push($this->form_objects, $form_object);
        return $this;
    }

    public function setSubmit_btn_class($submit_btn_class) {
        $this->submit_btn_class = $submit_btn_class;
        return $this;
    }


}

class SelectOption {
    private $selected;
    private $value;
    private $text;
    private $extras;
    
    public function __construct($text = null, $value = null, $selected = false, $extras = null) {
        $this->selected = $selected;
        $this->value = $value;
        $this->text = $text;
        $this->extras = $extras;
    }
    
    public function getExtras() {
        return $this->extras;
    }

    public function setExtras($extras) {
        $this->extras = $extras;
        return $this;
    }

        
    public function __toString() {
        
        $str = "<option";
        
        if ($this->getValue() !== null) {
            $str .= " value=\"" . $this->getValue() . "\"";
        }
        
        if ($this->getSelected()) {
            $str .= " selected";
        }
        
        if ($this->getExtras() !== null) {
            $str .= " " . $this->getExtras();
        }
        
        $str .= ">" . $this->getText() . "</option>";
        
        return $str;
    }

    public function getSelected() {
        return $this->selected;
    }

    public function getValue() {
        return $this->value;
    }

    public function getText() {
        return $this->text;
    }

    public function setSelected($selected) {
        $this->selected = $selected;
        return $this;
    }

    public function setValue($value) {
        $this->value = $value;
        return $this;
    }

    public function setText($text) {
        $this->text = $text;
        return $this;
    }


    
}

class SelectObject extends FormObject {
    
    private $select_options;
    private $labelClass;
    
    public function __construct($element_id = null, $element_extras = null, $element_label = null, $select_options = null, $include_div = true, $element_class = "form-control", $div_class = "form-group") {
        parent::__construct("select", $element_id, null, $element_extras, $element_label, $include_div, null, $element_class, $div_class);
        $this->select_options = $select_options;
    }
    
    public function getLabelClass() {
        return $this->labelClass;
    }

    public function setLabelClass($labelClass) {
        $this->labelClass = $labelClass;
        return $this;
    }

        
    public function __toString() {
        $str = "";
        
        if ($this->getInclude_div()) {
            $str .= "<div class=\"" . $this->getDiv_class() . "\">\r\n";
        }
        
        if ($this->getElement_label() !== null) {
            $str .= "<label";
            
            if ($this->getLabelClass() !== null && strlen(trim($this->getLabelClass())) > 0) {
                $str .= " class=\"" . trim($this->getLabelClass()) . "\"";
            } 
            
            if ($this->getElement_id() !== null) {
                $str .= " for=\"" . str_replace("[]", "", $this->getElement_id()) . "\"";
            }
            $str .= ">" . $this->getElement_label() . "</label>\r\n";
        }
        
        $str .= "<" . $this->getElement();
        
        if ($this->getElement_id() !== null) {
            $str .= " id=\"" . str_replace("[]", "", $this->getElement_id()) . "\" name=\"" . $this->getElement_id() . "\"";
        }
        
        if ($this->getElement_class() !== null) {
            $str .= " class=\"" . $this->getElement_class() . "\"";
        }
        
        if ($this->getElement_extras() !== null) {
            $str .= " " . $this->getElement_extras();
        }
        $str .= ">";
        
        foreach ($this->getSelect_options() as $select_option) {
            $str .= $select_option;
        }
        
        $str .= "</" . $this->getElement() . ">\r\n";
        
        if ($this->getInclude_div()) {
            $str .= "</div>\r\n";
        }
        
        return $str;
    }
    
    public function getSelect_options() {
        return $this->select_options;
    }

    public function setSelect_options($select_options) {
        $this->select_options = $select_options;
        return $this;
    }

    public function appendSelect_options(SelectOption $selectOption) {
        if ($this->getSelect_options() === null) {
            $this->select_options = array();
        }
        array_push($this->select_options, $selectOption);
        return $this;
    }

}

class FormObject {
    
    private $element;
    private $element_id;
    private $element_type;
    private $element_class;
    private $element_extras;
    private $element_label;
    private $include_div;
    private $div_class;
    private $default_value; //if textarea, put in tags.
    private $labelClass;
    
    public function __construct($element = null, $element_id = null, $element_type = null, $element_extras = null, $element_label = null, $include_div = true, $default_value = null, $element_class = "form-control", $div_class = "form-group") {
        $this->element = $element;
        $this->element_id = $element_id;
        $this->element_type = $element_type;
        $this->element_class = $element_class;
        $this->element_extras = $element_extras;
        $this->element_label = $element_label;
        $this->include_div = $include_div;
        $this->div_class = $div_class;
        $this->default_value = $default_value;
    }
    
    public function getLabelClass() {
        return $this->labelClass;
    }

    public function setLabelClass($labelClass) {
        $this->labelClass = $labelClass;
        return $this;
    }

    
    public function __toString() {
        $str = "";
        
        if ($this->getInclude_div()) {
            $str .= "<div class=\"" . $this->getDiv_class() . "\">\r\n";
        }
        
        if ($this->getElement_label() !== null) {
            $str .= "<label";
            
            if ($this->getLabelClass() !== null && strlen(trim($this->getLabelClass())) > 0) {
                $str .= " class=\"" . trim($this->getLabelClass()) . "\"";
            } 
            
            if ($this->getElement_id() !== null) {
                $str .= " for=\"" . str_replace("[]", "", $this->getElement_id()) . "\"";
            }
            $str .= ">" . $this->getElement_label() . "</label>\r\n";
        }
        
        $str .= "<" . $this->getElement();
        
        if ($this->getElement_id() !== null) {
            $str .= " id=\"" . str_replace("[]", "", $this->getElement_id()) . "\" name=\"" . $this->getElement_id() . "\"";
        }
        
        if ($this->getElement_type() !== null) {
            $str .= " type=\"" . $this->getElement_type() . "\"";
        }
        
        if ($this->getElement_class() !== null) {
            $str .= " class=\"" . $this->getElement_class() . "\"";
        }
        
        if ($this->getElement_extras() !== null) {
            $str .= " " . $this->getElement_extras();
        }
        
        
        if ($this->getDefault_value() !== null) {
            if ($this->getElement() === "textarea") {
                $str .= ">" . $this->getDefault_value();
            } else if ($this->getElement() === "button") {
                $str .= ">" . $this->getDefault_value();
            } else {
                $str .= " value=\"" . $this->getDefault_value() . "\"";
                $str .= ">";
            }
        } 
        
        if ($this->getDefault_value() === null) {
            $str .= ">";
        }
        if ($this->getElement() !== "input") {
            $str .= "</" . $this->getElement() . ">\r\n";
        }
        
        
        if ($this->getInclude_div()) {
            $str .= "</div>\r\n";
        }
        
        return $str;
    }
    
    public function getElement_id() {
        return $this->element_id;
    }

    public function setElement_id($element_id) {
        $this->element_id = $element_id;
        return $this;
    }

    
    public function getElement() {
        return $this->element;
    }

    public function getElement_type() {
        return $this->element_type;
    }

    public function getElement_class() {
        return $this->element_class;
    }

    public function getElement_extras() {
        return $this->element_extras;
    }

    public function getElement_label() {
        return $this->element_label;
    }

    public function getInclude_div() {
        return $this->include_div;
    }

    public function getDiv_class() {
        return $this->div_class;
    }

    public function getDefault_value() {
        return $this->default_value;
    }

    public function setElement($element) {
        $this->element = $element;
        return $this;
    }

    public function setElement_type($element_type) {
        $this->element_type = $element_type;
        return $this;
    }

    public function setElement_class($element_class) {
        $this->element_class = $element_class;
        return $this;
    }

    public function setElement_extras($element_extras) {
        $this->element_extras = $element_extras;
        return $this;
    }

    public function setElement_label($element_label) {
        $this->element_label = $element_label;
        return $this;
    }

    public function setInclude_div($include_div) {
        $this->include_div = $include_div;
        return $this;
    }

    public function setDiv_class($div_class) {
        $this->div_class = $div_class;
        return $this;
    }

    public function setDefault_value($default_value) {
        $this->default_value = $default_value;
        return $this;
    }



}
