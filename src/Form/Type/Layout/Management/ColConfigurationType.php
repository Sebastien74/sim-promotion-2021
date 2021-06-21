<?php

namespace App\Form\Type\Layout\Management;

use App\Entity\Core\Website;
use App\Entity\Layout\Col;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ColConfigurationType
 *
 * @property TranslatorInterface $translator
 * @property bool $isInternalUser
 * @property Website $website
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ColConfigurationType extends AbstractType
{
	private $translator;
	private $isInternalUser;
	private $website;

	/**
	 * ColConfigurationType constructor.
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
		$this->website = $options['website'];

		$margins = new MarginType($this->translator);
		$margins->add($builder);

		$builder->add('fullSize', Type\CheckboxType::class, [
			'required' => false,
			'display' => 'button',
			'color' => 'outline-dark',
			'label' => $this->translator->trans('Toute la largeur', [], 'admin'),
			'attr' => ['group' => 'col-12', 'class' => 'w-100']
		]);

		$builder->add('verticalAlign', Type\CheckboxType::class, [
			'required' => false,
			'display' => 'button',
			'color' => 'outline-dark',
			'label' => $this->translator->trans('Centrer verticalement le contenu colonne', [], 'admin'),
			'attr' => ['group' => 'col-12', 'class' => 'w-100']
		]);

		$builder->add('endAlign', Type\CheckboxType::class, [
			'required' => false,
			'display' => 'button',
			'color' => 'outline-dark',
			'label' => $this->translator->trans('Aligner en bas de la colonne', [], 'admin'),
			'attr' => ['group' => 'col-12', 'class' => 'w-100']
		]);

		$builder->add('reverse', Type\CheckboxType::class, [
			'required' => false,
			'display' => 'button',
			'color' => 'outline-dark',
			'label' => $this->translator->trans('Afficher la colonne en première position sur mobile', [], 'admin'),
			'attr' => ['group' => 'col-12', 'class' => 'w-100']
		]);

		$builder->add('hide', Type\CheckboxType::class, [
			'required' => false,
			'display' => 'button',
			'color' => 'outline-dark',
			'label' => $this->translator->trans('Cacher la colonne', [], 'admin'),
			'attr' => ['group' => 'col-12', 'class' => 'w-100']
		]);

		$choices = [];
		$choices[$this->translator->trans('Par défault', [], 'admin')] = NULL;
		for ($x = 1; $x <= $builder->getData()->getZone()->getCols()->count(); $x++) {
			$choices[$x] = $x;
		}

		$builder->add('mobilePosition', Type\ChoiceType::class, [
			'required' => false,
			'label' => $this->translator->trans('Ordre sur mobile', [], 'admin'),
			'display' => 'search',
			'choices' => $choices,
			'attr' => ['group' => 'col-md-6']
		]);

		$builder->add('tabletPosition', Type\ChoiceType::class, [
			'required' => false,
			'label' => $this->translator->trans('Ordre sur tablette', [], 'admin'),
			'display' => 'search',
			'choices' => $choices,
			'attr' => ['group' => 'col-md-6']
		]);

		$builder->add('alignment', AlignmentType::class);

		$builder->add('elementsAlignment', Type\ChoiceType::class, [
			'required' => false,
			'label' => $this->translator->trans('Alignement des blocs', [], 'admin'),
			'placeholder' => $this->translator->trans('Sélectionnez', [], 'admin'),
			'display' => 'search',
			'choices' => [
				$this->translator->trans('À gauche', [], 'admin') => 'd-flex justify-content-start',
				$this->translator->trans('Centré', [], 'admin') => 'd-flex justify-content-center',
				$this->translator->trans('À droite', [], 'admin') => 'd-flex justify-content-end',
			]
		]);

		$transitions = new TransitionType($this->translator);
		$transitions->add($builder, ['website' => $this->website]);

		$builder->add('blocks', Type\CollectionType::class, [
			'entry_type' => ColBlocksType::class,
			'entry_options' => [
				'col' => $builder->getData(),
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
	 * Get padding sizes
	 *
	 * @param string $position
	 * @return array
	 */
	private function getSizes(string $position)
	{
		return [
			$this->translator->trans('Aucune', [], 'admin') => 'p' . $position . '-0',
			'S' => 'col-p' . $position . '-sm',
			'M' => 'col-p' . $position . '-md',
			'L' => 'col-p' . $position . '-lg',
			'XL' => 'col-p' . $position . '-xl',
			'XXL' => 'col-p' . $position . '-xxl'
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' => Col::class,
			'website' => NULL,
			'translation_domain' => 'admin'
		]);
	}
}