<?php
/**
 * Created by IntelliJ IDEA.
 * User: onigoetz
 * Date: 23.02.14
 * Time: 15:28
 */

namespace Rocket\Translation\Model;

use Eloquent;

class String extends Eloquent
{
    /**
     * {@inheritdoc}
     */
    public $timestamps = false;

    /**
     * {@inheritdoc}
     */
    protected $table = 'strings';
}
