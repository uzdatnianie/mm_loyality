<?php

declare(strict_types = 1);

namespace Drupal\mm_loyalty\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the loyalty transaction entity.
 *
 * @ContentEntityType(
 *   id = "mm_loyalty_transaction",
 *   label = @Translation("Loyalty transaction"),
 *   base_table = "mm_loyalty_transactions",
 *   entity_keys = {
 *     "id" = "id",
 *     "uid" = "uid"
 *   },
 *   handlers = {
 *     "access" = "Drupal\Core\Entity\EntityAccessControlHandler"
 *   }
 * )
 */
class LoyaltyTransaction extends ContentEntityBase implements EntityOwnerInterface {

  use EntityOwnerTrait;

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User'))
      ->setSetting('target_type', 'user')
      ->setRequired(TRUE);

    $fields['points'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Points'))
      ->setRequired(TRUE);

    $fields['operation_type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Operation type'))
      ->setSettings(['max_length' => 64])
      ->setRequired(TRUE);

    $fields['entity_type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Entity type'))
      ->setSettings(['max_length' => 64])
      ->setRequired(FALSE);

    $fields['entity_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Entity ID'))
      ->setSettings(['max_length' => 128])
      ->setRequired(FALSE);

    $fields['order_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Order ID'))
      ->setRequired(FALSE);

    $fields['description'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Description'))
      ->setSettings(['max_length' => 255])
      ->setRequired(FALSE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'));

    return $fields;
  }

}
