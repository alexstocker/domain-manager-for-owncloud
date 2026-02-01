<?php

declare(strict_types=1);

namespace OCA\DomainManager\Db;

use OCP\IDBConnection;

class DbDomainRepository implements IDomainRepository
{
    private $db;

    public function __construct(IDBConnection $db)
    {
        $this->db = $db;
    }

    public function findAll(): array
    {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('domain_manager_domains');
        $cursor = $qb->execute();
        $rows = $cursor->fetchAll();
        
        return array_map(function ($row) {
            $row['configuration'] = json_decode($row['configuration'], true);
            if (isset($row['last_lookup_data'])) {
                $row['last_lookup_data'] = json_decode($row['last_lookup_data'], true);
            }
            // Merge price, tax, payment period into configuration for frontend consumption
            if (isset($row['price'])) $row['configuration']['price'] = $row['price'];
            if (isset($row['tax_rate'])) $row['configuration']['tax_rate'] = $row['tax_rate'];
            if (isset($row['payment_period'])) $row['configuration']['payment_period'] = $row['payment_period'];
            
            return $row;
        }, $rows);
    }

    public function findAllForOwner(?string $owner): array
    {
        // if owner is null, return all
        if ($owner === null) {
            return $this->findAll();
        }

        $query = $this->db->getQueryBuilder();
        $query->select('*')
            ->from('domain_manager_domains')
            ->where($query->expr()->eq('owner', $query->createNamedParameter($owner)));
        $result = $query->execute();

        $rows = $result->fetchAll();
        foreach ($rows as &$row) {
            if (isset($row['configuration']) && !empty($row['configuration'])) {
                $row['configuration'] = json_decode($row['configuration'], true);
            } else {
                $row['configuration'] = [];
            }
            // Merge price, tax, payment period into configuration for frontend consumption
            if (isset($row['price'])) $row['configuration']['price'] = $row['price'];
            if (isset($row['tax_rate'])) $row['configuration']['tax_rate'] = $row['tax_rate'];
            if (isset($row['payment_period'])) $row['configuration']['payment_period'] = $row['payment_period'];
        }
        return $rows;
    }

    public function findByDomain(string $domain): ?array
    {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from('domain_manager_domains')
            ->where($qb->expr()->eq('domain', $qb->createNamedParameter($domain)));
        $cursor = $qb->execute();
        $row = $cursor->fetch();

        if ($row) {
            $row['configuration'] = json_decode($row['configuration'], true);
            if (isset($row['last_lookup_data'])) {
                $row['last_lookup_data'] = json_decode($row['last_lookup_data'], true);
            }
            // Merge price, tax, payment period into configuration for frontend consumption
            if (isset($row['price'])) $row['configuration']['price'] = $row['price'];
            if (isset($row['tax_rate'])) $row['configuration']['tax_rate'] = $row['tax_rate'];
            if (isset($row['payment_period'])) $row['configuration']['payment_period'] = $row['payment_period'];
            return $row;
        }
        return null;
    }

    public function findByDomainAndOwner(string $domain, ?string $owner): ?array
    {
        $query = $this->db->getQueryBuilder();
        $query->select('*')
            ->from('domain_manager_domains')
            ->where($query->expr()->eq('domain', $query->createNamedParameter($domain)));

        if ($owner === null) {
            $query->andWhere($query->expr()->isNull('owner'));
        } else {
            $query->andWhere($query->expr()->eq('owner', $query->createNamedParameter($owner)));
        }

        $result = $query->execute();
        $row = $result->fetch();
        if ($row) {
            if (isset($row['configuration']) && !empty($row['configuration'])) {
                $row['configuration'] = json_decode($row['configuration'], true);
            } else {
                $row['configuration'] = [];
            }
            // Merge price, tax, payment period into configuration for frontend consumption
            if (isset($row['price'])) $row['configuration']['price'] = $row['price'];
            if (isset($row['tax_rate'])) $row['configuration']['tax_rate'] = $row['tax_rate'];
            if (isset($row['payment_period'])) $row['configuration']['payment_period'] = $row['payment_period'];
            return $row;
        }
        return null;
    }

    public function findById(int $id): ?array
    {
        $query = $this->db->getQueryBuilder();
        $query->select('*')
            ->from('domain_manager_domains')
            ->where($query->expr()->eq('id', $query->createNamedParameter($id, \PDO::PARAM_INT)));
        $result = $query->execute();
        $row = $result->fetch();
        if ($row) {
            if (isset($row['configuration']) && !empty($row['configuration'])) {
                $row['configuration'] = json_decode($row['configuration'], true);
            } else {
                $row['configuration'] = [];
            }
            // Merge price, tax, payment period into configuration for frontend consumption
            if (isset($row['price'])) $row['configuration']['price'] = $row['price'];
            if (isset($row['tax_rate'])) $row['configuration']['tax_rate'] = $row['tax_rate'];
            if (isset($row['payment_period'])) $row['configuration']['payment_period'] = $row['payment_period'];
            return $row;
        }
        return null;
    }

    public function insert(string $domain, array $configuration = []): void
    {
        $owner = isset($configuration['owner']) ? $configuration['owner'] : null;
        // avoid keeping owner duplicated inside configuration JSON
        $cfgForStorage = $configuration;
        if (isset($cfgForStorage['owner'])) {
            unset($cfgForStorage['owner']);
        }
        if (isset($cfgForStorage['price'])) unset($cfgForStorage['price']);
        if (isset($cfgForStorage['tax_rate'])) unset($cfgForStorage['tax_rate']);
        if (isset($cfgForStorage['payment_period'])) unset($cfgForStorage['payment_period']);

        $query = $this->db->getQueryBuilder();
        $query->insert('domain_manager_domains')
            ->setValue('domain', $query->createNamedParameter($domain))
            ->setValue('provider', $query->createNamedParameter($configuration['provider'] ?? 'local'))
            ->setValue('configuration', $query->createNamedParameter(json_encode($cfgForStorage)))
            ->setValue('created_at', $query->createNamedParameter(date('Y-m-d H:i:s')));

        if ($owner !== null) {
            $query->setValue('owner', $query->createNamedParameter($owner));
        }

        if (isset($configuration['price'])) {
            $query->setValue('price', $query->createNamedParameter($configuration['price']));
        }
        if (isset($configuration['tax_rate'])) {
            $query->setValue('tax_rate', $query->createNamedParameter($configuration['tax_rate']));
        }
        if (isset($configuration['payment_period'])) {
            $query->setValue('payment_period', $query->createNamedParameter($configuration['payment_period']));
        }

        $this->db->executeUpdate($query->getSQL(), $query->getParameters(), $query->getParameterTypes());
    }

    public function update(int $id, string $domain, array $configuration = []): void
    {
        $owner = isset($configuration['owner']) ? $configuration['owner'] : null;
        $cfgForStorage = $configuration;
        if (isset($cfgForStorage['owner'])) {
            unset($cfgForStorage['owner']);
        }
        if (isset($cfgForStorage['price'])) unset($cfgForStorage['price']);
        if (isset($cfgForStorage['tax_rate'])) unset($cfgForStorage['tax_rate']);
        if (isset($cfgForStorage['payment_period'])) unset($cfgForStorage['payment_period']);

        $query = $this->db->getQueryBuilder();
        $query->update('domain_manager_domains')
            ->set('domain', $query->createNamedParameter($domain))
            ->set('provider', $query->createNamedParameter($configuration['provider'] ?? 'local'))
            ->set('configuration', $query->createNamedParameter(json_encode($cfgForStorage)))
            ->where($query->expr()->eq('id', $query->createNamedParameter($id, \PDO::PARAM_INT)));

        if ($owner !== null) {
            $query->set('owner', $query->createNamedParameter($owner));
        }

        if (isset($configuration['price'])) {
            $query->set('price', $query->createNamedParameter($configuration['price']));
        }
        if (isset($configuration['tax_rate'])) {
            $query->set('tax_rate', $query->createNamedParameter($configuration['tax_rate']));
        }
        if (isset($configuration['payment_period'])) {
            $query->set('payment_period', $query->createNamedParameter($configuration['payment_period']));
        }

        $this->db->executeUpdate($query->getSQL(), $query->getParameters(), $query->getParameterTypes());
    }

    public function delete(int $id): void
    {
        $query = $this->db->getQueryBuilder();
        $query->delete('domain_manager_domains')
            ->where($query->expr()->eq('id', $query->createNamedParameter($id, \PDO::PARAM_INT)));

        $this->db->executeUpdate($query->getSQL(), $query->getParameters(), $query->getParameterTypes());
    }

    public function findOrphaned(): array
    {
        $query = $this->db->getQueryBuilder();
        $query->select('*')
            ->from('domain_manager_domains')
            ->where($query->expr()->isNull('owner'));
        $result = $query->execute();

        $rows = $result->fetchAll();
        foreach ($rows as &$row) {
            if (isset($row['configuration']) && !empty($row['configuration'])) {
                $row['configuration'] = json_decode($row['configuration'], true);
            } else {
                $row['configuration'] = [];
            }
            // Merge price, tax, payment period into configuration for frontend consumption
            if (isset($row['price'])) $row['configuration']['price'] = $row['price'];
            if (isset($row['tax_rate'])) $row['configuration']['tax_rate'] = $row['tax_rate'];
            if (isset($row['payment_period'])) $row['configuration']['payment_period'] = $row['payment_period'];
        }
        return $rows;
    }

    public function setOwner(int $id, ?string $owner): void
    {
        $query = $this->db->getQueryBuilder();
        $query->update('domain_manager_domains')
            ->set('owner', $query->createNamedParameter($owner))
            ->where($query->expr()->eq('id', $query->createNamedParameter($id, \PDO::PARAM_INT)));
        $this->db->executeUpdate($query->getSQL(), $query->getParameters(), $query->getParameterTypes());
    }

    public function updateLookupData(int $id, array $data, int $timestamp): void
    {
        $qb = $this->db->getQueryBuilder();
        $qb->update('domain_manager_domains')
            ->set('last_lookup_data', $qb->createNamedParameter(json_encode($data)))
            ->set('last_lookup_time', $qb->createNamedParameter($timestamp))
            ->where($qb->expr()->eq('id', $qb->createNamedParameter($id)));
        $qb->execute();
    }
}
