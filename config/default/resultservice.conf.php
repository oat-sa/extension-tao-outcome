<?php
/**
 * Default config header
 *
 * To replace this add a file /home/bout/code/php/taoTrunk/taoResultServer/config/header/default_resultserver.conf.php
 */
use oat\taoResultServer\models\classes\implementation\ResultServerService;

return new ResultServerService([
    ResultServerService::OPTION_RESULT_STORAGE => 'taoOutcomeRds/RdsResultStorage'
]);
