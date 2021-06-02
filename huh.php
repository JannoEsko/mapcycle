<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once __DIR__ . "/autoload_cron.php";

$currlive = PushToLive::getLiveMapcycle();

$potentialLive = PushToLive::getLiveCandidate();



$newLive = null;

if (($currlive === null || !($currlive instanceof PushToLive)) && ($potentialLive === null || !($potentialLive instanceof PushToLive))) {
    exit(); //nothing to do.
}

if ($currlive !== null && $currlive instanceof PushToLive && $potentialLive !== null && $potentialLive instanceof PushToLive) {
    if (intval($currlive->getPushtolive_id()) === intval($potentialLive->getPushtolive_id())) {
        if ($currlive->isLive()) {
            exit();
        } else {
            //resend to live
            try {
                $currlive->setLive(1)->write()->pushToProd();
                XenApi::addPost("Resent mc (id " . $currlive->getMapcycle_id() . ") to live.", 6161, 1);
            } catch (Exception $ex) {
                
                XenApi::addPost("Exception on CRON (fail to resend to Live)\r\n" . $ex->getMessage() . "\r\nFile - " . $ex->getFile() . " (" . $ex->getLine() . ")\r\n\r\nFull trace: " . $ex->getTraceAsString(), 6161, 1);
            }
            
            exit();
        }
    } else {
        try {
            $potentialLive->setLive(1)->write()->pushToProd();
            $currlive->setLive(0)->write();
            XenApi::addPost("Pushed new mc (id " . $potentialLive->getMapcycle_id() . ") to live.", 6161, 1);
        } catch (Exception $ex) {
            $currlive->setLive(1)->write()->pushToProd();
            $potentialLive->setLive(0)->setDeleted(1)->write();
            XenApi::addPost("Exception on CRON (fail to send potentialLive, deleted potential, readded currlive)\r\n" . $ex->getMessage() . "\r\nFile - " . $ex->getFile() . " (" . $ex->getLine() . ")\r\n\r\nFull trace: " . $ex->getTraceAsString(), 6161, 1);
        }
        
        exit();
    }
}

if ($currlive === null || !($currlive instanceof PushToLive)) {
    try {
        $potentialLive->setLive(1)->write()->pushToProd();
        XenApi::addPost("Currlive = null, Pushed new mc (id " . $potentialLive->getMapcycle_id() . ") to live.", 6161, 1);
    } catch (Exception $ex) {
        $potentialLive->setDeleted(1)->setLive(0)->write();
        XenApi::addPost("Exception on CRON (fail to send potential live, deleted potential)\r\n" . $ex->getMessage() . "\r\nFile - " . $ex->getFile() . " (" . $ex->getLine() . ")\r\n\r\nFull trace: " . $ex->getTraceAsString(), 6161, 1);
    }
    
    exit();
}

if ($currlive !== null && $currlive instanceof PushToLive) {
    if ($currlive->isLive()) {
        exit();
    } else {
        try {
            $currlive->setLive(1)->write()->pushToProd();
            XenApi::addPost("Resent mc (id " . $currlive->getMapcycle_id() . ") to live.", 6161, 1);
        } catch (Exception $ex) {
            //$currlive->setDeleted(1)->setLive(0)->write();
            XenApi::addPost("Exception on CRON (failed to resend live)\r\n" . $ex->getMessage() . "\r\nFile - " . $ex->getFile() . " (" . $ex->getLine() . ")\r\n\r\nFull trace: " . $ex->getTraceAsString(), 6161, 1);
        }
        
    }
}
