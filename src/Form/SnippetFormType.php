<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class SnippetFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('text', \Symfony\Component\Form\Extension\Core\Type\TextareaType::class)
            ->add('private', \Symfony\Component\Form\Extension\Core\Type\CheckboxType::class, ["mapped" => false, 'required' => false])
            ->add('save', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class);
    }
}