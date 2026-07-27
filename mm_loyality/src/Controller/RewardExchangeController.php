<?php

declare(strict_types = 1);

namespace Drupal\mm_loyalty\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\User\AccountProxyInterface;
use Drupal\Core\Url;
use Drupal\mm_loyalty\Service\LoyaltyManager;
use Drupal\mm_loyalty\Service\RewardManager;

/**
 * Controller for reward redemption actions.
 */
class RewardExchangeController extends ControllerBase {

  private RewardManager $rewardManager;
  private LoyaltyManager $loyaltyManager;
  private AccountProxyInterface $currentUser;
  private EntityTypeManagerInterface $entityTypeManager;

  public function __construct(RewardManager $rewardManager, LoyaltyManager $loyaltyManager, AccountProxyInterface $currentUser, EntityTypeManagerInterface $entityTypeManager) {
    $this->rewardManager = $rewardManager;
    $this->loyaltyManager = $loyaltyManager;
    $this->currentUser = $currentUser;
    $this->entityTypeManager = $entityTypeManager;
  }

  public static function create($container) {
    return new static(
      $container->get('mm_loyalty.reward_manager'),
      $container->get('mm_loyalty.loyalty_manager'),
      $container->get('current_user'),
      $container->get('entity_type.manager')
    );
  }

  public function exchange($reward): array {
    $uid = (int) $this->currentUser->id();
    if ($uid <= 0) {
      throw $this->createAccessDeniedException();
    }

    $rewardId = (int) $reward->id();
    if ($this->rewardManager->redeemReward($uid, $rewardId)) {
      $this->messenger()->addStatus($this->t('Reward redeemed successfully.'));
    }
    else {
      $this->messenger()->addError($this->t('Unable to redeem reward.'));
    }

    return $this->redirect('mm_loyalty.rewards')->getResponse();
  }

}
