<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Review;
use App\Form\ReviewType;
use App\Repository\RecipeRepository;

class RecipeController extends AbstractController
{
    #[Route('/recipes', name: 'recipe_list')]
    public function index(RecipeRepository $recipeRepository): Response
    {
        // Fetch all recipes from the database
        $recipes = $recipeRepository->findAll();

        return $this->render('recipe/index.html.twig', [
            'recipes' => $recipes,
        ]);
    }

    #[Route('/recipes/{id}', name: 'recipe_show')]
    public function show(
        int $id,
        RecipeRepository $recipeRepository,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        // Fetch the recipe by its ID
        $recipe = $recipeRepository->find($id);

        if (!$recipe) {
            throw $this->createNotFoundException('The recipe does not exist');
        }

        // Create a new Review entity
        $review = new Review();
        $review->setRecipe($recipe);
        $review->setUser($this->getUser()); // Set the currently logged-in user
        $review->setCreatedAt(new \DateTime()); // Set the current time

        // Create and handle the review form
        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Save the review to the database
            $entityManager->persist($review);
            $entityManager->flush();

            // Add a success flash message
            $this->addFlash('success', 'Your review has been submitted.');

            // Redirect to the same page to avoid form resubmission
            return $this->redirectToRoute('recipe_show', ['id' => $recipe->getId()]);
        }

        // Fetch all reviews for this recipe
        $reviews = $recipe->getReviews();

        return $this->render('recipe/show.html.twig', [
            'recipe' => $recipe,
            'reviews' => $reviews,
            'reviewForm' => $form->createView(),
            'averageRating' => $recipe->calculateAverageRating(), // Calculate average rating dynamically
        ]);
    }

}
