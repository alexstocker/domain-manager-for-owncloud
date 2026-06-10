<?php

declare(strict_types=1);

namespace OCA\DomainManager\Settings;

use OCP\IL10N;
use OCP\Settings\ISection;

class PersonalSection implements ISection
{
    private $l;

    public function __construct(IL10N $l)
    {
        $this->l = $l;
    }

    public function getID()
    {
        return 'domain_manager';
    }

    public function getName()
    {
        return $this->l->t('Domain Manager');
    }

    public function getPriority()
    {
        return 75;
    }

    public function getIconName()
    {
        return 'link';
    }
}
