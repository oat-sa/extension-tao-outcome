<?php

declare(strict_types=1);

namespace oat\taoResultServer\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\oatbox\log\LoggerService;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\oatbox\service\ServiceManagerAwareInterface;
use oat\oatbox\service\ServiceManagerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202005191825391179_taoResultServer extends AbstractMigration implements ServiceManagerAwareInterface
{
    use ServiceManagerAwareTrait;

    public function getDescription(): string
    {
        return 'A test migration for extension taoResultServer (1).';
    }

    public function up(Schema $schema): void
    {
        /** @var LoggerService $logger */
        $logger = $this->getServiceLocator()->get(LoggerService::LOGGER_OPTION);
        $logger->debug("taoResultServer Migration 1 UP.");
    }

    public function down(Schema $schema): void
    {
        /** @var LoggerService $logger */
        $logger = $this->getServiceLocator()->get(LoggerService::LOGGER_OPTION);
        $logger->debug("taoResultServer Migration 1 DOWN.");
    }
}
