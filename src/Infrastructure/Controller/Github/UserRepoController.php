<?php

namespace App\Infrastructure\Controller\Github;

use App\Service\Github\UserRepoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/github/user/repo')]
class UserRepoController extends AbstractController
{
    #[Route('/', name: 'app_github_user_repo_index', methods: ['GET'])]
    public function index(UserRepoService $userRepoService, int $limit = 10): Response
    {
        return $this->render('github/user_repo/index.html.twig', [
            'user_repos' => $userRepoService->getBy(orderBy: ['repoUpdatedAt' => 'desc'], limit: $limit),
        ]);
    }
}
