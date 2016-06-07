<?php namespace Rocket\Entities;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Rocket\Entities\Exceptions\EntityNotFoundException;
use Rocket\Entities\Exceptions\InvalidFieldTypeException;
use Rocket\Entities\Exceptions\NonExistentFieldException;
use Rocket\Entities\Exceptions\NoPublishedRevisionForLanguageException;
use Rocket\Entities\Exceptions\NoRevisionForLanguageException;
use Rocket\Entities\Exceptions\ReservedFieldNameException;

/**
 * Entity manager
 *
 * @property int $id The content ID
 * @property int $language_id The language in which this entity is
 * @property string $type The type of the Entity
 * @property bool $published Is this content published
 * @property \Rocket\Entities\Revision[] $revisions all revisions in this content
 * @property-read \DateTime $created_at
 * @property-read \DateTime $updated_at
 */
abstract class Entity
{
    /**
     * @var array<class> The list of field types, filled with the configuration
     */
    public static $types;

    /**
     * The content represented by this entity
     *
     * @var Content
     */
    protected $content; //id, created_at, type, published

    /**
     * The revision represented by this entity
     *
     * @var Revision
     */
    protected $revision; //language_id, updated_at, type, published

    /**
     * The fields in this entity
     *
     * @var array<FieldCollection>
     */
    protected $data;

    /**
     * Entity constructor.
     *
     * @param int $language_id The language this specific entity is in
     */
    public function __construct($language_id)
    {
        if (!is_numeric($language_id) || $language_id == 0) {
            throw new InvalidArgumentException("You must set a valid 'language_id'.");
        }

        $fields = $this->getFields();

        $this->initialize($fields);

        $this->type = $this->getContentType();
        $this->language_id = $language_id;
    }

    /**
     * Creates the Content, Revision and FieldCollections
     *
     * @param array $fields The fields and their configurations
     * @throws InvalidFieldTypeException
     * @throws ReservedFieldNameException
     */
    protected function initialize(array $fields)
    {
        $this->content = new Content;
        $this->revision = new Revision;

        foreach ($fields as $field => $settings) {
            $this->data[$field] = $this->initializeField($field, $settings);
        }
    }

    /**
     * Validate configuration and prepare a FieldCollection
     *
     * @param string $field
     * @param array $settings
     * @throws InvalidFieldTypeException
     * @throws ReservedFieldNameException
     * @return FieldCollection
     */
    protected function initializeField($field, $settings)
    {
        if ($this->isContentField($field) || $this->isRevisionField($field)) {
            throw new ReservedFieldNameException(
                "The field '$field' cannot be used in '" . get_class($this) . "' as it is a reserved name"
            );
        }

        $type = $settings['type'];

        if (!array_key_exists($type, self::$types)) {
            throw new InvalidFieldTypeException("Unkown type '$type' in '" . get_class($this) . "'");
        }

        $settings['type'] = self::$types[$settings['type']];

        return FieldCollection::initField($settings);
    }

    /**
     * Return the fields in this entity
     *
     * @return array
     */
    abstract public function getFields();

    /**
     * Get the database friendly content type
     *
     * @return string
     */
    public static function getContentType()
    {
        return str_replace('\\', '', snake_case((new \ReflectionClass(get_called_class()))->getShortName()));
    }

    /**
     * Create a new revision based on the same content ID but without the content.
     * Very useful if you want to add a new language
     *
     * @param int $language_id
     * @return static
     */
    public function newRevision($language_id = null)
    {
        $created = new static($language_id ?: $this->language_id);
        $created->content = $this->content;

        return $created;
    }

    /**
     * Check if the field is related to the content
     *
     * @param string $field
     * @return bool
     */
    protected function isContentField($field)
    {
        return in_array($field, ['id', 'created_at', 'type', 'published']);
    }

    /**
     * Check if the field exists on the entity
     *
     * @param string $field
     * @return bool
     */
    public function hasField($field)
    {
        return array_key_exists($field, $this->data);
    }

    /**
     * Get a field's FieldCollection.
     *
     * Be careful as this gives you the real field instances.
     *
     * @param string $field
     * @return FieldCollection
     */
    public function getField($field)
    {
        return $this->data[$field];
    }

    /**
     * Check if the field is related to the revision
     *
     * @param string $field
     * @return bool
     */
    protected function isRevisionField($field)
    {
        return in_array($field, ['language_id', 'updated_at', 'published']);
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param string $key
     * @throws NonExistentFieldException
     * @return $this|bool|\Carbon\Carbon|\DateTime|mixed|static
     */
    public function __get($key)
    {
        if ($this->isContentField($key)) {
            return $this->content->getAttribute($key);
        }

        if ($this->isRevisionField($key)) {
            return $this->revision->getAttribute($key);
        }

        if ($this->hasField($key)) {
            return $this->getField($key);
        }

        if ($key == 'revisions') {
            return $this->content->revisions;
        }

        throw new NonExistentFieldException("Field '$key' doesn't exist in '" . get_class($this) . "'");
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param string $key
     * @param mixed $value
     * @throws NonExistentFieldException
     */
    public function __set($key, $value)
    {
        if ($this->isContentField($key)) {
            $this->content->setAttribute($key, $value);

            return;
        }

        if ($this->isRevisionField($key)) {
            $this->revision->setAttribute($key, $value);

            return;
        }

        if ($this->hasField($key)) {
            $this->setOnField($this->getField($key), $value);

            return;
        }

        throw new NonExistentFieldException("Field '$key' doesn't exist in '" . get_class($this) . "'");
    }

    /**
     * Set values on a field
     *
     * @param FieldCollection $field
     * @param $value
     */
    protected function setOnField(FieldCollection $field, $value)
    {
        if (!is_array($value)) {
            $field->offsetSet(0, $value);

            return;
        }

        $field->clear();

        // This happens when the array is
        // replaced completely by another array
        if (count($value)) {
            foreach ($value as $k => $v) {
                $field->offsetSet($k, $v);
            }
        }
    }

    /**
     * Get all field types in this Entity.
     *
     * @return Collection<string>
     */
    protected function getFieldTypes()
    {
        return (new Collection($this->getFields()))
            ->map(function ($options) {
                return $options['type'];
            })
            ->values()
            ->unique()
            ->map(function ($type) {
                return self::$types[$type];
            });
    }

    /**
     * Find the latest valid revision for this entity
     *
     * @param int $id
     * @param int $language_id
     * @return static
     * @throws EntityNotFoundException
     * @throws NoPublishedRevisionForLanguageException
     * @throws NoRevisionForLanguageException
     */
    public static function find($id, $language_id)
    {
        $instance = new static($language_id);

        try {
            $instance->content = Content::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw new EntityNotFoundException("The entity with id '$id' doesn't exist", 0, $e);
        }

        try {
            $instance->revision = Revision::where('content_id', $id)
                ->where('language_id', $language_id)
                ->where('published', true)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $count = Revision::where('content_id', $id)->where('language_id', $language_id)->count();

            if ($count) {
                $message = "There are revisions in language_id='$language_id' for Entity '$id' but none is published";
                throw new NoPublishedRevisionForLanguageException($message, 0, $e);
            } else {
                $message = "There no revisions in language_id='$language_id' for Entity '$id' but none is published";
                throw new NoRevisionForLanguageException($message, 0, $e);
            }
        }


        $instance->getFieldTypes()
            ->each(function ($type) use ($instance) {
                $type::where('revision_id', $instance->revision->id)
                    ->get()
                    ->each(function (Field $value) use ($instance) {
                        $instance->data[$value->name][$value->weight] = $value;
                    });
            });

        return $instance;
    }

    /**
     * Save a revision
     *
     * @param bool $newRevision Should we create a new revision, false by default
     * @param bool $publishRevision Should we immediately publish this revision, true by default
     */
    public function save($newRevision = false, $publishRevision = true)
    {
        if ($newRevision) {
            $revision = new Revision;
            $revision->language_id = $this->revision->language_id;

            $this->revision = $revision;
        }

        DB::transaction(
            function () use ($newRevision, $publishRevision) {
                $this->saveContent();

                $this->saveRevision($publishRevision);

                // Prepare and save fields
                foreach (array_keys($this->data) as $fieldName) {
                    /** @var FieldCollection $field */
                    $field = $this->data[$fieldName];

                    if (!$newRevision) {
                        $field->deleted()->each(function (Field $value) {
                            $value->delete();
                        });
                    }

                    $field->each(function (Field $value, $key) use ($newRevision, $fieldName) {
                        $value->weight = $key;
                        $value->name = $fieldName;
                        $this->saveField($value, $newRevision);
                    });

                    $field->syncOriginal();
                }
            }
        );
    }

    /**
     * Save the content
     */
    protected function saveContent()
    {
        $this->content->save();
    }

    /**
     * Save the revision
     *
     * @param bool $publishRevision Should we immediately publish this revision, true by default
     */
    protected function saveRevision($publishRevision)
    {
        if (!$this->revision->exists && !$publishRevision) {
            $this->revision->published = $publishRevision;
        }

        $this->revision->content_id = $this->content->id;
        $this->revision->save();

        if ($publishRevision) {
            $this->unpublishOtherRevisions();
        }
    }

    /**
     * Unpublish the revisions other than this one.
     * Only for the same content_id and language_id
     */
    protected function unpublishOtherRevisions()
    {
        if ($this->content->wasRecentlyCreated) {
            return;
        }

        // Unpublish all other revisions
        Revision::where('content_id', $this->content->id)
            ->where('language_id', $this->revision->language_id)
            ->where('id', '!=', $this->revision->id)
            ->update(['published' => false]);
    }

    /**
     * Save a single field instance
     *
     * @param Field $field The field instance to save
     * @param bool $newRevision Should we create a new revision?
     */
    protected function saveField(Field $field, $newRevision)
    {
        // If we create a new revision, this will
        // reinit the field to a non-saved field
        // and create a new row in the database
        if ($newRevision) {
            $field->id = null;
            $field->exists = false;
        }

        $field->revision_id = $this->revision->id;

        $field->save();
    }

    /**
     * Convert the Entity to an array.
     *
     * @return array
     */
    public function toArray()
    {
        $content = [
            '_content' => $this->content->toArray(),
            '_revision' => $this->revision->toArray(),
        ];

        foreach ($this->data as $field => $data) {
            $content[$field] = $data->toArray();
        }

        return $content;
    }

    public function delete($clear = true)
    {
        $revisions = Revision::where('content_id', $this->content->id)->get();

        $ids = $revisions->pluck('id');

        $this->getFieldTypes()->each(function ($type) use ($ids) {
            $type::whereIn('revision_id', $ids)->delete();
        });

        Revision::whereIn('id', $ids)->delete();
        $this->revision->exists = false;

        // TODO :: add an event system to be able to remove this content from entity fields

        $this->content->delete();

        if ($clear) {
            $this->revision->id = null;
            $this->content->id = null;
            $this->clearFields();
        }
    }

    public function deleteRevision($clear = true)
    {
        $this->getFieldTypes()->each(function ($type) {
            $type::where('revision_id', $this->revision->id)->delete();
        });

        // If this revision is currently
        // published, we need to publish
        // another revision in place.
        if ($this->revision->published && $this->revision->exists) {
            //TODO :: improve this logic
            Revision::where('content_id', $this->content->id)
                ->where('id', '!=', $this->revision->id)
                ->take(1)
                ->update(['published' => true]);
        }

        $this->revision->delete();

        if ($clear) {
            $this->clearFields();
        }
    }

    protected function clearFields()
    {
        // Void all the fields
        foreach (array_keys($this->data) as $fieldName) {
            /** @var FieldCollection $field */
            $field = $this->data[$fieldName];

            $field->clear();
            $field->syncOriginal();
        }
    }
}
