<?php

namespace App\Models;

use Antlr\Antlr4\Runtime\CommonTokenStream;
use Antlr\Antlr4\Runtime\InputStream;
use Antlr\Antlr4\Runtime\Tree\ParseTreeWalker;
use App\Dsl\FilterLanguage\Collection;
use App\Models\Scopes\OwnerScope;
use FilterLexer;
use FilterParser;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Filter extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'rule',
        'is_active',
        'is_default',
        'user_id'
    ];

    public function validate(): bool
    {
        $data = InputStream::fromString($this->rule);
        $parser = new FilterParser(new CommonTokenStream(new FilterLexer($data)));
        $parser->setBuildParseTree(true);
        try {
            ParseTreeWalker::default()->walk(new \App\Dsl\FilterLanguage\ValidatorTree(), $parser->expr());
        } catch (\SyntaxError)
        {
            return false;
        }
        return true;
    }

    public function apply()
    {
        $data = InputStream::fromString($this);
        $parser = new FilterParser(new CommonTokenStream(new FilterLexer($data)));
        $parser->setBuildParseTree(true);
        $visitor = new Collection(collect());
        $visitor->visit($parser->expr());
        $visitor->collection();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
