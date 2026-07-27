<?php

declare(strict_types = 1);

namespace Drupal\mm_loyalty\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\User\AccountProxyInterface;
use Drupal\mm_loyalty\Service\LoyaltyManager;

/**
 * Controller for loyalty admin user listing.
 */
class AdminLoyaltyController extends ControllerBase {

  private EntityTypeManagerInterface $entityTypeManager;
  private LoyaltyManager $loyaltyManager;
  private AccountProxyInterface $currentUser;

  public function __construct(EntityTypeManagerInterface $entityTypeManager, LoyaltyManager $loyaltyManager, AccountProxyInterface $currentUser) {
    $this->entityTypeManager = $entityTypeManager;
    $this->loyaltyManager = $loyaltyManager;
    $this->currentUser = $currentUser;
  }

  public static function create($container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('mm_loyalty.loyalty_manager'),
      $container->get('current_user')
    );
  }

  public function users(): array {
    $storage = $this->entityTypeManager->getStorage('user');
    $uids = $storage->getQuery()->condition('status', 1)->execute();
    $users = $uids ? $storage->loadMultiple($uids) : [];

    $rows = [];
    foreach ($users as $user) {
      $uid = $user->id();
      $balance = $this->loyaltyManager->getBalance($uid);
      $history = $this->loyaltyManager->getHistory($uid);
      $lastActivity = NULL;
      if (!empty($history)) {
        $lastActivity = date('Y-m-d H:i:s', (int) reset($history)->created->value);
      }

      $rows[] = [
        'data' => [
          $uid,
          $user->getDisplayName(),
          $user->getEmail(),
          $balance,
          $lastActivity ?? $this->t('No activity'),
          Link::fromTextAndUrl($this->t('History'), Url::fromRoute('mm_loyalty.admin_user_history', ['user' => $uid])),
        ],
      ];
    }

    $build = [
      '#type' => 'table',
      '#header' => [
        $this->t('UID'),
        $this->t('Username'),
        $this->t('Email'),
        $this->t('Balance'),
        $this->t('Last activity'),
        $this->t('Operations'),
      ],
      '#rows' => $rows,
      '#empty' => $this->t('No users found.'),
    ];

    return $build;
  }

}
