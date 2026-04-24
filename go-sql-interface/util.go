package main

import (
	"os"
	"regexp"
	"strings"

	"github.com/ajitpratap0/GoSQLX/pkg/sql/ast"
)

func GetEnv(key, def string) string {
	if value, isPresent := os.LookupEnv(key); isPresent {
		return value
	} else {
		return def
	}
}

func NormalizeMysqlQuery(q string) string {
	// stripLeadingSQLComments
	q = strings.TrimSpace(q)
	for strings.HasPrefix(q, "/*") {
		end := strings.Index(q, "*/")
		if end < 0 {
			return q
		}
		q = strings.TrimSpace(q[end+2:])
	}

	// Wrap @@ system variables
	re := regexp.MustCompile(`@@[a-zA-Z_][a-zA-Z0-9_]*`)

	// replace each match unless already inside backticks
	q = re.ReplaceAllStringFunc(q, func(match string) string {
		// already wrapped? skip
		if len(match) > 2 && match[0] == '`' && match[len(match)-1] == '`' {
			return match
		}
		return "`" + match + "`"
	})

	// Replace bug instruction
	q = strings.ReplaceAll(q,
		"left(user(),instr(concat(user(),'@'),'@')-1)",
		"user_name",
	)

	// trim suffix
	q = strings.TrimSuffix(strings.TrimSpace(q), ";")
	return strings.TrimSpace(q)
}

func ColumnsToStringList(columns []ast.Expression) []string {
	columnsStr := make([]string, len(columns))
	for i, col := range columns {
		columnsStr[i] = col.TokenLiteral()
	}
	return columnsStr
}
