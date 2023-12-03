<?php
namespace App\Service;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;

class UserImageService
{
private $entityManager;

public function __construct(EntityManagerInterface $entityManager)
{
$this->entityManager = $entityManager;
}

public function getUserImage(int $userId)
{
$userRepository = $this->entityManager->getRepository(Utilisateur::class);
$user = $userRepository->find($userId);

if ($user) {
return base64_encode(stream_get_contents($user->getPhotoProfil()));
}

return null;
}
}