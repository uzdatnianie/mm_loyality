<?php

declare(strict_types = 1);

namespace Drupal\mm_loyalty\EventSubscriber;

use Drupal\comment\CommentEvents;
use Drupal\comment\Event\CommentCreateEvent;
use Drupal\mm_loyalty\Service\LoyaltyManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribes to comment creation events.
 */
class CommentInsertSubscriber implements EventSubscriberInterface {

  private LoyaltyManager $loyaltyManager;
  private LoggerInterface $logger;

  public function __construct(LoyaltyManager $loyaltyManager, LoggerInterface $logger) {
    $this->loyaltyManager = $loyaltyManager;
    $this->logger = $logger;
  }

  public static function getSubscribedEvents(): array {
    return [
      CommentEvents::COMMENT_CREATE => 'onCommentCreate',
    ];
  }

  public function onCommentCreate(CommentCreateEvent $event): void {
    $comment = $event->getComment();
    $uid = (int) $comment->getOwnerId();
    if ($uid <= 0) {
      return;
    }

    $entityType = $comment->getCommentedEntity()->getEntityTypeId();
    $entityId = (string) $comment->getCommentedEntity()->id();
    if (!$this->loyaltyManager->addCommentPoints($uid, $entityType, $entityId)) {
      $this->logger->debug('No loyalty points for comment @comment', ['@comment' => $comment->id()]);
    }
  }

}
