<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class
 *
 * @author plichart
 */
interface taoResultServer_models_classes_persistence_ResultsPersistence {
/**
 * @var taoResultServer_models_classes_assessmentResult
 */
    /**
     * if none found  a new assessmentResult is created
     */
    abstract function load($sessionIdentifier);
    abstract function save();
}
?>