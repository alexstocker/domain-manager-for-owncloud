<?php

declare(strict_types=1);

namespace OCA\domain_manager\Migrations;

use OCP\Migration\ISchemaMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20260127000000 implements ISchemaMigration
{
    public function changeSchema(Schema $schema, array $options)
    {
        $prefix = $options['tablePrefix'];
        $tableName = $prefix . 'domain_manager_domains';

        if ($schema->hasTable($tableName)) {
            $table = $schema->getTable($tableName);
            
            if (!$table->hasColumn('price')) {
                $table->addColumn('price', 'decimal', [
                    'notnull' => false,
                    'scale' => 2,
                    'precision' => 10,
                    'default' => 0.00
                ]);
            }
            
            if (!$table->hasColumn('tax_rate')) {
                $table->addColumn('tax_rate', 'decimal', [
                    'notnull' => false,
                    'scale' => 2,
                    'precision' => 5,
                    'default' => 0.00
                ]);
            }

            if (!$table->hasColumn('payment_period')) {
                $table->addColumn('payment_period', 'string', [
                    'notnull' => false,
                    'length' => 64,
                    'default' => ''
                ]);
            }
        }

        return $schema;
    }
}
