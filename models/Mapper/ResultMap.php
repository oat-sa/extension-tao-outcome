<?php


namespace oat\taoResultServer\models\Mapper;


class ResultMap
{
    protected $context;
    protected $testResult;
    protected $itemResult;

    /**
     * ResultMap constructor.
     * @param $context
     * @param $testResult
     * @param $itemResult
     */
    public function __construct($context, $testResult, $itemResult)
    {
        $this->context = $context;
        $this->testResult = $testResult;
        $this->itemResult = $itemResult;
    }

    /**
     * @return mixed
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return mixed
     */
    public function getTestResult()
    {
        return $this->testResult;
    }

    /**
     * @return mixed
     */
    public function getItemResult()
    {
        return $this->itemResult;
    }

}