<?php

namespace App\Controller;

use App\Entity\MediaAttachment;
use App\Entity\Post;
use App\Repository\PostRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\File;

class PostController extends AbstractController
{
    #[Route('/', name: 'site_index')]
    public function siteIndex(EntityManagerInterface $entityManager): Response
    {
        
        return $this->render('index.html.twig', [
            'post' => $entityManager->getRepository(Post::class)->find('018ea1eb-6fb2-7052-baa0-99cb9fc0e36d')
        ]);
    }

    #[Route('/posts', name: 'posts_index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $posts = $entityManager->getRepository(Post::class)->findAll();
        return $this->render('post/index.html.twig', [
            'posts' => $posts
        ]);
    }

    #[Route('/post', name: 'new_post')]
    public function newPost(EntityManagerInterface $entityManager): Response
    {
        $post = new Post();
        $entityManager->persist($post);
        $entityManager->flush();
        return new RedirectResponse($this->generateUrl('draft_post', ['id' => $post->getId()]));
    }

    #[Route('/post/draft/{id}', name: 'draft_post')]
    public function draftPost(Request $request, FileUploader $uploader, EntityManagerInterface $entityManager, string $id): Response
    {
        $post = $entityManager->getRepository(Post::class)->find($id);

        $form = $this->createFormBuilder($post)
            ->add('text', TextareaType::class)
            ->add('attachments', FileType::class, [
                'label' => 'Attachment (Images Only)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/png',
                            'image/jpeg',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image file (jpg/png/gif/webp)'
                    ])
                ]
            ])
            ->add('save', SubmitType::class, ['label'=>'Post'])
            ->getForm();

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('attachments')->getData();
            if($imageFile) {
                $attachment = new MediaAttachment();
                $imageFilename = $uploader->upload($imageFile, date('Y') . '/' . date('m'), $attachment);
                $attachment->setTitle(pathinfo($imageFilename, PATHINFO_FILENAME));
                $attachment->setPost($post);
                $entityManager->persist($attachment);
            }

            $post->setText($form->get('text')->getData());
            $entityManager->persist($post);
            $entityManager->flush();
            return $this->redirectToRoute('posts_index');
        }

        return $this->render('post/draft.html.twig', [
            'form' => $form,
            'post' => $post
        ]);
    }

    #[Route('/post/{id}', name: 'view_post')]
    public function viewPost(EntityManagerInterface $entityManager, string $id): Response
    {
        $post = $entityManager->getRepository(Post::class)->find($id);
        return $this->render('post/single.html.twig', [
            'post' => $post
        ]);
    }

    #[Route('/post/{id}/delete', name: 'delete_post')]
    public function delete(EntityManagerInterface $entityManager, string $id): Response
    {
        $post = $entityManager->getRepository(Post::class)->find($id);
        if($post) {
            $entityManager->remove($post);
            $entityManager->flush();
        }
        return $this->redirectToRoute('posts_index');
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
