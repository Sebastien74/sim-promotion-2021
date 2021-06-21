<?php

namespace App\Form\Type\Module\Newscast;

use App\Entity\Module\Newscast\Category;
use App\Entity\Module\Newscast\Newscast;
use App\Entity\Core\Website;
use App\Form\Widget as WidgetType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * NewscastType
 *
 * @property TranslatorInterface $translator
 * @property bool $isLayoutUser
 * @property EntityManagerInterface $entityManager
 * @property Website $website
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class NewscastType extends AbstractType
{
	private $translator;
	private $isLayoutUser;
	private $entityManager;
	private $website;

	/**
	 * NewscastType constructor.
	 *
	 * @param TranslatorInterface $translator
	 * @param AuthorizationCheckerInterface $authorizationChecker
	 * @param EntityManagerInterface $entityManager
	 */
	public function __construct(TranslatorInterface $translator, AuthorizationCheckerInterface $authorizationChecker, EntityManagerInterface $entityManager)
	{
		$this->translator = $translator;
		$this->isLayoutUser = $authorizationChecker->isGranted('ROLE_LAYOUT_NEWSCAST');
		$this->entityManager = $entityManager;
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		/** @var Newscast $data */
		$data = $builder->getData();
		$isNew = !$data->getId();
		$this->website = $options['website'];
		$displayCategory = count($this->entityManager->getRepository(Category::class)->findBy(['website' => $this->website])) > 1 || !$data->getCategory();

		$adminNameClass = $isNew ? 'col-md-9' : 'col-md-4';
		if (!$displayCategory) {
			$adminNameClass = $isNew ? 'col-12' : 'col-md-7';
		}

		$adminName = new WidgetType\AdminNameType($this->translator);
		$adminName->add($builder, [
			'adminNameGroup' => $adminNameClass,
			'class' => 'refer-code'
		]);

		$builder->add('category', EntityType::class, [
			'label' => $this->translator->trans('Catégorie', [], 'admin'),
			'placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
			'display' => 'search',
			'attr' => [
				'data-placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
				'group' => $displayCategory ? "col-md-3" : 'd-none',
			],
			'class' => Category::class,
			'query_builder' => function (EntityRepository $er) {
				return $er->createQueryBuilder('c')
					->orderBy('c.adminName', 'ASC');
			},
			'choice_label' => function ($entity) {
				return strip_tags($entity->getAdminName());
			},
			'constraints' => [new Assert\NotBlank()]
		]);

		if ($isNew && $this->isLayoutUser) {
			$builder->add('customLayout', Type\CheckboxType::class, [
				'label' => $this->translator->trans("Template personnalisé ?", [], 'admin'),
				'required' => false,
				'attr' => ['group' => "col-12 text-center"]
			]);
		}

		if (!$isNew) {

			$builder->add('author', Type\TextType::class, [
				'label' => $this->translator->trans("Auteur", [], 'admin'),
				'required' => false,
				'attr' => [
					'group' => "col-md-3",
					'placeholder' => $this->translator->trans("Saisissez un auteur", [], 'admin')
				]
			]);

			$builder->add('promote', Type\CheckboxType::class, [
				'required' => false,
				'display' => 'button',
				'color' => 'outline-dark',
				'label' => $this->translator->trans('Mettre en avant', [], 'admin'),
				'attr' => ['group' => 'col-md-2 d-flex align-items-end', 'class' => 'w-100']
			]);

			if (!$data->getCustomLayout()) {
				$i18ns = new WidgetType\i18nsCollectionType($this->translator);
				$i18ns->add($builder, [
					'website' => $options['website'],
					'fields' => [
						'alignment' => 'col-md-3',
						'targetAlignment' => 'col-md-3',
						'title' => 'col-md-6',
						'subTitle' => 'col-md-4',
						'introduction',
						'body',
						'video',
						'targetLink' => 'col-md-12 add-title',
						'targetPage' => 'col-md-4',
						'targetLabel' => 'col-md-4',
						'targetStyle' => 'col-md-4',
						'newTab' => 'col-md-4'
					]
				]);
			}

			$urls = new WidgetType\UrlsCollectionType($this->translator);
			$urls->add($builder, ['display_seo' => true]);

			$dates = new WidgetType\PublicationDatesType($this->translator);
			$dates->add($builder);

			if ($this->isLayoutUser) {
				$builder->add('customLayout', Type\CheckboxType::class, [
					'required' => false,
					'display' => 'button',
					'color' => 'outline-dark',
					'label' => $this->translator->trans('Template personnalisé', [], 'admin'),
					'attr' => ['group' => 'col-md-4 d-flex align-items-end', 'class' => 'w-100', 'data-config' => true]
				]);
			}
		}

		$save = new WidgetType\SubmitType($this->translator);
		$save->add($builder, ['btn_both' => true]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' => Newscast::class,
			'website' => NULL,
			'translation_domain' => 'admin'
		]);
	}
}