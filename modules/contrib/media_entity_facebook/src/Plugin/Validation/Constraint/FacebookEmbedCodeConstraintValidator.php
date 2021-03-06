<?php

namespace Drupal\media_entity_facebook\Plugin\Validation\Constraint;

use Drupal\media_entity\EmbedCodeValueTrait;
use Drupal\media_entity_facebook\Plugin\MediaEntity\Type\Facebook;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the FacebookEmbedCode constraint.
 */
class FacebookEmbedCodeConstraintValidator extends ConstraintValidator {

  use EmbedCodeValueTrait;

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
    $value = $this->getEmbedCode($value);
    if (!isset($value)) {
      return;
    }

    $post_url = Facebook::parseFacebookEmbedField($value);
    if ($post_url === FALSE) {
      $this->context->addViolation($constraint->message);
    }
  }

}
