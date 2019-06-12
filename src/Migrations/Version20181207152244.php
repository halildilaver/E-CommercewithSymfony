<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181207152244 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE kopyala_category');
        $this->addSql('DROP TABLE kopyala_product');
        $this->addSql('DROP TABLE kopyala_users');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE kopyala_category (id INT AUTO_INCREMENT NOT NULL, parentid INT NOT NULL, title VARCHAR(30) NOT NULL COLLATE utf8mb4_unicode_ci, keywords VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, description VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, status VARCHAR(10) NOT NULL COLLATE utf8mb4_unicode_ci, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE kopyala_product (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(150) DEFAULT NULL COLLATE utf8mb4_unicode_ci, keywords VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, description VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, type VARCHAR(20) DEFAULT NULL COLLATE utf8mb4_unicode_ci, publisher_id INT DEFAULT NULL, year INT DEFAULT NULL, amount INT DEFAULT NULL, pprice DOUBLE PRECISION DEFAULT NULL, sprice DOUBLE PRECISION NOT NULL, min INT DEFAULT NULL, detail LONGTEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci, image VARCHAR(150) DEFAULT NULL COLLATE utf8mb4_unicode_ci, writer_id INT DEFAULT NULL, category_id INT DEFAULT NULL, user_id INT DEFAULT NULL, status VARCHAR(10) DEFAULT NULL COLLATE utf8mb4_unicode_ci, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE kopyala_users (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(20) NOT NULL COLLATE utf8mb4_unicode_ci, surname VARCHAR(30) NOT NULL COLLATE utf8mb4_unicode_ci, email VARCHAR(50) NOT NULL COLLATE utf8mb4_unicode_ci, password VARCHAR(100) NOT NULL COLLATE utf8mb4_unicode_ci, type VARCHAR(10) NOT NULL COLLATE utf8mb4_unicode_ci, status VARCHAR(10) NOT NULL COLLATE utf8mb4_unicode_ci, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }
}
