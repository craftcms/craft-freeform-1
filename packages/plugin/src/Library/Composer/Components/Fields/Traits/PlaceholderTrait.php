<?php
/**
 * Freeform for Craft CMS.
 *
 * @author        Solspace, Inc.
 * @copyright     Copyright (c) 2008-2022, Solspace, Inc.
 *
 * @see           https://docs.solspace.com/craft/freeform
 *
 * @license       https://docs.solspace.com/license-agreement
 */

namespace Solspace\Freeform\Library\Composer\Components\Fields\Traits;

use Solspace\Freeform\Attributes\Field\EditableProperty;
use Solspace\Freeform\Freeform;

trait PlaceholderTrait
{
    #[EditableProperty(
        label: 'Placeholder',
        instructions: 'The text that will be shown if the field doesn\'t have a value',
    )]
    protected string $placeholder = '';

    public function getPlaceholder(): string
    {
        return Freeform::t($this->placeholder);
    }
}
