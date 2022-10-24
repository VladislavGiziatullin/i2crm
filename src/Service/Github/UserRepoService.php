<?php

namespace App\Service\Github;

use App\Entity\Github\User;
use App\Entity\Github\UserRepo;
use App\Repository\Github\APIRepository;
use App\Repository\Github\UserRepoRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

class UserRepoService
{
    public function __construct(
        private readonly APIRepository $APIRepository, // TODO: need hidden by interface
        private readonly UserRepoRepository $repository, // TODO: need hidden by interface
        private readonly UserService $userService,
     ){}

    /**
     * @return iterable|UserRepo[]
     */
    public function getBy(iterable $criteria = [], ?iterable $orderBy = null, ?int $limit = null): iterable
    {
        return $this->repository->findBy($criteria, $orderBy, $limit);
    }

    // Транзакции в моем случае не нужны т.к. у меня выбираются только те репозитории, где пользователь является владельцем.
    // Если нужно будет подтягивать ещё репозитории где пользователь является участников, то тогда нужно ввести сущность Github\UserToRepoLink, чтобы в случае добавление в список отслеживаемых пользователей владельца репозитория и его участников их можно было бы слинковать. Уровень изоляции тут я не могу выбрать, просто потому что нам не важно кто и сколько раз обновит поле repo_updated_at т.к. если это будет в один момент времени, то там будет одинаковое значение, значит нас не интересует ни одна из аномалий, поэтому тут можно вообще без них обойтись. Но если нам важно будет допустим брать данные из самого последнего запроса, то в таком случае есть 3 варианта. Первый можно использовать LSM деревья и не переживать о транзакциях, т.к. в таком случае механизм уплотнения и слияния решит всё сам. Второй в реляционных БД, тут имхо проще всего UPSERT(INSERT ON CONFLICT DO UPDATE) правда не знаю на сколько такое решение производительно, но тоже не придется думать о транзакциях, да и заморачиваться с апдейтами, нужно будет только удалять репозитории которые больше не существуют у пользователя. Третий в реляционных БД, блокировки, можно использовать SELECT FOR NO KEY UPDATE OF github_user_repo NOWAIT, перехватить исключение и отправить задачу обратно в пул. Ну или если вернемся к тому что нам не важно кто и сколько раз обновил эту строку, то можно использовать SELECT FOR NO KEY UPDATE OF github_user_repo SKIP LOCKED в таком случае мы просто будет скипать строки для апдейта если другой консьюмер их уже захватил для обработки, а если он их захватил то смысла обновлять дважды нету.
    public function updateUserRepos(User $user): void
    {
        $githubUserId = $user->getGithubUserId();
        $oldUserRepos = $this->getBy(['githubUserId' => $githubUserId]);
        $actualUserRepos = [];

        // If data will be so many(exp 1kk repos), we can separate this task to some tasks.
        // This task just get data from github example 1k repos(this need calculate by memory usage)
        // and create another tasks for handle of this part data
        $actualUserReposRaw = $this->APIRepository->getAllUserRepos($user->getUsername(), $user->getAddedByUserId());
        foreach ($actualUserReposRaw as $userRepo) {
            array_walk(
                $oldUserRepos,
                fn(UserRepo $value)
                    => ($value->getGithubRepoId() !== $userRepo->getGithubRepoId())
                        ?: $value->setRepoUpdatedAt($userRepo->getRepoUpdatedAt())
            );

            $actualUserRepos[] = $userRepo;
        }

        $this->repository->saveBulk($this->diff($actualUserRepos, $oldUserRepos));
        $this->repository->removeBulk($this->diff($oldUserRepos, $actualUserRepos));

        $this->userService->save($user->setRepoLastUpdatedAt(new DateTime()));
    }

    // Отмечу. Спорно, можно ли тащить Arraycollection в слой бизнес логики, но сделал для упрощения
    private function diff(iterable $collection1, iterable $collection2): iterable
    {
        $collection1 = new ArrayCollection($collection1);
        $collection2 = new ArrayCollection($collection2);

        return $collection2->isEmpty()
            ? $collection1
            : $collection1->filter(function (UserRepo $oV, $oK) use ($collection2) {
                return !$collection2->exists(fn($nK, UserRepo $nV): bool => $oV->getGithubRepoId() === $nV->getGithubRepoId());
            });
    }
}
