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
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */
?>
<?php

error_reporting(E_ALL);

/**
 *
 *  * TODO Move it to the taoResultServer
 * TAO - taoResultServer/models/classes/class.ResultServerAuthoringService.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 20.09.2012, 17:44:09 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package taoResultServer
 * @subpackage models_classes
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * The Service class is an abstraction of each service instance. 
 * Used to centralize the behavior related to every servcie instances.
 *
 * @author Joel Bout, <joel.bout@tudor.lu>
 */
require_once('tao/models/classes/class.GenerisService.php');

/* user defined includes */
// section 10-13-1-39-5129ca57:1276133a327:-8000:000000000000201D-includes begin
// section 10-13-1-39-5129ca57:1276133a327:-8000:000000000000201D-includes end

/* user defined constants */
// section 10-13-1-39-5129ca57:1276133a327:-8000:000000000000201D-constants begin
// section 10-13-1-39-5129ca57:1276133a327:-8000:000000000000201D-constants end

/**
 * Short description of class
 *
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package taoResultServer
 * @subpackage models_classes
 */
class taoResultServer_models_classes_ResultServerAuthoringService
    extends tao_models_classes_GenerisService
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute resultServerClass
     *
     * @access protected
     * @var Class
     */
    protected $resultServerClass = null;

    // --- OPERATIONS ---

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return mixed
     */
    public function __construct()
    {
        // section 10-13-1-39-5129ca57:1276133a327:-8000:000000000000216F begin
        
    	parent::__construct();
		
		$this->resultServerClass = new core_kernel_classes_Class(TAO_RESULTSERVER_CLASS);
    	
        // section 10-13-1-39-5129ca57:1276133a327:-8000:000000000000216F end
    }

    /**
     * Short description of method createResultServerClass
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  Class clazz
     * @param  string label
     * @param  array properties
     * @return core_kernel_classes_Class
     */
    public function createResultServerClass( core_kernel_classes_Class $clazz = null, $label = '', $properties = array())
    {
        $returnValue = null;

        // section 10-13-1-39-5129ca57:1276133a327:-8000:0000000000002171 begin
        
    	if(is_null($clazz)){
			$clazz = $this->resultServerClass;
		}
		
		if($this->isResultServerClass($clazz)){
		
			$resultServerClass = $this->createSubClass($clazz, $label);//call method form TAO_model_service
			
			foreach($properties as $propertyName => $propertyValue){
				$myProperty = $resultServerClass->createProperty(
					$propertyName,
					$propertyName . ' ' . $label .' resultServer property from ' . get_class($this) . ' the '. date('Y-m-d h:i:s') 
				);
			}
			$returnValue = $resultServerClass;
		}
        
        // section 10-13-1-39-5129ca57:1276133a327:-8000:0000000000002171 end

        return $returnValue;
    }

    /**
     * Short description of method deleteResultServer
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  Resource resultServer
     * @return boolean
     */
    public function deleteResultServer( core_kernel_classes_Resource $resultServer)
    {
        $returnValue = (bool) false;

        // section 10-13-1-39-5129ca57:1276133a327:-8000:0000000000002173 begin
        
    	if(!is_null($resultServer)){
			$returnValue = $resultServer->delete();
		}
        
        // section 10-13-1-39-5129ca57:1276133a327:-8000:0000000000002173 end

        return (bool) $returnValue;
    }

    /**
     * Short description of method deleteResultServerClass
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  Class clazz
     * @return boolean
     */
    public function deleteResultServerClass( core_kernel_classes_Class $clazz)
    {
        $returnValue = (bool) false;

        // section 10-13-1-39-5129ca57:1276133a327:-8000:0000000000002175 begin
        
    	if(!is_null($clazz)){
			if($this->isResultServerClass($clazz) && $clazz->getUri() != $this->resultServerClass->getUri()){
				$returnValue = $clazz->delete();
			}
		}
        
        // section 10-13-1-39-5129ca57:1276133a327:-8000:0000000000002175 end

        return (bool) $returnValue;
    }

    



    /**
     * Short description of method isResultServerClass
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  Class clazz
     * @return boolean
     */
    public function isResultServerClass( core_kernel_classes_Class $clazz)
    {
        $returnValue = (bool) false;

        // section 10-13-1-39-5129ca57:1276133a327:-8000:000000000000217D begin
        
    	if($clazz->getUri() == $this->resultServerClass->getUri()){
			$returnValue = true;	
		}
		else{
			foreach($this->resultServerClass->getSubClasses(true) as $subclass){
				if($clazz->getUri() == $subclass->getUri()){
					$returnValue = true;
					break;	
				}
			}
		}
        
        // section 10-13-1-39-5129ca57:1276133a327:-8000:000000000000217D end

        return (bool) $returnValue;
    }

    /**
     * Short description of method getResultServerClass
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  string uri
     * @return core_kernel_classes_Class
     */
    public function getResultServerClass($uri = '')
    {
        $returnValue = null;

        // section 10-13-1-39-5129ca57:1276133a327:-8000:000000000000217F begin
        
    	if(empty($uri) && !is_null($this->resultServerClass)){
			$returnValue = $this->resultServerClass;
		}
		else{
			$clazz = new core_kernel_classes_Class($uri);
			if($this->isResultServerClass($clazz)){
				$returnValue = $clazz;
			}
		}
        
        // section 10-13-1-39-5129ca57:1276133a327:-8000:000000000000217F end

        return $returnValue;
    }

    /**
     * Short description of method getDelpoymentParameters
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @param  Resource resultServer
     * @return array
     */
    /*
    public function getDelpoymentParameters( core_kernel_classes_Resource $resultServer)
    {
        $returnValue = array();

        // section 127-0-1-1--1fd8ff6b:12c3688e878:-8000:00000000000028A1 begin
        
        if(!is_null($resultServer)){
        	
        	$resultUrl 		= (string) $resultServer->getOnePropertyValue(new core_kernel_classes_Property(TAO_DELIVERY_RESULTSERVER_RESULT_URL_PROP));
        	$eventUrl		= (string) $resultServer->getOnePropertyValue(new core_kernel_classes_Property(TAO_DELIVERY_RESULTSERVER_EVENT_URL_PROP));
        	$matchingUrl	= (string) $resultServer->getOnePropertyValue(new core_kernel_classes_Property(TAO_DELIVERY_RESULTSERVER_MATCHING_URL_PROP));
        	$matchingSide 	= $resultServer->getOnePropertyValue(new core_kernel_classes_Property(TAO_DELIVERY_RESULTSERVER_MATCHING_SERVER_PROP));
        	
        	$returnValue = array(
        		'save_result_url' 	=> preg_match('/^\//',$resultUrl)? ROOT_URL.$resultUrl : $resultUrl,
        		'save_event_url' 	=> preg_match('/^\//',$eventUrl)? ROOT_URL.$eventUrl : $eventUrl,
        		'matching_url' 		=> preg_match('/^\//',$matchingUrl)? ROOT_URL.$matchingUrl : $matchingUrl,
        		'matching_server' 	=> ($matchingSide->getUri() == GENERIS_TRUE)
        	);
        }
		
        // section 127-0-1-1--1fd8ff6b:12c3688e878:-8000:00000000000028A1 end

        return (array) $returnValue;
    }
     * 
     */

} /* end of class taoResultServer_models_classes_ResultServerAuthoringService */

?>