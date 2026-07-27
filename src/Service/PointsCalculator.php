<?php

declare(strict_types = 1);

namespace Drupal\mm_loyalty\Service;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Calculates loyalty points based on configurable rules.
 */
class PointsCalculator {

  public const CONFIG_NAME = 'mm_loyalty.settings';

  private ConfigFactoryInterface $configFactory;

  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
  }

  public function getAmountPerPoint(): int {
    return max(1, (int) $this->configFactory->get(self::CONFIG_NAME)->get('amount_per_point', 10));
  }

  public function getCommentPoints(): int {
    return max(0, (int) $this->configFactory->get(self::CONFIG_NAME)->get('comment_points', 100));
  }

  public function getRatingPoints(): int {
    return max(0, (int) $this->configFactory->get(self::CONFIG_NAME)->get('rating_points', 10));
  }

  public function getPurchaseEnabled(): bool {
    return (bool) $this->configFactory->get(self::CONFIG_NAME)->get('enable_purchase', TRUE);
  }

  public function getCommentEnabled(): bool {
    return (bool) $this->configFactory->get(self::CONFIG_NAME)->get('enable_comment', TRUE);
  }

  public function getRatingEnabled(): bool {
    return (bool) $this->configFactory->get(self::CONFIG_NAME)->get('enable_rating', TRUE);
  }

  public function calculatePurchasePoints(float $amount): int {
    $pointsPerCurrency = $this->getAmountPerPoint();
    if ($pointsPerCurrency <= 0) {
      return 0;
    }

    return (int) floor($amount / $pointsPerCurrency);
  }

}
