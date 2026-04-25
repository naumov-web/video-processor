<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260425203703 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            create table users (
                id bigserial primary key,
                email varchar(180) not null,
                roles jsonb not null,
                password varchar(255) not null,
                created_at timestamp(0) with time zone not null,
                constraint uniq_user_email unique (email)
            )
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("
            drop table users;
        ");
    }
}
