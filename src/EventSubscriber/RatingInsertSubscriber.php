<?php

declare(strict_types = 1);

namespace Drupal\mm_loyalty\EventSubscriber;

use Drupal\rating\Event\VoteEvent;
use Drupal\rating\RatingEvents;
use Drupal\mm_loyalty\Service\LoyaltyManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribes to rating create events.
 */
class RatingInsertSubscriber implements EventSubscriberInterface {

  private LoyaltyManager $loyaltyManager;
  private LoggerInterface $logger;

  public function __construct(LoyaltyManager $loyaltyManager, LoggerInterface $logger) {
    $this->loyaltyManager = $loyaltyManager;
    $this->logger = $logger;
  }

  public static function getSubscribedEvents(): array {
    return [
      RatingEvents::VOTE_INSERT => 'onVoteInsert',
    ];
  }

  public function onVoteInsert(VoteEvent $event): void {
    $vote = $event->getVote();
    $uid = (int) $vote->getOwnerId();
    if ($uid <= 0) {
      return;
    }

    $entityType = $vote->getEntity()->getEntityTypeId();
    $entityId = (string) $vote->getEntity()->id();
    if (!$this->loyaltyManager->addRatingPoints($uid, $entityType, $entityId)) {
      $this->logger->debug('No loyalty points awarded for rating @vote', ['@vote' => $vote->id()]);
    }
  }

}
