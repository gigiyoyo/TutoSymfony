<?php

namespace App\Form;

use App\Entity\Recipe;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use function PHPUnit\Framework\isNull;

class RecipeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('slug')
            ->add('content')
            ->add('duration')
            ->add('save', SubmitType::class)
            ->addEventListener(FormEvents::PRE_SUBMIT, $this->autoSlug(...)) # Collable
            ->addEventListener(FormEvents::POST_SUBMIT, $this->autoCreatedDate(...))
            ->addEventListener(FormEvents::POST_SUBMIT, $this->autoUpdatedDate(...));
        ;
    }

    public function autoSlug(PreSubmitEvent $event): void {
        $data = $event->getData();
        
        if(trim($data['slug']) === ""){         # or if(empty($data['slug']))
            $slugger = new AsciiSlugger();
            $data['slug'] = $slugger->slug($data['title'])->lower();
            $event->setData($data);
        }
    }

    public function autoCreatedDate(PostSubmitEvent $event): void {
        $data  = $event->getData();
        // dd($data);
        if (isNull($data->getCreatedAt())) {
            $data->setCreatedAt(new \DateTimeImmutable());
        }
        // dd($data);
    }

    public function autoUpdatedDate(PostSubmitEvent $event): void {
        $data  = $event->getData();

        if (isNull($data->getUpdatedAt())) {
            $data->setUpdatedAt(new \DateTimeImmutable());
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recipe::class,
        ]);
    }
}
