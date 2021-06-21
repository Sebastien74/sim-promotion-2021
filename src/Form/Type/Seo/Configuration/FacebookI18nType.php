<?php

namespace App\Form\Type\Seo\Configuration;

use App\Entity\Api\FacebookI18n;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * FacebookI18nType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FacebookI18nType extends AbstractType
{
	private $translator;

	/**
	 * FacebookI18nType constructor.
	 *
	 * @param TranslatorInterface $translator
	 */
	public function __construct(TranslatorInterface $translator)
	{
		$this->translator = $translator;
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('domainVerification', Type\TextType::class, [
			'required' => false,
			'label' => $this->translator->trans('Domain verification', [], 'admin'),
			'attr' => [
				'placeholder' => $this->translator->trans('Saisissez la clé', [], 'admin'),
				'group' => "col-md-4"
			]
		]);

		$builder->add('phoneTrack', Type\CheckboxType::class, [
			'required' => false,
			'display' => 'button',
			'color' => 'outline-dark',
			'label' => $this->translator->trans('Activer Phone track', [], 'admin'),
			'attr' => ['group' => 'col-md-3 d-flex align-items-end', 'class' => 'w-100']
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' => FacebookI18n::class,
			'website' => NULL,
			'translation_domain' => 'admin'
		]);
	}
}