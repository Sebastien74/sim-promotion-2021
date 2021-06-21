<?php

namespace App\Service\DataFixtures;

use App\Entity\Core\ScheduledCommand;
use App\Entity\Core\Website;
use App\Entity\Security\User;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CommandFixture
 *
 * Command Fixture management
 *
 * @property EntityManagerInterface $entityManager
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CommandFixture
{
    private $entityManager;
    private $translator;

    /**
     * GdprFixture constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param TranslatorInterface $translator
     */
    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    /**
     * Add ScheduledCommand[]
     *
     * @param Website $website
     * @param User|NULL $user
     */
    public function add(Website $website, User $user = NULL)
    {
        foreach ($this->getScheduledConfiguration() as $configuration) {
            $this->addScheduledCommand($website, $configuration, $user);
        }
    }

    /**
     * Add ScheduledCommand
     *
     * @param Website $website
     * @param array $configuration
     * @param User|NULL $user
     */
    private function addScheduledCommand(Website $website, array $configuration, User $user = NULL)
    {
        $command = new ScheduledCommand();
        $command->setWebsite($website);
        $command->setCreatedBy($user);
        $command->setAdminName($configuration['name']);
        $command->setCommand($configuration['command']);
        $command->setCronExpression($configuration['expression']);
        $command->setDescription($configuration['description']);
        $command->setLogFile(Urlizer::urlize($configuration['command']) . '.log');
        $command->setActive(isset($configuration['active']) && $configuration['active']);

        $this->entityManager->persist($command);
        $this->entityManager->flush();
    }

    /**
     * Get Schedules configuration
     *
     * @return array
     */
    private function getScheduledConfiguration(): array
    {
        return [
            ['name' => 'Suppression des données RGPD', 'command' => 'gdpr:remove', 'expression' => '00 1 * * *', 'description' => $this->translator->trans('Supprime les données personnelles tous les jours à 1H du matin', [], 'admin')],
            ['name' => 'Suppression des tokens utilisateurs', 'command' => 'security:reset:token', 'expression' => '* * * * *', 'description' => $this->translator->trans('Suppression des tokens de plus de 2H', [], 'admin')],
            ['name' => 'Alertes expiration des mots de passe utilisateurs', 'command' => 'security:password:expire', 'expression' => '00 11 * * *', 'description' => $this->translator->trans("Envoi d'emails (arrive à expiration & à expiré) tous les jours à 11H du matin", [], 'admin')],
            ['name' => 'Synchronisation des Social walls', 'command' => 'social-wall:synchronization', 'expression' => '* * * * *', 'description' => $this->translator->trans('Mise à jour des social wall toutes les minutes', [], 'admin')],
        ];
    }
}