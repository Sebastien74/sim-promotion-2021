<?php

namespace App\Form\Type\Development;

use App\Service\Development\CommandParser;
use Exception;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CommandChoiceType
 *
 * @property TranslatorInterface $translator
 * @property CommandParser $commandParser
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class CommandChoiceType extends AbstractType
{
    private $translator;
    private $commandParser;

    /**
     * ScheduledCommandType constructor.
     *
     * @param TranslatorInterface $translator
     * @param CommandParser $commandParser
     */
    public function __construct(TranslatorInterface $translator, CommandParser $commandParser)
    {
        $this->translator = $translator;
        $this->commandParser = $commandParser;
    }

    /**
     * @inheritdoc
     *
     * @throws Exception
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => $this->commandParser->getCommands(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}