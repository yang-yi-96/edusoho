<?php

namespace Biz\Visualization\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

abstract class BaseRefreshJob extends AbstractJob
{
    protected $logger;

    protected function getLogger()
    {
        if (!$this->logger instanceof Logger) {
            $biz = $this->biz;
            $logger = new Logger('RefreshLearnData');
            $logger->pushHandler(new StreamHandler($biz['log_directory'].'/refresh-data.log', Logger::DEBUG));
            $this->logger = $logger;
        }

        return $this->logger;
    }
}
