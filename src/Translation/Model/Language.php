<?php
/**
 * Created by IntelliJ IDEA.
 * User: onigoetz
 * Date: 23.02.14
 * Time: 15:28
 */
namespace Rocket\Translation\Model;

use Eloquent;

class Language extends Eloquent
{
    /**
     * @var string The table associated with the model.
     */
    protected $table = 'languages';
}
