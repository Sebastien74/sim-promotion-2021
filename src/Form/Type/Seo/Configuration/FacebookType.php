<?php

namespace App\Form\Type\Seo\Configuration;

use App\Entity\Api\Facebook;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * FacebookType
 *
 * @property TranslatorInterface $translator
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class FacebookType extends AbstractType
{
	private $translator;

	/**
	 * FacebookType constructor.
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
		$builder->add('facebookI18ns', CollectionType::class, [
			'label' => false,
			'entry_type' => FacebookI18nType::class
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' => Facebook::class,
			'website' => NULL,
			'translation_domain' => 'admin'
		]);
	}
}