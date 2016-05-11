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
    const TESTTAKER = 'testtaker';
    const DELIVERY = 'delivery';
    const DELIVERY_EXECUTION = 'deliveryExecution';

    protected $service;

    /**
     * Return the service for Qti Result
     *
     * @return QtiResultsService
     */
    protected function getQtiResultService()
    {
        if (!$this->service) {
            $this->service = QtiResultsService::singleton();
            $this->service->setServiceLocator($this->getServiceManager());
        }
        return $this->service;
    }

    /**
     * Override tao_actions_CommonRestModule::get to route only to getDeliveryExecution
     * Valid parameters & get delivery execution
     *
     * @param null $uri
     * @return void
     */
    protected function get($uri = null)
    {
        try {
            $deliveryExecution = $this->getValidDeliveryExecutionFromParameters();
            $data = $this->getQtiResultService()->getDeliveryExecutionXml($deliveryExecution);
            if (empty($data)) {
                throw new common_exception_NotFound('No data to output.');
            } else {
                echo $this->returnValidXmlSuccess($data);
            }
        } catch (Exception $e) {
            $this->returnFailure($e);
        }
    }

    /**
     * Valid parameters TESTTAKER uri and DELIVERY uri couple
     * OR DELIVERYEXECUTION uri
     *
     * @return core_kernel_classes_Resource|mixed
     * @throws common_exception_InvalidArgumentType
     * @throws common_exception_MissingParameter
     */
    protected function getValidDeliveryExecutionFromParameters()
    {
        if ($this->hasRequestParameter(self::TESTTAKER) && $this->hasRequestParameter(self::DELIVERY)) {
            return $this->getQtiResultService()->getDeliveryExecutionByTestTakerAndDelivery(
                $this->getRequestParameter(self::DELIVERY),
                $this->getRequestParameter(self::TESTTAKER)
            );
        } elseif ($this->hasRequestParameter(self::DELIVERY_EXECUTION)) {
            return $this->getQtiResultService()->getDeliveryExecutionById(
                $this->getRequestParameter(self::DELIVERY_EXECUTION)
            );
        } else {
            throw new common_exception_MissingParameter(self::TESTTAKER . ' coupled with ' . self::DELIVERY .
                ', or ' . self::DELIVERY_EXECUTION, $this->getRequestURI());
        }
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
}