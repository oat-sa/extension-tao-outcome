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

use oat\taoResultServer\models\classes\ResultService;

class taoResultServer_actions_QtiRestResults extends tao_actions_RestController
{
    const TESTTAKER = 'testtaker';
    const DELIVERY = 'delivery';
    const DELIVERY_EXECUTION = 'deliveryExecution';
    const RESULT = 'result';
    
    protected $service;
    
    public function getQtiResultXml()
    {
        try
        {
            $this->checkMethod();
        
            $this->validateParams(array(self::DELIVERY, self::RESULT));
            $deliveryId = $this->getRequestParameter(self::DELIVERY);
            $resultId = $this->getRequestParameter(self::RESULT);
        
            $this->returnValidXml($this->getQtiResultService()->getQtiResultXml($deliveryId, $resultId));
        } catch (Exception $e) {
            $this->returnFailure($e);
        }
    }

    /**
     * Return the service for Result
     *
     * @return ResultService
     */
    protected function getQtiResultService()
    {
        if (!$this->service) {
            $this->service = $this->getServiceManager()->get(ResultService::SERVICE_ID);
        }
        return $this->service;
    }

    /**
     * It's based on TestTaker and Delivery. Parameters are in URI format.
     * It fetches the latest Delivery Execution.
     *
     * There are two endpoints because of swagger. In this way, it's possible to describe the parameters properly.
     *
     * @author Gyula Szucs, <gyula@taotesting.com>
     */
    public function getLatest()
    {
        try
        {
            $this->checkMethod();

            $this->validateParams(array(self::TESTTAKER, self::DELIVERY));

            $deliveryExecution = $this->getQtiResultService()->getDeliveryExecutionByTestTakerAndDelivery(
                $this->getRequestParameter(self::DELIVERY),
                $this->getRequestParameter(self::TESTTAKER)
            );

            $this->returnValidXml($this->getQtiResultService()->getDeliveryExecutionXml($deliveryExecution));
        } catch (Exception $e) {
            $this->returnFailure($e);
        }
    }

    /**
     * It requires only Delivery Execution in URI format.
     *
     * @author Gyula Szucs, <gyula@taotesting.com>
     */
    public function byDeliveryExecution()
    {
        try
        {
            $this->checkMethod();

            $this->validateParams(array(self::DELIVERY_EXECUTION));

            $deliveryExecution = $this->getQtiResultService()->getDeliveryExecutionById(
                $this->getRequestParameter(self::DELIVERY_EXECUTION)
            );

            $this->returnValidXml($this->getQtiResultService()->getDeliveryExecutionXml($deliveryExecution));
        } catch (Exception $e) {
            $this->returnFailure($e);
        }
    }

    /**
     * Checks the required request method.
     *
     * @author Gyula Szucs, <gyula@taotesting.com>
     * @throws common_exception_MethodNotAllowed
     */
    protected function checkMethod()
    {
        if ($this->getRequestMethod()!='GET') {
            throw new common_exception_MethodNotAllowed($this->getRequestURI());
        }
    }

    /**
     * Validates the given parameters.
     *
     * @author Gyula Szucs, <gyula@taotesting.com>
     * @param array $params
     * @throws common_exception_MissingParameter
     * @throws common_exception_ValidationFailed
     */
    protected function validateParams(array $params)
    {
        foreach ($params as $param) {
            if (!$this->hasRequestParameter($param)) {
                throw new common_exception_MissingParameter($param .' is missing from the request.', $this->getRequestURI());
            }

            if (empty($this->getRequestParameter($param))) {
                throw new common_exception_ValidationFailed($param, $param .' cannot be empty');
            }
        }
    }

    /**
     * Return XML response if it is valid.
     *
     * @param string $data
     * @throws Exception
     * @throws common_exception_NotFound
     */
    protected function returnValidXml($data)
    {
        if (empty($data)) {
            throw new common_exception_NotFound('Delivery execution not found.');
        }

        $doc = @simplexml_load_string($data);
        if (!$doc) {
            common_Logger::i('invalid xml result');
            throw new Exception('Xml output is malformed.');
        }

        // force XML content type header
        header('Content-Type: application/xml');

        echo $data;
        exit(0);
    }
}