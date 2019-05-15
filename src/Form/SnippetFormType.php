<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

/**
 * Форма для добавления или редактирования сниппета
 */
class SnippetFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('text', \Symfony\Component\Form\Extension\Core\Type\TextareaType::class)
            ->add('isPrivate', \Symfony\Component\Form\Extension\Core\Type\CheckboxType::class, ['required' => false])
            ->add('save', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class);
    }
}