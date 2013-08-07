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
abstract class taoResultServer_models_classes_ResponseVariable extends taoResultServer_models_classes_ItemVariable{

    /**
     * When a response variable is bound to an interaction that supports the shuffling of choices, the sequence of choices experienced by the candidate will vary between test instances. When shuffling is in effect, the sequence of choices should be reported as a sequence of choice identifiers using this attribute.
     * @var array
     */
    private $choicesequence;
    /**
     * The correct response may be output as part of the report if desired. Systems are not limited to reporting correct responses declared in responseDeclarations. For example, a correct response may be set by a templateRule or may simply have been suppressed from the declaration passed to the delivery engine (e.g., for security).
     * @var string (todo should be a class)
     */
    private $correctResponse;
    /**
     *
     * @var  candidateResponse
     */
    private $candidateResponse;

    public function setChoiceSequence($choicesequence){
	$this->choiceSequence = $choiceSequence;
    }
    public function getChoiceSequence(){
	return $this->choiceSequence;
    }
    public function setCorrectResponse($correctResponse){
	$this->correctResponse = $correctResponse;
    }
    public function getCorrectResponse(){
	return $this->correctResponse;
    }
    public function setCandidateResponse(taoResultServer_models_classes_CandidateResponse $candidateResponse){
	$this->candidateResponse = $candidateResponse;
    }
    public function getCandidateResponse(){
	return $this->candidateResponse;
    }
}

?>