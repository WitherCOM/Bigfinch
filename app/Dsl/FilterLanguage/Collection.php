<?php

namespace App\Dsl\FilterLanguage;
use Antlr\Antlr4\Runtime\Tree\ParseTree;
use Context;

class Collection extends \FilterBaseVisitor
{
    private \Illuminate\Support\Collection $baseCollection;
    private array $collections = [];

    public function __construct(\Illuminate\Support\Collection $collection)
    {
        $this->baseCollection = $collection;
    }

    public function visitData(\Context\DataContext $context)
    {
        if ($context->NUMBER())
        {
            return floatval($context->NUMBER()->getText());
        }
        else
        {
            return $context->STRING()->getText();
        }
    }

    public function visitAnd(\Context\AndContext $context)
    {
        $collection = $this->baseCollection;
        foreach ($context->equal() as $equal)
        {
            $collection = $collection->where($equal->factor()->getText(), $this->visitData($equal->data()));
        }
        $this->collections[] = $collection;
    }

    public function collection(): \Illuminate\Support\Collection
    {
        $output = collect();
        foreach ($this->collections as $collection)
        {
            $output = $output->merge($collection);
        }
        return $output;
    }
}
