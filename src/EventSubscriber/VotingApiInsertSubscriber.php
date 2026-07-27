<?php

declare(strict_types = 1);

namespace Drupal\mm_loyalty\EventSubscriber;

use Drupal\mm_loyalty\Service\LoyaltyManager;
use Drupal\votingapi\Event\VoteEvent;
use Drupal\votingapi\VotingApiEvents;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribes to Voting API vote insert events.
 */
class VotingApiInsertSubscriber implements EventSubscriberInterface {

  public function __construct(
    private readonly LoyaltyManager $loyaltyManager,
    private readonly LoggerInterface $logger,
  ) {}

  public static function getSubscribedEvents(): array {
    return [
      VotingApiEvents::VOTE_INSERT => 'onVoteInsert',
    ];
  }

  public function onVoteInsert(VoteEvent $event): void {
    $vote = $event->getVote();

    $uid = 0;
    if (method_exists($vote, 'getOwnerId')) {
      $uid = (int) $vote->getOwnerId();
    }
    elseif (method_exists($vote, 'getUserId')) {
      $uid = (int) $vote->getUserId();
    }

    if ($uid <= 0) {
      return;
    }

    $entity = NULL;
    if (method_exists($vote, 'getVotedEntity')) {
      $entity = $vote->getVotedEntity();
    }
    elseif (method_exists($vote, 'getEntity')) {
      $entity = $vote->getEntity();
    }

    if (!$entity || !method_exists($entity, 'getEntityTypeId') || !method_exists($entity, 'id')) {
      return;
    }

    $entityType = $entity->getEntityTypeId();
    $entityId = (string) $entity->id();

    if (!$this->loyaltyManager->addRatingPoints($uid, $entityType, $entityId)) {
      $this->logger->debug('No loyalty points awarded for voting @vote', ['@vote' => $vote->id()]);
    }
  }

}
