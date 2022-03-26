<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200303053716 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE dtb_delivery_duration SET duration = -1 WHERE id = 9 and duration = 0");
		$check = $this->fetchAll('select * from dtb_page');
    }

    public function down(Schema $schema): void
    {
        $this->addSql("INSERT INTO dtb_page (`id`, `master_page_id`, `page_name`, `url`, `file_name`, `edit_type`, `author`, `description`, `keyword`, `create_date`, `update_date`, `meta_robots`, `meta_tags`, `discriminator_type`) VALUES ('46', NULL, 'mine', 'restaurant', 'mine', '1', NULL, NULL, NULL, '2021-11-25 00:00:00', '2021-11-29 00:00:00', NULL, NULL, '');");
        $this->addSql("INSERT INTO dtb_page_layout (`page_id`, `layout_id`, `sort_no`, `discriminator_type`) VALUES ('46', '2', '42', 'pagelayout');");
        $this->addSql("UPDATE dtb_delivery_duration SET duration = 0 WHERE id = 9 and duration = -1");
    }
}
