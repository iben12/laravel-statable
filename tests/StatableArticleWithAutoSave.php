<?php

namespace Iben\Statable\Test;

use Iben\Statable\Statable;
use Illuminate\Database\Eloquent\Model;

class StatableArticleWithAutoSave extends Model
{
    use Statable;

    protected $table = 'articles';

    protected $guarded = [];

    /**
     * @return string
     */
    protected function getGraph()
    {
        return 'article';
    }

    protected function saveBeforeTransition()
    {
        return true;
    }
}
