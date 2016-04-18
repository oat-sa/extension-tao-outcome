<?php
/**
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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

//http://tao.dev/taoResultServer/QtiRestResults?testtaker=http%3A%2F%2Ftao.local%2Fmytao.rdf%23i1460560178726251&delivery=http%3A%2F%2Ftao.local%2Fmytao.rdf%23i14607116346750186

use oat\taoResultServer\models\classes\QtiResultsService;

class taoResultServer_actions_QtiRestResults extends \tao_actions_CommonRestModule
{
    /**
     * taoResultServer_actions_QtiRestResults constructor.
     * Pass model service to handle http call business
     */
    public function __construct()
    {
        parent::__construct();
        $this->service = QtiResultsService::singleton();
    }

    /**
     * Return a xml output as 200 rest response
     *
     * @param $data
     * @return mixed
     * @throws Exception
     */
    protected function returnValidXmlSuccess($data)
    {
        $doc = @simplexml_load_string($data);

        if ($doc) {
            return $data;
        } else {
            common_Logger::i('invalid xml result');
            throw new Exception('Xml output is malformed.');
        }
    }

    /**
     * Override tao_actions_CommonRestModule::get to route only to getDeliveryExecution
     *
     * @param null $uri
     * @return void
     */
    protected function get($uri = null){
        try {
            $this->service->setParameters($this->getParameters());
            $data = $this->service->getDeliveryExecution();
            if (empty($data)) {
                common_Logger::e('Empty delivery execution');
                throw new common_exception_NoContent('No data to output.');
            } else {
                echo $this->returnValidXmlSuccess($data);
            }
        } catch (Exception $e) {
            $this->returnFailure($e);
        }
    }

    /**
     * Optionnaly a specific rest controller may declare
     * aliases for parameters used for the rest communication
     */
    protected function getParametersAliases()
    {
        return array(
            "testtaker" => PROPERTY_DELVIERYEXECUTION_SUBJECT,
            "delivery"  => PROPERTY_DELVIERYEXECUTION_DELIVERY
        );
    }

    /**
     * Optionnal Requirements for parameters to be sent on every service
     */
    protected function getParametersRequirements()
    {
        return array(
            "get" => array(
                PROPERTY_DELVIERYEXECUTION_SUBJECT,
                PROPERTY_DELVIERYEXECUTION_DELIVERY
            )
        );
    }

}