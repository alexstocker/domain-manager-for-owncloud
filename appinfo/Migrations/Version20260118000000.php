<?php

declare(strict_types=1);

namespace OCA\domain_manager\Migrations;

use OCP\Migration\ISchemaMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20260118000000 implements ISchemaMigration
{
    public function changeSchema(Schema $schema, array $options)
    {
        $prefix = $options['tablePrefix'];

        if ($schema->hasTable($prefix . 'domain_manager_domains')) {
            return $schema;
        }

        $table = $schema->createTable($prefix . 'domain_manager_domains');
        $table->addColumn('id', 'integer', ['autoincrement' => true, 'notnull' => true]);
        $table->addColumn('domain', 'string', ['length' => 255, 'notnull' => true]);
        $table->addColumn('created_at', 'datetime', ['notnull' => true]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['domain'], 'dm_domain_unique');

        return $schema;
    }
}
