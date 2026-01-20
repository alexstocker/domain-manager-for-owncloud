<?php

declare(strict_types=1);

namespace OCA\domain_manager\Migrations;

use OCP\Migration\ISchemaMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20260120000000 implements ISchemaMigration
{
    public function changeSchema(Schema $schema, array $options)
    {
        $prefix = $options['tablePrefix'];
        $table = $schema->getTable($prefix . 'domain_manager_domains');

        if (!$table->hasColumn('configuration')) {
            $table->addColumn('configuration', 'text', [
                'notnull' => false,
                'default' => null,
            ]);
        }

        if (!$table->hasColumn('provider')) {
            $table->addColumn('provider', 'string', [
                'length' => 64,
                'notnull' => false,
                'default' => 'local',
            ]);
        }

        return $schema;
    }
}
