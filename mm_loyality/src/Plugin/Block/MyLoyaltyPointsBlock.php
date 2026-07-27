<?php

declare(strict_types = 1);

namespace Drupal\mm_loyalty\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\User\AccountProxyInterface;
use Drupal\mm_loyalty\Service\LoyaltyManager;

/**
 * Provides a block that shows loyalty points.
 *
 * @Block(
 *   id = "mm_loyalty_points_block",
 *   admin_label = @Translation("My Loyalty Points"),
 * )
 */
class MyLoyaltyPointsBlock extends BlockBase {

  private LoyaltyManager $loyaltyManager;
  private AccountProxyInterface $currentUser;
  private EntityTypeManagerInterface $entityTypeManager;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, LoyaltyManager $loyaltyManager, AccountProxyInterface $currentUser, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->loyaltyManager = $loyaltyManager;
    $this->currentUser = $currentUser;
    $this->entityTypeManager = $entityTypeManager;
  }

  public static function create($container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('mm_loyalty.loyalty_manager'),
      $container->get('current_user'),
      $container->get('entity_type.manager')
    );
  }

  public function build(): array {
    $uid = (int) $this->currentUser->id();
    if ($uid <= 0) {
      return ['#markup' => $this->t('Please log in to see your loyalty points.')];
    }

    $balance = $this->loyaltyManager->getBalance($uid);
    return [
      '#theme' => 'item_list',
      '#items' => [
        $this->t('Points: @balance', ['@balance' => $balance]),
        $this->t('<a href="/user/@uid/loyalty">View history</a>', ['@uid' => $uid]),
      ],
    ];
  }

}
