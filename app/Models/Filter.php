<?php

namespace App\Models;

use Antlr\Antlr4\Runtime\CommonTokenStream;
use Antlr\Antlr4\Runtime\InputStream;
use Antlr\Antlr4\Runtime\Tree\ParseTreeWalker;
use App\Dsl\FilterLanguage\Collection;
use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids as HasUuids;
use App\Enums\Direction;
use App\Enums\FilterAction;
use App\Enums\Flag;
use App\Models\Scopes\OwnerScope;
use FilterLexer;
use FilterParser;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Filter extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'from',
        'to',
        'tag',
        'merchant',
        'direction',
        'min_value',
        'max_value',
        'currency',
        'description',
        'flag',
        'action',
        'action_parameters',
        'user_id'
    ];

    protected $casts = [
        'from' => 'datetime',
        'to' => 'datetime',
        'direction' => Direction::class,
        'flag' => Flag::class,
        'action' => FilterAction::class,
        'action_parameters' => 'array'
    ];

    public function filterHighlight(): Attribute
    {
        return Attribute::get(function () {
            $highlight = "";
            foreach (['from', 'to', 'tag', 'merchant', 'direction',
                         'min_value', 'max_value', 'currency', 'description', 'flag'] as $key) {
                if ($this->{$key}) {
                    $highlight .= "$key=" . $this->{$key} . ",";
                }
            }
            return $highlight;
        });
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
