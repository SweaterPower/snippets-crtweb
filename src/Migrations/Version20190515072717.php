<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Добавляет связь многие ко многим между пользователями и ролями, переименовывает запись о владельце сниппета
 */
final class Version20190515072717 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'add manytomany relation between user and user_role, rename snippet owner field';
    }

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user_user_role (user_id INT NOT NULL, user_role_id INT NOT NULL, INDEX IDX_2D084B47A76ED395 (user_id), INDEX IDX_2D084B478E0E3CA6 (user_role_id), PRIMARY KEY(user_id, user_role_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_user_role ADD CONSTRAINT FK_2D084B47A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_user_role ADD CONSTRAINT FK_2D084B478E0E3CA6 FOREIGN KEY (user_role_id) REFERENCES user_role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE snippet DROP FOREIGN KEY FK_961C8CD5A76ED395');
        $this->addSql('DROP INDEX IDX_961C8CD5A76ED395 ON snippet');
        $this->addSql('ALTER TABLE snippet CHANGE owner owner_id INT NOT NULL');
        $this->addSql('ALTER TABLE snippet ADD CONSTRAINT FK_961C8CD57E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_961C8CD57E3C61F9 ON snippet (owner_id)');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE user_user_role');
        $this->addSql('ALTER TABLE snippet DROP FOREIGN KEY FK_961C8CD57E3C61F9');
        $this->addSql('DROP INDEX IDX_961C8CD57E3C61F9 ON snippet');
        $this->addSql('ALTER TABLE snippet CHANGE owner_id owner INT NOT NULL');
        $this->addSql('ALTER TABLE snippet ADD CONSTRAINT FK_961C8CD5A76ED395 FOREIGN KEY (owner) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_961C8CD5A76ED395 ON snippet (owner)');
    }
}
