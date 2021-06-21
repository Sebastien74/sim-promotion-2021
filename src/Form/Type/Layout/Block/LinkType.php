<?php

namespace App\Form\Type\Layout\Block;

use App\Entity\Core\Icon;
use App\Entity\Core\Website;
use App\Entity\Layout\Block;
use App\Form\Widget as WidgetType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * LinkType
 *
 * @property array $icons
 * @property TranslatorInterface $translator
 * @property bool $isInternalUser
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class LinkType extends AbstractType
{
	private $icons;
	private $translator;
	private $isInternalUser;

	/**
	 * LinkType constructor.
	 *
	 * @param TranslatorInterface $translator
	 * @param EntityManagerInterface $entityManager
	 * @param RequestStack $requestStack
	 * @param AuthorizationCheckerInterface $authorizationChecker
	 */
	public function __construct(
		TranslatorInterface $translator,
		EntityManagerInterface $entityManager,
		RequestStack $requestStack,
		AuthorizationCheckerInterface $authorizationChecker)
	{
		$request = $requestStack->getMasterRequest();
		$website = $entityManager->getRepository(Website::class)->find($request->get('website'));

		$this->icons = $entityManager->getRepository(Icon::class)->findBy(['configuration' => $website->getConfiguration()]);
		$this->translator = $translator;
		$this->isInternalUser = $authorizationChecker->isGranted('ROLE_INTERNAL');
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('template', WidgetType\TemplateBlockType::class);

		if (!empty($this->icons)) {

			$builder->add('icon', WidgetType\IconType::class, [
				'required' => false,
				'attr' => ['class' => 'select-icons', 'group' => 'col-md-3'],
			]);

			$builder->add('iconSize', ChoiceType::class, [
				'required' => false,
				'display' => 'search',
				'label' => $this->translator->trans("Taille de l'icône", [], 'admin'),
				'placeholder' => $this->translator->trans("Sélectionnez", [], 'admin'),
				'attr' => ['group' => 'col-md-3'],
				'choices' => ['XS' => 'xs', 'S' => 'sm', 'M' => 'md', 'L' => 'lg', 'XL' => 'xl', 'XXL' => 'xxl']
			]);

			$builder->add('iconPosition', ChoiceType::class, [
				'required' => false,
				'display' => 'search',
				'label' => $this->translator->trans("Position de l'icône", [], 'admin'),
				'placeholder' => $this->translator->trans("Sélectionnez", [], 'admin'),
				'attr' => ['group' => 'col-md-3'],
				'choices' => [
					$this->translator->trans("En haut", [], 'admin') => 'top',
					$this->translator->trans("À droite", [], 'admin') => 'right',
					$this->translator->trans("En bas", [], 'admin') => 'bottom',
					$this->translator->trans("À gauche", [], 'admin') => 'left',
				]
			]);

			$builder->add('color', WidgetType\AppColorType::class, [
				'label' => $this->translator->trans("Couleur de l'icône", [], 'admin'),
				'attr' => ['class' => 'select-icons', 'group' => 'col-md-3']
			]);
		}

		$i18ns = new WidgetType\i18nsCollectionType($this->translator);
		$i18ns->add($builder, [
			'website' => $options['website'],
			'fields' => ['targetLink' => 'col-md-12', 'targetPage' => 'col-md-4', 'targetLabel' => 'col-md-4', 'targetStyle' => 'col-md-4'],
			'groups_fields' => ['newTab' => 'col-md-4']
		]);

		if ($this->isInternalUser) {
			$builder->add('script', Type\TextareaType::class, [
				'required' => false,
				'editor' => false,
				'label' => $this->translator->trans('Script (SEO Tracking)', [], 'admin'),
				'attr' => [
					'placeholder' => $this->translator->trans('Ajouter le script', [], 'admin')
				]
			]);
		}

		$save = new WidgetType\SubmitType($this->translator);
		$save->add($builder, ['btn_back' => true]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' => Block::class,
			'translation_domain' => 'admin',
			'website' => NULL
		]);
	}
}