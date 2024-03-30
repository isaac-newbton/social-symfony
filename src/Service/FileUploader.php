<?php

namespace App\Service;

use App\Entity\MediaAttachment;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    public function __construct(
        private string $targetDirectory,
        private SluggerInterface $slugger,
    ) {
    }

    public function upload(UploadedFile $file, string $path = '', ?MediaAttachment $attachment): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $filename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();
        $targetPath = $this->getTargetDirectory() . ($path !== '' ? "/$path" : '');
        try {
            $file->move($targetPath, $filename);
            if($attachment) {
                $attachment->setPath('/' . $path . '/' . $filename);
                $attachment->setSystemPath($targetPath . '/' . $filename);
            }
        } catch (FileException $exception) {

        }
        return $filename;
    }

    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }
}