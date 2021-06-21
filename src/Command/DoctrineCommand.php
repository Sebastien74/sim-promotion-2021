<?php

namespace App\Command;

use Doctrine\Common\Cache\ArrayCache;

/**
 * DoctrineCommand
 *
 * To execute doctrine commands
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class DoctrineCommand extends BaseCommand
{
    /**
     * Execute doctrine:schema:update
     *
     * @return string $output->fetch()
     */
    public function update(): string
    {
        return $this->execute([
            'command' => 'doctrine:schema:update',
            '--force' => true
        ]);
    }

    /**
     * Execute doctrine:cache:clear-result
     *
     * @return string $output->fetch()
     */
    public function cacheClearResult(): string
    {
        $this->execute(['command' => 'cache:pool:clear']);
        $this->execute(['command' => 'doctrine:cache:clear-metadata']);
        $this->execute(['command' => 'doctrine:cache:clear-query']);

        $cacheDriver = new ArrayCache();
        $cacheDriver->deleteAll();

        return $this->execute(['command' => 'doctrine:cache:clear-result']);
    }

    /**
     * Execute doctrine:schema:update
     *
     * @return string $output->fetch()
     */
    public function validate(): string
    {
        return $this->execute(['command' => 'doctrine:schema:validate']);
    }

    /**
     * Execute doctrine:fixtures:load
     *
     * @return string $output->fetch()
     */
    public function fixtures(): string
    {
        return $this->execute([
            'command' => 'doctrine:fixtures:load',
            '--no-interaction' => true
        ]);
    }
}