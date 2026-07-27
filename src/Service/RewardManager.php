<?php

declare(strict_types = 1);

namespace Drupal\mm_loyalty\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\User\AccountProxyInterface;
use Drupal\mm_loyalty\Entity\RewardRedemption;

/**
 * Handles reward listings and redemption logic.
 */
class RewardManager {

  private EntityTypeManagerInterface $entityTypeManager;
  private AccountProxyInterface $currentUser;
  private LoyaltyManager $loyaltyManager;
  private LoggerChannelInterface $logger;

  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    AccountProxyInterface $currentUser,
    LoyaltyManager $loyaltyManager,
    LoggerChannelInterface $logger
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->currentUser = $currentUser;
    $this->loyaltyManager = $loyaltyManager;
    $this->logger = $logger;
  }

  public function loadActiveRewards(): array {
    $storage = $this->entityTypeManager->getStorage('reward');
    $ids = $storage->getQuery()
      ->condition('status', TRUE)
      ->sort('weight', 'ASC')
      ->execute();

    return empty($ids) ? [] : $storage->loadMultiple($ids);
  }

  public function redeemReward(int $uid, int $rewardId): bool {
    $reward = $this->entityTypeManager->getStorage('reward')->load($rewardId);
    if (!$reward || !$reward->status->value) {
      return FALSE;
    }

    $cost = (int) $reward->cost->value;
    if (!$this->loyaltyManager->hasEnoughPoints($uid, $cost)) {
      return FALSE;
    }

    $result = $this->loyaltyManager->adjustPoints($uid, -$cost, $this->t('Reward redemption: @reward', ['@reward' => $reward->label->value]));
    if (!$result) {
      return FALSE;
    }

    $redemption = RewardRedemption::create([
      'uid' => $uid,
      'reward_id' => $rewardId,
      'status' => 'new',
      'description' => $this->t('Redeemed reward @reward', ['@reward' => $reward->label->value]),
      'created' => time(),
      'changed' => time(),
    ]);
    $redemption->save();

    return TRUE;
  }

  public function loadRedemptionHistory(): array {
    $storage = $this->entityTypeManager->getStorage('mm_loyalty_reward_redemption');
    $ids = $storage->getQuery()->sort('created', 'DESC')->execute();
    return empty($ids) ? [] : $storage->loadMultiple($ids);
  }

}
