<?php
namespace App\Mapper\Entity;
use App\Entity\User;
use App\Model\Response\Entity\User\UserDetail;
use App\Entity\Assessment;

class UserMapper
{
  public function mapToDetail(User $user, UserDetail $model): UserDetail
  {
    return $model
      ->setId($user->getId())
      ->setUsername($user->getUsername())
      ->setEmail($user->getEmail())
      ->setAbout($user->getAbout())
      ->setAge($user->getAge())
      ->setDisplayName($user->getDisplayName())
      ->setCover($user->getCover())
      ->setAvatar($user->getAvatar())
      ->setLastLogin($user->getLastLogin())
      ->setRoles($user->getRoles())
      ;
      // ->setAssessmentsData($user->getAssessments()->toArray());
  }

  private function mapAssessmentsData(array $assessments): array
  {
    return array_map(function (Assessment $assessment) {
      return [
        'id' => $assessment->getId(),
        'comment' => $assessment->getComment(),
        'rating' => $assessment->getRating(),
        'createdAt' => $assessment->getCreatedAt(),
      ];
    } , $assessments);
    
  }
}