<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170919071247 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE posts (id INT AUTO_INCREMENT NOT NULL, author_id INT DEFAULT NULL, category_id INT DEFAULT NULL, image VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, summary TEXT NOT NULL, content TEXT NOT NULL, creation_date DATETIME NOT NULL, rating INT NOT NULL, INDEX IDX_885DBAFAF675F31B (author_id), INDEX IDX_885DBAFA12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE similarPosts (post_source INT NOT NULL, post_target INT NOT NULL, INDEX IDX_B490B9656FA89B16 (post_source), INDEX IDX_B490B965764DCB99 (post_target), PRIMARY KEY(post_source, post_target)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE categories (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, name VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_3AF346685E237E06 (name), INDEX IDX_3AF34668727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE confirmation_tokens (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, hash VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_75A5965ED1B862B8 (hash), UNIQUE INDEX UNIQ_75A5965EA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE posts ADD CONSTRAINT FK_885DBAFAF675F31B FOREIGN KEY (author_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE posts ADD CONSTRAINT FK_885DBAFA12469DE2 FOREIGN KEY (category_id) REFERENCES categories (id)');
        $this->addSql('ALTER TABLE similarPosts ADD CONSTRAINT FK_B490B9656FA89B16 FOREIGN KEY (post_source) REFERENCES posts (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE similarPosts ADD CONSTRAINT FK_B490B965764DCB99 FOREIGN KEY (post_target) REFERENCES posts (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE categories ADD CONSTRAINT FK_3AF34668727ACA70 FOREIGN KEY (parent_id) REFERENCES categories (id)');
        $this->addSql('ALTER TABLE confirmation_tokens ADD CONSTRAINT FK_75A5965EA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('DROP TABLE confirm_tokens');
        $this->addSql('DROP INDEX UNIQ_7FB2AF175F37A13B ON reset_tokens');
        $this->addSql('ALTER TABLE reset_tokens CHANGE token hash VARCHAR(255) NOT NULL, CHANGE create_time creation_time DATETIME NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7FB2AF17D1B862B8 ON reset_tokens (hash)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE similarPosts DROP FOREIGN KEY FK_B490B9656FA89B16');
        $this->addSql('ALTER TABLE similarPosts DROP FOREIGN KEY FK_B490B965764DCB99');
        $this->addSql('ALTER TABLE posts DROP FOREIGN KEY FK_885DBAFA12469DE2');
        $this->addSql('ALTER TABLE categories DROP FOREIGN KEY FK_3AF34668727ACA70');
        $this->addSql('CREATE TABLE confirm_tokens (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, token VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, UNIQUE INDEX UNIQ_7CA67C35F37A13B (token), UNIQUE INDEX UNIQ_7CA67C3A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE confirm_tokens ADD CONSTRAINT FK_7CA67C3A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('DROP TABLE posts');
        $this->addSql('DROP TABLE similarPosts');
        $this->addSql('DROP TABLE categories');
        $this->addSql('DROP TABLE confirmation_tokens');
        $this->addSql('DROP INDEX UNIQ_7FB2AF17D1B862B8 ON reset_tokens');
        $this->addSql('ALTER TABLE reset_tokens CHANGE hash token VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE creation_time create_time DATETIME NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7FB2AF175F37A13B ON reset_tokens (token)');
    }
}
