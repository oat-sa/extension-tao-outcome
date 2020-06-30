<?php

declare(strict_types=1);

namespace oat\taoResultServer\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\taoResultServer\helpers\BinaryContentRenderer;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202006301337151179_taoResultServer extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'New service for rendering binary content of file as string';
    }

    public function up(Schema $schema): void
    {
        $this->getServiceLocator()->register(BinaryContentRenderer::SERVICE_ID, new BinaryContentRenderer());
    }

    public function down(Schema $schema): void
    {
        $this->getServiceLocator()->unregister(BinaryContentRenderer::SERVICE_ID);
    }
}
