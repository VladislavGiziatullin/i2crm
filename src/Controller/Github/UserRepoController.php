<?php

namespace App\Controller\Github;

use App\Entity\Github\UserRepo;
use App\Form\Github\UserRepoType;
use App\Repository\Github\UserRepoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/github/user/repo')]
class UserRepoController extends AbstractController
{
    #[Route('/', name: 'app_github_user_repo_index', methods: ['GET'])]
    public function index(UserRepoRepository $userRepoRepository): Response
    {
        return $this->render('github/user_repo/index.html.twig', [
            'user_repos' => $userRepoRepository->findAll(),
        ]);
    }
}
