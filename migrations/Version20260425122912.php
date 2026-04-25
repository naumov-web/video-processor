<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260425122912 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            create table tasks (
                id bigserial primary key,
                video_id bigint not null,
                type varchar(50) not null,
                status varchar(20) not null,
                priority int not null default 0,
                attempts_count int not null default 0,
                max_attempts int not null default 3,
                input_data jsonb not null,
                output_data jsonb null,
                last_error jsonb null,
                next_retry_at timestamp null,
                processing_key varchar(255) null,
                source varchar(50) not null,
                started_at timestamp null,
                finished_at timestamp null,
                created_at timestamp not null default now(),
                updated_at timestamp not null default now(),
                version int not null default 1
            );
        ");
        $this->addSql("
            create index idx_tasks_status_priority_created
                on tasks (status, priority desc, created_at asc);
        ");
        $this->addSql("
            create index idx_tasks_next_retry
                on tasks (next_retry_at)
                where status = 'pending';
        ");
        $this->addSql("
            create index idx_tasks_video_id
                on tasks (video_id);
        ");
        $this->addSql("
            create unique index uniq_tasks_processing_key
                on tasks (processing_key)
                where processing_key is not null;
        ");
        $this->addSql("
            create unique index uniq_active_task
                on tasks (video_id, type)
                where status in ('pending', 'running');
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('drop table tasks;');
    }
}
