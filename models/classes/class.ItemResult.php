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
class taoResultServer_models_classes_ItemResult {

    /**
     * @var taoResultServer_models_classes_context
     */
    private $identifier;
    /**
     * When a test result is given the following item results must relate only to items that were selected for presentation as part of the corresponding test session. Furthermore, all items selected for presentation should be reported with a corresponding itemResult.
     * @var taoResultServer_models_classes_testResult
     */
    private $sequenceIndex;
    /**
     *
     *The date stamp of when this result was recorded.
     * @var array taoResultServer_models_classes_itemResult
     */
    private $dateStamp;
    /**
     * The session status is used to interpret the values of the item variables. See sessionStatus below.
     * @ var {initial, pendingSubmission, pendingResponseProcessing, Final}
     * should move to an enumeration
     */
    private $sessionStatus;
	/**
	 * @var array taoResultServer_models_classes_itemVariable
	 */
     private $itemVariables;
	/**
	 *
	 * @var string
	 */
     private $candidateComment;

     public function setIdentifier($identifier){
	 $this->identifier = $identifier;
     }
     public function getIdentifier(){
	 return $this->getIdentifier();
     }
     /**
      * @param boolean
      */
     public function setSequenceIndex($sequenceIndex){
	 $this->sequenceIndex = $sequenceIndex;
     }
     public function getSequenceIndex(){
	 return $this->sequenceIndex;
     }
     public function setDateStamp(DateTime $dateTime = null){
         if ($dateTime == null){
         $this->dateStamp = new DateTime('NOW');
         }
     }
     public function getDateStamp(){
	 return $this->dateStamp;
     }
     public function setSessionStatus($status = "initial"){
	 if (!(in_array($status, array("initial","pendingSubmission","pendingResponseProcessing", "final")))){
	     throw  new common_exception_InvalidArgumentType("status");
	     }
	 $this->status = $status;
     }
     public function getSessionStatus(){
	 return $this->status;
     }
      public function getItemVariables(){
	return $this->itemVariables;
    }
    public function addItemVariable(taoResultServer_models_classes_itemVariable $itemVariable){
	$this->itemVariables[] = $itemVariable;
    }

    public function setCandidateComment($candidateComment){
	$this->candidateComment = $candidateComment;
    }
    public function getCandidateComment(){
	return $this->candidateComment;
    }
}