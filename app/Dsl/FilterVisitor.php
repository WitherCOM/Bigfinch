<?php

/*
 * Generated from app/Dsl/Filter.g4 by ANTLR 4.13.2
 */

use Antlr\Antlr4\Runtime\Tree\ParseTreeVisitor;

/**
 * This interface defines a complete generic visitor for a parse tree produced by {@see FilterParser}.
 */
interface FilterVisitor extends ParseTreeVisitor
{
	/**
	 * Visit a parse tree produced by {@see FilterParser::expr()}.
	 *
	 * @param Context\ExprContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitExpr(Context\ExprContext $context);

	/**
	 * Visit a parse tree produced by {@see FilterParser::and()}.
	 *
	 * @param Context\AndContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitAnd(Context\AndContext $context);

	/**
	 * Visit a parse tree produced by {@see FilterParser::equal()}.
	 *
	 * @param Context\EqualContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitEqual(Context\EqualContext $context);

	/**
	 * Visit a parse tree produced by {@see FilterParser::data()}.
	 *
	 * @param Context\DataContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitData(Context\DataContext $context);

	/**
	 * Visit a parse tree produced by {@see FilterParser::factor()}.
	 *
	 * @param Context\FactorContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitFactor(Context\FactorContext $context);
}