<?php

namespace App\Controller;

use App\Entity\MediaAttachment;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MediaAttachmentController extends AbstractController
{
    #[Route('/media', name: 'media_index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $attachments = $entityManager->getRepository(MediaAttachment::class)->findAll();
        return $this->render('media_attachment/index.html.twig', [
            'attachments' => $attachments
        ]);
    }

    #[Route('/media/{id}', name: 'view_media')]
    public function view(EntityManagerInterface $entityManager, string $id): Response
    {
        $attachment = $entityManager->getRepository(MediaAttachment::class)->find($id);
        return $this->render('media_attachment/single.html.twig', [
            'attachment' => $attachment
        ]);
    }

    #[Route('/media/{id}/delete', name: 'delete_media')]
    public function delete(EntityManagerInterface $entityManager, FileUploader $uploader, string $id): Response
    {
        $attachment = $entityManager->getRepository(MediaAttachment::class)->find($id);
        if($attachment) {
            // $filePath = str_replace('/attachments', $attachment->getPath(), $uploader->getTargetDirectory());
            // if(file_exists($filePath)) {
            //     unlink($filePath);
                $entityManager->remove($attachment);
                $entityManager->flush();
            // }
        }
        return $this->redirectToRoute('media_index');
    }
}
