<?php

namespace App\Form\Manager\Front;

use App\Entity\Module\Forum\Comment;
use App\Entity\Module\Forum\Forum;
use App\Service\Content\RecaptchaService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * ForumManager
 *
 * Manage front Forum Action
 *
 * @property RecaptchaService $recaptcha
 * @property EntityManagerInterface $entityManager
 * @property Request $request
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ForumManager
{
    private $recaptcha;
    private $entityManager;
    private $request;

    /**
     * ForumManager constructor.
     *
     * @param RecaptchaService $recaptcha
     * @param EntityManagerInterface $entityManager
     * @param RequestStack $requestStack
     */
    public function __construct(RecaptchaService $recaptcha, EntityManagerInterface $entityManager, RequestStack $requestStack)
    {
        $this->recaptcha = $recaptcha;
        $this->entityManager = $entityManager;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * Execute request
     *
     * @param Form $form
     * @param Forum $forum
     * @param Comment $comment
     * @return bool
     * @throws Exception
     */
    public function execute(Form $form, Forum $forum, Comment $comment)
    {
        $website = $forum->getWebsite();

        if (!$this->recaptcha->execute($website, $forum, $form)) {
            return false;
        }

        $position = $forum->getComments()->count() + 1;
        $comment->setPosition($position);
        $comment->setLocale($this->request->getLocale());
        $forum->addComment($comment);

        if (!$forum->getModeration()) {
            $comment->setActive(true);
        }

        $this->entityManager->persist($forum);
        $this->entityManager->flush();
        $this->entityManager->refresh($forum);

        return true;
    }
}