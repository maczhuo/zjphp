<?php
namespace ZJPHP\Facade;

use ZJPHP\Base\Facade;

class MQScheduledConsumer extends Facade
{
    /**
     * @inheritDoc
     */
    public static function getFacadeComponentId()
    {
        return 'mqScheduledConsumer';
    }
}
