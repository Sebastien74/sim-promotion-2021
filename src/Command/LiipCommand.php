<?php

namespace App\Command;

/**
 * LiipCommand
 *
 * To execute liip imagine commands
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class LiipCommand extends BaseCommand
{
    /**
     * Execute liip:imagine:cache:remove
     *
     * @return string $output->fetch()
     */
    public function remove(): string
    {
        return $this->execute([
            'command' => 'liip:imagine:cache:remove'
        ]);
    }
}