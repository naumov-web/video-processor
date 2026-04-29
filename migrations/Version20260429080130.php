<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260429080130 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            alter table tasks add last_heartbeat_at timestamp(0) default null;
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("alter table tasks drop last_heartbeat_at;");
    }
}
