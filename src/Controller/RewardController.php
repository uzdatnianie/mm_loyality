<?php

declare(strict_types = 1);

namespace Drupal\mm_loyalty\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\User\AccountProxyInterface;
use Drupal\mm_loyalty\Service\RewardManager;

/**
 * Controller for public reward listing.
 */
class RewardController extends ControllerBase {

  private RewardManager $rewardManager;
  private AccountProxyInterface $currentUser;
  private EntityTypeManagerInterface $entityTypeManager;

  public function __construct(RewardManager $rewardManager, AccountProxyInterface $currentUser, EntityTypeManagerInterface $entityTypeManager) {
    $this->rewardManager = $rewardManager;
    $this->currentUser = $currentUser;
    $this->entityTypeManager = $entityTypeManager;
  }

  public static function create($container) {
    return new static(
      $container->get('mm_loyalty.reward_manager'),
      $container->get('current_user'),
      $container->get('entity_type.manager')
    );
  }

  public function rewards(): array {
    $rewards = $this->rewardManager->loadActiveRewards();
    $rows = [];
    foreach ($rewards as $reward) {
      $rows[] = [
        'data' => [
          $reward->label->value,
          $reward->description->value,
          $reward->cost->value,
          Link::fromTextAndUrl($this->t('Exchange points'), Url::fromRoute('mm_loyalty.reward_exchange', ['reward' => $reward->id()])),
        ],
      ];
    }

    return [
      '#type' => 'table',
      '#header' => [
        $this->t('Reward'),
        $this->t('Description'),
        $this->t('Points cost'),
        $this->t('Action'),
      ],
      '#rows' => $rows,
      '#empty' => $this->t('No rewards available.'),
    ];
  }

}
