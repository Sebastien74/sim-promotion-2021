<?php

namespace App\DataFixtures;

use App\Entity\Core\Module;
use App\Entity\Core\Website;
use App\Entity\Layout\BlockType;
use App\Entity\Security\User;
use App\Entity\Translation\i18n;
use App\Service\Core\SubscriberService;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * WebsiteFixture
 *
 * Website Fixture management
 *
 * @property TranslatorInterface $translator
 * @property SubscriberService $subscriber
 * @property KernelInterface $kernel
 * @property array $descriptions
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class WebsiteFixture extends BaseFixture implements DependentFixtureInterface
{
    protected $translator;
    protected $subscriber;

    private $kernel;
    private $descriptions = [];

    /**
     * WebsiteFixture constructor.
     *
     * @param TranslatorInterface $translator
     * @param SubscriberService $subscriber
     * @param KernelInterface $kernel
     */
    public function __construct(TranslatorInterface $translator, SubscriberService $subscriber, KernelInterface $kernel)
    {
        parent::__construct($translator, $subscriber);

        $this->kernel = $kernel;
    }

    /**
     * {@inheritDoc}
     */
    protected function loadData(ObjectManager $manager)
    {
        $descriptionDirname = $this->kernel->getProjectDir() . '/bin/data/fixtures/extensions.yaml';
        $descriptionDirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $descriptionDirname);
        $this->descriptions = Yaml::parseFile($descriptionDirname);

        /** @var User $user */
        $user = $this->getReference('webmaster');

        $website = new Website();

        $this->subscriber->get(\App\Service\DataFixtures\WebsiteFixture::class)->initialize($website, $this->locale, $user);

        $website->setAdminName('Site principal');
        $website->setSlug('default');

        $configuration = $website->getConfiguration();
        $configuration->setHasDefault(true);

        $manager->persist($website);
        $manager->flush();

        $this->addReference('website', $website);
        $this->addReference('configuration', $configuration);

        $this->addBlocksTypesI18ns($manager, $website);
        $this->addModulesI18ns($manager, $website);
    }

    /**
     * Add Modules i18n
     *
     * @param ObjectManager $manager
     * @param Website $website
     */
    private function addBlocksTypesI18ns(ObjectManager $manager, Website $website)
    {
        $blocksTypes = $manager->getRepository(BlockType::class)->findAll();
        foreach ($blocksTypes as $blockType) {
            $this->addI18n($manager, $website,'blocks_types', $blockType);
        }
    }

    /**
     * Add Modules i18n
     *
     * @param ObjectManager $manager
     * @param Website $website
     */
    private function addModulesI18ns(ObjectManager $manager, Website $website)
    {
        $modules = $manager->getRepository(Module::class)->findAll();
        foreach ($modules as $module) {
            $this->addI18n($manager, $website,'modules', $module);
        }
    }

    /**
     * Add I18n
     *
     * @param ObjectManager $manager
     * @param Website $website
     * @param string $type
     * @param $entity
     */
    private function addI18n(ObjectManager $manager, Website $website, string $type, $entity)
    {
        $descriptions = !empty($this->descriptions[$type][$entity->getSlug()])
            ? $this->descriptions[$type][$entity->getSlug()]
            : [];

        $hasAdvert = isset($descriptions['advert']) ? $descriptions['advert'] : false;
        $entity->setInAdvert($hasAdvert);

        foreach ($descriptions as $local => $fields) {

            if($local !== 'advert') {

                $i18n = new i18n();
                $entity->addI18n($i18n);
                $i18n->setLocale($local);
                $i18n->setWebsite($website);

                foreach ($fields as $property => $value) {
                    $setter = 'set' . ucfirst($property);
                    if(method_exists($i18n, $setter)) {
                        $i18n->$setter($value);
                    }
                }
            }
        }

        if($entity->getI18ns()->count() === 0) {
            $i18n = new i18n();
            $i18n->setWebsite($website);
            $i18n->setLocale($this->locale);
            $entity->addI18n($i18n);
        }

        $manager->persist($entity);
        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getDependencies()
    {
        return [
            SecurityFixture::class,
            BlockTypeFixture::class,
            ActionFixture::class,
        ];
    }
}
