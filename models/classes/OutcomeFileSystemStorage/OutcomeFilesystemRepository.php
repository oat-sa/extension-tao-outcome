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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\taoResultServer\models\classes\OutcomeFileSystemStorage;


use common_exception_Error;
use common_exception_NotFound;
use Exception;
use oat\oatbox\filesystem\FileSystem;
use oat\oatbox\filesystem\FileSystemService;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\service\exception\InvalidServiceManagerException;
use oat\taoDelivery\model\execution\Delete\DeliveryExecutionDeleteRequest;
use oat\taoResultServer\models\classes\ResultStorageInterface;
use taoResultServer_models_classes_ResponseVariable;
use taoResultServer_models_classes_Variable;

class OutcomeFilesystemRepository extends ConfigurableService implements ResultStorageInterface
{
    const SERVICE_ID = 'taoResultServer/fileSystemRepository';

    const OPTION_STORAGE = 'storage';
    const OPTION_FILESYSTEM = 'filesystem';

    const BASE_TYPE_FILE_REFERENCE = 'fileReference';

    /**
     * @var ResultStorageInterface
     */
    private $storage;
    /**
     * @var FileSystem
     */
    private $filesystem;
    /**
     * @var FilePathFactory
     */
    private $filePathFactory;

    protected function getFilePathFactory()
    {
        if (null === $this->filePathFactory) {
            $this->filePathFactory = new FilePathFactory();
        }

        return $this->filePathFactory;
    }

    /**
     * @return ResultStorageInterface
     */
    protected function getDbStorage()
    {
        if (null === $this->storage) {
            $this->storage = $this->getServiceLocator()->get($this->getOption(self::OPTION_STORAGE));
        }

        return $this->storage;
    }

    /**
     * @return FileSystem
     *
     * @throws common_exception_Error
     * @throws common_exception_NotFound
     * @throws InvalidServiceManagerException
     */
    protected function getFileSystem()
    {
        if (null === $this->filesystem) {
            /** @var FileSystemService $fileSystemService */
            $fileSystemService = $this->getServiceManager()->get(FileSystemService::SERVICE_ID);
            $this->filesystem = $fileSystemService->getFileSystem($this->getOption(self::OPTION_FILESYSTEM));
        }

        return $this->filesystem;
    }

    /**
     * Get only one property from a variable
     *
     * @param string $variableId   on which we want the property
     * @param string $propertyName to retrieve
     *
     * @return int|string the property retrieved
     *
     * @throws InvalidServiceManagerException
     * @throws common_exception_Error
     * @throws common_exception_NotFound
     */
    public function getVariableProperty($variableId, $propertyName)
    {
        $propertyValue = $this->getDbStorage()->getVariableProperty($variableId, $propertyName);

        if ($propertyName === 'baseType' && $propertyValue === self::BASE_TYPE_FILE_REFERENCE) {
            return 'file';
        }

        if ($propertyName === 'candidateResponse') {
            $baseType = $this->getDbStorage()->getVariableProperty($variableId, 'baseType');

            if ($baseType === self::BASE_TYPE_FILE_REFERENCE) {
                return $this->getFileSystem()->read($propertyValue);
            }
        }

        return $propertyValue;
    }

    /**
     * Remove the result and all the related variables
     *
     * @param string $deliveryResultIdentifier The identifier of the delivery execution
     *
     * @return boolean if the deletion was successful or not
     *
     * @throws InvalidServiceManagerException
     * @throws common_exception_Error
     * @throws common_exception_NotFound
     */
    public function deleteResult($deliveryResultIdentifier)
    {
        $dbStorage = $this->getDbStorage();

        $rawVariables = $dbStorage->getDeliveryVariables($deliveryResultIdentifier);

        foreach ($rawVariables as $rawVariable) {
            /** @var taoResultServer_models_classes_Variable $variable */
            $variable = $rawVariable[0]->variable;
            if ($this->isFileReference($variable)) {
                $this->getFileSystem()->deleteDir(
                    $this->getFilePathFactory()->getDirPath($deliveryResultIdentifier)
                );
            }
        }

        return $dbStorage->deleteResult($deliveryResultIdentifier);
    }

    /**
     * Get the complete variables list stored for delivery execution
     *
     * @param array|string $deliveryResultIdentifier
     *
     * @return mixed
     */
    public function getDeliveryVariables($deliveryResultIdentifier)
    {
        return $this->getDbStorage()->getDeliveryVariables($deliveryResultIdentifier);
    }

    /**
     * get the complete variables list stored for a call id (item or test)
     *
     * @param string|array $callId an execution identifier
     *
     * @return array that contains the variables related to the call id
     * Array
     *(
     *   [uri] => Array
     *   (
     *       [0] => stdClass Object
     *       (
     *           [uri] => uri
     *           [class] => taoResultServer_models_classes_ResponseVariable
     *           [deliveryResultIdentifier] => http://tao.localdomain:8888/tao.rdf#i14176019092304877
     *           [callIdItem] => http://tao.localdomain:8888/tao.rdf#i14176019092304877.item-1.0
     *           [callIdTest] =>
     *           [test] => http://tao.localdomain:8888/tao.rdf#i14175986702737865-
     *           [item] => http://tao.localdomain:8888/tao.rdf#i141631732273405
     *           [variable] => taoResultServer_models_classes_ResponseVariable Object
     *           (
     *               [correctResponse] =>
     *               [candidateResponse] => MQ==
     *               [identifier] => numAttempts
     *               [cardinality] => single
     *               [baseType] => integer
     *               [epoch] => 0.28031200 1417601924
     *           )
     *
     *       )
     *
     *   )
     *
     *   [uri2] => Array
     *   (
     *       [0] => stdClass Object
     *       (
     *           [uri] => uri2
     *           [class] => taoResultServer_models_classes_OutcomeVariable
     *           [deliveryResultIdentifier] => http://tao.localdomain:8888/tao.rdf#i14176019092304877
     *           [callIdItem] => http://tao.localdomain:8888/tao.rdf#i14176019092304877.item-1.0
     *           [callIdTest] =>
     *           [test] => http://tao.localdomain:8888/tao.rdf#i14175986702737865-
     *           [item] => http://tao.localdomain:8888/tao.rdf#i141631732273405
     *           [variable] => taoResultServer_models_classes_OutcomeVariable Object
     *           (
     *               [normalMaximum] =>
     *               [normalMinimum] =>
     *               [value] => Y29tcGxldGVk
     *               [identifier] => completionStatus
     *               [cardinality] => single
     *               [baseType] => identifier
     *               [epoch] => 0.28939600 1417601924
     *           )
     *
     *       )
     *
     *   )
     *
     *)
     * @throws InvalidServiceManagerException
     * @throws common_exception_Error
     * @throws common_exception_NotFound
     */
    public function getVariables($callId)
    {
        $rawVariables = $this->getDbStorage()->getVariables($callId);

        /** @var  $rawVariable */
        foreach ($rawVariables as &$rawVariable) {
            if ($this->canFileBeRestored($rawVariable[0]->variable)) {
                $rawVariable[0]->variable = $this->restoreFile($rawVariable[0]->variable);
            }
        }

        return $rawVariables;
    }

    /**
     * @param taoResultServer_models_classes_ResponseVariable $rawVariable
     *
     * @return taoResultServer_models_classes_ResponseVariable
     *
     * @throws InvalidServiceManagerException
     * @throws common_exception_Error
     * @throws common_exception_NotFound
     */
    private function restoreFile(taoResultServer_models_classes_ResponseVariable $rawVariable)
    {
        $variable = clone $rawVariable;

        $path = $variable->getCandidateResponse();
        $fileContent = $this->getFileSystem()->read($path);

        $variable->setCandidateResponse($fileContent);
        $variable->setBaseType('file');

        return $variable;
    }

    /**
     * Get The variable that match params
     *
     * @param string $callId             an execution identifier
     * @param string $variableIdentifier the identifier of the variable
     *
     * @return array variable that match call id and variable identifier
     * Array
     *(
     *   [uri] => Array
     *   (
     *       [0] => stdClass Object
     *       (
     *           [uri] => uri
     *           [class] => taoResultServer_models_classes_OutcomeVariable
     *           [deliveryResultIdentifier] => MyDeliveryResultIdentifier#1
     *           [callIdItem] => MyCallId#2
     *           [callIdTest] =>
     *           [test] => MyGreatTest#2
     *           [item] => MyGreatItem#2
     *           [variable] => taoResultServer_models_classes_OutcomeVariable Object
     *           (
     *               [normalMaximum] =>
     *               [normalMinimum] =>
     *               [value] => TXlWYWx1ZQ==
     *               [identifier] => Identifier
     *               [cardinality] => multiple
     *               [baseType] => float
     *               [epoch] => 0.58277800 1417621663
     *           )
     *
     *       )
     *
     *   )
     *
     *)
     * @throws InvalidServiceManagerException
     * @throws common_exception_Error
     * @throws common_exception_NotFound
     */
    public function getVariable($callId, $variableIdentifier)
    {
        $rawVariable = $this->getDbStorage()->getVariable($callId, $variableIdentifier);

        if ($this->canFileBeRestored($rawVariable[0]->variable)) {
            $rawVariable[0]->variable = $this->restoreFile($rawVariable[0]->variable);
        }

        return $rawVariable;
    }

    /**
     * Get the test taker id related to one specific delivery execution
     *
     * @param string $deliveryResultIdentifier the identifier of the delivery execution
     *
     * @return string the uri of the test taker related to the delivery execution
     */
    public function getTestTaker($deliveryResultIdentifier)
    {
        return $this->getDbStorage()->getTestTaker($deliveryResultIdentifier);
    }

    /**
     * Get the delivery id related to one specific delivery execution
     *
     * @param string $deliveryResultIdentifier the identifier of the delivery execution
     *
     * @return string the uri of the delivery related to the delivery execution
     */
    public function getDelivery($deliveryResultIdentifier)
    {
        return $this->getDbStorage()->getDelivery($deliveryResultIdentifier);
    }

    /**
     * Get the entire list of call ids that are stored (item or test)
     * @return array the list of executions ids (across all results)
     */
    public function getAllCallIds()
    {
        return $this->getDbStorage()->getAllCallIds();
    }

    /**
     * get all the ids of test taker that have attempt a test
     * @return array of all test taker ids array(array('deliveryResultIdentifier' => 123, 'testTakerIdentifier' => 456))
     */
    public function getAllTestTakerIds()
    {
        return $this->getDbStorage()->getAllTestTakerIds();
    }

    /**
     * Get all the ids of delivery that are stored.
     *
     * @return array of all delivery ids array(array('deliveryResultIdentifier' => 123, 'deliveryIdentifier' => 456))
     */
    public function getAllDeliveryIds()
    {
        return $this->getDbStorage()->getAllDeliveryIds();
    }

    /**
     * Spawn Result
     *
     * Initialize a new raw Delivery Result.
     *
     * After initialization, the Delivery Result will be empty, and will not be linked
     * to a Test Taker or a Delivery.
     *
     * Please note that it is the responisibility of the implementer to generate Delivery
     * Result identifiers that are as unique as possible.
     *
     * @return string The unique identifier of the initialized Delivery Result.
     */
    public function spawnResult()
    {
        return $this->getDbStorage()->spawnResult();
    }

    /**
     * Store Related Test Taker
     *
     * Attach a given Test Taker to a Delivery Result.
     *
     * A Delivery Result is always attached to a single Test Taker. This method enables
     * the client code to register a given Test Taker, using its $testTakerIdentifier, to a
     * given Delivery Result, using its $deliveryResultIdentifier.
     *
     * @param string $deliveryResultIdentifier The identifier of the Delivery Result (usually a Delivery Execution URI).
     * @param string $testTakerIdentifier      The identifier of the Test Taker (usually a URI).
     *
     */
    public function storeRelatedTestTaker($deliveryResultIdentifier, $testTakerIdentifier)
    {
        return $this->getDbStorage()->storeRelatedTestTaker($deliveryResultIdentifier, $testTakerIdentifier);
    }

    /**
     * Store Related Delivery
     *
     * Store a delivery related to a specific delivery execution
     *
     * @param string $deliveryResultIdentifier (mostly delivery execution uri)
     * @param string $deliveryIdentifier       (uri recommended)
     */
    public function storeRelatedDelivery($deliveryResultIdentifier, $deliveryIdentifier)
    {
        return $this->getDbStorage()->storeRelatedDelivery($deliveryResultIdentifier, $deliveryIdentifier);
    }

    /**
     * @param string                                  $deliveryResultIdentifier
     * @param taoResultServer_models_classes_Variable $itemVariable
     *
     * @return taoResultServer_models_classes_Variable
     * @throws InvalidServiceManagerException
     * @throws common_exception_Error
     * @throws common_exception_NotFound
     */
    private function handleFiles($deliveryResultIdentifier, taoResultServer_models_classes_Variable $itemVariable)
    {
        if ($this->canFileBeExtracted($itemVariable)) {
            $variable = clone $itemVariable;

            $path = $this->getFilePathFactory()->getFilePath($deliveryResultIdentifier);

            $fileContent = $this->getValueAndReplaceWithPath($variable, $path);

            $this->getFileSystem()->write($path, $fileContent);

            $variable->setBaseType(self::BASE_TYPE_FILE_REFERENCE);

            return $variable;
        }

        return $itemVariable;
    }

    /**
     * @param string $deliveryResultIdentifier
     * @param        $test
     * @param        $item
     * @param array  $itemVariables
     * @param        $callIdItem
     *
     * @throws InvalidServiceManagerException
     * @throws common_exception_Error
     * @throws common_exception_NotFound
     */
    public function storeItemVariables($deliveryResultIdentifier, $test, $item, array $itemVariables, $callIdItem)
    {
        $variables = [];

        foreach ($itemVariables as $itemVariable) {
            $variables[] = $this->handleFiles($deliveryResultIdentifier, $itemVariable);
        }

        $this->getDbStorage()->storeItemVariables(
            $deliveryResultIdentifier,
            $test,
            $item,
            $variables,
            $callIdItem
        );
    }

    /**
     * Store Test Variable
     *
     * Submit a specific test Variable and store it
     *
     * @param string                                  $deliveryResultIdentifier
     * @param string                                  $test
     * @param taoResultServer_models_classes_Variable $testVariable
     * @param                                         $callIdTest
     */
    public function storeTestVariable(
        $deliveryResultIdentifier,
        $test,
        taoResultServer_models_classes_Variable $testVariable,
        $callIdTest
    ) {
        $this->getDbStorage()->storeTestVariable(
            $deliveryResultIdentifier,
            $test,
            $testVariable,
            $callIdTest
        );
    }

    /**
     * Store Item Variable
     *
     * Submit a specific Item Variable, (ResponseVariable and OutcomeVariable shall be used respectively for collected
     * data and score/interpretation computation) and store it with all the dependencies
     *
     * @param string                                  $deliveryResultIdentifier
     * @param string                                  $test         (uri recommended)
     * @param string                                  $item         (uri recommended)
     * @param taoResultServer_models_classes_Variable $itemVariable the variable to store
     * @param string                                  $callIdItem   contextual call id for the variable, ex. :  to
     *                                                              distinguish the same variable output by the same
     *                                                              item and that is presented several times in the
     *                                                              same test
     *
     * @throws InvalidServiceManagerException
     * @throws common_exception_Error
     * @throws common_exception_NotFound
     */
    public function storeItemVariable(
        $deliveryResultIdentifier,
        $test,
        $item,
        taoResultServer_models_classes_Variable $itemVariable,
        $callIdItem
    ) {
        $variable = $this->handleFiles($deliveryResultIdentifier, $itemVariable);

        $this->getDbStorage()->storeItemVariable(
            $deliveryResultIdentifier,
            $test,
            $item,
            $variable,
            $callIdItem
        );
    }

    private function getValueAndReplaceWithPath(taoResultServer_models_classes_ResponseVariable $variable, $path)
    {
        $variableValue = $variable->getCandidateResponse();
        $variable->setCandidateResponse($path);

        return $variableValue;
    }

    public function storeTestVariables($deliveryResultIdentifier, $test, array $testVariables, $callIdTest)
    {
        return $this->getDbStorage()->storeTestVariables($deliveryResultIdentifier, $test, $testVariables, $callIdTest);
    }

    /**
     * The storage may configure itself based on the resultServer definition
     *
     * @param array $callOptions
     */
    public function configure($callOptions = [])
    {
        return $this->getDbStorage()->configure($callOptions);
    }

    /**
     * @param DeliveryExecutionDeleteRequest $request
     *
     * @throws Exception
     * @return bool
     */
    public function deleteDeliveryExecutionData(DeliveryExecutionDeleteRequest $request)
    {
        return $this->getDbStorage()->deleteDeliveryExecutionData($request);
    }


    /**
     * Get the result information (test taker, delivery, delivery execution) from filters
     *
     * @param array $delivery list of delivery to search : array('test','myValue')
     * @param array $options  params to restrict results array(
     *                        "order"=> "deliveryResultIdentifier" || "testTakerIdentifier" || "deliveryIdentifier",
     *                        "orderdir"=>"ASC" || "DESC",
     *                        "offset"=> an int,
     *                        "limit"=> an int
     *                        )
     *
     * @return array of results that match the filter : array(array('deliveryResultIdentifier' => '123',
     *               'testTakerIdentifier' => '456', 'deliveryIdentifier' => '789'))
     */
    public function getResultByDelivery($delivery, $options = [])
    {
        return $this->getDbStorage()->getResultByDelivery($delivery, $options);
    }

    /**
     * Get all the ids of the callItem for a specific delivery execution
     *
     * @param string $deliveryResultIdentifier The identifier of the delivery execution
     *
     * @return array the list of call item ids (across all results)
     */
    public function getRelatedItemCallIds($deliveryResultIdentifier)
    {
        return $this->getDbStorage()->getRelatedItemCallIds($deliveryResultIdentifier);
    }

    /**
     * Get all the ids of the callTest for a specific delivery execution
     *
     * @param string $deliveryResultIdentifier The identifier of the delivery execution
     *
     * @return array the list of call test ids (across all results)
     */
    public function getRelatedTestCallIds($deliveryResultIdentifier)
    {
        return $this->getDbStorage()->getRelatedTestCallIds($deliveryResultIdentifier);
    }

    /**
     * Count the number of result that match the filter
     *
     * @param array $delivery list of delivery to search : array('test','myValue')
     *
     * @return int the number of results that match filter
     */
    public function countResultByDelivery($delivery)
    {
        return $this->getDbStorage()->countResultByDelivery($delivery);
    }

    private function isFileReference(taoResultServer_models_classes_Variable $variable)
    {
        return $variable->getBaseType() === self::BASE_TYPE_FILE_REFERENCE;
    }

    private function canFileBeRestored(taoResultServer_models_classes_Variable $variable)
    {
        return $variable instanceof taoResultServer_models_classes_ResponseVariable
            && $this->isFileReference($variable);
    }

    private function canFileBeExtracted(taoResultServer_models_classes_Variable $variable)
    {
        return $variable->getBaseType() === 'file'
            && $variable instanceof taoResultServer_models_classes_ResponseVariable;

    }
}
