<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221023222227 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE github_user ADD github_user_id INT NOT NULL');
        $this->addSql('ALTER TABLE github_user ADD added_by_user_id INT NOT NULL');
        $this->addSql('ALTER TABLE github_user ADD repo_last_updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7C2334A8A8674B11 ON github_user (github_user_id)');
        $this->addSql('CREATE INDEX idx_github_user_github_user_id ON github_user (github_user_id)');
        $this->addSql('ALTER TABLE github_user_repo ALTER repo_updated_at DROP NOT NULL');
        $this->addSql('CREATE INDEX idx_github_user_repo_github_user_id ON github_user_repo (github_user_id)');
        $this->addSql('CREATE INDEX idx_github_user_repo_github_repo_id ON github_user_repo (github_repo_id)');
        $this->addSql('CREATE INDEX idx_repo_updated_at ON github_user_repo (repo_updated_at)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX UNIQ_7C2334A8A8674B11');
        $this->addSql('DROP INDEX idx_github_user_github_user_id');
        $this->addSql('ALTER TABLE "github_user" DROP github_user_id');
        $this->addSql('ALTER TABLE "github_user" DROP added_by_user_id');
        $this->addSql('ALTER TABLE "github_user" DROP repo_last_updated_at');
        $this->addSql('DROP INDEX idx_github_user_repo_github_user_id');
        $this->addSql('DROP INDEX idx_github_user_repo_github_repo_id');
        $this->addSql('DROP INDEX idx_repo_updated_at');
        $this->addSql('ALTER TABLE "github_user_repo" ALTER repo_updated_at SET NOT NULL');
    }
}
