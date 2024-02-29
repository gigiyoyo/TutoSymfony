<?php

namespace App\Form;

use App\Entity\Recipe;
use function PHPUnit\Framework\isNull;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class RecipeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class)
            ->add('slug', TextType::class, [
                'required' => false
            ])
            ->add('content')
            ->add('duration')
            ->add('save', SubmitType::class)
            ->addEventListener(FormEvents::PRE_SUBMIT, $this->autoSlug(...)) # Collable
            ->addEventListener(FormEvents::POST_SUBMIT, $this->attachTimestamps(...));
        ;
    }

    public function autoSlug(PreSubmitEvent $event): void {
        $data = $event->getData();
        
        if(empty($data['slug'])){ 
            $slugger = new AsciiSlugger();
            $data['slug'] = $slugger->slug($data['title'])->lower();
            $event->setData($data);
        }
    }

    public function attachTimestamps(PostSubmitEvent $event): void {
        $data  = $event->getData();
        // dd($data);
        if (!($data instanceof Recipe)){    # S'assurer qu'on a affaire à une instance de Recipe
            return;
        }

        $data->setUpdatedAt(new \DateTimeImmutable());      # Actualiser automatiquement la date de mise à jour

        if(!$data->getId()){
            $data->setCreatedAt(new \DateTimeImmutable());      # Ajouter la date de création dans le cas d'une nouvelle recette
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recipe::class,
        ]);
    }
}
