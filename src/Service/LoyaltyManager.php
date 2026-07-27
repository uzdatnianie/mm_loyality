<?php

declare(strict_types = 1);

namespace Drupal\mm_loyalty\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\StringTranslation\StringTranslationInterface;
use Drupal\Core\User\AccountProxyInterface;

/**
 * Provides loyalty point operations.
 */
class LoyaltyManager {

  private EntityTypeManagerInterface $entityTypeManager;
  private AccountProxyInterface $currentUser;
  private ConfigFactoryInterface $configFactory;
  private LoggerChannelInterface $logger;
  private TransactionManager $transactionManager;
  private PointsCalculator $pointsCalculator;
  private StringTranslationInterface $stringTranslation;

  public const OPERATION_PURCHASE = 'purchase';
  public const OPERATION_COMMENT = 'comment';
  public const OPERATION_RATING = 'rating';
  public const OPERATION_REWARD = 'reward';
  public const OPERATION_ADMIN = 'admin';

  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    AccountProxyInterface $currentUser,
    ConfigFactoryInterface $configFactory,
    LoggerChannelInterface $logger,
    TransactionManager $transactionManager,
    PointsCalculator $pointsCalculator,
    StringTranslationInterface $stringTranslation
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->currentUser = $currentUser;
    $this->configFactory = $configFactory;
    $this->logger = $logger;
    $this->transactionManager = $transactionManager;
    $this->pointsCalculator = $pointsCalculator;
    $this->stringTranslation = $stringTranslation;
  }

  public function getBalance(int $uid): int {
    return $this->transactionManager->getBalance($uid);
  }

  public function getHistory(int $uid): array {
    return $this->transactionManager->loadHistory($uid);
  }

  public function addPurchasePoints(int $uid, float $amount, int $orderId): bool {
    if (!$this->pointsCalculator->getPurchaseEnabled()) {
      return FALSE;
    }

    $points = $this->pointsCalculator->calculatePurchasePoints($amount);
    if ($points <= 0) {
      return FALSE;
    }

    return $this->transactionManager->recordTransaction($uid, $points, self::OPERATION_PURCHASE, [
      'order_id' => $orderId,
      'description' => $this->stringTranslation->translate('Purchase #%order', ['%order' => $orderId]),
    ]);
  }

  public function addCommentPoints(int $uid, string $entityType, string $entityId): bool {
    if (!$this->pointsCalculator->getCommentEnabled()) {
      return FALSE;
    }

    if ($this->transactionManager->hasTransaction($uid, self::OPERATION_COMMENT, $entityType, $entityId)) {
      return FALSE;
    }

    $points = $this->pointsCalculator->getCommentPoints();
    if ($points <= 0) {
      return FALSE;
    }

    return $this->transactionManager->recordTransaction($uid, $points, self::OPERATION_COMMENT, [
      'entity_type' => $entityType,
      'entity_id' => $entityId,
      'description' => $this->stringTranslation->translate('Product comment'),
    ]);
  }

  public function addRatingPoints(int $uid, string $entityType, string $entityId): bool {
    if (!$this->pointsCalculator->getRatingEnabled()) {
      return FALSE;
    }

    if ($this->transactionManager->hasTransaction($uid, self::OPERATION_RATING, $entityType, $entityId)) {
      return FALSE;
    }

    $points = $this->pointsCalculator->getRatingPoints();
    if ($points <= 0) {
      return FALSE;
    }

    return $this->transactionManager->recordTransaction($uid, $points, self::OPERATION_RATING, [
      'entity_type' => $entityType,
      'entity_id' => $entityId,
      'description' => $this->stringTranslation->translate('Product rating'),
    ]);
  }

  public function adjustPoints(int $uid, int $points, string $description = ''): bool {
    $operation = $points >= 0 ? self::OPERATION_ADMIN : self::OPERATION_ADMIN;
    return $this->transactionManager->recordTransaction($uid, $points, $operation, [
      'description' => $this->stringTranslation->translate($description),
    ]);
  }

  public function hasEnoughPoints(int $uid, int $cost): bool {
    return $this->getBalance($uid) >= $cost;
  }

}
