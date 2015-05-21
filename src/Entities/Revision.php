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
}
