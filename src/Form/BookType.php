<?php

namespace App\Form;

use App\Entity\Author;
use App\Entity\Book;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class BookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('Name')
            ->add('year')
            ->add('ISBN')
            ->add('Pages')
            ->add('brochure', FileType::class, array(
				'label' => "Brochure image",
				'data_class' => null,
				'required' => false
				)
			)
			->add('Authors', EntityType::class, array(
				'class' => Author::class,
				'choice_label' => 'SurnameAndInitials',
				'multiple' => true,
				'expanded' => true,
				'by_reference' => false
				)
			)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Book::class,
        ]);
    }
}
