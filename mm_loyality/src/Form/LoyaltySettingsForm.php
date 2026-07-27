<?php

declare(strict_types = 1);

namespace Drupal\mm_loyalty\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Provides a configuration form for MM Loyalty.
 */
class LoyaltySettingsForm extends ConfigFormBase {

  public const SETTINGS = 'mm_loyalty.settings';

  public function __construct(ConfigFactoryInterface $config_factory) {
    parent::__construct($config_factory);
  }

  public static function create($container) {
    return new static($container->get('config.factory'));
  }

  public function getFormId(): string {
    return 'mm_loyalty_settings_form';
  }

  protected function getEditableConfigNames(): array {
    return [self::SETTINGS];
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config(self::SETTINGS);

    $form['amount_per_point'] = [
      '#type' => 'number',
      '#title' => $this->t('Amount in PLN for 1 point'),
      '#default_value' => $config->get('amount_per_point'),
      '#min' => 1,
      '#required' => TRUE,
    ];

    $form['comment_points'] = [
      '#type' => 'number',
      '#title' => $this->t('Points for product comment'),
      '#default_value' => $config->get('comment_points'),
      '#min' => 0,
      '#required' => TRUE,
    ];

    $form['rating_points'] = [
      '#type' => 'number',
      '#title' => $this->t('Points for product rating'),
      '#default_value' => $config->get('rating_points'),
      '#min' => 0,
      '#required' => TRUE,
    ];

    $form['enable_purchase'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable purchase points'),
      '#default_value' => $config->get('enable_purchase'),
    ];

    $form['enable_comment'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable comment points'),
      '#default_value' => $config->get('enable_comment'),
    ];

    $form['enable_rating'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable rating points'),
      '#default_value' => $config->get('enable_rating'),
    ];

    return parent::buildForm($form, $form_state);
  }

  public function validateForm(array &$form, FormStateInterface $form_state): void {
    if ($form_state->getValue('amount_per_point') <= 0) {
      $form_state->setErrorByName('amount_per_point', $this->t('The amount per point must be greater than zero.'));
    }

    if ($form_state->getValue('comment_points') < 0) {
      $form_state->setErrorByName('comment_points', $this->t('Comment points cannot be negative.'));
    }

    if ($form_state->getValue('rating_points') < 0) {
      $form_state->setErrorByName('rating_points', $this->t('Rating points cannot be negative.'));
    }

    parent::validateForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config(self::SETTINGS)
      ->set('amount_per_point', (int) $form_state->getValue('amount_per_point'))
      ->set('comment_points', (int) $form_state->getValue('comment_points'))
      ->set('rating_points', (int) $form_state->getValue('rating_points'))
      ->set('enable_purchase', (bool) $form_state->getValue('enable_purchase'))
      ->set('enable_comment', (bool) $form_state->getValue('enable_comment'))
      ->set('enable_rating', (bool) $form_state->getValue('enable_rating'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
