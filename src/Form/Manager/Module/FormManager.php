<?php

namespace App\Form\Manager\Module;

use App\Entity\Module\Form\Configuration;
use App\Entity\Module\Form\Form;
use App\Entity\Core\Website;
use App\Entity\Layout as Layout;
use App\Entity\Translation\i18n;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * FormManager
 *
 * Manage admin Form form
 *
 * @property EntityManagerInterface $entityManager
 * @property Request $request
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FormManager
{
    private $entityManager;
    private $request;

    /**
     * FormManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param RequestStack $requestStack
     */
    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack)
    {
        $this->entityManager = $entityManager;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @prePersist
     *
     * @param Form $form
     * @param Website $website
     * @throws Exception
     */
    public function prePersist(Form $form, Website $website)
    {
        if (!$form->getStepform()) {
            $configuration = new Configuration();
            $configuration->setSecurityKey(crypt(random_bytes(10), 'rl'));
            $configuration->setReceivingEmails(['contact@' . $this->request->getHost()]);
            $configuration->setSendingEmail('no-reply@' . $this->request->getHost());
            $configuration->setForm($form);
            $form->setConfiguration($configuration);
        }

        $this->addLayout($form, $website);

        $this->entityManager->persist($form);
    }

    /**
     * Add Layout & GDPR field
     *
     * @param Form $form
     * @param Website $website
     */
    private function addLayout(Form $form, Website $website)
    {
        $layout = new Layout\Layout();
        $layout->setAdminName($form->getAdminName());
        $layout->setForm($form);
        $layout->setWebsite($website);

        if (!$form->getStepform()) {
            $this->addZone($layout, $website);
        }
    }

    /**
     * Add Zone Layout
     *
     * @param Layout\Layout $layout
     * @param Website $website
     */
    private function addZone(Layout\Layout $layout, Website $website)
    {
        $zone = new Layout\Zone();
        $zone->setFullSize(true);
        $layout->addZone($zone);

        $this->addCol($zone, $website);
    }

    private function addCol(Layout\Zone $zone, Website $website)
    {
        $col = new Layout\Col();
        $zone->addCol($col);

        $this->addBlock($col, $website, "RGPD", 'form-gdpr');
        $this->addBlock($col, $website, "Bouton de soumission", 'form-submit', 'Envoyer');
    }

    /**
     * Add Col Block
     *
     * @param Layout\Col $col
     * @param Website $website
     * @param string $adminName
     * @param string $field
     * @param string|null $label
     */
    private function addBlock(Layout\Col $col, Website $website, string $adminName, string $field, string $label = NULL)
    {
        $block = new Layout\Block();
        $block->setAdminName($adminName);
        $col->addBlock($block);

        $this->addBlockType($block, $field);
        $this->addBlockI18n($block, $website, $field, $label);
        $this->addField($block, $website);
    }

    /**
     * Add Block BlockType
     *
     * @param Layout\Block $block
     * @param string $field
     */
    private function addBlockType(Layout\Block $block, string $field)
    {
        $blockType = $this->entityManager->getRepository(Layout\BlockType::class)->findOneBySlug($field);
        $block->setBlockType($blockType);
    }

    /**
     * Add Block i18n
     *
     * @param Layout\Block $block
     * @param Website $website
     * @param string $field
     * @param string|null $label
     */
    private function addBlockI18n(Layout\Block $block, Website $website, string $field, string $label = NULL)
    {
        $i18n = new i18n();
        $i18n->setLocale($website->getConfiguration()->getLocale());
        $i18n->setWebsite($website);

        if ($field === 'form-submit') {
            $i18n->setTitle($label);
        }

        $block->addI18n($i18n);
    }

    /**
     * Add Block field
     *
     * @param Layout\Block $block
     * @param Website $website
     */
    private function addField(Layout\Block $block, Website $website)
    {
        $configuration = new Layout\FieldConfiguration();
        $configuration->setRequired(true);
        $configuration->setBlock($block);
        $configuration->setExpanded(true);
        $configuration->setMultiple(true);

        $label = "En soumettant ce formulaire, vous acceptez que les informations saisies soient [utilisées, exploitées, traitées] pour permettre de [vous recontacter, pour vous envoyer la newsletter, dans le cadre de la relation commerciale qui découle de cette demande de devis].";

        $valueI18n = new i18n();
        $valueI18n->setLocale($website->getConfiguration()->getLocale());
        $valueI18n->setWebsite($website);
        $valueI18n->setIntroduction($label);
        $valueI18n->setBody(true);

        $value = new Layout\FieldValue();
        $value->setAdminName($label);
        $value->addI18n($valueI18n);

        $configuration->addFieldValue($value);

        $block->setFieldConfiguration($configuration);
    }
}