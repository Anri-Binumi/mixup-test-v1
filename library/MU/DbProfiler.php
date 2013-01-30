<?php

/** Zend_Db_Profiler */
require_once 'Zend/Db/Profiler.php';

class MU_DbProfiler extends Zend_Db_Profiler
{

    /**
     * The original label for this profiler.
     * @var string
     */
    protected $_label = null;

    /**
     * The total time taken for all profiled queries.
     * @var float
     */
    protected $_totalElapsedTime = 0;

    /**
     * Constructor
     *
     * @param string $label OPTIONAL Label for the profiling info.
     * @return void
     */
    public function __construct($enabled = true, $label = null)
    {
        $this->_label = $label;
        if (!$this->_label)
        {
            $this->_label = 'DB';
        }

        $this->setEnabled($enabled);
    }

    /**
     * Intercept the query end and log the profiling data.
     *
     * @param  integer $queryId
     * @throws Zend_Db_Profiler_Exception
     * @return void
     */
    public function queryEnd($queryId)
    {
        $state = parent::queryEnd($queryId);

        if (!$this->getEnabled() || $state == self::IGNORED)
            return;

        $profile = $this->getQueryProfile($queryId);
        $this->_totalElapsedTime += $profile->getElapsedSecs();

        $queryTime = (string) round($profile->getElapsedSecs(), 5);
        $queryParams = count($profile->getQueryParams()) ? print_r($profile->getQueryParams(), true) : '';

        U::debug(sprintf('Query %s [%s] %s -- %s', $queryId, $queryTime, $profile->getQuery(), $queryParams));
    }

}
