<?php

/*
 * Generated from app/Dsl/Filter.g4 by ANTLR 4.13.2
 */

namespace {
	use Antlr\Antlr4\Runtime\Atn\ATNDeserializer;
	use Antlr\Antlr4\Runtime\Atn\LexerATNSimulator;
	use Antlr\Antlr4\Runtime\Lexer;
	use Antlr\Antlr4\Runtime\CharStream;
	use Antlr\Antlr4\Runtime\PredictionContexts\PredictionContextCache;
	use Antlr\Antlr4\Runtime\RuleContext;
	use Antlr\Antlr4\Runtime\Atn\ATN;
	use Antlr\Antlr4\Runtime\Dfa\DFA;
	use Antlr\Antlr4\Runtime\Vocabulary;
	use Antlr\Antlr4\Runtime\RuntimeMetaData;
	use Antlr\Antlr4\Runtime\VocabularyImpl;

	final class FilterLexer extends Lexer
	{
		public const T__0 = 1, T__1 = 2, T__2 = 3, T__3 = 4, T__4 = 5, NAME_KYW = 6, 
               DATE_KYW = 7, DESCRIPTION_KYW = 8, NUMBER = 9, STRING = 10, 
               WS = 11;

		/**
		 * @var array<string>
		 */
		public const CHANNEL_NAMES = [
			'DEFAULT_TOKEN_CHANNEL', 'HIDDEN'
		];

		/**
		 * @var array<string>
		 */
		public const MODE_NAMES = [
			'DEFAULT_MODE'
		];

		/**
		 * @var array<string>
		 */
		public const RULE_NAMES = [
			'T__0', 'T__1', 'T__2', 'T__3', 'T__4', 'NAME_KYW', 'DATE_KYW', 'DESCRIPTION_KYW', 
			'NUMBER', 'STRING', 'WS'
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
			[4, 0, 11, 86, 6, -1, 2, 0, 7, 0, 2, 1, 7, 1, 2, 2, 7, 2, 2, 3, 7, 3, 
		    2, 4, 7, 4, 2, 5, 7, 5, 2, 6, 7, 6, 2, 7, 7, 7, 2, 8, 7, 8, 2, 9, 
		    7, 9, 2, 10, 7, 10, 1, 0, 1, 0, 1, 1, 1, 1, 1, 2, 1, 2, 1, 2, 1, 3, 
		    1, 3, 1, 3, 1, 3, 1, 4, 1, 4, 1, 5, 1, 5, 1, 5, 1, 5, 1, 5, 1, 6, 
		    1, 6, 1, 6, 1, 6, 1, 6, 1, 7, 1, 7, 1, 7, 1, 7, 1, 7, 1, 7, 1, 7, 
		    1, 7, 1, 7, 1, 7, 1, 7, 1, 7, 1, 8, 4, 8, 60, 8, 8, 11, 8, 12, 8, 
		    61, 1, 8, 1, 8, 4, 8, 66, 8, 8, 11, 8, 12, 8, 67, 3, 8, 70, 8, 8, 
		    1, 9, 1, 9, 4, 9, 74, 8, 9, 11, 9, 12, 9, 75, 1, 9, 1, 9, 1, 10, 4, 
		    10, 81, 8, 10, 11, 10, 12, 10, 82, 1, 10, 1, 10, 0, 0, 11, 1, 1, 3, 
		    2, 5, 3, 7, 4, 9, 5, 11, 6, 13, 7, 15, 8, 17, 9, 19, 10, 21, 11, 1, 
		    0, 3, 1, 0, 48, 57, 4, 0, 48, 57, 65, 90, 95, 95, 97, 122, 3, 0, 9, 
		    10, 13, 13, 32, 32, 90, 0, 1, 1, 0, 0, 0, 0, 3, 1, 0, 0, 0, 0, 5, 
		    1, 0, 0, 0, 0, 7, 1, 0, 0, 0, 0, 9, 1, 0, 0, 0, 0, 11, 1, 0, 0, 0, 
		    0, 13, 1, 0, 0, 0, 0, 15, 1, 0, 0, 0, 0, 17, 1, 0, 0, 0, 0, 19, 1, 
		    0, 0, 0, 0, 21, 1, 0, 0, 0, 1, 23, 1, 0, 0, 0, 3, 25, 1, 0, 0, 0, 
		    5, 27, 1, 0, 0, 0, 7, 30, 1, 0, 0, 0, 9, 34, 1, 0, 0, 0, 11, 36, 1, 
		    0, 0, 0, 13, 41, 1, 0, 0, 0, 15, 46, 1, 0, 0, 0, 17, 59, 1, 0, 0, 
		    0, 19, 71, 1, 0, 0, 0, 21, 80, 1, 0, 0, 0, 23, 24, 5, 40, 0, 0, 24, 
		    2, 1, 0, 0, 0, 25, 26, 5, 41, 0, 0, 26, 4, 1, 0, 0, 0, 27, 28, 5, 
		    111, 0, 0, 28, 29, 5, 114, 0, 0, 29, 6, 1, 0, 0, 0, 30, 31, 5, 97, 
		    0, 0, 31, 32, 5, 110, 0, 0, 32, 33, 5, 100, 0, 0, 33, 8, 1, 0, 0, 
		    0, 34, 35, 5, 61, 0, 0, 35, 10, 1, 0, 0, 0, 36, 37, 5, 110, 0, 0, 
		    37, 38, 5, 97, 0, 0, 38, 39, 5, 109, 0, 0, 39, 40, 5, 101, 0, 0, 40, 
		    12, 1, 0, 0, 0, 41, 42, 5, 100, 0, 0, 42, 43, 5, 97, 0, 0, 43, 44, 
		    5, 116, 0, 0, 44, 45, 5, 101, 0, 0, 45, 14, 1, 0, 0, 0, 46, 47, 5, 
		    100, 0, 0, 47, 48, 5, 101, 0, 0, 48, 49, 5, 115, 0, 0, 49, 50, 5, 
		    99, 0, 0, 50, 51, 5, 114, 0, 0, 51, 52, 5, 105, 0, 0, 52, 53, 5, 112, 
		    0, 0, 53, 54, 5, 116, 0, 0, 54, 55, 5, 105, 0, 0, 55, 56, 5, 111, 
		    0, 0, 56, 57, 5, 110, 0, 0, 57, 16, 1, 0, 0, 0, 58, 60, 7, 0, 0, 0, 
		    59, 58, 1, 0, 0, 0, 60, 61, 1, 0, 0, 0, 61, 59, 1, 0, 0, 0, 61, 62, 
		    1, 0, 0, 0, 62, 69, 1, 0, 0, 0, 63, 65, 5, 46, 0, 0, 64, 66, 7, 0, 
		    0, 0, 65, 64, 1, 0, 0, 0, 66, 67, 1, 0, 0, 0, 67, 65, 1, 0, 0, 0, 
		    67, 68, 1, 0, 0, 0, 68, 70, 1, 0, 0, 0, 69, 63, 1, 0, 0, 0, 69, 70, 
		    1, 0, 0, 0, 70, 18, 1, 0, 0, 0, 71, 73, 5, 34, 0, 0, 72, 74, 7, 1, 
		    0, 0, 73, 72, 1, 0, 0, 0, 74, 75, 1, 0, 0, 0, 75, 73, 1, 0, 0, 0, 
		    75, 76, 1, 0, 0, 0, 76, 77, 1, 0, 0, 0, 77, 78, 5, 34, 0, 0, 78, 20, 
		    1, 0, 0, 0, 79, 81, 7, 2, 0, 0, 80, 79, 1, 0, 0, 0, 81, 82, 1, 0, 
		    0, 0, 82, 80, 1, 0, 0, 0, 82, 83, 1, 0, 0, 0, 83, 84, 1, 0, 0, 0, 
		    84, 85, 6, 10, 0, 0, 85, 22, 1, 0, 0, 0, 6, 0, 61, 67, 69, 75, 82, 
		    1, 6, 0, 0];
		protected static $atn;
		protected static $decisionToDFA;
		protected static $sharedContextCache;
		public function __construct(CharStream $input)
		{
			parent::__construct($input);

			self::initialize();

			$this->interp = new LexerATNSimulator($this, self::$atn, self::$decisionToDFA, self::$sharedContextCache);
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

		public static function vocabulary(): Vocabulary
		{
			static $vocabulary;

			return $vocabulary = $vocabulary ?? new VocabularyImpl(self::LITERAL_NAMES, self::SYMBOLIC_NAMES);
		}

		public function getGrammarFileName(): string
		{
			return 'Filter.g4';
		}

		public function getRuleNames(): array
		{
			return self::RULE_NAMES;
		}

		public function getSerializedATN(): array
		{
			return self::SERIALIZED_ATN;
		}

		/**
		 * @return array<string>
		 */
		public function getChannelNames(): array
		{
			return self::CHANNEL_NAMES;
		}

		/**
		 * @return array<string>
		 */
		public function getModeNames(): array
		{
			return self::MODE_NAMES;
		}

		public function getATN(): ATN
		{
			return self::$atn;
		}

		public function getVocabulary(): Vocabulary
		{
			return self::vocabulary();
		}
	}
}