<?php

namespace App\Form;

use App\Entity\Book;
use App\Entity\Theme;
use App\Entity\Auteur;
use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookType extends AbstractType   
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {    
        $builder
            ->add('nom')
            ->add('prix')
            ->add('quantity')
            ->add('theme',EntityType::class, [
                
                'class' => Theme::class,'choice_label' => 'nom'])
            ->add('auteur',EntityType::class, [
                
                'class' => Auteur::class,'choice_label' => 'nom']);
            
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Book::class,
        ]);
    }
}
