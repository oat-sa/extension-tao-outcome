<?php
/*
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2013 (original work) Open Assessment Technologies S.A.
 *
 */

/**
 * Description of taoResultServer_models_classes_assessmentResult
 *
 * @author plichart
 */

/**
 * An Assessment Result is used to report the results of a candidate's interaction
 * with a test and/or one or more items attempted. Information about the test is optional,
 *  in some systems it may be possible to interact with items that are not organized into a test at all. For example, items that are organized with learning resources and presented individually in a formative context.
 */
class taoResultServer_models_classes_TestResult {

    /**
     * The identifier of the test for which this is a result.
     * @var string
     */
    private $identifier;
    /**
     * The date stamp of when this result was recorded.
     * @var datetime
     */
    private $datestamp;
    /**
     * The values of the test outcomes and any durations that were tracked during the test. Note that durations are reported as built-in test-level response variables with name duration. The duration of individual test parts or sections being distinguished by prefixing them with the associated identifier as described in Assessment Test, Section and Item Information Model.
     * @var array taoResultServer_models_classes_itemVariable
     */
    private $itemVariables;


    public function setIdentifier($identifier){
	$this->identifier = $identifier;
    }
    public function getIdentifier(){
	return $this->getIdentifier();
    }

    public function setDateStamp(){
	$this->dateStamp = new DateTime('NOW');
    }

    public function getDateStamp(){
	return $this->getDateStamp();
    }

    public function getTestVariables(){
	return $this->itemVariables;
    }

    public function addTestVariable(taoResultServer_models_classes_ItemVariable $itemVariable){
	$this->itemVariables[] = $itemVariable;
    }

}

?>