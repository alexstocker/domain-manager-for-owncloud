<?php

namespace OCA\DomainManager\Service;

use DateTimeImmutable;
use DateInterval;
use Exception;

class DomainAgeService {

    /**
     * Calculates the age of a domain based on its creation date.
     *
     * @param DateTimeImmutable|null $creationDate
     * @return DateInterval|null Returns null if creation date is unknown.
     */
    public function calculateAge(?DateTimeImmutable $creationDate): ?DateInterval {
        if ($creationDate === null) {
            return null;
        }

        $now = new DateTimeImmutable();
        
        // If creation date is in the future (sanity check), return zero interval
        if ($creationDate > $now) {
            return new DateInterval('P0D');
        }

        return $creationDate->diff($now);
    }

    /**
     * Returns a human-readable string for the domain age.
     * e.g. "5 years, 3 months" or "10 days"
     *
     * @param DateInterval|null $interval
     * @return string
     */
    public function getReadableAge(?DateInterval $interval): string {
        if ($interval === null) {
            return 'Unknown';
        }

        $parts = [];

        if ($interval->y > 0) {
            $parts[] = $interval->y . ' ' . ($interval->y === 1 ? 'year' : 'years');
        }

        if ($interval->m > 0) {
            $parts[] = $interval->m . ' ' . ($interval->m === 1 ? 'month' : 'months');
        }

        // If less than a month, show days
        if ($interval->y === 0 && $interval->m === 0) {
            if ($interval->d > 0) {
                $parts[] = $interval->d . ' ' . ($interval->d === 1 ? 'day' : 'days');
            } else {
                return 'Less than 1 day';
            }
        }

        return implode(', ', $parts);
    }

    /**
     * Example integration method to fetch and calculate.
     * You would inject your Whois/RDAP service here.
     */
    public function getDomainAgeString(string $domain, callable $creationDateProvider): string {
        // $creationDateProvider is a closure or service call that returns ?DateTimeImmutable
        // e.g. $creationDate = $this->rdapService->getCreationDate($domain);
        $creationDate = $creationDateProvider($domain);
        
        $age = $this->calculateAge($creationDate);
        return $this->getReadableAge($age);
    }
}