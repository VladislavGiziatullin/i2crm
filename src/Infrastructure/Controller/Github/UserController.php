<?php

namespace App\Infrastructure\Controller\Github;

use App\Entity\Github\User as GithubUser;
use App\Entity\User;
use App\Infrastructure\Form\Github\UserType;
use App\Service\Github\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/github/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_github_user_index', methods: ['GET'])]
    public function index(UserService $userService): Response
    {
        return $this->render('github/user/index.html.twig', [
            'users' => $userService->getAll(),
        ]);
    }

    #[Route('/new', name: 'app_github_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserService $userService): Response
    {
        $user = new GithubUser();
        /** @var User $sessionUser */
        $sessionUser = $this->getUser();
        $user->setAddedByUserId($sessionUser->getId()); //  Need for App\Validator\Github\UserNameIsExistsValidator

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userService->create($user, $sessionUser);

            return $this->redirectToRoute('app_github_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('github/user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_github_user_delete', methods: ['POST'])]
    public function delete(Request $request, GithubUser $user, UserService $userService): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $userService->remove($user);
        }

        return $this->redirectToRoute('app_github_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
