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
use oat\oatbox\service\ServiceManager;

class taoResultServer_actions_QtiRestResults extends \tao_actions_CommonRestModule
{
    const TESTTAKER = 'testtaker';
    const DELIVERY = 'delivery';
    const DELIVERY_EXECUTION = 'deliveryExecution';

    protected $deliveryExecution;

    /**
     * taoResultServer_actions_QtiRestResults constructor.
     * Pass model service to handle http call business
     */
    public function __construct()
    {
        parent::__construct();
        $this->service = QtiResultsService::singleton();
        $this->service->setServiceLocator(ServiceManager::getServiceManager());
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
            $data = $this->service->getDeliveryExecution($deliveryExecution);
            if (empty($data)) {
                common_Logger::e('Empty delivery execution');
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
            $deliveryExecution = $this->service->getDeliveryExecutionByTestTakerAndDelivery(
                $this->validFromUri($this->getRequestParameter(self::DELIVERY), __FUNCTION__),
                $this->validFromUri($this->getRequestParameter(self::TESTTAKER), __FUNCTION__)
            );
        } elseif ($this->hasRequestParameter(self::DELIVERY_EXECUTION)) {
            $deliveryExecution = $this->service->getDeliveryExecutionByResource(
                $this->validFromUri($this->getRequestParameter(self::DELIVERY_EXECUTION), __FUNCTION__)
            );
        } else {
            throw new common_exception_MissingParameter(self::TESTTAKER . ' coupled with ' . self::DELIVERY .
                ', or ' . self::DELIVERY_EXECUTION, $this->getRequestURI());
        }

        return $deliveryExecution;
    }

    /**
     * Check if $uri is a valid uri & create resource from $uri
     *
     * @param $uri
     * @param string $function
     * @return core_kernel_classes_Resource
     * @throws common_exception_InvalidArgumentType
     */
    protected function validFromUri($uri, $function = __FUNCTION__)
    {
        if (!\common_Utils::isUri($uri)) {
            throw new \common_exception_InvalidArgumentType('QtiRestResults', $function, '', 'uri', $uri);
        }
        return new core_kernel_classes_Resource($uri);
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