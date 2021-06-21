<?php

namespace App\Form\Type\Layout\Management;

use App\Entity\Core\Website;
use App\Entity\Layout\Zone;
use App\Form\Widget as WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ZoneConfigurationType
 *
 * @property TranslatorInterface $translator
 * @property bool $isInternalUser
 * @property Website $website
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ZoneConfigurationType extends AbstractType
{
	private $translator;
	private $isInternalUser;
	private $website;

	/**
	 * ZoneConfigurationType constructor.
	 *
	 * @param TranslatorInterface $translator
	 * @param AuthorizationCheckerInterface $authorizationChecker
	 */
	public function __construct(TranslatorInterface $translator, AuthorizationCheckerInterface $authorizationChecker)
	{
		$this->translator = $translator;
		$this->isInternalUser = $authorizationChecker->isGranted('ROLE_INTERNAL');
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		/** @var Zone $zone */
		$zone = $builder->getData();
		$this->website = $options['website'];

		$builder->add('customClass', Type\TextType::class, [
			'required' => false,
			'label' => $this->translator->trans('Classes personnalisées', [], 'admin'),
			'attr' => [
				'group' => 'col-md-8',
				'class' => 'input-css',
				'placeholder' => $this->translator->trans('Éditer', [], 'admin')
			]
		]);

		$builder->add('customId', Type\TextType::class, [
			'required' => false,
			'label' => $this->translator->trans('Id personnalisé', [], 'admin'),
			'attr' => [
				'group' => 'col-md-4',
				'placeholder' => $this->translator->trans('Éditer', [], 'admin')
			]
		]);

		$margins = new MarginType($this->translator);
		$margins->add($builder);

		$transitions = new TransitionType($this->translator);
		$transitions->add($builder, ['website' => $this->website]);

		$builder->add('alignment', AlignmentType::class);

		$builder->add('fullSize', Type\CheckboxType::class, [
			'required' => false,
			'display' => 'button',
			'color' => 'outline-dark',
			'label' => $this->translator->trans("Étendre", [], 'admin'),
			'attr' => ['group' => 'col-12', 'class' => 'w-100']
		]);

		$builder->add('hideMobile', HideMobileType::class, [
			'label' => $this->translator->trans("Cacher la zone sur mobile", [], 'admin')
		]);

		$builder->add('hide', HideType::class, [
			'label' => $this->translator->trans('Cacher la zone', [], 'admin')
		]);

		$centerColLabel = $zone->getCols()->count() === 1
			? $this->translator->trans('Centrer la colonne', [], 'admin')
			: $this->translator->trans('Centrer les colonnes', [], 'admin');
		$builder->add('centerCol', Type\CheckboxType::class, [
			'required' => false,
			'display' => 'button',
			'color' => 'outline-dark',
			'label' => $centerColLabel,
			'attr' => ['group' => 'col-12', 'class' => 'w-100']
		]);

		if($zone->getCols()->count() > 1) {
			$builder->add('centerColsGroup', Type\CheckboxType::class, [
				'required' => false,
				'display' => 'button',
				'color' => 'outline-dark',
				'label' => $this->translator->trans('Centrer le groupe de colonnes', [], 'admin'),
				'attr' => ['group' => 'col-12', 'class' => 'w-100']
			]);
		}

		$mediaRelations = new WidgetType\MediaRelationsCollectionType($this->translator);
		$mediaRelations->add($builder, ['entry_options' => ['onlyMedia' => true, 'dataHeight' => 100]]);

		$builder->add('standardizeMedia', Type\CheckboxType::class, [
			'required' => false,
			'display' => 'button',
			'color' => 'outline-dark',
			'label' => $this->translator->trans('Uniformiser la hauteur des médias', [], 'admin'),
			'attr' => ['group' => 'col-md-4', 'class' => 'w-100']
		]);

		if ($this->isInternalUser) {

			$builder->add('backgroundFixed', Type\CheckboxType::class, [
				'required' => false,
				'display' => 'button',
				'color' => 'outline-dark',
				'label' => $this->translator->trans('Arrière-plan fixe ?', [], 'admin'),
				'attr' => ['group' => 'col-md-4', 'class' => 'w-100']
			]);

			$builder->add('backgroundParallax', Type\CheckboxType::class, [
				'required' => false,
				'display' => 'button',
				'color' => 'outline-dark',
				'label' => $this->translator->trans('Arrière-plan avec effet de parallax ?', [], 'admin'),
				'attr' => ['group' => 'col-md-4', 'class' => 'w-100']
			]);

			$builder->add('titlePosition', Type\ChoiceType::class, [
				'required' => false,
				'display' => 'search',
				'placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
				'choices' => [
					$this->translator->trans("En haut à droite", [], 'admin') => "vertical-top-right",
					$this->translator->trans("Centré à droite", [], 'admin') => "vertical-center-right",
					$this->translator->trans("En en bas à droite", [], 'admin') => "vertical-bottom-right",
					$this->translator->trans("En en haut à gauche", [], 'admin') => "vertical-top-left",
					$this->translator->trans("Centré à gauche", [], 'admin') => "vertical-center-left",
					$this->translator->trans("En en bas à gauche", [], 'admin') => "vertical-bottom-left"
				],
				'label' => $this->translator->trans("Position du titre", [], 'admin'),
				'attr' => ['group' => 'col-md-4']
			]);

			$i18ns = new WidgetType\i18nsCollectionType($this->translator);
			$i18ns->add($builder, [
				'website' => $options['website'],
				'fields' => ['title'],
				'label_fields' => ['title' => $this->translator->trans('Intitulé vertical', [], 'admin')],
				'placeholder_fields' => ['title' => $this->translator->trans('Saisissez un intitulé', [], 'admin')],
				'content_config' => false,
				'fields_data' => ['titleForce' => 2]
			]);
		}

		$builder->add('cols', Type\CollectionType::class, [
			'entry_type' => ZoneColsType::class,
			'entry_options' => [
				'zone' => $builder->getData(),
				'website' => $this->website,
				'attr' => ['class' => 'col-order'],
			],
		]);

		$builder->add('save', Type\SubmitType::class, [
			'label' => $this->translator->trans('Enregistrer', [], 'admin'),
			'attr' => ['class' => 'btn-info edit-element-submit-btn']
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' => Zone::class,
			'website' => NULL,
			'translation_domain' => 'admin'
		]);
	}
}