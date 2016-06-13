<?php
/**
 * Created by IntelliJ IDEA.
 * User: onigoetz
 * Date: 23.02.14
 * Time: 15:28
 */
namespace Rocket\Translation\Model;

use Eloquent;

class Translation extends Eloquent
{
    /**
     * @var bool Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * @var string The table associated with the model.
     */
    protected $table = 'translations';
}
