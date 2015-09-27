<?php namespace Rocket\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * A revision is the status of a content at a certain moment in time
 *
 * @property int $language_id the language of this revision
 * @property int $content_id the content this revision is related to
 */
class Revision extends Model
{
}
