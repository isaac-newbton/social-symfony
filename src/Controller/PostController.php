<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PostController extends AbstractController
{
    #[Route('/post', name: 'new_post')]
    public function newPost(EntityManagerInterface $entityManager): Response
    {
        $post = new Post();
        $entityManager->persist($post);
        $entityManager->flush();
        return new RedirectResponse($this->generateUrl('draft_post', ['id' => $post->getId()]));
    }

    #[Route('/post/draft/{id}', name: 'draft_post')]
    public function draftPost(EntityManagerInterface $entityManager, string $id): Response
    {
        $post = $entityManager->getRepository(Post::class)->find($id);

        $form = $this->createFormBuilder($post)
            ->add('text', TextareaType::class)
            ->add('save', SubmitType::class, ['label'=>'Post'])
            ->getForm();

        return $this->render('post/draft.html.twig', [
            'form' => $form,
            'post' => $post
        ]);
    }

    #[Route('/post/{id}', name: 'view_post')]
    public function viewPost(EntityManagerInterface $entityManager, string $id): Response
    {
        $post = $entityManager->getRepository(Post::class)->find($id);
        return $this->render('post/index.html.twig', [
            'post' => $post
        ]);
    }

    #[Route('/posts/cleanup', name: 'cleanup_posts')]
    public function cleanupPosts(EntityManagerInterface $entityManager, PostRepository $postRepository): Response
    {
        $num = 0; $total = 0;
        $posts = $postRepository->findByStatus(0);
        foreach($posts as $post) {
            $total++;
            if($post->shouldExpire()) {
                $num++;
                $entityManager->remove($post);
            }
        }
        if(0 < $num) {
            $entityManager->flush();
        }
        return new Response('Deleted ' . "$num/$total" . ' old draft(s)');
    }
}
