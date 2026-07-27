<?php

declare(strict_types = 1);

namespace Drupal\mm_loyalty\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;

/**
 * Manages loyalty point transaction records.
 */
class TransactionManager {

  private EntityTypeManagerInterface $entityTypeManager;
  private LoggerChannelInterface $logger;

  public function __construct(EntityTypeManagerInterface $entityTypeManager, LoggerChannelInterface $logger) {
    $this->entityTypeManager = $entityTypeManager;
    $this->logger = $logger;
  }

  public function recordTransaction(int $uid, int $points, string $operationType, array $data = []): bool {
    $transaction = [
      'uid' => $uid,
      'points' => $points,
      'operation_type' => $operationType,
      'entity_type' => $data['entity_type'] ?? '',
      'entity_id' => (string) ($data['entity_id'] ?? ''),
      'order_id' => $data['order_id'] !== NULL ? (int) $data['order_id'] : NULL,
      'description' => $data['description'] ?? '',
    ];

    try {
      $this->entityTypeManager->getStorage('mm_loyalty_transaction')->create($transaction)->save();
      return TRUE;
    }
    catch (\Throwable $exception) {
      $this->logger->error('Unable to save loyalty transaction: @message', ['@message' => $exception->getMessage()]);
      return FALSE;
    }
  }

  public function hasTransaction(int $uid, string $operationType, string $entityType, string $entityId): bool {
    $storage = $this->entityTypeManager->getStorage('mm_loyalty_transaction');
    $query = $storage->getQuery()
      ->condition('uid', $uid)
      ->condition('operation_type', $operationType)
      ->condition('entity_type', $entityType)
      ->condition('entity_id', $entityId)
      ->range(0, 1);

    return (bool) $query->execute();
  }

  public function getBalance(int $uid): int {
    $storage = $this->entityTypeManager->getStorage('mm_loyalty_transaction');
    $query = $storage->getQuery()->condition('uid', $uid);
    $transactionIds = $query->execute();
    if (empty($transactionIds)) {
      return 0;
    }

    $transactions = $storage->loadMultiple($transactionIds);
    $balance = 0;
    foreach ($transactions as $transaction) {
      $balance += (int) $transaction->points->value;
    }

    return $balance;
  }

  public function loadHistory(int $uid): array {
    $storage = $this->entityTypeManager->getStorage('mm_loyalty_transaction');
    $query = $storage->getQuery()
      ->condition('uid', $uid)
      ->sort('created', 'DESC');
    $ids = $query->execute();
    if (empty($ids)) {
      return [];
    }

    return $storage->loadMultiple($ids);
  }

}
