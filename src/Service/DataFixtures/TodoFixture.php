<?php

namespace App\Service\DataFixtures;

use App\Entity\Core\Website;
use App\Entity\Security\User;
use App\Entity\Todo\Task;
use App\Entity\Todo\Todo;
use Doctrine\ORM\EntityManagerInterface;

/**
 * TodoFixture
 *
 * Todo Fixture management
 *
 * @property EntityManagerInterface $entityManager
 * @property Website $website
 * @property User $user
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class TodoFixture
{
    private $entityManager;
    private $website;
    private $user;

    /**
     * GdprFixture constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Add Todo[]
     *
     * @param Website $website
     * @param User|NULL $user
     */
    public function add(Website $website, User $user = NULL)
    {
        $this->website = $website;
        $this->user = $user;

        $this->addTodo('Développement', 'development');
        $this->addTodo('Pré-production', 'preproduction');
        $this->addTodo('Production', 'production');
    }

    /**
     * Add Todo production
     *
     * @param string $adminName
     * @param string $slug
     */
    private function addTodo(string $adminName, string $slug)
    {
        $position = count($this->entityManager->getRepository(Todo::class)->findBy([
                'website' => $this->website
            ])) + 1;

        $todo = new Todo();
        $todo->setAdminName($adminName);
        $todo->setSlug($slug);
        $todo->setPosition($position);
        $todo->setWebsite($this->website);

        if ($this->user) {
            $todo->setCreatedBy($this->user);
        }

        $this->entityManager->persist($todo);
        $this->entityManager->flush();

        $method = 'get' . ucfirst($slug) . 'Tasks';
        $this->addTasks($todo, $this->$method());
    }

    /**
     * Add Task[]
     *
     * @param Todo $todo
     * @param array $tasks
     */
    private function addTasks(Todo $todo, array $tasks)
    {
        foreach ($tasks as $key => $task) {
            $newTask = new Task();
            $newTask->setAdminName($task);
            $newTask->setPosition($key + 1);
            $todo->addTask($newTask);
        }
    }

    /**
     * Todo development tasks
     *
     * @return array
     */
    private function getDevelopmentTasks(): array
    {
        return [
            'Si multi-sites, penser à changer les inclusion "default" par le bon code site',
            'Pour les entités de type fiche: add seo on created',
            'Pour les entités de type fiche en front: use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;',
            'Pour les entités de type fiche: & ajouter dans LocaleService',
            'Vérifier les vulnérabilités de sécurité : symfony check:security',
            'Générer la configuration des entités - Rubrique "Développement > Générer la configuration des entités"',
            'Configurer les Thumbnails - Rubrique "Configuration > Thumbnails"',
            'Configurer les Mises en page - Rubrique "Configuration > Configuration des mises en page"',
            'Configurer les Grilles de mises en page si nécéssaire - Rubrique "Configuration > Grilles"',
        ];
    }

    /**
     * Todo Pre production tasks
     *
     * @return array
     */
    private function getPreproductionTasks(): array
    {
        return [
            'Générer les favicons',
            "Vérifier que l'OG image est bien remplie avec le bon format (1200 pixels x 628)",
            "Vérifier que toutes les images dans la partie « Éléments de site » sont bien des images du client et non Félix",
            'Vérifier que la maquette est iso-preprod',
            'Vérifier que tout fonctionne bien sur mobile',
            'Vérifier le site sous Firefox',
            'Vérifier le site sous Chrome',
            'Vérifier le site sous Edge',
            'Vérifier le site sous Safari',
            'Vérifier le site sous Safari',
            "Vérifier qu'il y ait bien un titre H1 pertinent sur la home",
            "Vérifier qu'il y ait bien des Hn sur la home",
            "Vérifier qu'il y ait bien des Hn sur la home",
            'Générer le certificat SSL',
            'Configuration du domaine de production si nécessaire (Ex: nouveau site sans nom de domaine)',
            'Renseigner les adresses  - Rubrique "Information"',
            'Renseigner les numéros de téléphones  - Rubrique "Information"',
            'Renseigner les e-mails par défaut - Rubrique "Information"',
            'Renseigner les réseaux sociaux  - Rubrique "Information"',
            "Vérifier que les informations clients sont toutes bien remplies dans la partie information et qu'il ne reste aucune info Félix",
            'Générer les traductions',
            "Vérifier que tous les cookies utilisés sur le site sont bien activés dans le module RGPD, et que ceux inutiles sont désactivés",
            'Désactiver tous les modules non utilisés',
            'Vérifier le fonctionnement de tous les modules',
            'Configurer le compte client',
            'Tester back logger sur compte client',
            'Configurer les tâches planifiées',
            'Vérifier que les traductions sont générées',
            'Créer compte utilisateur avec identifiant complexe et personnalisé au client',
            "Modifier l'URL de connexion à l'admin + identifiants webmaster",
            'Tester les tâches planifiées',
            'Configurer les couleurs  - Rubrique "Configuration > Élements de site"',
            'Mettre les descriptions pour les Classes personnalisées - Rubrique "Configuration > Élements de site"',
            'Verifier la configuration des Thumbnails - Rubrique "Configuration > Thumbnails"',
            'Verifier la configuration des Mises en page - Rubrique "Configuration > Configuration des mises en page"',
            'Verifier la configuration des Grilles de mises en page si nécéssaire - Rubrique "Configuration > Grilles"',
            'Tester les formulaires',
            'Demander au client de vérifier le fonctionnement du formulaire',
            "Prevenir le webmarket qu'un site va bientôt sortir",
            'Faire tester les formulaires par le client',
            "[MULTI-LINGUES] : Vérifier l'ensemble des logos",
            '[MULTI-LINGUES] : Vérifier les traductions',
            'https://gtmetrix.com',
            'https://web.dev/measure/',
            'https://validator.w3.org',
        ];
    }

    /**
     * Todo production tasks
     *
     * @return array
     */
    private function getProductionTasks(): array
    {
        return [
            'Configurer les domaines principaux',
            'Configurer les tâches planifiées',
            'Renseigner User Agent Google Analytics ou Google Tag manager  - Rubrique "Référencement > Configuration"',
            "Faire les 301 ou mettre les mêmes URL que l'ancien site",
            "Retirer les images de tests de la bibliothèque de médias",
            'Activer le référencement du site - Rubrique "Site courant"',
            'Vérifier le robots.txt',
            'Vérifier le sitemap.xml',
            "[MULTI-LINGUES] : Vérifier l'ensemble des logos",
            "[MULTI-LINGUES] : Vérifier les traductions",
            "Tester les formulaires",
            "Faire tester les formulaires par le client",
            'Configuration mail : https://intranet.felix-creation.fr/books/d%C3%A9veloppement-web/page/tuto-debug-mails-des-sites#bkmrk-site-h%C3%A9berg%C3%A9-chez-',
            'https://gtmetrix.com',
            'https://web.dev/measure/',
            'https://validator.w3.org',
        ];
    }
}