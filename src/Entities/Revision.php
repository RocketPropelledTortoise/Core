<?php namespace Rocket\Entities;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

/**
 * A revision is the status of a content at a certain moment in time
 *
 * @property integer $language_id the language of this revision
 * @property integer $content_id the content this revision is related to
 */
class Revision extends Model
{
    protected $data = [];

    public function initShape($fields)
    {
        $types = Config::get('rocket_entities.field_types');

        foreach ($fields as $field => $settings) {
            $type = $settings['type'];

            if (!array_key_exists($type, $types)) {
                throw new Exception("Unkown type '$type'");
            }

            $this->data[$field] = FieldCollection::initField($types[$type], $settings);
        }
    }

    protected function hasField($field)
    {
        return array_key_exists($field, $this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function setAttribute($key, $value)
    {
        if (!$this->hasField($key)) {
            return parent::setAttribute($key, $value);
        }

        if ($this->data[$key]->getMaxItems() == 1) {
            $this->data[$key][1] = $value;
            return;
        }

        throw new \Exception("how did you event get here ???");
        $this->data->$key = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute($key)
    {
        if (!$this->hasField($key)) {
            return parent::getAttribute($key);
        }

        //TODO :: get field
    }
}
