<?php

/*
 * Generated from app/Dsl/Filter.g4 by ANTLR 4.13.2
 */

namespace {
	use Antlr\Antlr4\Runtime\Atn\ATN;
	use Antlr\Antlr4\Runtime\Atn\ATNDeserializer;
	use Antlr\Antlr4\Runtime\Atn\ParserATNSimulator;
	use Antlr\Antlr4\Runtime\Dfa\DFA;
	use Antlr\Antlr4\Runtime\Error\Exceptions\FailedPredicateException;
	use Antlr\Antlr4\Runtime\Error\Exceptions\NoViableAltException;
	use Antlr\Antlr4\Runtime\PredictionContexts\PredictionContextCache;
	use Antlr\Antlr4\Runtime\Error\Exceptions\RecognitionException;
	use Antlr\Antlr4\Runtime\RuleContext;
	use Antlr\Antlr4\Runtime\Token;
	use Antlr\Antlr4\Runtime\TokenStream;
	use Antlr\Antlr4\Runtime\Vocabulary;
	use Antlr\Antlr4\Runtime\VocabularyImpl;
	use Antlr\Antlr4\Runtime\RuntimeMetaData;
	use Antlr\Antlr4\Runtime\Parser;

	final class FilterParser extends Parser
	{
		public const T__0 = 1, T__1 = 2, T__2 = 3, T__3 = 4, T__4 = 5, NAME_KYW = 6, 
               DATE_KYW = 7, DESCRIPTION_KYW = 8, NUMBER = 9, STRING = 10, 
               WS = 11;

		public const RULE_expr = 0, RULE_and = 1, RULE_equal = 2, RULE_data = 3, 
               RULE_factor = 4;

		/**
		 * @var array<string>
		 */
		public const RULE_NAMES = [
			'expr', 'and', 'equal', 'data', 'factor'
		];

		/**
		 * @var array<string|null>
		 */
		private const LITERAL_NAMES = [
		    null, "'('", "')'", "'or'", "'and'", "'='", "'name'", "'date'", "'description'"
		];

		/**
		 * @var array<string>
		 */
		private const SYMBOLIC_NAMES = [
		    null, null, null, null, null, null, "NAME_KYW", "DATE_KYW", "DESCRIPTION_KYW", 
		    "NUMBER", "STRING", "WS"
		];

		private const SERIALIZED_ATN =
			[4, 1, 11, 42, 2, 0, 7, 0, 2, 1, 7, 1, 2, 2, 7, 2, 2, 3, 7, 3, 2, 4, 
		    7, 4, 1, 0, 1, 0, 1, 0, 1, 0, 1, 0, 1, 0, 1, 0, 1, 0, 4, 0, 19, 8, 
		    0, 11, 0, 12, 0, 20, 1, 0, 3, 0, 24, 8, 0, 1, 1, 1, 1, 1, 1, 5, 1, 
		    29, 8, 1, 10, 1, 12, 1, 32, 9, 1, 1, 2, 1, 2, 1, 2, 1, 2, 1, 3, 1, 
		    3, 1, 4, 1, 4, 1, 4, 0, 0, 5, 0, 2, 4, 6, 8, 0, 2, 1, 0, 9, 10, 1, 
		    0, 6, 8, 39, 0, 23, 1, 0, 0, 0, 2, 25, 1, 0, 0, 0, 4, 33, 1, 0, 0, 
		    0, 6, 37, 1, 0, 0, 0, 8, 39, 1, 0, 0, 0, 10, 11, 5, 1, 0, 0, 11, 12, 
		    3, 2, 1, 0, 12, 18, 5, 2, 0, 0, 13, 14, 5, 3, 0, 0, 14, 15, 5, 1, 
		    0, 0, 15, 16, 3, 2, 1, 0, 16, 17, 5, 2, 0, 0, 17, 19, 1, 0, 0, 0, 
		    18, 13, 1, 0, 0, 0, 19, 20, 1, 0, 0, 0, 20, 18, 1, 0, 0, 0, 20, 21, 
		    1, 0, 0, 0, 21, 24, 1, 0, 0, 0, 22, 24, 3, 2, 1, 0, 23, 10, 1, 0, 
		    0, 0, 23, 22, 1, 0, 0, 0, 24, 1, 1, 0, 0, 0, 25, 30, 3, 4, 2, 0, 26, 
		    27, 5, 4, 0, 0, 27, 29, 3, 4, 2, 0, 28, 26, 1, 0, 0, 0, 29, 32, 1, 
		    0, 0, 0, 30, 28, 1, 0, 0, 0, 30, 31, 1, 0, 0, 0, 31, 3, 1, 0, 0, 0, 
		    32, 30, 1, 0, 0, 0, 33, 34, 3, 8, 4, 0, 34, 35, 5, 5, 0, 0, 35, 36, 
		    3, 6, 3, 0, 36, 5, 1, 0, 0, 0, 37, 38, 7, 0, 0, 0, 38, 7, 1, 0, 0, 
		    0, 39, 40, 7, 1, 0, 0, 40, 9, 1, 0, 0, 0, 3, 20, 23, 30];
		protected static $atn;
		protected static $decisionToDFA;
		protected static $sharedContextCache;

		public function __construct(TokenStream $input)
		{
			parent::__construct($input);

			self::initialize();

			$this->interp = new ParserATNSimulator($this, self::$atn, self::$decisionToDFA, self::$sharedContextCache);
		}

		private static function initialize(): void
		{
			if (self::$atn !== null) {
				return;
			}

			RuntimeMetaData::checkVersion('4.13.2', RuntimeMetaData::VERSION);

			$atn = (new ATNDeserializer())->deserialize(self::SERIALIZED_ATN);

			$decisionToDFA = [];
			for ($i = 0, $count = $atn->getNumberOfDecisions(); $i < $count; $i++) {
				$decisionToDFA[] = new DFA($atn->getDecisionState($i), $i);
			}

			self::$atn = $atn;
			self::$decisionToDFA = $decisionToDFA;
			self::$sharedContextCache = new PredictionContextCache();
		}

		public function getGrammarFileName(): string
		{
			return "Filter.g4";
		}

		public function getRuleNames(): array
		{
			return self::RULE_NAMES;
		}

		public function getSerializedATN(): array
		{
			return self::SERIALIZED_ATN;
		}

		public function getATN(): ATN
		{
			return self::$atn;
		}

		public function getVocabulary(): Vocabulary
        {
            static $vocabulary;

			return $vocabulary = $vocabulary ?? new VocabularyImpl(self::LITERAL_NAMES, self::SYMBOLIC_NAMES);
        }

		/**
		 * @throws RecognitionException
		 */
		public function expr(): Context\ExprContext
		{
		    $localContext = new Context\ExprContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 0, self::RULE_expr);

		    try {
		        $this->setState(23);
		        $this->errorHandler->sync($this);

		        switch ($this->input->LA(1)) {
		            case self::T__0:
		            	$this->enterOuterAlt($localContext, 1);
		            	$this->setState(10);
		            	$this->match(self::T__0);
		            	$this->setState(11);
		            	$this->and_();
		            	$this->setState(12);
		            	$this->match(self::T__1);
		            	$this->setState(18); 
		            	$this->errorHandler->sync($this);

		            	$_la = $this->input->LA(1);
		            	do {
		            		$this->setState(13);
		            		$this->match(self::T__2);
		            		$this->setState(14);
		            		$this->match(self::T__0);
		            		$this->setState(15);
		            		$this->and_();
		            		$this->setState(16);
		            		$this->match(self::T__1);
		            		$this->setState(20); 
		            		$this->errorHandler->sync($this);
		            		$_la = $this->input->LA(1);
		            	} while ($_la === self::T__2);
		            	break;

		            case self::NAME_KYW:
		            case self::DATE_KYW:
		            case self::DESCRIPTION_KYW:
		            	$this->enterOuterAlt($localContext, 2);
		            	$this->setState(22);
		            	$this->and_();
		            	break;

		        default:
		        	throw new NoViableAltException($this);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function and_(): Context\AndContext
		{
		    $localContext = new Context\AndContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 2, self::RULE_and);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(25);
		        $this->equal();
		        $this->setState(30);
		        $this->errorHandler->sync($this);

		        $_la = $this->input->LA(1);
		        while ($_la === self::T__3) {
		        	$this->setState(26);
		        	$this->match(self::T__3);
		        	$this->setState(27);
		        	$this->equal();
		        	$this->setState(32);
		        	$this->errorHandler->sync($this);
		        	$_la = $this->input->LA(1);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function equal(): Context\EqualContext
		{
		    $localContext = new Context\EqualContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 4, self::RULE_equal);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(33);
		        $this->factor();
		        $this->setState(34);
		        $this->match(self::T__4);
		        $this->setState(35);
		        $this->data();
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function data(): Context\DataContext
		{
		    $localContext = new Context\DataContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 6, self::RULE_data);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(37);

		        $_la = $this->input->LA(1);

		        if (!($_la === self::NUMBER || $_la === self::STRING)) {
		        $this->errorHandler->recoverInline($this);
		        } else {
		        	if ($this->input->LA(1) === Token::EOF) {
		        	    $this->matchedEOF = true;
		            }

		        	$this->errorHandler->reportMatch($this);
		        	$this->consume();
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function factor(): Context\FactorContext
		{
		    $localContext = new Context\FactorContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 8, self::RULE_factor);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(39);

		        $_la = $this->input->LA(1);

		        if (!(((($_la) & ~0x3f) === 0 && ((1 << $_la) & 448) !== 0))) {
		        $this->errorHandler->recoverInline($this);
		        } else {
		        	if ($this->input->LA(1) === Token::EOF) {
		        	    $this->matchedEOF = true;
		            }

		        	$this->errorHandler->reportMatch($this);
		        	$this->consume();
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}
	}
}

namespace Context {
	use Antlr\Antlr4\Runtime\ParserRuleContext;
	use Antlr\Antlr4\Runtime\Token;
	use Antlr\Antlr4\Runtime\Tree\ParseTreeVisitor;
	use Antlr\Antlr4\Runtime\Tree\TerminalNode;
	use Antlr\Antlr4\Runtime\Tree\ParseTreeListener;
	use FilterParser;
	use FilterVisitor;
	use FilterListener;

	class ExprContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return FilterParser::RULE_expr;
	    }

	    /**
	     * @return array<AndContext>|AndContext|null
	     */
	    public function and(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(AndContext::class);
	    	}

	        return $this->getTypedRuleContext(AndContext::class, $index);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof FilterListener) {
			    $listener->enterExpr($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof FilterListener) {
			    $listener->exitExpr($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof FilterVisitor) {
			    return $visitor->visitExpr($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class AndContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return FilterParser::RULE_and;
	    }

	    /**
	     * @return array<EqualContext>|EqualContext|null
	     */
	    public function equal(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(EqualContext::class);
	    	}

	        return $this->getTypedRuleContext(EqualContext::class, $index);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof FilterListener) {
			    $listener->enterAnd($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof FilterListener) {
			    $listener->exitAnd($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof FilterVisitor) {
			    return $visitor->visitAnd($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class EqualContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return FilterParser::RULE_equal;
	    }

	    public function factor(): ?FactorContext
	    {
	    	return $this->getTypedRuleContext(FactorContext::class, 0);
	    }

	    public function data(): ?DataContext
	    {
	    	return $this->getTypedRuleContext(DataContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof FilterListener) {
			    $listener->enterEqual($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof FilterListener) {
			    $listener->exitEqual($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof FilterVisitor) {
			    return $visitor->visitEqual($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class DataContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return FilterParser::RULE_data;
	    }

	    public function NUMBER(): ?TerminalNode
	    {
	        return $this->getToken(FilterParser::NUMBER, 0);
	    }

	    public function STRING(): ?TerminalNode
	    {
	        return $this->getToken(FilterParser::STRING, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof FilterListener) {
			    $listener->enterData($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof FilterListener) {
			    $listener->exitData($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof FilterVisitor) {
			    return $visitor->visitData($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class FactorContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return FilterParser::RULE_factor;
	    }

	    public function NAME_KYW(): ?TerminalNode
	    {
	        return $this->getToken(FilterParser::NAME_KYW, 0);
	    }

	    public function DATE_KYW(): ?TerminalNode
	    {
	        return $this->getToken(FilterParser::DATE_KYW, 0);
	    }

	    public function DESCRIPTION_KYW(): ?TerminalNode
	    {
	        return $this->getToken(FilterParser::DESCRIPTION_KYW, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof FilterListener) {
			    $listener->enterFactor($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof FilterListener) {
			    $listener->exitFactor($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof FilterVisitor) {
			    return $visitor->visitFactor($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 
}