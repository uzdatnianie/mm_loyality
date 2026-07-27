<?php

declare(strict_types = 1);

namespace Drupal\mm_loyalty\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\mm_loyalty\Service\LoyaltyManager;
use Drupal\user\UserInterface;

/**
 * Provides a form to adjust loyalty points for a user.
 */
class LoyaltyAdjustmentForm extends FormBase {

  private LoyaltyManager $loyaltyManager;

  public function __construct(LoyaltyManager $loyaltyManager) {
    $this->loyaltyManager = $loyaltyManager;
  }

  public static function create($container) {
    return new static($container->get('mm_loyalty.loyalty_manager'));
  }

  public function getFormId(): string {
    return 'mm_loyalty_adjustment_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, UserInterface $user = NULL): array {
    $form['user_id'] = [
      '#type' => 'value',
      '#value' => $user ? $user->id() : 0,
    ];

    $form['points'] = [
      '#type' => 'number',
      '#title' => $this->t('Points to add or remove'),
      '#description' => $this->t('Positive to add points, negative to remove points.'),
      '#required' => TRUE,
    ];

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Reason'),
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $points = (int) $form_state->getValue('points');
    if ($points === 0) {
      $form_state->setErrorByName('points', $this->t('You must enter a non-zero point amount.'));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $uid = (int) $form_state->getValue('user_id');
    $points = (int) $form_state->getValue('points');
    $description = $form_state->getValue('description');

    $this->loyaltyManager->adjustPoints($uid, $points, $description);
    $this->messenger()->addMessage($this->t('Loyalty balance has been updated.'));
    $form_state->setRedirectUrl(Url::fromRoute('mm_loyalty.admin_user_history', ['user' => $uid]));
  }

}
