<?php

namespace App\Dsl\FilterLanguage;

use Context;
use FilterListener;

final class ValidatorTree implements FilterListener
{
    public function enterExpr(\Context\ExprContext $context): void
    {
        // TODO: Implement enterExpr() method.
    }

    public function exitExpr(\Context\ExprContext $context): void
    {
        // TODO: Implement exitExpr() method.
    }

    public function enterCompare(\Context\CompareContext $context): void
    {
        throw_if($context->data()->NUMBER() && !is_numeric($context->data()->NUMBER()->getText()), SyntaxError::class);
        throw_if($context->data()->NUMBER() && !is_numeric($context->data()->NUMBER()->getText()), SyntaxError::class);
        throw_if(is_null($context->data()->NUMBER()) && is_null($context->data()->STRING()), SyntaxError::class);
    }

    public function exitCompare(\Context\CompareContext $context): void
    {
        // TODO: Implement exitCompare() method.
    }

    public function visitTerminal(\Antlr\Antlr4\Runtime\Tree\TerminalNode $node): void
    {
    }

    public function visitErrorNode(\Antlr\Antlr4\Runtime\Tree\ErrorNode $node): void
    {
        throw new \SyntaxError();
    }

    public function enterEveryRule(\Antlr\Antlr4\Runtime\ParserRuleContext $ctx): void
    {
    }

    public function exitEveryRule(\Antlr\Antlr4\Runtime\ParserRuleContext $ctx): void
    {
    }

    public function enterData(\Context\DataContext $context): void
    {
    }

    public function exitData(\Context\DataContext $context): void
    {
    }

    public function enterAnd(\Context\AndContext $context): void
    {
        // TODO: Implement enterAnd() method.
    }

    public function exitAnd(\Context\AndContext $context): void
    {
        // TODO: Implement exitAnd() method.
    }

    public function enterEqual(\Context\EqualContext $context): void
    {
        // TODO: Implement enterEqual() method.
    }

    public function exitEqual(\Context\EqualContext $context): void
    {
        // TODO: Implement exitEqual() method.
    }

    public function enterFactor(\Context\FactorContext $context): void
    {
        // TODO: Implement enterFactor() method.
    }

    public function exitFactor(\Context\FactorContext $context): void
    {
        // TODO: Implement exitFactor() method.
    }
}
