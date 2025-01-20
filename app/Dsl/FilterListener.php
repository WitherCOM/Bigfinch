<?php

/*
 * Generated from app/Dsl/Filter.g4 by ANTLR 4.13.2
 */

use Antlr\Antlr4\Runtime\Tree\ParseTreeListener;

/**
 * This interface defines a complete listener for a parse tree produced by
 * {@see FilterParser}.
 */
interface FilterListener extends ParseTreeListener {
	/**
	 * Enter a parse tree produced by {@see FilterParser::expr()}.
	 * @param $context The parse tree.
	 */
	public function enterExpr(Context\ExprContext $context): void;
	/**
	 * Exit a parse tree produced by {@see FilterParser::expr()}.
	 * @param $context The parse tree.
	 */
	public function exitExpr(Context\ExprContext $context): void;
	/**
	 * Enter a parse tree produced by {@see FilterParser::and()}.
	 * @param $context The parse tree.
	 */
	public function enterAnd(Context\AndContext $context): void;
	/**
	 * Exit a parse tree produced by {@see FilterParser::and()}.
	 * @param $context The parse tree.
	 */
	public function exitAnd(Context\AndContext $context): void;
	/**
	 * Enter a parse tree produced by {@see FilterParser::equal()}.
	 * @param $context The parse tree.
	 */
	public function enterEqual(Context\EqualContext $context): void;
	/**
	 * Exit a parse tree produced by {@see FilterParser::equal()}.
	 * @param $context The parse tree.
	 */
	public function exitEqual(Context\EqualContext $context): void;
	/**
	 * Enter a parse tree produced by {@see FilterParser::data()}.
	 * @param $context The parse tree.
	 */
	public function enterData(Context\DataContext $context): void;
	/**
	 * Exit a parse tree produced by {@see FilterParser::data()}.
	 * @param $context The parse tree.
	 */
	public function exitData(Context\DataContext $context): void;
	/**
	 * Enter a parse tree produced by {@see FilterParser::factor()}.
	 * @param $context The parse tree.
	 */
	public function enterFactor(Context\FactorContext $context): void;
	/**
	 * Exit a parse tree produced by {@see FilterParser::factor()}.
	 * @param $context The parse tree.
	 */
	public function exitFactor(Context\FactorContext $context): void;
}