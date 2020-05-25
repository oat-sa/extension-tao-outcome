<?php

declare(strict_types=1);

namespace oat\taoResultServer\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\scripts\tools\migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202005251237321179_taoResultServer extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'A test migration for extension taoResultServer (3).';
    }

    public function up(Schema $schema): void
    {
        $this->getLogger()->debug("taoResultServer Migration 3 UP.");
    }

    public function down(Schema $schema): void
    {
        $this->getLogger()->debug("taoResultServer Migration 3 DOWN.");
    }
}
