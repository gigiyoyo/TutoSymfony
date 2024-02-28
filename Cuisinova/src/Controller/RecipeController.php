<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Form\RecipeType;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use function PHPUnit\Framework\returnSelf;

class RecipeController extends AbstractController
{
    #[Route('/recipes', name: 'recipe_index')]
    public function index(RecipeRepository $recipeRepository): Response
    {
        $recipes = $recipeRepository->findAll();
        $total = $recipeRepository->findTotalDuration();

        // Récupérer les recettes dont la durée est inférieure à une valeur données
        // $recipes = $recipeRepository->findWithDurationLowerThan(10);

        return $this->render('recipe/index.html.twig', [
            'recipes' => $recipes,
            'total' => $total,
        ]);
    }

    #[Route('/recipes/{slug}-{id}', name: 'recipe_show', requirements:['id'=>'\d+', 'slug'=>'[^/]+'])]
    public function show(Request $request, RecipeRepository $recipeRepository, string $slug, int $id): Response
    {
        $recipe = $recipeRepository->find($id);
        if ($slug !== $recipe->getSlug()) {
            return $this->redirectToRoute('recipe_show', ['slug' => $recipe->getSlug(), 'id' => $recipe->getId()]);
        }
        return $this->render('recipe/show.html.twig', [
            'recipe' => $recipe
        ]);
    }

    #[Route('/recipes/{id}/edit', name: 'recipe_edit', requirements: ['id'=>'\d+'])]
    public function edit(Recipe $recipe, Request $request, EntityManagerInterface $em) : Response
    {
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $recipe->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();
            $this->addFlash('success', 'La recette a été modifié avec succès !');
            return $this->redirectToRoute('recipe_index');
        }
        return $this->render('recipe/edit.html.twig', [
            'recipe' => $recipe,
            'form' => $form
        ]);
    }

    #[Route('/recipes/create', name: 'recipe_create')]
    public function create(Request $request, EntityManagerInterface $em): Response {
        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $recipe->setCreatedAt(new \DateTimeImmutable());
            $recipe->setUpdatedAt(new \DateTimeImmutable());
            $em->persist($recipe);
            $em->flush();
            $this->addFlash('success', 'La nouvelle recette a été ajouté avec succès !');
            return $this->redirectToRoute('recipe_index');
        }
        return $this->render('recipe/create.html.twig', [
            'recipe' => $recipe,
            'form' => $form
        ]);
    }
}
