<?php

declare(strict_types = 1);

namespace Drupal\mm_loyalty\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines a reward redemption entity.
 *
 * @ContentEntityType(
 *   id = "mm_loyalty_reward_redemption",
 *   label = @Translation("Reward redemption"),
 *   base_table = "mm_loyalty_reward_redemptions",
 *   entity_keys = {
 *     "id" = "id"
 *   },
 *   handlers = {
 *     "access" = "Drupal\Core\Entity\EntityAccessControlHandler"
 *   }
 * )
 */
class RewardRedemption extends ContentEntityBase {

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User'))
      ->setSetting('target_type', 'user')
      ->setRequired(TRUE);

    $fields['reward_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Reward ID'))
      ->setRequired(TRUE);

    $fields['status'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Status'))
      ->setSettings(['max_length' => 64])
      ->setRequired(TRUE)
      ->setDefaultValue('new');

    $fields['description'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Description'))
      ->setSettings(['max_length' => 255])
      ->setRequired(FALSE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'));

    return $fields;
  }

}
