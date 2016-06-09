<?php namespace Rocket\Entities\Fields;

use Rocket\Entities\Field;

/**
 * Date Field
 *
 * @property Date $value The field's value
 */
class Date extends Field
{
    public $table = 'field_date';

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'value' => 'date',
    ];

    /**
     * {@inheritdoc}
     */
    protected function prepareValue($value)
    {
        $value = $this->asDateTime($value)->startOfDay();

        return $value->format($this->getDateFormat());
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return $this->serializeDate($this->getAttribute('value'));
    }

    /**
     * {@inheritdoc}
     */
    protected function isValid($value)
    {
        try {
            $this->asDateTime($value);

            return true;
        } catch (\InvalidArgumentException $e) {
            return false;
        }
    }
}
