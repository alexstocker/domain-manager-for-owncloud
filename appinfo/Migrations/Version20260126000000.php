<?php

declare(strict_types=1);

namespace OCA\domain_manager\Migrations;

use OCP\Migration\ISchemaMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20260126000000 implements ISchemaMigration
{
    public function changeSchema(Schema $schema, array $options)
    {
        $prefix = $options['tablePrefix'];
        $tableName = $prefix . 'domain_manager_domains';

        if ($schema->hasTable($tableName)) {
            $table = $schema->getTable($tableName);
            
            if (!$table->hasColumn('last_lookup_data')) {
                $table->addColumn('last_lookup_data', 'text', [
                    'notnull' => false,
                    'length' => 65535,
                ]);
            }
            
            if (!$table->hasColumn('last_lookup_time')) {
                $table->addColumn('last_lookup_time', 'integer', [
                    'notnull' => false,
                    'default' => 0,
                ]);
            }
        }

        return $schema;
    }
}
