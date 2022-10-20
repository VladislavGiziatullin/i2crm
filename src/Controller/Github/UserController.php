<?php

namespace App\Controller\Github;

use App\Entity\Github\User as GithubUser;
use App\Entity\User;
use App\Form\Github\UserType;
use App\Repository\Github\UserRepository;
use Github\AuthMethod;
use Github\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/github/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_github_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('github/user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_github_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserRepository $userRepository, Client $githubClient): Response
    {
        /** @var User $sessionUser */
        $sessionUser = $this->getUser();
        $githubClient->authenticate($sessionUser->getGithubAccessToken(), AuthMethod::JWT);

        $user = new GithubUser();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->save($user, true);

            return $this->redirectToRoute('app_github_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('github/user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_github_user_delete', methods: ['POST'])]
    public function delete(Request $request, GithubUser $user, UserRepository $userRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $userRepository->remove($user, true);
        }

        return $this->redirectToRoute('app_github_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
