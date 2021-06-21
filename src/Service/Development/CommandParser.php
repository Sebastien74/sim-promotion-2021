<?php

namespace App\Service\Development;

use Exception;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * CommandParser
 *
 * @property KernelInterface $kernel
 * @property array $excludedNamespaces
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CommandParser
{
    private $kernel;
    private $excludedNamespaces;

    /**
     * CommandParser constructor.
     *
     * @param KernelInterface $kernel
     * @param array $excludedNamespaces
     */
    public function __construct(KernelInterface $kernel, array $excludedNamespaces = [])
    {
        $this->kernel = $kernel;
        $this->excludedNamespaces = $excludedNamespaces;
    }

    /**
     * Execute the console command "list" with XML output to have all available command
     *
     * @return array
     * @throws Exception
     */
    public function getCommands()
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'list',
            '--format' => 'xml'
        ]);

        $output = new StreamOutput(fopen('php://memory', 'w+'));
        $application->run($input, $output);
        rewind($output->getStream());

        return $this->extractCommandsFromXML(stream_get_contents($output->getStream()));
    }

    /**
     * Chack if command exist
     *
     * @param string $name
     * @return bool
     * @throws Exception
     */
    public function getCommand(string $name)
    {
        $commands = $this->getCommands();

        foreach ($commands as $group => $commandsGroup) {
            if (isset($commandsGroup[$name])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extract an array of available Symfony command from the XML output
     *
     * @param $xml
     * @return array
     */
    private function extractCommandsFromXML($xml)
    {
        if ($xml == '') {
            return [];
        }

        $regex = "#<\s*?symfony\b[^>]*>(.*?)</symfony\b[^>]*>#s";
        preg_match($regex, $xml, $matches);
        $xml = trim('<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL . '<symfony>' . $matches[1] . PHP_EOL . '</symfony>');
        $node = new \SimpleXMLElement($xml);
        $commandsList = [];
        foreach ($node->namespaces->namespace as $namespace) {
            $namespaceId = (string)$namespace->attributes()->id;
            if (!in_array($namespaceId, $this->excludedNamespaces)) {
                foreach ($namespace->command as $command) {
                    $commandsList[$namespaceId][(string)$command] = (string)$command;
                }
            }
        }
        return $commandsList;
    }
}