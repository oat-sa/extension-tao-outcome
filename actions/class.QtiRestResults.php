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

class taoResultServer_actions_QtiRestResults extends tao_actions_RestController
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
     * Entry point of taoQtiRestResult api
     * For the moment only get method is allowed
     *
     * @throws common_exception_BadRequest
     */
    public function index()
    {
        try {
            if ($this->getRequestMethod()!='GET') {
                throw new common_exception_BadRequest($this->getRequestURI());
            }
            $this->get();
        } catch (Exception $e) {
            $this->returnFailure($e);
        }
    }

    /**
     * Valid parameters & get delivery execution
     *
     * @throws Exception
     * @throws common_exception_MissingParameter
     * @throws common_exception_NotFound
     * @return void
     */
    protected function get()
    {
        $deliveryExecution = $this->getValidDeliveryExecutionFromParameters();
        $data = $this->getQtiResultService()->getDeliveryExecutionXml($deliveryExecution);
        if (empty($data)) {
            throw new common_exception_NotFound('No data to output.');
        }
        $this->returnValidXml($data);
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
     * Valid the xml output
     *
     * @param $data
     * @return void
     * @throws Exception
     */
    protected function returnValidXml($data)
    {
        $doc = @simplexml_load_string($data);
        if (!$doc) {
            common_Logger::i('invalid xml result');
            throw new Exception('Xml output is malformed.');
        }
        echo $data;
        exit(0);
    }
}