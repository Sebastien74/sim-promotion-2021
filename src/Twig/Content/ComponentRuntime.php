<?php

namespace App\Twig\Content;

use App\Entity\Core\Website;
use App\Entity\Layout\Block;
use App\Entity\Layout\BlockType;
use App\Entity\Layout\Col;
use App\Entity\Layout\Zone;
use App\Entity\Security\User;
use App\Entity\Translation\i18n;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * ComponentRuntime
 *
 * @property Request $request
 * @property Environment $templating
 * @property EntityManagerInterface $entityManager
 * @property TokenStorageInterface $tokenStorage
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ComponentRuntime implements RuntimeExtensionInterface
{
    private $request;
    private $templating;
    private $entityManager;
    private $tokenStorage;

    /**
     * ComponentRuntime constructor.
     *
     * @param RequestStack $requestStack
     * @param Environment $templating
     * @param EntityManagerInterface $entityManager
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        RequestStack $requestStack,
        Environment $templating,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->templating = $templating;
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Check if is Admin User
     *
     * @return bool
     */
    public function isComponentUser()
    {
        $currentUser = method_exists($this->tokenStorage, 'getToken') && $this->tokenStorage->getToken()
            ? $this->tokenStorage->getToken()->getUser() : NULL;
        return $currentUser instanceof User;
    }

    /**
     * Generate & render Zone
     *
     * @param Website $website
     * @param array $options
     */
    public function newZone(Website $website, $options = [])
    {
        $template = $website->getConfiguration()->getTemplate();
        $zone = $this->addZone($options);

        if (!empty($options['blocks'])) {
            foreach ($options['blocks'] as $key => $configuration) {
                foreach ($configuration as $blockTemplate => $blockOptions) {
                    try {
                        $this->newBlock($website, $blockTemplate, $blockOptions, $zone);
                    } catch (LoaderError $e) {
                    } catch (RuntimeError $e) {
                    } catch (SyntaxError $e) {
                    }
                }
            }
        }

        try {
            echo $this->templating->render('front/' . $template . '/include/zone.html.twig', [
                'disabledEditTools' => true,
                'forceContainer' => !$zone->getFullSize(),
                'transitions' => [],
                'seo' => $options['seo'],
                'website' => $website,
                'zone' => $zone
            ]);
        } catch (LoaderError $e) {
        } catch (RuntimeError $e) {
        } catch (SyntaxError $e) {
        }
    }

    /**
     * Generate & render block
     *
     * @param Website $website
     * @param string $blockTemplate
     * @param array $options
     * @param Zone|null $zone
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function newBlock(Website $website, string $blockTemplate, array $options = [], Zone $zone = NULL)
    {
        $block = $this->addBlock($blockTemplate, $options);
        $col = $this->addCol($block);
        $newZone = !$zone ? $this->addZone() : $zone;
        $newZone->addCol($col);
        $newZone->setCustomClass($blockTemplate);

        if ($blockTemplate === 'titleheader') {
            $newZone->setPaddingTop(NULL);
            $newZone->setPaddingBottom(NULL);
        }

        if (!$zone) {

            $template = $website->getConfiguration()->getTemplate();
            $arguments = array_merge([
                'template' => $template,
                'block' => $block,
                'website' => $website
            ], $options);

            echo $this->templating->render('front/' . $template . '/blocks/' . $blockTemplate . '/default.html.twig', $arguments);
        }
    }

    /**
     * Add Zone
     *
     * @param array $options
     * @return Zone
     */
    private function addZone($options = [])
    {
        $fullSize = isset($options['fullSize']) ? $options['fullSize'] : false;
        $position = isset($options['position']) ? $options['position'] : 1;
        $background = isset($options['background']) ? $options['background'] : NULL;

        $zone = new Zone();
        $zone->setFullSize($fullSize);
        $zone->setPosition($position);
        $zone->setBackgroundColor($background);

        return $zone;
    }

    /**
     * Add Col
     *
     * @param Block $block
     * @return Col
     */
    private function addCol(Block $block)
    {
        $col = new Col();
        $col->addBlock($block);

        return $col;
    }

    /**
     * Add Block
     *
     * @param string $blockTemplate
     * @param array $options
     * @return Block
     */
    private function addBlock(string $blockTemplate, array $options = [])
    {
        $titleForce = isset($options['titleForce']) ? $options['titleForce'] : 2;
        $color = isset($options['color']) ? $options['color'] : NULL;

        /** @var BlockType $blockType */
        $blockType = $this->entityManager->getRepository(BlockType::class)->findOneBy(['slug' => $blockTemplate]);
        $i18n = $this->addI18n($blockTemplate, $titleForce);

        $block = new Block();
        $block->setColor($color);
        $block->addI18n($i18n);
        $block->setBlockType($blockType);
        $block->setAdminName('force-i18n');

        return $block;
    }

    /**
     * Add i18n
     *
     * @param string $blockTemplate
     * @param int $titleForce
     * @return i18n
     */
    private function addI18n(string $blockTemplate, int $titleForce = 2)
    {
        $faker = Factory::create();

        $i18n = new i18n();
        $i18n->setTitleForce($titleForce);
        $i18n->setLocale($this->request->getLocale());
        $i18n->setTitle('H' . $titleForce . '. ' . $faker->text(35));
        $i18n->setBody($faker->text(150));
        $i18n->setIntroduction($faker->text(600));
        $i18n->setSubTitle('Sous-titre ' . $faker->text(15));

        return $i18n;
    }
}