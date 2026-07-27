<?php

declare(strict_types = 1);

namespace Drupal\mm_loyalty\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\mm_loyalty\Service\LoyaltyManager;

/**
 * Access check for user loyalty page.
 */
class LoyaltyUserAccessCheck implements AccessInterface {

  private AccountProxyInterface $currentUser;
  private EntityTypeManagerInterface $entityTypeManager;
  private LoyaltyManager $loyaltyManager;

  public function __construct(AccountProxyInterface $currentUser, EntityTypeManagerInterface $entityTypeManager, LoyaltyManager $loyaltyManager) {
    $this->currentUser = $currentUser;
    $this->entityTypeManager = $entityTypeManager;
    $this->loyaltyManager = $loyaltyManager;
  }

  public function access(RouteMatchInterface $route_match): AccessResult {
    $user = $route_match->getParameter('user');
    if (!$user) {
      return AccessResult::forbidden();
    }

    $uid = (int) $user->id();
    if ($uid === $this->currentUser->id()) {
      return AccessResult::allowed();
    }

    if ($this->currentUser->hasPermission('view all loyalty history')) {
      return AccessResult::allowed();
    }

    return AccessResult::forbidden();
  }

}
