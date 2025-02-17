<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration to extend product table: adds cost and stock.
 */
final class Version20250213172809 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migration to extend product table: adds cost and stock.';
    }

    public function up(Schema $schema): void
    {
        # index has been renamed by doctrine
        $this->addSql('ALTER TABLE tblProductData RENAME INDEX strproductcode TO UNIQ_2C11248662F10A58');
        $this->addSql('ALTER TABLE tblProductData ADD intProductStock INT NOT NULL, ADD decProductCost DECIMAL(10, 2) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tblProductData DROP intProductStock, DROP decProductCost');
        $this->addSql('ALTER TABLE tblProductData RENAME INDEX uniq_2c11248662f10a58 TO strProductCode');
    }
}
