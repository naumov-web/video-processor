<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260426213222 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            create table outbox_events (
                id bigserial primary key,
                event_type varchar(255) not null,
                aggregate_id bigint not null,
                payload jsonb not null,
                status varchar(20) not null default 'pending',
                retries_count int not null default 0,
                last_error text null,
                created_at timestamp not null default now(),
                processed_at timestamp null
            );
        ");
        $this->addSql("
            create index idx_outbox_events_status_created
                on outbox_events (status, created_at);
        ");
        $this->addSql("
            create index idx_outbox_events_aggregate_id
                on outbox_events (aggregate_id);
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("drop table outbox_events;");
    }
}
