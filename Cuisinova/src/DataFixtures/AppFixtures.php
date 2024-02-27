<?php

namespace App\DataFixtures;

use Faker\Generator;
use Faker\Factory;
use App\Entity\Recipe;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\AsciiSlugger;

class AppFixtures extends Fixture
{
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory ::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        for ($i=0; $i < 100; $i++) { 
            $recipe = new Recipe();
            $recipe->setTitle(ucfirst($this->faker->words(2, true)));
            $slugger = new AsciiSlugger();
            $recipe->setSlug($slugger->slug($recipe->getTitle()));
            $recipe->setContent($this->faker->paragraph(2, false));
            $recipe->setCreatedAt(new \DateTimeImmutable());
            $recipe->setUpdatedAt(new \DateTimeImmutable());
            $recipe->setDuration(mt_rand(0,45));

            $manager->persist($recipe);
        }

        $manager->flush();
    }
}
