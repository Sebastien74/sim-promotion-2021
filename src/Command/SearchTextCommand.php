<?php

namespace App\Command;

use App\Entity\Module\Catalog\Product;
use App\Entity\Core\Website;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * SearchTextCommand
 *
 * @property EntityManagerInterface $entityManager
 * @property Environment $twig
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class SearchTextCommand extends Command
{
    protected static $defaultName = 'cms:catalog:search:text';
    private $twig;

    /**
     * SearchTextCommand constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param Environment $twig
     */
    public function __construct(EntityManagerInterface $entityManager, Environment $twig)
    {
        $this->entityManager = $entityManager;
        $this->twig = $twig;

        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setDescription('To search products by text.')
            ->addArgument('arguments', InputArgument::REQUIRED, 'Array arguments.')
            ->addArgument('post', InputArgument::OPTIONAL, 'Text search.');
    }

    /**
     * @inheritdoc
     *
     * @return OutputInterface
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function execute(InputInterface $input, OutputInterface $output): OutputInterface
    {
        $arguments = $input->getArgument('arguments');

        /** @var Website $website */
        $website = $this->entityManager->getRepository(Website::class)->find($arguments['website']);
        $products = $this->entityManager->getRepository(Product::class)
            ->findLikeInTitle($website, $arguments['locale'], $input->getArgument('post'));

        $arguments['count'] = $count = count($products);
        $arguments['products'] = $products;

        $output->writeln($this->twig->render($arguments['template'], $arguments));
        return $output;
    }
}