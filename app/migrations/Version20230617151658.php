<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230617151658 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE avatar (id INT AUTO_INCREMENT NOT NULL, image VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE conversation (id INT AUTO_INCREMENT NOT NULL, fk_listing_id INT NOT NULL, is_active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_8A8E26E9240F5789 (fk_listing_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE listing (id INT AUTO_INCREMENT NOT NULL, fk_listing_status_id INT NOT NULL, fk_listing_type_id INT NOT NULL, fk_user_id INT NOT NULL, sub_category_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, postcode VARCHAR(11) NOT NULL, city VARCHAR(50) NOT NULL, country VARCHAR(50) NOT NULL, latitude VARCHAR(50) NOT NULL, longitude VARCHAR(50) NOT NULL, INDEX IDX_CB0048D415F66A05 (fk_listing_status_id), INDEX IDX_CB0048D43D1A3E9 (fk_listing_type_id), INDEX IDX_CB0048D45741EEB9 (fk_user_id), INDEX IDX_CB0048D4F7BFE87C (sub_category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE listing_category (id INT AUTO_INCREMENT NOT NULL, category VARCHAR(50) NOT NULL, category_image VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE listing_image (id INT AUTO_INCREMENT NOT NULL, fk_listing_id INT NOT NULL, image VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_33D3DCD3240F5789 (fk_listing_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE listing_status (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE listing_type (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, fk_user_sender_id INT NOT NULL, fk_user_recipient_id INT NOT NULL, fk_conversation_id INT NOT NULL, content VARCHAR(800) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_B6BD307F83CAD790 (fk_user_sender_id), INDEX IDX_B6BD307FA7D47D82 (fk_user_recipient_id), INDEX IDX_B6BD307FD00F57A3 (fk_conversation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sub_category (id INT AUTO_INCREMENT NOT NULL, fk_listing_category_id INT NOT NULL, subcategory VARCHAR(50) NOT NULL, INDEX IDX_BCE3F798933D4F15 (fk_listing_category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, fk_user_rank_id INT NOT NULL, fk_avatar_id INT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_verified TINYINT(1) NOT NULL, pseudo VARCHAR(25) NOT NULL, street_number VARCHAR(11) DEFAULT NULL, street_name VARCHAR(255) DEFAULT NULL, postcode VARCHAR(11) DEFAULT NULL, city VARCHAR(50) DEFAULT NULL, country VARCHAR(50) DEFAULT NULL, is_active TINYINT(1) NOT NULL, gps_coordinates VARCHAR(50) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), INDEX IDX_8D93D649FC67B9C (fk_user_rank_id), INDEX IDX_8D93D649E761EC80 (fk_avatar_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_balance (id INT AUTO_INCREMENT NOT NULL, fk_user_id INT NOT NULL, balance INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_F4F901F45741EEB9 (fk_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_favorite_listing (id INT AUTO_INCREMENT NOT NULL, fk_user_id INT NOT NULL, fk_listing_id INT NOT NULL, INDEX IDX_EB41A4675741EEB9 (fk_user_id), INDEX IDX_EB41A467240F5789 (fk_listing_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_rank (id INT AUTO_INCREMENT NOT NULL, level VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E9240F5789 FOREIGN KEY (fk_listing_id) REFERENCES listing (id)');
        $this->addSql('ALTER TABLE listing ADD CONSTRAINT FK_CB0048D415F66A05 FOREIGN KEY (fk_listing_status_id) REFERENCES listing_status (id)');
        $this->addSql('ALTER TABLE listing ADD CONSTRAINT FK_CB0048D43D1A3E9 FOREIGN KEY (fk_listing_type_id) REFERENCES listing_type (id)');
        $this->addSql('ALTER TABLE listing ADD CONSTRAINT FK_CB0048D45741EEB9 FOREIGN KEY (fk_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE listing ADD CONSTRAINT FK_CB0048D4F7BFE87C FOREIGN KEY (sub_category_id) REFERENCES sub_category (id)');
        $this->addSql('ALTER TABLE listing_image ADD CONSTRAINT FK_33D3DCD3240F5789 FOREIGN KEY (fk_listing_id) REFERENCES listing (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F83CAD790 FOREIGN KEY (fk_user_sender_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FA7D47D82 FOREIGN KEY (fk_user_recipient_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FD00F57A3 FOREIGN KEY (fk_conversation_id) REFERENCES conversation (id)');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE sub_category ADD CONSTRAINT FK_BCE3F798933D4F15 FOREIGN KEY (fk_listing_category_id) REFERENCES listing_category (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649FC67B9C FOREIGN KEY (fk_user_rank_id) REFERENCES user_rank (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649E761EC80E761EC80 FOREIGN KEY (fk_avatar_id) REFERENCES avatar (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_balance ADD CONSTRAINT FK_F4F901F45741EEB9 FOREIGN KEY (fk_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_favorite_listing ADD CONSTRAINT FK_EB41A4675741EEB9 FOREIGN KEY (fk_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_favorite_listing ADD CONSTRAINT FK_EB41A467240F5789 FOREIGN KEY (fk_listing_id) REFERENCES listing (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E9240F5789');
        $this->addSql('ALTER TABLE listing DROP FOREIGN KEY FK_CB0048D415F66A05');
        $this->addSql('ALTER TABLE listing DROP FOREIGN KEY FK_CB0048D43D1A3E9');
        $this->addSql('ALTER TABLE listing DROP FOREIGN KEY FK_CB0048D45741EEB9');
        $this->addSql('ALTER TABLE listing DROP FOREIGN KEY FK_CB0048D4F7BFE87C');
        $this->addSql('ALTER TABLE listing_image DROP FOREIGN KEY FK_33D3DCD3240F5789');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F83CAD790');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FA7D47D82');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FD00F57A3');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('ALTER TABLE sub_category DROP FOREIGN KEY FK_BCE3F798933D4F15');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649FC67B9C');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649E761EC80E761EC80');
        $this->addSql('ALTER TABLE user_balance DROP FOREIGN KEY FK_F4F901F45741EEB9');
        $this->addSql('ALTER TABLE user_favorite_listing DROP FOREIGN KEY FK_EB41A4675741EEB9');
        $this->addSql('ALTER TABLE user_favorite_listing DROP FOREIGN KEY FK_EB41A467240F5789');
        $this->addSql('DROP TABLE avatar');
        $this->addSql('DROP TABLE conversation');
        $this->addSql('DROP TABLE listing');
        $this->addSql('DROP TABLE listing_category');
        $this->addSql('DROP TABLE listing_image');
        $this->addSql('DROP TABLE listing_status');
        $this->addSql('DROP TABLE listing_type');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE sub_category');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_balance');
        $this->addSql('DROP TABLE user_favorite_listing');
        $this->addSql('DROP TABLE user_rank');
    }
}
