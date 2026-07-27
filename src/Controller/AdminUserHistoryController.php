<?php

declare(strict_types = 1);

namespace Drupal\mm_loyalty\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\User\AccountProxyInterface;
use Drupal\mm_loyalty\Service\LoyaltyManager;

/**
 * Controller for user loyalty history and adjustment.
 */
class AdminUserHistoryController extends ControllerBase {

  private EntityTypeManagerInterface $entityTypeManager;
  private LoyaltyManager $loyaltyManager;
  private FormBuilderInterface $formBuilder;
  private AccountProxyInterface $currentUser;

  public function __construct(EntityTypeManagerInterface $entityTypeManager, LoyaltyManager $loyaltyManager, FormBuilderInterface $formBuilder, AccountProxyInterface $currentUser) {
    $this->entityTypeManager = $entityTypeManager;
    $this->loyaltyManager = $loyaltyManager;
    $this->formBuilder = $formBuilder;
    $this->currentUser = $currentUser;
  }

  public static function create($container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('mm_loyalty.loyalty_manager'),
      $container->get('form_builder'),
      $container->get('current_user')
    );
  }

  public function history($user): array {
    $uid = (int) $user->id();
    $balance = $this->loyaltyManager->getBalance($uid);
    $history = $this->loyaltyManager->getHistory($uid);

    $rows = [];
    foreach ($history as $transaction) {
      $rows[] = [
        $transaction->created->value,
        $transaction->operation_type->value,
        $transaction->points->value,
        $transaction->description->value,
      ];
    }

    return [
      'balance' => [
        '#type' => 'markup',
        '#markup' => $this->t('Balance for @user: @points', ['@user' => $user->getAccountName(), '@points' => $balance]),
      ],
      'form' => $this->formBuilder->getForm('Drupal\mm_loyalty\Form\LoyaltyAdjustmentForm', $user),
      'history' => [
        '#type' => 'table',
        '#header' => [
          $this->t('Created'),
          $this->t('Operation'),
          $this->t('Points'),
          $this->t('Description'),
        ],
        '#rows' => $rows,
        '#empty' => $this->t('No loyalty history yet.'),
      ],
    ];
  }

}
