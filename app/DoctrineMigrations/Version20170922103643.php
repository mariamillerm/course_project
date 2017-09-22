<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170922103643 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE categories RENAME INDEX uniq_3af346685e237e06 TO search_idx');
        $this->addSql('ALTER TABLE posts RENAME INDEX uniq_885dbafa2b36786b TO search_idx');
        $this->addSql('CREATE UNIQUE INDEX search_idx ON users (username, email)');
        $this->addSql('ALTER TABLE confirmation_tokens RENAME INDEX uniq_75a5965ed1b862b8 TO search_idx');
        $this->addSql('ALTER TABLE reset_tokens RENAME INDEX uniq_7fb2af17d1b862b8 TO search_idx');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE categories RENAME INDEX search_idx TO UNIQ_3AF346685E237E06');
        $this->addSql('ALTER TABLE confirmation_tokens RENAME INDEX search_idx TO UNIQ_75A5965ED1B862B8');
        $this->addSql('ALTER TABLE posts RENAME INDEX search_idx TO UNIQ_885DBAFA2B36786B');
        $this->addSql('ALTER TABLE reset_tokens RENAME INDEX search_idx TO UNIQ_7FB2AF17D1B862B8');
        $this->addSql('DROP INDEX search_idx ON users');
    }
}
