<?php

declare(strict_types = 1);

namespace Drupal\mm_loyalty\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\User\AccountProxyInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\mm_loyalty\Service\LoyaltyManager;

/**
 * Displays user loyalty balance and history.
 */
class UserLoyaltyController extends ControllerBase {

  private LoyaltyManager $loyaltyManager;
  private AccountProxyInterface $currentUser;
  private EntityTypeManagerInterface $entityTypeManager;

  public function __construct(LoyaltyManager $loyaltyManager, AccountProxyInterface $currentUser, EntityTypeManagerInterface $entityTypeManager) {
    $this->loyaltyManager = $loyaltyManager;
    $this->currentUser = $currentUser;
    $this->entityTypeManager = $entityTypeManager;
  }

  public static function create($container) {
    return new static(
      $container->get('mm_loyalty.loyalty_manager'),
      $container->get('current_user'),
      $container->get('entity_type.manager')
    );
  }

  public function view($user): array {
    $uid = (int) $user->id();
    if ($uid !== $this->currentUser->id() && !$this->currentUser->hasPermission('view all loyalty history')) {
      throw $this->createAccessDeniedException();
    }

    $balance = $this->loyaltyManager->getBalance($uid);
    $history = $this->loyaltyManager->getHistory($uid);

    $rows = [];
    foreach ($history as $transaction) {
      $rows[] = [
        'created' => date('Y-m-d H:i:s', (int) $transaction->created->value),
        'operation' => $transaction->operation_type->value,
        'points' => $transaction->points->value,
        'description' => $transaction->description->value,
      ];
    }

    return [
      'balance' => [
        '#type' => 'markup',
        '#markup' => $this->t('Current balance: @balance points', ['@balance' => $balance]),
      ],
      'rewards' => [
        '#type' => 'link',
        '#title' => $this->t('View rewards'),
        '#url' => Url::fromRoute('mm_loyalty.rewards'),
      ],
      'history' => [
        '#type' => 'table',
        '#header' => [
          $this->t('Created'),
          $this->t('Operation'),
          $this->t('Points'),
          $this->t('Description'),
        ],
        '#rows' => array_map(static fn ($row) => array_values($row), $rows),
        '#empty' => $this->t('No loyalty history available.'),
      ],
    ];
  }

}
