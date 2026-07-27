<?php

declare(strict_types = 1);

namespace Drupal\mm_loyalty\EventSubscriber;

use Drupal\Commerce\Order\Event\OrderEvent;
use Drupal\Commerce\Order\OrderEvents;
use Drupal\mm_loyalty\Service\LoyaltyManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribes to Commerce order completed events.
 */
class OrderCompletedSubscriber implements EventSubscriberInterface {

  private LoyaltyManager $loyaltyManager;
  private LoggerInterface $logger;

  public function __construct(LoyaltyManager $loyaltyManager, LoggerInterface $logger) {
    $this->loyaltyManager = $loyaltyManager;
    $this->logger = $logger;
  }

  public static function getSubscribedEvents(): array {
    return [
      OrderEvents::ORDER_POST_SAVE => 'onOrderPostSave',
    ];
  }

  public function onOrderPostSave(OrderEvent $event): void {
    $order = $event->getOrder();
    if (!$order->isCompleted()) {
      return;
    }

    $uid = (int) $order->getCustomerId();
    if ($uid <= 0) {
      return;
    }

    $amount = (float) $order->getTotalPrice()->getNumber();
    if (!$this->loyaltyManager->addPurchasePoints($uid, $amount, (int) $order->id())) {
      $this->logger->debug('No loyalty points awarded for order @order.', ['@order' => $order->id()]);
    }
  }

}
