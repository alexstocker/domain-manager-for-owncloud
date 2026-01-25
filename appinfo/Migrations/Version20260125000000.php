<?php

declare(strict_types=1);

namespace OCA\domain_manager\Migrations;

use OCP\Migration\ISchemaMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20260125000000 implements ISchemaMigration
{
    public function changeSchema(Schema $schema, array $options)
    {
        $prefix = $options['tablePrefix'];
        $tableName = $prefix . 'domain_manager_domains';

        if (!$schema->hasTable($tableName)) {
            return $schema; // nothing to do
        }

        $table = $schema->getTable($tableName);

        // Add owner column if missing
        if (!$table->hasColumn('owner')) {
            $table->addColumn('owner', 'string', [
                'length' => 64,
                'notnull' => false,
                'default' => null,
            ]);
        }

        // Replace unique index on domain with composite unique index on (domain, owner)
        // Drop existing unique index if present
        if ($table->hasIndex('dm_domain_unique')) {
            $table->dropIndex('dm_domain_unique');
        }

        // Add composite unique index
        if (!$table->hasIndex('dm_domain_unique')) {
            $table->addUniqueIndex(['domain', 'owner'], 'dm_domain_unique');
        }

        // Add index on owner to improve lookups
        if (!$table->hasIndex('idx_domain_owner')) {
            $table->addIndex(['owner'], 'idx_domain_owner');
        }

        return $schema;
    }
}
