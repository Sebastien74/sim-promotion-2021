<?php

namespace App\Command;

/**
 * DebugCommand
 *
 * To execute debug commands
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class DebugCommand extends BaseCommand
{
    /**
     * Execute debug:{service}
     *
     * @param string $service
     * @return string $output->fetch()
     */
    public function debug(string $service): string
    {
        return $this->execute([
            'command' => 'debug:' . $service
        ]);
    }
}